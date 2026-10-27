<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $items = Tag::withCount('news')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.addcslashes((string) $request->query('q'), '%_\\').'%'))
            ->orderBy('name')->paginate(50)->withQueryString();

        return view('admin.tags.index', compact('items'));
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60', Rule::unique('tags')->ignore($tag->id)]]);
        $slug = Str::slug($data['name']);
        if (Tag::where('slug', $slug)->whereKeyNot($tag->id)->exists()) {
            return back()->with('error', 'Já existe uma etiqueta equivalente.');
        }
        $tag->update(['name' => strip_tags($data['name']), 'slug' => $slug]);
        ActivityLogger::log('tags.updated', $tag, 'Renomeou a etiqueta para "'.$tag->name.'"');

        return back()->with('success', 'Etiqueta actualizada.');
    }

    public function destroy(Tag $tag)
    {
        $tag->news()->detach();
        $tag->delete();
        ActivityLogger::log('tags.deleted', null, 'Eliminou a etiqueta "'.$tag->name.'"');

        return back()->with('success', 'Etiqueta eliminada e removida das notícias.');
    }
}
