@extends('layouts.public')

@section('title', 'Acesso não autorizado')
@section('noindex', '1')

@section('content')
<section class="error-page">
  <div class="container-xl text-center">
    <div class="error-code">403</div>
    <h1 class="mt-3">Acesso não autorizado</h1>
    <p class="section-intro mx-auto mb-4">Não tem permissão para aceder a este conteúdo.</p>
    <a href="{{ url('/') }}" class="btn btn-outline-brand"><i class="bi bi-house"></i> Página Inicial</a>
  </div>
</section>
@endsection
