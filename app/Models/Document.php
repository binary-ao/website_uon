<?php

namespace App\Models;

use App\Models\Concerns\HasPublication;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasPublication, HasUniqueSlug, SoftDeletes;

    public const VISIBILITY = ['public' => 'Pública', 'restricted' => 'Restrita (utilizadores autenticados)'];

    protected $fillable = [
        'title', 'slug', 'description', 'category_id', 'document_date', 'visibility', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return ['document_date' => 'date', 'is_demo' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'current_version_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function downloadUrl(): string
    {
        return route('documents.download', $this->slug);
    }

    public function metaLabel(): string
    {
        $v = $this->currentVersion;
        $parts = [];
        if ($v) {
            $parts[] = strtoupper($v->extension);
            $parts[] = $v->humanSize();
        }
        $date = $this->document_date ?? $v?->created_at;
        if ($date) {
            $parts[] = 'Actualizado em '.$date->translatedFormat('M Y');
        }
        if (! $this->isPublic()) {
            $parts[] = 'Acesso restrito';
        }

        return implode(' · ', $parts);
    }

    public function icon(): string
    {
        if (! $this->isPublic()) {
            return 'bi-file-earmark-lock';
        }

        return match ($this->currentVersion?->extension) {
            'pdf' => 'bi-file-earmark-pdf',
            'doc', 'docx', 'odt' => 'bi-file-earmark-word',
            'xls', 'xlsx', 'ods', 'csv' => 'bi-file-earmark-spreadsheet',
            'ppt', 'pptx' => 'bi-file-earmark-slides',
            default => 'bi-file-earmark-text',
        };
    }
}
