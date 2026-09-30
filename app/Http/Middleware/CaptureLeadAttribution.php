<?php

namespace App\Http\Middleware;

use App\Support\LeadAttribution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureLeadAttribution
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->is('admin', 'admin/*', 'login') && $request->hasSession()) {
            $attribution = LeadAttribution::fromRequest($request);

            if ($attribution) {
                $request->session()->put(LeadAttribution::SESSION_KEY, $attribution);
            }
        }

        return $next($request);
    }
}
