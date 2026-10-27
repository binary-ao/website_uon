<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Degree;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\OrganicUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BannerCurriculumUnitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStructure();
    }

    private function image(): Media
    {
        return app(\App\Services\MediaService::class)->store(UploadedFile::fake()->image('b.jpg', 1920, 800), [], ['image']);
    }

    public function test_banner_slides_are_managed_in_cms_and_rendered_as_carousel(): void
    {
        $admin = $this->userWithRole('admin');
        $this->actingAs($admin)->get('/admin/hero-slides')->assertOk();
        $img = $this->image();

        $this->post('/admin/hero-slides', ['title' => 'Sem imagem', 'image_position' => 'center'])->assertSessionHasErrors('media_id');
        $this->post('/admin/hero-slides', ['title' => 'Mau', 'media_id' => $img->id, 'image_position' => 'center', 'primary_label' => 'X', 'primary_url' => 'javascript:alert(1)'])->assertSessionHasErrors('primary_url');

        foreach (['Primeiro diapositivo', 'Segundo diapositivo'] as $t) {
            $this->post('/admin/hero-slides', ['eyebrow' => 'UON', 'title' => $t, 'title_highlight' => 'destaque', 'text' => 'Texto', 'media_id' => $img->id,
                'image_position' => 'right', 'primary_label' => 'Conheça a UON', 'primary_url' => '/instituicao/sobre', 'is_active' => 1])->assertRedirect();
        }
        (new HeroSlide(['title' => 'Inactivo', 'media_id' => $img->id, 'is_active' => false]))->save();
        (new HeroSlide(['title' => 'Expirado', 'media_id' => $img->id, 'is_active' => true, 'ends_at' => now()->subDay()]))->save();

        $home = $this->get('/')->assertOk();
        $home->assertSee('id="heroCarousel"', false)->assertSee('data-bs-ride="carousel"', false)
            ->assertSeeInOrder(['Primeiro diapositivo', 'Segundo diapositivo'])->assertSee('/ 02')
            ->assertDontSee('Inactivo')->assertDontSee('Expirado')->assertSee('Conheça a UON');

        // Reordenar
        $second = HeroSlide::where('title', 'Segundo diapositivo')->first();
        $this->post("/admin/hero-slides/{$second->id}/move", ['direction' => 'up'])->assertRedirect();
        $this->get('/')->assertSeeInOrder(['Segundo diapositivo', 'Primeiro diapositivo']);

        // Imagem em uso não pode ser eliminada
        $this->delete("/admin/media/{$img->id}")->assertSessionHas('error');

        // Sem diapositivos: banner do template (configurações)
        HeroSlide::query()->delete();
        $this->get('/')->assertDontSee('id="heroCarousel"', false)->assertSee('<section class="hero">', false);

        // Autores não gerem o banner
        $this->actingAs($this->userWithRole('author'))->get('/admin/hero-slides')->assertForbidden();
    }

    public function test_curriculum_tabs_and_pdf_download(): void
    {
        $course = new Course(['name' => 'Curso PDF', 'degree_id' => Degree::first()->id, 'duration' => '4 anos'] + $this->published());
        $course->save();
        $course->subjects()->createMany([
            ['year' => 1, 'semester' => 1, 'code' => 'CP111', 'name' => 'Disciplina A', 'hours' => 60, 'credits' => 4, 'sort_order' => 0],
            ['year' => 1, 'semester' => 2, 'code' => 'CP121', 'name' => 'Disciplina B', 'hours' => 90, 'credits' => 6, 'sort_order' => 1],
            ['year' => 2, 'semester' => 1, 'code' => 'CP211', 'name' => 'Optativa C', 'hours' => 45, 'credits' => 3, 'is_optional' => true, 'sort_order' => 2],
        ]);

        $page = $this->get($course->url())->assertOk();
        $page->assertSee('uon-years', false)->assertSee('1.º Ano')->assertSee('2.º Ano')->assertSee('3.º Semestre')
            ->assertSee('CP121')->assertSee('uon-type-op', false)->assertSee(route('courses.curriculum.pdf', $course->slug), false);

        $pdf = $this->get(route('courses.curriculum.pdf', $course->slug));
        $pdf->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertStringContainsString('plano-curricular-curso-pdf.pdf', $pdf->headers->get('Content-Disposition'));

        // Curso não publicado → sem PDF
        $course->update(['status' => 'draft']);
        $this->get(route('courses.curriculum.pdf', $course->slug))->assertNotFound();
    }

    public function test_curriculum_editor_saves_code_and_optional(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $this->post('/admin/courses', ['name' => 'Curso UC', 'degree_id' => Degree::first()->id, 'status' => 'draft', 'subjects' => [
            ['year' => 1, 'semester' => 1, 'code' => 'X1', 'name' => 'UC Obrigatória', 'hours' => 60],
            ['year' => 1, 'semester' => 2, 'code' => 'X2', 'name' => 'UC Optativa', 'hours' => 60, 'is_optional' => 1],
        ]])->assertRedirect();
        $c = Course::where('name', 'Curso UC')->first();
        $this->assertSame(['X1', 'X2'], $c->subjects->pluck('code')->all());
        $this->assertSame([false, true], $c->subjects->pluck('is_optional')->all());
    }

    public function test_unit_cards_show_image_and_unit_page_shows_three_courses_map_and_contacts(): void
    {
        $img = $this->image();
        $fac = new OrganicUnit(['type' => 'faculdade', 'name' => 'Faculdade Alfa', 'short_name' => 'FA', 'featured_media_id' => $img->id,
            'location' => 'Edifício A, Campus', 'phone' => '+244 900 000 001 / +244 900 000 002', 'email' => 'fa@exemplo.test',
            'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=1,2,3,4'] + $this->published());
        $fac->save();
        $dept = new OrganicUnit(['type' => 'departamento', 'name' => 'Departamento Beta', 'parent_id' => $fac->id] + $this->published());
        $dept->save();
        foreach (range(1, 4) as $i) {
            (new Course(['name' => "Curso $i", 'degree_id' => Degree::first()->id, 'organic_unit_id' => $i % 2 ? $fac->id : $dept->id, 'sort_order' => $i] + $this->published()))->save();
        }

        $list = $this->get('/instituicao/unidades-organicas')->assertOk();
        $list->assertSee('unit-card', false)->assertSee($img->url('md'), false)->assertSee('4 cursos');

        $page = $this->get($fac->url())->assertOk();
        $page->assertSee('Curso 1')->assertSee('Curso 2')->assertSee('Curso 3')->assertDontSee('Curso 4')
            ->assertSee('Ver todos os cursos (4)')->assertSee('?unidade='.$fac->slug, false)
            ->assertSee('openstreetmap.org/export/embed.html', false)->assertSee('Abrir no Google Maps')
            ->assertSee('tel:+244900000002', false)->assertSee('fa@exemplo.test');

        // "Ver todos os cursos" inclui os cursos dos departamentos
        $this->get('/ensino/oferta-formativa?unidade='.$fac->slug)->assertSee('4 resultados');

        // Mapa: apenas URLs de incorporação permitidos
        $this->actingAs($this->userWithRole('admin'))->put("/admin/units/{$fac->id}", ['type' => 'faculdade', 'name' => 'Faculdade Alfa', 'status' => 'published', 'map_embed_url' => 'https://evil.example/x'])
            ->assertSessionHasErrors('map_embed_url');
    }

    public function test_course_cards_are_fully_clickable(): void
    {
        (new Course(['name' => 'Curso Clicável', 'degree_id' => Degree::first()->id] + $this->published()))->save();
        $this->get('/ensino/oferta-formativa')->assertSee('card-footer-link stretched-link', false)->assertSee('card-link', false);
    }
}
