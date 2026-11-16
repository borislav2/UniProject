<?php

namespace App\Http\Middleware;

use App\Support\Seo\SiteHealth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * На сървъра няма cron, затова посещенията задвижват проверката на SEO здравето: най-много веднъж на 10 минути (и само
 * при заявка GET) след отговора се вика SiteHealth::tick(), който сам пази паузата от 24 часа и ключалка в кеша.
 * Проверката обхожда нашия сайт отвътре, без заявки навън, и никога не чупи страница. SEO_HEALTH_AUTO=false я спира.
 */
class RunSeoHealth
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (SiteHealth::enabled() && $request->isMethod('GET') && Cache::add('seo-health:poke', true, 600)) {
            defer(function () {
                try {
                    SiteHealth::tick();
                } catch (\Throwable $e) {
                    report($e);
                }
            });
        }

        return $response;
    }
}
