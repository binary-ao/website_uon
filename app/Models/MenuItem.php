<?php

namespace App\Models;

use App\Support\SafeUrl;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    /** Tipos de apresentação suportados pela composição original do Header. */
    public const DISPLAYS = [
        'link' => 'Link simples',
        'dropdown' => 'Dropdown simples (submenu)',
        'mega' => 'Mega menu (colunas)',
        'column' => 'Coluna do mega menu (título)',
        'divider' => 'Separador',
    ];

    public const LINK_TYPES = [
        'none' => 'Sem ligação',
        'page' => 'Página interna',
        'url' => 'URL (interno ou externo)',
        'setting' => 'Plataforma externa (definida nas Configurações)',
    ];

    protected $fillable = [
        'parent_id', 'label', 'mobile_label', 'display', 'link_type', 'page_id', 'url',
        'open_in_new_tab', 'icon', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['open_in_new_tab' => 'boolean', 'is_active' => 'boolean'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function href(): string
    {
        return match ($this->link_type) {
            'page' => $this->page ? $this->page->url() : '#',
            'url' => SafeUrl::resolve($this->url) ?? '#',
            'setting' => SafeUrl::resolve(Settings::get((string) $this->url)) ?? '#',
            default => '#',
        };
    }

    public function isExternal(): bool
    {
        return match ($this->link_type) {
            'url' => SafeUrl::isExternal($this->url),
            'setting' => true,
            default => false,
        };
    }

    public function depth(): int
    {
        $depth = 1;
        $node = $this->parent;
        while ($node && $depth < 5) {
            $depth++;
            $node = $node->parent;
        }

        return $depth;
    }
}
