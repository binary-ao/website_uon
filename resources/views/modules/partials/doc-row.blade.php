{{-- .doc-row do website_template --}}
<div class="doc-row">
  <div class="doc-info">
    <div class="doc-icon"><i class="bi {{ $document->icon() }}"></i></div>
    <div>
      <h3 class="h6 mb-0">{{ $document->title }}@if ($document->is_demo && setting('show_demo_badges')) <span class="demo-badge">Exemplo</span>@endif</h3>
      @if ($document->description)<div class="small text-muted-brand">{{ \Illuminate\Support\Str::limit($document->description, 160) }}</div>@endif
      <div class="doc-meta">{{ $document->metaLabel() }}</div>
    </div>
  </div>
  <a href="{{ $document->downloadUrl() }}" class="btn btn-sm btn-outline-brand" rel="nofollow"><i class="bi bi-download"></i> Descarregar<span class="visually-hidden"> {{ $document->title }}</span></a>
</div>
