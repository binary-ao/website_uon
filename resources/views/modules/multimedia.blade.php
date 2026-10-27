{{-- Multimédia — multimedia.html (vídeos) --}}
<section class="section">
  <div class="container-xl">
    @if ($items->isEmpty())
      @include('partials.empty', ['icon' => 'bi-play-btn', 'title' => 'Sem conteúdos multimédia', 'text' => 'Ainda não foram publicados vídeos.'])
    @else
      <div class="row g-4">
        @foreach ($items as $item)
          <div class="col-md-6 col-lg-4">
            <div class="card-flat h-100">
              @if ($item->embedUrl())
                <div class="ratio ratio-16x9">
                  <iframe src="{{ $item->embedUrl() }}" title="{{ $item->title }}" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" style="border-radius: var(--radius) var(--radius) 0 0;"></iframe>
                </div>
              @endif
              <div class="card-body p-3">
                <h3 class="h6 mb-0">{{ $item->title }}@if ($item->is_demo && setting('show_demo_badges')) <span class="demo-badge">Exemplo</span>@endif</h3>
                <p class="text-muted-brand small mb-0">{{ collect([$item->caption, $item->duration_label])->filter()->implode(' · ') }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
      {{ $items->links('partials.pagination', ['label' => 'Paginação de vídeos']) }}
    @endif
  </div>
</section>
