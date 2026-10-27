@extends('layouts.admin')
@section('title', 'Galeria e multimédia')

@section('content')
<x-admin.title title="Galeria e multimédia" subtitle="Imagens da página Galeria e vídeos (YouTube/Vimeo) da página Multimédia.">
  @can('create', \App\Models\GalleryItem::class)
    <a href="{{ route('admin.gallery.create') }}" class="btn btn-uon"><i class="bi bi-image"></i> Nova imagem</a>
    <a href="{{ route('admin.gallery.create', ['type' => 'video']) }}" class="btn btn-u-light"><i class="bi bi-play-btn"></i> Novo vídeo</a>
  @endcan
</x-admin.title>

<x-admin.list-filters :authors="$authors" placeholder="Título ou legenda" :trash="false" :dates="false">
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1" for="flt_type">Tipo</label>
    <select class="form-select" id="flt_type" name="type"><option value="">Todos</option>
      @foreach (\App\Models\GalleryItem::TYPES as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach
    </select>
  </div>
</x-admin.list-filters>

@if ($items->isEmpty())
  <div class="d-box"><div class="r-empty"><i class="bi bi-images"></i>A galeria está vazia.</div></div>
@else
  <div class="g-grid">
    @foreach ($items as $item)
      <figure class="g-item">
        <span class="g-badge">{{ $item->type === 'video' ? 'Vídeo' : 'Imagem' }}</span>
        <div class="g-thumb">
          @if ($item->media && $item->media->isImage())<img src="{{ $item->media->url('md') }}" alt="" loading="lazy">@else<i class="bi {{ $item->type === 'video' ? 'bi-play-circle' : 'bi-image' }}"></i>@endif
        </div>
        <figcaption>
          <a href="{{ route('admin.gallery.edit', $item) }}" class="fw-semibold">{{ \Illuminate\Support\Str::limit($item->title, 40) }}</a>
          <small class="d-flex justify-content-between align-items-center mt-1"><x-admin.status :status="$item->status" />
            @can('delete', $item)<x-admin.delete-form :action="route('admin.gallery.destroy', $item)" message="Remover este item da galeria?" label="Remover" :icon-only="true" />@endcan
          </small>
        </figcaption>
      </figure>
    @endforeach
  </div>
  <div class="mt-3">{{ $items->links() }}</div>
@endif
@endsection
