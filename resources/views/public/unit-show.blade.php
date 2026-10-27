@extends('layouts.public')

{{-- Unidade orgânica — sobre.html (texto + coluna lateral), departamentos.html (card-directory),
     oferta-formativa.html (card-course) e o bloco "Localização + Contactos" do instituicao.html (Universidades.ao). --}}
@php
    $inst = \App\Models\Page::where('path', 'instituicao')->first();
    $listPage = \App\Models\Page::where('path', $unit->type === 'orgao' ? 'instituicao/orgaos-gestao' : ($unit->type === 'departamento' ? 'ensino/departamentos' : 'instituicao/unidades-organicas'))->first();
    $offer = \App\Models\Page::published()->where('path', 'ensino/oferta-formativa')->first();
    $mapOk = $unit->map_embed_url && preg_match('#^https://(www\.google\.com/maps/embed\?|www\.openstreetmap\.org/export/embed\.html\?)#', $unit->map_embed_url);
    $mapsLink = $unit->location ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($unit->name.', '.$unit->location) : null;
    $phones = array_filter(array_map('trim', preg_split('/[\/;,]/', (string) $unit->phone)));
    $hasContacts = $unit->location || $phones || $unit->email || $unit->head_name;
    $social = \App\Support\Settings::socialLinks();
@endphp

@section('title', $unit->name)
@section('meta_description', (string) ($unit->summary ?: \App\Support\HtmlSanitizer::toText($unit->description, 200)))
@if ($unit->featuredMedia)
  @section('og_image', $unit->featuredMedia->url())
@endif

@section('page_header')
  @include('partials.page-header', [
      'title' => $unit->name,
      'eyebrow' => $unit->typeLabel(),
      'intro' => $unit->summary,
      'breadcrumbs' => array_values(array_filter([
          $inst ? ['label' => $inst->eyebrow ?: $inst->title, 'url' => $inst->url()] : null,
          $listPage ? ['label' => $listPage->title, 'url' => $listPage->url()] : null,
      ])),
  ])
@endsection

@section('content')
<section class="section">
  <div class="container-xl">
    <div class="row gy-5">
      <div class="col-lg-8">
        @if ($unit->is_demo && setting('show_demo_badges'))<p><span class="demo-badge">Conteúdo de exemplo</span></p>@endif
        @if (filled($unit->description))
          <div class="article-body">{!! $unit->description !!}</div>
        @else
          @include('partials.empty', ['icon' => 'bi-hourglass-split', 'title' => 'Informação em preparação', 'text' => 'A apresentação desta unidade será publicada brevemente.', 'boxed' => true])
        @endif
      </div>
      <div class="col-lg-4">
        @if ($unit->featuredMedia)
          @include('partials.media', ['media' => $unit->featuredMedia, 'ratio' => '4/3', 'class' => 'mb-4', 'style' => 'border-radius: var(--radius);', 'alt' => $unit->name])
        @endif
        <div class="card-directory">
          <h3>Em resumo</h3>
          <ul class="mt-3">
            <li><i class="bi bi-bookmark"></i> {{ $unit->typeLabel() }}@if ($unit->short_name) · {{ $unit->short_name }}@endif</li>
            @if ($unit->parent)<li><i class="bi bi-diagram-3"></i> <a href="{{ $unit->parent->url() }}">{{ $unit->parent->name }}</a></li>@endif
            @if ($unit->children->isNotEmpty())<li><i class="bi bi-diagram-2"></i> {{ $unit->children->count() }} {{ $unit->children->count() === 1 ? 'departamento' : 'departamentos' }}</li>@endif
            @if ($courses->isNotEmpty())<li><i class="bi bi-mortarboard"></i> {{ $courses->count() }} {{ $courses->count() === 1 ? 'curso' : 'cursos' }}</li>@endif
            @if ($unit->head_name)<li><i class="bi bi-person-badge"></i> {{ $unit->head_title ? $unit->head_title.': ' : '' }}{{ $unit->head_name }}</li>@endif
          </ul>
          @if ($hasContacts)<a href="#contactos-unidade" class="btn btn-sm btn-outline-brand mt-3">Ver contactos</a>@endif
        </div>
      </div>
    </div>
  </div>
</section>

