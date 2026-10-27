<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Services\ActivityLogger;
use App\Support\SafeUrl;
use Illuminate\Http\Request;

/** Avisos e anúncios da faixa de destaques (notice-strip) da página inicial. */
class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $items = Notice::query()
            ->when($request->filled('q'), fn ($q) => $q->where('text', 'like', '%'.addcslashes((string) $request->query('q'), '%_\\').'%'))
            ->when($request->query('state') === 'current', fn ($q) => $q->current())
            ->orderBy('sort_order')->orderByDesc('id')
            ->paginate(config('uon.pagination.admin'))->withQueryString();

        return view('admin.notices.index', compact('items'));
    }

    public function create()
    {
        return view('admin.notices.form', ['notice' => new Notice(['is_active' => true, 'icon' => 'bi-megaphone'])]);
    }

    public function store(Request $request)
    {
        $notice = new Notice($this->validated($request));
        $notice->save();
        ActivityLogger::log('notices.created', $notice, 'Criou o aviso "'.$notice->text.'"');

        return redirect()->route('admin.notices.index')->with('success', 'Aviso criado.');
    }

    public function edit(Notice $notice)
    {
        return view('admin.notices.form', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $notice->fill($this->validated($request))->save();
        ActivityLogger::log('notices.updated', $notice, 'Editou o aviso "'.$notice->text.'"');

        return redirect()->route('admin.notices.index')->with('success', 'Aviso guardado.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        ActivityLogger::log('notices.deleted', null, 'Eliminou o aviso "'.$notice->text.'"');

        return redirect()->route('admin.notices.index')->with('success', 'Aviso eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:200'],
            'icon' => ['nullable', 'regex:/^bi-[a-z0-9-]{1,40}$/'],
            'url' => ['nullable', 'string', 'max:2048', SafeUrl::rule()],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ], ['icon.regex' => 'Ícone inválido (ex.: bi-megaphone).'], ['ends_at' => 'fim', 'starts_at' => 'início']);

        return [
            ...$data,
            'text' => strip_tags($data['text']),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
