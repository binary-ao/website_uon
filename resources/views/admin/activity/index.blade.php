@extends('layouts.admin')
@section('title', 'Registo de actividade')

@section('content')
<x-admin.title title="Registo de actividade" subtitle="Auditoria das acções administrativas e de autenticação." />
<form method="get" class="adm-filters" role="search">
  <div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label small mb-1" for="aq">Pesquisar</label><input type="search" id="aq" class="form-control" name="q" value="{{ request('q') }}"></div>
    <div class="col-6 col-md-2"><label class="form-label small mb-1" for="au">Utilizador</label>
      <select id="au" name="user" class="form-select"><option value="">Todos</option>@foreach ($users as $id => $n)<option value="{{ $id }}" @selected((string) request('user') === (string) $id)>{{ $n }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><label class="form-label small mb-1" for="aa">Área</label>
      <select id="aa" name="action" class="form-select"><option value="">Todas</option>@foreach ($actions as $k => $l)<option value="{{ $k }}" @selected(request('action') === $k)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><label class="form-label small mb-1" for="af">Desde</label><input type="date" id="af" name="from" class="form-control" value="{{ request('from') }}"></div>
    <div class="col-6 col-md-2"><label class="form-label small mb-1" for="at">Até</label><input type="date" id="at" name="to" class="form-control" value="{{ request('to') }}"></div>
    <div class="col-auto"><button class="btn btn-u-dark"><i class="bi bi-funnel"></i> Filtrar</button></div>
  </div>
</form>
<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-clock-history"></i>Sem registos.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Data</th><th>Utilizador</th><th>Acção</th><th>Descrição</th><th>IP</th></tr></thead>
    <tbody>
      @foreach ($items as $a)
        <tr>
          <td><small>{{ $a->created_at?->format('d/m/Y H:i:s') }}</small></td>
          <td><small>{{ $a->user?->name ?? '—' }}</small></td>
          <td><code class="small">{{ $a->action }}</code></td>
          <td><small>{{ $a->description }}</small></td>
          <td><small>{{ $a->ip_address }}</small></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
