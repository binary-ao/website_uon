<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\OrganicUnit;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\Settings;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeAndUnitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStructure();
    }

    public function test_home_about_stats_and_research_sections_follow_template_order(): void
    {
        Settings::set([
            'about_title' => 'Sobre nós título', 'about_text' => 'Texto sobre', 'about_figure_number' => '2009', 'about_figure_label' => 'Criação',
            'home_stats' => "+15 | Anos de História\n+20 | Cursos",
            'research_text' => 'Texto investigação', 'research_url' => 'https://investigacao.example.org/',
        ]);
        (new \App\Models\Course(['name' => 'Curso X', 'degree_id' => \App\Models\Degree::first()->id] + $this->published()))->save();
        (new News(['type' => 'news', 'title' => 'Notícia X'] + $this->published()))->save();

        $res = $this->get('/')->assertOk();
        $res->assertSee('about-figure-tag', false)->assertSee('Sobre nós título')->assertSee('2009');
        $res->assertSee('<span class="plus">+</span>15', false);
        $res->assertSee('research-panel', false)->assertSee('https://investigacao.example.org/', false);
        // Ordem do template: sobre → oferta formativa → números → notícias → investigação
        $res->assertSeeInOrder(['about-figure-tag', 'Oferta Formativa', 'stat-block', 'Notícia X', 'research-panel'], false);
    }

    public function test_unit_page_is_generated_from_cms_data(): void
    {
        $faculty = new OrganicUnit(['type' => 'faculdade', 'name' => 'Faculdade Teste', 'summary' => 'Área teste', 'description' => '<p>Apresentação da faculdade</p>', 'head_title' => 'Decano', 'head_name' => 'Prof. Teste'] + $this->published());
        $faculty->save();
        $dept = new OrganicUnit(['type' => 'departamento', 'name' => 'Departamento Teste', 'parent_id' => $faculty->id] + $this->published());
        $dept->save();
        $course = new \App\Models\Course(['name' => 'Curso do Departamento', 'degree_id' => \App\Models\Degree::first()->id, 'organic_unit_id' => $dept->id] + $this->published());
        $course->save();

        $this->get('/instituicao/unidades-organicas')->assertSee($faculty->url(), false);
        $this->get($faculty->url())->assertOk()
            ->assertSee('Apresentação da faculdade')->assertSee('Decano: Prof. Teste')
            ->assertSee('Departamento Teste')->assertSee('Curso do Departamento');

        // Rascunho não é público; pré-visualização no CMS funciona
        $faculty->update(['status' => 'draft']);
        $this->get(route('units.show', $faculty->slug))->assertNotFound();
        $this->actingAs($this->userWithRole('admin'))->get(route('admin.preview', ['units', $faculty->id]))->assertOk()->assertSee('Pré-visualização privada');
    }

    public function test_demo_content_fills_site_and_removal_restores_original_state(): void
    {
        $original = Page::where('path', 'instituicao/historia')->first();
        $this->assertNull($original->blocks);

        $this->seed(DemoContentSeeder::class);
        Settings::flush();

        $this->assertSame(3, OrganicUnit::where('type', 'faculdade')->count());
        $this->assertNotEmpty(Page::where('path', 'instituicao/historia')->first()->blocks);
        $this->assertSame('geral@uon.exemplo.ao', setting('contact_email'));
        $this->get('/')->assertSee('Formação de excelência ao serviço do desenvolvimento')->assertSee('research-panel', false);
        $this->get('/instituicao/historia')->assertSee('2009 — Criação');
        foreach (OrganicUnit::where('type', 'faculdade')->get() as $f) {
            $this->get($f->url())->assertOk();
        }
        $this->get('/comunicacao/multimedia')->assertSee('youtube-nocookie.com/embed/', false);

        // Selo "Exemplo" desactivável no CMS
        $this->get('/comunicacao/noticias')->assertSee('demo-badge', false);
        Settings::set(['show_demo_badges' => '0']);
        $this->get('/comunicacao/noticias')->assertDontSee('demo-badge', false);

        $this->artisan('uon:demo-content', ['--remove' => true])->expectsConfirmation('Remover definitivamente todo o conteúdo marcado como demonstração?', 'yes')->assertSuccessful();
        Settings::flush();
        $this->assertSame(0, OrganicUnit::count());
        $this->assertSame(0, News::count());
        $this->assertNull(Page::where('path', 'instituicao/historia')->first()->blocks);
        $this->assertNull(setting('contact_email'));
        $this->assertFalse(SiteSetting::where('key', DemoContentSeeder::STATE_KEY)->exists());
        $this->assertSame(0, \App\Models\Media::count());
    }
}
