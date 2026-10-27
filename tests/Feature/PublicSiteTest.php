<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Degree;
use App\Models\Document;
use App\Models\Event;
use App\Models\News;
use App\Models\Page;
use App\Models\Redirect;
use App\Services\DocumentStorage;
use App\Services\MenuRepository;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStructure();
    }

    private function news(array $attrs = []): News
    {
        $n = new News(array_merge(['type' => 'news', 'title' => 'Notícia de teste', 'excerpt' => 'Resumo'], $this->published(), $attrs));
        $n->save();

        return $n;
    }

    public function test_home_page_renders_with_template_header_and_footer(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        // Composição do Header original
        $res->assertSee('class="topbar d-none d-md-block"', false);
        $res->assertSee('<header class="site-header">', false);
        $res->assertSee('class="navbar navbar-expand-lg py-2"', false);
        $res->assertSee('class="dropdown-menu mega-menu"', false);
        $res->assertSee('class="dropdown-menu simple-dropdown"', false);
        $res->assertSee('id="searchFlyout"', false);
        $res->assertSee('id="mobileNav"', false);
        $res->assertSee('logo-uon.png', false);
        // Ordem dos menus do template
        $res->assertSeeInOrder(['Início', 'Instituição', 'Ensino', 'Investigação', 'Extensão', 'Comunicação', 'Serviços', 'Contactos']);
        // Footer original e contentor global
        $res->assertSee('<footer class="site-footer">', false);
        $res->assertSee('class="footer-bottom', false);
        $res->assertSee('id="backToTop"', false);
        $res->assertSee('class="site-shell"', false);
        $res->assertSee('Universidade Onze de Novembro');
    }

    public function test_all_template_pages_are_reachable_from_the_menus(): void
    {
        $menus = app(MenuRepository::class);
        $urls = [];
        foreach (['main', 'topbar', 'footer_1', 'footer_2', 'footer_bottom'] as $loc) {
            $walk = function ($items) use (&$walk, &$urls) {
                foreach ($items as $item) {
                    if ($item->href() !== '#' && ! $item->isExternal()) {
                        $urls[] = parse_url($item->href(), PHP_URL_PATH) ?: '/';
                    }
                    $walk($item->visibleChildren);
                }
            };
            $walk($menus->tree($loc));
        }
        $urls = array_unique($urls);
        $this->assertGreaterThan(35, count($urls));
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_external_links_without_configured_url_are_hidden_and_appear_when_configured(): void
    {
        $this->get('/')->assertDontSee('Revistas Científicas');
        Settings::set(['research_journals_url' => 'https://revistas.example.org/']);
        app()->forgetInstance(MenuRepository::class);
        $this->get('/')->assertSee('https://revistas.example.org/', false)->assertSee('Revistas Científicas');
    }

    public function test_draft_scheduled_and_archived_content_is_not_public(): void
    {
        $pub = $this->news(['title' => 'Publicada visível']);
        $draft = $this->news(['title' => 'Rascunho secreto', 'status' => PublicationStatus::Draft]);
        $future = $this->news(['title' => 'Agendada futura', 'status' => PublicationStatus::Published, 'published_at' => now()->addDay()]);
        $arch = $this->news(['title' => 'Arquivada antiga', 'status' => PublicationStatus::Archived]);

        $this->assertSame(PublicationStatus::Scheduled, $future->fresh()->status);

        $list = $this->get('/comunicacao/noticias');
        $list->assertSee('Publicada visível')->assertDontSee('Rascunho secreto')->assertDontSee('Agendada futura')->assertDontSee('Arquivada antiga');

        $this->get($pub->url())->assertOk();
        $this->get($draft->url())->assertNotFound();
        $this->get($future->url())->assertNotFound();
        $this->get($arch->url())->assertNotFound();

        // Quando a data chega, o conteúdo agendado fica visível (mesmo sem cron).
        $this->travel(2)->days();
        $this->get($future->url())->assertOk();
    }

    public function test_unpublished_page_returns_404(): void
    {
        Page::where('path', 'instituicao/historia')->update(['status' => 'draft']);
        $this->get('/instituicao/historia')->assertNotFound();
        $this->get('/')->assertDontSee('/instituicao/historia"', false);
    }

    public function test_news_pagination_and_category_filter(): void
    {
        $ensino = Category::where('type', 'news')->where('slug', 'ensino')->first();
        for ($i = 1; $i <= 11; $i++) {
            $this->news(['title' => "Notícia número $i", 'published_at' => now()->subDays($i), 'category_id' => $i <= 3 ? $ensino->id : null]);
        }
        $page1 = $this->get('/comunicacao/noticias');
        $page1->assertSee('11 resultados')->assertSee('Notícia número 1')->assertDontSee('Notícia número 11');
        $this->get('/comunicacao/noticias?page=2')->assertSee('Notícia número 11');

        $filtered = $this->get('/comunicacao/noticias?categoria=ensino');
        $filtered->assertSee('3 resultados')->assertSee('Notícia número 2')->assertDontSee('Notícia número 7');

        $this->get('/comunicacao/noticias?q=número 10')->assertSee('1 resultado');
    }

    public function test_events_are_split_between_upcoming_and_past(): void
    {
        (new Event(['title' => 'Evento futuro', 'starts_at' => now()->addWeek()] + $this->published()))->save();
        (new Event(['title' => 'Evento passado', 'starts_at' => now()->subWeek(), 'ends_at' => now()->subWeek()->addHour()] + $this->published()))->save();

        $this->get('/comunicacao/eventos')->assertSee('Evento futuro')->assertDontSee('Evento passado');
        $this->get('/comunicacao/eventos?periodo=anteriores')->assertSee('Evento passado')->assertDontSee('Evento futuro');
        $this->get('/')->assertSee('Evento futuro')->assertDontSee('Evento passado');
    }

    public function test_course_listing_degree_filter_and_detail(): void
    {
        $lic = Degree::where('slug', 'licenciatura')->first();
        $mes = Degree::where('slug', 'mestrado')->first();
        $c1 = new Course(['name' => 'Curso Alfa', 'degree_id' => $lic->id] + $this->published());
        $c1->save();
        $c1->subjects()->create(['year' => 1, 'semester' => 1, 'name' => 'Disciplina Zeta', 'hours' => 60]);
        (new Course(['name' => 'Curso Beta', 'degree_id' => $mes->id] + $this->published()))->save();

        $this->get('/ensino/licenciaturas')->assertSee('Curso Alfa')->assertDontSee('Curso Beta');
        $this->get('/ensino/oferta-formativa?grau=mestrado')->assertSee('Curso Beta')->assertDontSee('Curso Alfa');
        $this->get($c1->url())->assertOk()->assertSee('Disciplina Zeta')->assertSee('Plano Curricular');
    }

    public function test_global_search_returns_results_and_type_filter_works(): void
    {
        $this->news(['title' => 'Inovação pedagógica em destaque']);
        (new Course(['name' => 'Inovação Tecnológica', 'degree_id' => Degree::first()->id] + $this->published()))->save();

        $all = $this->get('/pesquisa?q=Inovação');
        $all->assertOk()->assertSee('2 resultados', false)->assertSee('Notícia')->assertSee('Curso');

        $onlyCourses = $this->get('/pesquisa?q=Inovação&tipo[]=cursos');
        $onlyCourses->assertSee('1 resultado')->assertDontSee('Inovação pedagógica em destaque');

        $this->get('/pesquisa?q=xyzinexistente')->assertSee('Sem resultados');
        $this->get('/pesquisa')->assertSee('Pesquise no website');
    }

    public function test_contact_form_validates_and_stores_message(): void
    {
        $this->post('/contactos', [])->assertSessionHasErrors(['name', 'email', 'subject', 'message', 'consent']);
        $this->post('/contactos', ['name' => 'Ana', 'email' => 'nao-e-email', 'subject' => 'Inventado', 'message' => 'curta', 'consent' => 1])
            ->assertSessionHasErrors(['email', 'subject', 'message']);

        $this->from('/contactos')->post('/contactos', [
            'name' => 'Ana <b>Teste</b>', 'email' => 'ana@example.org', 'subject' => 'Oferta formativa',
            'message' => 'Gostaria de obter informações.', 'consent' => 1,
        ])->assertRedirect('/contactos')->assertSessionHas('contact_success');
        $this->assertDatabaseHas('contact_messages', ['email' => 'ana@example.org', 'name' => 'Ana Teste', 'status' => 'new']);

        // Honeypot: bots não ficam registados
        $this->post('/contactos', ['website' => 'spam', 'name' => 'Bot', 'email' => 'b@b.com', 'subject' => 'Outro assunto', 'message' => 'spam spam spam', 'consent' => 1]);
        $this->assertDatabaseMissing('contact_messages', ['email' => 'b@b.com']);
    }

    public function test_404_page_and_redirects(): void
    {
        $this->get('/pagina-inexistente')->assertNotFound()->assertSee('Página não encontrada');
        Redirect::create(['from_path' => '/antigo', 'to_url' => '/contactos', 'status_code' => 301, 'is_active' => true]);
        $this->get('/antigo')->assertRedirect(url('/contactos'))->assertStatus(301);
        $this->assertSame(1, Redirect::first()->hits);
    }

    public function test_slug_change_of_published_news_creates_redirect(): void
    {
        $n = $this->news(['title' => 'Título original']);
        $old = $n->relativeUrl();
        $n->slug = 'titulo-novo';
        $n->save();
        $this->get($old)->assertRedirect(url('/comunicacao/noticias/titulo-novo'));
    }

    public function test_sitemap_and_robots(): void
    {
        $this->news(['title' => 'No sitemap']);
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertSee('/comunicacao/noticias/no-sitemap', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin');
        Settings::set(['seo_allow_indexing' => '0']);
        $this->get('/robots.txt')->assertSee("Disallow: /\n", false);
        $this->get('/')->assertSee('noindex', false);
    }

    public function test_documents_public_restricted_and_draft_download_rules(): void
    {
        $storage = app(DocumentStorage::class);
        $make = function (array $attrs) use ($storage) {
            $d = new Document(array_merge(['title' => 'Doc', 'visibility' => 'public'], $attrs));
            $d->save();
            $storage->addVersion($d, UploadedFile::fake()->createWithContent('doc.pdf', "%PDF-1.4\n%%EOF"));

            return $d->fresh();
        };
        $public = $make(['title' => 'Público'] + $this->published());
        $restricted = $make(['title' => 'Restrito', 'visibility' => 'restricted'] + $this->published());
        $draft = $make(['title' => 'Rascunho', 'status' => PublicationStatus::Draft]);

        $this->get($public->downloadUrl())->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get($restricted->downloadUrl())->assertRedirect(route('login'));
        $this->get($draft->downloadUrl())->assertNotFound();

        $this->actingAs($this->userWithRole('author'));
        $this->get($restricted->downloadUrl())->assertOk();

        // Lista pública não mostra documentos restritos a visitantes
        auth()->logout();
        Page::where('path', 'instituicao/documentos')->update(['module_options' => null]);
        $this->get('/instituicao/documentos')->assertSee('Público')->assertDontSee('Restrito');
    }

    public function test_security_headers_and_escaping(): void
    {
        $this->news(['title' => '<script>alert(1)</script> Título']);
        $res = $this->get('/comunicacao/noticias');
        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertDontSee('<script>alert(1)</script>', false);
        $res->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_empty_institutional_page_shows_preparation_state(): void
    {
        $this->get('/instituicao/historia')->assertOk()->assertSee('Conteúdo em preparação');
    }

    public function test_settings_update_header_and_footer_content_without_changing_structure(): void
    {
        Settings::set(['contact_phone' => '+244 000 000 000', 'contact_email' => 'geral@exemplo.test', 'social_facebook' => 'https://facebook.com/exemplo', 'footer_about' => 'Texto do rodapé']);
        $res = $this->get('/');
        $res->assertSee('+244 000 000 000')->assertSee('geral@exemplo.test')->assertSee('https://facebook.com/exemplo', false)->assertSee('Texto do rodapé');
        $res->assertSee('<footer class="site-footer">', false);
    }
}
