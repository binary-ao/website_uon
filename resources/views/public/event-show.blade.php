@extends('layouts.public')

{{-- Evento — evento-detalhe.html (Template D) --}}
@php
    $listPage = \App\Models\Page::where('path', 'comunicacao/eventos')->first();
    $commPage = \App\Models\Page::where('path', 'comunicacao')->first();
    $registration = \App\Support\SafeUrl::resolve($event->registration_url);
@endphp

@section('title', $event->meta_title ?: $event->title)
@section('meta_description', (string) ($event->meta_description ?: $event->excerpt))
@if ($event->featuredMedia)
  @section('og_image', $event->featuredMedia->url())
@endif

@push('head')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $event->title,
    'startDate' => $event->starts_at->toAtomString(),
    'endDate' => $event->ends_at?->toAtomString(),
    'location' => $event->location ? ['@type' => 'Place', 'name' => $event->location] : null,
    'organizer' => ['@type' => 'CollegeOrUniversity', 'name' => setting('site_name'), 'url' => url('/')],
    'image' => $event->featuredMedia?->url(),
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endpush

@section('page_header')
<div class="breadcrumb-bar py-2">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
        @if ($commPage)<li class="breadcrumb-item"><a href="{{ $commPage->url() }}">{{ $commPage->title }}</a></li>@endif
        @if ($listPage)<li class="breadcrumb-item"><a href="{{ $listPage->url() }}">{{ $listPage->title }}</a></li>@endif
        <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($event->title, 60) }}</li>
      </ol>
    </nav>
  </div>
</div>
@endsection

@section('content')
<article class="section">
  <div class="container-xl">
    <div class="row gy-5">
      <div class="col-lg-8">
        @if ($event->category)<span class="card-category">{{ $event->category->name }}</span>@endif
        @if ($event->is_demo && setting('show_demo_badges')) <span class="demo-badge ms-1">Conteúdo de exemplo</span>@endif
        <h1>{{ $event->title }}</h1>
        @if ($event->isPast())
          <div class="alert alert-secondary small mt-3 mb-0" role="note"><i class="bi bi-info-circle"></i> Este evento já decorreu.</div>
        @endif
        @include('partials.media', ['media' => $event->featuredMedia, 'ratio' => '16/9', 'class' => 'my-4', 'style' => 'border-radius: var(--radius-lg);', 'icon' => 'bi-calendar-event', 'variant' => null])
        <div class="article-body">
          @if ($event->body)
            {!! $event->body !!}
          @elseif ($event->excerpt)
            <p>{{ $event->excerpt }}</p>
          @endif
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card-directory">
          <h2 class="h3" style="font-size:1rem">Detalhes do Evento</h2>
          <ul class="mt-3">
            <li><i class="bi bi-calendar3"></i> {{ $event->dateRangeLabel() }}</li>
            @if ($event->timeLabel())<li><i class="bi bi-clock"></i> {{ $event->timeLabel() }}</li>@endif
            @if ($event->location)<li><i class="bi bi-geo-alt"></i> {{ $event->location }}</li>@endif
            @if ($event->registration_info)<li><i class="bi bi-people"></i> {{ $event->registration_info }}</li>@endif
          </ul>
          @if ($registration && ! $event->isPast())
            <a href="{{ $registration }}" class="btn btn-brand w-100 mt-3"@if (\App\Support\SafeUrl::isExternal($event->registration_url)) target="_blank" rel="noopener"@endif>Inscrever-me</a>
          @endif
        </div>
      </div>
    </div>
    @if ($listPage)
      <div class="mt-5">
        <a href="{{ $listPage->url() }}" class="btn-link-brand"><i class="bi bi-arrow-left"></i> Voltar aos eventos</a>
      </div>
    @endif
  </div>
</article>
@endsection
