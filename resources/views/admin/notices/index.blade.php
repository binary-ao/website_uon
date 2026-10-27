@extends('layouts.admin')
@section('title', 'Avisos')

@section('content')
<x-admin.title title="Avisos e anúncios" subtitle="Faixa de destaques da página inicial (máx. 4 avisos em vigor).">
  <a href="{{ route('admin.notices.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Novo aviso</a>
</x-admin.title>
<form method="get" class="adm-filters d-flex gap-2 flex-wrap" role="search">
  <input type="search" class="form-control w-auto" name="q" value="{{ request('q') }}" placeholder="Pesquisar" aria-label="Pesquisar">
  <select name="state" class="form-select w-auto" aria-label="Estado"><option value="">Todos</option><option value="current" @selected(request('state') === 'current')>Em vigor</option></select>
  <button class="btn btn-u-dark"><i class="bi bi-funnel"></i> Filtrar</button>
</form>
<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-megaphone"></i>Sem avisos.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Aviso</th><th>Período</th><th>Activo</th><th>Ordem</th><th class="text-end">Acções</th></tr></thead>
    <tbody>
      @foreach ($items as $n)
        <tr>
          <td><i class="bi {{ $n->icon ?: 'bi-megaphone' }} text-muted"></i> <a class="row-title" href="{{ route('admin.notices.edit', $n) }}">{{ $n->text }}</a>@if ($n->is_demo)<span class="u-badge demo ms-1">Exemplo</span>@endif</td>
          <td><small>{{ $n->starts_at?->format('d/m/Y H:i') ?? 'Sempre' }} → {{ $n->ends_at?->format('d/m/Y H:i') ?? 'sem fim' }}</small></td>
          <td>@if ($n->is_active)<span class="s-badge ok">Sim</span>@else<span class="s-badge off">Não</span>@endif</td>
          <td><small>{{ $n->sort_order }}</small></td>
          <td class="text-end text-nowrap"><a class="u-iconbtn" href="{{ route('admin.notices.edit', $n) }}" aria-label="Editar"><i class="bi bi-pencil"></i></a>
            <x-admin.delete-form :action="route('admin.notices.destroy', $n)" message="Eliminar este aviso?" :icon-only="true" /></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
