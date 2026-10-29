<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * www.creatiumlab.com → creatiumlab.com с 301 (същият път и параметри).
 * Nginx отговаря и на двата адреса, а Google индексираше www страниците като отделни,
 * което разделя позициите между две копия на сайта. Само в продукция и само за www на домейна от APP_URL.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($host && app()->environment('production') && strcasecmp($request->getHost(), 'www.' . $host) === 0) {
            return redirect()->to(rtrim((string) config('app.url'), '/') . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
