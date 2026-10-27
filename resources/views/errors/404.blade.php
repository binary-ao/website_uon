@extends('layouts.public')

{{-- 404.html do website_template --}}
@section('title', 'Página não encontrada')
@section('noindex', '1')

@section('content')
<section class="error-page">
  <div class="container-xl text-center">
    <div class="error-code">404</div>
    <h1 class="mt-3">Página não encontrada</h1>
    <p class="section-intro mx-auto mb-4">A página que procura pode ter sido removida, ter mudado de endereço ou nunca ter existido. Experimente pesquisar ou volte à página inicial.</p>
    <form class="d-flex gap-2 justify-content-center mb-4" style="max-width: 32rem; margin: 0 auto;" role="search" action="{{ route('search') }}" method="get">
      <label for="q404" class="visually-hidden">Pesquisar no website</label>
      <input class="form-control form-control-lg" type="search" id="q404" name="q" placeholder="Pesquisar no website..." maxlength="100">
      <button class="btn btn-brand" type="submit" aria-label="Pesquisar"><i class="bi bi-search"></i></button>
    </form>
    <div class="d-flex flex-wrap gap-3 justify-content-center">
      <a href="{{ url('/') }}" class="btn btn-outline-brand"><i class="bi bi-house"></i> Página Inicial</a>
      <a href="{{ url('ensino/oferta-formativa') }}" class="btn btn-outline-brand">Oferta Formativa</a>
      <a href="{{ url('contactos') }}" class="btn btn-outline-brand">Contactos</a>
    </div>
  </div>
</section>
@endsection
