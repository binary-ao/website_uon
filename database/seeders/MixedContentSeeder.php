<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\Notice;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Services\MediaService;
use App\Support\Blocks;
use App\Support\Settings;
use Illuminate\Support\Str;

/**
 * Conteúdo misto: OFICIAL onde o website uon.edu.ao tem informação;
 * EXEMPLO (fictício) onde não tem.
 *
 * Regras:
 *  - Listas que o site oficial publica (unidades orgânicas, cursos, notícias,
 *    banner) → apenas os itens oficiais; os campos em falta nesses itens
 *    (responsáveis, contactos, planos curriculares, objectivos…) são
 *    completados com dados de exemplo, assinalados com "(exemplo)".
 *  - O que o site oficial não publica (eventos, documentos, galeria, vídeos,
 *    parceiros, departamentos, órgãos de gestão além da Reitoria, números,
 *    redes sociais, plataformas de investigação, páginas sem texto oficial)
 *    → dados de exemplo (is_demo = 1, selo "Exemplo").
 *  - Exemplos que contradizem dados oficiais (faculdades/cursos inventados,
 *    datas históricas, valores) não são usados.
 *
 * Remoção dos exemplos: php artisan uon:official-content (repõe só o oficial).
 */
class MixedContentSeeder extends DemoContentSeeder
{
    private const EX = ' (exemplo)';

    /** Slugs dos cursos publicados em uon.edu.ao (ver OfficialContentSeeder::officialCourses). */
    public const OFFICIAL_COURSES = [
        'direito', 'ciencias-juridico-forenses', 'ciencias-juridico-politicas', 'gestao-de-empresas', 'contabilidade-e-auditoria',
        'economia-e-gestao', 'medicina-geral', 'saude-publica', 'enfermagem', 'analises-clinicas', 'psicologia-clinica',
        'engenharia-florestal', 'enfermagem-medico-cirurgica',
    ];

    private array $fills = [];

    public function run(): void
    {
        $this->call(DemoContentSeeder::class);
        $this->call(OfficialContentSeeder::class);
        $this->media = app(MediaService::class);

        $units = $this->reconcileUnits();
        $this->completeCourses();
        $this->reconcileNewsAndBanner();
        $this->reconcilePages();
        $this->reconcileSettings();
        $this->officialName();

        // Regista os campos completados com exemplos nos registos oficiais (para a remoção).
        $row = SiteSetting::where('key', self::STATE_KEY)->first();
        $state = $row ? (json_decode($row->value, true) ?: []) : [];
        $state['official_fills'] = $this->fills;
        SiteSetting::updateOrCreate(['key' => self::STATE_KEY], ['group' => 'system', 'value' => json_encode($state, JSON_UNESCAPED_UNICODE)]);

        Settings::flush();
        \App\Services\MenuRepository::flush();
        $this->command?->info('Conteúdo misto aplicado: oficial (uon.edu.ao) + exemplos onde o site não tem informação.');
    }

    private function fill($model, array $attrs): void
    {
        $fields = [];
        foreach ($attrs as $k => $v) {
            if (blank($model->getAttribute($k))) {
                $model->setAttribute($k, $v);
                $fields[] = $k;
            }
        }
        if ($fields) {
            $model->save();
            $this->fills[] = ['model' => get_class($model), 'id' => $model->id, 'fields' => $fields];
        }
    }

