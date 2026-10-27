@extends('layouts.admin')
@section('title', 'Categorias')

@section('content')
<x-admin.title title="Categorias" subtitle="Taxonomias de notícias, eventos, documentos e galeria. Categorias em uso não podem ser eliminadas.">
  <a href="{{ route('admin.categories.create', ['type' => $type ?? 'news']) }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Nova categoria</a>
</x-admin.title>
<div class="adm-tabs">
  <a href="{{ route('admin.categories.index') }}" class="{{ $type ? '' : 'active' }}">Todas</a>
  @foreach (\App\Models\Category::TYPES as $k => $l)<a href="{{ route('admin.categories.index', ['type' => $k]) }}" class="{{ $type === $k ? 'active' : '' }}">{{ $l }}</a>@endforeach
</div>
<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-tags"></i>Sem categorias.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Nome</th><th>Tipo</th><th>Endereço</th><th>Utilização</th><th class="text-end">Acções</th></tr></thead>
    <tbody>
      @foreach ($items as $c)
        <tr>
          <td><a class="row-title" href="{{ route('admin.categories.edit', $c) }}">{{ $c->name }}</a></td>
          <td><small>{{ $c->typeLabel() }}</small></td>
          <td><small>{{ $c->slug }}</small></td>
          <td><small>{{ $c->usageCount() }}</small></td>
          <td class="text-end text-nowrap"><a class="u-iconbtn" href="{{ route('admin.categories.edit', $c) }}" aria-label="Editar"><i class="bi bi-pencil"></i></a>
            <x-admin.delete-form :action="route('admin.categories.destroy', $c)" message="Eliminar a categoria &quot;{{ $c->name }}&quot;?" :icon-only="true" /></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
@endsection
