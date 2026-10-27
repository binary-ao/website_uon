@extends('layouts.public')

@section('title', 'Sessão expirada')
@section('noindex', '1')

@section('content')
<section class="error-page">
  <div class="container-xl text-center">
    <div class="error-code">419</div>
    <h1 class="mt-3">Sessão expirada</h1>
    <p class="section-intro mx-auto mb-4">A sua sessão expirou por inactividade. Volte à página anterior, actualize-a e tente novamente.</p>
    <a href="{{ url('/') }}" class="btn btn-outline-brand"><i class="bi bi-house"></i> Página Inicial</a>
  </div>
</section>
@endsection
