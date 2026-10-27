<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** A área administrativa e as pré-visualizações nunca são indexadas nem guardadas em cache. */
class AdminResponseHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        if (! $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
            && ! $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
