<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Media;

/**
 * Blocos de conteúdo controlados.
 *
 * Cada bloco corresponde a um componente existente no website_template
 * (card-service, card-directory, curriculum-year, accordion-brand, table-brand,
 * org-chart, profile-box, cta-banner, research-panel, doc-row, partner-logo…).
 * Os blocos guardam apenas TEXTO SIMPLES, URLs validados e IDs — nunca HTML —
 * e são sempre apresentados com escaping, pelo que não podem quebrar a
 * estrutura global do website.
 *
 * Estrutura: ['type' => 'cards', 'data' => [...campos], 'items' => [[...], ...]]
 */
class Blocks
{
    public const MAX_BLOCKS = 40;

    public const MAX_ITEMS = 80;

    public static function definitions(): array
    {
        return [
            'cards' => [
                'label' => 'Grelha de cartões (serviços / atalhos)',
                'icon' => 'bi-grid',
                'fields' => [
                    'title' => ['label' => 'Título da secção (opcional)', 'type' => 'text'],
                    'intro' => ['label' => 'Introdução (opcional)', 'type' => 'textarea'],
                    'columns' => ['label' => 'Colunas', 'type' => 'select', 'options' => ['3' => '3 colunas', '2' => '2 colunas', '4' => '4 colunas'], 'default' => '3'],
                ],
                'item_label' => 'Cartão',
                'items' => [
                    'icon' => ['label' => 'Ícone (Bootstrap Icons, ex.: bi-building)', 'type' => 'icon'],
                    'title' => ['label' => 'Título', 'type' => 'text', 'required' => true],
                    'text' => ['label' => 'Texto', 'type' => 'textarea'],
                    'url' => ['label' => 'Ligação (opcional)', 'type' => 'url'],
                ],
            ],
            'directory' => [
                'label' => 'Directório (cartões com função/cargo)',
                'icon' => 'bi-person-vcard',
                'fields' => [
                    'title' => ['label' => 'Título da secção (opcional)', 'type' => 'text'],
                    'intro' => ['label' => 'Introdução (opcional)', 'type' => 'textarea'],
                    'columns' => ['label' => 'Colunas', 'type' => 'select', 'options' => ['3' => '3 colunas', '2' => '2 colunas', '4' => '4 colunas'], 'default' => '3'],
                ],
                'item_label' => 'Entrada',
                'items' => [
                    'title' => ['label' => 'Nome / título', 'type' => 'text', 'required' => true],
                    'role' => ['label' => 'Função / subtítulo', 'type' => 'text'],
                    'text' => ['label' => 'Descrição', 'type' => 'textarea'],
                    'lines' => ['label' => 'Linhas de detalhe (uma por linha; opcional "bi-icone | texto")', 'type' => 'textarea'],
                ],
            ],
            'timeline' => [
                'label' => 'Cronologia (história)',
                'icon' => 'bi-hourglass-split',
                'fields' => [
                    'title' => ['label' => 'Título da secção (opcional)', 'type' => 'text'],
                ],
                'item_label' => 'Marco',
                'items' => [
                    'heading' => ['label' => 'Data / título do marco', 'type' => 'text', 'required' => true],
                    'text' => ['label' => 'Texto', 'type' => 'textarea'],
                ],
            ],
            'accordion' => [
                'label' => 'Acordeão (perguntas, valores, informações)',
                'icon' => 'bi-list-nested',
                'fields' => [
                    'title' => ['label' => 'Título da secção (opcional)', 'type' => 'text'],
                ],
                'item_label' => 'Painel',
                'items' => [
                    'title' => ['label' => 'Título', 'type' => 'text', 'required' => true],
                    'text' => ['label' => 'Conteúdo', 'type' => 'textarea'],
                ],
            ],
            'table' => [
                'label' => 'Tabela (calendários, horários, planos)',
                'icon' => 'bi-table',
                'fields' => [
                    'title' => ['label' => 'Título (opcional)', 'type' => 'text'],
                    'headers' => ['label' => 'Cabeçalhos das colunas (separados por |)', 'type' => 'text', 'required' => true],
                    'rows' => ['label' => 'Linhas (uma por linha, colunas separadas por |)', 'type' => 'textarea', 'rows' => 8],
                    'note' => ['label' => 'Nota sob a tabela (opcional)', 'type' => 'text'],
                ],
            ],
            'org_chart' => [
                'label' => 'Organograma',
                'icon' => 'bi-diagram-3',
                'fields' => [
                    'title' => ['label' => 'Título (opcional)', 'type' => 'text'],
                    'note' => ['label' => 'Nota (opcional)', 'type' => 'text'],
                ],
                'item_label' => 'Nível',
                'items' => [
                    'nodes' => ['label' => 'Elementos deste nível (separados por ;)', 'type' => 'text', 'required' => true],
                    'highlight' => ['label' => 'Destacar (nível de topo)', 'type' => 'checkbox'],
                ],
            ],
            'profile_box' => [
                'label' => 'Caixa de destaque',
                'icon' => 'bi-card-text',
                'fields' => [
                    'title' => ['label' => 'Título', 'type' => 'text'],
                    'text' => ['label' => 'Texto', 'type' => 'textarea', 'required' => true],
                ],
            ],
            'text' => [
                'label' => 'Texto simples',
                'icon' => 'bi-text-paragraph',
                'fields' => [
                    'title' => ['label' => 'Título (opcional)', 'type' => 'text'],
                    'text' => ['label' => 'Texto (parágrafos separados por linha em branco)', 'type' => 'textarea', 'rows' => 8, 'required' => true],
                ],
            ],
            'image' => [
                'label' => 'Imagem',
                'icon' => 'bi-image',
                'fields' => [
                    'media_id' => ['label' => 'Imagem', 'type' => 'media', 'required' => true],
                    'caption' => ['label' => 'Legenda (opcional)', 'type' => 'text'],
                ],
            ],
            'video' => [
                'label' => 'Vídeo (YouTube / Vimeo)',
                'icon' => 'bi-play-btn',
                'fields' => [
                    'url' => ['label' => 'URL do vídeo', 'type' => 'video', 'required' => true],
                    'caption' => ['label' => 'Legenda (opcional)', 'type' => 'text'],
                ],
            ],
            'documents' => [
                'label' => 'Lista de documentos',
                'icon' => 'bi-file-earmark-text',
                'fields' => [
                    'title' => ['label' => 'Título (opcional)', 'type' => 'text'],
                    'category_id' => ['label' => 'Categoria de documentos', 'type' => 'select', 'options' => 'document_categories', 'required' => true],
                    'limit' => ['label' => 'Número máximo', 'type' => 'select', 'options' => ['5' => '5', '10' => '10', '20' => '20', '50' => '50'], 'default' => '10'],
                ],
            ],
            'partners' => [
                'label' => 'Parceiros (lista gerida no módulo Parceiros)',
                'icon' => 'bi-people',
                'fields' => [
                    'title' => ['label' => 'Título (opcional)', 'type' => 'text'],
                ],
            ],
            'links_panel' => [
                'label' => 'Painel de acessos externos',
                'icon' => 'bi-box-arrow-up-right',
                'fields' => [
                    'eyebrow' => ['label' => 'Sobretítulo', 'type' => 'text'],
                    'title' => ['label' => 'Título', 'type' => 'text', 'required' => true],
                    'text' => ['label' => 'Texto', 'type' => 'textarea'],
                    'use_research_links' => ['label' => 'Incluir as ligações de Investigação definidas nas Configurações', 'type' => 'checkbox'],
                ],
                'item_label' => 'Ligação',
                'items' => [
                    'label' => ['label' => 'Texto', 'type' => 'text', 'required' => true],
                    'url' => ['label' => 'URL', 'type' => 'url', 'required' => true],
                ],
            ],
            'cta' => [
                'label' => 'Chamada à acção (banner)',
                'icon' => 'bi-megaphone',
                'fields' => [
                    'title' => ['label' => 'Título', 'type' => 'text', 'required' => true],
                    'text' => ['label' => 'Texto', 'type' => 'textarea'],
                    'button_label' => ['label' => 'Texto do botão', 'type' => 'text'],
                    'button_url' => ['label' => 'Destino do botão', 'type' => 'url'],
                ],
            ],
        ];
    }

