@extends('layouts.admin')
@section('title', 'Menu: '.$menu->name)

@php
    $isMain = $menu->location === 'main';
    $displayOptions = $isMain ? \App\Models\MenuItem::DISPLAYS : ['link' => 'Link simples'];
@endphp

@section('content')
<x-admin.title :title="$menu->name" :subtitle="\App\Models\Menu::LOCATIONS[$menu->location]['name'] ?? ''" :crumbs="['Menus' => route('admin.menus.index')]" />

<div class="row g-4">
  <div class="col-xl-7">
    @if (str_starts_with($menu->location, 'footer_') && $menu->location !== 'footer_bottom')
      <form method="post" action="{{ route('admin.menus.update', $menu) }}" class="u-card u-form d-flex gap-2 align-items-end">
        @csrf @method('PUT')
        <x-admin.field col="flex-grow-1" name="title" label="Título da coluna no rodapé" :value="$menu->title" />
        <button class="btn btn-u-dark mb-0" type="submit">Guardar título</button>
      </form>
    @endif

    <div class="d-box mt-0">
      <div class="d-box-head"><h2>Itens</h2><small class="text-muted">Profundidade máxima: {{ $menu->maxDepth() }} nível(is)</small></div>
      <div class="d-body">
        @if ($tree->isEmpty())
          <div class="r-empty"><i class="bi bi-list-nested"></i>Sem itens.</div>
        @else
          @include('admin.menus.tree', ['nodes' => $tree, 'menu' => $menu])
        @endif
      </div>
    </div>
    <p class="small text-muted mt-2"><i class="bi bi-info-circle"></i> Itens que apontam para páginas não publicadas ou para plataformas externas sem URL configurado não são apresentados no website.</p>
  </div>

  <div class="col-xl-5">
    <form method="post" action="{{ route('admin.menus.items.store', $menu) }}" class="u-card u-form" id="newItem">
      @csrf
      <div class="u-card-title"><i class="bi bi-plus-lg"></i> Adicionar item</div>
      @include('admin.menus.item-fields', ['item' => new \App\Models\MenuItem(['display' => 'link', 'link_type' => 'page', 'is_active' => true]), 'prefix' => 'n'])
      <button type="submit" class="btn btn-uon">Adicionar</button>
    </form>
    @if ($isMain)
      <div class="u-card small">
        <div class="u-card-title"><i class="bi bi-diagram-2"></i> Regras de composição do Header</div>
        <ul class="mb-0">
          <li><strong>1.º nível:</strong> link simples, dropdown simples ou mega menu.</li>
          <li><strong>Mega menu:</strong> contém colunas (título da coluna) e cada coluna contém links.</li>
          <li><strong>Dropdown:</strong> contém links e separadores.</li>
          <li>O menu móvel (offcanvas) é gerado automaticamente a partir da mesma árvore.</li>
        </ul>
      </div>
    @endif
  </div>
</div>
@endsection
