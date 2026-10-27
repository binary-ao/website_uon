<?php

namespace App\Support;

/**
 * Definição das configurações globais administráveis.
 *
 * Valores por omissão: apenas o nome institucional (confirmado pelo
 * logótipo oficial) e textos de interface neutros herdados do template.
 * Contactos, moradas, redes sociais e ligações externas ficam VAZIOS até
 * serem confirmados pela UON — os elementos correspondentes não são
 * apresentados no website enquanto estiverem vazios.
 */
class SettingsSchema
{
    public static function groups(): array
    {
        return [
            'identity' => [
                'label' => 'Identidade',
                'icon' => 'bi-bank',
                'fields' => [
                    'site_name' => ['label' => 'Nome institucional', 'type' => 'text', 'default' => 'Universidade Onze de Novembro', 'required' => true],
                    'site_short_name' => ['label' => 'Sigla', 'type' => 'text', 'default' => 'UON'],
                    'site_tagline' => ['label' => 'Lema / assinatura', 'type' => 'text', 'default' => '', 'help' => 'Por confirmar pela UON. Se vazio, não é apresentado.'],
                    'site_description' => ['label' => 'Descrição institucional', 'type' => 'textarea', 'default' => '', 'help' => 'Usada como meta description por omissão e no rodapé (se o texto do rodapé estiver vazio).'],
                    'logo_media_id' => ['label' => 'Logótipo (Header)', 'type' => 'media', 'default' => '', 'help' => 'Se vazio, é usado o logótipo oficial fornecido (LOGO_UON_160X100.png). Não deformar: a imagem é sempre apresentada proporcionalmente.'],
                    'logo_footer_media_id' => ['label' => 'Logótipo (Rodapé)', 'type' => 'media', 'default' => '', 'help' => 'Se vazio, usa o mesmo logótipo do Header.'],
                    'favicon_media_id' => ['label' => 'Favicon', 'type' => 'media', 'default' => '', 'help' => 'Imagem quadrada (PNG). Se vazio, usa o logótipo.'],
                ],
            ],
            'contacts' => [
                'label' => 'Contactos',
                'icon' => 'bi-telephone',
                'fields' => [
                    'contact_phone' => ['label' => 'Telefone principal', 'type' => 'text', 'default' => ''],
                    'contact_phone_alt' => ['label' => 'Telefone alternativo', 'type' => 'text', 'default' => ''],
                    'contact_email' => ['label' => 'Email geral', 'type' => 'email', 'default' => ''],
                    'contact_email_secondary' => ['label' => 'Email secundário', 'type' => 'email', 'default' => '', 'help' => 'Ex.: email dos serviços académicos ou candidaturas.'],
                    'contact_address' => ['label' => 'Morada', 'type' => 'textarea', 'default' => ''],
                    'office_hours' => ['label' => 'Horário de atendimento', 'type' => 'text', 'default' => ''],
                    'map_embed_url' => ['label' => 'URL de incorporação do mapa', 'type' => 'url', 'default' => '', 'help' => 'Opcional. Apenas https://www.google.com/maps/embed?... ou https://www.openstreetmap.org/export/embed.html?...'],
                    'contact_form_recipient' => ['label' => 'Email que recebe as mensagens do formulário', 'type' => 'email', 'default' => '', 'help' => 'As mensagens ficam sempre guardadas no CMS. O envio por email requer configuração SMTP no .env.'],
                    'contact_subjects' => ['label' => 'Assuntos do formulário de contacto', 'type' => 'lines', 'default' => "Informações sobre candidaturas\nOferta formativa\nParcerias e cooperação\nOutro assunto", 'help' => 'Um assunto por linha.'],
                ],
            ],
            'social' => [
                'label' => 'Redes sociais',
                'icon' => 'bi-share',
                'fields' => [
                    'social_facebook' => ['label' => 'Facebook', 'type' => 'url', 'default' => ''],
                    'social_linkedin' => ['label' => 'LinkedIn', 'type' => 'url', 'default' => ''],
                    'social_instagram' => ['label' => 'Instagram', 'type' => 'url', 'default' => ''],
                    'social_youtube' => ['label' => 'YouTube', 'type' => 'url', 'default' => ''],
                ],
            ],
            'external' => [
                'label' => 'Plataformas externas',
                'icon' => 'bi-box-arrow-up-right',
                'fields' => [
                    'research_url' => ['label' => 'Centro de Investigação', 'type' => 'url', 'default' => ''],
                    'research_projects_url' => ['label' => 'Projectos', 'type' => 'url', 'default' => ''],
                    'research_publications_url' => ['label' => 'Publicações', 'type' => 'url', 'default' => ''],
                    'research_journals_url' => ['label' => 'Revistas Científicas', 'type' => 'url', 'default' => ''],
                ],
            ],
            'header' => [
                'label' => 'Header',
                'icon' => 'bi-window',
                'fields' => [
                    'header_cta_label' => ['label' => 'Botão de destaque — texto', 'type' => 'text', 'default' => 'Candidaturas'],
                    'header_cta_url' => ['label' => 'Botão de destaque — destino', 'type' => 'url', 'default' => '/ensino/oferta-formativa'],
                    'search_placeholder' => ['label' => 'Texto do campo de pesquisa', 'type' => 'text', 'default' => 'Pesquisar no website (cursos, notícias, serviços...)'],
                ],
            ],
            'footer' => [
                'label' => 'Rodapé',
                'icon' => 'bi-layout-text-window-reverse',
                'fields' => [
                    'footer_about' => ['label' => 'Texto institucional do rodapé', 'type' => 'textarea', 'default' => ''],
                    'footer_external_note' => ['label' => 'Nota sob a coluna de plataformas externas', 'type' => 'text', 'default' => '↗ Acesso a plataformas externas'],
                    'footer_contacts_title' => ['label' => 'Título da coluna de contactos', 'type' => 'text', 'default' => 'Contactos'],
                    'footer_contacts_button' => ['label' => 'Botão da coluna de contactos', 'type' => 'text', 'default' => 'Ver contactos'],
                    'footer_copyright' => ['label' => 'Texto de direitos de autor', 'type' => 'text', 'default' => 'Todos os direitos reservados.'],
                ],
            ],
            'seo' => [
                'label' => 'SEO',
                'icon' => 'bi-search',
                'fields' => [
                    'seo_title_separator' => ['label' => 'Separador do título', 'type' => 'text', 'default' => '—'],
                    'seo_home_title' => ['label' => 'Título da página inicial', 'type' => 'text', 'default' => ''],
                    'seo_og_image_media_id' => ['label' => 'Imagem de partilha (Open Graph)', 'type' => 'media', 'default' => ''],
                    'seo_allow_indexing' => ['label' => 'Permitir indexação pelos motores de pesquisa', 'type' => 'bool', 'default' => '1'],
                    'show_demo_badges' => ['label' => 'Mostrar o selo "Exemplo" nos conteúdos de demonstração', 'type' => 'bool', 'default' => '1', 'help' => 'Os conteúdos de demonstração continuam marcados no CMS e podem ser removidos com php artisan uon:demo-content --remove.'],
                ],
            ],
            'home' => [
                'label' => 'Página inicial',
                'icon' => 'bi-house',
                'fields' => [
                    'hero_eyebrow' => ['label' => 'Hero — sobretítulo', 'type' => 'text', 'default' => 'Universidade Onze de Novembro'],
                    'hero_title' => ['label' => 'Hero — título', 'type' => 'text', 'default' => 'Universidade Onze de Novembro'],
                    'hero_title_highlight' => ['label' => 'Hero — parte do título em destaque', 'type' => 'text', 'default' => '', 'help' => 'Texto acrescentado ao título com a cor institucional.'],
                    'hero_lead' => ['label' => 'Hero — texto', 'type' => 'textarea', 'default' => ''],
                    'hero_primary_label' => ['label' => 'Hero — botão principal', 'type' => 'text', 'default' => 'Conheça a Universidade'],
                    'hero_primary_url' => ['label' => 'Hero — destino do botão principal', 'type' => 'url', 'default' => '/instituicao/sobre'],
                    'hero_secondary_label' => ['label' => 'Hero — botão secundário', 'type' => 'text', 'default' => 'Oferta Formativa'],
                    'hero_secondary_url' => ['label' => 'Hero — destino do botão secundário', 'type' => 'url', 'default' => '/ensino/oferta-formativa'],
                    'hero_media_id' => ['label' => 'Hero — imagem', 'type' => 'media', 'default' => ''],
                    'home_stats' => ['label' => 'Números institucionais', 'type' => 'lines', 'default' => '', 'help' => 'Uma linha por indicador, no formato "valor | rótulo" (ex.: "+30 | Cursos"). Apenas números oficiais. Se vazio, a secção não é apresentada.'],
                    'about_eyebrow' => ['label' => 'Apresentação — sobretítulo', 'type' => 'text', 'default' => 'Sobre a Universidade'],
                    'about_title' => ['label' => 'Apresentação — título', 'type' => 'text', 'default' => ''],
                    'about_text' => ['label' => 'Apresentação — texto', 'type' => 'textarea', 'default' => '', 'help' => 'Se o título e o texto estiverem vazios, a secção não é apresentada.'],
                    'about_media_id' => ['label' => 'Apresentação — imagem', 'type' => 'media', 'default' => ''],
                    'about_figure_number' => ['label' => 'Apresentação — destaque numérico', 'type' => 'text', 'default' => '', 'help' => 'Ex.: ano de fundação (apenas se confirmado).'],
                    'about_figure_label' => ['label' => 'Apresentação — legenda do destaque', 'type' => 'text', 'default' => ''],
                    'about_link_label' => ['label' => 'Apresentação — texto do link', 'type' => 'text', 'default' => 'Conheça a Universidade'],
                    'about_link_url' => ['label' => 'Apresentação — destino do link', 'type' => 'url', 'default' => '/instituicao/sobre'],
                    'courses_title' => ['label' => 'Oferta formativa — título', 'type' => 'text', 'default' => 'Oferta Formativa'],
                    'courses_intro' => ['label' => 'Oferta formativa — introdução', 'type' => 'textarea', 'default' => ''],
                    'research_title' => ['label' => 'Investigação — título', 'type' => 'text', 'default' => 'Investigação e Conhecimento'],
                    'research_text' => ['label' => 'Investigação — texto', 'type' => 'textarea', 'default' => ''],
                    'cta_title' => ['label' => 'Chamada final — título', 'type' => 'text', 'default' => 'Conheça a nossa oferta formativa'],
                    'cta_text' => ['label' => 'Chamada final — texto', 'type' => 'textarea', 'default' => ''],
                    'cta_button_label' => ['label' => 'Chamada final — botão', 'type' => 'text', 'default' => 'Ver oferta formativa'],
                    'cta_button_url' => ['label' => 'Chamada final — destino', 'type' => 'url', 'default' => '/ensino/oferta-formativa'],
                    'partners_title' => ['label' => 'Parceiros — título', 'type' => 'text', 'default' => 'Instituições e empresas parceiras'],
                ],
            ],
        ];
    }

    public static function fields(): array
    {
        $out = [];
        foreach (static::groups() as $group => $def) {
            foreach ($def['fields'] as $key => $field) {
                $out[$key] = $field + ['group' => $group];
            }
        }

        return $out;
    }

    public static function defaults(): array
    {
        return array_map(fn ($f) => $f['default'] ?? '', static::fields());
    }

    /** Chaves de URL que podem ser usadas como destino de itens de menu. */
    public static function linkableKeys(): array
    {
        return [
            'research_url' => 'Centro de Investigação',
            'research_projects_url' => 'Projectos (investigação)',
            'research_publications_url' => 'Publicações',
            'research_journals_url' => 'Revistas Científicas',
            'social_facebook' => 'Facebook',
            'social_linkedin' => 'LinkedIn',
            'social_instagram' => 'Instagram',
            'social_youtube' => 'YouTube',
        ];
    }
}
