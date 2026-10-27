@extends('layouts.public')

{{-- Página inicial — estrutura de website_template/index.html (Template A — Landing).
     Cada secção só é apresentada quando tem conteúdo real no CMS. --}}

@section('section', 'inicio')
@section('meta_description', (string) (setting('site_description')))

@push('head')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'CollegeOrUniversity',
    'name' => setting('site_name'),
    'alternateName' => setting('site_short_name'),
    'url' => url('/'),
    'logo' => \App\Support\Settings::logoUrl(),
    'email' => setting('contact_email'),
    'telephone' => setting('contact_phone'),
    'address' => setting('contact_address') ? ['@type' => 'PostalAddress', 'streetAddress' => setting('contact_address'), 'addressCountry' => 'AO'] : null,
    'sameAs' => array_column(\App\Support\Settings::socialLinks(), 'url') ?: null,
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endpush

@section('content')
  @php
      $heroMedia = \App\Support\Settings::media('hero_media_id');
      $aboutMedia = \App\Support\Settings::media('about_media_id');
      $primaryUrl = \App\Support\SafeUrl::resolve(setting('hero_primary_url'));
      $secondaryUrl = \App\Support\SafeUrl::resolve(setting('hero_secondary_url'));
      $offerPage = \App\Models\Page::published()->where('path', 'ensino/oferta-formativa')->first();
      $newsPage = \App\Models\Page::published()->where('path', 'comunicacao/noticias')->first();
      $eventsPage = \App\Models\Page::published()->where('path', 'comunicacao/eventos')->first();
      $partnersPage = \App\Models\Page::published()->where('path', 'instituicao/parceiros')->first();
  @endphp

  @if ($slides->isNotEmpty())
    @include('public.partials.hero-carousel', ['slides' => $slides])
  @else
  <!-- HERO (sem diapositivos: banner do template, gerido em Configurações → Página inicial) -->
  <section class="hero">
    <div class="container-xl position-relative">
      <div class="row align-items-center gy-5">
        <div class="col-lg-7">
          @if (setting('hero_eyebrow'))<p class="hero-eyebrow">{{ setting('hero_eyebrow') }}</p>@endif
          <h1>{{ setting('hero_title', setting('site_name')) }}@if (setting('hero_title_highlight')) <em>{{ setting('hero_title_highlight') }}</em>@endif</h1>
          @if (setting('hero_lead'))
            <p class="lead">{{ setting('hero_lead') }}</p>
          @endif
          @if (($primaryUrl && setting('hero_primary_label')) || ($secondaryUrl && setting('hero_secondary_label')))
            <div class="hero-actions d-flex flex-wrap gap-3">
              @if ($primaryUrl && setting('hero_primary_label'))
                <a href="{{ $primaryUrl }}" class="btn btn-brand btn-lg">{{ setting('hero_primary_label') }}</a>
              @endif
              @if ($secondaryUrl && setting('hero_secondary_label'))
                <a href="{{ $secondaryUrl }}" class="btn btn-outline-light-brand btn-lg">{{ setting('hero_secondary_label') }}</a>
              @endif
            </div>
          @endif
          @if ($stats)
            <div class="hero-stats">
              @foreach ($stats as $stat)
                <div>
                  <div class="stat-num">{{ $stat['value'] }}</div>
                  <div class="stat-label">{{ $stat['label'] }}</div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
        <div class="col-lg-5">
          <div class="hero-visual">
            @include('partials.media', ['media' => $heroMedia, 'ratio' => '4/5', 'dark' => true, 'icon' => 'bi-mortarboard', 'label' => 'Campus · '.setting('site_name'), 'variant' => null, 'alt' => setting('site_name')])
          </div>
        </div>
      </div>
    </div>
  </section>
  @endif

  @if ($notices->isNotEmpty())
  <!-- DESTAQUES / AVISOS -->
  <div class="notice-strip py-3">
    <div class="container-xl">
      <div class="d-flex flex-wrap align-items-center justify-content-center gap-4">
        @foreach ($notices as $notice)
          @if ($notice->href())
            <a href="{{ $notice->href() }}" class="notice-item text-decoration-none"><i class="bi {{ $notice->icon ?: 'bi-megaphone' }}"></i> {{ $notice->text }}</a>
          @else
            <div class="notice-item"><i class="bi {{ $notice->icon ?: 'bi-megaphone' }}"></i> {{ $notice->text }}</div>
          @endif
        @endforeach
      </div>
    </div>
  </div>
  @endif

  @if (setting('about_title') || setting('about_text'))
  <!-- APRESENTAÇÃO DA INSTITUIÇÃO -->
  <section class="section">
    <div class="container-xl">
      <div class="row align-items-center gy-5">
        <div class="col-lg-6">
          <div class="about-figure-wrap">
            <div class="about-media">
              @include('partials.media', ['media' => $aboutMedia, 'ratio' => '4/3', 'icon' => 'bi-building', 'label' => setting('site_name')])
            </div>
            @if (setting('about_figure_number'))
              <div class="about-figure-tag">
                <div class="num">{{ setting('about_figure_number') }}</div>
                <div class="small text-muted-brand">{{ setting('about_figure_label') }}</div>
              </div>
            @endif
          </div>
        </div>
        <div class="col-lg-6">
          @if (setting('about_eyebrow'))<p class="section-eyebrow">{{ setting('about_eyebrow') }}</p>@endif
          @if (setting('about_title'))<h2 class="section-title">{{ setting('about_title') }}</h2>@endif
          @foreach (preg_split('/\n\s*\n/', (string) setting('about_text')) as $i => $para)
            @if (trim($para) !== '')
              <p class="{{ $i === 0 ? 'section-intro' : 'text-muted-brand' }} mb-4">{{ trim($para) }}</p>
            @endif
          @endforeach
          @if (setting('about_link_label') && \App\Support\SafeUrl::resolve(setting('about_link_url')))
            <a href="{{ \App\Support\SafeUrl::resolve(setting('about_link_url')) }}" class="btn-link-brand">
              {{ setting('about_link_label') }} <i class="bi bi-arrow-right"></i>
            </a>
          @endif
        </div>
      </div>
    </div>
  </section>
  @endif

  @if ($courses->isNotEmpty())
  <!-- OFERTA FORMATIVA -->
  <section class="section section-alt">
    <div class="container-xl">
      <div class="row section-head-row align-items-end">
        <div class="col-lg-7">
          <p class="section-eyebrow">Ensino</p>
          <h2 class="section-title">{{ setting('courses_title') }}</h2>
          @if (setting('courses_intro'))<p class="section-intro">{{ setting('courses_intro') }}</p>@endif
        </div>
        @if ($offerPage)
          <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
            <a href="{{ $offerPage->url() }}" class="btn-link-brand">Ver toda a oferta formativa <i class="bi bi-arrow-right"></i></a>
          </div>
        @endif
      </div>
      <div class="row g-4">
        @foreach ($courses as $course)
          <div class="col-md-6 col-lg-3">
            @include('modules.partials.course-card', ['course' => $course])
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  @if ($stats)
  <!-- NÚMEROS DA INSTITUIÇÃO -->
  <section class="section py-5 bg-ink">
    <div class="container-xl">
      <div class="row g-4 justify-content-center">
        @foreach ($stats as $stat)
          <div class="col-6 col-lg-3">
            <div class="stat-block">
              <div class="num text-white">@if (str_starts_with($stat['value'], '+'))<span class="plus">+</span>{{ substr($stat['value'], 1) }}@else{{ $stat['value'] }}@endif</div>
              <div class="label text-white-50">{{ $stat['label'] }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  @if ($news->isNotEmpty())
  <!-- NOTÍCIAS -->
  <section class="section">
    <div class="container-xl">
      <div class="row section-head-row align-items-end">
        <div class="col-lg-7">
          <p class="section-eyebrow">Comunicação</p>
          <h2 class="section-title">Notícias</h2>
        </div>
        @if ($newsPage)
          <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
            <a href="{{ $newsPage->url() }}" class="btn-link-brand">Ver todas as notícias <i class="bi bi-arrow-right"></i></a>
          </div>
        @endif
      </div>
      <div class="row g-4">
        @foreach ($news as $i => $item)
          @if ($i === 0)
            <div class="col-lg-6">
              @include('modules.partials.news-card', ['item' => $item, 'featured' => true])
            </div>
          @else
            <div class="col-lg-3 col-md-6">
              @include('modules.partials.news-card', ['item' => $item, 'compact' => true])
            </div>
          @endif
        @endforeach
      </div>
    </div>
  </section>
  @endif

  @if ($research || setting('research_text'))
  <!-- INVESTIGAÇÃO / CIÊNCIA -->
  <section class="section-sm">
    <div class="container-xl">
      <div class="research-panel">
        <div class="row align-items-center">
          <div class="col-lg-7 position-relative">
            <p class="section-eyebrow" style="color: var(--primary);">Investigação</p>
            <h2>{{ setting('research_title') }}</h2>
            @if (setting('research_text'))<p>{{ setting('research_text') }}</p>@endif
            @if ($research)
              <div class="research-link-list">
                @foreach ($research as $link)
                  <a href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ $link['label'] }} <i class="bi bi-box-arrow-up-right"></i></a>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
  @endif

  @if ($events->isNotEmpty())
  <!-- EVENTOS -->
  <section class="section section-alt">
    <div class="container-xl">
      <div class="row section-head-row align-items-end">
        <div class="col-lg-7">
          <p class="section-eyebrow">Comunicação</p>
          <h2 class="section-title">Próximos Eventos</h2>
        </div>
        @if ($eventsPage)
          <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
            <a href="{{ $eventsPage->url() }}" class="btn-link-brand">Ver todos os eventos <i class="bi bi-arrow-right"></i></a>
          </div>
        @endif
      </div>
      <div class="row g-4">
        @foreach ($events as $event)
          <div class="col-lg-4">
            @include('modules.partials.event-card', ['event' => $event, 'showTime' => false])
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  @if (setting('cta_title'))
  <!-- CANDIDATURAS / CTA -->
  <section class="section">
    <div class="container-xl">
      @include('blocks.cta', ['block' => ['data' => [
          'title' => setting('cta_title'), 'text' => setting('cta_text'),
          'button_label' => setting('cta_button_label'), 'button_url' => setting('cta_button_url'),
      ]], 'bare' => true])
    </div>
  </section>
  @endif

  @if ($partners->isNotEmpty())
  <!-- PARCEIROS -->
  <section class="section-sm section-alt">
    <div class="container-xl">
      <p class="section-eyebrow justify-content-center d-flex text-center">Parceiros</p>
      <h2 class="section-title text-center mb-4">{{ setting('partners_title') }}</h2>
      @include('modules.partials.partner-grid', ['items' => $partners])
      @if ($partnersPage)
        <div class="text-center mt-4">
          <a href="{{ $partnersPage->url() }}" class="btn-link-brand justify-content-center">Ver todos os parceiros <i class="bi bi-arrow-right"></i></a>
        </div>
      @endif
    </div>
  </section>
  @endif
@endsection
