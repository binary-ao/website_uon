@extends('layouts.public')

{{-- Notícia / Comunicado — noticia-detalhe.html (Template D) --}}
@php
    $isAnnouncement = $news->type === 'announcement';
    $listPath = $isAnnouncement ? 'comunicacao/comunicados' : 'comunicacao/noticias';
    $listPage = \App\Models\Page::where('path', $listPath)->first();
    $commPage = \App\Models\Page::where('path', 'comunicacao')->first();
    $shareUrl = urlencode($news->url());
@endphp

@section('title', $news->meta_title ?: $news->title)
@section('meta_description', (string) ($news->meta_description ?: $news->excerpt))
@section('og_type', 'article')
@if ($news->featuredMedia)
  @section('og_image', $news->featuredMedia->url())
@endif

@push('head')
<meta property="article:published_time" content="{{ $news->published_at?->toAtomString() }}">
<meta property="article:modified_time" content="{{ $news->updated_at?->toAtomString() }}">
@if ($news->category)<meta property="article:section" content="{{ $news->category->name }}">@endif
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $news->title,
    'datePublished' => $news->published_at?->toAtomString(),
    'dateModified' => $news->updated_at?->toAtomString(),
    'image' => $news->featuredMedia?->url(),
    'publisher' => ['@type' => 'CollegeOrUniversity', 'name' => setting('site_name')],
    'mainEntityOfPage' => $news->url(),
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
        <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($news->title, 60) }}</li>
      </ol>
    </nav>
  </div>
</div>
@endsection

@section('content')
<article class="section">
  <div class="container-xl">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="article-header">
          <span class="card-category">{{ $news->category?->name ?? $news->typeLabel() }}</span>
          @if ($news->is_demo && setting('show_demo_badges')) <span class="demo-badge ms-1">Conteúdo de exemplo</span>@endif
          <h1 class="mt-2">{{ $news->title }}</h1>
          <div class="article-meta">
            <span><i class="bi bi-calendar3"></i> <time datetime="{{ $news->published_at->toDateString() }}">{{ $news->published_at->translatedFormat('d \d\e F \d\e Y') }}</time></span>
            @if ($news->displayAuthor())<span><i class="bi bi-person"></i> {{ $news->displayAuthor() }}</span>@endif
          </div>
        </div>

        @if ($news->featuredMedia)
          @include('partials.media', ['media' => $news->featuredMedia, 'ratio' => '16/9', 'class' => 'mb-4', 'style' => 'border-radius: var(--radius-lg);', 'variant' => null])
        @endif

        @if ($news->excerpt && ! $news->body)
          <p class="article-body">{{ $news->excerpt }}</p>
        @endif
        <div class="article-body">{!! $news->body !!}</div>

        @include('public.partials.attached-documents', ['documents' => $news->documents])

        @if ($news->tags->isNotEmpty())
          <div class="tag-list mt-4" aria-label="Etiquetas">
            @foreach ($news->tags as $tag)
              <a href="{{ route('search', ['q' => $tag->name]) }}">#{{ $tag->name }}</a>
            @endforeach
          </div>
        @endif

        <div class="d-flex align-items-center justify-content-between border-top pt-4 mt-4 flex-wrap gap-3">
          <div class="share-links">
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Partilhar no Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Partilhar no LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="mailto:?subject={{ rawurlencode($news->title) }}&amp;body={{ $shareUrl }}" aria-label="Partilhar por email"><i class="bi bi-envelope"></i></a>
          </div>
          @if ($listPage)
            <a href="{{ $listPage->url() }}" class="btn-link-brand"><i class="bi bi-arrow-left"></i> Voltar {{ $isAnnouncement ? 'aos comunicados' : 'às notícias' }}</a>
          @endif
        </div>
      </div>
    </div>
  </div>
</article>

@if ($related->isNotEmpty())
<section class="section-sm section-alt">
  <div class="container-xl">
    <h2 class="section-title h4 mb-4">{{ $isAnnouncement ? 'Outros comunicados' : 'Notícias relacionadas' }}</h2>
    <div class="row g-4">
      @foreach ($related as $item)
        <div class="col-md-4">
          @include('modules.partials.news-card', ['item' => $item, 'compact' => true])
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
