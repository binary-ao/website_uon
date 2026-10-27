{{-- .accordion-brand (missao-visao-valores.html / informacoes-academicas.html) --}}
@php $id = 'acc'.substr(md5(json_encode($block)), 0, 8); @endphp
<div class="row">
  <div class="col-lg-8 mx-auto">
    @if (! empty($block['data']['title']))<h2 class="section-title text-center mb-4">{{ $block['data']['title'] }}</h2>@endif
    <div class="accordion accordion-brand" id="{{ $id }}">
      @foreach ($block['items'] ?? [] as $i => $item)
        <div class="accordion-item">
          <h3 class="accordion-header">
            <button class="accordion-button{{ $i ? ' collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $id }}-{{ $i }}" aria-expanded="{{ $i ? 'false' : 'true' }}" aria-controls="{{ $id }}-{{ $i }}">{{ $item['title'] }}</button>
          </h3>
          <div id="{{ $id }}-{{ $i }}" class="accordion-collapse collapse{{ $i ? '' : ' show' }}" data-bs-parent="#{{ $id }}">
            <div class="accordion-body text-muted-brand">{!! nl2br(e($item['text'] ?? '')) !!}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
