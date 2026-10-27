<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\ManagesContent;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Degree;
use App\Models\Document;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Support\Blocks;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    use ManagesContent;

    protected function modelClass(): string
    {
        return Page::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.pages';
    }

    protected function label(): string
    {
        return 'página';
    }

    protected function permissionModule(): ?string
    {
        return 'pages';
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Page::class);
        $query = Page::query()->with(['parent:id,title', 'editor:id,name']);
        $this->applyListFilters($query, $request, ['title', 'path', 'excerpt']);
        if ($request->filled('layout')) {
            $query->where('layout', $request->query('layout'));
        }
        if ($request->filled('parent')) {
            $request->query('parent') === 'root' ? $query->whereNull('parent_id') : $query->where('parent_id', (int) $request->query('parent'));
        }
        if (! $request->filled('sort')) {
            $query->reorder()->orderBy('path');
        }

        return view('admin.pages.index', [
            'items' => $query->paginate(config('uon.pagination.admin'))->withQueryString(),
            'authors' => $this->authorOptions(),
            'roots' => Page::whereNull('parent_id')->orderBy('sort_order')->pluck('title', 'id'),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Page::class);
        $page = new Page(['layout' => 'standard', 'status' => PublicationStatus::Draft, 'parent_id' => $request->integer('parent') ?: null, 'show_in_search' => true]);

        return view('admin.pages.form', $this->formData($page));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Page::class);
        $page = new Page();
        $this->save($request, $page);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Página criada.');
    }

    public function edit(Page $page)
    {
        $this->authorize('view', $page);

        return view('admin.pages.form', $this->formData($page));
    }

    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);
        $this->save($request, $page);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Página guardada.');
    }

    private function formData(Page $page): array
    {
        $exclude = $page->exists ? $page->descendantIds() : [];

        return [
            'page' => $page,
            'parents' => Page::treeOptions($exclude),
            'blockSchema' => Blocks::editorSchema(),
            'documentCategories' => Category::ofType('document')->pluck('name', 'id'),
            'degrees' => Degree::orderBy('sort_order')->pluck('name', 'slug'),
            'unitTypes' => ['academic' => 'Faculdades, institutos, escolas e centros'] + OrganicUnit::TYPES,
            'documents' => Document::orderBy('title')->get(['id', 'title']),
            'menuUsage' => $page->exists ? MenuItem::with('menu:id,name')->where('page_id', $page->id)->get() : collect(),
        ];
    }

    private function save(Request $request, Page $page): void
    {
        $isSystem = $page->exists && $page->is_system;
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [$isSystem ? 'nullable' : 'nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'parent_id' => ['nullable', 'integer', Rule::exists('pages', 'id')->whereNull('deleted_at')],
            'layout' => ['required', Rule::in(array_keys(Page::LAYOUTS))],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:500000'],
            'blocks_json' => ['nullable', 'string', 'max:500000'],
            'sidebar_title' => ['nullable', 'string', 'max:120'],
            'sidebar_items' => ['nullable', 'string', 'max:3000'],
            'featured_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'status' => ['required', Rule::in(array_keys(PublicationStatus::options()))],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'show_in_search' => ['nullable', 'boolean'],
            'module_degree' => ['nullable', Rule::exists('degrees', 'slug')],
            'module_category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'document')],
            'module_unit_type' => ['nullable', Rule::in(array_merge(['academic'], array_keys(OrganicUnit::TYPES)))],
            'document_ids' => ['nullable', 'array', 'max:50'],
            'document_ids.*' => ['integer', Rule::exists('documents', 'id')],
        ], [
            'slug.regex' => 'O endereço (slug) só pode conter letras minúsculas sem acentos, números e hífenes.',
        ]);

        // A hierarquia não pode conter ciclos.
        if ($page->exists && ! empty($data['parent_id']) && in_array((int) $data['parent_id'], $page->descendantIds(), true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['parent_id' => 'Uma página não pode ser colocada dentro de si própria ou de uma subpágina.']);
        }

        $slug = Str::slug(($data['slug'] ?? null) ?: $data['title']);
        if ($isSystem) {
            // Páginas estruturais do template: endereço e hierarquia fixos (usados por menus e módulos).
            $slug = $page->slug;
            $data['parent_id'] = $page->parent_id;
        }
        $parentPath = ! empty($data['parent_id']) ? Page::find($data['parent_id'])?->path : null;
        $path = $parentPath ? $parentPath.'/'.$slug : $slug;
        if (in_array(explode('/', $path)[0], ['admin', 'pesquisa', 'sitemap.xml', 'robots.txt', 'documentos'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slug' => 'Este endereço está reservado pelo sistema.']);
        }
        if (Page::withTrashed()->where('path', $path)->when($page->exists, fn ($q) => $q->whereKeyNot($page->id))->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slug' => 'Já existe uma página com o endereço /'.$path.' (incluindo na reciclagem).']);
        }

        $options = match ($data['layout']) {
            'courses_index' => array_filter(['degree' => $data['module_degree'] ?? null]),
            'documents_index' => array_filter(['category_id' => isset($data['module_category_id']) ? (int) $data['module_category_id'] : null]),
            'units_directory' => ['unit_type' => $data['module_unit_type'] ?? 'academic'],
            default => [],
        };

        DB::transaction(function () use ($request, $page, $data, $slug, $options) {
            $created = ! $page->exists;
            $page->fill([
                'title' => $data['title'],
                'slug' => $slug,
                'parent_id' => $data['parent_id'] ?? null,
                'layout' => $data['layout'],
                'module_options' => $options ?: null,
                'eyebrow' => $data['eyebrow'] ?? null,
                'excerpt' => $data['excerpt'] ?? null,
                'body' => HtmlSanitizer::clean($data['body'] ?? null),
                'blocks' => Blocks::sanitize($data['blocks_json'] ?? '[]') ?: null,
                'sidebar_title' => $data['sidebar_title'] ?? null,
                'sidebar_items' => isset($data['sidebar_items']) ? strip_tags($data['sidebar_items']) : null,
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'show_in_search' => $request->boolean('show_in_search'),
            ]);
            $page->fill($this->publicationFromRequest($request, $created ? null : $page));
            if ($created) {
                $page->author_id = $request->user()->id;
            }
            $page->updated_by = $request->user()->id;
            $page->save();

            $sync = [];
            foreach (array_values($data['document_ids'] ?? []) as $i => $docId) {
                $sync[$docId] = ['sort_order' => $i];
            }
            $page->documents()->sync($sync);

            $this->afterSave($page, $created);
        });
    }

    public function duplicate(Request $request, Page $page)
    {
        $this->authorize('view', $page);
        $this->authorize('create', Page::class);

        $copy = DB::transaction(function () use ($request, $page) {
            $copy = $page->replicate(['path', 'slug', 'status', 'published_at', 'is_system', 'author_id', 'updated_by', 'deleted_at']);
            $copy->title = $page->title.' (cópia)';
            $copy->slug = $copy->uniqueSlug(Str::slug($page->slug.'-copia'));
            $copy->status = PublicationStatus::Draft;
            $copy->published_at = null;
            $copy->is_system = false;
            $copy->author_id = $request->user()->id;
            $copy->updated_by = $request->user()->id;
            $copy->save();
            $copy->documents()->sync($page->documents->mapWithKeys(fn ($d) => [$d->id => ['sort_order' => $d->pivot->sort_order]])->all());
            $copy->recordRevision('duplicated');

            return $copy;
        });
        $this->log('pages.duplicated', $copy, 'Duplicou');

        return redirect()->route('admin.pages.edit', $copy)->with('success', 'Página duplicada como rascunho.');
    }

    public function destroy(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        $this->authorize('delete', $page);
        if ($page->is_system) {
            return back()->with('error', 'Esta página faz parte da estrutura do website (menus e módulos) e não pode ser eliminada. Pode arquivá-la ou despublicá-la.');
        }
        if ($page->children()->exists()) {
            return back()->with('error', 'A página tem subpáginas. Mova ou elimine primeiro as subpáginas.');
        }

        $page->delete();
        $this->log('pages.trashed', $page, 'Moveu para a reciclagem');

        return redirect()->route('admin.pages.index')->with('success', 'Página movida para a reciclagem.');
    }

    protected function beforeForceDelete(Model $model): void
    {
        $model->documents()->detach();
        MenuItem::where('page_id', $model->id)->update(['page_id' => null, 'link_type' => 'none', 'is_active' => false]);
    }

    protected function sortable(): array
    {
        return ['updated_at', 'published_at', 'title', 'path', 'sort_order'];
    }
}
