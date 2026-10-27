<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Revision extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'data', 'created_at'];

    protected function casts(): array
    {
        return ['data' => \App\Casts\UnicodeJson::class, 'created_at' => 'datetime'];
    }

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return [
            'created' => 'Criação',
            'updated' => 'Edição',
            'published' => 'Publicação',
            'unpublished' => 'Despublicação',
            'archived' => 'Arquivo',
            'restored' => 'Reposição de revisão',
            'duplicated' => 'Duplicação',
        ][$this->action] ?? $this->action;
    }
}
