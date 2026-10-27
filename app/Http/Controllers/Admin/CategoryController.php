<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $type = array_key_exists((string) $request->query('type'), Category::TYPES) ? $request->query('type') : null;
        $items = Category::query()->when($type, fn ($q) => $q->where('type', $type))
            ->orderBy('type')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.categories.index', ['items' => $items, 'type' => $type]);
    }

    public function create(Request $request)
    {
        return view('admin.categories.form', ['category' => new Category(['type' => $request->query('type', 'news')])]);
    }

    public function store(Request $request)
    {
        $category = new Category($this->validated($request));
        $category->save();
        ActivityLogger::log('categories.created', $category, 'Criou a categoria "'.$category->name.'"');

        return redirect()->route('admin.categories.index', ['type' => $category->type])->with('success', 'Categoria criada.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);
        if ($data['type'] !== $category->type && $category->usageCount() > 0) {
            return back()->withErrors(['type' => 'Não é possível alterar o tipo de uma categoria em uso.'])->withInput();
        }
        $category->fill($data)->save();
        ActivityLogger::log('categories.updated', $category, 'Editou a categoria "'.$category->name.'"');

        return redirect()->route('admin.categories.index', ['type' => $category->type])->with('success', 'Categoria guardada.');
    }

    public function destroy(Category $category)
    {
        if (($n = $category->usageCount()) > 0) {
            return back()->with('error', "A categoria \"{$category->name}\" está associada a {$n} conteúdo(s) e não pode ser eliminada.");
        }
        $category->delete();
        ActivityLogger::log('categories.deleted', null, 'Eliminou a categoria "'.$category->name.'"');

        return back()->with('success', 'Categoria eliminada.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $type = (string) $request->input('type');
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Category::TYPES))],
            'name' => ['required', 'string', 'max:100', Rule::unique('categories')->where('type', $type)->ignore($category?->id)],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories')->where('type', $type)->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:250'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ], ['name.unique' => 'Já existe uma categoria com este nome neste tipo de conteúdo.', 'slug.unique' => 'Já existe uma categoria com este endereço.']);

        return [...$data, 'name' => strip_tags($data['name']), 'slug' => ($data['slug'] ?? null) ?: Str::slug($data['name']), 'sort_order' => $data['sort_order'] ?? 0];
    }
}
