@extends('layouts.admin')
@section('title', 'O meu perfil')

@section('content')
<x-admin.title title="O meu perfil" subtitle="Função: {{ $user->roleLabel() }}" />
<div class="row g-4">
  <div class="col-lg-6">
    <form method="post" action="{{ route('admin.profile.update') }}" class="u-card u-form">
      @csrf @method('PUT')
      <div class="u-card-title"><i class="bi bi-person"></i> Dados pessoais</div>
      <x-admin.field name="name" label="Nome" :value="$user->name" required />
      <x-admin.field name="email" label="Email" type="email" :value="$user->email" required help="Para alterar o email, confirme a palavra-passe actual." />
      <x-admin.field name="job_title" label="Cargo / unidade" :value="$user->job_title" />
      <x-admin.field name="current_password" label="Palavra-passe actual (apenas se alterar o email)" type="password" />
      <button class="btn btn-uon" type="submit">Guardar perfil</button>
    </form>
  </div>
  <div class="col-lg-6" id="password">
    <form method="post" action="{{ route('admin.profile.password') }}" class="u-card u-form">
      @csrf @method('PUT')
      <div class="u-card-title"><i class="bi bi-key"></i> Alterar palavra-passe</div>
      <x-admin.field name="current_password" id="pw_current" label="Palavra-passe actual" type="password" required />
      <x-admin.field name="password" label="Nova palavra-passe" type="password" required help="Mínimo 10 caracteres, com maiúsculas, minúsculas e números." />
      <x-admin.field name="password_confirmation" label="Confirmar nova palavra-passe" type="password" required />
      <button class="btn btn-uon" type="submit">Alterar palavra-passe</button>
      @if ($user->password_changed_at)<p class="small text-muted mt-3 mb-0">Última alteração: {{ $user->password_changed_at->format('d/m/Y H:i') }}</p>@endif
    </form>
  </div>
</div>
@endsection
