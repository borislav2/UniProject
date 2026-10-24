<?php

namespace App\Http\Middleware;

use App\Support\Facebook\FacebookPublisher;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * На сървъра няма cron, затова посещенията задвижват публикуването във Facebook:
 * най-много веднъж на 10 минути, след като отговорът е изпратен (defer), се пуска една чакаща статия.
 */
class PublishBlogToFacebook
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (FacebookPublisher::configured() && ! FacebookPublisher::paused() && Cache::add('facebook:tick', true, 600)) {
            defer(fn () => app(FacebookPublisher::class)->run());
        }

        return $response;
    }
}
