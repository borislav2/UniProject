<?php

namespace App\Http\Middleware;

use App\Support\LinkedIn\LinkedInPublisher;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Като PublishBlogToFacebook: на сървъра няма cron, затова посещенията задвижват публикуването в LinkedIn,
 * най-много веднъж на 10 минути, след като отговорът е изпратен (defer).
 */
class PublishBlogToLinkedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (LinkedInPublisher::ready() && ! LinkedInPublisher::paused() && Cache::add('linkedin:tick', true, 600)) {
            defer(fn () => app(LinkedInPublisher::class)->run());
        }

        return $response;
    }
}
