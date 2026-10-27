{{-- Cronologia — padrão .curriculum-year/.year-title usado em historia.html --}}
<div class="row justify-content-center">
  <div class="col-lg-8">
    @include('blocks._head', ['size' => 'h4'])
    @foreach ($block['items'] ?? [] as $item)
      <div class="curriculum-year">
        <div class="year-title">{{ $item['heading'] }}</div>
        @if (! empty($item['text']))<p class="article-body">{{ $item['text'] }}</p>@endif
      </div>
    @endforeach
  </div>
</div>
