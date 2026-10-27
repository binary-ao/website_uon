{{-- .card-course do website_template --}}
<div class="card-flat card-course card-link">
  @include('partials.media', ['media' => $course->featuredMedia, 'ratio' => '16/10', 'icon' => $course->icon ?: 'bi-mortarboard'])
  <div class="card-body">
    <span class="badge-degree">{{ $course->degree?->name }}</span>
    <h3>{{ $course->name }}@if ($course->is_demo && setting('show_demo_badges')) <span class="demo-badge">Exemplo</span>@endif</h3>
    <ul class="meta-list">
      @if ($course->duration)<li><i class="bi bi-clock"></i> {{ $course->duration }}</li>@endif
      @if ($course->unit)<li><i class="bi bi-building"></i> {{ $course->unit->short_name ?: $course->unit->name }}</li>@endif
      @if ($course->modality)<li><i class="bi bi-person-workspace"></i> {{ $course->modality }}</li>@endif
    </ul>
    <a href="{{ $course->url() }}" class="btn-link-brand card-footer-link stretched-link">Ver curso <i class="bi bi-arrow-right"></i><span class="visually-hidden"> {{ $course->name }}</span></a>
  </div>
</div>
