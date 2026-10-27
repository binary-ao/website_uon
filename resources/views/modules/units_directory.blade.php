{{-- Órgãos / Unidades orgânicas / Departamentos — cartões com imagem (card-flat/card-course do template). --}}
<section class="section">
  <div class="container-xl">
    @if ($items->isEmpty())
      @include('partials.empty', ['icon' => 'bi-building', 'title' => 'Informação em preparação', 'text' => 'A informação desta secção será publicada brevemente.', 'boxed' => true])
    @else
      <div class="row g-4">
        @foreach ($items as $unit)
          @php $courseCount = $unit->courses->count() + $unit->children->sum(fn ($c) => $c->courses->count()); @endphp
          <div class="col-md-6 col-lg-4">
            <article class="card-flat card-course card-link unit-card">
              @include('partials.media', ['media' => $unit->featuredMedia, 'ratio' => '16/10', 'icon' => $unit->type === 'orgao' ? 'bi-diagram-3' : 'bi-building', 'alt' => $unit->name])
              <div class="card-body">
                <span class="badge-degree">{{ $unit->short_name ?: $unit->typeLabel() }}</span>
                <h3><a href="{{ $unit->url() }}" class="text-reset text-decoration-none stretched-link">{{ $unit->name }}</a>@if ($unit->is_demo && setting('show_demo_badges')) <span class="demo-badge">Exemplo</span>@endif</h3>
                @if ($unit->summary)<p class="unit-card-summary">{{ $unit->summary }}</p>@endif
                <ul class="meta-list">
                  @if ($unit->head_name)<li><i class="bi bi-person-badge"></i> {{ $unit->head_title ? $unit->head_title.': ' : '' }}{{ $unit->head_name }}</li>@endif
                  @if ($unit->parent)<li><i class="bi bi-diagram-3"></i> {{ $unit->parent->name }}</li>@endif
                  @if ($unit->children->isNotEmpty())<li><i class="bi bi-diagram-2"></i> {{ $unit->children->count() }} {{ $unit->children->count() === 1 ? 'departamento' : 'departamentos' }}</li>@endif
                  @if ($courseCount)<li><i class="bi bi-mortarboard"></i> {{ $courseCount }} {{ $courseCount === 1 ? 'curso' : 'cursos' }}</li>@endif
                </ul>
                <span class="btn-link-brand card-footer-link" aria-hidden="true">Conhecer {{ $unit->type === 'faculdade' ? 'a faculdade' : 'a unidade' }} <i class="bi bi-arrow-right"></i></span>
              </div>
            </article>
          </div>
        @endforeach
      </div>
    @endif
    @php $offer = \App\Models\Page::published()->where('path', 'ensino/oferta-formativa')->first(); @endphp
    @if ($type !== 'orgao' && $offer)
      <div class="text-center mt-5">
        <a href="{{ $offer->url() }}" class="btn-link-brand justify-content-center">Ver a oferta formativa <i class="bi bi-arrow-right"></i></a>
      </div>
    @endif
  </div>
</section>
