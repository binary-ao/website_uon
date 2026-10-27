<ul class="menu-tree">
  @foreach ($nodes as $node)
    <li id="item-{{ $node->id }}">
      <div class="menu-node{{ $node->is_active ? '' : ' inactive' }}">
        <i class="bi {{ ['mega' => 'bi-grid-3x3-gap', 'dropdown' => 'bi-caret-down-square', 'column' => 'bi-layout-three-columns', 'divider' => 'bi-dash-lg'][$node->display] ?? 'bi-link-45deg' }} text-muted" aria-hidden="true"></i>
        <div>
          <div class="mn-label">{{ $node->label }}@if (! $node->is_active) <span class="u-badge">Inactivo</span>@endif</div>
          <div class="mn-meta">
            {{ \App\Models\MenuItem::DISPLAYS[$node->display] ?? $node->display }}
            @if ($node->link_type === 'page') · Página: {{ $node->page?->title ?? 'removida' }} @if ($node->page && $node->page->status?->value !== 'published')<span class="text-danger">(não publicada)</span>@endif
            @elseif ($node->link_type === 'url') · {{ \Illuminate\Support\Str::limit($node->url, 40) }}
            @elseif ($node->link_type === 'setting') · {{ \App\Support\SettingsSchema::linkableKeys()[$node->url] ?? $node->url }} @if (! setting($node->url))<span class="text-danger">(URL por configurar)</span>@endif
            @endif
          </div>
        </div>
        <div class="mn-actions">
          <form method="post" action="{{ route('admin.menus.items.move', [$menu, $node]) }}">@csrf<input type="hidden" name="direction" value="up"><button class="u-iconbtn" title="Subir" aria-label="Subir {{ $node->label }}" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button></form>
          <form method="post" action="{{ route('admin.menus.items.move', [$menu, $node]) }}">@csrf<input type="hidden" name="direction" value="down"><button class="u-iconbtn" title="Descer" aria-label="Descer {{ $node->label }}" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button></form>
          <button class="u-iconbtn" type="button" data-bs-toggle="collapse" data-bs-target="#edit-{{ $node->id }}" aria-expanded="false" aria-controls="edit-{{ $node->id }}" title="Editar" aria-label="Editar {{ $node->label }}"><i class="bi bi-pencil"></i></button>
          <x-admin.delete-form :action="route('admin.menus.items.destroy', [$menu, $node])" message="Remover &quot;{{ $node->label }}&quot;{{ $node->childrenTree->isNotEmpty() ? ' e todos os subitens' : '' }}?" label="Remover" :icon-only="true" />
        </div>
      </div>
      <div class="collapse" id="edit-{{ $node->id }}">
        <form method="post" action="{{ route('admin.menus.items.update', [$menu, $node]) }}" class="u-card u-form mb-2">
          @csrf @method('PUT')
          @include('admin.menus.item-fields', ['item' => $node, 'prefix' => 'e'.$node->id])
          <button type="submit" class="btn btn-sm btn-uon">Guardar item</button>
        </form>
      </div>
      @if ($node->childrenTree->isNotEmpty())
        @include('admin.menus.tree', ['nodes' => $node->childrenTree, 'menu' => $menu])
      @endif
    </li>
  @endforeach
</ul>
