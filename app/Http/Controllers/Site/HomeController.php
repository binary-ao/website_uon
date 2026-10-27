<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Event;
use App\Models\News;
use App\Models\Notice;
use App\Models\Partner;
use App\Support\SafeUrl;
use App\Support\Settings;

class HomeController extends Controller
{
    public function __invoke()
    {
        $news = News::news()->published()->with(['category', 'featuredMedia'])
            ->orderByDesc('is_featured')->orderByDesc('published_at')->limit(3)->get();

        return view('public.home', [
            'slides' => \App\Models\HeroSlide::current()->with('media')->whereNotNull('media_id')->limit(8)->get(),
            'notices' => Notice::current()->limit(4)->get(),
            'courses' => Course::published()->with(['degree', 'unit', 'featuredMedia'])->orderBy('sort_order')->orderBy('name')->limit(4)->get(),
            'news' => $news,
            'events' => Event::published()->upcoming()->limit(3)->get(),
            'partners' => Partner::active()->with('logo')->limit(6)->get(),
            'stats' => Settings::pairs('home_stats'),
            'research' => static::researchLinks(),
        ]);
    }

    /** Ligações às plataformas externas de investigação (apenas as configuradas). */
    public static function researchLinks(): array
    {
        $links = [
            'research_url' => 'Centro de Investigação',
            'research_projects_url' => 'Projectos',
            'research_publications_url' => 'Publicações',
            'research_journals_url' => 'Revistas Científicas',
        ];
        $out = [];
        foreach ($links as $key => $label) {
            $url = SafeUrl::resolve(Settings::get($key));
            if ($url) {
                $out[] = ['label' => $label, 'url' => $url];
            }
        }

        return $out;
    }
}
