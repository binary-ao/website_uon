<?php

namespace App\Models;

use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\Revisionable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasPublication, HasUniqueSlug, Revisionable, SoftDeletes;

    public const TYPES = ['news' => 'Notícia', 'announcement' => 'Comunicado'];

    protected $fillable = [
        'type', 'title', 'slug', 'excerpt', 'body', 'featured_media_id', 'category_id', 'author_name',
        'is_featured', 'meta_title', 'meta_description', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_demo' => 'boolean'];
    }

    public function revisionFields(): array
    {
        return [
            'type', 'title', 'slug', 'excerpt', 'body', 'featured_media_id', 'category_id', 'author_name',
            'is_featured', 'meta_title', 'meta_description', 'status', 'published_at',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (News $news) {
            if ($news->published_at && ($news->wasChanged('slug') || $news->wasChanged('type')) && $news->getOriginal('slug')) {
                $old = (new static(['type' => $news->getOriginal('type')]))->forceFill(['slug' => $news->getOriginal('slug')]);
                Redirect::remember($old->relativeUrl(), $news->relativeUrl());
            }
        });
    }

    public function scopeNews(Builder $q): Builder
    {
        return $q->where('type', 'news');
    }

    public function scopeAnnouncements(Builder $q): Builder
    {
        return $q->where('type', 'announcement');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function documents(): MorphToMany
    {
        return $this->morphToMany(Document::class, 'documentable')->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function relativeUrl(): string
    {
        return ($this->type === 'announcement' ? '/comunicacao/comunicados/' : '/comunicacao/noticias/').$this->slug;
    }

    public function url(): string
    {
        return url($this->relativeUrl());
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function displayAuthor(): ?string
    {
        return $this->author_name ?: null;
    }
}