    /** Definições prontas para o editor JavaScript (opções dinâmicas resolvidas). */
    public static function editorSchema(): array
    {
        $defs = static::definitions();
        $dynamic = [
            'document_categories' => Category::ofType('document')->pluck('name', 'id')->mapWithKeys(fn ($n, $id) => [(string) $id => $n])->all(),
        ];
        foreach ($defs as &$def) {
            foreach (['fields', 'items'] as $group) {
                foreach ($def[$group] ?? [] as &$f) {
                    if (($f['type'] ?? '') === 'select' && is_string($f['options'])) {
                        $f['options'] = $dynamic[$f['options']] ?? [];
                    }
                }
            }
        }

        return $defs;
    }

    /**
     * Sanitiza os blocos recebidos do editor. Descarta tipos e campos
     * desconhecidos; texto é guardado sem HTML; URLs são validados.
     */
    public static function sanitize($input): array
    {
        if (is_string($input)) {
            $input = json_decode($input, true);
        }
        if (! is_array($input)) {
            return [];
        }

        $defs = static::editorSchema();
        $out = [];
        foreach (array_slice(array_values($input), 0, static::MAX_BLOCKS) as $block) {
            $type = $block['type'] ?? null;
            if (! is_string($type) || ! isset($defs[$type])) {
                continue;
            }
            $def = $defs[$type];
            $clean = ['type' => $type, 'data' => static::cleanFields($def['fields'] ?? [], (array) ($block['data'] ?? []))];
            if (isset($def['items'])) {
                $items = [];
                foreach (array_slice(array_values((array) ($block['items'] ?? [])), 0, static::MAX_ITEMS) as $item) {
                    $cleanItem = static::cleanFields($def['items'], (array) $item);
                    if (array_filter($cleanItem, fn ($v) => $v !== '' && $v !== false && $v !== null)) {
                        $items[] = $cleanItem;
                    }
                }
                $clean['items'] = $items;
            }
            $out[] = $clean;
        }

        return $out;
    }

