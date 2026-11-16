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

if (! function_exists('demo_img')) {
    /**
     * URL of a demo screenshot in public/images/demos ({slug}.webp, or {slug}-480.webp with $suffix '-480'), with ?v=<filemtime>
     * so browsers and the CDN fetch a re-shot image at once even though the file name stays the same.
     */
    function demo_img(string $slug, string $suffix = ''): string
    {
        $file = "images/demos/{$slug}{$suffix}.webp";
        $mtime = @filemtime(public_path($file));

        return asset($file) . ($mtime ? '?v=' . $mtime : '');
    }
}

if (! function_exists('demo_srcset')) {
    /**
     * srcset of a demo screenshot in public/images/demos: the smaller copies ({slug}-480/-640.webp) that exist plus the 960 px original,
     * so phones don't download the full-size image for a 300 px card. Every URL carries ?v=<filemtime> (see demo_img()).
     */
    function demo_srcset(string $slug, array $widths = [480, 640], int $full = 960): string
    {
        $sources = [];
        foreach ($widths as $width) {
            if (is_file(public_path("images/demos/{$slug}-{$width}.webp"))) {
                $sources[] = demo_img($slug, "-{$width}") . " {$width}w";
            }
        }
        $sources[] = demo_img($slug) . " {$full}w";

        return implode(', ', $sources);
    }
}

if (! function_exists('gview')) {
    /** A public page: its design G view (`g.<name>`) when design G is shown, otherwise the current view. */
    function gview(string $name, array $data = [], ?string $current = null)
    {
        return view(\App\Support\Design::is('g') ? 'g.' . $name : ($current ?? $name), $data);
    }
}

if (! function_exists('g_phones')) {
    /**
     * The phone list design G shows: only `creatium.g_phone` (one number, owners 13.11.2026), or all of `contact.phones` when it is empty.
     * The current design keeps listing every phone from `contact.phones`.
     *
     * @return list<string>
     */
    function g_phones(): array
    {
        $one = trim((string) config('creatium.g_phone'));

        return $one !== '' ? [$one] : array_values((array) config('creatium.contact.phones'));
    }
}

if (! function_exists('g_offer_url')) {
    /**
     * Where every „Изпратете запитване“ of design G leads (third round, 16.11.2026: one form everywhere): the form of the page itself
     * when it has one (home `#kontakt`, packages `#oferta`), else the form on the contact page. `$package` preselects a package (`?paket=N`).
     */
    function g_offer_url(?int $package = null): string
    {
        $query = $package !== null ? '?paket=' . $package : '';

        if (request()->routeIs('home', 'en.home')) {
            return $query . '#kontakt';
        }
        if (request()->routeIs('packages', 'en.packages')) {
            return $query . '#oferta';
        }

        return lroute('contact') . $query . '#kontakt';
    }
}
