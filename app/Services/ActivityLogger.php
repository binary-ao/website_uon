<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Registo de auditoria das acções administrativas importantes. */
class ActivityLogger
{
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): void
    {
        try {
            $request = request();
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'subject_type' => $subject ? $subject->getMorphClass() : null,
                'subject_id' => $subject?->getKey(),
                'description' => Str::limit($description ?? $action, 250),
                'properties' => $properties ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => Str::limit((string) $request?->userAgent(), 250, ''),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // O registo de auditoria nunca deve impedir a operação principal.
            report($e);
        }
    }
}
