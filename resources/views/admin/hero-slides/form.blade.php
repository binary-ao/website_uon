@extends('layouts.admin')
@section('title', $slide->exists ? 'Editar diapositivo' : 'Novo diapositivo')

@section('content')
<x-admin.title :title="$slide->exists ? $slide->title : 'Novo diapositivo'" :crumbs="['Banner da página inicial' => route('admin.hero-slides.index')]" />

<form method="post" action="{{ $slide->exists ? route('admin.hero-slides.update', $slide) : route('admin.hero-slides.store') }}" class="u-form" data-dirty-check>
  @csrf @if ($slide->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-type"></i> Textos</div>
        <x-admin.field name="eyebrow" label="Sobretítulo" :value="$slide->eyebrow" placeholder="Universidade Onze de Novembro" help="Linha pequena acima do título, precedida do traço vermelho." />
        <div class="row">
          <x-admin.field col="col-md-7 mb-3" name="title" label="Título" :value="$slide->title" required placeholder="Formar profissionais" />
          <x-admin.field col="col-md-5 mb-3" name="title_highlight" label="Continuação do título (opcional)" :value="$slide->title_highlight" placeholder="para uma Angola melhor" help="Mostrada na linha seguinte do título." />
        </div>
        <x-admin.field name="text" label="Texto" type="textarea" rows="3" :value="$slide->text" help="Até 500 caracteres." class="mb-0" />
      </div>
      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-hand-index"></i> Botões</div>
        <div class="row">
          <x-admin.field col="col-md-5 mb-3" name="primary_label" label="Botão principal — texto" :value="$slide->primary_label" placeholder="Conheça a UON" />
          <x-admin.field col="col-md-7 mb-3" name="primary_url" label="Botão principal — destino" :value="$slide->primary_url" placeholder="/instituicao/sobre" />
          <x-admin.field col="col-md-5 mb-0" name="secondary_label" label="Botão secundário — texto" :value="$slide->secondary_label" placeholder="Cursos" />
          <x-admin.field col="col-md-7 mb-0" name="secondary_url" label="Botão secundário — destino" :value="$slide->secondary_url" placeholder="/ensino/oferta-formativa" />
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="sticky-side">
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-eye"></i> Apresentação</div>
          <x-admin.field name="is_active" label="Activo" type="checkbox" :value="$slide->is_active" />
          <x-admin.field name="starts_at" label="Mostrar a partir de" type="datetime-local" :value="$slide->starts_at" />
          <x-admin.field name="ends_at" label="Mostrar até" type="datetime-local" :value="$slide->ends_at" help="Opcional. Útil para campanhas (ex.: candidaturas)." />
          <button type="submit" class="btn btn-uon w-100"><i class="bi bi-check2"></i> Guardar</button>
        </div>
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-image"></i> Imagem de fundo</div>
          <x-admin.media-field name="media_id" label="Fotografia" :media="$slide->media" required help="Recomendado: 1920×800 px ou superior, horizontal. O lado esquerdo fica escurecido para o texto ser legível." />
          <x-admin.field name="image_position" label="Ponto focal da imagem" type="select" :options="\App\Models\HeroSlide::POSITIONS" :value="$slide->image_position" class="mb-0" help="Parte da imagem que se mantém visível quando é recortada (ecrãs estreitos)." />
        </div>
      </div>
    </div>
  </div>
</form>

@if ($slide->exists)
  <div class="mt-4 text-end"><x-admin.delete-form :action="route('admin.hero-slides.destroy', $slide)" message="Eliminar este diapositivo?" label="Eliminar diapositivo" /></div>
@endif
@endsection
