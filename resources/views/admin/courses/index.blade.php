@extends('layouts.admin')
@section('title', 'Cursos')

@section('content')
<x-admin.title title="Cursos" subtitle="Oferta formativa. Registe apenas cursos confirmados pela UON.">
  @can('create', \App\Models\Course::class)<a href="{{ route('admin.courses.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Novo curso</a>@endcan
</x-admin.title>

<x-admin.list-filters :authors="$authors" placeholder="Nome ou resumo">
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_deg">Grau</label>
    <select class="form-select" id="flt_deg" name="degree"><option value="">Todos</option>
      @foreach ($degrees as $id => $n)<option value="{{ $id }}" @selected((string) request('degree') === (string) $id)>{{ $n }}</option>@endforeach
    </select>
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_unit">Unidade</label>
    <select class="form-select" id="flt_unit" name="unit"><option value="">Todas</option>
      @foreach ($units as $id => $n)<option value="{{ $id }}" @selected((string) request('unit') === (string) $id)>{{ $n }}</option>@endforeach
    </select>
  </div>
</x-admin.list-filters>

<div class="d-box">
  <div class="d-box-head"><h2>{{ $items->total() }} cursos</h2></div>
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-mortarboard"></i>Nenhum curso encontrado.</div>
  @else
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th><x-admin.sort field="name" label="Curso" /></th><th>Grau</th><th>Unidade orgânica</th><th>Plano</th><th>Estado</th><th class="text-end">Acções</th></tr></thead>
      <tbody>
        @foreach ($items as $c)
          <tr>
            <td><a class="row-title" href="{{ route('admin.courses.edit', $c) }}">{{ $c->name }}</a>@if ($c->is_demo)<span class="u-badge demo ms-1">Exemplo</span>@endif</td>
            <td><small>{{ $c->degree?->name }}</small></td>
            <td><small>{{ $c->unit?->name ?? '—' }}</small></td>
            <td><small>{{ $c->subjects_count }} UC</small></td>
            <td><x-admin.status :status="$c->status" :date="$c->published_at" /></td>
            <td class="text-end text-nowrap">
              @if ($c->trashed())
                <x-admin.trash-actions :model="$c" prefix="admin.courses" />
              @else
                <a class="u-iconbtn" href="{{ route('admin.preview', ['courses', $c->id]) }}" target="_blank" rel="noopener" title="Pré-visualizar" aria-label="Pré-visualizar"><i class="bi bi-eye"></i></a>
                <a class="u-iconbtn" href="{{ route('admin.courses.edit', $c) }}" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                @can('delete', $c)<x-admin.delete-form :action="route('admin.courses.destroy', $c)" message="Mover o curso para a reciclagem?" label="Mover para a reciclagem" :icon-only="true" />@endcan
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
