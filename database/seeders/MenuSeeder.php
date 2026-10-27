<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Menus iniciais — reproduzem exactamente a organização do Header
 * (navbar + mega menu + offcanvas) e do Footer do website_template.
 * Só são criados se o menu ainda não tiver itens (não sobrepõe edições).
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $p = fn (string $path) => ['link_type' => 'page', 'page_id' => Page::where('path', $path)->value('id')];
        $s = fn (string $key) => ['link_type' => 'setting', 'url' => $key, 'open_in_new_tab' => true];
        $u = fn (string $url) => ['link_type' => 'url', 'url' => $url];

        $menus = [
            'main' => ['Navegação principal', null, [
                ['Início', 'link', $u('/')],
                ['Instituição', 'mega', $p('instituicao'), [
                    ['Sobre a Universidade', 'column', [], [
                        ['Sobre a Universidade', 'link', $p('instituicao/sobre')],
                        ['História', 'link', $p('instituicao/historia')],
                        ['Missão, Visão e Valores', 'link', $p('instituicao/missao-visao-valores')],
                        ['Mensagem da Direcção', 'link', $p('instituicao/mensagem-direccao')],
                    ]],
                    ['Governação', 'column', [], [
                        ['Órgãos de Gestão', 'link', $p('instituicao/orgaos-gestao')],
                        ['Estrutura Organizacional', 'link', $p('instituicao/estrutura-organizacional')],
                        ['Unidades Orgânicas', 'link', $p('instituicao/unidades-organicas')],
                    ]],
                    ['Institucional', 'column', [], [
                        ['Serviços', 'link', $p('instituicao/servicos')],
                        ['Documentos Institucionais', 'link', $p('instituicao/documentos')],
                        ['Parceiros', 'link', $p('instituicao/parceiros')],
                    ]],
                ]],
                ['Ensino', 'mega', $p('ensino'), [
                    ['Oferta Formativa', 'column', [], [
                        ['Toda a Oferta Formativa', 'link', $p('ensino/oferta-formativa'), [], 'Oferta Formativa'],
                        ['Licenciaturas', 'link', $p('ensino/licenciaturas')],
                        ['Mestrados', 'link', $p('ensino/mestrados')],
                        ['Pós-Graduações', 'link', $p('ensino/pos-graduacoes')],
                    ]],
                    ['Vida Académica', 'column', [], [
                        ['Departamentos', 'link', $p('ensino/departamentos')],
                        ['Calendário Académico', 'link', $p('ensino/calendario-academico')],
                        ['Regulamentos', 'link', $p('ensino/regulamentos')],
                        ['Informações Académicas', 'link', $p('ensino/informacoes-academicas')],
                    ]],
                ]],
                ['Investigação', 'dropdown', $p('investigacao'), [
                    ['Visão Geral', 'link', $p('investigacao')],
                    ['—', 'divider', []],
                    ['Centro de Investigação', 'link', $s('research_url')],
                    ['Projectos', 'link', $s('research_projects_url')],
                    ['Publicações', 'link', $s('research_publications_url')],
                    ['Revistas Científicas', 'link', $s('research_journals_url')],
                ]],
                ['Extensão', 'dropdown', $p('extensao'), [
                    ['Extensão e Cooperação', 'link', $p('extensao')],
                    ['Projectos de Extensão', 'link', $p('extensao/projectos')],
                    ['Cooperação Institucional', 'link', $p('extensao/cooperacao')],
                    ['Protocolos', 'link', $p('extensao/protocolos')],
                    ['Mobilidade e Intercâmbio', 'link', $p('extensao/mobilidade')],
                ], 'Extensão e Cooperação'],
                ['Comunicação', 'dropdown', $p('comunicacao'), [
                    ['Notícias', 'link', $p('comunicacao/noticias')],
                    ['Eventos', 'link', $p('comunicacao/eventos')],
                    ['Comunicados', 'link', $p('comunicacao/comunicados')],
                    ['Agenda', 'link', $p('comunicacao/agenda')],
                    ['Galeria', 'link', $p('comunicacao/galeria')],
                    ['Multimédia', 'link', $p('comunicacao/multimedia')],
                ]],
                ['Serviços', 'dropdown', $p('servicos'), [
                    ['Todos os Serviços', 'link', $p('servicos')],
                    ['Serviços Académicos', 'link', $p('servicos/academicos')],
                    ['Biblioteca', 'link', $p('servicos/biblioteca')],
                    ['Apoio ao Estudante', 'link', $p('servicos/apoio-estudante')],
                    ['Serviços Administrativos', 'link', $p('servicos/administrativos')],
                    ['Documentos e Formulários', 'link', $p('servicos/documentos')],
                ]],
                ['Contactos', 'link', $p('contactos')],
            ]],
            'topbar' => ['Barra superior', null, [
                ['Biblioteca', 'link', $p('servicos/biblioteca') + ['icon' => 'bi-book']],
                ['Contactos', 'link', $p('contactos') + ['icon' => 'bi-geo-alt']],
            ]],
            'footer_1' => ['Rodapé — coluna 1', 'Instituição', [
                ['Sobre', 'link', $p('instituicao/sobre')],
                ['História', 'link', $p('instituicao/historia')],
                ['Órgãos de Gestão', 'link', $p('instituicao/orgaos-gestao')],
                ['Unidades Orgânicas', 'link', $p('instituicao/unidades-organicas')],
                ['Parceiros', 'link', $p('instituicao/parceiros')],
            ]],
            'footer_2' => ['Rodapé — coluna 2', 'Ensino', [
                ['Licenciaturas', 'link', $p('ensino/licenciaturas')],
                ['Mestrados', 'link', $p('ensino/mestrados')],
                ['Pós-Graduações', 'link', $p('ensino/pos-graduacoes')],
                ['Calendário Académico', 'link', $p('ensino/calendario-academico')],
                ['Regulamentos', 'link', $p('ensino/regulamentos')],
            ]],
            'footer_3' => ['Rodapé — coluna 3', 'Investigação', [
                ['Centro de Investigação', 'link', $s('research_url')],
                ['Projectos', 'link', $s('research_projects_url')],
                ['Publicações', 'link', $s('research_publications_url')],
                ['Revistas Científicas', 'link', $s('research_journals_url')],
            ]],
            'footer_bottom' => ['Rodapé — links inferiores', null, [
                ['Política de Privacidade', 'link', $p('politica-de-privacidade')],
                ['Termos de Utilização', 'link', $p('termos-de-utilizacao')],
                ['Acessibilidade', 'link', $p('acessibilidade')],
            ]],
        ];

        foreach ($menus as $location => [$name, $title, $items]) {
            $menu = Menu::firstOrCreate(['location' => $location], ['name' => $name, 'title' => $title]);
            if ($menu->items()->exists()) {
                continue;
            }
            $this->createItems($menu, $items, null);
        }
    }

    private function createItems(Menu $menu, array $items, ?MenuItem $parent): void
    {
        foreach ($items as $i => $def) {
            [$label, $display, $link] = $def;
            $children = $def[3] ?? [];
            $mobile = $def[4] ?? null;
            $item = new MenuItem(array_merge([
                'parent_id' => $parent?->id,
                'label' => $label,
                'mobile_label' => $mobile,
                'display' => $display,
                'link_type' => 'none',
                'is_active' => true,
                'sort_order' => $i,
            ], $link));
            $item->menu_id = $menu->id;
            $item->save();
            if ($children) {
                $this->createItems($menu, $children, $item);
            }
        }
    }
}
