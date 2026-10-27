@extends('layouts.admin')
@section('title', 'Eventos')

@section('content')
<x-admin.title title="Eventos" subtitle="Datas e horas no fuso horário {{ config('app.timezone') }}.">
  @can('create', \App\Models\Event::class)<a href="{{ route('admin.events.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Novo evento</a>@endcan
</x-admin.title>

<x-admin.list-filters :authors="$authors" placeholder="Título ou local">
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_period">Período</label>
    <select class="form-select" id="flt_period" name="period">
      <option value="">Todos</option>
      <option value="upcoming" @selected(request('period') === 'upcoming')>Próximos</option>
      <option value="past" @selected(request('period') === 'past')>Anteriores</option>
    </select>
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_cat">Categoria</label>
    <select class="form-select" id="flt_cat" name="category">
      <option value="">Todas</option>
      @foreach ($categories as $id => $n)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $n }}</option>@endforeach
    </select>
  </div>
</x-admin.list-filters>

<div class="d-box">
  <div class="d-box-head"><h2>{{ $items->total() }} eventos</h2></div>
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-calendar-event"></i>Nenhum evento encontrado.</div>
  @else
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th><x-admin.sort field="title" label="Título" /></th><th><x-admin.sort field="starts_at" label="Data" /></th><th>Local</th><th>Estado</th><th class="text-end">Acções</th></tr></thead>
      <tbody>
        @foreach ($items as $e)
          <tr>
            <td><a class="row-title" href="{{ route('admin.events.edit', $e) }}">{{ $e->title }}</a>
              @if ($e->is_demo)<span class="u-badge demo ms-1">Exemplo</span>@endif
              @if ($e->isPast())<small>Já decorreu</small>@endif</td>
            <td><small>{{ $e->starts_at->format('d/m/Y') }} {{ $e->all_day ? '' : $e->starts_at->format('H:i') }}@if ($e->ends_at)<br>até {{ $e->ends_at->format('d/m/Y H:i') }}@endif</small></td>
            <td><small>{{ $e->location ?? '—' }}</small></td>
            <td><x-admin.status :status="$e->status" :date="$e->published_at" /></td>
            <td class="text-end text-nowrap">
              @if ($e->trashed())
                <x-admin.trash-actions :model="$e" prefix="admin.events" />
              @else
                <a class="u-iconbtn" href="{{ route('admin.preview', ['events', $e->id]) }}" target="_blank" rel="noopener" title="Pré-visualizar" aria-label="Pré-visualizar"><i class="bi bi-eye"></i></a>
                <a class="u-iconbtn" href="{{ route('admin.events.edit', $e) }}" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                @can('delete', $e)<x-admin.delete-form :action="route('admin.events.destroy', $e)" message="Mover o evento para a reciclagem?" label="Mover para a reciclagem" :icon-only="true" />@endcan
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
