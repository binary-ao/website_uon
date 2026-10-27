<?php

namespace App\Support;

/** Converte URLs do YouTube/Vimeo em URLs de incorporação. Outros serviços não são aceites. */
class VideoEmbed
{
    public static function embedUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '' || ! preg_match('#^https://#i', $url)) {
            return null;
        }
        if (preg_match('~^https://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }
        if (preg_match('~^https://(?:www\.)?vimeo\.com/(?:video/)?(\d{6,12})~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    public static function rule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if ($value && ! static::embedUrl($value)) {
                $fail('Indique um URL https do YouTube ou do Vimeo.');
            }
        };
    }
}
