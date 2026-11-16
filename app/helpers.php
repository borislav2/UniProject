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

if (! function_exists('g_industry_link')) {
    /**
     * The second link of a project card in design G, next to „Вижте сайта“ (17.11.2026): a concept site goes to its industry page
     * `/izrabotka-na-sait-za-...` when it has one (Bulgarian only), else to the website service page with „Изработка на сайт за <бранш>“
     * (`demos[].g_industry_phrase`); a real client project (`$demo` null) says „Изработка на сайт“ and goes to the website service page.
     *
     * @return array{label: string, href: string}
     */
    function g_industry_link(?array $demo = null): array
    {
        $service = \App\Http\Controllers\PageController::serviceUrl('website');

        if ($demo === null) {
            return ['label' => __('Изработка на сайт'), 'href' => $service];
        }

        if (app()->getLocale() === 'bg') {
            foreach ((array) config('creatium.industry_pages') as $slug => $page) {
                if (($page['demo'] ?? null) === ($demo['slug'] ?? '')) {
                    return ['label' => (string) $page['title'], 'href' => route('industry', $slug)];
                }
            }
        }

        $phrase = filled($demo['g_industry_phrase'] ?? null) ? $demo['g_industry_phrase'] : \Illuminate\Support\Str::lower((string) ($demo['industry'] ?? ''));

        return ['label' => __('Изработка на сайт за :industry', ['industry' => $phrase]), 'href' => $service];
    }
}

if (! function_exists('g_cover')) {
    /**
     * The blog cover design G shows. A starter cover (`images/blog/<name>.webp`) has a G drawing in `public/images/blog/g/<name>.webp`
     * (and `<name>-640.webp` for cards); when that file exists it is used, otherwise the original cover. Uploaded covers (`uploads/blog/...`)
     * and any other path stay as they are. `$small` asks for the 640 px copy (falls back to the full G file, then to the original).
     * Returns a public path (with ?v=<filemtime> for a G file) to pass to asset(). The current design never calls this,
     * so it keeps the old path byte for byte.
     *
     * @param  \App\Models\Post|string|null  $cover  a post or its `cover_image`
     */
    function g_cover(\App\Models\Post|string|null $cover, bool $small = false): ?string
    {
        $path = $cover instanceof \App\Models\Post ? $cover->cover_image : $cover;

        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'images/blog/') && ! str_contains(substr($path, 12), '/')) {
            $name = basename($path, '.webp');
            foreach ($small ? ["images/blog/g/{$name}-640.webp", "images/blog/g/{$name}.webp"] : ["images/blog/g/{$name}.webp"] as $file) {
                if (is_file(public_path($file))) {
                    return $file . '?v=' . filemtime(public_path($file));
                }
            }
        }

        return $path;
    }
}

if (! function_exists('g_cover_srcset')) {
    /** srcset (640w + 1200w) of a G starter cover for cards, or null when the post has no G drawing (an upload or an original). */
    function g_cover_srcset(\App\Models\Post|string|null $cover): ?string
    {
        $small = g_cover($cover, true);
        $full = g_cover($cover);

        if ($small === null || $full === null || ! str_contains($small, 'images/blog/g/') || ! str_contains($small, '-640.webp')) {
            return null;
        }

        return asset($small) . ' 640w, ' . asset($full) . ' 1200w';
    }
}
