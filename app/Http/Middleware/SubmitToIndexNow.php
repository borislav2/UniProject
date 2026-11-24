<?php

namespace App\Http\Middleware;

use App\Support\Seo\IndexNow;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * На сървъра няма cron, затова посещенията подават новите статии към IndexNow (Bing, Yandex, Seznam):
 * най-много веднъж на 10 минути, при заявка GET и след изпращане на отговора. Грешка никога не чупи страница.
 */
class SubmitToIndexNow
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (IndexNow::enabled() && $request->isMethod('GET') && Cache::add('indexnow:tick', true, 600)) {
            defer(function () {
                try {
                    IndexNow::submitNewPosts();
                } catch (\Throwable $e) {
                    report($e);
                }
            });
        }

        return $response;
    }
}
