@extends('layouts.admin')
@section('title', $category->exists ? 'Editar categoria' : 'Nova categoria')

@section('content')
<x-admin.title :title="$category->exists ? $category->name : 'Nova categoria'" :crumbs="['Categorias' => route('admin.categories.index')]" />
<form method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="u-card u-form" style="max-width:640px">
  @csrf @if ($category->exists) @method('PUT') @endif
  <x-admin.field name="type" label="Tipo de conteúdo" type="select" :options="\App\Models\Category::TYPES" :value="$category->type" required />
  <x-admin.field name="name" label="Nome" :value="$category->name" required />
  <x-admin.field name="slug" label="Endereço (slug)" :value="$category->slug" help="Usado nos filtros públicos (?categoria=...)." />
  <x-admin.field name="description" label="Descrição" :value="$category->description" />
  <x-admin.field name="sort_order" label="Ordem" type="number" :value="$category->sort_order ?? 0" />
  <button class="btn btn-uon" type="submit">Guardar</button>
</form>
@endsection
