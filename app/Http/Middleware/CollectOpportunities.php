<?php

namespace App\Http\Middleware;

use App\Support\Opportunities\Collector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * На сървъра няма cron, затова посещенията задвижват събирането на възможности: най-много веднъж на 5 минути след отговора
 * се вика Collector::tick(), който сам пази паузата от interval_hours (6) на източник. Никога не праща и не подава нищо.
 * Само при заявки GET (да не бави действие на човек).
 */
class CollectOpportunities
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Collector::enabled() && $request->isMethod('GET') && Cache::add('opportunities:poke', true, 300)) {
            defer(fn () => app(Collector::class)->tick());
        }

        return $response;
    }
}
