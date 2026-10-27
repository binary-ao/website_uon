@extends('layouts.public')

{{-- Curso — curso-detalhe.html (Template D) --}}
@php
    $ensino = \App\Models\Page::where('path', 'ensino')->first();
    $offer = \App\Models\Page::where('path', 'ensino/oferta-formativa')->first();
    $contacts = \App\Models\Page::published()->where('path', 'contactos')->first();
    $sections = array_filter([
        'apresentacao' => filled($course->presentation) ? 'Apresentação' : null,
        'objectivos' => filled($course->objectives) ? 'Objectivos' : null,
        'perfil-entrada' => filled($course->entry_profile) ? 'Perfil de Entrada' : null,
        'perfil-saida' => filled($course->exit_profile) ? 'Perfil de Saída' : null,
        'plano-curricular' => ($years->isNotEmpty() || filled($course->curriculum_note)) ? 'Plano Curricular' : null,
        'saidas-profissionais' => ! empty($course->careers) ? 'Saídas Profissionais' : null,
    ]);
    $first = array_key_first($sections);
@endphp

@section('title', (string) ($course->meta_title ?: trim(($course->degree?->name ? $course->degree->name.' em ' : '').$course->name)))
@section('meta_description', (string) ($course->meta_description ?: $course->summary))
@if ($course->featuredMedia)
  @section('og_image', $course->featuredMedia->url())
@endif

@section('page_header')
<div class="breadcrumb-bar py-2">
  <div class="container-xl">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
        @if ($ensino)<li class="breadcrumb-item"><a href="{{ $ensino->url() }}">{{ $ensino->title }}</a></li>@endif
        @if ($offer)<li class="breadcrumb-item"><a href="{{ $offer->url() }}">{{ $offer->title }}</a></li>@endif
        <li class="breadcrumb-item active" aria-current="page">{{ $course->name }}</li>
      </ol>
    </nav>
  </div>
