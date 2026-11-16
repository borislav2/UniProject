<?php

namespace App\Support\Seo;

use App\Models\SeoReport;
use App\Support\Content;

/**
 * Какво реално излиза в Google за всяка страница от Content::SEO_PAGES: ефективното заглавие и описание (ваш текст
 * от админа или по подразбиране от шаблона), като страницата се рисува отвътре (InternalSite) и се чете нейният <title>
 * и meta description. Резултатът се пази за заявката.
 */
class PageInspector
{
    private const KEY = 'seo.inspector';

    /** @return array<string, array<string, array>> страница => език => данни */
    public static function pages(?array $only = null): array
    {
        $content = app(Content::class);
        $keys = $only ?? array_keys(Content::SEO_PAGES);
        $cache = request()->attributes->get(self::KEY, []);
        $missing = array_filter($keys, fn ($page) => ! isset($cache[$page]));

        if ($missing) {
            $report = SeoReport::latestOne();
            $inlinks = $report?->data['inlinks'] ?? [];
            InternalSite::with(function (InternalSite $site) use ($missing, $content, $inlinks, &$cache) {
                $sitemap = self::sitemap($site);
                foreach ($missing as $page) {
                    foreach (['bg', 'en'] as $locale) {
                        $url = lroute($page, [], $locale);
                        $got = $site->get($url);
                        $meta = PageMeta::parse($got['body']);
                        $own = $content->seo($page, $locale);
                        $path = parse_url($url, PHP_URL_PATH) ?: '/';
                        $cache[$page][$locale] = [
                            'url' => $url,
                            'path' => $path,
                            'status' => $got['status'],
                            'title' => $meta['title'],
                            'description' => $meta['description'],
                            'title_own' => filled($own['meta_title'] ?? null),
                            'description_own' => filled($own['meta_description'] ?? null),
                            'canonical' => $meta['canonical'],
                            'canonical_state' => self::canonicalState($meta['canonical'], $url),
                            'noindex' => $meta['noindex'] || str_contains(strtolower($got['robots'] ?? ''), 'noindex'),
                            'in_sitemap' => in_array(rtrim($url, '/') ?: $url, $sitemap, true),
                            'h1' => $meta['h1'][0] ?? null,
                            'h1_count' => count($meta['h1']),
                            'inlinks' => $inlinks[self::key($path)] ?? null,
                        ];
                    }
                }
            });
            request()->attributes->set(self::KEY, $cache);
        }

        return array_intersect_key($cache, array_flip($keys));
    }

    /** Адресите от /sitemap.xml (без наклонена черта накрая). */
    public static function sitemap(InternalSite $site): array
    {
        $got = $site->get('/sitemap.xml');
        preg_match_all('#<loc>\s*([^<\s]+)\s*</loc>#', $got['body'], $m);

        return array_map(fn ($u) => rtrim(html_entity_decode($u), '/') ?: $u, $m[1] ?? []);
    }

    public static function key(string $path): string
    {
        return '/' . trim($path, '/');
    }

    private static function canonicalState(?string $canonical, string $url): string
    {
        if (! $canonical) {
            return 'missing';
        }

        return rtrim($canonical, '/') === rtrim($url, '/') ? 'self' : 'other';
    }

    /** Повторения на ефективните заглавия и описания между страниците (и езиците): ['title' => [ключове], ...]. */
    public static function duplicates(array $pages): array
    {
        $titles = $descriptions = [];
        foreach ($pages as $page => $locales) {
            foreach ($locales as $locale => $row) {
                if ($row['noindex']) {
                    continue;
                }
                $titles["$page|$locale"] = $row['title'];
                $descriptions["$page|$locale"] = $row['description'];
            }
        }

        return ['title' => array_merge(...array_values(Snippet::duplicates($titles)) ?: [[]]), 'description' => array_merge(...array_values(Snippet::duplicates($descriptions)) ?: [[]])];
    }
}
