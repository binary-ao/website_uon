<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonySanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitização do HTML editorial no servidor (protecção contra XSS).
 * Só são permitidos elementos estruturais compatíveis com o estilo
 * .article-body do template; scripts, estilos inline, iframes e
 * atributos de eventos são sempre removidos.
 */
class HtmlSanitizer
{
    private static ?SymfonySanitizer $instance = null;

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }
        $clean = static::sanitizer()->sanitize($html);

        return trim($clean) === '' ? null : $clean;
    }

    private static function sanitizer(): SymfonySanitizer
    {
        if (static::$instance) {
            return static::$instance;
        }

        $config = (new HtmlSanitizerConfig())
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowElement('u')
            ->allowElement('s')
            ->allowElement('sub')
            ->allowElement('sup')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('blockquote')
            ->allowElement('hr')
            ->allowElement('code')
            ->allowElement('pre')
            ->allowElement('a', ['href', 'title', 'target'])
            ->allowElement('img', ['src', 'alt', 'width', 'height', 'title'])
            ->allowElement('figure')
            ->allowElement('figcaption')
            ->allowElement('table')
            ->allowElement('caption')
            ->allowElement('thead')
            ->allowElement('tbody')
            ->allowElement('tfoot')
            ->allowElement('tr')
            ->allowElement('th', ['colspan', 'rowspan', 'scope'])
            ->allowElement('td', ['colspan', 'rowspan'])
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias()
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->forceAttribute('img', 'loading', 'lazy')
            ->withMaxInputLength(500000);

        return static::$instance = new SymfonySanitizer($config);
    }

    /** Texto simples a partir de HTML (para resumos e pesquisa). */
    public static function toText(?string $html, int $limit = 0): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return $limit > 0 ? \Illuminate\Support\Str::limit($text, $limit) : $text;
    }
}
