<?php

namespace App\Support;

/**
 * Validação de URLs introduzidos no CMS (menus, botões, blocos, avisos…).
 * Aceita apenas: caminhos internos ("/..."), âncoras ("#..."), http(s), mailto e tel.
 * Bloqueia javascript:, data:, vbscript:, URLs protocol-relative ("//host") e afins.
 */
class SafeUrl
{
    public static function isValid(?string $url): bool
    {
        $url = trim((string) $url);
        if (preg_match('/^tel:\+?[0-9 ()\-]{3,30}$/i', $url)) {
            return true;
        }
        if ($url === '' || preg_match('/[\x00-\x1F\x7F\s]/', $url)) {
            return false;
        }
        if (str_starts_with($url, '//')) {
            return false;
        }
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return ! str_contains($url, '\\');
        }
        if (preg_match('#^https?://#i', $url)) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }
        if (preg_match('/^mailto:[^\s@]+@[^\s@]+$/i', $url)) {
            return true;
        }
        if (preg_match('/^tel:\+?[0-9 ()\-]{3,30}$/i', $url)) {
            return true;
        }

        return false;
    }

    /** URL pronto a usar num href, ou null se inválido/vazio. */
    public static function resolve(?string $url): ?string
    {
        $url = trim((string) $url);
        if (! static::isValid($url)) {
            return null;
        }
        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return $url;
    }

    public static function isExternal(?string $url): bool
    {
        $url = trim((string) $url);
        if (! preg_match('#^https?://#i', $url)) {
            return false;
        }
        $host = parse_url($url, PHP_URL_HOST);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        return $host && $host !== $appHost && $host !== request()->getHost();
    }

    /** Regra de validação reutilizável. */
    public static function rule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if ($value !== null && $value !== '' && ! static::isValid($value)) {
                $fail('O endereço indicado não é válido. Use um caminho interno (/...), http(s)://, mailto: ou tel:.');
            }
        };
    }
}
