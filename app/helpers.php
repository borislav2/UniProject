<?php

if (! function_exists('lroute')) {
    /**
     * URL of a public page in the current (or given) language: lroute('services') is /uslugi in Bulgarian and /en/services in English.
     */
    function lroute(string $name, mixed $parameters = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route(($locale === 'en' ? 'en.' : '') . $name, $parameters);
    }
}

if (! function_exists('site')) {
    /**
     * Site content in the current language: admin edits first, then config/creatium_en.php for English,
     * then config/creatium.php. See App\Support\Content.
     */
    function site(string $key): mixed
    {
        return app(\App\Support\Content::class)->get($key);
    }
}

if (! function_exists('demo_srcset')) {
    /**
     * srcset of a demo screenshot in public/images/demos: the smaller copies ({slug}-480/-640.webp) that exist plus the 960 px original,
     * so phones don't download the full-size image for a 300 px card.
     */
    function demo_srcset(string $slug, array $widths = [480, 640], int $full = 960): string
    {
        $sources = [];
        foreach ($widths as $width) {
            if (is_file(public_path("images/demos/{$slug}-{$width}.webp"))) {
                $sources[] = asset("images/demos/{$slug}-{$width}.webp") . " {$width}w";
            }
        }
        $sources[] = asset("images/demos/{$slug}.webp") . " {$full}w";

        return implode(', ', $sources);
    }
}
