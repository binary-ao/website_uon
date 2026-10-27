<!doctype html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>{{ $title }} — Universidade Onze de Novembro</title>
<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/variables.css') }}" rel="stylesheet">
<link href="{{ asset('css/components.css') }}" rel="stylesheet">
<link href="{{ asset('css/pages.css') }}" rel="stylesheet">
<link href="{{ asset('css/uon.css') }}" rel="stylesheet">
</head>
<body class="site-body">
<div class="site-shell">
  <header class="site-header"><div class="container-xl py-3"><a class="brand-logo" href="{{ url('/') }}"><img src="{{ asset('assets/images/brand/logo-uon.png') }}" alt="Universidade Onze de Novembro"></a></div></header>
  <main id="main-content">
    <section class="error-page">
      <div class="container-xl text-center">
        <div class="error-code">{{ $code }}</div>
        <h1 class="mt-3">{{ $title }}</h1>
        <p class="section-intro mx-auto mb-4">{{ $message }}</p>
        <a href="{{ url('/') }}" class="btn btn-outline-brand">Página Inicial</a>
      </div>
    </section>
  </main>
</div>
</body>
</html>
