<?php

namespace App\Services;

use App\Enums\PublicationStatus;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Constrói as árvores de menus para o Header e o Footer.
 * Itens inactivos, páginas não publicadas e plataformas externas sem URL
 * configurado não são apresentados publicamente.
 */
class MenuRepository
{
    private array $memo = [];

    public function tree(string $location): Collection
    {
        if (isset($this->memo[$location])) {
            return $this->memo[$location];
        }

        $menu = Cache::rememberForever('uon.menu.'.$location, function () use ($location) {
            return Menu::where('location', $location)->first();
        });

        if (! $menu) {
            return $this->memo[$location] = collect();
        }

        $items = MenuItem::with('page:id,path,status,published_at,deleted_at')
            ->where('menu_id', $menu->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $byParent = $items->groupBy(fn ($i) => $i->parent_id ?? 0);

        $build = function ($parentId) use (&$build, $byParent): Collection {
            return collect($byParent->get($parentId, []))
                ->map(function (MenuItem $item) use ($build) {
                    $item->setRelation('visibleChildren', $build($item->id));

                    return $item;
                })
                ->filter(fn (MenuItem $item) => $this->isVisible($item))
                ->values();
        };

        return $this->memo[$location] = $build(0);
    }

    public function title(string $location): ?string
    {
        return Cache::rememberForever('uon.menu.'.$location, fn () => Menu::where('location', $location)->first())?->title;
    }

    private function isVisible(MenuItem $item): bool
    {
        if ($item->display === 'divider') {
            return true;
        }
        if (in_array($item->display, ['dropdown', 'mega', 'column'], true) && $item->visibleChildren->isEmpty()) {
            return false;
        }

        return match ($item->link_type) {
            'page' => $item->page && ! $item->page->trashed()
                && in_array($item->page->status, [PublicationStatus::Published, PublicationStatus::Scheduled], true)
                && $item->page->published_at && ! $item->page->published_at->isFuture(),
            'url', 'setting' => $item->href() !== '#',
            default => in_array($item->display, ['dropdown', 'mega', 'column'], true),
        };
    }

    public static function flush(): void
    {
        foreach (array_keys(Menu::LOCATIONS) as $location) {
            Cache::forget('uon.menu.'.$location);
        }
    }

    /** Item activo: o caminho actual começa pelo caminho do link. */
    public static function isActive(MenuItem $item): bool
    {
        $current = '/'.trim(request()->path(), '/');
        $check = function (MenuItem $i) use ($current) {
            $href = $i->href();
            if ($href === '#' || $i->isExternal()) {
                return false;
            }
            $path = '/'.trim(parse_url($href, PHP_URL_PATH) ?? '', '/');
            $base = '/'.trim(parse_url(url('/'), PHP_URL_PATH) ?? '', '/');
            if ($base !== '/' && str_starts_with($path, $base)) {
                $path = '/'.ltrim(substr($path, strlen($base)), '/');
            }
            if ($path === '/') {
                return $current === '/';
            }

            return $current === $path || str_starts_with($current, rtrim($path, '/').'/');
        };

        if ($check($item)) {
            return true;
        }
        foreach ($item->visibleChildren ?? [] as $child) {
            if (static::isActive($child)) {
                return true;
            }
        }

        return false;
    }
}
