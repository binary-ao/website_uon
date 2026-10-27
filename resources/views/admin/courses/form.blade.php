@extends('layouts.admin')
@section('title', $course->exists ? 'Editar curso' : 'Novo curso')

@php
    $subjects = old('subjects', $course->exists ? $course->subjects->map(fn ($s) => $s->only(['year', 'semester', 'code', 'name', 'hours', 'credits', 'is_optional']))->all() : []);
    $careersText = old('careers', collect($course->careers ?? [])->map(fn ($c) => trim(($c['title'] ?? '').(($c['text'] ?? '') !== '' ? ' | '.$c['text'] : '')))->implode("\n"));
@endphp

@section('content')
<x-admin.title :title="$course->exists ? $course->name : 'Novo curso'" :crumbs="['Cursos' => route('admin.courses.index')]" />

<form method="post" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}" class="u-form" data-dirty-check>
  @csrf
  @if ($course->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="u-card">
        <x-admin.field name="name" label="Nome do curso" :value="$course->name" required />
        <div class="row">
          <x-admin.field col="col-md-4 mb-3" name="degree_id" label="Grau académico" type="select" :options="$degrees" :value="$course->degree_id" required empty="— Escolher —" />
          <x-admin.field col="col-md-8 mb-3" name="organic_unit_id" label="Unidade orgânica" type="select" :options="$units" :value="$course->organic_unit_id" empty="— Sem unidade —" />
        </div>
        <div class="row">
          <x-admin.field col="col-md-4 mb-3" name="duration" label="Duração" :value="$course->duration" placeholder="ex.: 4 anos" />
          <x-admin.field col="col-md-4 mb-3" name="modality" label="Modalidade" :value="$course->modality" placeholder="ex.: Presencial" />
          <x-admin.field col="col-md-4 mb-3" name="regime" label="Regime" :value="$course->regime" placeholder="ex.: Laboral / Pós-laboral" />
        </div>
        <x-admin.field name="summary" label="Resumo" type="textarea" rows="2" :value="$course->summary" class="mb-0" />
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-journal-text"></i> Apresentação</div>
        <label class="visually-hidden" for="f_presentation">Apresentação</label>
        <textarea id="f_presentation" name="presentation" data-editor rows="8">{{ old('presentation', $course->presentation) }}</textarea>
      </div>
      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-bullseye"></i> Objectivos</div>
        <label class="visually-hidden" for="f_objectives">Objectivos</label>
        <textarea id="f_objectives" name="objectives" data-editor rows="8">{{ old('objectives', $course->objectives) }}</textarea>
      </div>
      <div class="u-card">
        <div class="row">
          <x-admin.field col="col-md-6 mb-3" name="entry_profile" label="Perfil de entrada" type="textarea" rows="4" :value="$course->entry_profile" />
          <x-admin.field col="col-md-6 mb-3" name="exit_profile" label="Perfil de saída" type="textarea" rows="4" :value="$course->exit_profile" />
        </div>
        <x-admin.field name="careers" label="Saídas profissionais" type="textarea" rows="4" :value="$careersText" help='Uma por linha: "Área | Profissão/descrição".' class="mb-0" />
      </div>

      <div class="u-card" data-repeater>
        <div class="u-card-title"><i class="bi bi-table"></i> Plano curricular</div>
        <div class="table-responsive">
          <table class="table pc-table mb-2">
            <thead><tr><th style="width:3rem">#</th><th style="width:6rem">Ano</th><th style="width:7rem">Semestre</th><th style="width:7rem">Código</th><th>Unidade curricular</th><th style="width:6rem">Horas</th><th style="width:6rem">Créditos</th><th style="width:4.5rem" class="text-center">Optativa</th><th style="width:3rem"><span class="visually-hidden">Remover</span></th></tr></thead>
            <tbody>
              @foreach ($subjects as $i => $s)
                @include('admin.courses.subject-row', ['i' => $i, 's' => $s, 'n' => $loop->iteration])
              @endforeach
            </tbody>
          </table>
        </div>
        <template>@include('admin.courses.subject-row', ['i' => '__INDEX__', 's' => [], 'n' => ''])</template>
        <button type="button" class="pc-add" data-repeater-add><i class="bi bi-plus-lg"></i> Adicionar unidade curricular</button>
        <x-admin.field name="curriculum_note" label="Nota ao plano curricular" type="textarea" rows="2" :value="$course->curriculum_note" class="mt-3 mb-0" />
      </div>

      <x-admin.seo :model="$course" />
    </div>
    <div class="col-xl-4">
      <div class="sticky-side">
        <x-admin.publish-box :model="$course" preview-type="courses" route-prefix="admin.courses" />
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-gear"></i> Detalhes</div>
          <x-admin.field name="slug" label="Endereço (slug)" :value="$course->slug" />
          <x-admin.field name="icon" label="Ícone (sem imagem)" :value="$course->icon" placeholder="bi-mortarboard" help="Nome de um ícone Bootstrap Icons." />
          <x-admin.field name="coordinator_name" label="Coordenação" :value="$course->coordinator_name" />
          <x-admin.field name="contact_email" label="Email de contacto" type="email" :value="$course->contact_email" />
          <x-admin.field name="sort_order" label="Ordem" type="number" :value="$course->sort_order ?? 0" class="mb-0" />
        </div>
        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-image"></i> Imagem</div>
          <x-admin.media-field name="featured_media_id" :media="$course->featuredMedia" />
        </div>
        <x-admin.revisions :model="$course" />
      </div>
    </div>
  </div>
</form>

@if ($course->exists)
  @can('delete', $course)
    <div class="mt-4 text-end"><x-admin.delete-form :action="route('admin.courses.destroy', $course)" message="Mover este curso para a reciclagem?" label="Mover para a reciclagem" /></div>
  @endcan
@endif
@endsection
