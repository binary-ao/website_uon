@extends('layouts.admin')
@section('title', 'Etiquetas')

@section('content')
<x-admin.title title="Etiquetas" subtitle="As etiquetas são criadas ao editar notícias. Aqui pode renomeá-las ou eliminá-las." />
<form method="get" class="adm-filters d-flex gap-2" role="search">
  <input type="search" class="form-control w-auto" name="q" value="{{ request('q') }}" placeholder="Pesquisar" aria-label="Pesquisar">
  <button class="btn btn-u-dark"><i class="bi bi-funnel"></i></button>
</form>
<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-hash"></i>Sem etiquetas.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Etiqueta</th><th>Notícias</th><th class="text-end">Acções</th></tr></thead>
    <tbody>
      @foreach ($items as $t)
        <tr>
          <td>
            <form method="post" action="{{ route('admin.tags.update', $t) }}" class="d-flex gap-2">@csrf @method('PUT')
              <label class="visually-hidden" for="tag{{ $t->id }}">Nome</label>
              <input id="tag{{ $t->id }}" class="form-control form-control-sm w-auto" name="name" value="{{ $t->name }}" maxlength="60">
              <button class="btn btn-sm btn-u-light">Renomear</button>
            </form>
          </td>
          <td><small>{{ $t->news_count }}</small></td>
          <td class="text-end"><x-admin.delete-form :action="route('admin.tags.destroy', $t)" message="Eliminar a etiqueta &quot;{{ $t->name }}&quot;?" :icon-only="true" /></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
