<?php

use App\Support\Settings;

if (! function_exists('setting')) {
    /** Valor de uma configuração global do CMS. */
    function setting(string $key, $default = null)
    {
        return Settings::get($key, $default);
    }
}
