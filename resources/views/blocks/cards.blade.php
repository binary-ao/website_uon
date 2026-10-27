{{-- .card-service (Template A — Landing) --}}
@php
    $cols = ['2' => 'col-md-6', '3' => 'col-md-6 col-lg-4', '4' => 'col-md-6 col-lg-3'][$block['data']['columns'] ?? '3'] ?? 'col-md-6 col-lg-4';
@endphp
@include('blocks._head')
<div class="row g-4">
  @foreach ($block['items'] ?? [] as $item)
    @php $href = \App\Support\SafeUrl::resolve($item['url'] ?? null); @endphp
    <div class="{{ $cols }}">
      @if ($href)<a href="{{ $href }}" class="text-decoration-none d-block h-100"@if (\App\Support\SafeUrl::isExternal($item['url'])) target="_blank" rel="noopener"@endif>@endif
        <div class="card-service">
          @if (! empty($item['icon']))<div class="icon-tile"><i class="bi {{ $item['icon'] }}"></i></div>@endif
          <h3>{{ $item['title'] }}</h3>
          @if (! empty($item['text']))<p>{{ $item['text'] }}</p>@endif
        </div>
      @if ($href)</a>@endif
    </div>
  @endforeach
</div>
