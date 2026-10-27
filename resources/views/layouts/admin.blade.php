@php
    $u = auth()->user();
    $newMessages = $u->can('messages.manage') ? \App\Models\ContactMessage::where('status', 'new')->count() : 0;
    $pendingCount = \App\Models\News::where('status', 'pending')->count() + \App\Models\Page::where('status', 'pending')->count() + \App\Models\Event::where('status', 'pending')->count();
    $is = fn (...$patterns) => request()->routeIs(...$patterns) ? ' active' : '';
    $nav = [
        'Conteúdos' => [
            ['admin.dashboard', 'bi-speedometer2', 'Painel', true, ['admin.dashboard'], null],
            ['admin.pages.index', 'bi-file-earmark-richtext', 'Páginas', $u->can('viewAny', \App\Models\Page::class), ['admin.pages.*'], null],
            ['admin.news.index', 'bi-newspaper', 'Notícias e comunicados', $u->can('viewAny', \App\Models\News::class), ['admin.news.*'], null],
            ['admin.events.index', 'bi-calendar-event', 'Eventos', $u->can('viewAny', \App\Models\Event::class), ['admin.events.*'], null],
            ['admin.documents.index', 'bi-folder2-open', 'Documentos', $u->can('viewAny', \App\Models\Document::class), ['admin.documents.*'], null],
            ['admin.gallery.index', 'bi-images', 'Galeria e multimédia', $u->can('viewAny', \App\Models\GalleryItem::class), ['admin.gallery.*'], null],
            ['admin.media.index', 'bi-collection', 'Biblioteca multimédia', $u->can('media.upload'), ['admin.media.*'], null],
        ],
        'Académico' => [
            ['admin.courses.index', 'bi-mortarboard', 'Cursos', $u->can('viewAny', \App\Models\Course::class), ['admin.courses.*'], null],
            ['admin.units.index', 'bi-building', 'Unidades orgânicas', $u->can('viewAny', \App\Models\OrganicUnit::class), ['admin.units.*'], null],
            ['admin.degrees.index', 'bi-award', 'Graus académicos', $u->can('taxonomies.manage'), ['admin.degrees.*'], null],
        ],
        'Website' => [
            ['admin.hero-slides.index', 'bi-images', 'Banner da página inicial', $u->can('banners.manage'), ['admin.hero-slides.*'], null],
            ['admin.menus.index', 'bi-list-nested', 'Menus', $u->can('menus.manage'), ['admin.menus.*'], null],
            ['admin.settings.edit', 'bi-layout-text-window', 'Header, Footer e configurações', $u->can('settings.manage'), ['admin.settings.*'], null],
            ['admin.notices.index', 'bi-megaphone', 'Avisos', $u->can('notices.manage'), ['admin.notices.*'], null],
            ['admin.partners.index', 'bi-people', 'Parceiros', $u->can('partners.manage'), ['admin.partners.*'], null],
            ['admin.categories.index', 'bi-tags', 'Categorias', $u->can('taxonomies.manage'), ['admin.categories.*'], null],
            ['admin.tags.index', 'bi-hash', 'Etiquetas', $u->can('taxonomies.manage'), ['admin.tags.*'], null],
            ['admin.redirects.index', 'bi-signpost-split', 'Redireccionamentos', $u->can('redirects.manage'), ['admin.redirects.*'], null],
            ['admin.messages.index', 'bi-envelope', 'Mensagens de contacto', $u->can('messages.manage'), ['admin.messages.*'], $newMessages ?: null],
        ],
        'Administração' => [
            ['admin.users.index', 'bi-person-gear', 'Utilizadores', $u->can('users.manage'), ['admin.users.*'], null],
            ['admin.roles.index', 'bi-shield-lock', 'Funções e permissões', $u->can('roles.manage'), ['admin.roles.*'], null],
            ['admin.activity.index', 'bi-clock-history', 'Registo de actividade', $u->can('activity.view'), ['admin.activity.*'], null],
        ],
    ];
@endphp
<!doctype html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Painel') — CMS {{ setting('site_short_name', 'UON') }}</title>
<link rel="icon" href="{{ \App\Support\Settings::faviconUrl() }}" type="image/png">
<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/variables.css') }}" rel="stylesheet">
<link href="{{ asset('css/components.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin.css') }}?v=1" rel="stylesheet">
@stack('head')
</head>
<body class="admin-body">
<a class="visually-hidden-focusable" href="#admin-main">Saltar para o conteúdo</a>

