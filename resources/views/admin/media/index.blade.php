@extends('layouts.admin')
@section('title', 'Biblioteca multimédia')

@php
    $imgTypes = implode(', ', array_keys(config('uon.uploads.image_types')));
    $docTypes = implode(', ', array_keys(config('uon.uploads.document_types')));
@endphp

@section('content')
<x-admin.title title="Biblioteca multimédia" subtitle="Ficheiros reutilizáveis em páginas, notícias, eventos, cursos e configurações." />

<div class="row g-4">
  <div class="col-xl-4 order-xl-2">
    <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="u-card u-form">
      @csrf
      <div class="u-card-title"><i class="bi bi-cloud-arrow-up"></i> Carregar ficheiro</div>
      <label class="u-upload position-relative mb-3">
        <input type="file" name="file" required>
        <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
        <span>Clique para escolher</span>
        <span class="u-file"></span>
      </label>
      <x-admin.field name="title" label="Título" />
      <x-admin.field name="alt_text" label="Texto alternativo (imagens)" help="Descreva a imagem para utilizadores de leitores de ecrã." />
      <p class="form-text">Imagens: {{ $imgTypes }} (máx. {{ round(config('uon.uploads.max_image_kb') / 1024, 1) }} MB; re-codificadas e redimensionadas até {{ config('uon.uploads.image_max_width') }} px). Documentos: {{ $docTypes }} (máx. {{ round(config('uon.uploads.max_document_kb') / 1024, 1) }} MB).</p>
      <button class="btn btn-uon w-100" type="submit">Carregar</button>
    </form>
  </div>
  <div class="col-xl-8 order-xl-1">
    <form method="get" class="adm-filters" role="search">
      <div class="row g-2 align-items-end">
        <div class="col-md-5"><label class="form-label small mb-1" for="mq">Pesquisar</label><input type="search" id="mq" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nome, título, texto alternativo"></div>
        <div class="col-6 col-md-3"><label class="form-label small mb-1" for="mt">Tipo</label>
          <select id="mt" name="type" class="form-select"><option value="">Todos</option><option value="image" @selected(request('type') === 'image')>Imagens</option><option value="document" @selected(request('type') === 'document')>Documentos</option></select></div>
        <div class="col-6 col-md-2"><label class="form-label small mb-1" for="mu">Carregado por</label>
          <select id="mu" name="uploader" class="form-select"><option value="">Todos</option>@foreach ($uploaders as $id => $n)<option value="{{ $id }}" @selected((string) request('uploader') === (string) $id)>{{ $n }}</option>@endforeach</select></div>
        <div class="col-auto"><button class="btn btn-u-dark" type="submit"><i class="bi bi-funnel"></i></button></div>
      </div>
    </form>
    @if ($items->isEmpty())
      <div class="d-box"><div class="r-empty"><i class="bi bi-collection"></i>Sem ficheiros.</div></div>
    @else
      <div class="g-grid">
        @foreach ($items as $m)
          <figure class="g-item">
            <a href="{{ route('admin.media.edit', $m) }}" class="d-block text-reset text-decoration-none">
              <div class="g-thumb">@if ($m->isImage())<img src="{{ $m->url('md') }}" alt="{{ $m->altText() }}" loading="lazy">@else<i class="bi {{ $m->icon() }}"></i>@endif</div>
              <figcaption>{{ \Illuminate\Support\Str::limit($m->title ?: $m->original_name, 34) }}<small>{{ strtoupper($m->extension) }} · {{ $m->humanSize() }}@if ($m->width) · {{ $m->width }}×{{ $m->height }}@endif</small></figcaption>
            </a>
          </figure>
        @endforeach
      </div>
      <div class="mt-3">{{ $items->links() }}</div>
    @endif
  </div>
</div>
@endsection
