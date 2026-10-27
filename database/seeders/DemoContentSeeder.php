<?php

namespace Database\Seeders;

use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Degree;
use App\Models\Document;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\News;
use App\Models\Notice;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Models\Partner;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Services\DocumentStorage;
use App\Services\MediaService;
use App\Support\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * CONTEÚDO FICTÍCIO DE DEMONSTRAÇÃO — NÃO É INFORMAÇÃO OFICIAL DA UON.
 *
 * Preenche todo o website (configurações, página inicial, páginas
 * institucionais, unidades orgânicas, cursos, notícias, eventos, documentos,
 * galeria, parceiros e avisos) com dados fictícios, para demonstração.
 *
 * - Registos de módulos ficam marcados com is_demo = 1.
 * - O estado original das páginas e configurações alteradas é guardado
 *   (site_settings.demo_state) e reposto na remoção.
 * - Remoção total:  php artisan uon:demo-content --remove
 */
class DemoContentSeeder extends Seeder
{
    public const MEDIA_MARK = 'Conteúdo de demonstração (não oficial)';

    public const STATE_KEY = 'demo_state';

    private array $state = ['pages' => [], 'settings' => []];

    protected MediaService $media;

    protected int $imageIndex = 0;

    public function run(): void
    {
        if (SiteSetting::where('key', self::STATE_KEY)->exists() || News::where('is_demo', true)->exists()) {
            $this->command?->warn('O conteúdo de demonstração já existe. Remova-o primeiro: php artisan uon:demo-content --remove');

            return;
        }

        $this->media = app(MediaService::class);

        $this->settings();
        $units = $this->units();
        $this->courses($units);
        $this->news();
        $this->events();
        $documents = $this->documents();
        $this->gallery();
        $this->partners();
        $this->notices();
        $this->heroSlides();
        $this->pages($documents);

        SiteSetting::updateOrCreate(['key' => self::STATE_KEY], ['group' => 'system', 'value' => json_encode($this->state, JSON_UNESCAPED_UNICODE)]);
        Settings::flush();
        \App\Services\MenuRepository::flush();

        $this->command?->info('Conteúdo fictício de demonstração criado. Remoção: php artisan uon:demo-content --remove');
    }

    // ------------------------------------------------------------------
    // Configurações globais (Header, Footer, contactos, página inicial)
    // ------------------------------------------------------------------
    private function settings(): void
    {
        $hero = $this->image('Campus Universitário', 'dark');
        $about = $this->image('Edifício da Reitoria', 'red');

        $values = [
            'site_tagline' => 'Conhecimento ao serviço do desenvolvimento.',
            'site_description' => 'A Universidade Onze de Novembro é uma instituição pública de ensino superior dedicada à formação de quadros, à investigação científica e à extensão universitária.',
            'contact_phone' => '+244 923 000 111',
            'contact_phone_alt' => '+244 222 000 222',
            'contact_email' => 'geral@uon.exemplo.ao',
            'contact_email_secondary' => 'academicos@uon.exemplo.ao',
            'contact_address' => 'Avenida da Universidade, Campus Universitário, Cabinda, Angola',
            'office_hours' => 'Segunda a sexta, 08h00 – 16h00',
            'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=12.1650%2C-5.5800%2C12.2350%2C-5.5300&layer=mapnik',
            'contact_form_recipient' => 'geral@uon.exemplo.ao',
            'social_facebook' => 'https://www.facebook.com/uon.exemplo',
            'social_linkedin' => 'https://www.linkedin.com/school/uon-exemplo',
            'social_instagram' => 'https://www.instagram.com/uon.exemplo',
            'social_youtube' => 'https://www.youtube.com/@uon.exemplo',
            'research_url' => 'https://investigacao.uon.exemplo.ao/',
            'research_projects_url' => 'https://investigacao.uon.exemplo.ao/projectos',
            'research_publications_url' => 'https://investigacao.uon.exemplo.ao/publicacoes',
            'research_journals_url' => 'https://revistas.uon.exemplo.ao/',
            'footer_about' => 'Formação de excelência, investigação científica e compromisso com o desenvolvimento da sociedade. Uma universidade pública dedicada ao conhecimento e à inovação.',
            'seo_home_title' => 'Universidade Onze de Novembro — Ensino, Investigação e Extensão',
            'hero_eyebrow' => 'Universidade Onze de Novembro',
            'hero_title' => 'Conhecimento ao serviço do',
            'hero_title_highlight' => 'desenvolvimento.',
            'hero_lead' => 'Uma universidade pública dedicada à formação, à investigação e à extensão — construindo, todos os dias, as competências de que Angola precisa.',
            'hero_media_id' => (string) $hero->id,
            'home_stats' => "+15 | Anos de História\n+20 | Cursos\n+6.000 | Estudantes\n+300 | Docentes",
            'about_title' => 'Formação de excelência ao serviço do desenvolvimento',
            'about_text' => "A Universidade Onze de Novembro é uma instituição de ensino superior vocacionada para a formação de quadros qualificados, a produção de conhecimento científico e a prestação de serviços à comunidade. Desde a sua criação, tem consolidado uma cultura académica exigente, aberta à inovação e comprometida com o desenvolvimento sustentável do país.\n\nOrganizada em faculdades, departamentos e centros de investigação, e apoiada por uma rede de parcerias nacionais e internacionais, a Universidade promove um ensino orientado para a prática, a ética profissional e o pensamento crítico.",
            'about_media_id' => (string) $about->id,
            'about_figure_number' => '2009',
            'about_figure_label' => 'Ano de criação da Universidade',
            'courses_intro' => 'Programas académicos estruturados para preparar profissionais capazes de responder aos desafios do presente e do futuro.',
            'research_text' => 'Descubra o trabalho científico desenvolvido pela nossa comunidade académica, disponível na plataforma dedicada de investigação e publicações.',
            'cta_title' => 'Pronto para dar o próximo passo?',
            'cta_text' => 'As candidaturas para o ano académico 2026/2027 estão abertas. Consulte a nossa oferta formativa e inicie hoje o seu percurso.',
            'cta_button_label' => 'Ver oferta formativa',
        ];

        foreach (array_keys($values) as $key) {
            $this->state['settings'][$key] = SiteSetting::where('key', $key)->value('value');
        }
        Settings::set($values);
    }

