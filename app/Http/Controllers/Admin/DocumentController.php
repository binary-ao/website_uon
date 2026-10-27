<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\ManagesContent;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Services\ActivityLogger;
use App\Services\DocumentStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    use ManagesContent;

    public function __construct(private DocumentStorage $storage)
    {
    }

    protected function modelClass(): string
    {
        return Document::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.documents';
    }

    protected function label(): string
    {
        return 'documento';
    }

    protected function permissionModule(): ?string
    {
        return 'documents';
    }

    protected function sortable(): array
    {
        return ['updated_at', 'document_date', 'title', 'published_at'];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Document::class);
        $query = Document::query()->with(['category:id,name', 'currentVersion', 'author:id,name']);
        $this->applyListFilters($query, $request, ['title', 'description']);
        if ($request->filled('category')) {
            $query->where('category_id', (int) $request->query('category'));
        }
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->query('visibility'));
        }

        return view('admin.documents.index', [
            'items' => $query->paginate(config('uon.pagination.admin'))->withQueryString(),
            'categories' => Category::ofType('document')->pluck('name', 'id'),
            'authors' => $this->authorOptions(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Document::class);

        return view('admin.documents.form', [
            'document' => new Document(['status' => PublicationStatus::Draft, 'visibility' => 'public']),
            'categories' => Category::ofType('document')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Document::class);
        $document = new Document();
        $this->save($request, $document, true);

        return redirect()->route('admin.documents.edit', $document)->with('success', 'Documento criado.');
    }

    public function edit(Document $document)
    {
        $this->authorize('view', $document);
        $document->load(['versions.uploader:id,name']);

        return view('admin.documents.form', [
            'document' => $document,
            'categories' => Category::ofType('document')->pluck('name', 'id'),
            'usage' => DB::table('documentables')->where('document_id', $document->id)->get(),
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);
        $this->save($request, $document, false);

        return redirect()->route('admin.documents.edit', $document)->with('success', 'Documento guardado.');
    }

    private function save(Request $request, Document $document, bool $requireFile): void
    {
        $maxKb = max(config('uon.uploads.max_document_kb'), config('uon.uploads.max_image_kb'));
        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('documents', 'slug')->ignore($document->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'document')],
            'document_date' => ['nullable', 'date'],
            'visibility' => ['required', Rule::in(array_keys(Document::VISIBILITY))],
            'status' => ['required', Rule::in(array_keys(PublicationStatus::options()))],
            'published_at' => ['nullable', 'date'],
            'file' => [$requireFile ? 'required' : 'nullable', 'file', 'max:'.$maxKb],
            'version_notes' => ['nullable', 'string', 'max:250'],
        ], [], ['file' => 'ficheiro']);

        DB::transaction(function () use ($request, $document, $data) {
            $created = ! $document->exists;
            $document->fill([
                'title' => $data['title'],
                'slug' => ($data['slug'] ?? null) ?: ($created ? Str::slug($data['title']) : $document->slug),
                'description' => isset($data['description']) ? strip_tags($data['description']) : null,
                'category_id' => $data['category_id'] ?? null,
                'document_date' => $data['document_date'] ?? null,
                'visibility' => $data['visibility'],
            ]);
            $document->fill($this->publicationFromRequest($request, $created ? null : $document));
            if ($created) {
                $document->author_id = $request->user()->id;
            }
            $document->updated_by = $request->user()->id;
            $document->save();

            if ($request->hasFile('file')) {
                $version = $this->storage->addVersion($document, $request->file('file'), $data['version_notes'] ?? null);
                ActivityLogger::log('documents.version_added', $document, 'Carregou a versão '.$version->version.' do documento "'.$document->title.'"');
            }
            $this->afterSave($document, $created);
        });
    }

    public function addVersion(Request $request, Document $document)
    {
        $this->authorize('update', $document);
        $maxKb = max(config('uon.uploads.max_document_kb'), config('uon.uploads.max_image_kb'));
        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.$maxKb],
            'version_notes' => ['nullable', 'string', 'max:250'],
        ], [], ['file' => 'ficheiro']);
        $version = $this->storage->addVersion($document, $request->file('file'), $data['version_notes'] ?? null);
        $document->forceFill(['updated_by' => $request->user()->id])->save();
        ActivityLogger::log('documents.version_added', $document, 'Substituiu o ficheiro do documento "'.$document->title.'" (versão '.$version->version.')');

        return back()->with('success', 'Nova versão carregada. As versões anteriores foram preservadas.');
    }

    public function makeCurrent(Request $request, Document $document, DocumentVersion $version)
    {
        $this->authorize('update', $document);
        abort_unless($version->document_id === $document->id, 404);
        $document->forceFill(['current_version_id' => $version->id, 'updated_by' => $request->user()->id])->save();
        ActivityLogger::log('documents.version_current', $document, 'Repôs a versão '.$version->version.' do documento "'.$document->title.'"');

        return back()->with('success', 'A versão '.$version->version.' passou a ser a versão em vigor.');
    }

    protected function beforeForceDelete(Model $model): void
    {
        $model->forceFill(['current_version_id' => null])->saveQuietly();
        DB::table('documentables')->where('document_id', $model->id)->delete();
        $this->storage->purgeFiles($model);
    }
}
