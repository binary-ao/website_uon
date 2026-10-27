@extends('layouts.admin')
@section('title', $event->exists ? 'Editar evento' : 'Novo evento')

@section('content')
<x-admin.title :title="$event->exists ? $event->title : 'Novo evento'" :crumbs="['Eventos' => route('admin.events.index')]" />

<form method="post" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}" class="u-form" data-dirty-check>
  @csrf
  @if ($event->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="u-card">
        <x-admin.field name="title" label="Título" :value="$event->title" required />
        <div class="row">
          <x-admin.field col="col-md-6 mb-3" name="slug" label="Endereço (slug)" :value="$event->slug" />
          <x-admin.field col="col-md-6 mb-3" name="category_id" label="Categoria" type="select" :options="$categories" :value="$event->category_id" empty="— Sem categoria —" />
        </div>
        <x-admin.field name="excerpt" label="Resumo" type="textarea" rows="2" :value="$event->excerpt" class="mb-0" />
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-calendar3"></i> Data, hora e local <small class="text-muted fw-normal">({{ config('app.timezone') }})</small></div>
        <div class="row">
          <x-admin.field col="col-md-3 mb-3" name="start_date" label="Data de início" type="date" :value="$event->starts_at?->format('Y-m-d')" required />
          <x-admin.field col="col-md-3 mb-3" name="start_time" label="Hora de início" type="time" :value="$event->exists && ! $event->all_day ? $event->starts_at?->format('H:i') : null" />
          <x-admin.field col="col-md-3 mb-3" name="end_date" label="Data de fim" type="date" :value="$event->ends_at?->format('Y-m-d')" />
          <x-admin.field col="col-md-3 mb-3" name="end_time" label="Hora de fim" type="time" :value="$event->exists && ! $event->all_day ? $event->ends_at?->format('H:i') : null" />
        </div>
        <x-admin.field name="all_day" label="Evento de dia inteiro (sem horas)" type="checkbox" :value="$event->all_day" />
        <x-admin.field name="location" label="Local" :value="$event->location" class="mb-0" />
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-text-paragraph"></i> Descrição</div>
        <label for="f_body" class="visually-hidden">Descrição</label>
        <textarea id="f_body" name="body" data-editor class="form-control" rows="12">{{ old('body', $event->body) }}</textarea>
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-person-plus"></i> Inscrições (opcional)</div>
        <x-admin.field name="registration_url" label="Ligação de inscrição" :value="$event->registration_url" help="Apenas se existir inscrição. URL externo (https://) ou página interna (/...). O botão é ocultado depois de o evento terminar." />
        <x-admin.field name="registration_info" label="Nota sobre a participação" :value="$event->registration_info" help="Ex.: Entrada livre, sujeita a inscrição." class="mb-0" />
      </div>

      <x-admin.seo :model="$event" />
    </div>
    <div class="col-xl-4">
      <div class="sticky-side">
        <x-admin.publish-box :model="$event" preview-type="events" route-prefix="admin.events" />
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-image"></i> Imagem de destaque</div>
          <x-admin.media-field name="featured_media_id" :media="$event->featuredMedia" />
        </div>
        <x-admin.revisions :model="$event" />
      </div>
    </div>
  </div>
</form>

@if ($event->exists)
  @can('delete', $event)
    <div class="mt-4 text-end"><x-admin.delete-form :action="route('admin.events.destroy', $event)" message="Mover este evento para a reciclagem?" label="Mover para a reciclagem" /></div>
  @endcan
@endif
@endsection