    // --------------------------------------------------------------
    // Unidades: oficiais + departamentos e órgãos de exemplo
    // --------------------------------------------------------------
    private function reconcileUnits(): array
    {
        $o = OrganicUnit::whereIn('slug', ['faculdade-de-direito', 'faculdade-de-economia', 'faculdade-de-medicina', 'instituto-superior-politecnico-de-cabinda'])->get()->keyBy('slug');
        $fd = $o['faculdade-de-direito'];
        $fe = $o['faculdade-de-economia'];
        $fm = $o['faculdade-de-medicina'];
        $isp = $o['instituto-superior-politecnico-de-cabinda'];

        // Departamentos de exemplo que correspondem a unidades oficiais passam para essas unidades.
        $map = ['Departamento de Direito' => $fd, 'Departamento de Economia e Gestão' => $fe, 'Departamento de Saúde Pública' => $fm, 'Departamento de Enfermagem' => $isp];
        foreach (OrganicUnit::where('is_demo', true)->where('type', 'departamento')->get() as $dept) {
            if (isset($map[$dept->name])) {
                $dept->parent_id = $map[$dept->name]->id;
                $dept->save();
            } else {
                $dept->forceDelete();
            }
        }

        // O seeder oficial reaproveita registos de exemplo com o mesmo slug: passam a oficiais,
        // sem os campos de exemplo (voltam a ser completados abaixo, assinalados).
        OrganicUnit::where('slug', 'reitoria')->update(['is_demo' => false, 'description' => null]);
        foreach (Course::where('is_demo', true)->whereIn('slug', self::OFFICIAL_COURSES)->get() as $c) {
            $c->subjects()->delete();
            $c->forceFill([
                'is_demo' => false, 'objectives' => null, 'exit_profile' => null, 'careers' => null, 'modality' => null,
                'entry_profile' => null, 'coordinator_name' => null, 'featured_media_id' => null, 'curriculum_note' => null,
            ])->save();
        }

        // Cursos e faculdades de exemplo não correspondem à oferta oficial → removidos.
        foreach (Course::withTrashed()->where('is_demo', true)->get() as $c) {
            $c->revisions()->delete();
            $c->forceDelete();
        }
        foreach (OrganicUnit::where('is_demo', true)->where('type', 'faculdade')->get() as $f) {
            OrganicUnit::where('parent_id', $f->id)->update(['parent_id' => null]);
            $f->forceDelete();
        }

        // Campos que o site oficial não indica → exemplo.
        $exampleText = fn (string $name) => $this->html([
            ['h2', 'Missão'],
            'Formar profissionais competentes e eticamente responsáveis, desenvolver investigação relevante para a região e para o país e prestar serviços especializados à comunidade.',
            ['h2', 'Organização'],
            'A unidade organiza-se em departamentos científicos, coordenados por um conselho próprio e apoiados por uma secretaria académica, laboratórios e salas de estudo.',
        ]).'<p><em>Texto complementar de exemplo, a substituir pelo texto oficial da '.e($name).'.</em></p>';

        $heads = [
            [$fd, 'Decano', 'Prof. Doutor Manuel Kiala Bento', null, null],
            [$fe, 'Decana', 'Prof.ª Doutora Teresa Nzuzi Lopes', 'fe@uon.exemplo.ao', '+244 923 000 202'],
            [$fm, 'Decano', 'Prof. Doutor Alberto Tati Mavungo', null, '+244 923 000 203'],
            [$isp, 'Director-Geral', 'Prof. Doutor Domingos Pena', null, null],
        ];
        foreach ($heads as [$unit, $title, $name, $email, $phone]) {
            $this->fill($unit, array_filter([
                'head_title' => $title, 'head_name' => $name.self::EX,
                'email' => $email, 'phone' => $phone ? $phone.self::EX : null,
            ]));
            $unit->refresh();
            if (! str_contains((string) $unit->description, 'Texto complementar de exemplo')) {
                $this->fills[] = ['model' => OrganicUnit::class, 'id' => $unit->id, 'fields' => [], 'description' => $unit->description];
                $unit->description = $unit->description.$exampleText($unit->name);
                $unit->save();
            }
        }

        return $o->all();
    }

