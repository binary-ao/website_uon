@extends('layouts.auth')
@section('title', 'Definir nova palavra-passe')

@section('content')
<h2 class="h4 mb-1">Definir nova palavra-passe</h2>
<p class="text-muted small mb-4">Mínimo de 10 caracteres, com maiúsculas, minúsculas e números.</p>

@if ($errors->any())
  <div class="alert alert-brand-danger small" role="alert">
    @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
  </div>
@endif

<form method="post" action="{{ route('password.update') }}" class="u-card u-form">
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username">
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Nova palavra-passe</label>
    <div class="a-pass">
      <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password" minlength="10">
      <button type="button" data-toggle-password="password" aria-label="Mostrar palavra-passe" aria-pressed="false"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <div class="mb-3">
    <label for="password_confirmation" class="form-label">Confirmar palavra-passe</label>
    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
  </div>
  <button type="submit" class="btn btn-uon w-100 py-2">Guardar nova palavra-passe</button>
</form>
@endsection
