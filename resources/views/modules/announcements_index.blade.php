{{-- Comunicados — comunicados.html (lista .doc-row) --}}
<section class="section">
  <div class="container-xl">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="filter-bar mb-4">
          <span class="form-label d-block mb-0">Pesquisar comunicados</span>
          @include('modules.partials.filter-search', ['q' => $q, 'placeholder' => 'Pesquisar comunicados...', 'id' => 'annQ'])
        </div>
        @forelse ($items as $item)
          <div class="doc-row">
            <div class="doc-info">
              <div class="doc-icon"><i class="bi bi-megaphone"></i></div>
              <div>
                <h3 class="h6 mb-0"><a href="{{ $item->url() }}" class="text-reset">{{ $item->title }}</a>@if ($item->is_demo && setting('show_demo_badges')) <span class="demo-badge">Exemplo</span>@endif</h3>
                <div class="doc-meta">Publicado em {{ $item->published_at->translatedFormat('d \d\e F \d\e Y') }}</div>
              </div>
            </div>
            <a href="{{ $item->url() }}" class="btn btn-sm btn-outline-brand">Ler<span class="visually-hidden"> {{ $item->title }}</span> <i class="bi bi-arrow-right"></i></a>
          </div>
        @empty
          @include('partials.empty', ['icon' => 'bi-megaphone', 'title' => 'Sem comunicados', 'text' => $q ? 'Nenhum comunicado corresponde à pesquisa.' : 'Ainda não foram publicados comunicados.'])
        @endforelse
        {{ $items->links('partials.pagination', ['label' => 'Paginação de comunicados']) }}
      </div>
    </div>
  </div>
</section>