    // ------------------------------------------------------------------
    // Unidades orgânicas: 3 faculdades com departamentos + órgãos de gestão
    // ------------------------------------------------------------------
    private function units(): array
    {
        $faculties = [
            'fcsh' => [
                'Faculdade de Ciências Sociais e Humanas', 'FCSH', 'Direito, Economia, Gestão e Educação',
                'Decano', 'Prof. Doutor Manuel Kiala Bento', 'fcsh@uon.exemplo.ao', '+244 923 000 201', 'Edifício B, Campus Universitário',
                ['Direito' => 'Ciências Jurídicas', 'Economia e Gestão' => 'Economia, Gestão e Contabilidade', 'Ciências da Educação' => 'Formação de professores e supervisão pedagógica'],
            ],
            'fet' => [
                'Faculdade de Engenharia e Tecnologias', 'FET', 'Engenharias, Tecnologias e Ciências Exactas',
                'Decana', 'Prof.ª Doutora Teresa Nzuzi Lopes', 'fet@uon.exemplo.ao', '+244 923 000 202', 'Edifício C, Campus Universitário',
                ['Engenharia Informática' => 'Computação, software e sistemas de informação', 'Engenharia Civil' => 'Construção, estruturas e infra-estruturas', 'Matemática e Física' => 'Ciências exactas de base'],
            ],
            'fcs' => [
                'Faculdade de Ciências da Saúde', 'FCS', 'Enfermagem, Saúde Pública e Ciências Biomédicas',
                'Decano', 'Prof. Doutor Alberto Tati Mavungo', 'fcs@uon.exemplo.ao', '+244 923 000 203', 'Edifício D, Campus Universitário',
                ['Enfermagem' => 'Cuidados de enfermagem e prática clínica', 'Saúde Pública' => 'Epidemiologia, promoção da saúde e gestão sanitária'],
            ],
        ];
        $heads = ['Dr. João Mbuta', 'Dra. Ana Lelo Tchimpi', 'Dr. Carlos Bumba', 'Eng.º Paulo Massanga', 'Eng.ª Rosa Futi', 'Dr. Mateus Nsimba', 'Enf.ª Luísa Tomás', 'Dr. Domingos Pena'];

        $out = ['faculties' => [], 'departments' => []];
        $h = 0;
        $order = 0;
        foreach ($faculties as $key => [$name, $short, $summary, $headTitle, $headName, $email, $phone, $location, $depts]) {
            $f = $this->unit([
                'type' => 'faculdade', 'name' => $name, 'short_name' => $short, 'summary' => $summary,
                'description' => $this->html([
                    "A {$name} ({$short}) é uma das unidades orgânicas da Universidade Onze de Novembro, responsável pela formação graduada e pós-graduada nas áreas de ".mb_strtolower($summary).'.',
                    ['h2', 'Missão'],
                    'Formar profissionais competentes e eticamente responsáveis, desenvolver investigação relevante para a região e para o país e prestar serviços especializados à comunidade.',
                    ['h2', 'Organização'],
                    'A Faculdade organiza-se em departamentos científicos, coordenados por um Conselho de Faculdade e apoiados por uma secretaria académica própria, laboratórios e salas de estudo.',
                    ['ul', ['Ensino de licenciatura, mestrado e pós-graduação', 'Projectos de investigação e de extensão universitária', 'Estágios e cooperação com instituições públicas e privadas']],
                ]),
                'head_title' => $headTitle, 'head_name' => $headName, 'email' => $email, 'phone' => $phone.' / '.str_replace('923', '222', $phone), 'location' => $location.', Cabinda, Angola',
                'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox='.rawurlencode(sprintf('%.4f,%.4f,%.4f,%.4f', 12.18 + $order * 0.01, -5.57, 12.20 + $order * 0.01, -5.55)).'&layer=mapnik&marker='.rawurlencode(sprintf('%.4f,%.4f', -5.56, 12.19 + $order * 0.01)),
                'featured_media_id' => $this->image($short.' · '.$name, ['dark', 'red', 'grey'][$order % 3])->id,
                'sort_order' => $order++,
            ]);
            $out['faculties'][$key] = $f;
            $i = 0;
            foreach ($depts as $dname => $dsummary) {
                $out['departments'][$dname] = $this->unit([
                    'type' => 'departamento', 'parent_id' => $f->id, 'name' => 'Departamento de '.$dname, 'summary' => $dsummary,
                    'description' => $this->html(["O Departamento de {$dname} integra a {$name} e assegura o ensino e a investigação na área de ".mb_strtolower($dsummary).'.']),
                    'head_title' => 'Chefe de Departamento', 'head_name' => $heads[$h++ % count($heads)],
                    'email' => Str::slug($dname).'@uon.exemplo.ao', 'sort_order' => $i++,
                ]);
            }
        }

        $bodies = [
            ['Reitoria', 'Órgão executivo singular', 'Reitor', 'Prof. Doutor Joaquim Puati Simão', 'Dirige e representa a Universidade, assegurando a gestão académica, administrativa e financeira.'],
            ['Conselho Geral', 'Órgão máximo de definição estratégica', 'Presidente', 'Prof. Doutor Francisco Mavungo', 'Responsável pela orientação estratégica da Universidade e pela aprovação dos instrumentos de gestão.'],
            ['Senado Universitário', 'Órgão colegial de natureza académica', 'Presidente', 'Reitor da Universidade', 'Pronuncia-se sobre a política académica, a criação de cursos e os regulamentos de ensino.'],
            ['Conselho Científico', 'Órgão de coordenação científica', 'Presidente', 'Prof.ª Doutora Helena Sungo', 'Coordena a actividade científica e emite pareceres sobre planos de estudo e linhas de investigação.'],
            ['Conselho de Direcção', 'Órgão de apoio à gestão', 'Presidente', 'Reitor da Universidade', 'Apoia o Reitor na gestão corrente e na coordenação das unidades orgânicas e serviços.'],
            ['Provedoria do Estudante', 'Defesa dos direitos dos estudantes', 'Provedor', 'Dr. Ernesto Lumbo', 'Órgão independente que zela pelos direitos e interesses legítimos dos estudantes.'],
        ];
        foreach ($bodies as $i => [$name, $summary, $headTitle, $headName, $desc]) {
            $this->unit(['type' => 'orgao', 'name' => $name, 'summary' => $summary, 'head_title' => $headTitle, 'head_name' => $headName, 'description' => $this->html([$desc]), 'sort_order' => $i]);
        }

        return $out;
    }

    private function unit(array $attrs): OrganicUnit
    {
        $u = new OrganicUnit($attrs + ['status' => PublicationStatus::Published, 'published_at' => now()->subMonths(6)]);
        $u->is_demo = true;
        $u->save();

        return $u;
    }

