<?php

namespace App\Http\Middleware;

use App\Support\Design;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `?dizain=g` switches the redesign preview on and `?dizain=star` off, only for logged-in admin-panel users;
 * for everyone else the parameter is ignored.
 */
class DesignPreview
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && $request->has('dizain') && $request->hasSession() && Design::canPreview()) {
            match ($request->query('dizain')) {
                'g' => $request->session()->put(Design::SESSION_KEY, 'g'),
                'star' => $request->session()->forget(Design::SESSION_KEY),
                default => null,
            };
        }

        return $next($request);
    }
}
