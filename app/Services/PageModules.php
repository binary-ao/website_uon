<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Course;
use App\Models\Degree;
use App\Models\Document;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\News;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Models\Partner;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Dados para as páginas cujo conteúdo principal é um módulo do CMS.
 * Todos os filtros são aplicados no servidor (query string) e a paginação
 * é feita na base de dados.
 */
class PageModules
{
    public function data(Page $page, Request $request): array
    {
        $method = Str::camel($page->layout);

        return method_exists($this, $method) ? $this->{$method}($page, $request) : [];
    }

    private function search(Request $request): string
    {
        return Str::limit(trim((string) $request->query('q', '')), 100, '');
    }

    private function like(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }

    private function newsIndex(Page $page, Request $request): array
    {
        $categories = Category::ofType('news')->get();
        $current = $categories->firstWhere('slug', (string) $request->query('categoria'));
        $q = $this->search($request);

        $items = News::news()->published()
            ->with(['category', 'featuredMedia'])
            ->when($current, fn ($w) => $w->where('category_id', $current->id))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', $this->like($q))->orWhere('excerpt', 'like', $this->like($q))))
            ->orderByDesc('published_at')
            ->paginate(config('uon.pagination.public'))
            ->withQueryString();

        return compact('items', 'categories', 'current', 'q');
    }

    private function announcementsIndex(Page $page, Request $request): array
    {
        $q = $this->search($request);
        $items = News::announcements()->published()
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', $this->like($q))->orWhere('excerpt', 'like', $this->like($q))))
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return compact('items', 'q');
    }

    private function eventsIndex(Page $page, Request $request): array
    {
        $period = $request->query('periodo') === 'anteriores' ? 'anteriores' : 'proximos';
        $categories = Category::ofType('event')->get();
        $current = $categories->firstWhere('slug', (string) $request->query('categoria'));

        $items = Event::published()
            ->with(['category'])
            ->when($current, fn ($w) => $w->where('category_id', $current->id))
            ->when($period === 'anteriores', fn ($w) => $w->past(), fn ($w) => $w->upcoming())
            ->paginate(10)
            ->withQueryString();

        return compact('items', 'period', 'categories', 'current');
    }

    private function agenda(Page $page, Request $request): array
    {
        $events = Event::published()->upcoming()
            ->where('starts_at', '<=', now()->addMonths(12))
            ->limit(100)
            ->get();
        $months = $events->groupBy(fn (Event $e) => Str::ucfirst($e->starts_at->translatedFormat('F Y')));

        return compact('months');
    }

    private function gallery(Page $page, Request $request): array
    {
        $categories = Category::ofType('gallery')->get();
        $current = $categories->firstWhere('slug', (string) $request->query('categoria'));
        $items = GalleryItem::published()->where('type', 'image')->whereNotNull('media_id')
            ->with('media')
            ->when($current, fn ($w) => $w->where('category_id', $current->id))
            ->orderBy('sort_order')->orderByDesc('published_at')
            ->paginate(18)->withQueryString();

        return compact('items', 'categories', 'current');
    }

    private function multimedia(Page $page, Request $request): array
    {
        $items = GalleryItem::published()->where('type', 'video')
            ->orderBy('sort_order')->orderByDesc('published_at')
            ->paginate(9)->withQueryString();

        return compact('items');
    }

    private function coursesIndex(Page $page, Request $request): array
    {
        $fixedDegree = $page->option('degree') ? Degree::where('slug', $page->option('degree'))->first() : null;
        $degrees = Degree::orderBy('sort_order')->whereHas('courses', fn ($c) => $c->published())->get();
        $current = $fixedDegree ?? $degrees->firstWhere('slug', (string) $request->query('grau'));
        // Unidades com cursos próprios ou com cursos nos seus departamentos (faculdades).
        $units = OrganicUnit::published()
            ->where(fn ($w) => $w->whereHas('courses', fn ($c) => $c->published())
                ->orWhereHas('children', fn ($ch) => $ch->published()->whereHas('courses', fn ($c) => $c->published())))
            ->with(['children:id,parent_id'])->orderBy('type')->orderBy('name')->get(['id', 'name', 'slug', 'type']);
        $unit = $units->firstWhere('slug', (string) $request->query('unidade'));
        $unitIds = $unit ? array_merge([$unit->id], $unit->children->pluck('id')->all()) : [];
        $q = $this->search($request);

        $items = Course::published()
            ->with(['degree', 'unit', 'featuredMedia'])
            ->when($current, fn ($w) => $w->where('degree_id', $current->id))
            ->when($unit, fn ($w) => $w->whereIn('organic_unit_id', $unitIds))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', $this->like($q))->orWhere('summary', 'like', $this->like($q))))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(12)->withQueryString();

        return compact('items', 'degrees', 'current', 'fixedDegree', 'units', 'unit', 'q');
    }

    private function unitsDirectory(Page $page, Request $request): array
    {
        $type = $page->option('unit_type', 'academic');
        $types = match ($type) {
            'academic' => ['faculdade', 'instituto', 'escola', 'centro'],
            default => [$type],
        };
        $items = OrganicUnit::published()->whereIn('type', $types)
            ->with(['parent', 'page', 'featuredMedia',
                'courses' => fn ($c) => $c->published()->select('id', 'organic_unit_id', 'name', 'slug'),
                'children' => fn ($c) => $c->published()->with(['courses' => fn ($q) => $q->published()->select('id', 'organic_unit_id')]),
            ])
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        return compact('items', 'type');
    }

    private function documentsIndex(Page $page, Request $request): array
    {
        $categoryId = $page->option('category_id');
        $q = $this->search($request);
        $year = (int) $request->query('ano');

        $base = Document::published()
            ->when($categoryId, fn ($w) => $w->where('category_id', $categoryId))
            ->when(! auth()->check(), fn ($w) => $w->where('visibility', 'public'))
            ->whereNotNull('current_version_id');

        $years = (clone $base)->whereNotNull('document_date')->pluck('document_date')
            ->map(fn ($d) => $d->year)->unique()->sortDesc()->values();

        $items = (clone $base)->with(['currentVersion', 'category'])
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', $this->like($q))->orWhere('description', 'like', $this->like($q))))
            ->when($year > 0, fn ($w) => $w->whereYear('document_date', $year))
            ->orderByDesc('document_date')->orderBy('title')
            ->paginate(15)->withQueryString();

        return compact('items', 'q', 'year', 'years');
    }

    private function partners(Page $page, Request $request): array
    {
        return ['items' => Partner::active()->with('logo')->get()];
    }

    private function contact(Page $page, Request $request): array
    {
        return ['subjects' => Settings::lines('contact_subjects')];
    }
}
