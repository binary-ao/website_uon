<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['guest', 'admin.headers'])->group(function () {
    Route::get('login', [Auth\LoginController::class, 'show'])->name('login');
    Route::post('login', [Auth\LoginController::class, 'login'])->name('login.attempt');
    Route::get('recuperar-palavra-passe', [Auth\PasswordResetController::class, 'request'])->name('password.request');
    Route::post('recuperar-palavra-passe', [Auth\PasswordResetController::class, 'email'])->middleware('throttle:password-reset')->name('password.email');
    Route::get('redefinir-palavra-passe/{token}', [Auth\PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('redefinir-palavra-passe', [Auth\PasswordResetController::class, 'update'])->middleware('throttle:10,1')->name('password.update');
});

Route::post('admin/logout', [Auth\LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Área administrativa protegida
| Autorização no servidor: middleware "can:" + policies em cada acção.
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:access-admin', 'admin.headers'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    // Perfil do utilizador autenticado
    Route::get('perfil', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('perfil', [Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('perfil/palavra-passe', [Admin\ProfileController::class, 'password'])->name('profile.password');

    // Conteúdos editoriais (CRUD + fluxo editorial)
    foreach ([
        'pages' => Admin\PageController::class,
        'news' => Admin\NewsController::class,
        'events' => Admin\EventController::class,
        'documents' => Admin\DocumentController::class,
        'courses' => Admin\CourseController::class,
        'units' => Admin\OrganicUnitController::class,
        'gallery' => Admin\GalleryItemController::class,
    ] as $name => $controller) {
        Route::post("$name/{id}/restore", [$controller, 'restore'])->name("$name.restore");
        Route::delete("$name/{id}/force", [$controller, 'forceDelete'])->name("$name.force-delete");
        Route::resource($name, $controller)->except(['show']);
    }
    Route::post('pages/{page}/duplicate', [Admin\PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::post('news/{news}/duplicate', [Admin\NewsController::class, 'duplicate'])->name('news.duplicate');
    Route::post('documents/{document}/versions', [Admin\DocumentController::class, 'addVersion'])->name('documents.versions.store');
    Route::post('documents/{document}/versions/{version}/current', [Admin\DocumentController::class, 'makeCurrent'])->name('documents.versions.current');

    // Pré-visualizações privadas
    Route::get('preview/{type}/{id}', Admin\PreviewController::class)
        ->whereIn('type', ['pages', 'news', 'events', 'courses', 'units'])->whereNumber('id')->name('preview');

    // Revisões
    Route::get('revisions/{revision}', [Admin\RevisionController::class, 'show'])->name('revisions.show');
    Route::post('revisions/{revision}/restore', [Admin\RevisionController::class, 'restore'])->name('revisions.restore');

    // Biblioteca multimédia
    Route::get('media', [Admin\MediaController::class, 'index'])->middleware('can:media.upload')->name('media.index');
    Route::get('media/picker', [Admin\MediaController::class, 'picker'])->middleware('can:media.upload')->name('media.picker');
    Route::post('media', [Admin\MediaController::class, 'store'])->middleware(['can:media.upload', 'throttle:60,1'])->name('media.store');
    Route::get('media/{media}/edit', [Admin\MediaController::class, 'edit'])->middleware('can:media.upload')->name('media.edit');
    Route::put('media/{media}', [Admin\MediaController::class, 'update'])->middleware('can:media.upload')->name('media.update');
    Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->middleware('can:media.manage')->name('media.destroy');

    // Estrutura do website
    Route::middleware('can:menus.manage')->group(function () {
        Route::get('menus', [Admin\MenuController::class, 'index'])->name('menus.index');
        Route::get('menus/{menu}', [Admin\MenuController::class, 'edit'])->name('menus.edit');
        Route::put('menus/{menu}', [Admin\MenuController::class, 'update'])->name('menus.update');
        Route::post('menus/{menu}/items', [Admin\MenuController::class, 'storeItem'])->name('menus.items.store');
        Route::put('menus/{menu}/items/{item}', [Admin\MenuController::class, 'updateItem'])->name('menus.items.update');
        Route::post('menus/{menu}/items/{item}/move', [Admin\MenuController::class, 'moveItem'])->name('menus.items.move');
        Route::delete('menus/{menu}/items/{item}', [Admin\MenuController::class, 'destroyItem'])->name('menus.items.destroy');
    });

    Route::middleware('can:settings.manage')->group(function () {
        Route::get('configuracoes/{group?}', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('configuracoes/{group}', [Admin\SettingController::class, 'update'])->name('settings.update');
    });

    Route::middleware('can:banners.manage')->group(function () {
        Route::post('hero-slides/{heroSlide}/move', [Admin\HeroSlideController::class, 'move'])->name('hero-slides.move');
        Route::resource('hero-slides', Admin\HeroSlideController::class)->except(['show']);
    });

    Route::resource('notices', Admin\NoticeController::class)->except(['show'])->middleware('can:notices.manage');
    Route::resource('partners', Admin\PartnerController::class)->except(['show'])->middleware('can:partners.manage');

    Route::middleware('can:taxonomies.manage')->group(function () {
        Route::resource('categories', Admin\CategoryController::class)->except(['show']);
        Route::resource('tags', Admin\TagController::class)->only(['index', 'update', 'destroy']);
        Route::resource('degrees', Admin\DegreeController::class)->except(['show']);
    });

    Route::resource('redirects', Admin\RedirectController::class)->except(['show'])->middleware('can:redirects.manage');

    Route::middleware('can:messages.manage')->group(function () {
        Route::get('messages', [Admin\ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Admin\ContactMessageController::class, 'show'])->name('messages.show');
        Route::put('messages/{message}', [Admin\ContactMessageController::class, 'update'])->name('messages.update');
        Route::delete('messages/{message}', [Admin\ContactMessageController::class, 'destroy'])->name('messages.destroy');
    });

    // Utilizadores, funções e auditoria
    Route::resource('users', Admin\UserController::class)->except(['show'])->middleware('can:users.manage');
    Route::middleware('can:roles.manage')->group(function () {
        Route::get('roles', [Admin\RoleController::class, 'index'])->name('roles.index');
        Route::put('roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');
    });
    Route::get('activity', [Admin\ActivityLogController::class, 'index'])->middleware('can:activity.view')->name('activity.index');
});
