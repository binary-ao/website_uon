{{-- Galeria — galeria.html (Template F) com lightbox --}}
<section class="section">
  <div class="container-xl">
    @if ($categories->count() > 1)
      <div class="filter-bar mb-4">
        <div class="d-flex flex-wrap gap-2" role="group" aria-label="Filtrar galeria">
          <a class="filter-chip{{ ! $current ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['categoria' => null, 'page' => null]) }}">Todas</a>
          @foreach ($categories as $cat)
            <a class="filter-chip{{ $current?->id === $cat->id ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['categoria' => $cat->slug, 'page' => null]) }}">{{ $cat->name }}</a>
          @endforeach
        </div>
      </div>
    @endif

    @if ($items->isEmpty())
      @include('partials.empty', ['icon' => 'bi-images', 'title' => 'Galeria sem imagens', 'text' => 'Ainda não foram publicadas imagens.'])
    @else
      <div class="row g-3">
        @foreach ($items as $item)
          <div class="col-6 col-md-4">
            <button type="button" class="gallery-item" data-lightbox-src="{{ $item->media->url() }}" data-lightbox-alt="{{ $item->media->altText() ?: $item->title }}" data-lightbox-caption="{{ $item->caption ?: $item->title }}" aria-label="Ampliar imagem: {{ $item->title }}">
              @include('partials.media', ['media' => $item->media, 'ratio' => '1/1', 'alt' => $item->media->altText() ?: $item->title])
              <div class="gallery-caption">{{ $item->title }}@if ($item->is_demo && setting('show_demo_badges')) · Exemplo @endif</div>
            </button>
          </div>
        @endforeach
      </div>
      {{ $items->links('partials.pagination', ['label' => 'Paginação da galeria']) }}
    @endif
  </div>
</section>

<div class="modal fade uon-lightbox" id="galleryLightbox" tabindex="-1" aria-label="Imagem ampliada" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content position-relative">
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      <img src="" alt="">
      <div class="lightbox-caption"></div>
    </div>
  </div>
</div>
