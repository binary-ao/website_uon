<?php

namespace App\Policies;

class DocumentPolicy extends ContentPolicy
{
    protected function module(): string
    {
        return 'documents';
    }
}
