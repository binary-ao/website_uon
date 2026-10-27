@extends('layouts.admin')
@section('title', 'Menus')

@section('content')
<x-admin.title title="Menus" subtitle="Itens dos menus do Header (navegação principal, mega menu, menu móvel, barra superior) e do Footer. A composição visual dos componentes é fixa." />
<div class="d-box">
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Menu</th><th>Zona do template</th><th>Título visível</th><th>Itens</th><th class="text-end">Acções</th></tr></thead>
      <tbody>
        @foreach ($menus as $menu)
          <tr>
            <td><a class="row-title" href="{{ route('admin.menus.edit', $menu) }}">{{ $menu->name }}</a></td>
            <td><small>{{ \App\Models\Menu::LOCATIONS[$menu->location]['name'] ?? $menu->location }}</small></td>
            <td><small>{{ $menu->title ?? '—' }}</small></td>
            <td><small>{{ $menu->items_count }}</small></td>
            <td class="text-end"><a class="btn btn-sm btn-u-light" href="{{ route('admin.menus.edit', $menu) }}"><i class="bi bi-pencil"></i> Gerir itens</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
