@props(['authors' => null, 'status' => true, 'trash' => true, 'placeholder' => 'Pesquisar...', 'dates' => true])
<form method="get" class="adm-filters" role="search">
  <div class="row g-2 align-items-end">
    <div class="col-md-4 col-xl-3">
      <label class="form-label small mb-1" for="flt_q">Pesquisar</label>
      <input type="search" class="form-control" id="flt_q" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder }}">
    </div>
    @if ($status)
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1" for="flt_status">Estado</label>
        <select class="form-select" id="flt_status" name="status">
          <option value="">Todos</option>
          @foreach (\App\Enums\PublicationStatus::options() as $v => $l)
            <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
          @endforeach
        </select>
      </div>
    @endif
    {{ $slot }}
    @if ($authors)
      <div class="col-6 col-md-2">
        <label class="form-label small mb-1" for="flt_author">Autor</label>
        <select class="form-select" id="flt_author" name="author">
          <option value="">Todos</option>
          @foreach ($authors as $id => $name)
            <option value="{{ $id }}" @selected((string) request('author') === (string) $id)>{{ $name }}</option>
          @endforeach
        </select>
      </div>
    @endif
    @if ($dates)
      <div class="col-6 col-md-2 col-xl-1">
        <label class="form-label small mb-1" for="flt_from">Desde</label>
        <input type="date" class="form-control" id="flt_from" name="from" value="{{ request('from') }}">
      </div>
      <div class="col-6 col-md-2 col-xl-1">
        <label class="form-label small mb-1" for="flt_to">Até</label>
        <input type="date" class="form-control" id="flt_to" name="to" value="{{ request('to') }}">
      </div>
    @endif
    <div class="col-auto d-flex gap-2">
      @if (request('trashed'))<input type="hidden" name="trashed" value="1">@endif
      <button type="submit" class="btn btn-u-dark"><i class="bi bi-funnel"></i> Filtrar</button>
      @if (collect(request()->except(['page', 'trashed']))->filter()->isNotEmpty())
        <a href="{{ url()->current() }}{{ request('trashed') ? '?trashed=1' : '' }}" class="btn btn-u-light">Limpar</a>
      @endif
    </div>
  </div>
  @if ($trash)
    <div class="adm-tabs mt-3 mb-0">
      <a href="{{ request()->fullUrlWithQuery(['trashed' => null, 'page' => null]) }}" class="{{ request('trashed') ? '' : 'active' }}">Activos</a>
      <a href="{{ request()->fullUrlWithQuery(['trashed' => 1, 'page' => null]) }}" class="{{ request('trashed') ? 'active' : '' }}"><i class="bi bi-trash"></i> Reciclagem</a>
    </div>
  @endif
</form>
