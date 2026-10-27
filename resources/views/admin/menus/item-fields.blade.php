@php
    $isMain = $menu->location === 'main';
    $p = $prefix;
    $linkType = old('link_type', $item->link_type);
@endphp
<div class="row g-2">
  <x-admin.field col="col-md-7 mb-2" name="label" :id="$p.'_label'" label="Rótulo" :value="$item->label" required />
  @if ($isMain)
    <x-admin.field col="col-md-5 mb-2" name="mobile_label" :id="$p.'_mobile'" label="Rótulo no menu móvel" :value="$item->mobile_label" />
  @endif
  @if ($isMain)
    <x-admin.field col="col-md-6 mb-2" name="display" :id="$p.'_display'" label="Apresentação" type="select" :options="\App\Models\MenuItem::DISPLAYS" :value="$item->display" required />
  @else
    <input type="hidden" name="display" value="link">
  @endif
  @if ($menu->maxDepth() > 1)
    <x-admin.field col="col-md-6 mb-2" name="parent_id" :id="$p.'_parent'" label="Dentro de" type="select" :options="$parents" :value="$item->parent_id" empty="— Primeiro nível —" />
  @endif
  <div class="col-md-6 mb-2">
    <label class="form-label" for="{{ $p }}_lt">Destino</label>
    <select class="form-select" id="{{ $p }}_lt" name="link_type" data-link-type>
      @foreach (\App\Models\MenuItem::LINK_TYPES as $k => $l)<option value="{{ $k }}" @selected($linkType === $k)>{{ $l }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-6 mb-2" data-link-only="page">
    <label class="form-label" for="{{ $p }}_page">Página</label>
    <select class="form-select" id="{{ $p }}_page" name="page_id">
      <option value="">— Escolher —</option>
      @foreach ($pages as $id => $t)<option value="{{ $id }}" @selected((string) old('page_id', $item->page_id) === (string) $id)>{{ $t }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-6 mb-2" data-link-only="url">
    <label class="form-label" for="{{ $p }}_url">URL</label>
    <input class="form-control" id="{{ $p }}_url" name="url" value="{{ old('url', $item->link_type === 'url' ? $item->url : '') }}" placeholder="/caminho ou https://...">
  </div>
  <div class="col-md-6 mb-2" data-link-only="setting">
    <label class="form-label" for="{{ $p }}_set">Plataforma</label>
    <select class="form-select" id="{{ $p }}_set" name="setting_key">
      @foreach ($settingLinks as $k => $l)<option value="{{ $k }}" @selected(old('setting_key', $item->link_type === 'setting' ? $item->url : '') === $k)>{{ $l }}</option>@endforeach
    </select>
    <div class="form-text">URL definido em Configurações → Plataformas externas.</div>
  </div>
  @if ($menu->location === 'topbar')
    <x-admin.field col="col-md-6 mb-2" name="icon" :id="$p.'_icon'" label="Ícone" :value="$item->icon" placeholder="bi-book" />
  @endif
  <div class="col-12 d-flex gap-4 flex-wrap mb-3">
    <x-admin.field col="" name="is_active" :id="$p.'_active'" label="Activo" type="checkbox" :value="$item->is_active" />
    <x-admin.field col="" name="open_in_new_tab" :id="$p.'_blank'" label="Abrir num novo separador" type="checkbox" :value="$item->open_in_new_tab" />
  </div>
</div>
