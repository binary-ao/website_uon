{{-- .org-chart (estrutura-organizacional.html) --}}
@if (! empty($block['data']['title']))<h2 class="section-title h4 mb-4">{{ $block['data']['title'] }}</h2>@endif
<div class="org-chart">
  @foreach ($block['items'] ?? [] as $level)
    <div class="org-level{{ $loop->last ? ' mb-0' : '' }}">
      @foreach (array_filter(array_map('trim', explode(';', (string) $level['nodes']))) as $node)
        <div class="org-node{{ ! empty($level['highlight']) ? ' top' : '' }}">{{ $node }}</div>
      @endforeach
    </div>
  @endforeach
</div>
@if (! empty($block['data']['note']))<p class="text-center text-muted-brand small mt-4 mb-0">{{ $block['data']['note'] }}</p>@endif
