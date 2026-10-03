<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The public site is Bulgarian at the root and English under /en.
 * Runs as global middleware so 404 pages under /en are English too.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($request->segment(1) === 'en' ? 'en' : 'bg');

        return $next($request);
    }
}
