<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\News;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Models\Partner;
use App\Models\SiteSetting;
use App\Support\SettingsSchema;

/**
 * Verifica onde um ficheiro da biblioteca está a ser usado, para impedir
 * a eliminação de ficheiros associados a conteúdos.
 */
class MediaUsage
{
    /** @return array<int, string> descrições legíveis de cada utilização */
    public static function find(Media $media): array
    {
        $out = [];
        $id = $media->id;

        $fk = [
            [Page::class, 'featured_media_id', 'Página', 'title'],
            [News::class, 'featured_media_id', 'Notícia/comunicado', 'title'],
            [Event::class, 'featured_media_id', 'Evento', 'title'],
            [Course::class, 'featured_media_id', 'Curso', 'name'],
            [OrganicUnit::class, 'featured_media_id', 'Unidade orgânica', 'name'],
            [GalleryItem::class, 'media_id', 'Galeria', 'title'],
            [Partner::class, 'logo_media_id', 'Parceiro', 'name'],
            [\App\Models\HeroSlide::class, 'media_id', 'Banner da página inicial', 'title'],
        ];
        foreach ($fk as [$model, $column, $label, $titleCol]) {
            $query = $model::query()->where($column, $id);
            if (method_exists($model, 'bootSoftDeletes')) {
                $query->withTrashed();
            }
            foreach ($query->limit(20)->get([$titleCol]) as $row) {
                $out[] = $label.': '.$row->{$titleCol};
            }
        }

        $mediaKeys = array_keys(array_filter(SettingsSchema::fields(), fn ($f) => $f['type'] === 'media'));
        foreach (SiteSetting::whereIn('key', $mediaKeys)->where('value', (string) $id)->get() as $s) {
            $out[] = 'Configuração: '.(SettingsSchema::fields()[$s->key]['label'] ?? $s->key);
        }

        // Referências dentro de conteúdos HTML e blocos (imagens inseridas no editor).
        $needles = array_merge([$media->path], array_values($media->variants ?? []));
        foreach ($needles as $needle) {
            foreach ([[Page::class, ['body', 'blocks'], 'Página', 'title'], [News::class, ['body'], 'Notícia/comunicado', 'title'], [Event::class, ['body'], 'Evento', 'title'], [Course::class, ['presentation', 'objectives'], 'Curso', 'name']] as [$model, $cols, $label, $titleCol]) {
                $query = $model::withTrashed()->where(function ($w) use ($cols, $needle) {
                    foreach ($cols as $c) {
                        $w->orWhere($c, 'like', '%'.addcslashes($needle, '%_\\').'%');
                    }
                });
                foreach ($query->limit(20)->get([$titleCol]) as $row) {
                    $out[] = $label.' (conteúdo): '.$row->{$titleCol};
                }
            }
        }

        // Blocos de página que referenciam o ficheiro pelo ID (bloco "imagem").
        foreach (Page::withTrashed()->whereNotNull('blocks')->get(['title', 'blocks']) as $page) {
            foreach ((array) $page->blocks as $block) {
                if ((int) ($block['data']['media_id'] ?? 0) === $id) {
                    $out[] = 'Página (bloco): '.$page->title;
                }
            }
        }

        return array_values(array_unique($out));
    }
}
