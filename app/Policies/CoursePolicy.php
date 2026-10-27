<?php

namespace App\Policies;

class CoursePolicy extends ContentPolicy
{
    protected function module(): string
    {
        return 'courses';
    }
}
