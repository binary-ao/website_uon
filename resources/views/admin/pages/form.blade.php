@extends('layouts.admin')
@section('title', $page->exists ? 'Editar página' : 'Nova página')

@section('content')
<x-admin.title :title="$page->exists ? $page->title : 'Nova página'" :crumbs="['Páginas' => route('admin.pages.index')]"
  :subtitle="$page->exists ? 'Endereço: /'.$page->path : 'Crie uma nova página institucional.'">
  @if ($page->exists)
    @can('create', \App\Models\Page::class)
      <form method="post" action="{{ route('admin.pages.duplicate', $page) }}">@csrf<button class="btn btn-u-light"><i class="bi bi-copy"></i> Duplicar</button></form>
    @endcan
  @endif
</x-admin.title>

<form method="post" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="u-form" data-dirty-check>
  @csrf
  @if ($page->exists) @method('PUT') @endif
  <div class="row g-4">
    <div class="col-xl-8">
      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-card-heading"></i> Cabeçalho da página</div>
        <x-admin.field name="title" label="Título" :value="$page->title" required />
        <div class="row">
          <x-admin.field col="col-md-6 mb-3" name="eyebrow" label="Sobretítulo (eyebrow)" :value="$page->eyebrow" help="Texto pequeno acima do título (ex.: secção)." />
          <x-admin.field col="col-md-6 mb-3" name="slug" label="Endereço (slug)" :value="$page->slug" :help="$page->is_system ? 'Página estrutural: o endereço é fixo porque é usado por menus e módulos.' : 'Gerado a partir do título se ficar vazio.'" />
        </div>
        <x-admin.field name="excerpt" label="Introdução / resumo" type="textarea" rows="2" :value="$page->excerpt" help="Apresentado sob o título (page-header) e usado como descrição por omissão." class="mb-0" />
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-text-paragraph"></i> Conteúdo principal</div>
        <label for="f_body" class="form-label visually-hidden">Conteúdo principal</label>
        <textarea id="f_body" name="body" data-editor class="form-control" rows="14">{{ old('body', $page->body) }}</textarea>
        <div class="form-text">Títulos, parágrafos, listas, ligações, tabelas e imagens. O HTML é sanitizado no servidor.</div>
      </div>

      <div class="u-card" data-layout-only="sidebar">
        <div class="u-card-title"><i class="bi bi-layout-sidebar-reverse"></i> Coluna lateral</div>
        <x-admin.field name="sidebar_title" label="Título da caixa lateral" :value="$page->sidebar_title" />
        <x-admin.field name="sidebar_items" label="Linhas da caixa lateral" type="textarea" rows="5" :value="$page->sidebar_items" help='Uma linha por item. Opcionalmente com ícone: "bi-calendar3 | Texto".' class="mb-0" />
      </div>

      <div class="u-card" data-blocks-editor>
        <div class="u-card-title"><i class="bi bi-grid-1x2"></i> Blocos de conteúdo</div>
        <p class="small text-muted">Blocos controlados que reutilizam componentes do template (cartões, directório, cronologia, acordeão, tabela, organograma, chamada à acção…). Aparecem depois do conteúdo principal.</p>
        <script type="application/json" data-blocks-schema>{!! json_encode($blockSchema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
        <script type="application/json" data-blocks-value>{!! json_encode(old('blocks_json') ? json_decode(old('blocks_json'), true) : ($page->blocks ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
        <input type="hidden" name="blocks_json" value="">
        <div class="blk-list" data-blocks-list></div>
        <div class="blk-add-bar mt-3">
          <label for="blkType" class="visually-hidden">Tipo de bloco</label>
          <select class="form-select form-select-sm w-auto" id="blkType" data-blocks-type></select>
          <button type="button" class="btn btn-sm btn-u-dark" data-blocks-add><i class="bi bi-plus-lg"></i> Adicionar bloco</button>
        </div>
      </div>

      <div class="u-card">
        <div class="u-card-title"><i class="bi bi-paperclip"></i> Documentos associados</div>
        <label for="f_docs" class="form-label">Documentos apresentados no fim da página</label>
        <select multiple class="form-select" id="f_docs" name="document_ids[]" size="6">
          @php $selectedDocs = old('document_ids', $page->exists ? $page->documents->pluck('id')->all() : []); @endphp
          @foreach ($documents as $doc)
            <option value="{{ $doc->id }}" @selected(in_array($doc->id, $selectedDocs))>{{ $doc->title }}</option>
          @endforeach
        </select>
        <div class="form-text">Use Ctrl/Cmd para seleccionar vários. Apenas documentos publicados são apresentados.</div>
      </div>

      <x-admin.seo :model="$page" />
    </div>

    <div class="col-xl-4">
      <div class="sticky-side">
        <x-admin.publish-box :model="$page" preview-type="pages" route-prefix="admin.pages" />

        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-diagram-3"></i> Estrutura</div>
          @if ($page->is_system)
            <input type="hidden" name="parent_id" value="{{ $page->parent_id }}">
            <p class="small"><strong>Página-pai:</strong> {{ $page->parent?->title ?? 'Nível superior' }}</p>
          @else
            <x-admin.field name="parent_id" label="Página-pai" type="select" :options="$parents" :value="$page->parent_id" empty="— Nível superior —" />
          @endif
          <div class="mb-3">
            <label for="f_layout" class="form-label req">Layout (tipo de página do template)</label>
            <select class="form-select" id="f_layout" name="layout" data-layout-select>
              @foreach (\App\Models\Page::LAYOUTS as $k => $l)
                <option value="{{ $k }}" @selected(old('layout', $page->layout) === $k)>{{ $l }}</option>
              @endforeach
            </select>
          </div>
          <div data-layout-only="courses_index">
            <x-admin.field name="module_degree" label="Mostrar apenas o grau" type="select" :options="$degrees" :value="$page->option('degree')" empty="Todos (com filtros)" />
          </div>
          <div data-layout-only="documents_index">
            <x-admin.field name="module_category_id" label="Categoria de documentos" type="select" :options="$documentCategories" :value="$page->option('category_id')" empty="Todas as categorias" />
          </div>
          <div data-layout-only="units_directory">
            <x-admin.field name="module_unit_type" label="Tipo de unidades" type="select" :options="$unitTypes" :value="$page->option('unit_type', 'academic')" />
          </div>
          <x-admin.field name="sort_order" label="Ordem" type="number" :value="$page->sort_order ?? 0" help="Ordem entre páginas do mesmo nível." />
          <x-admin.field name="show_in_search" label="Incluir na pesquisa e no sitemap" type="checkbox" :value="$page->show_in_search ?? true" class="mb-0" />
        </div>

        <div class="u-card">
          <div class="u-card-title"><i class="bi bi-image"></i> Imagem de destaque</div>
          <x-admin.media-field name="featured_media_id" label="Imagem" :media="$page->featuredMedia" help="Usada na coluna lateral (layout com coluna) e na partilha em redes sociais." />
        </div>

        @if ($menuUsage->isNotEmpty())
          <div class="u-card small">
            <div class="u-card-title"><i class="bi bi-list-nested"></i> Usada nos menus</div>
            <ul class="mb-0">@foreach ($menuUsage as $mi)<li>{{ $mi->menu->name }} — {{ $mi->label }}</li>@endforeach</ul>
          </div>
        @endif

        <x-admin.revisions :model="$page" />

      </div>
    </div>
  </div>
</form>

@if ($page->exists && ! $page->is_system)
  @can('delete', $page)
    <div class="mt-4 text-end">
      <x-admin.delete-form :action="route('admin.pages.destroy', $page)" message="Mover esta página para a reciclagem?" label="Mover para a reciclagem" />
    </div>
  @endcan
@endif
@endsection
