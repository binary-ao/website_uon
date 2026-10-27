@extends('layouts.auth')
@section('title', 'Recuperar palavra-passe')

@section('content')
<h2 class="h4 mb-1">Recuperar palavra-passe</h2>
<p class="text-muted small mb-4">Indique o email da sua conta. Se existir uma conta activa, enviaremos uma ligação de recuperação válida durante 60 minutos.</p>

@if (session('status'))
  <div class="alert alert-brand-success small" role="status"><i class="bi bi-envelope-check"></i> {{ session('status') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-brand-danger small" role="alert">{{ $errors->first() }}</div>
@endif

<form method="post" action="{{ route('password.email') }}" class="u-card u-form">
  @csrf
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
  </div>
  <button type="submit" class="btn btn-uon w-100 py-2">Enviar ligação de recuperação</button>
</form>
<p class="small text-center mt-3"><a href="{{ route('login') }}">Voltar ao início de sessão</a></p>
@endsection
