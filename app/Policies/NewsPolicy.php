<?php

namespace App\Policies;

class NewsPolicy extends ContentPolicy
{
    protected function module(): string
    {
        return 'news';
    }
}
