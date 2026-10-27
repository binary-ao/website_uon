<?php

namespace App\Models;

use App\Support\SafeUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Diapositivo do banner (carrossel) da página inicial. */
class HeroSlide extends Model
{
    public const POSITIONS = ['center' => 'Centro', 'left' => 'Esquerda', 'right' => 'Direita', 'top' => 'Topo', 'bottom' => 'Fundo'];

    protected $fillable = [
        'eyebrow', 'title', 'title_highlight', 'text', 'media_id', 'image_position',
        'primary_label', 'primary_url', 'secondary_label', 'secondary_url',
        'starts_at', 'ends_at', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /** Activos e dentro do período de visualização, pela ordem definida. */
    public function scopeCurrent(Builder $q): Builder
    {
        $now = now();

        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order')->orderBy('id');
    }

    public function isCurrent(): bool
    {
        return $this->is_active
            && (! $this->starts_at || ! $this->starts_at->isFuture())
            && (! $this->ends_at || ! $this->ends_at->isPast());
    }

    public function primaryHref(): ?string
    {
        return SafeUrl::resolve($this->primary_url);
    }

    public function secondaryHref(): ?string
    {
        return SafeUrl::resolve($this->secondary_url);
    }

    public function backgroundPosition(): string
    {
        return array_key_exists($this->image_position, self::POSITIONS) ? $this->image_position : 'center';
    }
}
