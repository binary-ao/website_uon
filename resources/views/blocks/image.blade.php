@php $media = ! empty($block['data']['media_id']) ? \App\Models\Media::find($block['data']['media_id']) : null; @endphp
@if ($media && $media->isImage())
  <div class="row justify-content-center">
    <figure class="col-lg-9 mb-0">
      <img src="{{ $media->url() }}" alt="{{ $media->altText() }}" class="img-fluid w-100" style="border-radius: var(--radius-lg);" loading="lazy" width="{{ $media->width }}" height="{{ $media->height }}">
      @if (! empty($block['data']['caption']))<figcaption class="small text-muted-brand mt-2">{{ $block['data']['caption'] }}</figcaption>@endif
    </figure>
  </div>
@endif
