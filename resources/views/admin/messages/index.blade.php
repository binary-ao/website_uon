@extends('layouts.admin')
@section('title', 'Mensagens de contacto')

@section('content')
<x-admin.title title="Mensagens de contacto" subtitle="Mensagens enviadas através do formulário da página Contactos." />
<div class="adm-tabs">
  <a href="{{ route('admin.messages.index') }}" class="{{ $status ? '' : 'active' }}">Caixa de entrada</a>
  @foreach (\App\Models\ContactMessage::STATUSES as $k => $l)<a href="{{ route('admin.messages.index', ['status' => $k]) }}" class="{{ $status === $k ? 'active' : '' }}">{{ $l }}s</a>@endforeach
</div>
<form method="get" class="adm-filters d-flex gap-2" role="search">
  @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
  <input type="search" class="form-control w-auto" name="q" value="{{ request('q') }}" placeholder="Nome, email, assunto" aria-label="Pesquisar">
  <button class="btn btn-u-dark"><i class="bi bi-funnel"></i></button>
</form>
<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-envelope-open"></i>Sem mensagens.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Remetente</th><th>Assunto</th><th>Recebida</th><th>Estado</th></tr></thead>
    <tbody>
      @foreach ($items as $m)
        <tr>
          <td>@if ($m->status === 'new')<strong>{{ $m->name }}</strong>@else{{ $m->name }}@endif<small>{{ $m->email }}</small></td>
          <td><a class="row-title" href="{{ route('admin.messages.show', $m) }}">{{ $m->subject }}</a><small>{{ \Illuminate\Support\Str::limit($m->message, 80) }}</small></td>
          <td><small>{{ $m->created_at->format('d/m/Y H:i') }}</small></td>
          <td><span class="s-badge {{ ['new' => 'info', 'read' => 'ok', 'archived' => 'off'][$m->status] }}">{{ \App\Models\ContactMessage::STATUSES[$m->status] }}</span></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
