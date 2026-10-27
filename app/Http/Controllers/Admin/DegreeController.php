<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Degree;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DegreeController extends Controller
{
    public function index()
    {
        return view('admin.degrees.index', ['items' => Degree::withCount('courses')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.degrees.form', ['degree' => new Degree()]);
    }

    public function store(Request $request)
    {
        $degree = Degree::create($this->validated($request));
        ActivityLogger::log('degrees.created', $degree, 'Criou o grau "'.$degree->name.'"');

        return redirect()->route('admin.degrees.index')->with('success', 'Grau criado.');
    }

    public function edit(Degree $degree)
    {
        return view('admin.degrees.form', compact('degree'));
    }

    public function update(Request $request, Degree $degree)
    {
        $degree->fill($this->validated($request, $degree))->save();
        ActivityLogger::log('degrees.updated', $degree, 'Editou o grau "'.$degree->name.'"');

        return redirect()->route('admin.degrees.index')->with('success', 'Grau guardado. Nota: páginas que filtram por este grau usam o endereço (slug).');
    }

    public function destroy(Degree $degree)
    {
        if ($degree->courses()->withTrashed()->exists()) {
            return back()->with('error', 'Existem cursos associados a este grau.');
        }
        $degree->delete();
        ActivityLogger::log('degrees.deleted', null, 'Eliminou o grau "'.$degree->name.'"');

        return back()->with('success', 'Grau eliminado.');
    }

    private function validated(Request $request, ?Degree $degree = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('degrees')->ignore($degree?->id)],
            'plural_name' => ['nullable', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('degrees')->ignore($degree?->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        return [...$data, 'slug' => ($data['slug'] ?? null) ?: Str::slug($data['name']), 'sort_order' => $data['sort_order'] ?? 0];
    }
}
