<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    public const STATUSES = ['new' => 'Nova', 'read' => 'Lida', 'archived' => 'Arquivada'];

    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }
}
