<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Document;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\News;
use App\Models\Notice;
use App\Models\OrganicUnit;
use App\Models\Partner;
use App\Services\DocumentStorage;
use App\Services\MediaService;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** Cria ou remove o conteúdo de demonstração (is_demo = 1). */
class DemoContent extends Command
{
    protected $signature = 'uon:demo-content {--remove : Remove todo o conteúdo de demonstração} {--force : Não pedir confirmação}';

    protected $description = 'Cria (ou remove com --remove) conteúdo de demonstração claramente identificado';

    private bool $reapplyOfficial = false;

    public function handle(): int
    {
        if (! $this->option('remove')) {
            $this->call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Remover definitivamente todo o conteúdo marcado como demonstração?', true)) {
            return self::SUCCESS;
        }

        DB::transaction(function () {
            // Repor páginas e configurações alteradas pelo conteúdo de demonstração.
            $stateRow = \App\Models\SiteSetting::where('key', DemoContentSeeder::STATE_KEY)->first();
            $state = $stateRow ? (json_decode($stateRow->value, true) ?: []) : [];
            $this->reapplyOfficial = isset($state['official_fills']);
            // Conteúdo misto: limpar os campos de exemplo acrescentados aos registos oficiais.
            foreach ($state['official_fills'] ?? [] as $fill) {
                $model = $fill['model']::find($fill['id']);
                if (! $model) {
                    continue;
                }
                foreach ($fill['fields'] as $field) {
                    $model->setAttribute($field, null);
                }
                if (array_key_exists('description', $fill)) {
                    $model->setAttribute('description', $fill['description']);
                }
                if (! empty($fill['subjects'])) {
                    $model->subjects()->delete();
                }
                $model->save();
            }
            foreach ($state['pages'] ?? [] as $pageId => $snapshot) {
                if ($page = \App\Models\Page::withTrashed()->find($pageId)) {
                    $page->forceFill($snapshot)->save();
                }
            }
            foreach ($state['settings'] ?? [] as $key => $value) {
                $value === null
                    ? \App\Models\SiteSetting::where('key', $key)->delete()
                    : \App\Models\SiteSetting::where('key', $key)->update(['value' => $value]);
            }
            $stateRow?->delete();
            \App\Support\Settings::flush();

            foreach (News::withTrashed()->where('is_demo', true)->get() as $n) {
                $n->tags()->detach();
                $n->documents()->detach();
                $n->revisions()->delete();
                $n->forceDelete();
            }
            foreach (Event::withTrashed()->where('is_demo', true)->get() as $e) {
                $e->revisions()->delete();
                $e->forceDelete();
            }
            foreach (Course::withTrashed()->where('is_demo', true)->get() as $c) {
                $c->revisions()->delete();
                $c->forceDelete();
            }
            OrganicUnit::withTrashed()->where('is_demo', true)->whereNotNull('parent_id')->forceDelete();
            OrganicUnit::withTrashed()->where('is_demo', true)->forceDelete();
            GalleryItem::where('is_demo', true)->delete();
            Notice::where('is_demo', true)->delete();
            \App\Models\HeroSlide::where('is_demo', true)->delete();
            Partner::where('is_demo', true)->delete();
            $storage = app(DocumentStorage::class);
            foreach (Document::withTrashed()->where('is_demo', true)->get() as $d) {
                $d->forceFill(['current_version_id' => null])->saveQuietly();
                DB::table('documentables')->where('document_id', $d->id)->delete();
                $storage->purgeFiles($d);
                $d->forceDelete();
            }
        });

        $media = app(MediaService::class);
        $kept = 0;
        foreach (Media::where('description', DemoContentSeeder::MEDIA_MARK)->get() as $m) {
            if ($media->delete($m)) {
                $kept++;
            }
        }

        $this->info('Conteúdo de demonstração removido.'.($kept ? " {$kept} imagem(ns) mantida(s) por estarem em uso." : ''));

        // As páginas foram repostas ao estado anterior aos exemplos: voltar a aplicar o conteúdo oficial.
        if ($this->reapplyOfficial) {
            $this->call('db:seed', ['--class' => \Database\Seeders\OfficialContentSeeder::class, '--force' => true]);
        }

        return self::SUCCESS;
    }
}
