<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

class DocumentVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'version', 'disk', 'path', 'original_name', 'mime_type', 'extension', 'size', 'checksum',
        'notes', 'uploaded_by', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'size' => 'integer'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }
}
