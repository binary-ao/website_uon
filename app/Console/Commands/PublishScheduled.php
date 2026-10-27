<?php

namespace App\Console\Commands;

use App\Enums\PublicationStatus;
use App\Models\Course;
use App\Models\Document;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\News;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Services\ActivityLogger;
use App\Services\MenuRepository;
use Illuminate\Console\Command;

/**
 * Converte "Agendado" em "Publicado" quando a data chega.
 * Nota: a visibilidade pública NÃO depende deste comando (o website já
 * mostra conteúdos agendados cuja data passou); o comando mantém os
 * estados coerentes no CMS. Executado pelo agendador (schedule:run).
 */
class PublishScheduled extends Command
{
    protected $signature = 'content:publish-scheduled';

    protected $description = 'Actualiza para "Publicado" os conteúdos agendados cuja data já chegou';

    public function handle(): int
    {
        $total = 0;
        foreach ([Page::class, News::class, Event::class, Document::class, Course::class, OrganicUnit::class, GalleryItem::class] as $model) {
            $items = $model::where('status', PublicationStatus::Scheduled->value)->where('published_at', '<=', now())->get();
            foreach ($items as $item) {
                $item->status = PublicationStatus::Published;
                $item->saveQuietly();
                ActivityLogger::log('content.auto_published', $item, 'Publicação agendada concluída: '.($item->title ?? $item->name));
                $total++;
            }
        }
        if ($total) {
            MenuRepository::flush();
        }
        $this->info("Conteúdos publicados: {$total}");

        return self::SUCCESS;
    }
}
