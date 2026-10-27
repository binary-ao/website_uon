{{-- Banner da página inicial em carrossel — gerido em CMS → Banner da página inicial. --}}
@php $count = $slides->count(); @endphp
<section class="uon-hero carousel slide carousel-fade" id="heroCarousel" aria-roledescription="carrossel" aria-label="Destaques"
  @if ($count > 1) data-bs-ride="carousel" data-bs-interval="7000" data-bs-pause="hover" @endif data-uon-hero>
  <div class="carousel-inner">
    @foreach ($slides as $i => $slide)
      <div class="carousel-item{{ $i === 0 ? ' active' : '' }}" role="group" aria-roledescription="diapositivo" aria-label="{{ $i + 1 }} de {{ $count }}">
        <img class="uon-hero-img" src="{{ $slide->media->url() }}" alt="{{ $slide->media->altText() }}" style="object-position: {{ $slide->backgroundPosition() }};"
          @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif @if ($slide->media->width) width="{{ $slide->media->width }}" height="{{ $slide->media->height }}" @endif>
        <div class="uon-hero-shade" aria-hidden="true"></div>
        <div class="container-xl uon-hero-content">
          <div class="uon-hero-text">
            @if ($slide->eyebrow)<p class="uon-hero-eyebrow">{{ $slide->eyebrow }}</p>@endif
            @if ($i === 0)
              <h1 class="uon-hero-title">{{ $slide->title }}@if ($slide->title_highlight) <span>{{ $slide->title_highlight }}</span>@endif</h1>
            @else
              <h2 class="uon-hero-title">{{ $slide->title }}@if ($slide->title_highlight) <span>{{ $slide->title_highlight }}</span>@endif</h2>
            @endif
            @if ($slide->text)<p class="uon-hero-lead">{{ $slide->text }}</p>@endif
            @if (($slide->primaryHref() && $slide->primary_label) || ($slide->secondaryHref() && $slide->secondary_label))
              <div class="uon-hero-actions">
                @if ($slide->primaryHref() && $slide->primary_label)
                  <a href="{{ $slide->primaryHref() }}" class="btn btn-brand">{{ $slide->primary_label }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endif
                @if ($slide->secondaryHref() && $slide->secondary_label)
                  <a href="{{ $slide->secondaryHref() }}" class="btn btn-outline-light-brand">{{ $slide->secondary_label }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endif
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  @if ($count > 1)
    <div class="container-xl uon-hero-controls-wrap">
      <div class="uon-hero-controls">
        <span class="uon-hero-counter" aria-live="polite"><span data-hero-current>01</span> / {{ str_pad((string) $count, 2, '0', STR_PAD_LEFT) }}</span>
        <button class="uon-hero-btn" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Diapositivo anterior"><i class="bi bi-arrow-left"></i></button>
        <button class="uon-hero-btn" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Diapositivo seguinte"><i class="bi bi-arrow-right"></i></button>
        <button class="uon-hero-btn" type="button" data-hero-pause aria-label="Pausar a rotação automática" aria-pressed="false"><i class="bi bi-pause-fill"></i></button>
      </div>
    </div>
  @endif
</section>
