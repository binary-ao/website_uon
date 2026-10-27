<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Degree;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\News;
use App\Models\Notice;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Tag;
use App\Services\MediaService;
use App\Support\Blocks;
use App\Support\Settings;

/**
 * CONTEÚDO INSTITUCIONAL RECOLHIDO DO WEBSITE OFICIAL (https://uon.edu.ao).
 *
 * O website oficial bloqueia acessos automatizados (verificação anti-bot), pelo
 * que a informação foi obtida a partir das páginas oficiais indexadas pelos
 * motores de pesquisa (consulta de 22/10/2026). Cada dado indica a página de
 * origem. Ver docs/FONTES.md — a UON deve validar os textos antes de publicar.
 *
 * Só são carregados dados encontrados nessas páginas; o que não foi possível
 * confirmar fica vazio (o website mostra "Conteúdo em preparação").
 * As imagens são ilustrações (não há fotografias oficiais disponíveis).
 */
class OfficialContentSeeder extends DemoContentSeeder
{
    public const SRC = 'https://uon.edu.ao';

    public const ILLUSTRATION = 'Imagem ilustrativa — substituir por fotografia oficial';

    public function run(): void
    {
        $this->media = app(MediaService::class);

        $this->officialSettings();
        $units = $this->officialUnits();
        $this->officialCourses($units);
        $this->officialNews();
        $this->officialPages();
        $this->officialBanner();

        Partner::firstOrCreate(['name' => 'Okutala'], ['description' => 'Parceria para a saúde ocular dos funcionários (notícia de 10/05/2026)', 'is_active' => true, 'sort_order' => 0]);
        Notice::firstOrCreate(['text' => 'Candidaturas abertas — Exame de Acesso 2026/2027'], [
            'icon' => 'bi-megaphone', 'url' => 'https://www.candidaturas.sga-ed.info/uon', 'is_active' => true, 'sort_order' => 0,
        ]);

        Settings::flush();
        \App\Services\MenuRepository::flush();
        $this->command?->info('Conteúdo institucional (fonte: uon.edu.ao) carregado. Ver docs/FONTES.md.');
    }

    private function illustrative(Media $m): Media
    {
        $m->forceFill(['description' => self::ILLUSTRATION])->save();

        return $m;
    }

    // Fonte: /contact/, /sobre-a-uon/, /faqs/missao-visao-valores/, página inicial
    private function officialSettings(): void
    {
        Settings::set([
            'site_name' => 'Universidade 11 de Novembro',
            'site_short_name' => 'UON',
            'site_description' => 'A Universidade 11 de Novembro (UON) é uma instituição pública de ensino superior com sede em Cabinda, criada pelo Decreto Presidencial n.º 7/09, de 12 de Maio.',
            'contact_phone' => '(+244) 928 220 700',
            'contact_email' => 'geral@uon.edu.ao',
            'contact_address' => 'Rua Comendador Henrique Serrano, Cabinda, Angola',
            'contact_form_recipient' => 'geral@uon.edu.ao',
            'header_cta_label' => 'Candidaturas',
            'header_cta_url' => 'https://www.candidaturas.sga-ed.info/uon',
            'footer_about' => 'Formar profissionais qualificados, promover a investigação científica e a extensão universitária, contribuindo para o desenvolvimento técnico-científico e para a soberania nacional.',
            'seo_home_title' => 'UON — Universidade 11 de Novembro',
            'hero_eyebrow' => 'Universidade 11 de Novembro',
            'hero_title' => 'Formar profissionais qualificados',
            'hero_title_highlight' => 'em Cabinda e para Angola.',
            'hero_lead' => 'Ensino, investigação científica e extensão universitária ao serviço do desenvolvimento técnico-científico do país.',
            'about_eyebrow' => 'Sobre a UON',
            'about_title' => 'Universidade pública com sede em Cabinda',
            'about_text' => "A Universidade 11 de Novembro foi criada pelo Decreto Presidencial n.º 7/09, de 12 de Maio, no âmbito da reorganização da rede de instituições públicas de ensino superior, com sede em Cabinda.\n\nTem por missão formar profissionais qualificados, promover a investigação científica e a extensão universitária em diversas áreas, contribuindo para o desenvolvimento técnico-científico e para a soberania nacional.",
            'about_figure_number' => '2009',
            'about_figure_label' => 'Criação — Decreto Presidencial n.º 7/09, de 12 de Maio',
            'about_link_label' => 'Conheça a UON',
            'cta_title' => 'Exame de Acesso 2026/2027',
            'cta_text' => 'As candidaturas ao exame de acesso para o ano académico 2026/2027 estão abertas no Portal de Candidatura.',
            'cta_button_label' => 'Portal de Candidatura',
            'cta_button_url' => 'https://www.candidaturas.sga-ed.info/uon',
        ]);
    }

