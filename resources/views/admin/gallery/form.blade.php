@extends('layouts.admin')
@section('title', $item->exists ? 'Editar item da galeria' : 'Novo item da galeria')

@section('content')
<x-admin.title :title="$item->exists ? $item->title : 'Novo item da galeria'" :crumbs="['Galeria e multimédia' => route('admin.gallery.index')]" />

<form method="post" action="{{ $item->exists ? route('admin.gallery.update', $item) : route('admin.gallery.store') }}" class="u-form">
  @csrf
  @if ($item->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="u-card">
        <div class="mb-3">
          <label for="f_type" class="form-label req">Tipo</label>
          <select class="form-select" id="f_type" name="type" data-type-toggle>
            @foreach (\App\Models\GalleryItem::TYPES as $k => $l)<option value="{{ $k }}" @selected(old('type', $item->type) === $k)>{{ $l }}</option>@endforeach
          </select>
        </div>
        <x-admin.field name="title" label="Título" :value="$item->title" required />
        <x-admin.field name="caption" label="Legenda / descrição curta" :value="$item->caption" />
        <div data-type-only="image">
          <x-admin.media-field name="media_id" label="Imagem" :media="$item->media" required help="O texto alternativo da imagem é editado na biblioteca multimédia." />
        </div>
        <div data-type-only="video">
          <x-admin.field name="video_url" label="URL do vídeo (YouTube ou Vimeo)" :value="$item->video_url" placeholder="https://www.youtube.com/watch?v=..." />
          <x-admin.field name="duration_label" label="Duração / tipo" :value="$item->duration_label" placeholder="ex.: Vídeo institucional · 3min" />
        </div>
        <div class="row">
          <x-admin.field col="col-md-6 mb-0" name="category_id" label="Categoria / álbum" type="select" :options="$categories" :value="$item->category_id" empty="— Nenhuma —" />
          <x-admin.field col="col-md-6 mb-0" name="sort_order" label="Ordem" type="number" :value="$item->sort_order ?? 0" />
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <x-admin.publish-box :model="$item" route-prefix="admin.gallery" />
    </div>
  </div>
</form>
@endsection
