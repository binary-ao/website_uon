{{-- Oferta formativa / Licenciaturas / Mestrados / Pós-Graduações — oferta-formativa.html --}}
<section class="section">
  <div class="container-xl">
    <div class="filter-bar mb-4">
      @if (! $fixedDegree && $degrees->isNotEmpty())
        <span class="form-label d-block mb-2" id="courseTypeLabel">Filtrar por tipo de curso</span>
        <div class="d-flex flex-wrap gap-2" role="group" aria-labelledby="courseTypeLabel">
          <a class="filter-chip{{ ! $current ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['grau' => null, 'page' => null]) }}">Todos</a>
          @foreach ($degrees as $degree)
            <a class="filter-chip{{ $current?->id === $degree->id ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['grau' => $degree->slug, 'page' => null]) }}">{{ $degree->pluralLabel() }}</a>
          @endforeach
        </div>
      @endif
      <div class="row g-2 align-items-end {{ ! $fixedDegree && $degrees->isNotEmpty() ? 'mt-1' : '' }}">
        @if ($units->count() > 1)
          <div class="col-md-5">
            <form method="get" action="{{ url()->current() }}">
              @foreach (request()->except(['unidade', 'page']) as $k => $v)
                @if (is_string($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
              @endforeach
              <label for="courseUnit" class="form-label">Unidade orgânica</label>
              <select id="courseUnit" name="unidade" class="form-select" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach ($units as $u)
                  <option value="{{ $u->slug }}" @selected($unit?->id === $u->id)>{{ $u->name }}</option>
                @endforeach
              </select>
              <noscript><button class="btn btn-sm btn-outline-brand mt-2">Aplicar</button></noscript>
            </form>
          </div>
        @endif
        <div class="col">
          @include('modules.partials.filter-search', ['q' => $q, 'placeholder' => 'Pesquisar cursos...', 'id' => 'courseQ'])
        </div>
      </div>
    </div>
    <p class="results-count mb-4">{{ $items->total() }} {{ $items->total() === 1 ? 'resultado' : 'resultados' }}</p>

    @if ($items->isEmpty())
      @include('partials.empty', ['icon' => 'bi-search', 'title' => 'Nenhum curso encontrado', 'text' => ($q || $unit || ($current && ! $fixedDegree)) ? 'Não existem cursos que correspondam ao filtro seleccionado.' : 'A oferta formativa será publicada brevemente.'])
    @else
      <div class="row g-4">
        @foreach ($items as $course)
          <div class="col-md-6 col-lg-4">
            @include('modules.partials.course-card', ['course' => $course])
          </div>
        @endforeach
      </div>
      {{ $items->links('partials.pagination', ['label' => 'Paginação de cursos']) }}
    @endif
  </div>
</section>
