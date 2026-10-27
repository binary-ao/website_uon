@extends('layouts.admin')
@section('title', $degree->exists ? 'Editar grau' : 'Novo grau')

@section('content')
<x-admin.title :title="$degree->exists ? $degree->name : 'Novo grau'" :crumbs="['Graus académicos' => route('admin.degrees.index')]" />
<form method="post" action="{{ $degree->exists ? route('admin.degrees.update', $degree) : route('admin.degrees.store') }}" class="u-card u-form" style="max-width:640px">
  @csrf @if ($degree->exists) @method('PUT') @endif
  <x-admin.field name="name" label="Nome" :value="$degree->name" required />
  <x-admin.field name="plural_name" label="Plural" :value="$degree->plural_name" help="Usado nos filtros (ex.: Licenciaturas)." />
  <x-admin.field name="slug" label="Endereço (slug)" :value="$degree->slug" help="Usado nos filtros (?grau=...) e nas páginas de oferta formativa." />
  <x-admin.field name="sort_order" label="Ordem" type="number" :value="$degree->sort_order ?? 0" />
  <button class="btn btn-uon" type="submit">Guardar</button>
</form>
@endsection