    // --------------------------------------------------------------
    // Cursos oficiais: completar campos em falta com exemplos
    // --------------------------------------------------------------
    private function completeCourses(): void
    {
        $ucs = [
            'Direito' => ['Introdução ao Estudo do Direito', 'Direito Constitucional', 'Direito Civil I', 'Economia Política', 'Direito Administrativo', 'Direito Penal', 'Direito Comercial', 'Direito do Trabalho', 'Direito Internacional Público', 'Prática Jurídica'],
            'Gestão de Empresas' => ['Introdução à Gestão', 'Contabilidade Financeira', 'Marketing', 'Gestão de Recursos Humanos', 'Finanças Empresariais', 'Gestão de Operações', 'Empreendedorismo', 'Estratégia Empresarial', 'Sistemas de Informação de Gestão', 'Estágio'],
            'Medicina Geral' => ['Anatomia Humana I', 'Histologia', 'Bioquímica Médica', 'Fisiologia', 'Microbiologia', 'Farmacologia', 'Semiologia Médica', 'Patologia Geral', 'Medicina Interna', 'Cirurgia Geral', 'Pediatria', 'Ginecologia e Obstetrícia'],
            'Enfermagem' => ['Anatomia e Fisiologia', 'Fundamentos de Enfermagem', 'Bioquímica', 'Farmacologia', 'Enfermagem Médico-Cirúrgica', 'Saúde Materno-Infantil', 'Enfermagem Comunitária', 'Saúde Mental', 'Ensino Clínico I', 'Ensino Clínico II'],
            'Análises Clínicas' => ['Biologia Celular', 'Química Geral', 'Microbiologia', 'Hematologia', 'Parasitologia', 'Imunologia', 'Bioquímica Clínica', 'Bioestatística', 'Controlo de Qualidade Laboratorial', 'Estágio Laboratorial'],
            'Psicologia Clínica' => ['Introdução à Psicologia', 'Psicologia do Desenvolvimento', 'Neuropsicologia', 'Psicopatologia', 'Avaliação Psicológica', 'Psicoterapias', 'Psicologia da Saúde', 'Ética e Deontologia em Psicologia', 'Estágio Clínico I', 'Estágio Clínico II'],
            'Engenharia Florestal' => ['Botânica Florestal', 'Matemática I', 'Química Geral', 'Dendrologia', 'Solos Florestais', 'Silvicultura', 'Inventário Florestal', 'Ordenamento Florestal', 'Tecnologia da Madeira', 'Gestão de Recursos Naturais'],
        ];
        $mastersUcs = ['Metodologia de Investigação Científica', 'Seminário de Investigação', 'Estatística Avançada', 'Ética e Deontologia', 'Tópicos Avançados I', 'Tópicos Avançados II', 'Projecto de Dissertação', 'Dissertação'];
        $generic = ['Língua Portuguesa', 'Inglês Técnico', 'Metodologia de Investigação Científica', 'Ética e Deontologia Profissional', 'Informática na Óptica do Utilizador', 'Estatística Aplicada', 'Empreendedorismo', 'Seminário Temático', 'Comunicação e Expressão', 'Direitos Humanos e Cidadania'];

        foreach (Course::with('degree')->where('is_demo', false)->get() as $c) {
            $isLic = $c->degree?->slug === 'licenciatura';
            $this->fill($c, [
                'duration' => $isLic ? null : '2 anos'.self::EX,
                'modality' => 'Presencial'.self::EX,
                'objectives' => $this->html([['ul', [
                    'Dotar os estudantes de competências sólidas na área de formação;',
                    'Desenvolver a capacidade de análise, concepção e resolução de problemas;',
                    'Promover a ética profissional e a responsabilidade social;',
                    $isLic ? 'Preparar para a continuidade de estudos ao nível de mestrado.' : 'Desenvolver competências de investigação avançada.',
                ]]]).'<p><em>Objectivos de exemplo, a substituir pelos objectivos oficiais.</em></p>',
                'entry_profile' => ($isLic ? 'Candidatos com o ensino médio concluído, aprovados no exame de acesso da Universidade.' : 'Licenciados em áreas afins, seleccionados por análise curricular e entrevista.').self::EX,
                'exit_profile' => "Diplomado(a) em {$c->name} apto(a) a exercer funções técnicas e de gestão na sua área, a trabalhar em equipa e a liderar projectos.".self::EX,
                'careers' => [['title' => 'Exercício profissional (exemplo)', 'text' => 'Instituições públicas e privadas'], ['title' => 'Investigação e ensino (exemplo)', 'text' => 'Investigador(a) e docente'], ['title' => 'Consultoria (exemplo)', 'text' => 'Consultor(a) independente']],
                'featured_media_id' => $this->illustrativeMedia($c->name, $c->id),
            ]);

            if ($c->subjects()->exists()) {
                continue;
            }
            $years = max(1, (int) ($c->duration ?: '2'));
            $specific = $isLic ? ($ucs[$c->name] ?? []) : $mastersUcs;
            $prefix = strtoupper(collect(explode(' ', Str::ascii(preg_replace('/\b(de|da|do|e|em)\b/u', '', $c->name))))->filter()->map(fn ($w) => $w[0])->take(3)->implode(''));
            $order = 0;
            for ($y = 1; $y <= $years; $y++) {
                foreach ([1, 2] as $sem) {
                    for ($k = 1; $k <= ($isLic ? 5 : 4); $k++) {
                        $uc = array_shift($specific) ?? $generic[($y * 7 + $sem * 3 + $k) % count($generic)].' '.str_repeat('I', min(3, $y));
                        $c->subjects()->create([
                            'year' => $y, 'semester' => $sem, 'code' => sprintf('%s%d%d%d', $prefix, $y, $sem, $k), 'name' => $uc,
                            'hours' => [60, 90, 75, 45, 60][$k - 1], 'credits' => [4, 6, 5, 3, 4][$k - 1],
                            'is_optional' => $y === $years && $k === ($isLic ? 5 : 4) && $isLic, 'sort_order' => $order++,
                        ]);
                    }
                }
            }
            $c->forceFill(['curriculum_note' => 'Plano curricular de exemplo (ilustrativo), a substituir pelo plano oficial do curso.'])->save();
            $this->fills[] = ['model' => Course::class, 'id' => $c->id, 'fields' => ['curriculum_note'], 'subjects' => true];
        }
    }

