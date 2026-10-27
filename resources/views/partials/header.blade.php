{{--
    HEADER — convertido de website_template/components/header.html.
    Estrutura, classes, ordem e comportamento originais preservados:
    topbar + header principal (logo, navbar, mega menu, dropdowns, pesquisa, CTA) + offcanvas móvel.
    Conteúdos (contactos, links, menus, logótipo) vêm do CMS.
--}}
@php
    use App\Services\MenuRepository;
    $siteName = setting('site_name');
    $mainItems = $menus->tree('main');
    $topbarItems = $menus->tree('topbar');
    $social = \App\Support\Settings::socialLinks();
    $logo = \App\Support\Settings::logoUrl();
    $ctaLabel = setting('header_cta_label');
    $ctaUrl = \App\Support\SafeUrl::resolve(setting('header_cta_url'));
    $extIcon = fn ($item) => $item->isExternal() ? ' target="_blank" rel="noopener"' : ($item->open_in_new_tab ? ' target="_blank" rel="noopener"' : '');
@endphp
<!-- ===================== TOPBAR ===================== -->
<div class="topbar d-none d-md-block">
  <div class="container-xl d-flex justify-content-between align-items-center py-2">
    <div class="d-flex align-items-center gap-4 topbar-links">
      @if (setting('contact_phone'))
        <span class="topbar-item"><i class="bi bi-telephone"></i> <span>{{ setting('contact_phone') }}</span></span>
      @endif
      @if (setting('contact_email'))
        <span class="topbar-item"><i class="bi bi-envelope"></i> <span>{{ setting('contact_email') }}</span></span>
      @endif
      @if (setting('office_hours'))
        <span class="topbar-item d-none d-lg-inline-flex"><i class="bi bi-clock"></i> <span>{{ setting('office_hours') }}</span></span>
      @endif
    </div>
    <div class="d-flex align-items-center gap-3">
      @foreach ($topbarItems as $item)
        <a href="{{ $item->href() }}" class="topbar-item"{!! $extIcon($item) !!}>@if ($item->icon)<i class="bi {{ $item->icon }}"></i> @endif{{ $item->label }}</a>
      @endforeach
      @if ($social)
        <div class="topbar-social d-flex">
          @foreach ($social as $s)
            <a href="{{ $s['url'] }}" target="_blank" rel="noopener" aria-label="{{ $s['label'] }}"><i class="bi {{ $s['icon'] }}"></i></a>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>

