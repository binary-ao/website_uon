<?php

namespace App\Models;

use App\Casts\UnicodeJson;
use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\Revisionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasPublication, HasUniqueSlug, Revisionable, SoftDeletes;

    protected string $slugSource = 'name';

    protected $fillable = [
        'organic_unit_id', 'degree_id', 'name', 'slug', 'icon', 'summary', 'duration', 'modality', 'regime',
        'presentation', 'objectives', 'entry_profile', 'exit_profile', 'careers', 'curriculum_note',
        'coordinator_name', 'contact_email', 'featured_media_id', 'meta_title', 'meta_description',
        'status', 'published_at', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['careers' => UnicodeJson::class, 'is_demo' => 'boolean'];
    }

    public function revisionFields(): array
    {
        return [
            'organic_unit_id', 'degree_id', 'name', 'slug', 'icon', 'summary', 'duration', 'modality', 'regime',
            'presentation', 'objectives', 'entry_profile', 'exit_profile', 'careers', 'curriculum_note',
            'coordinator_name', 'contact_email', 'featured_media_id', 'meta_title', 'meta_description', 'status', 'published_at',
        ];
    }

    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(OrganicUnit::class, 'organic_unit_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(CourseSubject::class)->orderBy('year')->orderBy('semester')->orderBy('sort_order');
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
        return url('/ensino/cursos/'.$this->slug);
    }
}
