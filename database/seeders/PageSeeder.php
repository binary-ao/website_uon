<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Árvore de páginas correspondente à hierarquia do website_template.
 *
 * Os textos institucionais NÃO são inventados: as páginas são criadas com
 * título, sobretítulo e uma descrição neutra da sua função; o corpo fica
 * vazio para preenchimento pela UON (o website apresenta um estado
 * "conteúdo em preparação" enquanto a página estiver vazia).
 * Os cartões das páginas de secção são apenas navegação para as subpáginas.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $docCat = fn (string $slug) => Category::where('type', 'document')->where('slug', $slug)->value('id');

        $tree = [
            ['instituicao', 'Conheça a Universidade Onze de Novembro', 'standard', 'Instituição', 'Uma instituição académica dedicada ao ensino, à investigação e ao serviço à sociedade.', [], [
                ['sobre', 'Sobre a Universidade', 'sidebar', 'Instituição', 'Apresentação institucional da Universidade Onze de Novembro.', [], [], 'bi-building', 'Quem somos, a nossa área de actuação e o nosso propósito institucional.'],
                ['historia', 'A Nossa História', 'standard', 'Instituição', 'O percurso da Universidade desde a sua fundação.', [], [], 'bi-hourglass-split', 'O percurso da instituição desde a sua fundação até aos dias de hoje.'],
                ['missao-visao-valores', 'Missão, Visão e Valores', 'standard', 'Instituição', 'Os princípios que orientam a acção académica e institucional.', [], [], 'bi-compass', 'Os princípios que orientam a nossa acção académica e institucional.'],
                ['mensagem-direccao', 'Mensagem da Direcção', 'standard', 'Instituição', 'Uma palavra da liderança institucional à comunidade académica.', [], [], 'bi-chat-quote', 'Uma palavra da liderança institucional à comunidade académica.'],
                ['orgaos-gestao', 'Órgãos de Gestão', 'units_directory', 'Governação', 'Os órgãos responsáveis pela governação e direcção da Universidade.', ['unit_type' => 'orgao'], [], 'bi-diagram-3', 'Os órgãos responsáveis pela governação e direcção da instituição.'],
                ['estrutura-organizacional', 'Estrutura Organizacional', 'standard', 'Governação', 'Como a Universidade está organizada.', [], [], 'bi-diagram-2', 'Como a instituição está organizada, dos órgãos centrais às unidades orgânicas.'],
                ['unidades-organicas', 'Unidades Orgânicas', 'units_directory', 'Governação', 'Faculdades, institutos e escolas da Universidade.', ['unit_type' => 'academic'], [], 'bi-building-gear', 'Conheça as unidades orgânicas que sustentam a oferta formativa.'],
                ['servicos', 'Serviços', 'standard', 'Instituição', 'Serviços institucionais ao dispor de estudantes, docentes e comunidade.', [], [], 'bi-headset', 'Serviços institucionais ao dispor de estudantes, docentes e comunidade.'],
                ['documentos', 'Documentos Institucionais', 'documents_index', 'Instituição', 'Estatutos, regulamentos e relatórios de acesso público.', ['category_id' => $docCat('documentos-institucionais')], [], 'bi-file-earmark-text', 'Estatutos, regulamentos e relatórios de acesso público.'],
                ['parceiros', 'Parceiros', 'partners', 'Instituição', 'Instituições e empresas que colaboram com a Universidade.', [], [], 'bi-people', 'Instituições e empresas que colaboram connosco a nível nacional e internacional.'],
            ]],
            ['ensino', 'Ensino', 'standard', 'Ensino', 'Oferta formativa, vida académica e informação para estudantes.', [], [
                ['oferta-formativa', 'Oferta Formativa', 'courses_index', 'Ensino', 'Programas de licenciatura, mestrado e pós-graduação.', [], [], 'bi-mortarboard', 'Todos os cursos de licenciatura, mestrado e pós-graduação.'],
                ['licenciaturas', 'Licenciaturas', 'courses_index', 'Ensino', 'Cursos de licenciatura.', ['degree' => 'licenciatura'], [], null, null],
                ['mestrados', 'Mestrados', 'courses_index', 'Ensino', 'Cursos de mestrado.', ['degree' => 'mestrado'], [], null, null],
                ['pos-graduacoes', 'Pós-Graduações', 'courses_index', 'Ensino', 'Cursos de pós-graduação.', ['degree' => 'pos-graduacao'], [], null, null],
                ['departamentos', 'Departamentos', 'units_directory', 'Ensino', 'Departamentos responsáveis pela oferta formativa.', ['unit_type' => 'departamento'], [], 'bi-building', 'Os departamentos responsáveis pelos cursos.'],
                ['calendario-academico', 'Calendário Académico', 'standard', 'Ensino', 'Datas e períodos do ano académico.', [], [], 'bi-calendar3', 'Datas e períodos lectivos do ano académico.'],
                ['regulamentos', 'Regulamentos', 'documents_index', 'Ensino', 'Regulamentos académicos em vigor.', ['category_id' => $docCat('regulamentos')], [], 'bi-journal-text', 'Regulamentos académicos em vigor.'],
                ['informacoes-academicas', 'Informações Académicas', 'standard', 'Ensino', 'Informação útil para estudantes e candidatos.', [], [], 'bi-info-circle', 'Informação útil para estudantes e candidatos.'],
            ]],
            ['investigacao', 'Investigação e Conhecimento', 'standard', 'Investigação', 'Acesso às plataformas de investigação, projectos, publicações e revistas científicas.', [], []],
            ['extensao', 'Extensão e Cooperação', 'standard', 'Extensão', 'Projectos de extensão, cooperação institucional e mobilidade.', [], [
                ['projectos', 'Projectos de Extensão', 'standard', 'Extensão', 'Projectos de extensão universitária junto da comunidade.', [], [], 'bi-people', 'Projectos de extensão universitária junto da comunidade.'],
                ['cooperacao', 'Cooperação Institucional', 'partners', 'Extensão', 'Cooperação com instituições nacionais e internacionais.', [], [], 'bi-globe2', 'Cooperação com instituições nacionais e internacionais.'],
                ['protocolos', 'Protocolos', 'documents_index', 'Extensão', 'Protocolos e acordos de cooperação.', ['category_id' => $docCat('protocolos')], [], 'bi-file-earmark-check', 'Protocolos e acordos de cooperação.'],
                ['mobilidade', 'Mobilidade e Intercâmbio', 'standard', 'Extensão', 'Programas de mobilidade e intercâmbio académico.', [], [], 'bi-airplane', 'Programas de mobilidade e intercâmbio académico.'],
            ]],
            ['comunicacao', 'Comunicação', 'standard', 'Comunicação', 'Notícias, eventos, comunicados e conteúdos multimédia da Universidade.', [], [
                ['noticias', 'Notícias', 'news_index', 'Comunicação', 'As últimas novidades da nossa comunidade académica.', [], [], 'bi-newspaper', 'As últimas novidades da comunidade académica.'],
                ['eventos', 'Eventos', 'events_index', 'Comunicação', 'Conferências, cerimónias e actividades académicas.', [], [], 'bi-calendar-event', 'Conferências, cerimónias e actividades académicas.'],
                ['comunicados', 'Comunicados', 'announcements_index', 'Comunicação', 'Comunicados e avisos oficiais.', [], [], 'bi-megaphone', 'Comunicados e avisos oficiais.'],
                ['agenda', 'Agenda', 'agenda', 'Comunicação', 'Próximas actividades, organizadas por mês.', [], [], 'bi-calendar3', 'Próximas actividades, organizadas por mês.'],
                ['galeria', 'Galeria', 'gallery', 'Comunicação', 'Imagens da vida académica e institucional.', [], [], 'bi-images', 'Imagens da vida académica e institucional.'],
                ['multimedia', 'Multimédia', 'multimedia', 'Comunicação', 'Vídeos institucionais e outros conteúdos multimédia.', [], [], 'bi-play-btn', 'Vídeos institucionais e outros conteúdos.'],
            ]],
            ['servicos', 'Serviços', 'standard', 'Serviços', 'Serviços de apoio a estudantes, docentes e comunidade.', [], [
                ['academicos', 'Serviços Académicos', 'standard', 'Serviços', 'Matrículas, inscrições, certificados e outros actos académicos.', [], [], 'bi-mortarboard', 'Matrículas, inscrições, certificados e outros actos académicos.'],
                ['biblioteca', 'Biblioteca', 'standard', 'Serviços', 'Acervo, horários e serviços da biblioteca.', [], [], 'bi-book', 'Acervo, horários e serviços da biblioteca.'],
                ['apoio-estudante', 'Apoio ao Estudante', 'standard', 'Serviços', 'Serviços de apoio e acompanhamento dos estudantes.', [], [], 'bi-person-heart', 'Serviços de apoio e acompanhamento dos estudantes.'],
                ['administrativos', 'Serviços Administrativos', 'standard', 'Serviços', 'Serviços administrativos e financeiros.', [], [], 'bi-briefcase', 'Serviços administrativos e financeiros.'],
                ['documentos', 'Documentos e Formulários', 'documents_index', 'Serviços', 'Formulários e documentos para download.', ['category_id' => $docCat('formularios')], [], 'bi-file-earmark-arrow-down', 'Formulários e documentos para download.'],
            ]],
            ['contactos', 'Fale Connosco', 'contact', 'Contactos', 'Estamos disponíveis para esclarecer as suas dúvidas.', [], []],
            ['politica-de-privacidade', 'Política de Privacidade', 'standard', 'Informação legal', 'Como a Universidade trata os dados pessoais.', [], []],
            ['termos-de-utilizacao', 'Termos de Utilização', 'standard', 'Informação legal', 'Condições de utilização deste website.', [], []],
            ['acessibilidade', 'Acessibilidade', 'standard', 'Informação legal', 'Compromisso de acessibilidade deste website.', [], []],
        ];

        foreach ($tree as $i => $node) {
            $this->createNode($node, null, $i);
        }

        // Investigação: painel de acessos externos (usa os URLs das Configurações).
        $inv = Page::where('path', 'investigacao')->first();
        if ($inv && empty($inv->blocks)) {
            $inv->blocks = [[
                'type' => 'links_panel',
                'data' => [
                    'eyebrow' => 'Investigação',
                    'title' => 'Investigação e Conhecimento',
                    'text' => 'O Centro de Investigação, os projectos, as publicações e as revistas científicas estão disponíveis em plataformas externas próprias.',
                    'use_research_links' => true,
                ],
                'items' => [],
            ]];
            $inv->saveQuietly();
        }
    }

    private function createNode(array $node, ?Page $parent, int $order): Page
    {
        [$slug, $title, $layout, $eyebrow, $excerpt, $options, $children] = $node;
        $path = $parent ? $parent->path.'/'.$slug : $slug;

        $page = Page::withTrashed()->where('path', $path)->first();
        if (! $page) {
            $page = new Page([
                'parent_id' => $parent?->id,
                'title' => $title,
                'slug' => $slug,
                'layout' => $layout,
                'module_options' => $options ?: null,
                'eyebrow' => $eyebrow,
                'excerpt' => $excerpt,
                'status' => PublicationStatus::Published,
                'published_at' => now()->subMinute(),
                'sort_order' => $order,
            ]);
            $page->is_system = true;
            $page->save();
        }

        $cards = [];
        foreach ($children as $j => $child) {
            $childPage = $this->createNode($child, $page, $j);
            [$icon, $text] = [$child[7] ?? null, $child[8] ?? null];
            if ($icon) {
                $cards[] = ['icon' => $icon, 'title' => $childPage->title, 'text' => (string) $text, 'url' => '/'.$childPage->path];
            }
        }

        // Páginas de secção (template A — Landing): grelha de cartões de navegação.
        if ($cards && empty($page->blocks)) {
            $page->blocks = [['type' => 'cards', 'data' => ['title' => '', 'intro' => '', 'columns' => '3'], 'items' => $cards]];
            $page->saveQuietly();
        }

        return $page;
    }
}
