<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Degree;
use App\Models\News;
use App\Models\Page;
use App\Models\Tag;
use App\Support\Blocks;
use App\Support\SafeUrl;
use App\Support\VideoEmbed;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStructure();
    }

    public function test_migrations_create_expected_tables(): void
    {
        foreach (['users', 'roles', 'permissions', 'model_has_roles', 'pages', 'revisions', 'news', 'news_tag', 'tags', 'categories', 'events',
            'documents', 'document_versions', 'documentables', 'menus', 'menu_items', 'media', 'organic_units', 'degrees', 'courses',
            'course_subjects', 'site_settings', 'redirects', 'activity_logs', 'contact_messages', 'notices', 'partners', 'gallery_items'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabela em falta: $table");
        }
    }

    public function test_seeders_create_template_structure_and_are_idempotent(): void
    {
        $pages = Page::count();
        $items = \App\Models\MenuItem::count();
        $this->assertGreaterThanOrEqual(40, $pages);
        $this->seed([\Database\Seeders\PageSeeder::class, \Database\Seeders\MenuSeeder::class, \Database\Seeders\TaxonomySeeder::class]);
        $this->assertSame($pages, Page::count());
        $this->assertSame($items, \App\Models\MenuItem::count());
        $this->assertSame(0, \App\Models\User::count(), 'Não devem existir contas por omissão.');
    }

    public function test_unique_slugs_are_generated_and_enforced(): void
    {
        $a = new News(['type' => 'news', 'title' => 'Mesmo título', 'status' => 'draft']);
        $a->save();
        $b = new News(['type' => 'news', 'title' => 'Mesmo título', 'status' => 'draft']);
        $b->save();
        $this->assertSame('mesmo-titulo', $a->slug);
        $this->assertSame('mesmo-titulo-2', $b->slug);

        // Slugs na reciclagem também contam
        $a->delete();
        $c = new News(['type' => 'news', 'title' => 'Mesmo título', 'status' => 'draft']);
        $c->save();
        $this->assertSame('mesmo-titulo-3', $c->slug);

        $this->expectException(QueryException::class);
        \DB::table('news')->insert(['type' => 'news', 'title' => 'x', 'slug' => 'mesmo-titulo-2', 'status' => 'draft']);
    }

    public function test_foreign_keys_restrict_and_cascade(): void
    {
        $degree = Degree::first();
        $course = new Course(['name' => 'FK', 'degree_id' => $degree->id, 'status' => 'draft']);
        $course->save();
        $course->subjects()->create(['year' => 1, 'name' => 'UC']);

        try {
            $degree->delete();
            $this->fail('Grau com cursos não deveria ser eliminado.');
        } catch (QueryException) {
            $this->assertModelExists($degree);
        }

        $course->forceDelete();
        $this->assertDatabaseCount('course_subjects', 0);

        $cat = Category::where('type', 'news')->first();
        $news = new News(['type' => 'news', 'title' => 'Com categoria', 'category_id' => $cat->id, 'status' => 'draft']);
        $news->save();
        $tag = Tag::create(['name' => 'T', 'slug' => 't']);
        $news->tags()->attach($tag);
        $news->forceDelete();
        $this->assertDatabaseCount('news_tag', 0);
    }

    public function test_category_in_use_cannot_be_deleted_via_cms(): void
    {
        $cat = Category::where('type', 'news')->first();
        (new News(['type' => 'news', 'title' => 'Usa categoria', 'category_id' => $cat->id, 'status' => 'draft']))->save();
        $this->actingAs($this->userWithRole('admin'))->delete("/admin/categories/{$cat->id}")->assertSessionHas('error');
        $this->assertModelExists($cat);
    }

    public function test_queries_with_empty_data_do_not_fail(): void
    {
        foreach (['/comunicacao/noticias', '/comunicacao/eventos', '/comunicacao/agenda', '/comunicacao/galeria', '/comunicacao/multimedia',
            '/ensino/oferta-formativa', '/instituicao/unidades-organicas', '/instituicao/documentos', '/instituicao/parceiros', '/pesquisa?q=nada'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_archived_content_stays_in_cms_but_not_public(): void
    {
        $n = new News(['type' => 'news', 'title' => 'Arquivo', 'status' => 'archived']);
        $n->save();
        $this->get('/comunicacao/noticias')->assertDontSee('Arquivo');
        $this->actingAs($this->userWithRole('admin'))->get('/admin/news?status=archived')->assertSee('Arquivo');
    }

    public function test_url_and_video_validators(): void
    {
        foreach (['/contactos', '#topo', 'https://uon.example', 'mailto:a@b.co', 'tel:+244 900'] as $ok) {
            $this->assertTrue(SafeUrl::isValid($ok), $ok);
        }
        foreach (['javascript:alert(1)', 'JaVaScRiPt:alert(1)', 'data:text/html,x', '//evil.com', 'vbscript:x', "/a\nb", 'ftp://x'] as $bad) {
            $this->assertFalse(SafeUrl::isValid($bad), $bad);
        }
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', VideoEmbed::embedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertNull(VideoEmbed::embedUrl('https://evil.example/video'));
    }

    public function test_blocks_sanitizer_drops_unknown_fields_and_html(): void
    {
        $clean = Blocks::sanitize([['type' => 'cta', 'data' => ['title' => '<img src=x onerror=alert(1)>Título', 'button_url' => 'javascript:x', 'evil' => 'x']]]);
        $this->assertSame('Título', $clean[0]['data']['title']);
        $this->assertSame('', $clean[0]['data']['button_url']);
        $this->assertArrayNotHasKey('evil', $clean[0]['data']);
    }
}