</div>
<div class="page-header course-header">
  <div class="container-xl position-relative">
    <p class="eyebrow">{{ $course->degree?->name }}@if ($course->is_demo && setting('show_demo_badges')) · <span class="demo-badge">Exemplo</span>@endif</p>
    <h1>{{ $course->name }}</h1>
    @if ($course->summary)<p>{{ $course->summary }}</p>@endif
    <div class="course-meta-bar">
      @if ($course->duration)<div class="item"><i class="bi bi-clock"></i> {{ $course->duration }}</div>@endif
      @if ($course->modality)<div class="item"><i class="bi bi-person-workspace"></i> {{ $course->modality }}</div>@endif
      @if ($course->regime)<div class="item"><i class="bi bi-calendar-week"></i> {{ $course->regime }}</div>@endif
      @if ($course->unit)<div class="item"><i class="bi bi-building"></i> {{ $course->unit->name }}</div>@endif
      @if ($course->coordinator_name)<div class="item"><i class="bi bi-person-badge"></i> {{ $course->coordinator_name }}</div>@endif
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="section">
  <div class="container-xl">
    @if (! $sections)
      @include('partials.empty', ['icon' => 'bi-hourglass-split', 'title' => 'Informação do curso em preparação', 'text' => 'A informação detalhada deste curso será publicada brevemente.', 'boxed' => true])
    @else
    <div class="row gy-5">
      <div class="col-lg-3 d-none d-lg-block">
        <div class="course-toc">
          <nav class="list-group list-group-flush" aria-label="Secções do curso">
            @foreach ($sections as $id => $label)
              <a href="#{{ $id }}" class="list-group-item{{ $id === $first ? ' active' : '' }}">{{ $label }}</a>
            @endforeach
          </nav>
        </div>
      </div>
      <div class="col-lg-9">
        @isset($sections['apresentacao'])
          <div id="apresentacao" class="mb-5">
            <h2 class="h3">Apresentação</h2>
            <div class="article-body">{!! $course->presentation !!}</div>
          </div>
        @endisset
        @isset($sections['objectivos'])
          <div id="objectivos" class="mb-5">
            <h2 class="h3">Objectivos</h2>
            <div class="article-body">{!! $course->objectives !!}</div>
          </div>
        @endisset
        @isset($sections['perfil-entrada'])
          <div id="perfil-entrada" class="mb-5">
            <h2 class="h3">Perfil de Entrada</h2>
            <div class="profile-box"><p class="mb-0 text-muted-brand">{!! nl2br(e($course->entry_profile)) !!}</p></div>
          </div>
        @endisset
        @isset($sections['perfil-saida'])
          <div id="perfil-saida" class="mb-5">
            <h2 class="h3">Perfil de Saída</h2>
            <div class="profile-box"><p class="mb-0 text-muted-brand">{!! nl2br(e($course->exit_profile)) !!}</p></div>
          </div>
        @endisset
        @isset($sections['plano-curricular'])
          <div id="plano-curricular" class="mb-5">
            {{-- Apresentação do plano curricular segundo o curso.html do Universidades.ao (separadores por ano + cartões por semestre) --}}
            @php
                $plan = \App\Support\Curriculum::build($course);
                $withCredits = \App\Support\Curriculum::hasCredits($plan);
                $withCodes = \App\Support\Curriculum::hasCodes($course);
                $hasOptional = $course->subjects->contains('is_optional', true);
            @endphp
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
              <h2 class="h3 mb-0">Plano Curricular</h2>
              @if ($plan)
                <a href="{{ route('courses.curriculum.pdf', $course->slug) }}" class="btn-link-brand" rel="nofollow"><i class="bi bi-file-earmark-pdf"></i> Descarregar PDF</a>
              @endif
            </div>
            @if ($plan)
              <ul class="nav uon-years mb-4" role="tablist" aria-label="Anos curriculares">
                @foreach ($plan as $i => $year)
                  <li class="nav-item" role="presentation">
                    <button class="nav-link{{ $i === 0 ? ' active' : '' }}" id="ano{{ $year['year'] }}-tab" data-bs-toggle="pill" data-bs-target="#ano{{ $year['year'] }}" type="button" role="tab" aria-controls="ano{{ $year['year'] }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                      {{ $year['label'] }}<small>{{ $withCredits ? \App\Support\Curriculum::number($year['credits']).' créditos' : $year['hours'].' horas' }}</small>
                    </button>
                  </li>
                @endforeach
              </ul>
              <div class="tab-content">
                @foreach ($plan as $i => $year)
                  <div class="tab-pane fade{{ $i === 0 ? ' show active' : '' }}" id="ano{{ $year['year'] }}" role="tabpanel" aria-labelledby="ano{{ $year['year'] }}-tab" tabindex="0">
                    <div class="row g-4">
                      @foreach ($year['semesters'] as $sem)
                        <div class="col-12">
                          <div class="uon-sem">
                            <div class="uon-sem-head">
                              <h3>{{ $sem['label'] }}</h3>
                              <span class="uon-sem-badge">{{ $withCredits ? \App\Support\Curriculum::number($sem['credits']).' créditos' : $sem['hours'].' h' }}</span>
                            </div>
                            <div class="table-responsive">
                              <table class="table table-borderless align-middle mb-0">
                                <thead><tr>@if ($withCodes)<th scope="col">Código</th>@endif<th scope="col">Unidade curricular</th><th scope="col" class="num">CH</th>@if ($withCredits)<th scope="col" class="num">Créditos</th>@endif</tr></thead>
                                <tbody>
                                  @foreach ($sem['subjects'] as $s)
                                    <tr>
                                      @if ($withCodes)<td class="code">{{ $s->code ?? '—' }}</td>@endif
                                      <td>{{ $s->name }}@if ($s->is_optional)<span class="uon-type-op" title="Optativa">OP</span>@endif</td>
                                      <td class="num">{{ $s->hours ?? '—' }}</td>
                                      @if ($withCredits)<td class="num">{{ \App\Support\Curriculum::number($s->credits) }}</td>@endif
                                    </tr>
                                  @endforeach
                                </tbody>
                                <tfoot><tr>@if ($withCodes)<td></td>@endif<td>Total</td><td class="num">{{ $sem['hours'] }}</td>@if ($withCredits)<td class="num">{{ \App\Support\Curriculum::number($sem['credits']) }}</td>@endif</tr></tfoot>
                              </table>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endforeach
              </div>
              <p class="uon-legend mt-3 mb-0">@if ($hasOptional)<span class="uon-type-op ms-0">OP</span> Unidade curricular optativa · @endif CH = Carga horária total (horas)@if ($withCredits) · Créditos = unidades de crédito @endif</p>
            @endif
            @if ($course->curriculum_note)<p class="text-muted-brand mt-3">{{ $course->curriculum_note }}</p>@endif
          </div>
        @endisset
        @isset($sections['saidas-profissionais'])
          <div id="saidas-profissionais" class="mb-5">
            <h2 class="h3">Saídas Profissionais</h2>
            <div class="row g-3">
              @foreach ($course->careers as $career)
                <div class="col-md-4"><div class="card-directory"><h3 class="h6">{{ $career['title'] ?? '' }}</h3>@if (! empty($career['text']))<p class="text-muted-brand small mb-0">{{ $career['text'] }}</p>@endif</div></div>
              @endforeach
            </div>
          </div>
        @endisset

        @if ($contacts)
          <div class="cta-banner">
            <div class="row align-items-center">
              <div class="col-lg-8 position-relative">
                <h2>Interessado(a) neste curso?</h2>
                <p class="mb-0">Contacte-nos para mais informações sobre o acesso e a candidatura.@if ($course->contact_email) Email: {{ $course->contact_email }}@endif</p>
              </div>
              <div class="col-lg-4 text-lg-end mt-4 mt-lg-0 position-relative">
                <a href="{{ $contacts->url() }}" class="btn btn-brand btn-lg">Pedir informações</a>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
    @endif
  </div>
</section>
@endsection
