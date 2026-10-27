@extends('layouts.admin')
@section('title', $partner->exists ? 'Editar parceiro' : 'Novo parceiro')

@section('content')
<x-admin.title :title="$partner->exists ? $partner->name : 'Novo parceiro'" :crumbs="['Parceiros' => route('admin.partners.index')]" />
<form method="post" action="{{ $partner->exists ? route('admin.partners.update', $partner) : route('admin.partners.store') }}" class="u-card u-form" style="max-width:760px">
  @csrf @if ($partner->exists) @method('PUT') @endif
  <x-admin.field name="name" label="Nome" :value="$partner->name" required />
  <x-admin.media-field name="logo_media_id" label="Logótipo" :media="$partner->logo" help="Se vazio, é apresentado o nome." />
  <x-admin.field name="url" label="Website" :value="$partner->url" placeholder="https://" />
  <x-admin.field name="description" label="Descrição curta" :value="$partner->description" />
  <x-admin.field name="sort_order" label="Ordem" type="number" :value="$partner->sort_order ?? 0" />
  <x-admin.field name="is_active" label="Activo" type="checkbox" :value="$partner->is_active" />
  <button class="btn btn-uon" type="submit">Guardar</button>
</form>
@endsection
