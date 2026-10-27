<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Gera slugs legíveis e únicos (inclui registos na reciclagem, porque a
 * restrição de unicidade da base de dados também os inclui).
 */
trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model) {
            $source = $model->slugSourceField();
            $slug = Str::slug((string) ($model->slug ?: $model->{$source}));
            if ($slug === '') {
                $slug = Str::lower(Str::random(8));
            }
            if ($model->isDirty('slug') || ! $model->exists || $model->slug === null || $model->slug === '') {
                $model->slug = $model->uniqueSlug($slug);
            }
        });
    }

    protected function slugSourceField(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'title';
    }

    /** Campos que delimitam o âmbito da unicidade (ex.: parent_id nas páginas). */
    protected function slugScope(): array
    {
        return [];
    }

    public function uniqueSlug(string $base): string
    {
        $base = Str::limit($base, 180, '');
        $slug = $base;
        $i = 2;
        while ($this->slugExists($slug)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class), true)) {
            $query->withTrashed();
        }
        foreach ($this->slugScope() as $column) {
            $value = $this->{$column};
            $value === null ? $query->whereNull($column) : $query->where($column, $value);
        }
        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }
}
