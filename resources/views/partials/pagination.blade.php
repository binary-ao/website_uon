{{-- Paginação com a marcação do website_template (.pagination centrada, chevrons). --}}
@if ($paginator->hasPages())
<nav aria-label="{{ $label ?? 'Paginação' }}" class="mt-5">
  <ul class="pagination justify-content-center">
    @if ($paginator->onFirstPage())
      <li class="page-item disabled"><span class="page-link" aria-hidden="true"><i class="bi bi-chevron-left"></i></span></li>
    @else
      <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior"><i class="bi bi-chevron-left"></i></a></li>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
          @else
            <li class="page-item"><a class="page-link" href="{{ $url }}" aria-label="Página {{ $page }}">{{ $page }}</a></li>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Página seguinte"><i class="bi bi-chevron-right"></i></a></li>
    @else
      <li class="page-item disabled"><span class="page-link" aria-hidden="true"><i class="bi bi-chevron-right"></i></span></li>
    @endif
  </ul>
</nav>
@endif
