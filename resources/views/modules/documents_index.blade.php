{{-- Documentos / Regulamentos / Formulários / Protocolos — documentos.html (.doc-row) --}}
<section class="section">
  <div class="container-xl">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="filter-bar mb-4">
          <div class="row g-2 align-items-end">
            @if ($years->count() > 1)
              <div class="col-md-4">
                <form method="get" action="{{ url()->current() }}">
                  @if ($q)<input type="hidden" name="q" value="{{ $q }}">@endif
                  <label for="docYear" class="form-label">Ano</label>
                  <select id="docYear" name="ano" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach ($years as $y)
                      <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endforeach
                  </select>
                  <noscript><button class="btn btn-sm btn-outline-brand mt-2">Aplicar</button></noscript>
                </form>
              </div>
            @endif
            <div class="col">
              @include('modules.partials.filter-search', ['q' => $q, 'placeholder' => 'Pesquisar documentos...', 'id' => 'docQ'])
            </div>
          </div>
        </div>
        <p class="results-count mb-4">{{ $items->total() }} {{ $items->total() === 1 ? 'documento' : 'documentos' }}</p>
        @forelse ($items as $document)
          @include('modules.partials.doc-row', ['document' => $document])
        @empty
          @include('partials.empty', ['icon' => 'bi-file-earmark-text', 'title' => 'Sem documentos', 'text' => ($q || $year) ? 'Nenhum documento corresponde aos filtros seleccionados.' : 'Ainda não foram publicados documentos nesta secção.'])
        @endforelse
        {{ $items->links('partials.pagination', ['label' => 'Paginação de documentos']) }}
      </div>
    </div>
  </div>
</section>
