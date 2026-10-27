{{-- .table-brand dentro de .table-responsive-wrap (calendario-academico.html) --}}
@php
    $headers = array_map('trim', explode('|', (string) ($block['data']['headers'] ?? '')));
    $rows = array_values(array_filter(array_map(fn ($l) => trim($l) === '' ? null : array_map('trim', explode('|', $l)), preg_split('/\r?\n/', (string) ($block['data']['rows'] ?? '')))));
@endphp
<div class="row justify-content-center">
  <div class="col-lg-9">
    @if (! empty($block['data']['title']))<h2 class="h4 mb-3">{{ $block['data']['title'] }}</h2>@endif
    <div class="table-responsive-wrap">
      <table class="table table-brand mb-0">
        <thead><tr>@foreach ($headers as $h)<th scope="col">{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>@foreach (array_pad($row, count($headers), '') as $cell)<td>{{ $cell }}</td>@endforeach</tr>
          @empty
            <tr><td colspan="{{ count($headers) }}" class="text-muted-brand">Sem dados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if (! empty($block['data']['note']))<p class="text-muted-brand small mt-4">{{ $block['data']['note'] }}</p>@endif
  </div>
</div>
