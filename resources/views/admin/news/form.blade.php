@extends('layouts.admin')
@php $typeLabel = \App\Models\News::TYPES[old('type', $news->type)] ?? 'Notícia'; @endphp
@section('title', $news->exists ? 'Editar '.mb_strtolower($typeLabel) : 'Novo conteúdo')

@section('content')
<x-admin.title :title="$news->exists ? $news->title : ($news->type === 'announcement' ? 'Novo comunicado' : 'Nova notícia')" :crumbs="['Notícias e comunicados' => route('admin.news.index')]">
  @if ($news->exists)
    @can('create', \App\Models\News::class)
      <form method="post" action="{{ route('admin.news.duplicate', $news) }}">@csrf<button class="btn btn-u-light"><i class="bi bi-copy"></i> Duplicar</button></form>
    @endcan
  @endif
</x-admin.title>

<form method="post" action="{{ $news->exists ? route('admin.news.update', $news) : route('admin.news.store') }}" class="u-form" data-dirty-check>
  @csrf
  @if ($news->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="u-card">
        <div class="row">
          <x-admin.field col="col-md-4 mb-3" name="type" label="Tipo" type="select" :options="\App\Models\News::TYPES" :value="$news->type" required />
          <x-admin.field col="col-md-8 mb-3" name="category_id" label="Categoria" type="select" :options="$categories" :value="$news->category_id" empty="— Sem categoria —" />
        </div>
        <x-admin.field name="title" label="Título" :value="$news->title" required />
        <x-admin.field name="slug" label="Endereço (slug)" :value="$news->slug" help="Gerado automaticamente a partir do título. Alterar o endereço de um conteúdo publicado cria um redireccionamento." />
        <x-admin.field name="excerpt" label="Resumo" type="textarea" rows="3" :value="$news->excerpt" help="Apresentado nas listagens e como descrição por omissão." class="mb-0" />
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-text-paragraph"></i> Texto</div>
        <label for="f_body" class="visually-hidden">Texto</label>
        <textarea id="f_body" name="body" data-editor class="form-control" rows="16">{{ old('body', $news->body) }}</textarea>
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-paperclip"></i> Documentos anexos</div>
        @php $selectedDocs = old('document_ids', $news->exists ? $news->documents->pluck('id')->all() : []); @endphp
        <label for="f_docs" class="form-label">Documentos apresentados no fim do artigo</label>
        <select multiple class="form-select" id="f_docs" name="document_ids[]" size="5">
          @foreach ($documents as $doc)<option value="{{ $doc->id }}" @selected(in_array($doc->id, $selectedDocs))>{{ $doc->title }}</option>@endforeach
        </select>
      </div>

      <x-admin.seo :model="$news" />
    </div>
    <div class="col-xl-4">
      <div class="sticky-side">
        <x-admin.publish-box :model="$news" preview-type="news" route-prefix="admin.news" />
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-image"></i> Imagem de destaque</div>
          <x-admin.media-field name="featured_media_id" :media="$news->featuredMedia" />
        </div>
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-tags"></i> Classificação</div>
          <x-admin.field name="tags" label="Etiquetas" :value="$tagString" help="Separadas por vírgulas." />
          <x-admin.field name="author_name" label="Assinatura" :value="$news->author_name" help="Ex.: Gabinete de Comunicação. Se vazio, não é apresentada." />
          @can('publish', $news->exists ? $news : \App\Models\News::class)
            <x-admin.field name="is_featured" label="Destacar na página inicial" type="checkbox" :value="$news->is_featured" class="mb-0" />
          @endcan
        </div>
        <x-admin.revisions :model="$news" />
      </div>
    </div>
  </div>
</form>

@if ($news->exists)
  @can('delete', $news)
    <div class="mt-4 text-end"><x-admin.delete-form :action="route('admin.news.destroy', $news)" message="Mover este conteúdo para a reciclagem?" label="Mover para a reciclagem" /></div>
  @endcan
@endif
@endsection
