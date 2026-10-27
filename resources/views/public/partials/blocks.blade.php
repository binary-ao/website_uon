{{-- Renderização dos blocos controlados. Cada bloco usa um componente do website_template. --}}
@foreach ($blocks ?? [] as $block)
  @php
      $type = $block['type'] ?? null;
      $first = $loop->first && empty($afterContent);
  @endphp
  @if ($type && view()->exists('blocks.'.$type))
    <section class="{{ $first ? 'section' : 'section-sm pt-0' }}{{ $first && ! $loop->last ? ' pb-5' : '' }}">
      <div class="container-xl">
        @include('blocks.'.$type, ['block' => $block])
      </div>
    </section>
  @endif
@endforeach
