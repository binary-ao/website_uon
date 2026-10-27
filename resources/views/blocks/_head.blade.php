@if (! empty($block['data']['title']) || ! empty($block['data']['intro']))
  <div class="{{ $center ?? false ? 'text-center' : '' }} mb-4">
    @if (! empty($block['data']['title']))<h2 class="section-title {{ $size ?? 'h3' }}">{{ $block['data']['title'] }}</h2>@endif
    @if (! empty($block['data']['intro']))<p class="section-intro{{ $center ?? false ? ' mx-auto' : '' }}">{{ $block['data']['intro'] }}</p>@endif
  </div>
@endif
