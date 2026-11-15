<?php

namespace App\Http\Middleware;

use App\Support\Outreach\Autopilot;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * На сървъра няма cron, затова посещенията задвижват автопилота: най-много веднъж на минута (и само при включен автопилот
 * в работно време) след отговора се вика Autopilot::tick(), който сам пази паузата от 10 минути. Автопилотът никога не праща писма.
 */
class RunOutreachAutopilot
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Autopilot::enabled() && Autopilot::inHours() && Cache::add('outreach-autopilot:poke', true, 60)) {
            defer(fn () => app(Autopilot::class)->tick());
        }

        return $response;
    }
}
