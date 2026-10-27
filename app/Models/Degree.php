<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Degree extends Model
{
    use HasUniqueSlug;

    protected string $slugSource = 'name';

    protected $fillable = ['name', 'plural_name', 'slug', 'sort_order'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function pluralLabel(): string
    {
        return $this->plural_name ?: $this->name;
    }
}