    private function illustrativeMedia(string $name, int $i): int
    {
        $m = $this->image($name, ['red', 'dark', 'grey'][$i % 3]);
        $m->forceFill(['description' => self::MEDIA_MARK])->save();

        return $m->id;
    }

    // --------------------------------------------------------------
    // Notícias, avisos e banner: oficiais
    // --------------------------------------------------------------
    private function reconcileNewsAndBanner(): void
    {
        foreach (News::withTrashed()->where('is_demo', true)->get() as $n) {
            $n->tags()->detach();
            $n->documents()->detach();
            $n->revisions()->delete();
            $n->forceDelete();
        }
        HeroSlide::where('is_demo', true)->delete();
        Notice::where('is_demo', true)->where('text', 'like', 'Candidaturas 2026/2027%')->delete();
        Notice::where('is_demo', true)->where('text', 'like', 'Website em demonstração%')
            ->update(['text' => 'Alguns conteúdos deste website são exemplos ilustrativos e serão substituídos pelos conteúdos oficiais', 'sort_order' => 5]);
    }

    // --------------------------------------------------------------
    // Páginas: oficiais quando existem; exemplos assinalados nas restantes
    // --------------------------------------------------------------
    private function reconcilePages(): void
    {
        $note = '<p><span class="demo-badge">Exemplo</span> <em>Conteúdo ilustrativo, a substituir pelo texto oficial da UON.</em></p>';

        if ($p = Page::where('path', 'instituicao/sobre')->first()) {
            $p->blocks = Blocks::sanitize([
                ['type' => 'profile_box', 'data' => ['title' => 'Unidades orgânicas', 'text' => 'Faculdade de Direito · Faculdade de Economia · Faculdade de Medicina · Instituto Superior Politécnico de Cabinda']],
                ['type' => 'cta', 'data' => ['title' => 'Conheça a nossa oferta formativa', 'text' => 'Licenciaturas e mestrados nas áreas do Direito, Economia e Gestão, Medicina, Saúde, Psicologia e Engenharia Florestal.', 'button_label' => 'Ver oferta formativa', 'button_url' => '/ensino/oferta-formativa']],
            ]);
            $p->save();
        }

        if ($p = Page::where('path', 'instituicao/mensagem-direccao')->first()) {
            $body = str_replace(['Prof. Doutor Joaquim Puati Simão', 'Reitor da Universidade Onze de Novembro', 'Universidade Onze de Novembro'], ['Reitoria', 'Universidade 11 de Novembro', 'Universidade 11 de Novembro'], (string) $p->body);
            $p->body = $note.$body;
            $p->save();
        }

        if ($p = Page::where('path', 'instituicao/estrutura-organizacional')->first()) {
            $p->blocks = Blocks::sanitize([['type' => 'org_chart', 'data' => ['title' => '', 'note' => 'Representação simplificada e ilustrativa (exemplo) — a validar com o organograma oficial.'], 'items' => [
                ['nodes' => 'Reitoria', 'highlight' => true],
                ['nodes' => 'Conselho Geral; Senado Universitário; Conselho Científico; Conselho de Direcção', 'highlight' => false],
                ['nodes' => 'Faculdade de Direito; Faculdade de Economia; Faculdade de Medicina; Instituto Superior Politécnico de Cabinda', 'highlight' => false],
                ['nodes' => 'Serviços Académicos; Serviços Administrativos e Financeiros; Biblioteca', 'highlight' => false],
            ]]]);
            $p->save();
        }

        foreach (['ensino/calendario-academico', 'ensino/informacoes-academicas', 'investigacao', 'extensao/projectos', 'extensao/mobilidade', 'servicos/academicos', 'servicos/apoio-estudante', 'servicos/administrativos', 'servicos/biblioteca', 'instituicao/servicos', 'politica-de-privacidade', 'termos-de-utilizacao', 'acessibilidade'] as $path) {
            $p = Page::where('path', $path)->first();
            if (! $p) {
                continue;
            }
            $body = str_replace(['geral@uon.exemplo.ao', 'Universidade Onze de Novembro'], ['geral@uon.edu.ao', 'Universidade 11 de Novembro'], (string) $p->body);
            if (! str_contains($body, 'demo-badge')) {
                $body = $note.$body;
            }
            $p->body = $body;
            $p->save();
        }
    }

