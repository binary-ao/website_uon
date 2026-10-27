<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use App\Services\MenuRepository;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MenuRepository::class);
    }

    public function boot(): void
    {
        Carbon::setLocale('pt');
        setlocale(LC_TIME, 'pt_PT.UTF-8', 'pt_PT', 'Portuguese_Portugal.1252');
        Paginator::useBootstrapFive();
        Paginator::defaultView('partials.pagination');

        // Superadministrador: acesso global.
        Gate::before(function (User $user) {
            return $user->is_active && $user->isSuperAdmin() ? true : null;
        });

        Gate::define('access-admin', fn (User $user) => $user->is_active && $user->roles()->exists());

        Password::defaults(fn () => Password::min(10)->letters()->mixedCase()->numbers());

        RateLimiter::for('contact', function (Request $request) {
            return [Limit::perMinute(3)->by($request->ip()), Limit::perDay(30)->by($request->ip())];
        });
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        // Menus em cache: limpar quando páginas ou menus mudam.
        foreach ([Page::class, Menu::class, MenuItem::class] as $model) {
            $model::saved(fn () => MenuRepository::flush());
            $model::deleted(fn () => MenuRepository::flush());
        }

        View::composer('layouts.public', function ($view) {
            $menus = app(MenuRepository::class);
            $view->with('menus', $menus);
        });
    }
}
