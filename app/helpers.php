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