    // Fontes: /faculdade-de-direito/, /faculdade-de-medicina-2/, /instituto-politecnico-2/, notícia do concurso público
    private function officialUnits(): array
    {
        $defs = [
            'fd' => ['faculdade', 'Faculdade de Direito', 'FD', 'Ciências Jurídicas', 'Rua Comendador Henrique Serrano, Bairro Marien Ngouabi, Cidade de Cabinda, Cabinda-Angola', '(+244) 231 220 491', 'f.direito@uon.edu.ao',
                'A Faculdade de Direito da Universidade 11 de Novembro ministra a licenciatura em Direito e os mestrados em Ciências Jurídico-Forenses e em Ciências Jurídico-Políticas.'],
            'fe' => ['faculdade', 'Faculdade de Economia', 'FE', 'Economia, Gestão, Contabilidade e Auditoria', 'Cabinda, Angola', null, null,
                'A Faculdade de Economia da Universidade 11 de Novembro ministra a licenciatura em Gestão de Empresas e os mestrados em Contabilidade e Auditoria e em Economia e Gestão.'],
            'fm' => ['faculdade', 'Faculdade de Medicina', 'FM', 'Medicina Geral e Saúde Pública', 'Cabinda, Angola', null, 'f.medicina@uon.edu.ao',
                'A Faculdade de Medicina da Universidade 11 de Novembro ministra a licenciatura em Medicina Geral e o mestrado em Saúde Pública, contando com mais de 64 docentes.'],
            'isp' => ['instituto', 'Instituto Superior Politécnico de Cabinda', 'ISP-Cabinda', 'Enfermagem, Psicologia Clínica, Engenharia Florestal e Análises Clínicas', 'Rua das Mangueiras, Bairro 4 de Fevereiro, Cidade de Cabinda', '+244 972 329 758', 'ispcabinda@uon.edu.ao',
                'O Instituto Superior Politécnico de Cabinda (ISP-Cabinda) forma profissionais nas áreas de Enfermagem, Psicologia Clínica, Engenharia Florestal, Análises Clínicas e outras áreas técnicas.'],
        ];
        $out = [];
        $i = 0;
        foreach ($defs as $key => [$type, $name, $short, $summary, $location, $phone, $email, $desc]) {
            $u = OrganicUnit::firstOrNew(['slug' => \Illuminate\Support\Str::slug($name)]);
            $u->fill([
                'type' => $type, 'name' => $name, 'short_name' => $short, 'summary' => $summary,
                'description' => $this->html([$desc]), 'location' => $location, 'phone' => $phone, 'email' => $email,
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=12.1700%2C-5.5800%2C12.2200%2C-5.5300&layer=mapnik',
                'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'sort_order' => $i,
            ]);
            if (! $u->featured_media_id) {
                $u->featured_media_id = $this->illustrative($this->image($short.' · '.$name, ['dark', 'red', 'grey', 'red'][$i]))->id;
            }
            $u->save();
            $out[$key] = $u;
            $i++;
        }

        // Fonte: notícia "Segunda fase da construção do campus universitário do Caio" (Reitor da UON).
        $reitoria = OrganicUnit::firstOrNew(['slug' => 'reitoria']);
        $reitoria->fill(['type' => 'orgao', 'name' => 'Reitoria', 'summary' => 'Órgão executivo da Universidade', 'head_title' => 'Reitor', 'head_name' => 'Prof. Doutor Kianvu Tamo',
            'location' => 'Rua Comendador Henrique Serrano, Cabinda', 'phone' => '(+244) 928 220 700', 'email' => 'geral@uon.edu.ao',
            'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'sort_order' => 0])->save();

        return $out;
    }

    // Fontes: /cursos/ e páginas de cada curso; páginas das faculdades e do ISP-Cabinda
    private function officialCourses(array $u): void
    {
        $deg = Degree::pluck('id', 'slug');
        $courses = [
            ['Direito', 'licenciatura', 'fd', 'bi-bank', '5 anos', 'Diurno', 'Licenciatura em Direito (5 anos, regime diurno), com uma carga horária total de 6.000 horas.', null, null],
            ['Ciências Jurídico-Forenses', 'mestrado', 'fd', 'bi-journal-text', '2 anos', 'Diurno', 'O mestrado visa aprofundar o conhecimento e a prática jurídica aplicada ao contexto judicial e extrajudicial.', 'Licenciatura em Direito ou áreas afins.', ['Direito Processual Penal e Civil', 'Prova e Argumentação Jurídica', 'Medicina Legal', 'Criminologia', 'Arbitragem e Mediação']],
            ['Ciências Jurídico-Políticas', 'mestrado', 'fd', 'bi-bank2', null, null, 'Mestrado da Faculdade de Direito.', null, null],
            ['Gestão de Empresas', 'licenciatura', 'fe', 'bi-briefcase', '4 anos', null, 'Licenciatura em Gestão de Empresas (4 anos).', null, null],
            ['Contabilidade e Auditoria', 'mestrado', 'fe', 'bi-calculator', '2 anos', null, 'Mestrado orientado para a prática avançada de contabilidade e auditoria.', 'Licenciatura em Contabilidade, Auditoria, Gestão ou áreas afins.', ['Contabilidade avançada', 'Auditoria financeira', 'Controlo interno', 'Fiscalidade', 'Gestão de risco', 'Normas Internacionais de Relato Financeiro (IFRS)']],
            ['Economia e Gestão', 'mestrado', 'fe', 'bi-graph-up-arrow', '2 anos', null, 'Mestrado em análise económica e gestão empresarial.', 'Licenciatura em Economia, Gestão, Contabilidade ou áreas afins; selecção por avaliação curricular e entrevista.', null],
            ['Medicina Geral', 'licenciatura', 'fm', 'bi-heart-pulse', '6 anos', 'Diurno', 'Licenciatura em Medicina Geral (6 anos, regime diurno).', null, null],
            ['Saúde Pública', 'mestrado', 'fm', 'bi-hospital', '2 anos', null, 'Mestrado em Saúde Pública da Faculdade de Medicina.', null, null],
            ['Enfermagem', 'licenciatura', 'isp', 'bi-clipboard2-pulse', '4 anos', null, 'Licenciatura que prepara para o exercício de funções de enfermagem geral em unidades de saúde públicas, privadas e comunitárias.', null, null],
            ['Análises Clínicas', 'licenciatura', 'isp', 'bi-eyedropper', '4 anos', null, 'Licenciatura em Análises Clínicas (4 anos).', 'Preferencialmente, certificado do ensino secundário em Ciências Físicas e Biológicas; outros candidatos podem ser admitidos após avaliação.', null],
            ['Psicologia Clínica', 'licenciatura', 'isp', 'bi-person-heart', '4 anos', null, 'Licenciatura em Psicologia Clínica (4 anos).', null, null],
            ['Engenharia Florestal', 'licenciatura', 'isp', 'bi-tree', '5 anos', 'Diurno', 'Licenciatura em Engenharia Florestal (5 anos, regime diurno).', null, null],
            ['Enfermagem Médico-Cirúrgica', 'mestrado', 'isp', 'bi-bandaid', '2 anos', null, 'Mestrado em Enfermagem Médico-Cirúrgica (2 anos).', null, null],
        ];
        foreach ($courses as $i => [$name, $degree, $unit, $icon, $duration, $regime, $summary, $entry, $areas]) {
            $c = Course::firstOrNew(['slug' => \Illuminate\Support\Str::slug($name)]);
            $c->fill([
                'name' => $name, 'degree_id' => $deg[$degree], 'organic_unit_id' => $u[$unit]->id, 'icon' => $icon,
                'duration' => $duration, 'regime' => $regime, 'summary' => $summary,
                'presentation' => $this->html(array_filter([$summary, $areas ? ['h3', 'Áreas de estudo'] : null, $areas ? ['ul', $areas] : null])),
                'entry_profile' => $entry, 'contact_email' => $u[$unit]->email,
                'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'sort_order' => $i,
            ]);
            $c->save();
            $c->recordRevision('created');
        }
    }

    // Fonte: /noticias/ e páginas de cada notícia
    private function officialNews(): void
    {
        $inst = Category::where('type', 'news')->where('slug', 'institucional')->value('id');
        $inv = Category::where('type', 'news')->where('slug', 'investigacao')->value('id');
        $items = [
            ['news', 'Abertura do Concurso Público de Ingresso Externo e Interno', '2026-06-23 10:00', $inst, 'abertura-do-concurso-publico-de-ingresso-externo-e-interno',
                'A Universidade 11 de Novembro anunciou a abertura do concurso público de ingresso externo e interno, ao abrigo dos decretos presidenciais aplicáveis. O concurso inclui vagas para docentes, nomeadamente nas áreas de contabilidade e gestão da Faculdade de Economia.', ['Concurso público', 'Recrutamento']],
            ['news', 'África numa globalização vacilante — Jornada Científica «África o Nosso Continente», edição 2026', '2026-05-28 10:00', $inv, 'africa-numa-globalizacao-vacilante',
                'A Universidade 11 de Novembro realizou a Jornada Científica «África o Nosso Continente», edição 2026, em alusão ao Dia de África, celebrado a 25 de Maio. O encontro contou com seis comunicações, que sustentaram dois painéis de debate.', ['Jornada científica', 'Dia de África']],
            ['news', 'Segunda fase da construção do campus universitário do Caio fica concluída em Novembro de 2027', '2026-05-21 10:00', $inst, 'segunda-fase-da-construcao-do-campus-universitario-do-caio-que-teve-inicio-esta-quinta-feira-fica-concluida-em-novembro-de-2027',
                'Teve início a segunda fase da construção do campus universitário do Caio, em Cabinda, cuja conclusão está prevista para Novembro de 2027.', ['Campus do Caio', 'Infra-estruturas']],
            ['news', 'UON e Okutala em parceria pela saúde ocular dos funcionários', '2026-05-10 10:00', $inst, null,
                'A Universidade 11 de Novembro estabeleceu uma parceria com a Okutala para a promoção da saúde ocular dos seus funcionários.', ['Parcerias', 'Saúde']],
            ['announcement', 'Exame de Acesso 2026/2027', now()->format('Y-m-d H:i'), null, 'exame-de-acesso-2026-2027',
                'Estão abertas as candidaturas ao exame de acesso para o ano académico 2026/2027. As candidaturas são submetidas no Portal de Candidatura: https://www.candidaturas.sga-ed.info/uon', ['Candidaturas']],
        ];
        foreach ($items as [$type, $title, $date, $cat, $slug, $text, $tags]) {
            $n = News::firstOrNew(['slug' => \Illuminate\Support\Str::slug($title)]);
            $source = $slug ? self::SRC.'/'.$slug.'/' : self::SRC.'/noticias/';
            $n->fill([
                'type' => $type, 'title' => $title, 'excerpt' => \Illuminate\Support\Str::limit($text, 240),
                'body' => '<p>'.e($text).'</p><p><a href="'.e($source).'" target="_blank" rel="noopener">Ler a publicação original no website da UON</a></p>',
                'category_id' => $cat, 'author_name' => $type === 'announcement' ? 'Universidade 11 de Novembro' : 'UON',
                'is_featured' => false, 'status' => PublicationStatus::Published, 'published_at' => \Illuminate\Support\Carbon::parse($date),
            ]);
            $n->save();
            $n->tags()->sync(Tag::idsFromString(implode(',', $tags)));
            $n->recordRevision('created');
        }
    }

    // Fonte: /sobre-a-uon/, /faqs/missao-visao-valores/
    private function officialPages(): void
    {
        $this->setPage('instituicao', ['title' => 'Conheça a Universidade 11 de Novembro']);
        $this->setPage('instituicao/sobre', [
            'title' => 'Sobre a UON',
            'body' => $this->html([
                'A Universidade 11 de Novembro (UON) foi criada pelo Decreto Presidencial n.º 7/09, de 12 de Maio, que reorganizou a rede de instituições públicas de ensino superior, com sede em Cabinda e presença académica na província do Zaire.',
                'Pelo Decreto Presidencial n.º 285/20, de 29 de Outubro, que reorganiza a Rede de Instituições Públicas de Ensino Superior (RIPES), a Escola Superior Politécnica de M\'banza-Kongo foi separada da UON e tornou-se autónoma.',
                'A UON organiza-se em unidades orgânicas — a Faculdade de Direito, a Faculdade de Economia, a Faculdade de Medicina e o Instituto Superior Politécnico de Cabinda (ISP-Cabinda) —, que ministram cursos de licenciatura e de mestrado.',
            ]),
            'sidebar_title' => 'A UON em resumo',
            'sidebar_items' => "bi-calendar3 | Criada pelo Decreto Presidencial n.º 7/09, de 12 de Maio\nbi-geo-alt | Sede em Cabinda\nbi-building | 3 faculdades e 1 instituto superior politécnico\nbi-mortarboard | Licenciaturas e mestrados",
        ]);
        $this->setPage('instituicao/missao-visao-valores', ['blocks' => [
            ['type' => 'cards', 'data' => ['columns' => '3'], 'items' => [
                ['icon' => 'bi-bullseye', 'title' => 'Missão', 'text' => 'Formar profissionais qualificados, promover a investigação científica e a extensão universitária em diversas áreas, contribuindo para o desenvolvimento técnico-científico e para a soberania nacional.'],
                ['icon' => 'bi-binoculars', 'title' => 'Visão', 'text' => 'Ser reconhecida até 2030 como universidade de excelência no país e com prestígio internacional.'],
                ['icon' => 'bi-gem', 'title' => 'Valores', 'text' => 'Responsabilidade, honestidade, patriotismo, solidariedade, humanismo, laboriosidade, honradez e justiça.'],
            ]],
        ]]);
        $this->setPage('instituicao/historia', [
            'title' => 'A Nossa História',
            'blocks' => [['type' => 'timeline', 'data' => [], 'items' => [
                ['heading' => '2009 — Criação da Universidade', 'text' => 'A Universidade 11 de Novembro é criada pelo Decreto Presidencial n.º 7/09, de 12 de Maio, no âmbito da reorganização da rede de instituições públicas de ensino superior, com sede em Cabinda.'],
                ['heading' => '2020 — Reorganização da RIPES', 'text' => 'O Decreto Presidencial n.º 285/20, de 29 de Outubro, reorganiza a Rede de Instituições Públicas de Ensino Superior; a Escola Superior Politécnica de M\'banza-Kongo passa a ser autónoma.'],
                ['heading' => '2026 — Campus universitário do Caio', 'text' => 'Inicia-se a segunda fase da construção do campus universitário do Caio, com conclusão prevista para Novembro de 2027.'],
            ]]],
        ]);
    }

    // Banner: textos oficiais + ilustrações (sem fotografias oficiais disponíveis)
    private function officialBanner(): void
    {
        if (HeroSlide::where('is_demo', false)->exists()) {
            return;
        }
        $slides = [
            ['Universidade 11 de Novembro', 'Formar profissionais qualificados', 'em Cabinda e para Angola', 'Ensino, investigação científica e extensão universitária, contribuindo para o desenvolvimento técnico-científico e para a soberania nacional.', 'Conheça a UON', '/instituicao/sobre', 'Cursos', '/ensino/oferta-formativa', 'day'],
            ['Exame de Acesso 2026/2027', 'Candidaturas abertas', 'para o ano académico 2026/2027', 'As candidaturas ao exame de acesso são submetidas no Portal de Candidatura da UON.', 'Portal de Candidatura', 'https://www.candidaturas.sga-ed.info/uon', 'Oferta formativa', '/ensino/oferta-formativa', 'sunset'],
            ['Campus universitário do Caio', 'Segunda fase da construção', 'concluída em Novembro de 2027', 'Teve início a segunda fase da construção do campus universitário do Caio, em Cabinda.', 'Ler notícia', '/comunicacao/noticias/segunda-fase-da-construcao-do-campus-universitario-do-caio-fica-concluida-em-novembro-de-2027', 'Notícias', '/comunicacao/noticias', 'dusk'],
        ];
        foreach ($slides as $i => [$eyebrow, $title, $highlight, $text, $l1, $u1, $l2, $u2, $mood]) {
            (new HeroSlide([
                'eyebrow' => $eyebrow, 'title' => $title, 'title_highlight' => $highlight, 'text' => $text,
                'media_id' => $this->illustrative($this->campusImage($mood, $title))->id, 'image_position' => 'right',
                'primary_label' => $l1, 'primary_url' => $u1, 'secondary_label' => $l2, 'secondary_url' => $u2,
                'is_active' => true, 'sort_order' => $i,
            ]))->save();
        }
    }

    private function setPage(string $path, array $attrs): void
    {
        $page = Page::where('path', $path)->first();
        if (! $page) {
            return;
        }
        if (isset($attrs['blocks'])) {
            $attrs['blocks'] = Blocks::sanitize($attrs['blocks']);
        }
        $page->fill($attrs)->save();
        $page->recordRevision('updated');
    }
}
