{{-- Breadcrumb + Page Header (padrão das páginas internas do website_template) --}}
<div class="breadcrumb-bar py-2">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
        @foreach ($breadcrumbs ?? [] as $crumb)
          <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
        @endforeach
        <li class="breadcrumb-item active" aria-current="page">{{ $current ?? $title }}</li>
      </ol>
    </nav>
  </div>
</div>

<div class="page-header">
  <div class="container-xl position-relative">
    @if (! empty($eyebrow))<p class="eyebrow">{{ $eyebrow }}</p>@endif
    <h1>{{ $title }}</h1>
    @if (! empty($intro))<p>{{ $intro }}</p>@endif
  </div>
</div>
