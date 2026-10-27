<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasUniqueSlug;

    public const TYPES = [
        'news' => 'Notícias e comunicados',
        'event' => 'Eventos',
        'document' => 'Documentos',
        'gallery' => 'Galeria / Multimédia',
    ];

    protected string $slugSource = 'name';

    protected $fillable = ['type', 'name', 'slug', 'description', 'sort_order'];

    protected function slugScope(): array
    {
        return ['type'];
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type)->orderBy('sort_order')->orderBy('name');
    }

    /** Número de registos que usam a categoria (para impedir eliminações indevidas). */
    public function usageCount(): int
    {
        return match ($this->type) {
            'news' => News::withTrashed()->where('category_id', $this->id)->count(),
            'event' => Event::withTrashed()->where('category_id', $this->id)->count(),
            'document' => Document::withTrashed()->where('category_id', $this->id)->count(),
            'gallery' => GalleryItem::where('category_id', $this->id)->count(),
            default => 0,
        };
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? Str::headline($this->type);
    }
}
