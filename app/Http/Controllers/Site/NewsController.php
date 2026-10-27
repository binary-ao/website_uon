<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\News;

class NewsController extends Controller
{
    public function show(string $slug)
    {
        return $this->display($this->find('news', $slug));
    }

    public function showAnnouncement(string $slug)
    {
        return $this->display($this->find('announcement', $slug));
    }

    private function find(string $type, string $slug): News
    {
        return News::published()->where('type', $type)->where('slug', $slug)
            ->with(['category', 'tags', 'featuredMedia', 'documents' => fn ($d) => $d->published()->with('currentVersion')])
            ->firstOrFail();
    }

    public function display(News $news, bool $isPreview = false)
    {
        $related = News::published()->where('type', $news->type)->whereKeyNot($news->id)
            ->when($news->category_id, fn ($q) => $q->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$news->category_id]))
            ->with(['category', 'featuredMedia'])
            ->orderByDesc('published_at')->limit(3)->get();

        return view('public.news-show', [
            'news' => $news,
            'related' => $related,
            'isPreview' => $isPreview,
            'previewBackUrl' => $isPreview ? route('admin.news.edit', $news) : null,
        ]);
    }
}