    // ------------------------------------------------------------------
    // Cursos e planos curriculares
    // ------------------------------------------------------------------
    private function courses(array $units): void
    {
        $deg = Degree::pluck('id', 'slug');
        $d = $units['departments'];
        $courses = [
            ['Direito', 'licenciatura', 'Direito', 'bi-bank', '5 anos', ['Introdução ao Estudo do Direito', 'Direito Constitucional', 'Direito Civil I', 'Economia Política', 'Direito Administrativo', 'Direito Penal', 'Direito Comercial', 'Direito do Trabalho', 'Direito Internacional Público', 'Prática Jurídica'], 'Advocacia | Advogado(a) e consultor(a) jurídico(a)'],
            ['Economia', 'licenciatura', 'Economia e Gestão', 'bi-graph-up-arrow', '4 anos', ['Microeconomia', 'Matemática para Economistas', 'Contabilidade Geral', 'Macroeconomia', 'Estatística', 'Economia Monetária', 'Econometria', 'Finanças Públicas', 'Economia do Desenvolvimento', 'Seminário de Projecto'], 'Economia e finanças | Economista, analista financeiro(a)'],
            ['Gestão de Empresas', 'licenciatura', 'Economia e Gestão', 'bi-briefcase', '4 anos', ['Introdução à Gestão', 'Contabilidade Financeira', 'Marketing', 'Gestão de Recursos Humanos', 'Finanças Empresariais', 'Gestão de Operações', 'Empreendedorismo', 'Estratégia Empresarial', 'Sistemas de Informação de Gestão', 'Estágio'], 'Gestão | Gestor(a), consultor(a) empresarial'],
            ['Ensino da Matemática', 'licenciatura', 'Ciências da Educação', 'bi-easel', '4 anos', ['Psicologia da Educação', 'Cálculo I', 'Álgebra Linear', 'Didáctica da Matemática', 'Geometria', 'Estatística Aplicada', 'Metodologia de Investigação', 'Prática Pedagógica I', 'Prática Pedagógica II', 'Trabalho de Fim de Curso'], 'Ensino | Professor(a) do ensino secundário'],
            ['Engenharia Informática', 'licenciatura', 'Engenharia Informática', 'bi-cpu', '5 anos', ['Matemática I', 'Introdução à Programação', 'Física Geral', 'Estruturas de Dados', 'Bases de Dados', 'Redes de Computadores', 'Engenharia de Software', 'Sistemas Operativos', 'Inteligência Artificial', 'Projecto Final'], 'Desenvolvimento de software | Engenheiro(a) de software'],
            ['Engenharia Civil', 'licenciatura', 'Engenharia Civil', 'bi-cone-striped', '5 anos', ['Análise Matemática I', 'Desenho Técnico', 'Mecânica Aplicada', 'Resistência dos Materiais', 'Materiais de Construção', 'Hidráulica', 'Estruturas de Betão', 'Geotecnia', 'Vias de Comunicação', 'Gestão de Obras'], 'Construção | Engenheiro(a) civil, director(a) de obra'],
            ['Enfermagem', 'licenciatura', 'Enfermagem', 'bi-clipboard2-pulse', '4 anos', ['Anatomia e Fisiologia', 'Fundamentos de Enfermagem', 'Bioquímica', 'Farmacologia', 'Enfermagem Médico-Cirúrgica', 'Saúde Materno-Infantil', 'Enfermagem Comunitária', 'Saúde Mental', 'Ensino Clínico I', 'Ensino Clínico II'], 'Cuidados de saúde | Enfermeiro(a) hospitalar e comunitário(a)'],
            ['Análises Clínicas e Saúde Pública', 'licenciatura', 'Saúde Pública', 'bi-heart-pulse', '4 anos', ['Biologia Celular', 'Química Geral', 'Microbiologia', 'Hematologia', 'Parasitologia', 'Epidemiologia', 'Imunologia', 'Bioestatística', 'Saúde Ambiental', 'Estágio Laboratorial'], 'Laboratório | Técnico(a) de análises clínicas'],
            ['Gestão Pública', 'mestrado', 'Economia e Gestão', 'bi-building-gear', '2 anos', ['Teoria da Administração Pública', 'Políticas Públicas', 'Finanças Públicas Avançadas', 'Gestão Estratégica no Sector Público', 'Métodos de Investigação', 'Dissertação'], 'Administração pública | Quadro superior da administração'],
            ['Saúde Pública', 'mestrado', 'Saúde Pública', 'bi-hospital', '2 anos', ['Epidemiologia Avançada', 'Bioestatística Avançada', 'Promoção da Saúde', 'Gestão de Serviços de Saúde', 'Métodos de Investigação', 'Dissertação'], 'Saúde pública | Gestor(a) de programas de saúde'],
            ['Engenharia de Software', 'mestrado', 'Engenharia Informática', 'bi-code-slash', '2 anos', ['Arquitectura de Software', 'Engenharia de Requisitos', 'Qualidade e Testes de Software', 'Computação em Nuvem', 'Métodos de Investigação', 'Dissertação'], 'Tecnologias de informação | Arquitecto(a) de software'],
            ['Supervisão Pedagógica', 'pos-graduacao', 'Ciências da Educação', 'bi-person-video3', '1 ano', ['Supervisão e Avaliação', 'Gestão Escolar', 'Currículo e Inovação', 'Projecto de Intervenção'], 'Educação | Supervisor(a) e gestor(a) escolar'],
        ];

        foreach ($courses as $i => [$name, $degree, $dept, $icon, $duration, $ucs, $career]) {
            $isLic = $degree === 'licenciatura';
            $c = new Course([
                'name' => $name, 'degree_id' => $deg[$degree], 'organic_unit_id' => $d[$dept]->id ?? null, 'icon' => $icon,
                'duration' => $duration, 'modality' => 'Presencial', 'regime' => $isLic ? 'Laboral e pós-laboral' : 'Pós-laboral',
                'summary' => "Formação em {$name} orientada para a prática profissional, a investigação e o desenvolvimento da região.",
                'presentation' => $this->html([
                    "O curso de {$name} forma profissionais capazes de responder com rigor científico e sentido ético aos desafios da sua área. Combina componentes teóricas e práticas, incluindo laboratórios, projectos aplicados e estágio em contexto real de trabalho.",
                    'O plano de estudos foi concebido em articulação com entidades empregadoras e com as orientações nacionais para o ensino superior.',
                ]),
                'objectives' => $this->html([['ul', [
                    'Dotar os estudantes de competências sólidas na área de formação;',
                    'Desenvolver a capacidade de análise, concepção e resolução de problemas;',
                    'Promover a ética profissional e a responsabilidade social;',
                    $isLic ? 'Preparar para a continuidade de estudos ao nível de mestrado.' : 'Desenvolver competências de investigação avançada.',
                ]]]),
                'entry_profile' => $isLic ? 'Candidatos com o ensino médio concluído, aprovados no exame de acesso da Universidade.' : 'Licenciados em áreas afins, seleccionados por análise curricular e entrevista.',
                'exit_profile' => "Diplomado(a) em {$name} apto(a) a exercer funções técnicas e de gestão na sua área, a trabalhar em equipa e a liderar projectos.",
                'careers' => array_map(fn ($l) => array_combine(['title', 'text'], array_map('trim', explode('|', $l))), [$career, 'Investigação e ensino | Investigador(a) e docente', 'Consultoria | Consultor(a) independente']),
                'coordinator_name' => 'Coordenação do curso de '.$name,
                'contact_email' => Str::slug($name).'@uon.exemplo.ao',
                'featured_media_id' => $this->image($name, ['red', 'dark', 'grey'][$i % 3])->id,
                'status' => PublicationStatus::Published, 'published_at' => now()->subMonths(3), 'sort_order' => $i,
            ]);
            $c->is_demo = true;
            $c->save();

            // Plano curricular: 5 UC por semestre (as específicas do curso + UC transversais), com código.
            $years = max(1, (int) $duration);
            $generic = ['Língua Portuguesa', 'Inglês Técnico', 'Metodologia de Investigação Científica', 'Ética e Deontologia Profissional', 'Informática na Óptica do Utilizador', 'Estatística Aplicada', 'Empreendedorismo', 'Seminário Temático', 'Comunicação e Expressão', 'Direitos Humanos e Cidadania'];
            $prefix = strtoupper(collect(explode(' ', Str::ascii(preg_replace('/\b(de|da|do|e|em)\b/u', '', $name))))->filter()->map(fn ($w) => $w[0])->take(3)->implode(''));
            $specific = array_values($ucs);
            $order = 0;
            for ($y = 1; $y <= $years; $y++) {
                foreach ([1, 2] as $sem) {
                    for ($k = 1; $k <= 5; $k++) {
                        $uc = array_shift($specific) ?? $generic[($y * 7 + $sem * 3 + $k) % count($generic)].' '.str_repeat('I', min(3, $y));
                        $isLastYear = $y === $years;
                        $c->subjects()->create([
                            'year' => $y, 'semester' => $sem, 'code' => sprintf('%s%d%d%d', $prefix, $y, $sem, $k),
                            'name' => $uc, 'hours' => [60, 90, 75, 45, 60][$k - 1], 'credits' => [4, 6, 5, 3, 4][$k - 1],
                            'is_optional' => $isLastYear && $k === 5, 'sort_order' => $order++,
                        ]);
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------
    // Notícias e comunicados
    // ------------------------------------------------------------------
    private function news(): void
    {
        $cats = Category::where('type', 'news')->pluck('id', 'slug');
        $items = [
            ['Universidade abre oficialmente o ano académico 2026/2027', 'institucional', 'Cerimónia reuniu estudantes, docentes e parceiros no auditório principal do campus.', ['Ano académico', 'Reitoria']],
            ['Faculdade de Engenharia inaugura novo laboratório de informática', 'ensino', 'O espaço dispõe de 40 postos de trabalho e servirá as licenciaturas e o mestrado da área.', ['Laboratórios', 'Engenharia']],
            ['Investigadores apresentam estudo sobre a qualidade da água na província', 'investigacao', 'O trabalho, desenvolvido com parceiros locais, propõe medidas de monitorização contínua.', ['Investigação', 'Saúde Pública']],
            ['Estudantes de Enfermagem participam em campanha de vacinação comunitária', 'extensao', 'Mais de 600 pessoas foram abrangidas pela acção, realizada em três bairros.', ['Extensão', 'Saúde']],
            ['Universidade assina protocolo de cooperação com instituição portuguesa', 'institucional', 'O acordo prevê mobilidade de docentes e estudantes e projectos de investigação conjuntos.', ['Cooperação', 'Mobilidade']],
            ['Biblioteca alarga horário durante o período de avaliações', 'ensino', 'A biblioteca central passa a funcionar até às 21h00 nos dias úteis.', ['Biblioteca']],
            ['Seminário debate o papel do ensino superior no desenvolvimento local', 'institucional', 'O encontro reuniu académicos, autoridades locais e representantes do sector empresarial.', ['Seminário']],
            ['Mestrado em Gestão Pública recebe a primeira turma', 'ensino', 'O programa, em regime pós-laboral, conta com 35 mestrandos.', ['Mestrado', 'Gestão Pública']],
            ['Equipa de estudantes vence concurso nacional de programação', 'ensino', 'A equipa da licenciatura em Engenharia Informática conquistou o primeiro lugar.', ['Engenharia', 'Estudantes']],
            ['Jornadas científicas reúnem mais de 50 comunicações', 'investigacao', 'As jornadas anuais destacaram trabalhos de docentes e estudantes de pós-graduação.', ['Investigação', 'Jornadas']],
            ['Projecto de literacia digital forma 200 jovens', 'extensao', 'A iniciativa de extensão decorreu em parceria com escolas secundárias da província.', ['Extensão', 'Literacia digital']],
            ['Universidade recebe visita de delegação ministerial', 'institucional', 'A visita incluiu as novas instalações da Faculdade de Ciências da Saúde.', ['Institucional']],
        ];
        foreach ($items as $i => [$title, $cat, $excerpt, $tags]) {
            $n = new News([
                'type' => 'news', 'title' => $title, 'excerpt' => $excerpt,
                'body' => $this->html([
                    $excerpt.' A iniciativa insere-se na estratégia da Universidade de reforço da qualidade do ensino, da investigação e da ligação à comunidade.',
                    'Na ocasião, a direcção da Universidade sublinhou a importância do envolvimento de toda a comunidade académica e dos parceiros institucionais para o sucesso desta acção.',
                    ['blockquote', '"Esta é mais uma etapa no compromisso da Universidade com a excelência e com o desenvolvimento da nossa região", afirmou um responsável da instituição.'],
                    ['h2', 'Próximos passos'],
                    'Nos próximos meses serão realizadas novas actividades, cujas datas serão divulgadas oportunamente na agenda do website.',
                    ['ul', ['Divulgação dos resultados à comunidade', 'Avaliação conjunta com os parceiros', 'Planeamento da próxima edição']],
                ]),
                'category_id' => $cats[$cat] ?? null,
                'author_name' => 'Gabinete de Comunicação e Imagem',
                'featured_media_id' => $this->image(Str::limit($title, 48, '…'), ['red', 'dark', 'grey'][$i % 3])->id,
                'is_featured' => $i === 0,
                'status' => PublicationStatus::Published, 'published_at' => now()->subDays($i * 6 + 1)->setTime(10, 0),
            ]);
            $n->is_demo = true;
            $n->save();
            $n->tags()->sync(Tag::idsFromString(implode(',', $tags)));
        }

        $announcements = [
            'Calendário de matrículas e inscrições 2026/2027',
            'Abertura de candidaturas aos cursos de mestrado',
            'Encerramento dos serviços na quadra festiva',
            'Normas de acesso e utilização da biblioteca',
        ];
        foreach ($announcements as $i => $t) {
            $n = new News(['type' => 'announcement', 'title' => $t, 'excerpt' => 'Comunicado oficial da Reitoria.', 'author_name' => 'Reitoria',
                'body' => $this->html(['A Reitoria da Universidade Onze de Novembro informa toda a comunidade académica e o público em geral do seguinte:', ['ul', ['As datas e procedimentos encontram-se disponíveis nos serviços académicos.', 'Os interessados devem consultar regularmente o website.', 'Para mais informações, contacte a secretaria geral.']], 'Cabinda, '.now()->subDays($i * 9 + 2)->translatedFormat('d \d\e F \d\e Y').'.']),
                'status' => PublicationStatus::Published, 'published_at' => now()->subDays($i * 9 + 2)]);
            $n->is_demo = true;
            $n->save();
        }
    }

    // ------------------------------------------------------------------
    // Eventos
    // ------------------------------------------------------------------
    private function events(): void
    {
        $cats = Category::where('type', 'event')->pluck('id', 'slug');
        $events = [
            [5, 9, 3, 'Semana da Ciência e Inovação', 'Auditório Principal', 'cientifico', 'Entrada livre, sujeita a inscrição'],
            [12, 10, 0, 'Cerimónia de Abertura Solene do Ano Académico', 'Campus Universitário', 'institucional', null],
            [19, 8, 0, 'Feira de Emprego e Estágios', 'Pavilhão Multiusos', 'academico', 'Entrada livre'],
            [26, 14, 0, 'Provas Públicas de Mestrado', 'Sala de Actos da Reitoria', 'academico', null],
            [33, 15, 0, 'Conferência: Ensino Superior e Desenvolvimento Regional', 'Auditório Principal', 'cientifico', 'Inscrição gratuita'],
            [47, 9, 4, 'Semana Cultural Universitária', 'Campus Universitário', 'cultural', null],
            [-12, 9, 0, 'Workshop de Escrita Científica', 'Biblioteca Central', 'cientifico', null],
            [-30, 10, 0, 'Dia Aberto da Universidade', 'Campus Universitário', 'institucional', null],
        ];
        foreach ($events as $i => [$days, $hour, $length, $title, $place, $cat, $info]) {
            $start = now()->addDays($days)->setTime($hour, 0);
            $e = new Event([
                'title' => $title, 'location' => $place, 'registration_info' => $info,
                'excerpt' => "{$title}, organizado pela Universidade Onze de Novembro.",
                'body' => $this->html([
                    "A Universidade Onze de Novembro organiza o evento \"{$title}\", aberto a estudantes, docentes, investigadores e ao público em geral.",
                    'O programa inclui sessões de apresentação, debates e momentos de convívio entre a comunidade académica e os parceiros da Universidade.',
                    ['h2', 'Programa'],
                    ['ul', ['Sessão de abertura', 'Painéis temáticos', 'Apresentação de trabalhos', 'Encerramento']],
                ]),
                'starts_at' => $start, 'ends_at' => $length ? $start->copy()->addDays($length)->setTime(17, 0) : $start->copy()->addHours(4),
                'category_id' => $cats[$cat] ?? null,
                'registration_url' => $info && $days > 0 ? '/contactos' : null,
                'featured_media_id' => $this->image($title, ['dark', 'red', 'grey'][$i % 3])->id,
                'status' => PublicationStatus::Published, 'published_at' => now()->subDays(40),
            ]);
            $e->is_demo = true;
            $e->save();
        }
    }

    // ------------------------------------------------------------------
    // Documentos (PDF gerados localmente)
    // ------------------------------------------------------------------
    private function documents(): array
    {
        $storage = app(DocumentStorage::class);
        $cats = Category::where('type', 'document')->pluck('id', 'slug');
        $docs = [
            ['Estatutos da Universidade', 'documentos-institucionais', 'Estatutos que regem a organização e o funcionamento da Universidade.', 14],
            ['Plano Estratégico 2025–2030', 'documentos-institucionais', 'Objectivos e eixos estratégicos de desenvolvimento institucional.', 10],
            ['Relatório de Actividades 2025', 'documentos-institucionais', 'Síntese das actividades de ensino, investigação e extensão.', 7],
            ['Código de Ética e Conduta', 'documentos-institucionais', 'Princípios éticos aplicáveis à comunidade académica.', 12],
            ['Regulamento Académico Geral', 'regulamentos', 'Normas sobre acesso, matrículas, frequência e conclusão dos cursos.', 9],
            ['Regulamento de Avaliação', 'regulamentos', 'Regras de avaliação contínua, exames e épocas de recurso.', 9],
            ['Regulamento de Propinas e Emolumentos', 'regulamentos', 'Valores, prazos e procedimentos de pagamento.', 8],
            ['Requerimento Geral', 'formularios', 'Formulário para pedidos dirigidos aos serviços académicos.', 3],
            ['Pedido de Certificado de Habilitações', 'formularios', 'Formulário de pedido de certificados e declarações.', 3],
            ['Ficha de Matrícula', 'formularios', 'Ficha a preencher no acto da matrícula.', 2],
            ['Protocolo de Cooperação com Universidade Parceira', 'protocolos', 'Acordo de cooperação académica e científica.', 5],
            ['Protocolo com o Hospital Provincial', 'protocolos', 'Acordo para realização de ensinos clínicos e estágios.', 4],
        ];
        $out = [];
        foreach ($docs as $i => [$title, $cat, $desc, $monthsAgo]) {
            $doc = new Document(['title' => $title, 'description' => $desc, 'category_id' => $cats[$cat] ?? null,
                'document_date' => now()->subMonths($monthsAgo)->startOfMonth(), 'visibility' => 'public',
                'status' => PublicationStatus::Published, 'published_at' => now()->subMonths($monthsAgo)]);
            $doc->is_demo = true;
            $doc->save();
            $storage->addVersion($doc, $this->pdf($title));
            $out[$title] = $doc;
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // Galeria, multimédia, parceiros e avisos
    // ------------------------------------------------------------------
    private function gallery(): void
    {
        $photos = ['Cerimónia de Abertura', 'Colação de Grau', 'Semana da Ciência', 'Feira de Emprego', 'Campus Universitário', 'Biblioteca Central', 'Aula Prática de Enfermagem', 'Laboratório de Informática', 'Semana Cultural'];
        foreach ($photos as $i => $title) {
            $g = new GalleryItem(['type' => 'image', 'title' => $title, 'caption' => $title.' — '.now()->subMonths($i)->translatedFormat('F Y'),
                'media_id' => $this->image($title, ['dark', 'red', 'grey'][$i % 3])->id, 'status' => PublicationStatus::Published,
                'published_at' => now()->subDays($i * 10), 'sort_order' => $i]);
            $g->is_demo = true;
            $g->save();
        }
        // Vídeo de demonstração com licença aberta (Big Buck Bunny, Blender Foundation, CC BY 3.0).
        foreach (['Conheça a Universidade Onze de Novembro' => 'Vídeo institucional · 3min', 'Testemunhos de antigos estudantes' => 'Depoimentos · 5min', 'Semana da Ciência e Inovação' => 'Reportagem · 4min'] as $title => $label) {
            $g = new GalleryItem(['type' => 'video', 'title' => $title, 'caption' => 'Vídeo de demonstração', 'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'duration_label' => $label, 'status' => PublicationStatus::Published, 'published_at' => now()->subWeek()]);
            $g->is_demo = true;
            $g->save();
        }
    }

    private function partners(): void
    {
        $names = ['Ministério do Ensino Superior', 'Governo Provincial', 'Hospital Provincial', 'Universidade Parceira de Lisboa', 'Câmara de Comércio Local', 'Empresa Petrolífera Exemplo', 'Instituto Nacional de Estatística', 'Rede de Bibliotecas Universitárias'];
        foreach ($names as $i => $name) {
            $p = new Partner(['name' => $name, 'logo_media_id' => $this->logo($name, $i)->id, 'url' => 'https://www.exemplo.ao/', 'description' => 'Parceiro institucional (fictício)', 'is_active' => true, 'sort_order' => $i]);
            $p->is_demo = true;
            $p->save();
        }
    }

    private function notices(): void
    {
        $items = [
            ['Website em demonstração: os conteúdos apresentados são fictícios', 'bi-info-circle', null],
            ['Candidaturas 2026/2027 abertas até 30 de Novembro', 'bi-megaphone', '/ensino/oferta-formativa'],
            ['Calendário académico do 1.º semestre já disponível', 'bi-calendar-event', '/ensino/calendario-academico'],
        ];
        foreach ($items as $i => [$text, $icon, $url]) {
            $n = new Notice(['text' => $text, 'icon' => $icon, 'url' => $url, 'is_active' => true, 'sort_order' => $i]);
            $n->is_demo = true;
            $n->save();
        }
    }

    // ------------------------------------------------------------------
    // Banner da página inicial (carrossel)
    // ------------------------------------------------------------------
    private function heroSlides(): void
    {
        $slides = [
            ['Universidade Onze de Novembro', 'Formar profissionais', 'para uma Angola melhor', 'A Universidade Onze de Novembro é uma instituição de ensino superior pública, comprometida com a excelência académica, a investigação e o desenvolvimento nacional.', 'Conheça a UON', '/instituicao/sobre', 'Cursos', '/ensino/oferta-formativa', 'day'],
            ['Candidaturas 2026/2027', 'Candidaturas abertas', 'para o novo ano académico', 'Licenciaturas, mestrados e pós-graduações nas áreas das ciências sociais, engenharias, tecnologias e ciências da saúde.', 'Ver oferta formativa', '/ensino/oferta-formativa', 'Informações académicas', '/ensino/informacoes-academicas', 'sunset'],
            ['Investigação e extensão', 'Ciência e inovação', 'ao serviço da região', 'Projectos de investigação e de extensão universitária que aproximam o conhecimento produzido no campus das necessidades da comunidade.', 'Investigação', '/investigacao', 'Notícias', '/comunicacao/noticias', 'dusk'],
        ];
        foreach ($slides as $i => [$eyebrow, $title, $highlight, $text, $l1, $u1, $l2, $u2, $mood]) {
            $slide = new \App\Models\HeroSlide([
                'eyebrow' => $eyebrow, 'title' => $title, 'title_highlight' => $highlight, 'text' => $text,
                'media_id' => $this->campusImage($mood, $title)->id, 'image_position' => 'right',
                'primary_label' => $l1, 'primary_url' => $u1, 'secondary_label' => $l2, 'secondary_url' => $u2,
                'is_active' => true, 'sort_order' => $i,
            ]);
            $slide->is_demo = true;
            $slide->save();
        }
    }

    /**
     * Ilustração do campus para o banner (1920x820): céu, edifício branco com
     * torre vermelha e logótipo da UON, bandeiras e vegetação. Sem fotografias de terceiros.
     */
    protected function campusImage(string $mood, string $title): Media
    {
        $W = 1920;
        $H = 820;
        $im = imagecreatetruecolor($W, $H);
        imagealphablending($im, true);
        [$skyTop, $skyBottom] = match ($mood) {
            'sunset' => [[232, 120, 70], [250, 214, 160]],
            'dusk' => [[38, 44, 78], [150, 110, 130]],
            default => [[92, 160, 215], [214, 233, 245]],
        };
        for ($y = 0; $y < $H; $y++) {
            $t = min(1, $y / ($H * 0.75));
            $c = imagecolorallocate($im, (int) ($skyTop[0] + ($skyBottom[0] - $skyTop[0]) * $t), (int) ($skyTop[1] + ($skyBottom[1] - $skyTop[1]) * $t), (int) ($skyTop[2] + ($skyBottom[2] - $skyTop[2]) * $t));
            imageline($im, 0, $y, $W, $y, $c);
        }
        $night = $mood === 'dusk';
        $col = fn (int $r, int $g, int $b, int $a = 0) => imagecolorallocatealpha($im, $r, $g, $b, $a);
        foreach ([[1200, 120, 260], [1560, 90, 200], [900, 170, 180]] as [$cx, $cy, $w]) {
            imagefilledellipse($im, $cx, $cy, $w, 46, $col(255, 255, 255, $night ? 110 : 70));
            imagefilledellipse($im, $cx + 60, $cy - 16, (int) ($w * 0.6), 50, $col(255, 255, 255, $night ? 110 : 70));
        }
        $ground = 650;
        imagefilledrectangle($im, 0, $ground, $W, $H, $night ? $col(40, 70, 45) : $col(86, 140, 70));
        imagefilledpolygon($im, [1250, $ground, 1390, $ground, 1520, $H, 1060, $H], $col(205, 195, 180));
        $white = $night ? $col(205, 205, 210) : $col(245, 245, 242);
        $shade = $night ? $col(165, 165, 172) : $col(222, 222, 218);
        $glass = $night ? $col(250, 214, 120) : $col(70, 95, 115);
        $dark = $col(90, 96, 110);
        imagefilledrectangle($im, 760, 400, 1880, $ground, $white);
        imagefilledrectangle($im, 760, 390, 1880, 404, $shade);
        for ($row = 0; $row < 3; $row++) {
            for ($x = 790; $x < 1860; $x += 62) {
                if ($x > 1180 && $x < 1500) {
                    continue;
                }
                $y = 425 + $row * 72;
                imagefilledrectangle($im, $x, $y, $x + 40, $y + 46, ($night && (($x + $row) % 3)) ? $dark : $glass);
            }
        }
        imagefilledrectangle($im, 1200, 250, 1480, $ground, $col(196, 22, 28));
        imagefilledrectangle($im, 1190, 240, 1490, 262, $col(160, 14, 20));
        for ($y = 470; $y < $ground - 40; $y += 70) {
            imagefilledrectangle($im, 1230, $y, 1450, $y + 44, $glass);
        }
        $logoFile = public_path('assets/images/brand/logo-uon.png');
        if (is_file($logoFile) && ($logo = @imagecreatefrompng($logoFile))) {
            imagefilledrectangle($im, 1222, 285, 1458, 440, $col(255, 255, 255));
            $lw = 220;
            $lh = (int) (imagesy($logo) * $lw / imagesx($logo));
            imagecopyresampled($im, $logo, 1230, 300 + (int) ((125 - $lh) / 2), 0, 0, $lw, $lh, imagesx($logo), imagesy($logo));
            imagedestroy($logo);
        }
        imagefilledrectangle($im, 1290, $ground - 70, 1390, $ground, $col(60, 40, 35));
        $flags = [1040 => [[204, 0, 0], [20, 20, 20]], 1090 => [[230, 230, 230], [204, 0, 0]], 1140 => [[204, 0, 0], [20, 20, 20]]];
        foreach ($flags as $x => [$top, $bottom]) {
            imagefilledrectangle($im, $x, 380, $x + 5, $ground, $col(190, 190, 190));
            imagefilledrectangle($im, $x + 6, 384, $x + 66, 404, $col($top[0], $top[1], $top[2]));
            imagefilledrectangle($im, $x + 6, 404, $x + 66, 424, $col($bottom[0], $bottom[1], $bottom[2]));
        }
        $trunk = $col(105, 80, 55);
        $leaf = $night ? $col(30, 60, 38) : $col(40, 110, 55);
        foreach ([700, 960, 1640, 1830] as $i => $x) {
            $top = $ground - 220 - ($i % 2) * 40;
            imagesetthickness($im, 12);
            imageline($im, $x, $ground, $x + 14, $top, $trunk);
            imagesetthickness($im, 1);
            foreach ([-150, -110, -60, -20, 30, 70, 120, 160] as $a) {
                $rad = deg2rad($a);
                imagefilledpolygon($im, [$x + 14, $top, (int) ($x + 14 + cos($rad) * 120), (int) ($top + sin($rad) * 50 + 40), (int) ($x + 14 + cos($rad) * 90), (int) ($top + sin($rad) * 50 + 58)], $leaf);
            }
        }
        $bush = $night ? $col(35, 66, 40) : $col(55, 120, 60);
        for ($x = 760; $x < 1880; $x += 90) {
            imagefilledellipse($im, $x, $ground + 6, 110, 46, $bush);
        }
        $tmp = tempnam(sys_get_temp_dir(), 'uon').'.jpg';
        imagejpeg($im, $tmp, 86);
        imagedestroy($im);
        $media = $this->media->store(new UploadedFile($tmp, 'banner-'.Str::slug($title).'.jpg', 'image/jpeg', null, true),
            ['title' => 'Banner — '.$title, 'alt_text' => 'Ilustração do campus da Universidade Onze de Novembro', 'description' => self::MEDIA_MARK], ['image']);
        @unlink($tmp);

        return $media;
    }

    // ------------------------------------------------------------------
    // Páginas institucionais
    // ------------------------------------------------------------------
    private function pages(array $documents): void
    {
        $this->fillPage('instituicao/sobre', [
            'body' => $this->html([
                'A Universidade Onze de Novembro (UON) é uma instituição pública de ensino superior vocacionada para a formação de quadros qualificados nas áreas das ciências sociais e humanas, das engenharias e tecnologias e das ciências da saúde. Ao longo do seu percurso, tem procurado conjugar rigor académico, proximidade com o mercado de trabalho e um forte compromisso com a investigação aplicada aos desafios do desenvolvimento regional e nacional.',
                'A comunidade académica é composta por estudantes, docentes, investigadores e pessoal técnico-administrativo que partilham um mesmo propósito: contribuir para a formação de profissionais competentes, éticos e preparados para actuar num contexto em constante mudança.',
                'Para além da actividade lectiva, a Universidade mantém uma rede activa de cooperação com universidades, centros de investigação, empresas e instituições públicas, promovendo a mobilidade académica, a extensão universitária e a transferência de conhecimento para a sociedade.',
            ]),
            'sidebar_title' => 'A Universidade em números',
            'sidebar_items' => "bi-calendar3 | Criada em 2009\nbi-building | 3 faculdades\nbi-mortarboard | +20 cursos activos\nbi-people | +6.000 estudantes\nbi-person-badge | +300 docentes",
            'featured_media_id' => $this->image('Universidade Onze de Novembro', 'red')->id,
            'blocks' => [
                ['type' => 'profile_box', 'data' => ['title' => 'Áreas de actuação', 'text' => 'Ciências Sociais e Humanas · Engenharias e Tecnologias · Ciências da Saúde · Educação']],
                ['type' => 'cta', 'data' => ['title' => 'Quer saber mais sobre a nossa oferta formativa?', 'text' => 'Explore os nossos cursos de licenciatura, mestrado e pós-graduação.', 'button_label' => 'Ver oferta formativa', 'button_url' => '/ensino/oferta-formativa']],
            ],
        ]);

        $this->fillPage('instituicao/historia', [
            'body' => $this->html(['A história da Universidade Onze de Novembro acompanha o crescimento do ensino superior em Angola e a afirmação da região como pólo de formação e de conhecimento.']),
            'blocks' => [['type' => 'timeline', 'data' => ['title' => ''], 'items' => [
                ['heading' => '2009 — Criação', 'text' => 'A Universidade é criada no âmbito da reorganização da rede de instituições públicas de ensino superior, com sede em Cabinda.'],
                ['heading' => '2010 — Início da actividade lectiva', 'text' => 'Entram as primeiras turmas nos cursos de licenciatura em Direito, Economia e Enfermagem.'],
                ['heading' => '2014 — Primeiros diplomados', 'text' => 'Realiza-se a primeira cerimónia de colação de grau, com mais de duzentos licenciados.'],
                ['heading' => '2018 — Formação pós-graduada', 'text' => 'São lançados os primeiros programas de mestrado e pós-graduação.'],
                ['heading' => '2022 — Centro de Investigação', 'text' => 'É criado o Centro de Investigação, que reúne projectos, publicações e revistas científicas.'],
                ['heading' => '2026 — Actualidade', 'text' => 'A Universidade conta com três faculdades, mais de vinte cursos e uma rede de cooperação nacional e internacional.'],
            ]]],
        ]);

        $this->fillPage('instituicao/missao-visao-valores', ['blocks' => [
            ['type' => 'cards', 'data' => ['title' => '', 'intro' => '', 'columns' => '3'], 'items' => [
                ['icon' => 'bi-bullseye', 'title' => 'Missão', 'text' => 'Formar profissionais competentes e eticamente responsáveis, produzir conhecimento científico relevante e prestar serviço à comunidade, contribuindo para o desenvolvimento sustentável.', 'url' => ''],
                ['icon' => 'bi-binoculars', 'title' => 'Visão', 'text' => 'Ser uma universidade de referência, reconhecida pela qualidade da formação, pela relevância da investigação e pelo impacto positivo na sociedade.', 'url' => ''],
                ['icon' => 'bi-gem', 'title' => 'Valores', 'text' => 'Rigor académico, ética e integridade, inovação, inclusão, responsabilidade social e compromisso com a excelência.', 'url' => ''],
            ]],
            ['type' => 'accordion', 'data' => ['title' => 'Os nossos valores em detalhe'], 'items' => [
                ['title' => 'Rigor Académico', 'text' => 'Exigência científica e pedagógica em todos os programas formativos, avaliada de forma contínua e transparente.'],
                ['title' => 'Ética e Integridade', 'text' => 'Respeito pela verdade, pela honestidade intelectual e pelos direitos de todos os membros da comunidade.'],
                ['title' => 'Inovação', 'text' => 'Abertura a novas ideias, métodos e tecnologias ao serviço do ensino e da investigação.'],
                ['title' => 'Inclusão', 'text' => 'Igualdade de oportunidades no acesso e no sucesso académico.'],
                ['title' => 'Responsabilidade Social', 'text' => 'Compromisso com o desenvolvimento da região e do país através da extensão universitária.'],
            ]],
        ]]);

        $this->fillPage('instituicao/mensagem-direccao', ['body' => $this->html([
            'Caros estudantes, docentes, colaboradores e parceiros,',
            'É com grande satisfação que vos dou as boas-vindas ao website da Universidade Onze de Novembro. Este é um espaço de informação e de proximidade, que reflecte o dinamismo de uma comunidade académica empenhada na formação, na investigação e no serviço à sociedade.',
            'Os desafios do nosso tempo exigem uma universidade aberta, inovadora e comprometida com a qualidade. Temos investido na modernização das infra-estruturas, na qualificação do corpo docente e no reforço da cooperação nacional e internacional.',
            'Convido-vos a conhecer a nossa oferta formativa, os nossos projectos e as iniciativas que diariamente dão vida ao campus.',
            ['blockquote', '"Juntos, construímos o conhecimento que transforma o futuro."'],
            '<strong>Prof. Doutor Joaquim Puati Simão</strong><br>Reitor da Universidade Onze de Novembro',
        ])]);

        $this->fillPage('instituicao/estrutura-organizacional', [
            'body' => $this->html(['A Universidade organiza-se em órgãos de governo, unidades orgânicas de ensino e investigação e serviços de apoio.']),
            'blocks' => [['type' => 'org_chart', 'data' => ['title' => '', 'note' => 'Representação simplificada da estrutura organizacional.'], 'items' => [
                ['nodes' => 'Conselho Geral', 'highlight' => true],
                ['nodes' => 'Reitoria', 'highlight' => true],
                ['nodes' => 'Senado Universitário; Conselho Científico; Conselho de Direcção', 'highlight' => false],
                ['nodes' => 'Faculdade de Ciências Sociais e Humanas; Faculdade de Engenharia e Tecnologias; Faculdade de Ciências da Saúde', 'highlight' => false],
                ['nodes' => 'Serviços Académicos; Serviços Administrativos e Financeiros; Biblioteca Central; Centro de Investigação', 'highlight' => false],
            ]]],
        ]);

        $this->fillPage('instituicao/servicos', ['blocks' => [['type' => 'cards', 'data' => ['columns' => '3'], 'items' => [
            ['icon' => 'bi-mortarboard', 'title' => 'Serviços Académicos', 'text' => 'Matrículas, inscrições, certificados e diplomas.', 'url' => '/servicos/academicos'],
            ['icon' => 'bi-book', 'title' => 'Biblioteca Central', 'text' => 'Acervo bibliográfico, salas de estudo e recursos digitais.', 'url' => '/servicos/biblioteca'],
            ['icon' => 'bi-person-heart', 'title' => 'Apoio ao Estudante', 'text' => 'Acção social, orientação e apoio psicopedagógico.', 'url' => '/servicos/apoio-estudante'],
        ]]]]);

        $this->fillPage('ensino/calendario-academico', ['blocks' => [['type' => 'table', 'data' => [
            'title' => 'Ano académico 2026/2027', 'headers' => 'Período | Actividade | Datas',
            'rows' => "1.º Semestre | Início das aulas | 02 de Março de 2026\n1.º Semestre | Avaliações contínuas | Abril a Junho de 2026\n1.º Semestre | Exames | 06 a 24 de Julho de 2026\nPausa lectiva | Férias intercalares | 27 de Julho a 14 de Agosto de 2026\n2.º Semestre | Início das aulas | 17 de Agosto de 2026\n2.º Semestre | Exames | 30 de Novembro a 18 de Dezembro de 2026\nGeral | Época de recurso | 11 a 22 de Janeiro de 2027\nGeral | Candidaturas ao ano seguinte | Outubro a Novembro de 2026",
            'note' => 'As datas podem ser ajustadas por despacho da Reitoria.',
        ]]]]);

        $this->fillPage('ensino/informacoes-academicas', [
            'body' => $this->html(['Reunimos aqui a informação essencial para candidatos e estudantes. Para situações específicas, contacte os Serviços Académicos.']),
            'blocks' => [
                ['type' => 'accordion', 'data' => ['title' => 'Perguntas frequentes'], 'items' => [
                    ['title' => 'Como me candidato a um curso de licenciatura?', 'text' => 'As candidaturas são feitas nos Serviços Académicos, no período anunciado no calendário académico, mediante apresentação do certificado do ensino médio e do bilhete de identidade.'],
                    ['title' => 'Como funciona o exame de acesso?', 'text' => 'O exame de acesso avalia conhecimentos nas disciplinas nucleares de cada curso. Os resultados são publicados no website e na secretaria.'],
                    ['title' => 'Quais são os prazos de matrícula?', 'text' => 'Os candidatos admitidos devem formalizar a matrícula no prazo indicado no edital de resultados.'],
                    ['title' => 'Como é feita a avaliação?', 'text' => 'A avaliação combina provas contínuas, trabalhos e exames finais, conforme o Regulamento de Avaliação.'],
                    ['title' => 'Posso pedir equivalências?', 'text' => 'Sim. Os pedidos de equivalência são apresentados nos Serviços Académicos e analisados pelo Conselho Científico da Faculdade.'],
                ]],
                ['type' => 'documents', 'data' => ['title' => 'Regulamentos aplicáveis', 'category_id' => (string) Category::where('type', 'document')->where('slug', 'regulamentos')->value('id'), 'limit' => '5']],
            ],
        ]);

        $this->fillPage('investigacao', ['blocks' => array_merge(Page::where('path', 'investigacao')->first()?->blocks ?? [], [
            ['type' => 'cards', 'data' => ['title' => 'Linhas de investigação', 'intro' => 'Áreas prioritárias do Centro de Investigação da Universidade.', 'columns' => '4'], 'items' => [
                ['icon' => 'bi-droplet', 'title' => 'Ambiente e Recursos Naturais', 'text' => 'Água, florestas e gestão sustentável dos recursos.', 'url' => ''],
                ['icon' => 'bi-heart-pulse', 'title' => 'Saúde Pública', 'text' => 'Doenças tropicais, nutrição e serviços de saúde.', 'url' => ''],
                ['icon' => 'bi-cpu', 'title' => 'Tecnologias e Inovação', 'text' => 'Transformação digital e sistemas de informação.', 'url' => ''],
                ['icon' => 'bi-people', 'title' => 'Sociedade e Desenvolvimento', 'text' => 'Direito, economia, educação e governação local.', 'url' => ''],
            ]],
        ])]);

        $this->fillPage('extensao/projectos', [
            'body' => $this->html(['A extensão universitária aproxima a Universidade da comunidade, através de projectos que aplicam o conhecimento produzido no campus à resolução de problemas concretos.']),
            'blocks' => [['type' => 'directory', 'data' => ['columns' => '3'], 'items' => [
                ['title' => 'Literacia Digital para Jovens', 'role' => 'Faculdade de Engenharia e Tecnologias', 'text' => 'Formação em competências digitais básicas em escolas secundárias.', 'lines' => "bi-calendar3 | Desde 2023\nbi-people | +200 participantes"],
                ['title' => 'Saúde na Comunidade', 'role' => 'Faculdade de Ciências da Saúde', 'text' => 'Campanhas de rastreio, vacinação e educação para a saúde.', 'lines' => "bi-calendar3 | Desde 2021\nbi-geo-alt | 5 bairros"],
                ['title' => 'Clínica Jurídica', 'role' => 'Faculdade de Ciências Sociais e Humanas', 'text' => 'Apoio jurídico gratuito prestado por estudantes, sob supervisão docente.', 'lines' => "bi-calendar3 | Desde 2022\nbi-chat-square-text | +150 atendimentos/ano"],
            ]]],
        ]);

        $this->fillPage('extensao/mobilidade', [
            'body' => $this->html(['A Universidade promove a mobilidade de estudantes, docentes e pessoal técnico, no âmbito de acordos de cooperação com instituições nacionais e estrangeiras.']),
            'blocks' => [
                ['type' => 'cards', 'data' => ['columns' => '2'], 'items' => [
                    ['icon' => 'bi-airplane', 'title' => 'Mobilidade de estudantes', 'text' => 'Períodos de estudo de um semestre em universidades parceiras, com reconhecimento académico.', 'url' => ''],
                    ['icon' => 'bi-person-workspace', 'title' => 'Mobilidade de docentes', 'text' => 'Missões de ensino, formação e investigação em instituições parceiras.', 'url' => ''],
                ]],
                ['type' => 'cta', 'data' => ['title' => 'Interessado(a) num programa de mobilidade?', 'text' => 'Contacte o Gabinete de Cooperação para conhecer as oportunidades disponíveis.', 'button_label' => 'Contactar', 'button_url' => '/contactos']],
            ],
        ]);

        $serviceCards = [
            'servicos/academicos' => ['Os Serviços Académicos asseguram o atendimento aos candidatos e estudantes em todos os actos académicos.', [
                ['bi-pencil-square', 'Candidaturas', 'Recepção e tratamento das candidaturas.'], ['bi-card-checklist', 'Matrículas e inscrições', 'Matrícula inicial e inscrição anual.'],
                ['bi-file-earmark-text', 'Certificados', 'Emissão de certificados e declarações.'], ['bi-award', 'Diplomas', 'Emissão de diplomas e cartas de curso.'],
                ['bi-arrow-left-right', 'Equivalências', 'Pedidos de equivalência e transferência.'], ['bi-clock', 'Atendimento', 'Segunda a sexta, das 08h00 às 15h00.'],
            ]],
            'servicos/apoio-estudante' => ['O Gabinete de Apoio ao Estudante acompanha os estudantes ao longo do percurso académico.', [
                ['bi-cash-coin', 'Bolsas de estudo', 'Informação sobre bolsas internas e externas.'], ['bi-chat-heart', 'Apoio psicopedagógico', 'Acompanhamento e orientação individual.'],
                ['bi-house', 'Alojamento', 'Apoio na procura de alojamento estudantil.'], ['bi-trophy', 'Desporto e cultura', 'Actividades desportivas e culturais no campus.'],
            ]],
            'servicos/administrativos' => ['Os Serviços Administrativos e Financeiros asseguram a gestão de recursos da Universidade.', [
                ['bi-receipt', 'Tesouraria', 'Pagamento de propinas e emolumentos.'], ['bi-people', 'Recursos Humanos', 'Gestão do pessoal docente e não docente.'],
                ['bi-box-seam', 'Património', 'Gestão de instalações e equipamentos.'], ['bi-cart', 'Aquisições', 'Contratação pública e fornecedores.'],
            ]],
        ];
        foreach ($serviceCards as $path => [$intro, $cards]) {
            $this->fillPage($path, ['body' => $this->html([$intro]), 'blocks' => [['type' => 'cards', 'data' => ['columns' => '3'], 'items' => array_map(fn ($c) => ['icon' => $c[0], 'title' => $c[1], 'text' => $c[2], 'url' => ''], $cards)]]]);
        }

        $this->fillPage('servicos/biblioteca', [
            'layout' => 'sidebar',
            'body' => $this->html([
                'A Biblioteca Central da Universidade disponibiliza um acervo de monografias, periódicos e trabalhos académicos que apoia o ensino e a investigação.',
                'Os utilizadores têm acesso a salas de leitura, postos de consulta informática, rede sem fios e serviços de empréstimo domiciliário.',
                ['h2', 'Serviços disponíveis'],
                ['ul', ['Consulta presencial e empréstimo domiciliário', 'Pesquisa bibliográfica assistida', 'Acesso a bases de dados científicas', 'Formação de utilizadores']],
            ]),
            'sidebar_title' => 'Horário e contactos',
            'sidebar_items' => "bi-clock | Segunda a sexta, 08h00 – 21h00\nbi-clock-history | Sábado, 09h00 – 13h00\nbi-geo-alt | Edifício A, piso 0\nbi-envelope | biblioteca@uon.exemplo.ao",
            'featured_media_id' => $this->image('Biblioteca Central', 'grey')->id,
        ]);

        $legal = [
            'politica-de-privacidade' => ['A Universidade Onze de Novembro respeita a privacidade dos utilizadores deste website e trata os dados pessoais de acordo com a legislação angolana aplicável.', 'Os dados recolhidos através do formulário de contacto destinam-se exclusivamente a responder aos pedidos apresentados e não são cedidos a terceiros.', 'Os titulares dos dados podem solicitar o acesso, a rectificação ou a eliminação dos seus dados através do email geral@uon.exemplo.ao.'],
            'termos-de-utilizacao' => ['A utilização deste website implica a aceitação dos presentes termos.', 'Os conteúdos são disponibilizados para fins informativos e podem ser alterados sem aviso prévio. A reprodução de textos e imagens requer autorização prévia da Universidade.', 'A Universidade não se responsabiliza pelos conteúdos de websites externos para os quais existam ligações.'],
            'acessibilidade' => ['Este website foi desenvolvido seguindo boas práticas de acessibilidade (WCAG): estrutura semântica, navegação por teclado, contraste adequado e textos alternativos nas imagens.', 'Se encontrar alguma dificuldade de acesso a conteúdos, contacte-nos através da página de contactos, indicando a página e o problema encontrado.'],
        ];
        foreach ($legal as $path => $paras) {
            $this->fillPage($path, ['body' => $this->html($paras)]);
        }

        // Documentos associados a páginas (módulo "Documentos associados").
        if ($p = Page::where('path', 'servicos/academicos')->first()) {
            $p->documents()->syncWithoutDetaching([$documents['Requerimento Geral']->id => ['sort_order' => 0], $documents['Pedido de Certificado de Habilitações']->id => ['sort_order' => 1]]);
            $this->state['page_documents'][] = $p->id;
        }
    }

    /** Preenche uma página guardando o estado anterior (reposto na remoção). */
    private function fillPage(string $path, array $attrs): void
    {
        $page = Page::where('path', $path)->first();
        if (! $page) {
            return;
        }
        $fields = ['layout', 'body', 'blocks', 'sidebar_title', 'sidebar_items', 'featured_media_id'];
        if (! isset($this->state['pages'][$page->id])) {
            $snapshot = [];
            foreach ($fields as $f) {
                $snapshot[$f] = $page->getAttribute($f);
            }
            $this->state['pages'][$page->id] = $snapshot;
        }
        if (isset($attrs['blocks'])) {
            $attrs['blocks'] = \App\Support\Blocks::sanitize($attrs['blocks']);
        }
        $page->fill(array_intersect_key($attrs, array_flip($fields)));
        $page->save();
        $page->recordRevision('updated');
    }

    // ------------------------------------------------------------------
    // Utilitários: HTML, imagens ilustrativas e PDF
    // ------------------------------------------------------------------
    protected function html(array $parts): string
    {
        $out = '';
        foreach ($parts as $p) {
            if (is_array($p)) {
                [$tag, $content] = $p;
                $out .= $tag === 'ul'
                    ? '<ul>'.implode('', array_map(fn ($li) => '<li>'.e($li).'</li>', $content)).'</ul>'
                    : "<{$tag}>".e($content)."</{$tag}>";
            } else {
                $out .= str_contains($p, '<strong>') ? "<p>{$p}</p>" : '<p>'.e($p).'</p>';
            }
        }

        return $out;
    }

    /** Imagem ilustrativa com as cores e a tipografia da UON (sem fotografias de terceiros). */
    protected function image(string $caption, string $scheme): Media
    {
        $w = 1600;
        $h = 1000;
        $im = imagecreatetruecolor($w, $h);
        [$top, $bottom] = match ($scheme) {
            'red' => [[163, 0, 0], [40, 10, 10]],
            'grey' => [[74, 68, 68], [29, 27, 27]],
            default => [[29, 27, 27], [120, 0, 0]],
        };
        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $c = imagecolorallocate($im, (int) ($top[0] + ($bottom[0] - $top[0]) * $t), (int) ($top[1] + ($bottom[1] - $top[1]) * $t), (int) ($top[2] + ($bottom[2] - $top[2]) * $t));
            imageline($im, 0, $y, $w, $y, $c);
        }
        $this->overlayPattern($im, $w - 900 + (($this->imageIndex % 3) * -120), 160);
        $red = imagecolorallocate($im, 255, 56, 56);
        $yellow = imagecolorallocate($im, 243, 232, 0);
        $white = imagecolorallocate($im, 255, 255, 255);
        $muted = imagecolorallocatealpha($im, 255, 255, 255, 50);
        // Texto centrado: continua legível quando a imagem é recortada (object-fit: cover).
        $bold = public_path('assets/fonts/montserrat/Montserrat-Bold.ttf');
        $reg = public_path('assets/fonts/montserrat/Montserrat-Medium.ttf');
        $center = function (string $text, float $size, string $font, int $y, $color) use ($im, $w) {
            $box = imagettfbbox($size, 0, $font, $text);
            imagettftext($im, $size, 0, (int) (($w - ($box[2] - $box[0])) / 2), $y, $color, $font, $text);
        };
        $lines = array_slice(explode("\n", wordwrap($caption, 18, "\n", true)), 0, 3);
        $y = (int) (500 - (count($lines) - 1) * 34);
        imagefilledrectangle($im, (int) ($w / 2 - 50), $y - 110, (int) ($w / 2 + 30), $y - 102, $red);
        imagefilledellipse($im, (int) ($w / 2 + 50), $y - 106, 14, 14, $yellow);
        foreach ($lines as $line) {
            $center($line, 44, $bold, $y, $white);
            $y += 68;
        }
        $center('IMAGEM ILUSTRATIVA', 16, $reg, $y + 20, $muted);
        $this->imageIndex++;

        $tmp = tempnam(sys_get_temp_dir(), 'uon').'.jpg';
        imagejpeg($im, $tmp, 86);
        imagedestroy($im);
        $media = $this->media->store(new UploadedFile($tmp, Str::slug($caption).'.jpg', 'image/jpeg', null, true),
            ['title' => $caption, 'alt_text' => $caption.' (imagem ilustrativa)', 'description' => self::MEDIA_MARK], ['image']);
        @unlink($tmp);

        return $media;
    }

    protected ?array $patternPixels = null;

    /** Sobrepõe o padrão gráfico do template (branco, 18% de opacidade). Pixels calculados uma única vez. */
    protected function overlayPattern($im, int $x, int $y): void
    {
        if ($this->patternPixels === null) {
            $this->patternPixels = [];
            $file = public_path('assets/images/pattern/pattern-white.png');
            if (! is_file($file) || ! ($pat = @imagecreatefrompng($file))) {
                return;
            }
            $pw = 900;
            $ph = (int) (imagesy($pat) * $pw / imagesx($pat));
            $scaled = imagecreatetruecolor($pw, $ph);
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
            imagecopyresampled($scaled, $pat, 0, 0, 0, 0, $pw, $ph, imagesx($pat), imagesy($pat));
            for ($i = 0; $i < $pw; $i++) {
                for ($j = 0; $j < $ph; $j++) {
                    $alpha = (imagecolorat($scaled, $i, $j) >> 24) & 127;
                    if ($alpha < 120) {
                        $this->patternPixels[] = [$i, $j, (1 - $alpha / 127) * 0.18];
                    }
                }
            }
            imagedestroy($scaled);
            imagedestroy($pat);
        }
        $W = imagesx($im);
        $H = imagesy($im);
        foreach ($this->patternPixels as [$i, $j, $o]) {
            $dx = $x + $i;
            $dy = $y + $j;
            if ($dx < 0 || $dy < 0 || $dx >= $W || $dy >= $H) {
                continue;
            }
            $bg = imagecolorat($im, $dx, $dy);
            imagesetpixel($im, $dx, $dy, ((int) ((($bg >> 16) & 255) * (1 - $o) + 255 * $o) << 16) | ((int) ((($bg >> 8) & 255) * (1 - $o) + 255 * $o) << 8) | (int) (($bg & 255) * (1 - $o) + 255 * $o));
        }
    }

    /** Logótipo fictício de parceiro (texto sobre fundo branco). */
    protected function logo(string $name, int $i): Media
    {
        $im = imagecreatetruecolor(600, 240);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
        $colors = [[209, 0, 0], [29, 27, 27], [95, 90, 90]];
        [$r, $g, $b] = $colors[$i % 3];
        $col = imagecolorallocate($im, $r, $g, $b);
        $bold = public_path('assets/fonts/montserrat/Montserrat-Bold.ttf');
        imagefilledellipse($im, 80, 120, 90, 90, $col);
        imagettftext($im, 30, 0, 58, 134, imagecolorallocate($im, 255, 255, 255), $bold, mb_strtoupper(mb_substr($name, 0, 1)));
        $lines = explode("\n", wordwrap($name, 18, "\n", true));
        $y = 120 - (count($lines) - 1) * 18;
        foreach (array_slice($lines, 0, 3) as $line) {
            imagettftext($im, 22, 0, 150, $y + 10, $col, $bold, $line);
            $y += 36;
        }
        $tmp = tempnam(sys_get_temp_dir(), 'uon').'.png';
        imagepng($im, $tmp);
        imagedestroy($im);
        $media = $this->media->store(new UploadedFile($tmp, Str::slug($name).'.png', 'image/png', null, true),
            ['title' => 'Logótipo — '.$name, 'alt_text' => $name, 'description' => self::MEDIA_MARK], ['image']);
        @unlink($tmp);

        return $media;
    }

    protected function pdf(string $title): UploadedFile
    {
        $ascii = fn ($s) => str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s));
        $lines = [
            'BT /F2 20 Tf 72 720 Td ('.$ascii('Universidade Onze de Novembro').') Tj ET',
            'BT /F2 16 Tf 72 680 Td ('.$ascii($title).') Tj ET',
            'BT /F1 11 Tf 72 640 Td ('.$ascii('Documento ficticio de demonstracao - nao oficial.').') Tj ET',
            'BT /F1 11 Tf 72 620 Td ('.$ascii('Gerado automaticamente para testar o modulo de documentos do CMS.').') Tj ET',
        ];
        $stream = implode("\n", $lines);
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n$stream\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n$obj\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        $tmp = tempnam(sys_get_temp_dir(), 'uon').'.pdf';
        file_put_contents($tmp, $pdf);

        return new UploadedFile($tmp, Str::slug($title).'.pdf', 'application/pdf', null, true);
    }
}
