<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\ManagesContent;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Document;
use App\Models\News;
use App\Models\Tag;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    use ManagesContent;

    protected function modelClass(): string
    {
        return News::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.news';
    }

    protected function label(): string
    {
        return 'notícia';
    }

    protected function permissionModule(): ?string
    {
        return 'news';
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', News::class);
        $query = News::query()->with(['category:id,name', 'author:id,name']);
        $this->applyListFilters($query, $request, ['title', 'excerpt']);
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }
        if ($request->filled('category')) {
            $query->where('category_id', (int) $request->query('category'));
        }
        if ($request->filled('featured')) {
            $query->where('is_featured', true);
        }

        return view('admin.news.index', [
            'items' => $query->paginate(config('uon.pagination.admin'))->withQueryString(),
            'categories' => Category::ofType('news')->pluck('name', 'id'),
            'authors' => $this->authorOptions(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', News::class);
        $news = new News([
            'type' => $request->query('type') === 'announcement' ? 'announcement' : 'news',
            'status' => PublicationStatus::Draft,
        ]);

        return view('admin.news.form', $this->formData($news));
    }

    public function store(Request $request)
    {
        $this->authorize('create', News::class);
        $news = new News();
        $this->save($request, $news);

        return redirect()->route('admin.news.edit', $news)->with('success', ucfirst($news->typeLabel()).' criada.');
    }

    public function edit(News $news)
    {
        $this->authorize('view', $news);

        return view('admin.news.form', $this->formData($news));
    }

    public function update(Request $request, News $news)
    {
        $this->authorize('update', $news);
        $this->save($request, $news);

        return redirect()->route('admin.news.edit', $news)->with('success', ucfirst($news->typeLabel()).' guardada.');
    }

    private function formData(News $news): array
    {
        return [
            'news' => $news,
            'categories' => Category::ofType('news')->pluck('name', 'id'),
            'documents' => Document::orderBy('title')->get(['id', 'title']),
            'tagString' => $news->exists ? $news->tags->pluck('name')->implode(', ') : '',
        ];
    }

    private function save(Request $request, News $news): void
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(News::TYPES))],
            'title' => ['required', 'string', 'max:220'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('news', 'slug')->ignore($news->id)],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'body' => ['nullable', 'string', 'max:500000'],
            'featured_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'news')],
            'tags' => ['nullable', 'string', 'max:500'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'status' => ['required', Rule::in(array_keys(PublicationStatus::options()))],
            'published_at' => ['nullable', 'date'],
            'document_ids' => ['nullable', 'array', 'max:30'],
            'document_ids.*' => ['integer', Rule::exists('documents', 'id')],
        ], ['slug.regex' => 'O endereço (slug) só pode conter letras minúsculas sem acentos, números e hífenes.', 'slug.unique' => 'Já existe outro conteúdo com este endereço.']);

        DB::transaction(function () use ($request, $news, $data) {
            $created = ! $news->exists;
            $news->fill([
                'type' => $data['type'],
                'title' => $data['title'],
                'slug' => ($data['slug'] ?? null) ?: ($created ? Str::slug($data['title']) : $news->slug),
                'excerpt' => $data['excerpt'] ?? null,
                'body' => HtmlSanitizer::clean($data['body'] ?? null),
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'author_name' => $data['author_name'] ?? null,
                'is_featured' => $request->boolean('is_featured') && $request->user()->can('publish', $news->exists ? $news : News::class),
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]);
            $news->fill($this->publicationFromRequest($request, $created ? null : $news));
            if ($created) {
                $news->author_id = $request->user()->id;
            }
            $news->updated_by = $request->user()->id;
            $news->save();

            $news->tags()->sync(Tag::idsFromString($data['tags'] ?? ''));
            $sync = [];
            foreach (array_values($data['document_ids'] ?? []) as $i => $docId) {
                $sync[$docId] = ['sort_order' => $i];
            }
            $news->documents()->sync($sync);

            $this->afterSave($news, $created);
        });
    }

    public function duplicate(Request $request, News $news)
    {
        $this->authorize('view', $news);
        $this->authorize('create', News::class);
        $copy = DB::transaction(function () use ($request, $news) {
            $copy = $news->replicate(['slug', 'status', 'published_at', 'author_id', 'updated_by', 'deleted_at', 'is_demo', 'is_featured']);
            $copy->title = $news->title.' (cópia)';
            $copy->slug = $copy->uniqueSlug(Str::slug($news->slug.'-copia'));
            $copy->status = PublicationStatus::Draft;
            $copy->author_id = $request->user()->id;
            $copy->updated_by = $request->user()->id;
            $copy->save();
            $copy->tags()->sync($news->tags->pluck('id'));
            $copy->recordRevision('duplicated');

            return $copy;
        });
        $this->log('news.duplicated', $copy, 'Duplicou');

        return redirect()->route('admin.news.edit', $copy)->with('success', 'Duplicado como rascunho.');
    }

    protected function beforeForceDelete(Model $model): void
    {
        $model->tags()->detach();
        $model->documents()->detach();
    }
}
