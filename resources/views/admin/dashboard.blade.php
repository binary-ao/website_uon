@extends('layouts.admin')
@section('title', 'Painel')

@section('content')
<x-admin.title title="Painel" subtitle="Bem-vindo(a), {{ auth()->user()->name }}. Indicadores calculados em tempo real a partir da base de dados." />

@php $u = auth()->user(); @endphp
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a class="d-stat" href="{{ route('admin.pages.index') }}"><span class="ic"><i class="bi bi-file-earmark-richtext"></i></span>
      <div><strong>{{ $stats['pages']['total'] }}</strong><small>Páginas</small><div class="d-sub">{{ $stats['pages']['published'] }} publicadas · {{ $stats['pages']['draft'] }} rascunhos</div></div></a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="d-stat" href="{{ route('admin.news.index') }}"><span class="ic"><i class="bi bi-newspaper"></i></span>
      <div><strong>{{ $stats['news']['total'] }}</strong><small>Notícias e comunicados</small><div class="d-sub">{{ $stats['news']['published'] }} publicadas · {{ $stats['news']['scheduled'] }} agendadas</div></div></a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="d-stat" href="{{ route('admin.events.index') }}"><span class="ic"><i class="bi bi-calendar-event"></i></span>
      <div><strong>{{ $stats['events']['total'] }}</strong><small>Eventos</small><div class="d-sub">{{ $stats['events']['upcoming'] }} próximos publicados</div></div></a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="d-stat" href="{{ route('admin.documents.index') }}"><span class="ic"><i class="bi bi-folder2-open"></i></span>
      <div><strong>{{ $stats['documents']['total'] }}</strong><small>Documentos</small><div class="d-sub">{{ $stats['documents']['restricted'] }} de acesso restrito</div></div></a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="d-stat" href="{{ route('admin.courses.index') }}"><span class="ic"><i class="bi bi-mortarboard"></i></span>
      <div><strong>{{ $stats['courses']['total'] }}</strong><small>Cursos</small><div class="d-sub">{{ $stats['courses']['published'] }} publicados</div></div></a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="d-stat" href="{{ route('admin.media.index') }}"><span class="ic"><i class="bi bi-collection"></i></span>
      <div><strong>{{ $stats['media']['total'] }}</strong><small>Ficheiros multimédia</small><div class="d-sub">{{ \Illuminate\Support\Number::fileSize($stats['media']['size'], 1) }} no total</div></div></a>
  </div>
  @if ($stats['messages']['new'] !== null)
    <div class="col-sm-6 col-xl-3">
      <a class="d-stat" href="{{ route('admin.messages.index', ['status' => 'new']) }}"><span class="ic"><i class="bi bi-envelope"></i></span>
        <div><strong>{{ $stats['messages']['new'] }}</strong><small>Mensagens por ler</small></div></a>
    </div>
  @endif
  <div class="col-sm-6 col-xl-3">
    <div class="d-stat"><span class="ic"><i class="bi bi-hourglass-split"></i></span>
      <div><strong>{{ $pending->count() }}</strong><small>Pendentes de revisão</small></div></div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="d-box mb-4">
      <div class="d-box-head"><h2>Atalhos</h2></div>
      <div class="d-body d-flex flex-wrap gap-2">
        @can('create', \App\Models\News::class)<a class="btn btn-uon" href="{{ route('admin.news.create') }}"><i class="bi bi-plus-lg"></i> Nova notícia</a>
        <a class="btn btn-u-light" href="{{ route('admin.news.create', ['type' => 'announcement']) }}"><i class="bi bi-megaphone"></i> Novo comunicado</a>@endcan
        @can('create', \App\Models\Event::class)<a class="btn btn-u-light" href="{{ route('admin.events.create') }}"><i class="bi bi-calendar-plus"></i> Novo evento</a>@endcan
        @can('create', \App\Models\Document::class)<a class="btn btn-u-light" href="{{ route('admin.documents.create') }}"><i class="bi bi-file-earmark-arrow-up"></i> Carregar documento</a>@endcan
        @can('create', \App\Models\Page::class)<a class="btn btn-u-light" href="{{ route('admin.pages.create') }}"><i class="bi bi-file-earmark-plus"></i> Nova página</a>@endcan
        @can('settings.manage')<a class="btn btn-u-light" href="{{ route('admin.settings.edit', 'contacts') }}"><i class="bi bi-telephone"></i> Contactos oficiais</a>@endcan
        @can('menus.manage')<a class="btn btn-u-light" href="{{ route('admin.menus.index') }}"><i class="bi bi-list-nested"></i> Menus</a>@endcan
      </div>
    </div>

    <div class="d-box mb-4">
      <div class="d-box-head"><h2>Pendentes de revisão</h2></div>
      @if ($pending->isEmpty())
        <div class="r-empty"><i class="bi bi-check2-circle"></i>Não existem conteúdos a aguardar revisão.</div>
      @else
        <div class="table-responsive"><table class="table">
          <thead><tr><th>Conteúdo</th><th>Tipo</th><th>Autor</th><th>Actualizado</th></tr></thead>
          <tbody>
            @foreach ($pending as $p)
              <tr><td><a class="row-title" href="{{ $p->url }}">{{ $p->title }}</a></td><td>{{ $p->label }}</td><td>{{ $p->author ?? '—' }}</td><td><small>{{ $p->updated_at?->diffForHumans() }}</small></td></tr>
            @endforeach
          </tbody>
        </table></div>
      @endif
    </div>

    <div class="d-box">
      <div class="d-box-head"><h2>Actualizados recentemente</h2></div>
      @if ($recent->isEmpty())
        <div class="r-empty"><i class="bi bi-inbox"></i>Ainda não existem conteúdos.</div>
      @else
        <div class="table-responsive"><table class="table">
          <thead><tr><th>Conteúdo</th><th>Tipo</th><th>Estado</th><th>Actualizado</th></tr></thead>
          <tbody>
            @foreach ($recent as $r)
              <tr><td><a class="row-title" href="{{ $r->url }}">{{ \Illuminate\Support\Str::limit($r->title, 70) }}</a></td><td>{{ $r->label }}</td><td><x-admin.status :status="$r->status" /></td><td><small>{{ $r->updated_at?->format('d/m/Y H:i') }}</small></td></tr>
            @endforeach
          </tbody>
        </table></div>
      @endif
    </div>
  </div>

  <div class="col-xl-4">
    @can('viewAny', \App\Models\Page::class)
    <div class="d-box mb-4">
      <div class="d-box-head"><h2>Páginas por preencher</h2><span class="u-badge red">{{ $emptyPages->count() }}</span></div>
      <div class="d-body">
        @if ($emptyPages->isEmpty())
          <p class="small text-muted mb-0">Todas as páginas institucionais têm conteúdo.</p>
        @else
          <p class="small text-muted">Páginas publicadas sem texto nem blocos. O website apresenta "Conteúdo em preparação" até serem preenchidas com informação validada pela UON.</p>
          <ul class="list-unstyled small mb-0">
            @foreach ($emptyPages as $p)
              <li class="mb-1"><a href="{{ route('admin.pages.edit', $p) }}">{{ $p->title }}</a> <span class="text-muted">/{{ $p->path }}</span></li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
    @endcan

    @if ($activity->isNotEmpty())
      <div class="d-box">
        <div class="d-box-head"><h2>Actividade recente</h2><a class="small" href="{{ route('admin.activity.index') }}">Ver tudo</a></div>
        <div class="d-body">
          <ul class="d-tl">
            @foreach ($activity as $a)
              <li>{{ $a->description }}<small>{{ $a->user?->name ?? 'Sistema / visitante' }} · {{ $a->created_at?->diffForHumans() }}</small></li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
