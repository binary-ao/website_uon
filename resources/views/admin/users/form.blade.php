@extends('layouts.admin')
@section('title', $user->exists ? 'Editar utilizador' : 'Novo utilizador')

@php $isSelf = $user->exists && $user->is(auth()->user()); @endphp

@section('content')
<x-admin.title :title="$user->exists ? $user->name : 'Novo utilizador'" :crumbs="['Utilizadores' => route('admin.users.index')]" />
<form method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="u-form">
  @csrf @if ($user->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="u-card">
        <x-admin.field name="name" label="Nome" :value="$user->name" required />
        <x-admin.field name="email" label="Email" type="email" :value="$user->email" required />
        <x-admin.field name="job_title" label="Cargo / unidade" :value="$user->job_title" />
      </div>
      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-key"></i> {{ $user->exists ? 'Definir nova palavra-passe' : 'Palavra-passe inicial' }}</div>
        <x-admin.field name="password" label="Palavra-passe" type="password" :required="! $user->exists" help="Mínimo 10 caracteres, com maiúsculas, minúsculas e números.{{ $user->exists ? ' Deixe vazio para manter.' : ' Comunique-a por canal seguro e peça ao utilizador que a altere.' }}" />
        <x-admin.field name="password_confirmation" label="Confirmar palavra-passe" type="password" class="mb-0" />
      </div>
    </div>
    <div class="col-lg-5">
      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-shield-lock"></i> Acesso</div>
        @if ($isSelf)
          <p class="small">Função: <strong>{{ $user->roleLabel() }}</strong></p>
          <p class="small text-muted">Não pode alterar a sua própria função nem desactivar a sua conta.</p>
        @else
          <x-admin.field name="role" label="Função" type="select" :options="$roles" :value="$currentRole" required />
          <ul class="small text-muted ps-3">
            <li><strong>Superadministrador:</strong> acesso global, utilizadores e permissões.</li>
            <li><strong>Administrador:</strong> todos os conteúdos e módulos do website.</li>
            <li><strong>Editor:</strong> cria, edita, revê e publica conteúdos.</li>
            <li><strong>Autor:</strong> cria e edita os próprios conteúdos; envia para revisão.</li>
          </ul>
          <x-admin.field name="is_active" label="Conta activa" type="checkbox" :value="$user->exists ? $user->is_active : true" help="Contas inactivas não conseguem iniciar sessão; as sessões abertas são terminadas." />
        @endif
        <button class="btn btn-uon w-100" type="submit">Guardar</button>
        @if ($user->exists)
          <p class="small text-muted mt-3 mb-0">Último acesso: {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'nunca' }}{{ $user->last_login_ip ? ' · '.$user->last_login_ip : '' }}</p>
        @endif
      </div>
    </div>
  </div>
</form>
@endsection