@if ($unit->children->isNotEmpty())
<section class="section-sm section-alt">
  <div class="container-xl">
    <p class="section-eyebrow">Estrutura</p>
    <h2 class="section-title mb-4">{{ $unit->children->every(fn ($c) => $c->type === 'departamento') ? 'Departamentos' : 'Subunidades' }}</h2>
    <div class="row g-4">
      @foreach ($unit->children as $child)
        <div class="col-md-6 col-lg-4">
          <div class="card-directory position-relative">
            <h3><a href="{{ $child->url() }}" class="text-reset stretched-link">{{ $child->name }}</a></h3>
            @if ($child->summary)<p class="role">{{ $child->summary }}</p>@endif
            @if ($child->head_name)<ul><li><i class="bi bi-person-badge"></i> {{ $child->head_title ? $child->head_title.': ' : '' }}{{ $child->head_name }}</li></ul>@endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@if ($courses->isNotEmpty())
<section class="section">
  <div class="container-xl">
    <div class="row section-head-row align-items-end">
      <div class="col-lg-7">
        <p class="section-eyebrow">Ensino</p>
        <h2 class="section-title">Oferta formativa</h2>
      </div>
      @if ($offer)
        <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
          <a href="{{ $offer->url() }}?unidade={{ $unit->slug }}" class="btn-link-brand">Ver todos os cursos ({{ $courses->count() }}) <i class="bi bi-arrow-right"></i></a>
        </div>
      @endif
    </div>
    <div class="row g-4">
      @foreach ($courses->take(3) as $course)
        <div class="col-md-6 col-lg-4">@include('modules.partials.course-card', ['course' => $course])</div>
      @endforeach
    </div>
    @if ($offer)
      <div class="text-center mt-5">
        <a href="{{ $offer->url() }}?unidade={{ $unit->slug }}" class="btn btn-brand">Ver todos os cursos da {{ $unit->short_name ?: mb_strtolower($unit->typeLabel()) }} <i class="bi bi-arrow-right"></i></a>
      </div>
    @endif
  </div>
</section>
@endif

@if ($hasContacts || $mapOk)
<section class="section-sm section-alt" id="contactos-unidade">
  <div class="container-xl">
    <div class="row g-3">
      <div class="col-lg-7">
        <div class="unit-loc">
          <h2 class="section-title h4 mb-3">Localização</h2>
          <div class="unit-map">
            @if ($mapOk)
              <iframe src="{{ $unit->map_embed_url }}" title="Mapa — {{ $unit->name }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            @else
              <div class="unit-pin"><i class="bi bi-geo-alt-fill"></i><div><strong>{{ $unit->name }}</strong>@if ($unit->location)<small>{{ $unit->location }}</small>@endif</div></div>
            @endif
            @if ($mapsLink)
              <a class="unit-maps-btn" href="{{ $mapsLink }}" target="_blank" rel="noopener"><i class="bi bi-map"></i> Abrir no Google Maps</a>
            @endif
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="unit-contacts">
          <h2 class="h4">Contactos</h2>
          <ul class="list-unstyled unit-contacts-list">
            @if ($unit->location)<li><i class="bi bi-geo-alt"></i><span>{{ $unit->location }}</span></li>@endif
            @if ($phones)<li><i class="bi bi-telephone"></i><span>@foreach ($phones as $p)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $p) }}">{{ $p }}</a>@if (! $loop->last) / @endif @endforeach</span></li>@endif
            @if ($unit->email)<li><i class="bi bi-envelope"></i><a href="mailto:{{ $unit->email }}">{{ $unit->email }}</a></li>@endif
            @if ($unit->head_name)<li><i class="bi bi-person-badge"></i><span>{{ $unit->head_title ? $unit->head_title.': ' : '' }}{{ $unit->head_name }}</span></li>@endif
            @if (setting('office_hours'))<li><i class="bi bi-clock"></i><span>{{ setting('office_hours') }}</span></li>@endif
          </ul>
          @if ($social)
            <hr>
            <div class="unit-contacts-follow">Siga a Universidade</div>
            <div class="d-flex flex-wrap gap-2 unit-social">
              @foreach ($social as $s)<a href="{{ $s['url'] }}" target="_blank" rel="noopener" aria-label="{{ $s['label'] }}"><i class="bi {{ $s['icon'] }}"></i></a>@endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
@endif

@if ($listPage)
<section class="section-sm">
  <div class="container-xl">
    <a href="{{ $listPage->url() }}" class="btn-link-brand"><i class="bi bi-arrow-left"></i> Voltar a {{ mb_strtolower($listPage->title) }}</a>
  </div>
</section>
@endif
@endsection
