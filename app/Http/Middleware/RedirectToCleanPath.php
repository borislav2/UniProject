<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * One address per page: /uslugi/ → /uslugi and /index.php/uslugi → /uslugi with a 301 (the query string is kept).
 * Without it the server answers both with 200 and the page lives under two addresses (the canonical tag only softens that).
 * Only GET and HEAD, so a form that posts to a slash address is never broken. The home page (/) and /en stay as they are.
 */
class RedirectToCleanPath
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $uri = $request->getRequestUri();
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $query = $request->getQueryString();

        $clean = preg_replace('#^/index\.php(?=/|$)#', '', $path);
        $clean = preg_replace('#/{2,}#', '/', $clean);
        if ($clean !== '/' && str_ends_with($clean, '/')) {
            $clean = rtrim($clean, '/');
        }
        $clean = $clean === '' ? '/' : $clean;

        if ($clean !== $path) {
            return redirect()->to($clean . ($query !== null && $query !== '' ? '?' . $query : ''), 301);
        }

        return $next($request);
    }
}
