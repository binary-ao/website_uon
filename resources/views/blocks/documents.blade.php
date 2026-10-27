@php
    $docs = \App\Models\Document::published()->with('currentVersion')
        ->where('category_id', (int) ($block['data']['category_id'] ?? 0))
        ->whereNotNull('current_version_id')
        ->when(! auth()->check(), fn ($q) => $q->where('visibility', 'public'))
        ->orderByDesc('document_date')->limit((int) ($block['data']['limit'] ?? 10))->get();
@endphp
<div class="row justify-content-center">
  <div class="col-lg-9">
    @if (! empty($block['data']['title']))<h2 class="h4 mb-3">{{ $block['data']['title'] }}</h2>@endif
    @forelse ($docs as $document)
      @include('modules.partials.doc-row', ['document' => $document])
    @empty
      <p class="text-muted-brand">Ainda não existem documentos publicados nesta categoria.</p>
    @endforelse
  </div>
</div>
