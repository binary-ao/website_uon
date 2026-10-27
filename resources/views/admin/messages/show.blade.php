@extends('layouts.admin')
@section('title', 'Mensagem')

@section('content')
<x-admin.title :title="$message->subject" :crumbs="['Mensagens de contacto' => route('admin.messages.index')]" />
<div class="row g-4">
  <div class="col-lg-8">
    <div class="u-card">
      <dl class="row small mb-3">
        <dt class="col-sm-3">De</dt><dd class="col-sm-9">{{ $message->name }} &lt;<a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}">{{ $message->email }}</a>&gt;</dd>
        <dt class="col-sm-3">Telefone</dt><dd class="col-sm-9">{{ $message->phone ?? '—' }}</dd>
        <dt class="col-sm-3">Recebida</dt><dd class="col-sm-9">{{ $message->created_at->format('d/m/Y H:i') }}</dd>
        @if ($message->read_at)<dt class="col-sm-3">Lida</dt><dd class="col-sm-9">{{ $message->read_at->format('d/m/Y H:i') }} · {{ $message->reader?->name }}</dd>@endif
      </dl>
      <div class="border-top pt-3" style="white-space: pre-wrap">{{ $message->message }}</div>
    </div>
  </div>
  <div class="col-lg-4">
    <form method="post" action="{{ route('admin.messages.update', $message) }}" class="u-card u-form">
      @csrf @method('PUT')
      <x-admin.field name="status" label="Estado" type="select" :options="\App\Models\ContactMessage::STATUSES" :value="$message->status" />
      <button class="btn btn-uon w-100" type="submit">Actualizar estado</button>
    </form>
    <div class="u-card">
      <a class="btn btn-u-light w-100 mb-2" href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}"><i class="bi bi-reply"></i> Responder por email</a>
      <x-admin.delete-form :action="route('admin.messages.destroy', $message)" message="Eliminar definitivamente esta mensagem?" label="Eliminar mensagem" btn="btn btn-sm btn-outline-danger w-100" />
    </div>
  </div>
</div>
@endsection
