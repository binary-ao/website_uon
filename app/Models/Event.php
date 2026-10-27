<?php

namespace App\Models;

use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\Revisionable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasPublication, HasUniqueSlug, Revisionable, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'location', 'starts_at', 'ends_at', 'all_day', 'featured_media_id',
        'category_id', 'registration_url', 'registration_info', 'meta_title', 'meta_description', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'all_day' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function revisionFields(): array
    {
        return [
            'title', 'slug', 'excerpt', 'body', 'location', 'starts_at', 'ends_at', 'all_day', 'featured_media_id',
            'category_id', 'registration_url', 'registration_info', 'meta_title', 'meta_description', 'status', 'published_at',
        ];
    }

    /** Próximos: ainda não terminaram (usa a data de fim, ou o fim do dia de início). */
    public function scopeUpcoming(Builder $q): Builder
    {
        $now = now();

        return $q->where(function ($w) use ($now) {
            $w->where('ends_at', '>=', $now)
                ->orWhere(fn ($x) => $x->whereNull('ends_at')->where('starts_at', '>=', $now->copy()->startOfDay()));
        })->orderBy('starts_at');
    }

    public function scopePast(Builder $q): Builder
    {
        $now = now();

        return $q->where(function ($w) use ($now) {
            $w->where('ends_at', '<', $now)
                ->orWhere(fn ($x) => $x->whereNull('ends_at')->where('starts_at', '<', $now->copy()->startOfDay()));
        })->orderByDesc('starts_at');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function url(): string
    {
        return url('/comunicacao/eventos/'.$this->slug);
    }

    public function isPast(): bool
    {
        $end = $this->ends_at ?? $this->starts_at->copy()->endOfDay();

        return $end->isPast();
    }

    public function dateRangeLabel(): string
    {
        $s = $this->starts_at;
        $e = $this->ends_at;
        if (! $e || $e->isSameDay($s)) {
            return $s->translatedFormat('d \d\e F \d\e Y');
        }
        if ($s->isSameMonth($e)) {
            return $s->format('d').' a '.$e->translatedFormat('d \d\e F \d\e Y');
        }

        return $s->translatedFormat('d \d\e F').' a '.$e->translatedFormat('d \d\e F \d\e Y');
    }

    public function timeLabel(): ?string
    {
        if ($this->all_day) {
            return 'Todo o dia';
        }
        $label = $this->starts_at->format('H\hi');
        if ($this->ends_at && $this->ends_at->format('H:i') !== '00:00') {
            $label .= ' – '.$this->ends_at->format('H\hi');
        }

        return $label;
    }
}
