<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\Course;
use App\Models\Degree;
use App\Models\Document;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCmsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStructure();
        $this->admin = $this->userWithRole('admin');
    }

    public function test_all_admin_screens_render_for_superadmin(): void
    {
        $super = $this->userWithRole('superadmin');
        $this->actingAs($super);
        $page = Page::first();
        $menu = Menu::where('location', 'main')->first();
        foreach ([
            '/admin', '/admin/pages', '/admin/pages/create', "/admin/pages/{$page->id}/edit", '/admin/news', '/admin/news/create',
            '/admin/news/create?type=announcement', '/admin/events', '/admin/events/create', '/admin/documents', '/admin/documents/create',
            '/admin/courses', '/admin/courses/create', '/admin/units', '/admin/units/create', '/admin/gallery', '/admin/gallery/create',
            '/admin/gallery/create?type=video', '/admin/media', '/admin/menus', "/admin/menus/{$menu->id}", '/admin/configuracoes',
            '/admin/configuracoes/home', '/admin/configuracoes/contacts', '/admin/notices', '/admin/notices/create', '/admin/partners',
            '/admin/partners/create', '/admin/categories', '/admin/categories/create', '/admin/tags', '/admin/degrees', '/admin/degrees/create',
            '/admin/redirects', '/admin/redirects/create', '/admin/messages', '/admin/users', '/admin/users/create', '/admin/roles',
            '/admin/activity', '/admin/perfil', '/admin/hero-slides', '/admin/hero-slides/create', '/admin/pages?trashed=1', '/admin/media/picker',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_page_crud_with_sanitized_html_and_blocks(): void
    {
        $this->actingAs($this->admin);
        $blocks = json_encode([
            ['type' => 'cards', 'data' => ['title' => 'Atalhos', 'columns' => '3'], 'items' => [
                ['icon' => 'bi-building', 'title' => 'Cartão <b>um</b>', 'text' => 'Texto', 'url' => '/contactos'],
                ['icon' => 'x" onclick="alert(1)', 'title' => 'Cartão dois', 'url' => 'javascript:alert(1)'],
            ]],
            ['type' => 'tipo_inexistente', 'data' => ['x' => 1]],
        ]);
        $res = $this->post('/admin/pages', [
            'title' => 'Página de teste', 'layout' => 'standard', 'status' => 'published',
            'body' => '<p>Olá</p><script>alert(1)</script><a href="javascript:alert(1)" onclick="x()">link</a><img src="x" onerror="alert(1)">',
            'blocks_json' => $blocks, 'show_in_search' => 1,
        ]);
        $page = Page::where('title', 'Página de teste')->firstOrFail();
        $res->assertRedirect(route('admin.pages.edit', $page));

        $this->assertStringNotContainsString('<script', $page->body);
        $this->assertStringNotContainsString('javascript:', $page->body);
        $this->assertStringNotContainsString('onclick', $page->body);
        $this->assertStringNotContainsString('onerror', $page->body);
        $this->assertCount(1, $page->blocks);
        $this->assertSame('Cartão um', $page->blocks[0]['items'][0]['title']);
        $this->assertSame('', $page->blocks[0]['items'][1]['icon']);
        $this->assertSame('', $page->blocks[0]['items'][1]['url']);
        $this->assertSame('pagina-de-teste', $page->path);
        $this->assertSame($this->admin->id, $page->author_id);
        $this->assertDatabaseHas('revisions', ['revisionable_id' => $page->id, 'action' => 'created']);

        $this->get('/pagina-de-teste')->assertOk()->assertSee('Cartão um')->assertSee('Atalhos');

        // Edição e histórico
        $this->put("/admin/pages/{$page->id}", ['title' => 'Página editada', 'slug' => 'pagina-de-teste', 'layout' => 'standard', 'status' => 'published'])->assertRedirect();
        $this->assertSame(2, $page->revisions()->count());
        $first = $page->revisions()->reorder()->oldest('id')->first();
        $this->get(route('admin.revisions.show', $first))->assertOk()->assertSee('Diferente');
        $this->post(route('admin.revisions.restore', $first))->assertRedirect();
        $this->assertSame('Página de teste', $page->fresh()->title);
    }

    public function test_page_slug_uniqueness_reserved_paths_and_cycles(): void
    {
        $this->actingAs($this->admin);
        $this->post('/admin/pages', ['title' => 'Contactos', 'slug' => 'contactos', 'layout' => 'standard', 'status' => 'draft'])->assertSessionHasErrors('slug');
        $this->post('/admin/pages', ['title' => 'Admin', 'slug' => 'admin', 'layout' => 'standard', 'status' => 'draft'])->assertSessionHasErrors('slug');

        $this->post('/admin/pages', ['title' => 'Pai', 'layout' => 'standard', 'status' => 'draft']);
        $parent = Page::where('title', 'Pai')->first();
        $this->post('/admin/pages', ['title' => 'Filho', 'parent_id' => $parent->id, 'layout' => 'standard', 'status' => 'draft']);
        $child = Page::where('title', 'Filho')->first();
        $this->assertSame('pai/filho', $child->path);
        $this->put("/admin/pages/{$parent->id}", ['title' => 'Pai', 'parent_id' => $child->id, 'layout' => 'standard', 'status' => 'draft'])->assertSessionHasErrors('parent_id');

        // Mudar o slug do pai actualiza os caminhos dos filhos.
        $this->put("/admin/pages/{$parent->id}", ['title' => 'Pai', 'slug' => 'novo-pai', 'layout' => 'standard', 'status' => 'draft']);
        $this->assertSame('novo-pai/filho', $child->fresh()->path);
    }

    public function test_system_pages_cannot_be_deleted_and_keep_their_address(): void
    {
        $this->actingAs($this->admin);
        $page = Page::where('path', 'contactos')->first();
        $this->delete("/admin/pages/{$page->id}")->assertSessionHas('error');
        $this->assertNotSoftDeleted($page);
        $this->put("/admin/pages/{$page->id}", ['title' => 'Contactos UON', 'slug' => 'outro', 'layout' => 'contact', 'status' => 'published']);
        $this->assertSame('contactos', $page->fresh()->path);
    }

    public function test_soft_delete_restore_and_duplicate(): void
    {
        $this->actingAs($this->admin);
        $news = new News(['type' => 'news', 'title' => 'Para reciclar'] + $this->published());
        $news->save();
        $this->delete("/admin/news/{$news->id}")->assertRedirect();
        $this->assertSoftDeleted($news);
        $this->get($news->url())->assertNotFound();
        $this->post("/admin/news/{$news->id}/restore")->assertRedirect();
        $this->assertNotSoftDeleted($news);

        $this->post("/admin/news/{$news->id}/duplicate")->assertRedirect();
        $copy = News::where('title', 'Para reciclar (cópia)')->first();
        $this->assertSame(PublicationStatus::Draft, $copy->status);
        $this->assertNotSame($news->slug, $copy->slug);
    }

    public function test_news_with_tags_category_and_schedule(): void
    {
        $this->actingAs($this->admin);
        $this->post('/admin/news', [
            'type' => 'news', 'title' => 'Notícia agendada', 'status' => 'published',
            'published_at' => now()->addDays(3)->format('Y-m-d\TH:i'), 'tags' => 'Ciência, ciência, Inovação',
        ])->assertRedirect();
        $news = News::where('title', 'Notícia agendada')->first();
        $this->assertSame(PublicationStatus::Scheduled, $news->status);
        $this->assertCount(2, $news->tags);
        $this->get($news->url())->assertNotFound();

        // Pré-visualização privada
        $this->get(route('admin.preview', ['news', $news->id]))->assertOk()->assertSee('Pré-visualização privada')->assertSee('noindex', false);
        auth()->logout();
        $this->get(route('admin.preview', ['news', $news->id]))->assertRedirect(route('login'));

        // Comando de publicação agendada
        $this->travel(4)->days();
        $this->artisan('content:publish-scheduled')->assertSuccessful();
        $this->assertSame(PublicationStatus::Published, $news->fresh()->status);
    }

    public function test_author_permissions_are_enforced_server_side(): void
    {
        $author = $this->userWithRole('author');
        $other = $this->userWithRole('author');
        $this->actingAs($author);

        // Tentativa de publicar e de destacar: fica em rascunho.
        $this->post('/admin/news', ['type' => 'news', 'title' => 'Do autor', 'status' => 'published', 'is_featured' => 1])->assertRedirect();
        $own = News::where('title', 'Do autor')->first();
        $this->assertSame(PublicationStatus::Draft, $own->status);
        $this->assertFalse($own->is_featured);

        // Pode enviar para revisão
        $this->put("/admin/news/{$own->id}", ['type' => 'news', 'title' => 'Do autor', 'status' => 'pending'])->assertRedirect();
        $this->assertSame(PublicationStatus::Pending, $own->fresh()->status);

        // Não edita conteúdos de outros
        $foreign = new News(['type' => 'news', 'title' => 'Alheia', 'status' => 'draft']);
        $foreign->author_id = $other->id;
        $foreign->save();
        $this->put("/admin/news/{$foreign->id}", ['type' => 'news', 'title' => 'Alterada', 'status' => 'draft'])->assertForbidden();
        $this->get('/admin/news')->assertDontSee('Alheia');

        // Não edita após publicação
        $own->forceFill(['status' => 'published', 'published_at' => now()->subMinute()])->save();
        $this->put("/admin/news/{$own->id}", ['type' => 'news', 'title' => 'Mudar', 'status' => 'draft'])->assertForbidden();

        // Módulos sem permissão
        foreach (['/admin/pages', '/admin/configuracoes', '/admin/menus', '/admin/users', '/admin/roles', '/admin/activity', '/admin/courses'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/pages', ['title' => 'x', 'layout' => 'standard', 'status' => 'draft'])->assertForbidden();
    }

    public function test_editor_can_publish_but_cannot_manage_settings_or_users(): void
    {
        $editor = $this->userWithRole('editor');
        $this->actingAs($editor);
        $this->post('/admin/news', ['type' => 'news', 'title' => 'Do editor', 'status' => 'published'])->assertRedirect();
        $this->assertSame(PublicationStatus::Published, News::where('title', 'Do editor')->first()->status);
        $this->put('/admin/configuracoes/identity', ['site_name' => 'Hack'])->assertForbidden();
        $this->post('/admin/users', ['name' => 'x', 'email' => 'x@x.com', 'role' => 'superadmin'])->assertForbidden();
    }

    public function test_privilege_escalation_is_prevented(): void
    {
        $this->actingAs($this->admin);
        // Administrador não gere utilizadores (reservado ao superadministrador).
        $this->get('/admin/users')->assertForbidden();

        $super = $this->userWithRole('superadmin');
        $this->actingAs($super);
        // Não pode alterar a própria função nem desactivar-se
        $this->put("/admin/users/{$super->id}", ['name' => $super->name, 'email' => $super->email, 'role' => 'author', 'is_active' => 0])->assertRedirect();
        $this->assertTrue($super->fresh()->isSuperAdmin());
        $this->assertTrue($super->fresh()->is_active);
        // Não pode eliminar o último superadministrador (a si próprio)
        $this->delete("/admin/users/{$super->id}")->assertSessionHas('error');

        // Mass assignment: campos não permitidos são ignorados
        $this->put('/admin/perfil', ['name' => 'Novo Nome', 'email' => $super->email, 'is_active' => 0, 'roles' => ['author']])->assertSessionHas('success');
        $this->assertTrue($super->fresh()->is_active);

        // Funções não editáveis para o superadministrador e permissões reservadas
        $role = \Spatie\Permission\Models\Role::findByName('editor');
        $this->put("/admin/roles/{$role->id}", ['permissions' => ['news.create', 'users.manage']])->assertSessionHas('success');
        $this->assertTrue($role->fresh()->hasPermissionTo('news.create'));
        $this->assertFalse($role->fresh()->hasPermissionTo('users.manage'));
        $superRole = \Spatie\Permission\Models\Role::findByName('superadmin');
        $this->put("/admin/roles/{$superRole->id}", ['permissions' => []])->assertForbidden();
    }

    public function test_media_upload_validation_and_controlled_deletion(): void
    {
        $this->actingAs($this->admin);
        $this->post('/admin/media', ['file' => UploadedFile::fake()->image('foto.jpg', 2400, 1200)])->assertSessionHas('success');
        $media = Media::latest('id')->first();
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertSame(1920, $media->width);
        $this->assertArrayHasKey('md', $media->variants);
        Storage::disk('uploads')->assertExists($media->path);
        $this->assertStringNotContainsString('foto', $media->path);

        // PHP disfarçado de imagem é rejeitado (validação pelo conteúdo, não pela extensão)
        $this->post('/admin/media', ['file' => UploadedFile::fake()->createWithContent('shell.jpg', '<?php system($_GET["c"]); ?>')])->assertSessionHasErrors('file');
        $this->post('/admin/media', ['file' => UploadedFile::fake()->createWithContent('x.php', '<?php echo 1;')])->assertSessionHasErrors('file');
        $this->post('/admin/media', ['file' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>')])->assertSessionHasErrors('file');
        $this->postJson('/admin/media', ['file' => UploadedFile::fake()->createWithContent('x.html', '<script>alert(1)</script>')])->assertStatus(422);

        // Ficheiro em uso não pode ser eliminado
        $news = new News(['type' => 'news', 'title' => 'Com imagem', 'featured_media_id' => $media->id] + $this->published());
        $news->save();
        $this->delete("/admin/media/{$media->id}")->assertSessionHas('error');
        $this->assertModelExists($media);
        $news->forceFill(['featured_media_id' => null])->save();
        $this->delete("/admin/media/{$media->id}")->assertRedirect(route('admin.media.index'));
        $this->assertModelMissing($media);
        Storage::disk('uploads')->assertMissing($media->path);

        // Autor pode carregar mas não eliminar ficheiros
        $author = $this->userWithRole('author');
        $this->actingAs($author)->postJson('/admin/media', ['file' => UploadedFile::fake()->image('a.png', 100, 100)])->assertCreated()->assertJsonStructure(['location', 'media' => ['id']]);
        $m2 = Media::latest('id')->first();
        $this->delete("/admin/media/{$m2->id}")->assertForbidden();
    }

    public function test_document_upload_versions_and_private_storage(): void
    {
        $this->actingAs($this->admin);
        $this->post('/admin/documents', [
            'title' => 'Regulamento', 'visibility' => 'public', 'status' => 'published',
            'file' => UploadedFile::fake()->createWithContent('reg.pdf', "%PDF-1.4\nv1\n%%EOF"),
        ])->assertRedirect();
        $doc = Document::where('title', 'Regulamento')->first();
        $this->assertSame(1, $doc->versions()->count());
        Storage::disk('documents')->assertExists($doc->currentVersion->path);
        $this->assertStringNotContainsString('reg', $doc->currentVersion->path);

        $this->post("/admin/documents/{$doc->id}/versions", ['file' => UploadedFile::fake()->createWithContent('reg2.pdf', "%PDF-1.4\nv2\n%%EOF")])->assertSessionHas('success');
        $doc->refresh();
        $this->assertSame(2, $doc->currentVersion->version);
        $v1 = $doc->versions()->where('version', 1)->first();
        Storage::disk('documents')->assertExists($v1->path);
        $this->post(route('admin.documents.versions.current', [$doc, $v1]))->assertSessionHas('success');
        $this->assertSame($v1->id, $doc->fresh()->current_version_id);

        // Executável rejeitado
        $this->post("/admin/documents/{$doc->id}/versions", ['file' => UploadedFile::fake()->createWithContent('mal.pdf', 'MZ'.str_repeat("\0", 64))])->assertSessionHasErrors('file');

        // Eliminação definitiva remove ficheiros
        $this->delete("/admin/documents/{$doc->id}");
        $this->delete("/admin/documents/{$doc->id}/force")->assertRedirect();
        Storage::disk('documents')->assertMissing($v1->path);
    }

    public function test_course_with_curriculum_saved_in_transaction(): void
    {
        $this->actingAs($this->admin);
        $degree = Degree::where('slug', 'licenciatura')->first();
        $this->post('/admin/courses', [
            'name' => 'Curso Teste', 'degree_id' => $degree->id, 'status' => 'published',
            'careers' => "Área A | Profissão A\nÁrea B",
            'subjects' => [
                ['year' => 1, 'semester' => 1, 'name' => 'UC 1', 'hours' => 60],
                ['year' => 2, 'semester' => 2, 'name' => 'UC 2', 'hours' => 90],
                ['year' => 1, 'name' => ''],
            ],
        ])->assertRedirect();
        $course = Course::where('name', 'Curso Teste')->first();
        $this->assertSame(2, $course->subjects()->count());
        $this->assertSame([['title' => 'Área A', 'text' => 'Profissão A'], ['title' => 'Área B', 'text' => '']], $course->careers);
        $this->get($course->url())->assertOk()->assertSee('UC 2')->assertSee('Profissão A');
    }

    public function test_menu_management_respects_template_composition_and_safe_urls(): void
    {
        $this->actingAs($this->admin);
        $main = Menu::where('location', 'main')->first();
        $mega = MenuItem::where('menu_id', $main->id)->where('display', 'mega')->first();

        $this->post("/admin/menus/{$main->id}/items", ['label' => 'Mau', 'display' => 'link', 'link_type' => 'url', 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
        $this->post("/admin/menus/{$main->id}/items", ['label' => 'Mau', 'display' => 'link', 'link_type' => 'url', 'url' => '//evil.example'])->assertSessionHasErrors('url');
        $this->post("/admin/menus/{$main->id}/items", ['label' => 'Link solto', 'display' => 'link', 'parent_id' => $mega->id, 'link_type' => 'url', 'url' => '/x'])->assertSessionHasErrors('display');
        $this->post("/admin/menus/{$main->id}/items", ['label' => 'Mega aninhado', 'display' => 'mega', 'parent_id' => $mega->id, 'link_type' => 'none'])->assertSessionHasErrors('display');

        $this->post("/admin/menus/{$main->id}/items", ['label' => 'Novo item', 'display' => 'link', 'link_type' => 'url', 'url' => '/pesquisa', 'is_active' => 1])->assertSessionHas('success');
        $item = MenuItem::where('label', 'Novo item')->first();
        $this->get('/')->assertSee('Novo item');

        $this->post("/admin/menus/{$main->id}/items/{$item->id}/move", ['direction' => 'up'])->assertSessionHas('success');
        $this->put("/admin/menus/{$main->id}/items/{$item->id}", ['label' => 'Item renomeado', 'display' => 'link', 'link_type' => 'url', 'url' => '/pesquisa', 'is_active' => 0]);
        $this->get('/')->assertDontSee('Item renomeado');

        $footer = Menu::where('location', 'footer_1')->first();
        $this->put("/admin/menus/{$footer->id}", ['title' => 'A Universidade']);
        $this->get('/')->assertSee('A Universidade');
    }

    public function test_settings_are_validated(): void
    {
        $this->actingAs($this->admin);
        $this->put('/admin/configuracoes/contacts', ['contact_email' => 'invalido'])->assertSessionHasErrors('contact_email');
        $this->put('/admin/configuracoes/social', ['social_facebook' => 'javascript:alert(1)'])->assertSessionHasErrors('social_facebook');
        $this->put('/admin/configuracoes/contacts', ['map_embed_url' => 'https://evil.example/embed'])->assertSessionHasErrors('map_embed_url');
        $this->put('/admin/configuracoes/identity', ['site_name' => ''])->assertSessionHasErrors('site_name');
        $this->put('/admin/configuracoes/header', ['header_cta_label' => 'Inscrições', 'header_cta_url' => '/contactos'])->assertSessionHas('success');
        $this->get('/')->assertSee('Inscrições');
        $this->assertDatabaseHas('activity_logs', ['action' => 'settings.updated']);
    }

    public function test_dashboard_counts_come_from_database(): void
    {
        $this->actingAs($this->admin);
        (new News(['type' => 'news', 'title' => 'Pendente X', 'status' => 'pending']))->save();
        $res = $this->get('/admin');
        $res->assertOk()->assertSee('Pendente X')->assertSee((string) Page::count());
    }
}
