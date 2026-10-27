<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Document;
use App\Models\Event;
use App\Models\Media;
use App\Models\News;
use App\Models\Page;
use Illuminate\Support\Collection;

/** Painel principal — todos os indicadores são calculados na base de dados. */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $count = fn ($model, ?PublicationStatus $status = null) => $model::query()
            ->when($status, fn ($q) => $q->where('status', $status->value))->count();

        $stats = [
            'pages' => ['total' => Page::count(), 'published' => $count(Page::class, PublicationStatus::Published), 'draft' => $count(Page::class, PublicationStatus::Draft)],
            'news' => ['total' => News::count(), 'published' => $count(News::class, PublicationStatus::Published), 'scheduled' => $count(News::class, PublicationStatus::Scheduled)],
            'events' => ['total' => Event::count(), 'upcoming' => Event::published()->upcoming()->count()],
            'documents' => ['total' => Document::count(), 'restricted' => Document::where('visibility', 'restricted')->count()],
            'courses' => ['total' => Course::count(), 'published' => $count(Course::class, PublicationStatus::Published)],
            'media' => ['total' => Media::count(), 'size' => (int) Media::sum('size')],
            'messages' => ['new' => $user->can('messages.manage') ? ContactMessage::where('status', 'new')->count() : null],
        ];

        $pending = $this->pending();
        $recent = $this->recent();
        $emptyPages = Page::published()->where('layout', 'standard')->whereNull('body')->whereNull('blocks')->orderBy('path')->limit(12)->get(['id', 'title', 'path']);

        $activity = $user->can('activity.view')
            ? ActivityLog::with('user:id,name')->latest('id')->limit(8)->get()
            : collect();

        return view('admin.dashboard', compact('stats', 'pending', 'recent', 'emptyPages', 'activity'));
    }

    /** Conteúdos pendentes de revisão (estado "Em revisão"). */
    private function pending(): Collection
    {
        return collect([
            [Page::class, 'Página', 'admin.pages.edit', 'title'],
            [News::class, 'Notícia', 'admin.news.edit', 'title'],
            [Event::class, 'Evento', 'admin.events.edit', 'title'],
            [Document::class, 'Documento', 'admin.documents.edit', 'title'],
            [Course::class, 'Curso', 'admin.courses.edit', 'name'],
        ])->flatMap(function ($def) {
            [$model, $label, $route, $col] = $def;

            return $model::with('author:id,name')->where('status', PublicationStatus::Pending->value)
                ->latest('updated_at')->limit(10)->get()
                ->map(fn ($m) => (object) ['label' => $label, 'title' => $m->{$col}, 'url' => route($route, $m), 'author' => $m->author?->name, 'updated_at' => $m->updated_at]);
        })->sortByDesc('updated_at')->take(10)->values();
    }

    /** Conteúdos actualizados recentemente. */
    private function recent(): Collection
    {
        return collect([
            [Page::class, 'Página', 'admin.pages.edit', 'title'],
            [News::class, 'Notícia', 'admin.news.edit', 'title'],
            [Event::class, 'Evento', 'admin.events.edit', 'title'],
            [Document::class, 'Documento', 'admin.documents.edit', 'title'],
            [Course::class, 'Curso', 'admin.courses.edit', 'name'],
        ])->flatMap(function ($def) {
            [$model, $label, $route, $col] = $def;

            return $model::latest('updated_at')->limit(8)->get()
                ->map(fn ($m) => (object) ['label' => $label, 'title' => $m->{$col}, 'url' => route($route, $m), 'status' => $m->status, 'updated_at' => $m->updated_at]);
        })->sortByDesc('updated_at')->take(8)->values();
    }
}
