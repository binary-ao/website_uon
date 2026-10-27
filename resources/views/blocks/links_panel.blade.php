{{-- .research-panel (index.html / investigacao) --}}
@php
    $links = [];
    if (! empty($block['data']['use_research_links'])) {
        $links = \App\Http\Controllers\Site\HomeController::researchLinks();
    }
    foreach ($block['items'] ?? [] as $item) {
        if ($u = \App\Support\SafeUrl::resolve($item['url'] ?? null)) {
            $links[] = ['label' => $item['label'], 'url' => $u];
        }
    }
@endphp
<div class="research-panel">
  <div class="row align-items-center">
    <div class="col-lg-8 position-relative">
      @if (! empty($block['data']['eyebrow']))<p class="section-eyebrow" style="color: var(--primary);">{{ $block['data']['eyebrow'] }}</p>@endif
      <h2>{{ $block['data']['title'] }}</h2>
      @if (! empty($block['data']['text']))<p>{{ $block['data']['text'] }}</p>@endif
      @if ($links)
        <div class="research-link-list">
          @foreach ($links as $link)
            <a href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ $link['label'] }} <i class="bi bi-box-arrow-up-right"></i></a>
          @endforeach
        </div>
      @else
        <p class="small mb-0" style="color: rgba(255,255,255,.55);">As ligações às plataformas externas serão disponibilizadas brevemente.</p>
      @endif
    </div>
  </div>
</div>
