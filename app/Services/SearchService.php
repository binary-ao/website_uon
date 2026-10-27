<?php

namespace App\Services;

use App\Enums\PublicationStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Pesquisa global no conteúdo publicado (páginas, notícias/comunicados,
 * eventos, cursos, documentos públicos e unidades orgânicas).
 * Consultas parametrizadas; resultados unidos e paginados na base de dados.
 */
class SearchService
{
    public const TYPES = [
        'paginas' => 'Páginas',
        'noticias' => 'Notícias e comunicados',
        'eventos' => 'Eventos',
        'cursos' => 'Cursos',
        'documentos' => 'Documentos',
        'unidades' => 'Unidades orgânicas',
    ];

    public function search(string $term, array $types, int $perPage = 10): LengthAwarePaginator
    {
        $types = array_values(array_intersect($types ?: array_keys(self::TYPES), array_keys(self::TYPES)));
        $like = '%'.addcslashes($term, '%_\\').'%';
        $now = now();
        $public = [PublicationStatus::Published->value, PublicationStatus::Scheduled->value];

        $published = function (Builder $q, string $table) use ($now, $public) {
            $q->whereIn("$table.status", $public)->where("$table.published_at", '<=', $now);
            if (in_array($table, ['pages', 'news', 'events', 'documents', 'courses', 'organic_units'], true)) {
                $q->whereNull("$table.deleted_at");
            }
        };

        $queries = [];

        if (in_array('paginas', $types, true)) {
            $q = DB::table('pages')->selectRaw("'page' as kind, id, title, excerpt as snippet, path as ref, NULL as subtype, published_at as sort_date, CASE WHEN title LIKE ? THEN 2 ELSE 1 END as relevance", [$like])
                ->where('show_in_search', true)
                ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('body', 'like', $like)->orWhere('blocks', 'like', $like));
            $published($q, 'pages');
            $queries[] = $q;
        }
        if (in_array('noticias', $types, true)) {
            $q = DB::table('news')->selectRaw("'news' as kind, id, title, excerpt as snippet, slug as ref, type as subtype, published_at as sort_date, CASE WHEN title LIKE ? THEN 2 ELSE 1 END as relevance", [$like])
                ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('body', 'like', $like));
            $published($q, 'news');
            $queries[] = $q;
        }
        if (in_array('eventos', $types, true)) {
            $q = DB::table('events')->selectRaw("'event' as kind, id, title, excerpt as snippet, slug as ref, NULL as subtype, starts_at as sort_date, CASE WHEN title LIKE ? THEN 2 ELSE 1 END as relevance", [$like])
                ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('location', 'like', $like)->orWhere('body', 'like', $like));
            $published($q, 'events');
            $queries[] = $q;
        }
        if (in_array('cursos', $types, true)) {
            $q = DB::table('courses')->leftJoin('degrees', 'degrees.id', '=', 'courses.degree_id')
                ->selectRaw("'course' as kind, courses.id, courses.name as title, courses.summary as snippet, courses.slug as ref, degrees.name as subtype, courses.published_at as sort_date, CASE WHEN courses.name LIKE ? THEN 2 ELSE 1 END as relevance", [$like])
                ->where(fn ($w) => $w->where('courses.name', 'like', $like)->orWhere('courses.summary', 'like', $like)->orWhere('courses.presentation', 'like', $like));
            $published($q, 'courses');
            $queries[] = $q;
        }
        if (in_array('documentos', $types, true)) {
            $q = DB::table('documents')->selectRaw("'document' as kind, id, title, description as snippet, slug as ref, NULL as subtype, published_at as sort_date, CASE WHEN title LIKE ? THEN 2 ELSE 1 END as relevance", [$like])
                ->where('visibility', 'public')->whereNotNull('current_version_id')
                ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like));
            $published($q, 'documents');
            $queries[] = $q;
        }
        if (in_array('unidades', $types, true)) {
            $q = DB::table('organic_units')->leftJoin('pages', 'pages.id', '=', 'organic_units.page_id')
                ->selectRaw("'unit' as kind, organic_units.id, organic_units.name as title, organic_units.summary as snippet, organic_units.slug as ref, organic_units.type as subtype, organic_units.published_at as sort_date, CASE WHEN organic_units.name LIKE ? THEN 2 ELSE 1 END as relevance", [$like])
                ->where(fn ($w) => $w->where('organic_units.name', 'like', $like)->orWhere('organic_units.summary', 'like', $like)->orWhere('organic_units.description', 'like', $like));
            $published($q, 'organic_units');
            $queries[] = $q;
        }

        if (! $queries) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $union = array_shift($queries);
        foreach ($queries as $q) {
            $union->unionAll($q);
        }

        $paginator = DB::query()->fromSub($union, 'results')
            ->orderByDesc('relevance')->orderByDesc('sort_date')
            ->paginate($perPage)->withQueryString();

        $paginator->getCollection()->transform(fn ($row) => $this->present($row));

        return $paginator;
    }

    private function present(object $row): object
    {
        [$label, $url] = match ($row->kind) {
            'page' => ['Página Institucional', url($row->ref)],
            'news' => [$row->subtype === 'announcement' ? 'Comunicado' : 'Notícia', url(($row->subtype === 'announcement' ? 'comunicacao/comunicados/' : 'comunicacao/noticias/').$row->ref)],
            'event' => ['Evento', url('comunicacao/eventos/'.$row->ref)],
            'course' => ['Curso'.($row->subtype ? ' · '.$row->subtype : ''), url('ensino/cursos/'.$row->ref)],
            'document' => ['Documento', route('documents.download', $row->ref)],
            'unit' => [\App\Models\OrganicUnit::TYPES[$row->subtype] ?? 'Unidade orgânica', route('units.show', $row->ref)],
            default => ['Conteúdo', null],
        };
        $row->label = $label;
        $row->url = $url;
        $row->snippet = \App\Support\HtmlSanitizer::toText($row->snippet, 220);

        return $row;
    }
}
