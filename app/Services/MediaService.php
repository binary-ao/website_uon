<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Biblioteca multimédia: armazenamento seguro de imagens e ficheiros públicos.
 * - Nomes de armazenamento aleatórios (o nome original nunca é usado no caminho).
 * - Imagens re-codificadas com GD: remove metadados/conteúdo embebido,
 *   limita a largura máxima e gera uma variante média para listagens.
 */
class MediaService
{
    public function store(UploadedFile $file, array $attributes = [], array $kinds = ['image', 'document']): Media
    {
        $info = UploadValidator::inspect($file, $kinds);
        $disk = Storage::disk('uploads');
        $dir = ($info['kind'] === 'image' ? 'images/' : 'files/').now()->format('Y/m');
        $base = Str::lower(Str::random(32));

        $media = new Media();
        $media->disk = 'uploads';
        $media->original_name = UploadValidator::cleanOriginalName($file);
        $media->extension = $info['extension'];
        $media->mime_type = $info['mime'];
        $media->uploaded_by = auth()->id();
        $media->fill(array_intersect_key($attributes, array_flip(['title', 'alt_text', 'description'])));
        if (! $media->title) {
            $media->title = Str::limit(pathinfo($media->original_name, PATHINFO_FILENAME), 120, '');
        }

        $written = [];
        try {
            if ($info['kind'] === 'image') {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getRealPath());
                $maxW = config('uon.uploads.image_max_width');
                if ($image->width() > $maxW) {
                    $image->scaleDown(width: $maxW);
                }
                $ext = $info['extension'] === 'gif' ? 'png' : $info['extension'];
                $path = $dir.'/'.$base.'.'.$ext;
                $disk->put($path, (string) $this->encode($image, $ext));
                $written[] = $path;

                $variants = [];
                if ($image->width() > 800) {
                    $md = $manager->read($disk->path($path))->scaleDown(width: 800);
                    $mdPath = $dir.'/'.$base.'-md.'.$ext;
                    $disk->put($mdPath, (string) $this->encode($md, $ext));
                    $written[] = $mdPath;
                    $variants['md'] = $mdPath;
                }

                $media->path = $path;
                $media->file_name = basename($path);
                $media->extension = $ext;
                $media->mime_type = $ext === 'jpg' ? 'image/jpeg' : 'image/'.$ext;
                $media->width = $image->width();
                $media->height = $image->height();
                $media->variants = $variants ?: null;
                $media->size = $disk->size($path);
            } else {
                $path = $dir.'/'.$base.'.'.$info['extension'];
                $disk->putFileAs($dir, $file, basename($path));
                $written[] = $path;
                $media->path = $path;
                $media->file_name = basename($path);
                $media->size = $disk->size($path);
            }

            $media->save();
        } catch (\Throwable $e) {
            foreach ($written as $p) {
                $disk->delete($p);
            }
            throw $e;
        }

        ActivityLogger::log('media.uploaded', $media, 'Carregou o ficheiro "'.$media->original_name.'"');

        return $media;
    }

    private function encode($image, string $ext)
    {
        $quality = config('uon.uploads.image_quality', 82);

        return match ($ext) {
            'png' => $image->toPng(),
            'webp' => $image->toWebp($quality),
            default => $image->toJpeg($quality),
        };
    }

    /** Eliminação controlada: recusa se o ficheiro estiver em uso. */
    public function delete(Media $media): array
    {
        $usages = MediaUsage::find($media);
        if ($usages) {
            return $usages;
        }

        DB::transaction(function () use ($media) {
            $disk = Storage::disk($media->disk);
            $paths = array_merge([$media->path], array_values($media->variants ?? []));
            $media->delete();
            foreach ($paths as $p) {
                $disk->delete($p);
            }
        });
        ActivityLogger::log('media.deleted', null, 'Eliminou o ficheiro "'.$media->original_name.'"', ['path' => $media->path]);

        return [];
    }
}
