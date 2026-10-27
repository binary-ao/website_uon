@extends('layouts.public')

{{-- Páginas geridas no CMS. O layout escolhido corresponde a um tipo de página do website_template:
     standard/sidebar (B — Institucional), módulos (C — Listagem, E — Directório, F — Galeria, G — Contactos). --}}

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', (string) ($page->meta_description ?: $page->excerpt))
@if ($page->featuredMedia)
  @section('og_image', $page->featuredMedia->url())
@endif
@if (request()->query() && $page->isModule())
  @section('canonical', $page->url())
@endif

@section('page_header')
  @include('partials.page-header', [
      'title' => $page->title,
      'eyebrow' => $page->eyebrow,
      'intro' => $page->excerpt,
      'breadcrumbs' => collect($page->ancestors())->map(fn ($a) => ['label' => $a->title, 'url' => $a->url()])->all(),
  ])
@endsection

@section('content')
  @php
      $blocks = $page->blocks ?? [];
      $hasBody = filled($page->body);
      $docs = $page->documents ?? collect();
      $isEmpty = ! $page->isModule() && ! $hasBody && empty($blocks) && $docs->isEmpty()
          && ! ($page->layout === 'sidebar' && ($page->featuredMedia || $page->sidebarItemList()));
  @endphp

  @if ($page->isModule())
    @if ($hasBody)
      <section class="section pb-0">
        <div class="container-xl">
          <div class="row justify-content-center">
            <div class="col-lg-9"><div class="article-body">{!! $page->body !!}</div></div>
          </div>
        </div>
      </section>
    @endif
    @include('modules.'.$page->layout)
  @elseif ($page->layout === 'sidebar')
    <section class="section">
      <div class="container-xl">
        <div class="row gy-5">
          <div class="col-lg-8">
            @if ($hasBody)
              <div class="article-body">{!! $page->body !!}</div>
            @else
              @include('partials.empty', ['icon' => 'bi-hourglass-split', 'title' => 'Conteúdo em preparação', 'text' => 'Esta página está a ser actualizada pela Universidade. Volte a consultá-la brevemente.', 'boxed' => true])
            @endif
            @include('public.partials.attached-documents', ['documents' => $docs])
          </div>
          <div class="col-lg-4">
            @if ($page->featuredMedia)
              @include('partials.media', ['media' => $page->featuredMedia, 'ratio' => '4/5', 'class' => 'mb-4', 'style' => 'border-radius: var(--radius);'])
            @endif
            @if ($page->sidebarItemList())
              <div class="card-directory">
                @if ($page->sidebar_title)<h3>{{ $page->sidebar_title }}</h3>@endif
                <ul class="mt-3">
                  @foreach ($page->sidebarItemList() as $line)
                    @php [$icon, $text] = str_contains($line, '|') ? array_map('trim', explode('|', $line, 2)) : ['bi-check2', $line]; @endphp
                    <li><i class="bi {{ preg_match('/^bi-[a-z0-9-]+$/', $icon) ? $icon : 'bi-check2' }}"></i> {{ $text }}</li>
                  @endforeach
                </ul>
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>
  @else
    @if ($hasBody || $docs->isNotEmpty())
      <section class="section">
        <div class="container-xl">
          <div class="row justify-content-center">
            <div class="col-lg-8">
              @if ($hasBody)<div class="article-body">{!! $page->body !!}</div>@endif
              @include('public.partials.attached-documents', ['documents' => $docs])
            </div>
          </div>
        </div>
      </section>
    @endif
  @endif

  @include('public.partials.blocks', ['blocks' => $blocks, 'afterContent' => $hasBody || $page->isModule() || $page->layout === 'sidebar' || $docs->isNotEmpty()])

  @if ($isEmpty)
    <section class="section">
      <div class="container-xl">
        @include('partials.empty', ['icon' => 'bi-hourglass-split', 'title' => 'Conteúdo em preparação', 'text' => 'Esta página está a ser actualizada pela Universidade. Volte a consultá-la brevemente.', 'boxed' => true])
      </div>
    </section>
  @endif
@endsection
