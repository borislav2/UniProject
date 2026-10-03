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
     * Site content in the current language: config/creatium_en.php for English, falling back to config/creatium.php.
     */
    function site(string $key): mixed
    {
        if (app()->getLocale() === 'en' && ($value = config("creatium_en.$key")) !== null) {
            return $value;
        }

        return config("creatium.$key");
    }
}
