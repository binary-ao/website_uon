<?php

namespace App\Support;

use App\Models\Media;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/** Acesso em cache às configurações globais (tabela site_settings). */
class Settings
{
    public const CACHE_KEY = 'uon.site_settings';

    private static ?array $values = null;

    private static array $mediaCache = [];

    public static function all(): array
    {
        if (static::$values !== null) {
            return static::$values;
        }

        $stored = [];
        try {
            $stored = Cache::rememberForever(static::CACHE_KEY, function () {
                return Schema::hasTable('site_settings')
                    ? SiteSetting::pluck('value', 'key')->all()
                    : [];
            });
        } catch (\Throwable $e) {
            report($e);
        }

        return static::$values = array_merge(SettingsSchema::defaults(), array_filter($stored, fn ($v) => $v !== null));
    }

    public static function get(string $key, $default = null)
    {
        $value = static::all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function flush(): void
    {
        static::$values = null;
        static::$mediaCache = [];
        Cache::forget(static::CACHE_KEY);
    }

    public static function set(array $values): void
    {
        $fields = SettingsSchema::fields();
        foreach ($values as $key => $value) {
            if (! isset($fields[$key])) {
                continue;
            }
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $fields[$key]['group']]);
        }
        static::flush();
    }

    public static function media(string $key): ?Media
    {
        $id = (int) static::get($key);
        if (! $id) {
            return null;
        }
        if (! array_key_exists($id, static::$mediaCache)) {
            static::$mediaCache[$id] = Media::find($id);
        }

        return static::$mediaCache[$id];
    }

    public static function logoUrl(bool $footer = false): string
    {
        $media = ($footer ? static::media('logo_footer_media_id') : null) ?? static::media('logo_media_id');

        return $media ? $media->url() : asset('assets/images/brand/logo-uon.png');
    }

    public static function faviconUrl(): string
    {
        $media = static::media('favicon_media_id');

        return $media ? $media->url() : asset('assets/images/brand/logo-uon.png');
    }

    /** Linhas de um campo do tipo "lines". */
    public static function lines(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) static::get($key, '')))));
    }

    /** Linhas "valor | rótulo" → [['value'=>..,'label'=>..]] */
    public static function pairs(string $key): array
    {
        return array_values(array_filter(array_map(function ($line) {
            [$value, $label] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');

            return $value !== '' ? ['value' => $value, 'label' => $label] : null;
        }, static::lines($key))));
    }

    /** Redes sociais preenchidas, pela ordem do template. */
    public static function socialLinks(): array
    {
        $map = [
            'facebook' => ['Facebook', 'bi-facebook'],
            'linkedin' => ['LinkedIn', 'bi-linkedin'],
            'instagram' => ['Instagram', 'bi-instagram'],
            'youtube' => ['YouTube', 'bi-youtube'],
        ];
        $out = [];
        foreach ($map as $key => [$label, $icon]) {
            $url = SafeUrl::resolve(static::get('social_'.$key));
            if ($url) {
                $out[] = ['label' => $label, 'icon' => $icon, 'url' => $url];
            }
        }

        return $out;
    }
}
