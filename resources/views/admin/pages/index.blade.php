@extends('layouts.admin')
@section('title', 'Páginas')

@section('content')
<x-admin.title title="Páginas" subtitle="Hierarquia de páginas do website (estrutura do website_template).">
  @can('create', \App\Models\Page::class)<a href="{{ route('admin.pages.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Nova página</a>@endcan
</x-admin.title>

<x-admin.list-filters :authors="$authors" placeholder="Título, endereço ou resumo">
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_layout">Layout</label>
    <select class="form-select" id="flt_layout" name="layout">
      <option value="">Todos</option>
      @foreach (\App\Models\Page::LAYOUTS as $k => $l)<option value="{{ $k }}" @selected(request('layout') === $k)>{{ $l }}</option>@endforeach
    </select>
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_parent">Secção</label>
    <select class="form-select" id="flt_parent" name="parent">
      <option value="">Todas</option>
      <option value="root" @selected(request('parent') === 'root')>Nível superior</option>
      @foreach ($roots as $id => $t)<option value="{{ $id }}" @selected((string) request('parent') === (string) $id)>{{ $t }}</option>@endforeach
    </select>
  </div>
</x-admin.list-filters>

<div class="d-box">
  <div class="d-box-head"><h2>{{ $items->total() }} {{ $items->total() === 1 ? 'página' : 'páginas' }}</h2></div>
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-file-earmark"></i>Nenhuma página encontrada.</div>
  @else
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th><x-admin.sort field="title" label="Título" /></th><th><x-admin.sort field="path" label="Endereço" /></th><th>Layout</th><th>Estado</th><th><x-admin.sort field="updated_at" label="Actualizada" /></th><th class="text-end">Acções</th></tr></thead>
      <tbody>
        @foreach ($items as $page)
          <tr>
            <td>
              <a class="row-title" href="{{ route('admin.pages.edit', $page) }}">{{ $page->title }}</a>
              @if ($page->is_system)<span class="u-badge ms-1" title="Página estrutural do template (endereço fixo)">Estrutura</span>@endif
              @if (! $page->isModule() && blank($page->body) && empty($page->blocks))<span class="u-badge red ms-1">Por preencher</span>@endif
              @if ($page->parent)<small>em {{ $page->parent->title }}</small>@endif
            </td>
            <td><small>/{{ $page->path }}</small></td>
            <td><small>{{ \App\Models\Page::LAYOUTS[$page->layout] ?? $page->layout }}</small></td>
            <td><x-admin.status :status="$page->status" :date="$page->published_at" /></td>
            <td><small>{{ $page->updated_at?->format('d/m/Y H:i') }}<br>{{ $page->editor?->name }}</small></td>
            <td class="text-end text-nowrap">
              @if ($page->trashed())
                <x-admin.trash-actions :model="$page" prefix="admin.pages" />
              @else
                <a class="u-iconbtn" href="{{ route('admin.preview', ['pages', $page->id]) }}" target="_blank" rel="noopener" title="Pré-visualizar" aria-label="Pré-visualizar {{ $page->title }}"><i class="bi bi-eye"></i></a>
                @can('update', $page)<a class="u-iconbtn" href="{{ route('admin.pages.edit', $page) }}" title="Editar" aria-label="Editar {{ $page->title }}"><i class="bi bi-pencil"></i></a>@endcan
                @can('create', \App\Models\Page::class)
                  <form method="post" action="{{ route('admin.pages.duplicate', $page) }}" class="d-inline">@csrf<button class="u-iconbtn" title="Duplicar" aria-label="Duplicar {{ $page->title }}"><i class="bi bi-copy"></i></button></form>
                @endcan
                @if (! $page->is_system)
                  @can('delete', $page)<x-admin.delete-form :action="route('admin.pages.destroy', $page)" message="Mover a página &quot;{{ $page->title }}&quot; para a reciclagem?" label="Mover para a reciclagem" :icon-only="true" />@endcan
                @endif
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
