@extends('layouts.admin')
@section('title', 'Unidades orgânicas')

@section('content')
<x-admin.title title="Unidades orgânicas" subtitle="Faculdades, institutos, escolas, departamentos, centros, serviços e órgãos de gestão. Registe apenas informação confirmada.">
  @can('create', \App\Models\OrganicUnit::class)<a href="{{ route('admin.units.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Nova unidade</a>@endcan
</x-admin.title>

<x-admin.list-filters :authors="$authors" placeholder="Nome, sigla ou responsável" :dates="false">
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_type">Tipo</label>
    <select class="form-select" id="flt_type" name="type"><option value="">Todos</option>
      @foreach (\App\Models\OrganicUnit::TYPES as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach
    </select>
  </div>
</x-admin.list-filters>

<div class="d-box">
  <div class="d-box-head"><h2>{{ $items->total() }} unidades</h2></div>
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-building"></i>Nenhuma unidade registada.</div>
  @else
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th><x-admin.sort field="name" label="Nome" /></th><th>Tipo</th><th>Pertence a</th><th>Responsável</th><th>Cursos</th><th>Estado</th><th class="text-end">Acções</th></tr></thead>
      <tbody>
        @foreach ($items as $u)
          <tr>
            <td><a class="row-title" href="{{ route('admin.units.edit', $u) }}">{{ $u->name }}</a>@if ($u->is_demo)<span class="u-badge demo ms-1">Exemplo</span>@endif</td>
            <td><small>{{ $u->typeLabel() }}</small></td>
            <td><small>{{ $u->parent?->name ?? '—' }}</small></td>
            <td><small>{{ $u->head_name ?? '—' }}</small></td>
            <td><small>{{ $u->courses_count }}</small></td>
            <td><x-admin.status :status="$u->status" /></td>
            <td class="text-end text-nowrap">
              @if ($u->trashed())
                <x-admin.trash-actions :model="$u" prefix="admin.units" />
              @else
                <a class="u-iconbtn" href="{{ route('admin.units.edit', $u) }}" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                @can('delete', $u)<x-admin.delete-form :action="route('admin.units.destroy', $u)" message="Mover a unidade para a reciclagem?" label="Mover para a reciclagem" :icon-only="true" />@endcan
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
