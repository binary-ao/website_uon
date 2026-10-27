<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasUniqueSlug;

    protected string $slugSource = 'name';

    protected $fillable = ['name', 'slug'];

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class);
    }

    /** Converte "a, b, c" em IDs de etiquetas, criando as inexistentes (sem duplicar). */
    public static function idsFromString(?string $input): array
    {
        $names = collect(explode(',', (string) $input))
            ->map(fn ($n) => trim(Str::limit(strip_tags($n), 60, '')))
            ->filter()
            ->unique(fn ($n) => Str::slug($n));

        return $names->map(function ($name) {
            $slug = Str::slug($name);

            return static::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
        })->values()->all();
    }
}
