<?php

namespace App\Http\Middleware;

use App\Support\SiteCheck\FollowUps;
use App\Support\SiteCheck\MonthlyChecks;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * На сървъра няма cron, затова посещенията пускат и работата след безплатната проверка на сайт: писмата след отчета
 * (най-много 10 на тик, 09:00 до 19:00 по София) и месечната проверка на абонатите (един сайт на тик). Най-много веднъж на 10 минути
 * за всяка работа, при заявка GET и след изпращане на отговора. Грешка никога не чупи страница.
 */
class SendSiteCheckFollowUps
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET')) {
            if (FollowUps::enabled() && FollowUps::inWindow() && Cache::add('site-check-followups:tick', true, 600)) {
                defer(function () {
                    try {
                        app(FollowUps::class)->run();
                    } catch (\Throwable $e) {
                        report($e);
                    }
                });
            }
            if (MonthlyChecks::enabled() && Cache::add('monthly-checks:tick', true, 600)) {
                defer(function () {
                    try {
                        app(MonthlyChecks::class)->run(1);
                    } catch (\Throwable $e) {
                        report($e);
                    }
                });
            }
        }

        return $response;
    }
}
