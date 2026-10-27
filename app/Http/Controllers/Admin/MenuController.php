<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Services\ActivityLogger;
use App\Services\MenuRepository;
use App\Support\SafeUrl;
use App\Support\SettingsSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Gestão dos menus do Header e do Footer.
 * As localizações e a profundidade máxima são fixas (composição do template):
 * o editor altera apenas os itens — rótulos, destinos, ordem e activação.
 */
class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::withCount('items')->get()->sortBy(fn ($m) => array_search($m->location, array_keys(Menu::LOCATIONS)));

        return view('admin.menus.index', compact('menus'));
    }

    public function edit(Menu $menu)
    {
        $items = $menu->items()->with('page:id,title,path,status')->get();

        return view('admin.menus.edit', [
            'menu' => $menu,
            'tree' => $this->tree($items),
            'pages' => Page::treeOptions(),
            'settingLinks' => SettingsSchema::linkableKeys(),
            'parents' => $this->parentOptions($menu, $items),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate(['title' => ['nullable', 'string', 'max:80']]);
        $menu->update(['title' => $data['title'] ?? null]);
        ActivityLogger::log('menus.updated', $menu, 'Alterou o título do menu "'.$menu->name.'"');

        return back()->with('success', 'Menu actualizado.');
    }

    private function tree($items)
    {
        $byParent = $items->groupBy(fn ($i) => $i->parent_id ?? 0);
        $build = function ($pid, $depth) use (&$build, $byParent) {
            $out = [];
            foreach ($byParent->get($pid, []) as $item) {
                $item->depth = $depth;
                $item->setRelation('childrenTree', collect($build($item->id, $depth + 1)));
                $out[] = $item;
            }

            return $out;
        };

        return collect($build(0, 1));
    }

    private function parentOptions(Menu $menu, $items): array
    {
        $max = $menu->maxDepth();
        $out = [];
        foreach ($this->tree($items) as $root) {
            $walk = function ($node) use (&$walk, &$out, $max) {
                if ($node->depth < $max && in_array($node->display, ['dropdown', 'mega', 'column'], true)) {
                    $out[$node->id] = str_repeat('— ', $node->depth - 1).$node->label;
                }
                foreach ($node->childrenTree as $c) {
                    $walk($c);
                }
            };
            $walk($root);
        }

        return $out;
    }

    private function validated(Request $request, Menu $menu, ?MenuItem $item = null): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'mobile_label' => ['nullable', 'string', 'max:80'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $menu->id)],
            'display' => ['required', Rule::in(array_keys(MenuItem::DISPLAYS))],
            'link_type' => ['required', Rule::in(array_keys(MenuItem::LINK_TYPES))],
            'page_id' => ['nullable', 'required_if:link_type,page', 'integer', Rule::exists('pages', 'id')],
            'url' => ['nullable', 'required_if:link_type,url', 'string', 'max:2048', SafeUrl::rule()],
            'setting_key' => ['nullable', 'required_if:link_type,setting', Rule::in(array_keys(SettingsSchema::linkableKeys()))],
            'icon' => ['nullable', 'regex:/^bi-[a-z0-9-]{1,40}$/'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], ['page_id.required_if' => 'Escolha a página de destino.', 'url.required_if' => 'Indique o URL.', 'icon.regex' => 'Ícone inválido (ex.: bi-book).']);

        // Regras de composição: protegem a estrutura do Header/Footer.
        $parent = ! empty($data['parent_id']) ? MenuItem::find($data['parent_id']) : null;
        $depth = $parent ? $parent->depth() + 1 : 1;
        $errors = [];
        if ($item && $parent && ($parent->id === $item->id || $this->isDescendant($parent, $item))) {
            $errors['parent_id'] = 'Um item não pode ficar dentro de si próprio.';
        }
        if ($depth > $menu->maxDepth()) {
            $errors['parent_id'] = 'Este menu só admite '.$menu->maxDepth().' nível(is).';
        }
        if ($menu->location !== 'main' && $data['display'] !== 'link') {
            $data['display'] = 'link';
        }
        if ($menu->location === 'main') {
            if ($depth === 1 && $data['display'] === 'column') {
                $errors['display'] = 'Colunas só podem existir dentro de um mega menu.';
            }
            if ($depth > 1 && in_array($data['display'], ['dropdown', 'mega'], true)) {
                $errors['display'] = 'Submenus só podem ser criados no primeiro nível (composição do Header).';
            }
            if ($parent && $parent->display === 'mega' && $data['display'] !== 'column') {
                $errors['display'] = 'Dentro de um mega menu só podem existir colunas; os links ficam dentro das colunas.';
            }
            if ($parent && $parent->display === 'column' && $data['display'] !== 'link') {
                $errors['display'] = 'Dentro de uma coluna só podem existir links.';
            }
            if ($parent && $parent->display === 'dropdown' && ! in_array($data['display'], ['link', 'divider'], true)) {
                $errors['display'] = 'Dentro de um dropdown só podem existir links e separadores.';
            }
        }
        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return [
            'label' => strip_tags($data['label']),
            'mobile_label' => isset($data['mobile_label']) ? strip_tags($data['mobile_label']) : null,
            'parent_id' => $parent?->id,
            'display' => $data['display'],
            'link_type' => $data['link_type'],
            'page_id' => $data['link_type'] === 'page' ? $data['page_id'] : null,
            'url' => match ($data['link_type']) {
                'url' => $data['url'],
                'setting' => $data['setting_key'],
                default => null,
            },
            'icon' => $data['icon'] ?? null,
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function isDescendant(MenuItem $candidate, MenuItem $ancestor): bool
    {
        $node = $candidate->parent;
        $guard = 0;
        while ($node && $guard++ < 10) {
            if ($node->id === $ancestor->id) {
                return true;
            }
            $node = $node->parent;
        }

        return false;
    }

    public function storeItem(Request $request, Menu $menu)
    {
        $data = $this->validated($request, $menu);
        $item = new MenuItem($data);
        $item->menu_id = $menu->id;
        $item->sort_order = (int) $menu->items()->where('parent_id', $data['parent_id'])->max('sort_order') + 1;
        $item->save();
        MenuRepository::flush();
        ActivityLogger::log('menus.item_created', $item, 'Adicionou o item "'.$item->label.'" ao menu "'.$menu->name.'"');

        return back()->with('success', 'Item adicionado.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $data = $this->validated($request, $menu, $item);
        $item->fill($data)->save();
        MenuRepository::flush();
        ActivityLogger::log('menus.item_updated', $item, 'Editou o item "'.$item->label.'" do menu "'.$menu->name.'"');

        return back()->with('success', 'Item actualizado.');
    }

    /** Reordenação acessível (botões subir/descer). */
    public function moveItem(Request $request, Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        DB::transaction(function () use ($menu, $item, $direction) {
            $siblings = $menu->items()->where('parent_id', $item->parent_id)->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get()->values();
            $index = $siblings->search(fn ($s) => $s->id === $item->id);
            $swap = $direction === 'up' ? $index - 1 : $index + 1;
            if ($index === false || $swap < 0 || $swap >= $siblings->count()) {
                return;
            }
            $ordered = $siblings->all();
            [$ordered[$index], $ordered[$swap]] = [$ordered[$swap], $ordered[$index]];
            foreach ($ordered as $pos => $sibling) {
                $sibling->forceFill(['sort_order' => $pos])->saveQuietly();
            }
        });
        MenuRepository::flush();

        return back()->with('success', 'Ordem actualizada.')->withFragment('item-'.$item->id);
    }

    public function destroyItem(Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $count = 1 + $item->children()->count();
        $item->delete();
        MenuRepository::flush();
        ActivityLogger::log('menus.item_deleted', null, 'Removeu o item "'.$item->label.'" do menu "'.$menu->name.'"');

        return back()->with('success', $count > 1 ? 'Item e respectivos subitens removidos.' : 'Item removido.');
    }
}
