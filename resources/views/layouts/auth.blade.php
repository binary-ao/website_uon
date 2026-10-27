<!doctype html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>@yield('title') — CMS {{ setting('site_short_name', 'UON') }}</title>
<link rel="icon" href="{{ \App\Support\Settings::faviconUrl() }}" type="image/png">
<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/variables.css') }}" rel="stylesheet">
<link href="{{ asset('css/components.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin.css') }}?v=1" rel="stylesheet">
</head>
<body class="admin-body">
<div class="a-wrap">
  <aside class="a-side">
    <a href="{{ url('/') }}" class="a-logo"><img src="{{ \App\Support\Settings::logoUrl() }}" alt="{{ setting('site_name') }} — website"></a>
    <h1>Área de gestão de conteúdos <span>do website institucional</span></h1>
    <p class="a-hide-sm mt-3">Acesso reservado a utilizadores autorizados da {{ setting('site_name') }}. Todas as acções são registadas.</p>
  </aside>
  <main class="a-main">
    <div class="a-box">
      @yield('content')
      <p class="small text-muted mt-4 mb-0 text-center"><a href="{{ url('/') }}" class="text-muted"><i class="bi bi-arrow-left"></i> Voltar ao website</a></p>
    </div>
  </main>
</div>
<script>
document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var input = document.getElementById(btn.getAttribute('data-toggle-password'));
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-pressed', String(show));
    btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
});
</script>
</body>
</html>
