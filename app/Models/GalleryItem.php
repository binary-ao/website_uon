<?php

namespace App\Models;

use App\Models\Concerns\HasPublication;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
    use HasPublication;

    public const TYPES = ['image' => 'Imagem', 'video' => 'Vídeo'];

    protected $fillable = [
        'type', 'title', 'caption', 'media_id', 'video_url', 'duration_label', 'category_id',
        'status', 'published_at', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_demo' => 'boolean'];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function embedUrl(): ?string
    {
        return VideoEmbed::embedUrl($this->video_url);
    }
}
