<div class="row justify-content-center">
  <div class="col-lg-8 block-text">
    @if (! empty($block['data']['title']))<h2 class="h4 mb-3">{{ $block['data']['title'] }}</h2>@endif
    @foreach (preg_split('/\n\s*\n/', (string) ($block['data']['text'] ?? '')) as $para)
      @if (trim($para) !== '')<p class="article-body">{!! nl2br(e(trim($para))) !!}</p>@endif
    @endforeach
  </div>
</div>
