<?php

namespace App\Policies;

class OrganicUnitPolicy extends ContentPolicy
{
    protected function module(): string
    {
        return 'units';
    }
}
