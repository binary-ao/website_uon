{{-- Parceiros / Cooperação — parceiros.html (.partner-logo) --}}
<section class="section">
  <div class="container-xl">
    @if ($items->isEmpty())
      @include('partials.empty', ['icon' => 'bi-people', 'title' => 'Informação em preparação', 'text' => 'A lista de parceiros será publicada brevemente.', 'boxed' => true])
    @else
      @include('modules.partials.partner-grid', ['items' => $items])
    @endif
  </div>
</section>
