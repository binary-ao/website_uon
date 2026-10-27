<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\News;
use App\Models\OrganicUnit;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_content_replaces_demo_content(): void
    {
        $this->seedStructure();
        $this->seed(DemoContentSeeder::class);
        $this->assertTrue(News::where('is_demo', true)->exists());

        $this->artisan('uon:official-content', ['--force' => true])->assertSuccessful();
        \App\Support\Settings::flush();

        $this->assertFalse(News::where('is_demo', true)->exists());
        $this->assertSame('geral@uon.edu.ao', setting('contact_email'));
        $this->assertSame(4, OrganicUnit::whereIn('type', ['faculdade', 'instituto'])->count());
        $this->assertTrue(Course::where('name', 'Medicina Geral')->where('duration', '6 anos')->exists());
        $this->get('/')->assertSee('Universidade 11 de Novembro')->assertSee('Decreto Presidencial n.º 7/09');
        $this->get('/instituicao/missao-visao-valores')->assertSee('Ser reconhecida até 2030');
        $this->get('/instituicao/unidades-organicas/faculdade-de-direito')->assertSee('f.direito@uon.edu.ao')->assertSee('Ciências Jurídico-Forenses');
        $this->get('/contactos')->assertSee('(+244) 928 220 700');
    }

    public function test_mixed_content_keeps_official_data_and_examples_where_missing(): void
    {
        $this->seedStructure();
        $this->seed(DemoContentSeeder::class);

        $this->artisan('uon:official-content', ['--force' => true, '--with-examples' => true])->assertSuccessful();
        \App\Support\Settings::flush();

        // Listas oficiais: só itens oficiais.
        $this->assertSame(4, OrganicUnit::whereIn('type', ['faculdade', 'instituto'])->count());
        $this->assertSame(13, Course::count());
        $this->assertFalse(Course::where('is_demo', true)->exists());
        $this->assertFalse(News::where('is_demo', true)->exists());
        $this->assertFalse(\App\Models\HeroSlide::where('is_demo', true)->exists());
        $this->assertSame('Prof. Doutor Kianvu Tamo', OrganicUnit::where('slug', 'reitoria')->value('head_name'));

        // O que o site oficial não tem: exemplos.
        $this->assertTrue(\App\Models\Event::where('is_demo', true)->exists());
        $this->assertTrue(\App\Models\Document::where('is_demo', true)->exists());
        $this->assertTrue(\App\Models\GalleryItem::where('is_demo', true)->exists());
        $fd = OrganicUnit::where('slug', 'faculdade-de-direito')->first();
        $this->assertTrue(OrganicUnit::where('parent_id', $fd->id)->where('name', 'Departamento de Direito')->exists());
        $this->assertStringContainsString('(exemplo)', $fd->head_name);

        $direito = Course::where('slug', 'direito')->first();
        $this->assertSame('5 anos', $direito->duration);
        $this->assertSame(50, $direito->subjects()->count());
        $this->get('/ensino/cursos/direito')->assertOk()->assertSee('Plano curricular de exemplo');
        $this->get('/ensino/cursos/direito/plano-curricular.pdf')->assertOk();
        $this->get('/')->assertOk()->assertSee('Decreto Presidencial n.º 7/09')->assertDontSee('Faculdade de Engenharia e Tecnologias');
        $this->get('/instituicao/mensagem-direccao')->assertOk()->assertDontSee('Joaquim Puati');

        // Remover os exemplos repõe apenas o conteúdo oficial.
        $this->artisan('uon:demo-content', ['--remove' => true, '--force' => true])->assertSuccessful();
        $direito->refresh();
        $this->assertNull($direito->objectives);
        $this->assertSame(0, $direito->subjects()->count());
        $this->assertNull(OrganicUnit::where('slug', 'faculdade-de-direito')->value('head_name'));
        $this->assertSame(13, Course::count());
        $this->get('/instituicao/missao-visao-valores')->assertSee('Ser reconhecida até 2030');
    }
}
