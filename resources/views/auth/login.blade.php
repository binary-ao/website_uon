@extends('layouts.auth')
@section('title', 'Iniciar sessão')

@section('content')
<h2 class="h4 mb-1">Iniciar sessão</h2>
<p class="text-muted small mb-4">Introduza as suas credenciais para aceder ao CMS.</p>

@if (session('status'))
  <div class="alert alert-brand-success small" role="status"><i class="bi bi-info-circle"></i> {{ session('status') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-brand-danger small" role="alert"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>
@endif

<form method="post" action="{{ route('login.attempt') }}" class="u-card u-form" novalidate>
  @csrf
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" maxlength="190">
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Palavra-passe</label>
    <div class="a-pass">
      <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
      <button type="button" data-toggle-password="password" aria-label="Mostrar palavra-passe" aria-pressed="false"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
      <label class="form-check-label small" for="remember">Manter sessão</label>
    </div>
    <a href="{{ route('password.request') }}" class="small">Esqueceu a palavra-passe?</a>
  </div>
  <button type="submit" class="btn btn-uon w-100 py-2">Entrar</button>
</form>
@endsection
