<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /**
     * Localizações fixas: correspondem às zonas do Header e do Footer do
     * website_template. O CMS edita os itens, nunca a composição.
     */
    public const LOCATIONS = [
        'main' => ['name' => 'Navegação principal (Header + menu móvel)', 'depth' => 3],
        'topbar' => ['name' => 'Barra superior (Topbar)', 'depth' => 1],
        'footer_1' => ['name' => 'Rodapé — 1.ª coluna de links', 'depth' => 1],
        'footer_2' => ['name' => 'Rodapé — 2.ª coluna de links', 'depth' => 1],
        'footer_3' => ['name' => 'Rodapé — 3.ª coluna de links', 'depth' => 1],
        'footer_bottom' => ['name' => 'Rodapé — links inferiores', 'depth' => 1],
    ];

    protected $fillable = ['location', 'name', 'title', 'description'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }

    public function maxDepth(): int
    {
        return self::LOCATIONS[$this->location]['depth'] ?? 1;
    }
}
