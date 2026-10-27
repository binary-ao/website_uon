<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['title', 'alt_text', 'description'];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /** URL público; $variant = 'md' devolve a versão média, se existir. */
    public function url(?string $variant = null): string
    {
        $path = $variant && ! empty($this->variants[$variant]) ? $this->variants[$variant] : $this->path;

        return asset('uploads/'.$path);
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }

    public function altText(): string
    {
        return (string) ($this->alt_text ?: $this->title ?: '');
    }

    public function icon(): string
    {
        return match (true) {
            $this->isImage() => 'bi-file-earmark-image',
            $this->extension === 'pdf' => 'bi-file-earmark-pdf',
            in_array($this->extension, ['doc', 'docx', 'odt']) => 'bi-file-earmark-word',
            in_array($this->extension, ['xls', 'xlsx', 'ods', 'csv']) => 'bi-file-earmark-spreadsheet',
            in_array($this->extension, ['ppt', 'pptx']) => 'bi-file-earmark-slides',
            default => 'bi-file-earmark',
        };
    }
}
