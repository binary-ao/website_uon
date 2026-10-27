<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use App\Services\ActivityLogger;
use App\Support\SafeUrl;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RedirectController extends Controller
{
    public function index(Request $request)
    {
        $items = Redirect::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%'.addcslashes((string) $request->query('q'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('from_path', 'like', $like)->orWhere('to_url', 'like', $like));
            })
            ->latest('id')->paginate(config('uon.pagination.admin'))->withQueryString();

        return view('admin.redirects.index', compact('items'));
    }

    public function create()
    {
        return view('admin.redirects.form', ['redirect' => new Redirect(['status_code' => 301, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $redirect = new Redirect($this->validated($request));
        $redirect->created_by = $request->user()->id;
        $redirect->save();
        ActivityLogger::log('redirects.created', $redirect, 'Criou o redireccionamento '.$redirect->from_path.' → '.$redirect->to_url);

        return redirect()->route('admin.redirects.index')->with('success', 'Redireccionamento criado.');
    }

    public function edit(Redirect $redirect)
    {
        return view('admin.redirects.form', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect)
    {
        $redirect->fill($this->validated($request, $redirect))->save();
        ActivityLogger::log('redirects.updated', $redirect, 'Editou o redireccionamento '.$redirect->from_path);

        return redirect()->route('admin.redirects.index')->with('success', 'Redireccionamento guardado.');
    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();
        ActivityLogger::log('redirects.deleted', null, 'Eliminou o redireccionamento '.$redirect->from_path);

        return back()->with('success', 'Redireccionamento eliminado.');
    }

    private function validated(Request $request, ?Redirect $redirect = null): array
    {
        $request->merge(['from_path' => Redirect::normalizePath((string) $request->input('from_path'))]);
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:255', 'regex:#^/[^\s]*$#', 'not_in:/', Rule::unique('redirects', 'from_path')->ignore($redirect?->id)],
            'to_url' => ['required', 'string', 'max:2048', SafeUrl::rule()],
            'status_code' => ['required', Rule::in([301, 302])],
        ], [
            'from_path.not_in' => 'Não é possível redireccionar a página inicial.',
            'from_path.unique' => 'Já existe um redireccionamento para este endereço.',
        ], ['from_path' => 'endereço de origem', 'to_url' => 'destino']);

        if (str_starts_with($data['from_path'], '/admin')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['from_path' => 'Não é possível redireccionar endereços da administração.']);
        }
        if (Redirect::normalizePath($data['to_url']) === $data['from_path']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['to_url' => 'O destino não pode ser igual à origem.']);
        }

        return [...$data, 'is_active' => $request->boolean('is_active')];
    }
}
