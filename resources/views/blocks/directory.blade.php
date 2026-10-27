{{-- .card-directory (Template E — Directório) --}}
@php
    $cols = ['2' => 'col-md-6', '3' => 'col-md-6 col-lg-4', '4' => 'col-md-6 col-lg-3'][$block['data']['columns'] ?? '3'] ?? 'col-md-6 col-lg-4';
@endphp
@include('blocks._head')
<div class="row g-4">
  @foreach ($block['items'] ?? [] as $item)
    <div class="{{ $cols }}">
      <div class="card-directory">
        <h3>{{ $item['title'] }}</h3>
        @if (! empty($item['role']))<p class="role">{{ $item['role'] }}</p>@endif
        @if (! empty($item['text']))<p class="text-muted-brand small mb-2">{{ $item['text'] }}</p>@endif
        @php $lines = array_filter(array_map('trim', preg_split('/\r?\n/', (string) ($item['lines'] ?? '')))); @endphp
        @if ($lines)
          <ul>
            @foreach ($lines as $line)
              @php [$icon, $text] = (str_contains($line, '|') ? array_map('trim', explode('|', $line, 2)) : ['bi-dot', $line]); @endphp
              <li><i class="bi {{ preg_match('/^bi-[a-z0-9-]+$/', $icon) ? $icon : 'bi-dot' }}"></i> {{ $text }}</li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  @endforeach
</div>
