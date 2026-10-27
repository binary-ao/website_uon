<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Armazenamento versionado dos documentos institucionais, fora da pasta
 * pública (disco "documents"). Substituir o ficheiro cria uma nova versão
 * e preserva as anteriores.
 */
class DocumentStorage
{
    public function addVersion(Document $document, UploadedFile $file, ?string $notes = null): DocumentVersion
    {
        $info = UploadValidator::inspect($file, ['document', 'image'], 'file');
        $disk = Storage::disk('documents');
        $path = $document->id.'/'.Str::lower(Str::random(40)).'.'.$info['extension'];
        $disk->putFileAs(dirname($path), $file, basename($path));

        try {
            return DB::transaction(function () use ($document, $file, $info, $path, $notes, $disk) {
                $next = (int) $document->versions()->lockForUpdate()->max('version') + 1;
                $version = $document->versions()->create([
                    'version' => $next,
                    'disk' => 'documents',
                    'path' => $path,
                    'original_name' => UploadValidator::cleanOriginalName($file),
                    'mime_type' => $info['mime'],
                    'extension' => $info['extension'],
                    'size' => $disk->size($path),
                    'checksum' => hash_file('sha256', $disk->path($path)),
                    'notes' => $notes ? Str::limit(strip_tags($notes), 250) : null,
                    'uploaded_by' => auth()->id(),
                    'created_at' => now(),
                ]);
                $document->current_version_id = $version->id;
                $document->saveQuietly();

                return $version;
            });
        } catch (\Throwable $e) {
            $disk->delete($path);
            throw $e;
        }
    }

    /** Remove definitivamente os ficheiros de todas as versões (apenas na eliminação permanente). */
    public function purgeFiles(Document $document): void
    {
        $disk = Storage::disk('documents');
        foreach ($document->versions as $v) {
            $disk->delete($v->path);
        }
        $disk->deleteDirectory((string) $document->id);
    }
}
