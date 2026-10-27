@extends('layouts.admin')
@section('title', $notice->exists ? 'Editar aviso' : 'Novo aviso')

@section('content')
<x-admin.title :title="$notice->exists ? 'Editar aviso' : 'Novo aviso'" :crumbs="['Avisos' => route('admin.notices.index')]" />
<form method="post" action="{{ $notice->exists ? route('admin.notices.update', $notice) : route('admin.notices.store') }}" class="u-card u-form" style="max-width:760px">
  @csrf @if ($notice->exists) @method('PUT') @endif
  <x-admin.field name="text" label="Texto do aviso" :value="$notice->text" required help="Frase curta (até 200 caracteres)." />
  <div class="row">
    <x-admin.field col="col-md-6 mb-3" name="icon" label="Ícone" :value="$notice->icon" placeholder="bi-megaphone" />
    <x-admin.field col="col-md-6 mb-3" name="url" label="Ligação (opcional)" :value="$notice->url" placeholder="/caminho ou https://" />
    <x-admin.field col="col-md-6 mb-3" name="starts_at" label="Mostrar a partir de" type="datetime-local" :value="$notice->starts_at" />
    <x-admin.field col="col-md-6 mb-3" name="ends_at" label="Mostrar até" type="datetime-local" :value="$notice->ends_at" />
    <x-admin.field col="col-md-6 mb-3" name="sort_order" label="Ordem" type="number" :value="$notice->sort_order ?? 0" />
  </div>
  <x-admin.field name="is_active" label="Activo" type="checkbox" :value="$notice->is_active" />
  <button class="btn btn-uon" type="submit">Guardar</button>
</form>
@endsection
