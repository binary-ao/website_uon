<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Services\MediaService;
use App\Services\MediaUsage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    public function __construct(private MediaService $media)
    {
    }

    private function filtered(Request $request)
    {
        $q = trim((string) $request->query('q'));

        return Media::query()->with('uploader:id,name')
            ->when($q !== '', function ($w) use ($q) {
                $like = '%'.addcslashes($q, '%_\\').'%';
                $w->where(fn ($x) => $x->where('original_name', 'like', $like)->orWhere('title', 'like', $like)->orWhere('alt_text', 'like', $like));
            })
            ->when($request->query('type') === 'image', fn ($w) => $w->where('mime_type', 'like', 'image/%'))
            ->when($request->query('type') === 'document', fn ($w) => $w->where('mime_type', 'not like', 'image/%'))
            ->when($request->filled('uploader'), fn ($w) => $w->where('uploaded_by', (int) $request->query('uploader')))
            ->latest('id');
    }

    public function index(Request $request)
    {
        return view('admin.media.index', [
            'items' => $this->filtered($request)->paginate(24)->withQueryString(),
            'uploaders' => \App\Models\User::whereIn('id', Media::select('uploaded_by'))->pluck('name', 'id'),
        ]);
    }

    /** Lista JSON para o selector de ficheiros (editor, campos de imagem, blocos). */
    public function picker(Request $request)
    {
        $page = $this->filtered($request)->paginate(18)->withQueryString();

        return response()->json([
            'data' => $page->getCollection()->map(fn (Media $m) => $this->present($m))->values(),
            'next' => $page->nextPageUrl(),
            'prev' => $page->previousPageUrl(),
            'total' => $page->total(),
        ]);
    }

    private function present(Media $m): array
    {
        return [
            'id' => $m->id,
            'url' => $m->url(),
            'thumb' => $m->isImage() ? $m->url('md') : null,
            'title' => $m->title,
            'alt' => $m->alt_text,
            'name' => $m->original_name,
            'is_image' => $m->isImage(),
            'size' => $m->humanSize(),
            'icon' => $m->icon(),
            'dimensions' => $m->width ? $m->width.'×'.$m->height : null,
        ];
    }

    public function store(Request $request)
    {
        $maxKb = max(config('uon.uploads.max_document_kb'), config('uon.uploads.max_image_kb'));
        $request->validate([
            'file' => ['required', 'file', 'max:'.$maxKb],
            'title' => ['nullable', 'string', 'max:200'],
            'alt_text' => ['nullable', 'string', 'max:250'],
            'kind' => ['nullable', 'in:image,document'],
        ], [], ['file' => 'ficheiro']);

        $kinds = $request->input('kind') ? [$request->input('kind')] : ['image', 'document'];
        try {
            $media = $this->media->store($request->file('file'), $request->only(['title', 'alt_text']), $kinds);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
            }
            throw $e;
        }

        if ($request->expectsJson()) {
            // Formato aceite pelo TinyMCE (location) e pelo selector do CMS.
            return response()->json(['location' => $media->url(), 'media' => $this->present($media)], 201);
        }

        return back()->with('success', 'Ficheiro carregado: '.$media->original_name);
    }

    public function edit(Media $media)
    {
        return view('admin.media.edit', ['media' => $media, 'usages' => MediaUsage::find($media)]);
    }

    public function update(Request $request, Media $media)
    {
        if ((int) $media->uploaded_by !== (int) $request->user()->id) {
            abort_unless($request->user()->can('media.manage'), 403);
        }
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:200'],
            'alt_text' => ['nullable', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $media->fill(array_map(fn ($v) => $v === null ? null : strip_tags($v), $data))->save();
        ActivityLogger::log('media.updated', $media, 'Editou os dados do ficheiro "'.$media->original_name.'"');

        return back()->with('success', 'Dados do ficheiro guardados.');
    }

    public function destroy(Media $media)
    {
        $usages = $this->media->delete($media);
        if ($usages) {
            return back()->with('error', 'O ficheiro não foi eliminado porque está a ser utilizado: '.implode('; ', array_slice($usages, 0, 5)).(count($usages) > 5 ? '…' : '').'.');
        }

        return redirect()->route('admin.media.index')->with('success', 'Ficheiro eliminado.');
    }
}
