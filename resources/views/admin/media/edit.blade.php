@extends('layouts.admin')
@section('title', 'Ficheiro')

@section('content')
<x-admin.title :title="$media->title ?: $media->original_name" :crumbs="['Biblioteca multimédia' => route('admin.media.index')]" />
<div class="row g-4">
  <div class="col-lg-6">
    <div class="u-card text-center">
      @if ($media->isImage())
        <img src="{{ $media->url() }}" alt="{{ $media->altText() }}" class="img-fluid rounded" style="max-height:420px">
      @else
        <i class="bi {{ $media->icon() }}" style="font-size:4rem;color:var(--primary-600)"></i>
      @endif
      <dl class="row small text-start mt-3 mb-0">
        <dt class="col-4">Nome original</dt><dd class="col-8">{{ $media->original_name }}</dd>
        <dt class="col-4">Armazenamento</dt><dd class="col-8"><code>{{ $media->path }}</code></dd>
        <dt class="col-4">Tipo MIME</dt><dd class="col-8">{{ $media->mime_type }}</dd>
        <dt class="col-4">Tamanho</dt><dd class="col-8">{{ $media->humanSize() }}@if ($media->width) · {{ $media->width }}×{{ $media->height }} px @endif</dd>
        <dt class="col-4">Carregado</dt><dd class="col-8">{{ $media->created_at?->format('d/m/Y H:i') }} · {{ $media->uploader?->name ?? '—' }}</dd>
        <dt class="col-4">URL público</dt><dd class="col-8"><a href="{{ $media->url() }}" target="_blank" rel="noopener" class="text-break">{{ $media->url() }}</a></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-6">
    <form method="post" action="{{ route('admin.media.update', $media) }}" class="u-card u-form">
      @csrf @method('PUT')
      <div class="u-card-title"><i class="bi bi-pencil"></i> Dados do ficheiro</div>
      <x-admin.field name="title" label="Título" :value="$media->title" />
      <x-admin.field name="alt_text" label="Texto alternativo" :value="$media->alt_text" help="Obrigatório para acessibilidade em imagens informativas." />
      <x-admin.field name="description" label="Descrição" type="textarea" rows="3" :value="$media->description" />
      <button class="btn btn-uon" type="submit">Guardar</button>
    </form>
    <div class="u-card">
      <div class="u-card-title"><i class="bi bi-link"></i> Utilização</div>
      @if ($usages)
        <ul class="small mb-0">@foreach ($usages as $use)<li>{{ $use }}</li>@endforeach</ul>
        <p class="small text-muted mt-2 mb-0">Ficheiros em uso não podem ser eliminados.</p>
      @else
        <p class="small text-muted mb-3">Este ficheiro não está associado a nenhum conteúdo.</p>
        @can('media.manage')
          <x-admin.delete-form :action="route('admin.media.destroy', $media)" message="Eliminar definitivamente este ficheiro?" label="Eliminar ficheiro" />
        @endcan
      @endif
    </div>
  </div>
</div>
@endsection
