@extends('layouts.public')

@section('title', 'Demasiados pedidos')
@section('noindex', '1')

@section('content')
<section class="error-page">
  <div class="container-xl text-center">
    <div class="error-code">429</div>
    <h1 class="mt-3">Demasiados pedidos</h1>
    <p class="section-intro mx-auto mb-4">Foram feitos demasiados pedidos num curto espaço de tempo. Aguarde um momento e tente novamente.</p>
    <a href="{{ url('/') }}" class="btn btn-outline-brand"><i class="bi bi-house"></i> Página Inicial</a>
  </div>
</section>
@endsection
