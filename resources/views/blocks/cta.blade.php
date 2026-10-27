{{-- .cta-banner --}}
@php $btn = \App\Support\SafeUrl::resolve($block['data']['button_url'] ?? null); @endphp
<div class="cta-banner text-center text-lg-start">
  <div class="row align-items-center">
    <div class="col-lg-8 position-relative">
      <h2>{{ $block['data']['title'] }}</h2>
      @if (! empty($block['data']['text']))<p class="mb-0">{{ $block['data']['text'] }}</p>@endif
    </div>
    @if ($btn && ! empty($block['data']['button_label']))
      <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 position-relative">
        <a href="{{ $btn }}" class="btn btn-brand btn-lg">{{ $block['data']['button_label'] }}</a>
      </div>
    @endif
  </div>
</div>
