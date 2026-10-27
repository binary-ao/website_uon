@extends('layouts.admin')
@section('title', $document->exists ? 'Editar documento' : 'Novo documento')

@php
    $types = implode(', ', array_keys(config('uon.uploads.document_types'))).', '.implode(', ', array_keys(config('uon.uploads.image_types')));
    $maxMb = round(max(config('uon.uploads.max_document_kb'), config('uon.uploads.max_image_kb')) / 1024, 1);
@endphp

@section('content')
<x-admin.title :title="$document->exists ? $document->title : 'Novo documento'" :crumbs="['Documentos' => route('admin.documents.index')]" />

<form method="post" action="{{ $document->exists ? route('admin.documents.update', $document) : route('admin.documents.store') }}" enctype="multipart/form-data" class="u-form" data-dirty-check>
  @csrf
  @if ($document->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="u-card">
        <x-admin.field name="title" label="Título" :value="$document->title" required />
        <div class="row">
          <x-admin.field col="col-md-6 mb-3" name="category_id" label="Categoria" type="select" :options="$categories" :value="$document->category_id" empty="— Sem categoria —" help="Determina em que página o documento aparece." />
          <x-admin.field col="col-md-6 mb-3" name="document_date" label="Data do documento" type="date" :value="$document->document_date" />
        </div>
        <x-admin.field name="slug" label="Endereço (slug)" :value="$document->slug" help="Usado no URL de download." />
        <x-admin.field name="description" label="Descrição" type="textarea" rows="3" :value="$document->description" class="mb-0" />
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-cloud-arrow-up"></i> {{ $document->exists ? 'Substituir ficheiro (nova versão)' : 'Ficheiro' }}</div>
        <label class="u-upload position-relative">
          <input type="file" name="file" @required(! $document->exists) accept=".{{ str_replace(', ', ',.', $types) }}">
          <i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i>
          <span>Clique para escolher o ficheiro</span>
          <span class="u-file"></span>
        </label>
        <div class="form-text">Formatos: {{ $types }}. Máximo {{ $maxMb }} MB. O tipo é verificado pelo conteúdo do ficheiro no servidor.@if ($document->exists) As versões anteriores são preservadas.@endif</div>
        @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <x-admin.field name="version_notes" label="Nota da versão (opcional)" class="mt-3 mb-0" />
      </div>
    </div>
    <div class="col-xl-4">
      <div class="sticky-side">
        <x-admin.publish-box :model="$document" route-prefix="admin.documents" />
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-shield-lock"></i> Visibilidade</div>
          <x-admin.field name="visibility" label="Acesso" type="select" :options="\App\Models\Document::VISIBILITY" :value="$document->visibility" required help="Documentos restritos só podem ser descarregados por utilizadores autenticados e não aparecem nas listagens públicas." class="mb-0" />
        </div>
        @if ($document->exists && $document->currentVersion)
          <div class="u-card small">
            <div class="u-card-title"><i class="bi bi-link-45deg"></i> Ligação de download</div>
            <code class="d-block text-break">{{ $document->downloadUrl() }}</code>
          </div>
        @endif
      </div>
    </div>
  </div>
</form>

@if ($document->exists)
  <div class="d-box mt-4">
    <div class="d-box-head"><h2>Versões do ficheiro</h2></div>
    @if ($document->versions->isEmpty())
      <div class="r-empty"><i class="bi bi-file-earmark-x"></i>Ainda não foi carregado nenhum ficheiro.</div>
    @else
      <div class="table-responsive"><table class="table">
        <thead><tr><th>Versão</th><th>Ficheiro original</th><th>Tamanho</th><th>Carregado</th><th>Nota</th><th class="text-end">Acções</th></tr></thead>
        <tbody>
          @foreach ($document->versions as $v)
            <tr>
              <td><strong>v{{ $v->version }}</strong> @if ($v->id === $document->current_version_id)<span class="s-badge ok">Em vigor</span>@endif</td>
              <td><small>{{ $v->original_name }}</small></td>
              <td><small>{{ strtoupper($v->extension) }} · {{ $v->humanSize() }}</small></td>
              <td><small>{{ $v->created_at?->format('d/m/Y H:i') }}<br>{{ $v->uploader?->name }}</small></td>
              <td><small>{{ $v->notes ?? '—' }}</small></td>
              <td class="text-end text-nowrap">
                <a class="u-iconbtn" href="{{ route('documents.download', $document->slug) }}?versao={{ $v->version }}" title="Descarregar esta versão" aria-label="Descarregar versão {{ $v->version }}"><i class="bi bi-download"></i></a>
                @if ($v->id !== $document->current_version_id)
                  @can('update', $document)
                    <form method="post" action="{{ route('admin.documents.versions.current', [$document, $v]) }}" class="d-inline" data-confirm="Repor a versão {{ $v->version }} como versão em vigor?">@csrf
                      <button class="u-iconbtn" title="Repor esta versão" aria-label="Repor versão {{ $v->version }}"><i class="bi bi-arrow-counterclockwise"></i></button>
                    </form>
                  @endcan
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table></div>
    @endif
  </div>
  @if (($usage ?? collect())->isNotEmpty())
    <p class="small text-muted mt-2"><i class="bi bi-link"></i> Associado a {{ $usage->count() }} página(s)/notícia(s).</p>
  @endif
  @can('delete', $document)
    <div class="mt-4 text-end"><x-admin.delete-form :action="route('admin.documents.destroy', $document)" message="Mover este documento para a reciclagem? O download deixará de estar disponível." label="Mover para a reciclagem" /></div>
  @endcan
@endif
@endsection
