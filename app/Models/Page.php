<?php

namespace App\Models;

use App\Casts\UnicodeJson;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\Revisionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasPublication, HasUniqueSlug, Revisionable, SoftDeletes;

    /**
     * Layouts disponíveis — cada um corresponde a um tipo de página do
     * website_template (B — Institucional, C — Listagem, F — Galeria, G — Contactos…).
     */
    public const LAYOUTS = [
        'standard' => 'Institucional — texto e blocos',
        'sidebar' => 'Institucional — com coluna lateral',
        'news_index' => 'Listagem de notícias',
        'announcements_index' => 'Listagem de comunicados',
        'events_index' => 'Listagem de eventos',
        'agenda' => 'Agenda (eventos por mês)',
        'gallery' => 'Galeria de imagens',
        'multimedia' => 'Multimédia (vídeos)',
        'courses_index' => 'Oferta formativa (cursos)',
        'units_directory' => 'Directório de unidades orgânicas',
        'documents_index' => 'Lista de documentos',
        'partners' => 'Parceiros',
        'contact' => 'Contactos (com formulário)',
    ];

    /** Layouts cujo conteúdo principal é gerado a partir de um módulo do CMS. */
    public const MODULE_LAYOUTS = [
        'news_index', 'announcements_index', 'events_index', 'agenda', 'gallery',
        'multimedia', 'courses_index', 'units_directory', 'documents_index', 'partners', 'contact',
    ];

    protected $fillable = [
        'parent_id', 'title', 'slug', 'layout', 'module_options', 'eyebrow', 'excerpt', 'body', 'blocks',
        'sidebar_title', 'sidebar_items', 'featured_media_id', 'meta_title', 'meta_description',
        'status', 'published_at', 'sort_order', 'show_in_search',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => UnicodeJson::class,
            'module_options' => UnicodeJson::class,
            'is_system' => 'boolean',
            'show_in_search' => 'boolean',
        ];
    }

    protected function slugScope(): array
    {
        return ['parent_id'];
    }

    public function revisionFields(): array
    {
        return [
            'title', 'slug', 'layout', 'module_options', 'eyebrow', 'excerpt', 'body', 'blocks', 'sidebar_title',
            'sidebar_items', 'featured_media_id', 'meta_title', 'meta_description', 'status', 'published_at',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            $parentPath = $page->parent_id ? static::withTrashed()->find($page->parent_id)?->path : null;
            $page->path = $parentPath ? $parentPath.'/'.$page->slug : $page->slug;
        });

        static::saved(function (Page $page) {
            if ($page->wasChanged('path') && $page->getOriginal('path')) {
                if ($page->published_at) {
                    Redirect::remember('/'.$page->getOriginal('path'), '/'.$page->path);
                }
                foreach ($page->children()->withTrashed()->get() as $child) {
                    $child->save();
                }
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order')->orderBy('title');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function documents(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'documentable')->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function url(): string
    {
        return url($this->path);
    }

    /** Antepassados, do topo até ao pai directo (para breadcrumbs). */
    public function ancestors(): array
    {
        $out = [];
        $node = $this->parent;
        $guard = 0;
        while ($node && $guard++ < 20) {
            array_unshift($out, $node);
            $node = $node->parent;
        }

        return $out;
    }

    /** IDs da própria página e de todos os descendentes (evita ciclos na hierarquia). */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        $frontier = [$this->id];
        while ($frontier) {
            $frontier = static::withTrashed()->whereIn('parent_id', $frontier)->pluck('id')->all();
            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }

    public function option(string $key, $default = null)
    {
        return $this->module_options[$key] ?? $default;
    }

    public function isModule(): bool
    {
        return in_array($this->layout, self::MODULE_LAYOUTS, true);
    }

    public function sidebarItemList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $this->sidebar_items))));
    }

    /** Opções de página-pai para selects (indentadas por nível). */
    public static function treeOptions(?array $excludeIds = []): array
    {
        $all = static::orderBy('sort_order')->orderBy('title')->get(['id', 'parent_id', 'title', 'path']);
        $byParent = $all->groupBy(fn ($p) => $p->parent_id ?? 0);
        $out = [];
        $walk = function ($parentId, $depth) use (&$walk, &$out, $byParent, $excludeIds) {
            foreach ($byParent->get($parentId, []) as $p) {
                if (in_array($p->id, $excludeIds ?? [], true)) {
                    continue;
                }
                $out[$p->id] = str_repeat('— ', $depth).$p->title.'  (/'.$p->path.')';
                $walk($p->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return $out;
    }
}
