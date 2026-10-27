{{-- Agenda — agenda.html (eventos agrupados por mês) --}}
<section class="section">
  <div class="container-xl">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        @forelse ($months as $month => $events)
          <div class="agenda-month">{{ $month }}</div>
          @foreach ($events as $event)
            <div class="mb-3">@include('modules.partials.event-card', ['event' => $event, 'showTime' => true])</div>
          @endforeach
        @empty
          @include('partials.empty', ['icon' => 'bi-calendar3', 'title' => 'Agenda sem actividades', 'text' => 'De momento não existem actividades agendadas para os próximos meses.'])
        @endforelse
      </div>
    </div>
  </div>
</section>