    private static function cleanFields(array $fields, array $values): array
    {
        $clean = [];
        foreach ($fields as $name => $f) {
            $raw = $values[$name] ?? ($f['default'] ?? '');
            $clean[$name] = match ($f['type']) {
                'checkbox' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
                'url' => SafeUrl::isValid(is_string($raw) ? trim($raw) : '') ? trim($raw) : '',
                'video' => VideoEmbed::embedUrl(is_string($raw) ? trim($raw) : '') ? trim($raw) : '',
                'icon' => preg_match('/^bi-[a-z0-9-]{1,40}$/', (string) $raw) ? (string) $raw : '',
                'media' => (is_numeric($raw) && Media::whereKey((int) $raw)->exists()) ? (int) $raw : null,
                'select' => array_key_exists((string) $raw, (array) $f['options']) ? (string) $raw : (string) ($f['default'] ?? array_key_first((array) $f['options']) ?? ''),
                'textarea' => mb_substr(trim(strip_tags(str_replace("\r\n", "\n", (string) (is_scalar($raw) ? $raw : '')))), 0, 10000),
                default => mb_substr(trim(strip_tags((string) (is_scalar($raw) ? $raw : ''))), 0, 500),
            };
        }

        return $clean;
    }

    /** Texto plano de todos os blocos (para a pesquisa). */
    public static function plainText(?array $blocks): string
    {
        $parts = [];
        $blocks = $blocks ?? [];
        array_walk_recursive($blocks, function ($v, $k) use (&$parts) {
            if (is_string($v) && ! in_array($k, ['type', 'url', 'button_url', 'icon', 'columns'], true)) {
                $parts[] = $v;
            }
        });

        return implode(' ', $parts);
    }
}
