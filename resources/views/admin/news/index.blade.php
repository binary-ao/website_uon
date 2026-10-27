@extends('layouts.admin')
@section('title', 'Notícias e comunicados')

@section('content')
<x-admin.title title="Notícias e comunicados">
  @can('create', \App\Models\News::class)
    <a href="{{ route('admin.news.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Nova notícia</a>
    <a href="{{ route('admin.news.create', ['type' => 'announcement']) }}" class="btn btn-u-light"><i class="bi bi-megaphone"></i> Novo comunicado</a>
  @endcan
</x-admin.title>

<x-admin.list-filters :authors="$authors" placeholder="Título ou resumo">
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_type">Tipo</label>
    <select class="form-select" id="flt_type" name="type">
      <option value="">Todos</option>
      @foreach (\App\Models\News::TYPES as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach
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
  <div class="d-box-head"><h2>{{ $items->total() }} resultados</h2></div>
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-newspaper"></i>Nenhum conteúdo encontrado.</div>
  @else
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th><x-admin.sort field="title" label="Título" /></th><th>Tipo</th><th>Categoria</th><th>Estado</th><th><x-admin.sort field="published_at" label="Publicação" /></th><th>Autor</th><th class="text-end">Acções</th></tr></thead>
      <tbody>
        @foreach ($items as $n)
          <tr>
            <td>
              <a class="row-title" href="{{ route('admin.news.edit', $n) }}">{{ \Illuminate\Support\Str::limit($n->title, 80) }}</a>
              @if ($n->is_featured)<i class="bi bi-star-fill text-warning ms-1" title="Destaque"></i>@endif
              @if ($n->is_demo)<span class="u-badge demo ms-1">Exemplo</span>@endif
            </td>
            <td><small>{{ $n->typeLabel() }}</small></td>
            <td><small>{{ $n->category?->name ?? '—' }}</small></td>
            <td><x-admin.status :status="$n->status" :date="$n->published_at" /></td>
            <td><small>{{ $n->published_at?->format('d/m/Y H:i') ?? '—' }}</small></td>
            <td><small>{{ $n->author?->name ?? '—' }}</small></td>
            <td class="text-end text-nowrap">
              @if ($n->trashed())
                <x-admin.trash-actions :model="$n" prefix="admin.news" />
              @else
                <a class="u-iconbtn" href="{{ route('admin.preview', ['news', $n->id]) }}" target="_blank" rel="noopener" title="Pré-visualizar" aria-label="Pré-visualizar"><i class="bi bi-eye"></i></a>
                <a class="u-iconbtn" href="{{ route('admin.news.edit', $n) }}" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                @can('delete', $n)<x-admin.delete-form :action="route('admin.news.destroy', $n)" message="Mover &quot;{{ $n->title }}&quot; para a reciclagem?" label="Mover para a reciclagem" :icon-only="true" />@endcan
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
