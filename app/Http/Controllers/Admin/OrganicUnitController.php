<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\ManagesContent;
use App\Http\Controllers\Controller;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Services\ActivityLogger;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganicUnitController extends Controller
{
    use ManagesContent;

    protected function modelClass(): string
    {
        return OrganicUnit::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.units';
    }

    protected function label(): string
    {
        return 'unidade orgânica';
    }

    protected function permissionModule(): ?string
    {
        return 'units';
    }

    protected function sortable(): array
    {
        return ['updated_at', 'name', 'sort_order'];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', OrganicUnit::class);
        $query = OrganicUnit::query()->with('parent:id,name')->withCount('courses');
        $this->applyListFilters($query, $request, ['name', 'short_name', 'head_name']);
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }
        if (! $request->filled('sort')) {
            $query->reorder()->orderBy('type')->orderBy('sort_order')->orderBy('name');
        }

        return view('admin.units.index', [
            'items' => $query->paginate(config('uon.pagination.admin'))->withQueryString(),
            'authors' => $this->authorOptions(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', OrganicUnit::class);

        return view('admin.units.form', $this->formData(new OrganicUnit(['status' => PublicationStatus::Draft, 'type' => 'faculdade'])));
    }

    public function store(Request $request)
    {
        $this->authorize('create', OrganicUnit::class);
        $unit = new OrganicUnit();
        $this->save($request, $unit);

        return redirect()->route('admin.units.edit', $unit)->with('success', 'Unidade criada.');
    }

    public function edit(OrganicUnit $unit)
    {
        $this->authorize('view', $unit);

        return view('admin.units.form', $this->formData($unit));
    }

    public function update(Request $request, OrganicUnit $unit)
    {
        $this->authorize('update', $unit);
        $this->save($request, $unit);

        return redirect()->route('admin.units.edit', $unit)->with('success', 'Unidade guardada.');
    }

    private function formData(OrganicUnit $unit): array
    {
        return [
            'unit' => $unit,
            'parents' => OrganicUnit::when($unit->exists, fn ($q) => $q->whereKeyNot($unit->id))->orderBy('name')->pluck('name', 'id'),
            'pages' => Page::treeOptions(),
        ];
    }

    private function save(Request $request, OrganicUnit $unit): void
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(OrganicUnit::TYPES))],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('organic_units', 'slug')->ignore($unit->id)],
            'short_name' => ['nullable', 'string', 'max:80'],
            'parent_id' => ['nullable', 'integer', Rule::exists('organic_units', 'id'), Rule::notIn([$unit->id])],
            'summary' => ['nullable', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:100000'],
            'head_title' => ['nullable', 'string', 'max:120'],
            'head_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:60'],
            'location' => ['nullable', 'string', 'max:200'],
            'map_embed_url' => ['nullable', 'string', 'max:2048', 'regex:#^https://(www\.google\.com/maps/embed\?|www\.openstreetmap\.org/export/embed\.html\?)#'],
            'featured_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'page_id' => ['nullable', 'integer', Rule::exists('pages', 'id')],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(array_keys(PublicationStatus::options()))],
            'published_at' => ['nullable', 'date'],
        ], ['map_embed_url.regex' => 'Use um URL de incorporação do Google Maps (https://www.google.com/maps/embed?...) ou do OpenStreetMap.']);

        // Evita ciclos na hierarquia (pai não pode ser descendente).
        if (! empty($data['parent_id']) && $unit->exists) {
            $node = OrganicUnit::find($data['parent_id']);
            $guard = 0;
            while ($node && $guard++ < 20) {
                if ($node->id === $unit->id) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['parent_id' => 'A unidade-mãe não pode ser uma subunidade desta unidade.']);
                }
                $node = $node->parent;
            }
        }

        $created = ! $unit->exists;
        $unit->fill([
            ...collect($data)->except(['slug', 'status', 'published_at', 'description'])->all(),
            'slug' => ($data['slug'] ?? null) ?: ($created ? Str::slug($data['name']) : $unit->slug),
            'description' => HtmlSanitizer::clean($data['description'] ?? null),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        $unit->fill($this->publicationFromRequest($request, $created ? null : $unit));
        if ($created) {
            $unit->author_id = $request->user()->id;
        }
        $unit->updated_by = $request->user()->id;
        $unit->save();
        $this->afterSave($unit, $created);
    }

    public function destroy(Request $request, $id)
    {
        $unit = OrganicUnit::findOrFail($id);
        $this->authorize('delete', $unit);
        if ($unit->children()->exists()) {
            return back()->with('error', 'Esta unidade tem subunidades associadas. Altere-as primeiro.');
        }
        $unit->delete();
        ActivityLogger::log('units.trashed', $unit, 'Moveu para a reciclagem a unidade "'.$unit->name.'"');

        return redirect()->route('admin.units.index')->with('success', 'Unidade movida para a reciclagem.');
    }
}
