<?php

namespace App\Http\Middleware;

use App\Support\Admin\SiteMonitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * На сървъра няма cron: най-много веднъж на 15 минути, след като отговорът е изпратен (defer),
 * се проверяват до три сайта на клиенти (SiteMonitor).
 */
class MonitorClientSites
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Cache::add('site-monitor:tick', true, 900) && SiteMonitor::watched()->exists()) {
            defer(fn () => app(SiteMonitor::class)->run());
        }

        return $response;
    }
}
