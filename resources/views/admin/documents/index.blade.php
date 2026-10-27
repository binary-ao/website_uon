@extends('layouts.admin')
@section('title', 'Documentos')

@section('content')
<x-admin.title title="Documentos" subtitle="Ficheiros guardados fora da pasta pública e servidos por rota autorizada.">
  @can('create', \App\Models\Document::class)<a href="{{ route('admin.documents.create') }}" class="btn btn-uon"><i class="bi bi-file-earmark-arrow-up"></i> Novo documento</a>@endcan
</x-admin.title>

<x-admin.list-filters :authors="$authors" placeholder="Título ou descrição">
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_cat">Categoria</label>
    <select class="form-select" id="flt_cat" name="category">
      <option value="">Todas</option>
      @foreach ($categories as $id => $n)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $n }}</option>@endforeach
    </select>
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_vis">Visibilidade</label>
    <select class="form-select" id="flt_vis" name="visibility">
      <option value="">Todas</option>
      @foreach (\App\Models\Document::VISIBILITY as $k => $l)<option value="{{ $k }}" @selected(request('visibility') === $k)>{{ $k === 'public' ? 'Pública' : 'Restrita' }}</option>@endforeach
    </select>
  </div>
</x-admin.list-filters>

<div class="d-box">
  <div class="d-box-head"><h2>{{ $items->total() }} documentos</h2></div>
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-folder2-open"></i>Nenhum documento encontrado.</div>
  @else
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th><x-admin.sort field="title" label="Título" /></th><th>Categoria</th><th>Ficheiro</th><th>Visibilidade</th><th>Estado</th><th><x-admin.sort field="document_date" label="Data" /></th><th class="text-end">Acções</th></tr></thead>
      <tbody>
        @foreach ($items as $d)
          <tr>
            <td><a class="row-title" href="{{ route('admin.documents.edit', $d) }}">{{ $d->title }}</a>@if ($d->is_demo)<span class="u-badge demo ms-1">Exemplo</span>@endif</td>
            <td><small>{{ $d->category?->name ?? '—' }}</small></td>
            <td><small>@if ($d->currentVersion){{ strtoupper($d->currentVersion->extension) }} · {{ $d->currentVersion->humanSize() }} · v{{ $d->currentVersion->version }}@else<span class="text-danger">Sem ficheiro</span>@endif</small></td>
            <td>@if ($d->isPublic())<span class="s-badge info">Pública</span>@else<span class="s-badge wait">Restrita</span>@endif</td>
            <td><x-admin.status :status="$d->status" :date="$d->published_at" /></td>
            <td><small>{{ $d->document_date?->format('d/m/Y') ?? '—' }}</small></td>
            <td class="text-end text-nowrap">
              @if ($d->trashed())
                <x-admin.trash-actions :model="$d" prefix="admin.documents" />
              @else
                @if ($d->currentVersion)<a class="u-iconbtn" href="{{ $d->downloadUrl() }}" title="Descarregar" aria-label="Descarregar"><i class="bi bi-download"></i></a>@endif
                <a class="u-iconbtn" href="{{ route('admin.documents.edit', $d) }}" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                @can('delete', $d)<x-admin.delete-form :action="route('admin.documents.destroy', $d)" message="Mover o documento para a reciclagem?" label="Mover para a reciclagem" :icon-only="true" />@endcan
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
