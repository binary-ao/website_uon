@extends('layouts.admin')
@section('title', $redirect->exists ? 'Editar redireccionamento' : 'Novo redireccionamento')

@section('content')
<x-admin.title :title="$redirect->exists ? 'Editar redireccionamento' : 'Novo redireccionamento'" :crumbs="['Redireccionamentos' => route('admin.redirects.index')]" />
<form method="post" action="{{ $redirect->exists ? route('admin.redirects.update', $redirect) : route('admin.redirects.store') }}" class="u-card u-form" style="max-width:760px">
  @csrf @if ($redirect->exists) @method('PUT') @endif
  <x-admin.field name="from_path" label="Endereço antigo (origem)" :value="$redirect->from_path" placeholder="/pagina-antiga" required help="Caminho relativo ao website, começado por /." />
  <x-admin.field name="to_url" label="Destino" :value="$redirect->to_url" placeholder="/nova-pagina ou https://..." required />
  <x-admin.field name="status_code" label="Tipo" type="select" :options="[301 => '301 — Permanente', 302 => '302 — Temporário']" :value="$redirect->status_code" required />
  <x-admin.field name="is_active" label="Activo" type="checkbox" :value="$redirect->is_active" />
  <button class="btn btn-uon" type="submit">Guardar</button>
</form>
@endsection
