<?php

namespace App\Models;

use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganicUnit extends Model
{
    use HasPublication, HasUniqueSlug, SoftDeletes;

    public const TYPES = [
        'faculdade' => 'Faculdade',
        'instituto' => 'Instituto',
        'escola' => 'Escola',
        'departamento' => 'Departamento',
        'centro' => 'Centro',
        'servico' => 'Serviço',
        'orgao' => 'Órgão de gestão',
    ];

    protected string $slugSource = 'name';

    protected $fillable = [
        'parent_id', 'type', 'name', 'slug', 'short_name', 'summary', 'description', 'head_title', 'head_name',
        'email', 'phone', 'location', 'map_embed_url', 'featured_media_id', 'page_id', 'status', 'published_at', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_demo' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganicUnit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrganicUnit::class, 'parent_id')->orderBy('sort_order');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    /** Página da unidade: página do CMS associada (se publicada) ou página automática da unidade. */
    public function url(): string
    {
        if ($this->page_id && $this->page && $this->page->isPubliclyVisible()) {
            return $this->page->url();
        }

        return route('units.show', $this->slug);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
