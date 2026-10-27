<?php

use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\CourseController;
use App\Http\Controllers\Site\DocumentDownloadController;
use App\Http\Controllers\Site\EventController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\NewsController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\SearchController;
use App\Http\Controllers\Site\SeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Website público
|--------------------------------------------------------------------------
| As rotas de detalhe dos módulos são fixas; todas as restantes páginas
| (incluindo as listagens) são páginas do CMS resolvidas pelo caminho
| hierárquico, na rota "catch-all" registada no fim do ficheiro.
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/pesquisa', SearchController::class)->name('search');
Route::post('/contactos', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

Route::get('/comunicacao/noticias/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/comunicacao/comunicados/{slug}', [NewsController::class, 'showAnnouncement'])->name('announcements.show');
Route::get('/comunicacao/eventos/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/instituicao/unidades-organicas/{slug}', [\App\Http\Controllers\Site\UnitController::class, 'show'])->name('units.show');
Route::get('/ensino/cursos/{slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/ensino/cursos/{slug}/plano-curricular.pdf', \App\Http\Controllers\Site\CurriculumPdfController::class)->middleware('throttle:20,1')->name('courses.curriculum.pdf');
Route::get('/documentos/{slug}/download', DocumentDownloadController::class)->middleware('throttle:60,1')->name('documents.download');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| CMS (área administrativa)
|--------------------------------------------------------------------------
*/
require __DIR__.'/admin.php';

/*
|--------------------------------------------------------------------------
| Páginas do CMS (catch-all) — deve ser a última rota.
|--------------------------------------------------------------------------
*/
Route::get('/{path}', [PageController::class, 'show'])
    ->where('path', '^(?!admin(?:/|$))[A-Za-z0-9\-_/]+$')
    ->name('page');