    /** Nome oficial usado em uon.edu.ao: "Universidade 11 de Novembro". */
    private function officialName(): void
    {
        $columns = [
            'media' => ['title', 'alt_text'], 'pages' => ['title', 'excerpt', 'body'], 'organic_units' => ['summary', 'description'],
            'events' => ['title', 'summary', 'body'], 'documents' => ['title', 'description'], 'gallery_items' => ['title', 'caption'],
            'partners' => ['description'], 'site_settings' => ['value'],
        ];
        foreach ($columns as $table => $cols) {
            foreach ($cols as $col) {
                if (\Illuminate\Support\Facades\Schema::hasColumn($table, $col)) {
                    \Illuminate\Support\Facades\DB::table($table)->where($col, 'like', '%Onze de Novembro%')
                        ->update([$col => \Illuminate\Support\Facades\DB::raw("REPLACE(`{$col}`, 'Onze de Novembro', '11 de Novembro')")]);
                }
            }
        }
    }

    // --------------------------------------------------------------
    // Configurações: números coerentes com os dados oficiais
    // --------------------------------------------------------------
    private function reconcileSettings(): void
    {
        $years = now()->year - 2009;
        $courses = Course::published()->count();
        Settings::set([
            'home_stats' => "+{$years} | Anos de História\n{$courses} | Cursos\n+6.000 | Estudantes (exemplo)\n+300 | Docentes (exemplo)",
            'site_tagline' => '',
        ]);
    }
}
