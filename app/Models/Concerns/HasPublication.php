<?php

namespace App\Models\Concerns;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lógica de publicação partilhada.
 *
 * Um registo é visível publicamente quando o estado é "published" ou
 * "scheduled" E a data de publicação já passou. Assim, o agendamento
 * funciona mesmo que o agendador de tarefas (cron) não esteja configurado;
 * o comando content:publish-scheduled apenas actualiza o rótulo do estado.
 */
trait HasPublication
{
    public static function bootHasPublication(): void
    {
        static::saving(function ($model) {
            $model->normalizePublication();
        });
    }

    public function initializeHasPublication(): void
    {
        $this->mergeCasts([
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ]);
    }

    public function normalizePublication(): void
    {
        $status = $this->status instanceof PublicationStatus
            ? $this->status
            : PublicationStatus::tryFrom((string) $this->status) ?? PublicationStatus::Draft;

        if ($status->isPublic()) {
            if (! $this->published_at) {
                $this->published_at = now();
            }
            $status = $this->published_at->isFuture() ? PublicationStatus::Scheduled : PublicationStatus::Published;
        }

        $this->status = $status;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereIn($this->qualifyColumn('status'), [PublicationStatus::Published->value, PublicationStatus::Scheduled->value])
            ->where($this->qualifyColumn('published_at'), '<=', now());
    }

    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        if ($status && PublicationStatus::tryFrom($status)) {
            $query->where($this->qualifyColumn('status'), $status);
        }

        return $query;
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status?->isPublic() && $this->published_at && ! $this->published_at->isFuture()
            && (! method_exists($this, 'trashed') || ! $this->trashed());
    }
}
