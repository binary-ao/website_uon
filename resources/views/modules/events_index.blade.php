{{-- Eventos — eventos.html (grelha .card-event) com separação próximos/anteriores --}}
<section class="section">
  <div class="container-xl">
    <div class="filter-bar mb-4">
      <span class="form-label d-block mb-2" id="evPeriodLabel">Período</span>
      <div class="d-flex flex-wrap gap-2" role="group" aria-labelledby="evPeriodLabel">
        <a class="filter-chip{{ $period === 'proximos' ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['periodo' => null, 'page' => null]) }}">Próximos eventos</a>
        <a class="filter-chip{{ $period === 'anteriores' ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['periodo' => 'anteriores', 'page' => null]) }}">Eventos anteriores</a>
      </div>
      @if ($categories->isNotEmpty())
        <span class="form-label d-block mt-3 mb-2" id="evCatLabel">Categoria</span>
        <div class="d-flex flex-wrap gap-2" role="group" aria-labelledby="evCatLabel">
          <a class="filter-chip{{ ! $current ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['categoria' => null, 'page' => null]) }}">Todas</a>
          @foreach ($categories as $cat)
            <a class="filter-chip{{ $current?->id === $cat->id ? ' active' : '' }}" href="{{ request()->fullUrlWithQuery(['categoria' => $cat->slug, 'page' => null]) }}">{{ $cat->name }}</a>
          @endforeach
        </div>
      @endif
    </div>
    <p class="results-count mb-4">{{ $items->total() }} {{ $items->total() === 1 ? 'evento' : 'eventos' }}</p>

    @if ($items->isEmpty())
      @include('partials.empty', ['icon' => 'bi-calendar-event', 'title' => $period === 'anteriores' ? 'Sem eventos anteriores' : 'Sem eventos agendados', 'text' => $period === 'anteriores' ? 'Não existem eventos anteriores para os filtros seleccionados.' : 'De momento não existem eventos agendados. Consulte novamente em breve.'])
    @else
      <div class="row g-3">
        @foreach ($items as $event)
          <div class="col-lg-6">
            @include('modules.partials.event-card', ['event' => $event, 'showTime' => true, 'showYear' => $period === 'anteriores'])
          </div>
        @endforeach
      </div>
      {{ $items->links('partials.pagination', ['label' => 'Paginação de eventos']) }}
    @endif
  </div>
</section>