<header class="adm-topbar">
  <div class="container-fluid px-3 px-lg-4 d-flex align-items-center gap-3">
    <button class="btn btn-sm text-white d-lg-none p-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#admNav" aria-controls="admNav" aria-label="Abrir menu de administração"><i class="bi bi-list fs-4"></i></button>
    <a class="adm-brand" href="{{ route('admin.dashboard') }}">
      <img src="{{ \App\Support\Settings::logoUrl() }}" alt="">
      <span>CMS<small>{{ setting('site_name') }}</small></span>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <a class="adm-link d-none d-sm-inline" href="{{ url('/') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Ver website</a>
      <div class="dropdown">
        <button class="btn btn-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="u-avatar" aria-hidden="true">{{ $u->initials() }}</span><span class="d-none d-md-inline">{{ \Illuminate\Support\Str::limit($u->name, 22) }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li class="px-3 py-2 small text-muted">{{ $u->email }}<br><strong class="text-dark">{{ $u->roleLabel() }}</strong></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person"></i> O meu perfil</a></li>
          <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}#password"><i class="bi bi-key"></i> Alterar palavra-passe</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="post" action="{{ route('logout') }}">@csrf
              <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Terminar sessão</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>
</header>

<div class="d-wrap">
  <div class="container-fluid px-3 px-lg-4">
    <div class="row g-4">
      <aside class="col-lg-3 col-xxl-2">
        <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="admNav" aria-label="Menu de administração">
          <div class="offcanvas-header d-lg-none">
            <strong>Menu</strong>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#admNav" aria-label="Fechar"></button>
          </div>
          <div class="offcanvas-body p-lg-0">
            <nav class="d-nav w-100" aria-label="Navegação da administração">
              <div class="d-user">
                <span class="u-avatar" aria-hidden="true">{{ $u->initials() }}</span>
                <div><strong>{{ $u->name }}</strong><small>{{ $u->roleLabel() }}</small></div>
              </div>
              @foreach ($nav as $section => $links)
                @php $visible = array_filter($links, fn ($l) => $l[3]); @endphp
                @if ($visible)
                  <div class="nav-section">{{ $section }}</div>
                  @foreach ($visible as [$route, $icon, $label, $can, $patterns, $badge])
                    <a class="nav-link{{ $is(...$patterns) }}" href="{{ route($route) }}"@if (request()->routeIs(...$patterns)) aria-current="page"@endif><i class="bi {{ $icon }}"></i> {{ $label }}@if ($badge)<span class="badge rounded-pill">{{ $badge }}</span>@endif</a>
                  @endforeach
                @endif
              @endforeach
            </nav>
          </div>
        </div>
      </aside>

      <main class="col-lg-9 col-xxl-10" id="admin-main">
        @foreach (['success' => 'alert-brand-success', 'error' => 'alert-brand-danger', 'status' => 'alert-brand-success'] as $key => $class)
          @if (session($key))
            <div class="alert {{ $class }} alert-dismissible fade show" role="{{ $key === 'error' ? 'alert' : 'status' }}">
              <i class="bi {{ $key === 'error' ? 'bi-exclamation-triangle' : 'bi-check-circle' }}"></i> {{ session($key) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
          @endif
        @endforeach
        @if ($errors->any())
          <div class="alert alert-brand-danger" role="alert">
            <strong><i class="bi bi-exclamation-triangle"></i> Não foi possível guardar. Verifique os campos assinalados:</strong>
            <ul class="mb-0 mt-1 small">
              @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
          </div>
        @endif

        @yield('content')
      </main>
    </div>
  </div>
</div>

{{-- Confirmação de acções destrutivas --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="confirmModalTitle"><i class="bi bi-exclamation-triangle text-danger"></i> Confirmar acção</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body" data-confirm-text>Tem a certeza?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-u-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" data-confirm-ok>Confirmar</button>
      </div>
    </div>
  </div>
</div>

@include('admin.partials.media-picker')

@php $editorCss = [asset('vendor/bootstrap/css/bootstrap.min.css'), asset('css/variables.css'), asset('css/components.css'), asset('css/pages.css'), asset('css/uon.css')]; @endphp
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>window.UON = { csrf: @json(csrf_token()), pickerUrl: @json(route('admin.media.picker')), uploadUrl: @json(route('admin.media.store')), canUpload: @json($u->can('media.upload')), tinymceBase: @json(asset('vendor/tinymce')), contentCss: {!! json_encode($editorCss, JSON_UNESCAPED_SLASHES) !!} };</script>
<script src="{{ asset('js/admin.js') }}?v=1"></script>
@stack('scripts')
</body>
</html>
