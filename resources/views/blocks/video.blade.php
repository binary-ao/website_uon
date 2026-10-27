@php $embed = \App\Support\VideoEmbed::embedUrl($block['data']['url'] ?? null); @endphp
@if ($embed)
  <div class="row justify-content-center">
    <figure class="col-lg-9 mb-0">
      <div class="ratio ratio-16x9">
        <iframe src="{{ $embed }}" title="{{ $block['data']['caption'] ?: 'Vídeo' }}" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div>
      @if (! empty($block['data']['caption']))<figcaption class="small text-muted-brand mt-2">{{ $block['data']['caption'] }}</figcaption>@endif
    </figure>
  </div>
@endif
