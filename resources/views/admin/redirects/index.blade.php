@extends('layouts.admin')
@section('title', 'Redireccionamentos')

@section('content')
<x-admin.title title="Redireccionamentos" subtitle="Criados automaticamente quando o endereço de um conteúdo publicado muda; também podem ser criados manualmente.">
  <a href="{{ route('admin.redirects.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Novo redireccionamento</a>
</x-admin.title>
<form method="get" class="adm-filters d-flex gap-2" role="search">
  <input type="search" class="form-control w-auto" name="q" value="{{ request('q') }}" placeholder="Pesquisar" aria-label="Pesquisar">
  <button class="btn btn-u-dark"><i class="bi bi-funnel"></i></button>
</form>
<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-signpost-split"></i>Sem redireccionamentos.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Origem</th><th>Destino</th><th>Código</th><th>Utilizações</th><th>Activo</th><th class="text-end">Acções</th></tr></thead>
    <tbody>
      @foreach ($items as $r)
        <tr>
          <td><code>{{ $r->from_path }}</code></td>
          <td><small class="text-break">{{ $r->to_url }}</small></td>
          <td><small>{{ $r->status_code }}</small></td>
          <td><small>{{ $r->hits }}@if ($r->last_hit_at)<br>{{ $r->last_hit_at->format('d/m/Y') }}@endif</small></td>
          <td>@if ($r->is_active)<span class="s-badge ok">Sim</span>@else<span class="s-badge off">Não</span>@endif</td>
          <td class="text-end text-nowrap"><a class="u-iconbtn" href="{{ route('admin.redirects.edit', $r) }}" aria-label="Editar"><i class="bi bi-pencil"></i></a>
            <x-admin.delete-form :action="route('admin.redirects.destroy', $r)" message="Eliminar este redireccionamento?" :icon-only="true" /></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
