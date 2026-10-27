<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Event;
use App\Models\News;
use App\Models\Page;

class SeoController extends Controller
{
    public function sitemap()
    {
        $urls = collect([['loc' => url('/'), 'lastmod' => null]]);
        if (setting('seo_allow_indexing')) {
            foreach (Page::published()->where('show_in_search', true)->get(['path', 'updated_at']) as $p) {
                $urls->push(['loc' => url($p->path), 'lastmod' => $p->updated_at]);
            }
            foreach (News::published()->get(['type', 'slug', 'updated_at']) as $n) {
                $urls->push(['loc' => $n->url(), 'lastmod' => $n->updated_at]);
            }
            foreach (Event::published()->get(['slug', 'updated_at']) as $e) {
                $urls->push(['loc' => $e->url(), 'lastmod' => $e->updated_at]);
            }
            foreach (\App\Models\OrganicUnit::published()->get(['slug', 'updated_at']) as $u) {
                $urls->push(['loc' => route('units.show', $u->slug), 'lastmod' => $u->updated_at]);
            }
            foreach (Course::published()->get(['slug', 'updated_at']) as $c) {
                $urls->push(['loc' => $c->url(), 'lastmod' => $c->updated_at]);
            }
        }

        return response()->view('public.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $lines = ['User-agent: *'];
        if (setting('seo_allow_indexing')) {
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /pesquisa';
            $lines[] = '';
            $lines[] = 'Sitemap: '.route('sitemap');
        } else {
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
