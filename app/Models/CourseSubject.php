<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSubject extends Model
{
    public $timestamps = false;

    protected $fillable = ['year', 'semester', 'code', 'name', 'hours', 'credits', 'is_optional', 'sort_order'];

    protected function casts(): array
    {
        return ['is_optional' => 'boolean', 'credits' => 'float'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
