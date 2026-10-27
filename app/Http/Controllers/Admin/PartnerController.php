<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\ActivityLogger;
use App\Support\SafeUrl;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $items = Partner::query()->with('logo')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.addcslashes((string) $request->query('q'), '%_\\').'%'))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(config('uon.pagination.admin'))->withQueryString();

        return view('admin.partners.index', compact('items'));
    }

    public function create()
    {
        return view('admin.partners.form', ['partner' => new Partner(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $partner = new Partner($this->validated($request));
        $partner->save();
        ActivityLogger::log('partners.created', $partner, 'Adicionou o parceiro "'.$partner->name.'"');

        return redirect()->route('admin.partners.index')->with('success', 'Parceiro adicionado.');
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.form', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $partner->fill($this->validated($request))->save();
        ActivityLogger::log('partners.updated', $partner, 'Editou o parceiro "'.$partner->name.'"');

        return redirect()->route('admin.partners.index')->with('success', 'Parceiro guardado.');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        ActivityLogger::log('partners.deleted', null, 'Eliminou o parceiro "'.$partner->name.'"');

        return redirect()->route('admin.partners.index')->with('success', 'Parceiro eliminado (o logótipo continua na biblioteca).');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'logo_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where(fn ($q) => $q->where('mime_type', 'like', 'image/%'))],
            'url' => ['nullable', 'string', 'max:2048', SafeUrl::rule()],
            'description' => ['nullable', 'string', 'max:250'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        return [...$data, 'name' => strip_tags($data['name']), 'sort_order' => $data['sort_order'] ?? 0, 'is_active' => $request->boolean('is_active')];
    }
}
