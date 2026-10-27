@extends('layouts.admin')
@section('title', 'Parceiros')

@section('content')
<x-admin.title title="Parceiros" subtitle="Instituições e empresas parceiras confirmadas (página inicial, Parceiros e Cooperação).">
  <a href="{{ route('admin.partners.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Novo parceiro</a>
</x-admin.title>
<form method="get" class="adm-filters d-flex gap-2" role="search">
  <input type="search" class="form-control w-auto" name="q" value="{{ request('q') }}" placeholder="Pesquisar" aria-label="Pesquisar">
  <button class="btn btn-u-dark"><i class="bi bi-funnel"></i></button>
</form>
<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-people"></i>Sem parceiros registados.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Logótipo</th><th>Nome</th><th>Ligação</th><th>Activo</th><th>Ordem</th><th class="text-end">Acções</th></tr></thead>
    <tbody>
      @foreach ($items as $p)
        <tr>
          <td>@if ($p->logo)<img src="{{ $p->logo->url('md') }}" alt="" style="height:32px;max-width:90px;object-fit:contain">@else<span class="text-muted">—</span>@endif</td>
          <td><a class="row-title" href="{{ route('admin.partners.edit', $p) }}">{{ $p->name }}</a>@if ($p->is_demo)<span class="u-badge demo ms-1">Exemplo</span>@endif</td>
          <td><small>{{ $p->url ?? '—' }}</small></td>
          <td>@if ($p->is_active)<span class="s-badge ok">Sim</span>@else<span class="s-badge off">Não</span>@endif</td>
          <td><small>{{ $p->sort_order }}</small></td>
          <td class="text-end text-nowrap"><a class="u-iconbtn" href="{{ route('admin.partners.edit', $p) }}" aria-label="Editar"><i class="bi bi-pencil"></i></a>
            <x-admin.delete-form :action="route('admin.partners.destroy', $p)" message="Eliminar este parceiro?" :icon-only="true" /></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
