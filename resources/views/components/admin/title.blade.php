@props(['title', 'subtitle' => null, 'crumbs' => []])
<div class="d-title">
  <div>
    @if ($crumbs)
      <nav class="adm-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Painel</a></li>
          @foreach ($crumbs as $label => $url)
            <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
          @endforeach
        </ol>
      </nav>
    @endif
    <h1>{{ $title }}</h1>
    @if ($subtitle)<p>{{ $subtitle }}</p>@endif
  </div>
  @if (trim($slot) !== '')
    <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
  @endif
</div>