<!-- ===================== HEADER PRINCIPAL ===================== -->
<header class="site-header">
  <div class="container-xl">
    <nav class="navbar navbar-expand-lg py-2" aria-label="Navegação principal">
      <a class="brand-logo" href="{{ url('/') }}">
        <img src="{{ $logo }}" alt="{{ $siteName }} — página inicial" width="134" height="64">
      </a>

      <div class="d-flex align-items-center gap-2 order-lg-3 ms-auto">
        <button type="button" class="btn btn-sm btn-outline-brand rounded-circle p-2 lh-1" id="searchToggle" aria-expanded="false" aria-controls="searchFlyout" aria-label="Abrir pesquisa">
          <i class="bi bi-search"></i>
        </button>
        @if ($ctaLabel && $ctaUrl)
          <a href="{{ $ctaUrl }}" class="btn btn-brand header-cta d-none d-lg-inline-flex">{{ $ctaLabel }}</a>
        @endif
        <button class="navbar-toggler border-0 fs-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Abrir menu">
          <i class="bi bi-list"></i>
        </button>
      </div>

      <div class="collapse navbar-collapse order-lg-2 justify-content-center d-none d-lg-flex">
        <ul class="navbar-nav main-nav align-items-lg-center">
          @foreach ($mainItems as $item)
            @php $active = MenuRepository::isActive($item); @endphp
            @if ($item->display === 'mega')
              @php $cols = $item->visibleChildren; $span = max(3, intdiv(12, max(1, $cols->count()))); @endphp
              <li class="nav-item dropdown mega">
                <a class="nav-link dropdown-toggle{{ $active ? ' active' : '' }}" href="{{ $item->href() }}" role="button" data-bs-toggle="dropdown" aria-expanded="false"@if ($active) aria-current="page"@endif>{{ $item->label }}</a>
                <div class="dropdown-menu mega-menu">
                  <div class="row g-4">
                    @foreach ($cols as $col)
                      <div class="col-lg-{{ $span }}">
                        <div class="mega-menu-col-title">{{ $col->label }}</div>
                        <ul class="list-unstyled">
                          @foreach ($col->visibleChildren as $link)
                            @if ($link->display !== 'divider')
                              <li><a class="mega-link" href="{{ $link->href() }}"{!! $extIcon($link) !!}>{{ $link->label }}@if ($link->isExternal()) <i class="bi bi-box-arrow-up-right"></i>@endif</a></li>
                            @endif
                          @endforeach
                        </ul>
                      </div>
                    @endforeach
                  </div>
                </div>
              </li>
            @elseif ($item->display === 'dropdown')
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle{{ $active ? ' active' : '' }}" href="{{ $item->href() }}" role="button" data-bs-toggle="dropdown" aria-expanded="false"@if ($active) aria-current="page"@endif>{{ $item->label }}</a>
                <ul class="dropdown-menu simple-dropdown">
                  @foreach ($item->visibleChildren as $link)
                    @if ($link->display === 'divider')
                      <li><hr class="dropdown-divider"></li>
                    @else
                      <li><a class="dropdown-item" href="{{ $link->href() }}"{!! $extIcon($link) !!}>{{ $link->label }}@if ($link->isExternal()) <i class="bi bi-box-arrow-up-right small ms-1"></i>@endif</a></li>
                    @endif
                  @endforeach
                </ul>
              </li>
            @elseif ($item->display !== 'divider')
              <li class="nav-item">
                <a class="nav-link{{ $active ? ' active' : '' }}" href="{{ $item->href() }}"{!! $extIcon($item) !!}@if ($active) aria-current="page"@endif>{{ $item->label }}</a>
              </li>
            @endif
          @endforeach
        </ul>
      </div>
    </nav>
  </div>

  <!-- Flyout de pesquisa -->
  <div class="search-flyout d-none" id="searchFlyout">
    <div class="container-xl">
      <form class="d-flex gap-2" role="search" action="{{ route('search') }}" method="get">
        <input class="form-control form-control-lg" type="search" name="q" placeholder="{{ setting('search_placeholder') }}" aria-label="Pesquisar" maxlength="120">
        <button class="btn btn-brand" type="submit"><i class="bi bi-search"></i> Pesquisar</button>
      </form>
    </div>
  </div>
</header>

<!-- ===================== OFFCANVAS MOBILE ===================== -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
  <div class="offcanvas-header border-bottom">
    <img src="{{ $logo }}" alt="{{ $siteName }}" class="offcanvas-logo" id="mobileNavLabel">
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
  </div>
  <div class="offcanvas-body p-0">
    <nav class="offcanvas-nav" aria-label="Navegação móvel">
      @foreach ($mainItems as $item)
        @if (in_array($item->display, ['mega', 'dropdown'], true))
          @php
              $links = $item->display === 'mega'
                  ? $item->visibleChildren->flatMap(fn ($c) => $c->visibleChildren)
                  : $item->visibleChildren;
          @endphp
          <div class="accordion accordion-flush" id="acc{{ $item->id }}">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc{{ $item->id }}b" aria-expanded="false" aria-controls="acc{{ $item->id }}b">{{ $item->mobile_label ?: $item->label }}</button>
              </h2>
              <div id="acc{{ $item->id }}b" class="accordion-collapse collapse" data-bs-parent="#acc{{ $item->id }}">
                <div class="accordion-body">
                  @foreach ($links as $link)
                    @if ($link->display !== 'divider')
                      <a href="{{ $link->href() }}"{!! $extIcon($link) !!}>{{ $link->mobile_label ?: $link->label }}@if ($link->isExternal()) <i class="bi bi-box-arrow-up-right small"></i>@endif</a>
                    @endif
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @elseif ($item->display !== 'divider')
          <a class="nav-link" href="{{ $item->href() }}"{!! $extIcon($item) !!}>{{ $item->mobile_label ?: $item->label }}</a>
        @endif
      @endforeach
    </nav>
    @if ($ctaLabel && $ctaUrl)
      <div class="p-3">
        <a href="{{ $ctaUrl }}" class="btn btn-brand w-100">{{ $ctaLabel }}</a>
      </div>
    @endif
  </div>
</div>
