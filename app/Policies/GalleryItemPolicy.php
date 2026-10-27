<?php

namespace App\Policies;

class GalleryItemPolicy extends ContentPolicy
{
    protected function module(): string
    {
        return 'gallery';
    }
}
