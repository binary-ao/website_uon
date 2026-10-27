@extends('layouts.admin')
@section('title', $unit->exists ? 'Editar unidade' : 'Nova unidade')

@section('content')
<x-admin.title :title="$unit->exists ? $unit->name : 'Nova unidade orgânica'" :crumbs="['Unidades orgânicas' => route('admin.units.index')]" />

<form method="post" action="{{ $unit->exists ? route('admin.units.update', $unit) : route('admin.units.store') }}" class="u-form" data-dirty-check>
  @csrf
  @if ($unit->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="u-card">
        <div class="row">
          <x-admin.field col="col-md-4 mb-3" name="type" label="Tipo" type="select" :options="\App\Models\OrganicUnit::TYPES" :value="$unit->type" required />
          <x-admin.field col="col-md-8 mb-3" name="name" label="Nome" :value="$unit->name" required />
        </div>
        <div class="row">
          <x-admin.field col="col-md-4 mb-3" name="short_name" label="Sigla / nome curto" :value="$unit->short_name" />
          <x-admin.field col="col-md-8 mb-3" name="parent_id" label="Pertence a" type="select" :options="$parents" :value="$unit->parent_id" empty="— Nenhuma —" />
        </div>
        <x-admin.field name="summary" label="Subtítulo / área" :value="$unit->summary" help="Apresentado em destaque no cartão do directório." />
        <label for="f_desc" class="form-label">Descrição</label>
        <textarea id="f_desc" name="description" data-editor rows="8">{{ old('description', $unit->description) }}</textarea>
      </div>
      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-person-badge"></i> Responsável e contactos</div>
        <div class="row">
          <x-admin.field col="col-md-5 mb-3" name="head_title" label="Cargo do responsável" :value="$unit->head_title" placeholder="ex.: Decano(a)" />
          <x-admin.field col="col-md-7 mb-3" name="head_name" label="Nome do responsável" :value="$unit->head_name" />
          <x-admin.field col="col-md-6 mb-3" name="email" label="Email" type="email" :value="$unit->email" />
          <x-admin.field col="col-md-6 mb-3" name="phone" label="Telefone(s)" help="Vários números separados por /" :value="$unit->phone" />
        </div>
        <x-admin.field name="location" label="Localização / morada" :value="$unit->location" help="Usada no cartão de contactos e no botão Abrir no Google Maps." />
        <x-admin.field name="map_embed_url" label="URL de incorporação do mapa" :value="$unit->map_embed_url" placeholder="https://www.google.com/maps/embed?pb=..." help="Opcional. Google Maps → Partilhar → Incorporar mapa (copiar só o endereço src) ou OpenStreetMap → Partilhar → HTML." class="mb-0" />
      </div>
    </div>
    <div class="col-xl-4">
      <div class="sticky-side">
        <x-admin.publish-box :model="$unit" preview-type="units" route-prefix="admin.units" />
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-link"></i> Ligações</div>
          <x-admin.field name="page_id" label="Página institucional da unidade" type="select" :options="$pages" :value="$unit->page_id" empty="— Nenhuma —" help="Opcional. Por omissão, a unidade tem uma página própria gerada a partir destes dados; se indicar uma página do CMS, os links passam a apontar para ela." />
          <x-admin.field name="slug" label="Endereço (slug)" :value="$unit->slug" />
          <x-admin.field name="sort_order" label="Ordem" type="number" :value="$unit->sort_order ?? 0" class="mb-0" />
        </div>
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-image"></i> Imagem</div>
          <x-admin.media-field name="featured_media_id" :media="$unit->featuredMedia" />
        </div>
      </div>
    </div>
  </div>
</form>

@if ($unit->exists)
  @can('delete', $unit)
    <div class="mt-4 text-end"><x-admin.delete-form :action="route('admin.units.destroy', $unit)" message="Mover esta unidade para a reciclagem?" label="Mover para a reciclagem" /></div>
  @endcan
@endif
@endsection
