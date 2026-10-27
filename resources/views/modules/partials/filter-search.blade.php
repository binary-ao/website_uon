{{-- Campo de pesquisa dentro da .filter-bar (mantém os restantes filtros activos). --}}
<form method="get" action="{{ url()->current() }}" class="d-flex gap-2 mt-3" role="search">
  @foreach (request()->except(['q', 'page']) as $k => $v)
    @if (is_string($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
  @endforeach
  <label for="{{ $id ?? 'filterQ' }}" class="visually-hidden">{{ $label ?? 'Pesquisar' }}</label>
  <input type="search" class="form-control" id="{{ $id ?? 'filterQ' }}" name="q" value="{{ $q ?? '' }}" placeholder="{{ $placeholder ?? 'Pesquisar...' }}" maxlength="100">
  <button type="submit" class="btn btn-brand"><i class="bi bi-search"></i><span class="visually-hidden">Pesquisar</span></button>
  @if (! empty($q))
    <a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => null]) }}" class="btn btn-outline-brand" title="Limpar pesquisa"><i class="bi bi-x-lg"></i><span class="visually-hidden">Limpar pesquisa</span></a>
  @endif
</form>
