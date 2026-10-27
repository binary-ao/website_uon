<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\ManagesContent;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Support\VideoEmbed;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GalleryItemController extends Controller
{
    use ManagesContent;

    protected function modelClass(): string
    {
        return GalleryItem::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.gallery';
    }

    protected function label(): string
    {
        return 'item da galeria';
    }

    protected function permissionModule(): ?string
    {
        return 'gallery';
    }

    protected function sortable(): array
    {
        return ['updated_at', 'title', 'sort_order', 'published_at'];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', GalleryItem::class);
        $query = GalleryItem::query()->with(['media', 'category:id,name']);
        $request->query->remove('trashed');
        $this->applyListFilters($query, $request, ['title', 'caption']);
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }
        if (! $request->filled('sort')) {
            $query->reorder()->orderBy('sort_order')->orderByDesc('updated_at');
        }

        return view('admin.gallery.index', [
            'items' => $query->paginate(24)->withQueryString(),
            'authors' => $this->authorOptions(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', GalleryItem::class);

        return view('admin.gallery.form', [
            'item' => new GalleryItem(['type' => $request->query('type') === 'video' ? 'video' : 'image', 'status' => PublicationStatus::Draft]),
            'categories' => Category::ofType('gallery')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', GalleryItem::class);
        $item = new GalleryItem();
        $this->save($request, $item);

        return redirect()->route('admin.gallery.index')->with('success', 'Item adicionado à galeria.');
    }

    public function edit(GalleryItem $gallery)
    {
        $this->authorize('view', $gallery);

        return view('admin.gallery.form', ['item' => $gallery, 'categories' => Category::ofType('gallery')->pluck('name', 'id')]);
    }

    public function update(Request $request, GalleryItem $gallery)
    {
        $this->authorize('update', $gallery);
        $this->save($request, $gallery);

        return redirect()->route('admin.gallery.edit', $gallery)->with('success', 'Item guardado.');
    }

    private function save(Request $request, GalleryItem $item): void
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(GalleryItem::TYPES))],
            'title' => ['required', 'string', 'max:200'],
            'caption' => ['nullable', 'string', 'max:250'],
            'media_id' => ['nullable', 'required_if:type,image', 'integer', Rule::exists('media', 'id')],
            'video_url' => ['nullable', 'required_if:type,video', 'string', 'max:2048', VideoEmbed::rule()],
            'duration_label' => ['nullable', 'string', 'max:40'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'gallery')],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(array_keys(PublicationStatus::options()))],
            'published_at' => ['nullable', 'date'],
        ], ['media_id.required_if' => 'Escolha uma imagem.', 'video_url.required_if' => 'Indique o URL do vídeo.']);

        if ($data['type'] === 'image' && ! Media::find($data['media_id'])?->isImage()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['media_id' => 'O ficheiro escolhido não é uma imagem.']);
        }

        $created = ! $item->exists;
        $item->fill([
            'type' => $data['type'],
            'title' => $data['title'],
            'caption' => $data['caption'] ?? null,
            'media_id' => $data['type'] === 'image' ? $data['media_id'] : null,
            'video_url' => $data['type'] === 'video' ? $data['video_url'] : null,
            'duration_label' => $data['duration_label'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        $item->fill($this->publicationFromRequest($request, $created ? null : $item));
        if ($created) {
            $item->author_id = $request->user()->id;
        }
        $item->save();
        $this->afterSave($item, $created);
    }

    public function destroy(Request $request, $id)
    {
        $item = GalleryItem::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();
        ActivityLogger::log('gallery.deleted', null, 'Removeu da galeria "'.$item->title.'"');

        return redirect()->route('admin.gallery.index')->with('success', 'Item removido da galeria (o ficheiro continua na biblioteca multimédia).');
    }

    /** A galeria não usa reciclagem: a remoção é directa (o ficheiro permanece na biblioteca). */
    public function restore(Request $request, $id)
    {
        abort(404);
    }

    public function forceDelete(Request $request, $id)
    {
        abort(404);
    }
}
