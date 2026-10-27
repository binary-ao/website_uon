<?php

namespace App\Models\Concerns;

use App\Models\Revision;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Histórico de revisões: guarda um instantâneo dos campos editoriais
 * sempre que o conteúdo é gravado através do CMS.
 */
trait Revisionable
{
    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable')->latest('id');
    }

    /** Campos guardados em cada revisão. */
    abstract public function revisionFields(): array;

    public function recordRevision(string $action): Revision
    {
        $data = [];
        foreach ($this->revisionFields() as $field) {
            $value = $this->getAttribute($field);
            $data[$field] = $value instanceof \BackedEnum ? $value->value
                : ($value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : $value);
        }

        return $this->revisions()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'data' => $data,
            'created_at' => now(),
        ]);
    }

    public function applyRevision(Revision $revision): void
    {
        foreach ($revision->data as $field => $value) {
            if (in_array($field, $this->revisionFields(), true) && ! in_array($field, ['status', 'published_at'], true)) {
                $this->setAttribute($field, $value);
            }
        }
    }
}
