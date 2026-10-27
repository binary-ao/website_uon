@if (! empty($documents) && $documents->isNotEmpty())
  <div class="attached-docs mt-5">
    <h2 class="h5 mb-3">Documentos</h2>
    @foreach ($documents as $document)
      @if ($document->currentVersion && ($document->isPublic() || auth()->check()))
        @include('modules.partials.doc-row', ['document' => $document])
      @endif
    @endforeach
  </div>
@endif
