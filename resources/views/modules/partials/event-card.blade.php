{{-- .card-event do website_template --}}
<a href="{{ $event->url() }}" class="text-decoration-none text-reset d-block h-100">
  <div class="card-event h-100{{ $event->isPast() ? ' is-past' : '' }}">
    <div class="event-date"><span class="d">{{ $event->starts_at->format('d') }}</span><span class="m">{{ \Illuminate\Support\Str::ucfirst($event->starts_at->translatedFormat('M')) }}</span></div>
    <div>
      <h3>{{ $event->title }}@if ($event->is_demo && setting('show_demo_badges')) <span class="demo-badge">Exemplo</span>@endif</h3>
      <div class="event-meta">
        @if ($event->location)<i class="bi bi-geo-alt"></i>{{ $event->location }}@endif
        @if (! empty($showTime) && $event->timeLabel())@if ($event->location) · @endif<i class="bi bi-clock"></i> {{ $event->timeLabel() }}@endif
        @if (! empty($showYear)) · {{ $event->starts_at->format('Y') }}@endif
      </div>
    </div>
  </div>
</a>
