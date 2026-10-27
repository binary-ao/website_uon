<?php

use App\Http\Middleware\AdminResponseHeaders;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use App\Models\Redirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            EnsureUserIsActive::class,
        ]);
        $middleware->alias([
            'admin.headers' => AdminResponseHeaders::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Antes de mostrar 404, verifica se existe um redireccionamento configurado no CMS.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
                return null;
            }
            try {
                $redirect = Redirect::where('from_path', Redirect::normalizePath($request->path()))
                    ->where('is_active', true)->first();
            } catch (\Throwable) {
                return null;
            }
            if (! $redirect) {
                return null;
            }
            $redirect->increment('hits', 1, ['last_hit_at' => now()]);
            $target = \App\Support\SafeUrl::resolve($redirect->to_url);

            return $target ? redirect()->to($target, in_array($redirect->status_code, [301, 302], true) ? $redirect->status_code : 301) : null;
        });
    })->create();
