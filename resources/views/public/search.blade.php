@extends('layouts.public')

{{-- Pesquisa global — pesquisa.html --}}
@section('title', $q ? 'Pesquisa: '.$q : 'Pesquisa')
@section('noindex', '1')

@section('page_header')
<div class="search-hero">
  <div class="container-xl">
    <p class="section-eyebrow">Pesquisa</p>
    <h1 class="h2 mb-3">Pesquisar no website</h1>
    <form class="d-flex gap-2" style="max-width: 40rem;" role="search" method="get" action="{{ route('search') }}">
      <label for="searchQ" class="visually-hidden">Termos de pesquisa</label>
      <input class="form-control form-control-lg" type="search" id="searchQ" name="q" value="{{ $q }}" placeholder="Pesquisar cursos, notícias, serviços..." maxlength="100" required minlength="2">
      @foreach ($types as $t)<input type="hidden" name="tipo[]" value="{{ $t }}">@endforeach
      <button class="btn btn-brand" type="submit"><i class="bi bi-search"></i> Pesquisar</button>
    </form>
  </div>
</div>
@endsection

@section('content')
<section class="section">
  <div class="container-xl">
    <div class="row g-5">
      <div class="col-lg-3">
        <form class="filter-bar" method="get" action="{{ route('search') }}">
          <input type="hidden" name="q" value="{{ $q }}">
          <fieldset>
            <legend class="form-label d-block mb-2">Filtrar por tipo de conteúdo</legend>
            <div class="d-flex flex-column gap-2">
              @foreach ($typeOptions as $key => $label)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="tipo[]" value="{{ $key }}" id="t_{{ $key }}" @checked(! $types || in_array($key, $types, true))>
                  <label class="form-check-label" for="t_{{ $key }}">{{ $label }}</label>
                </div>
              @endforeach
            </div>
          </fieldset>
          <button type="submit" class="btn btn-sm btn-outline-brand mt-3 w-100">Aplicar filtros</button>
        </form>
      </div>
      <div class="col-lg-9">
        @if ($results === null)
          @include('partials.empty', ['icon' => 'bi-search', 'title' => 'Pesquise no website', 'text' => 'Escreva pelo menos dois caracteres para pesquisar cursos, notícias, eventos, documentos e páginas.'])
        @elseif ($results->isEmpty())
          @include('partials.empty', ['icon' => 'bi-search', 'title' => 'Sem resultados', 'text' => 'Não foram encontrados resultados para a sua pesquisa. Tente utilizar outras palavras-chave.'])
        @else
          <p class="results-count mb-4">{{ $results->total() }} {{ $results->total() === 1 ? 'resultado' : 'resultados' }} para "<strong>{{ $q }}</strong>"</p>
          @php $hl = fn ($text) => preg_replace('/('.preg_quote(e($q), '/').')/iu', '<mark>$1</mark>', e($text)); @endphp
          @foreach ($results as $r)
            <div class="search-result-item{{ $loop->last ? ' border-bottom-0' : '' }}">
              <span class="result-type">{{ $r->label }}</span>
              <h3>@if ($r->url)<a href="{{ $r->url }}">{!! $hl($r->title) !!}</a>@else{!! $hl($r->title) !!}@endif</h3>
              @if ($r->url)<div class="result-url">{{ preg_replace('#^https?://#', '', $r->url) }}</div>@endif
              @if ($r->snippet)<p class="text-muted-brand mb-0">{!! $hl($r->snippet) !!}</p>@endif
            </div>
          @endforeach
          {{ $results->links('partials.pagination', ['label' => 'Paginação de resultados']) }}
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
