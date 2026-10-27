<?php

namespace App\Models;

use App\Support\SafeUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Avisos da faixa de destaques (notice-strip) da página inicial. */
class Notice extends Model
{
    protected $fillable = ['text', 'icon', 'url', 'starts_at', 'ends_at', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function scopeCurrent(Builder $q): Builder
    {
        $now = now();

        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order');
    }

    public function href(): ?string
    {
        return SafeUrl::resolve($this->url);
    }
}
