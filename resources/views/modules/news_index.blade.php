{{-- Notícias — noticias.html (Template C — Listagem) --}}
<section class="section">
  <div class="container-xl">
    <div class="filter-bar mb-4">
      @if ($categories->isNotEmpty())
        <span class="form-label d-block mb-2" id="newsFilterLabel">Filtrar por categoria</span>
        <div class="d-flex flex-wrap gap-2" role="group" aria-labelledby="newsFilterLabel">
          <a class="filter-chip{{ ! $current ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['categoria' => null, 'page' => null]) }}"@if (! $current) aria-current="true"@endif>Todas</a>
          @foreach ($categories as $cat)
            <a class="filter-chip{{ $current?->id === $cat->id ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['categoria' => $cat->slug, 'page' => null]) }}"@if ($current?->id === $cat->id) aria-current="true"@endif>{{ $cat->name }}</a>
          @endforeach
        </div>
      @endif
      @include('modules.partials.filter-search', ['q' => $q, 'placeholder' => 'Pesquisar notícias...', 'id' => 'newsQ'])
    </div>
    <p class="results-count mb-4">{{ $items->total() }} {{ $items->total() === 1 ? 'resultado' : 'resultados' }}</p>

    @if ($items->isEmpty())
      @include('partials.empty', ['icon' => 'bi-newspaper', 'title' => 'Nenhuma notícia encontrada', 'text' => $q || $current ? 'Não existem notícias que correspondam aos filtros seleccionados.' : 'Ainda não foram publicadas notícias.'])
    @else
      <div class="row g-4">
        @foreach ($items as $item)
          <div class="col-md-6 col-lg-4">
            @include('modules.partials.news-card', ['item' => $item])
          </div>
        @endforeach
      </div>
      {{ $items->links('partials.pagination', ['label' => 'Paginação de notícias']) }}
    @endif
  </div>
</section>
