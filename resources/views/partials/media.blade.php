{{-- Imagem real dentro do componente .ph-media do template (placeholder quando não existe imagem). --}}
@php $m = $media ?? null; @endphp
<div class="ph-media{{ $m ? ' has-image' : '' }}{{ ! $m && ! empty($dark) ? ' dark' : '' }}{{ ! empty($class) ? ' '.$class : '' }}" style="--ph-ratio: {{ $ratio ?? '4/3' }};{{ $style ?? '' }}">
  @if ($m)
    <img class="media-cover" src="{{ $m->url($variant ?? 'md') }}" alt="{{ $alt ?? $m->altText() }}" loading="lazy" @if($m->width) width="{{ $m->width }}" height="{{ $m->height }}" @endif>
  @else
    <i class="bi {{ $icon ?? 'bi-image' }}" aria-hidden="true"></i>
    @if (! empty($label))<span class="ph-label">{{ $label }}</span>@endif
  @endif
</div>
