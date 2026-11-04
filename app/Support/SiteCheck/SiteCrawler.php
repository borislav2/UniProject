<?php

namespace App\Support\SiteCheck;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;

/**
 * Обхождане на сайта за безплатната проверка (като Screaming Frog SEO Spider, но по-малко): тръгва от началната страница
 * и от sitemap.xml, следва вътрешните връзки на ширина (най-много crawl_limit страници за crawl_seconds секунди)
 * и събира проблемите по целия сайт.
 *
 * Всяка заявка минава през SiteChecker::fetch() (HostResolver: само публични IPv4, закачени адреси, ръчно следени
 * пренасочвания, портове 80/443). Вътрешните страници се теглят без автоматично следване на пренасочвания, за да не излезем
 * от сайта. Един по един, с нашия user agent, с уважение към robots.txt.
 */
class SiteCrawler
{
    public const MAX_EXAMPLES = 5;

    public const MAX_RESOURCES = 40;

    public const MAX_EXTERNAL = 40;

    public const MAX_QUERY_VARIANTS = 3;

    public const MAX_URL_LENGTH = 500;

    public const SLOW_MS = 1500;

    public const THIN_WORDS = 200;

    public const MAX_DEPTH = 3;

    public const HEAVY_IMAGE_BYTES = 300 * 1024;

    private const MAX_HOPS = 5;

    private const MAX_SITEMAP_URLS = 1000;

    /** Тежестта на всеки проблем: error тежи повече от warning. */
    public const SEVERITY = [
        'server_errors' => 'error', 'broken_pages' => 'error', 'broken_resources' => 'error', 'mixed_content' => 'error',
        'canonical_broken' => 'error', 'sitemap_bad' => 'error', 'no_title' => 'error', 'no_description' => 'error',
        'broken_external' => 'warning', 'redirect_chains' => 'warning', 'links_to_redirects' => 'warning',
        'title_duplicate' => 'warning', 'title_length' => 'warning', 'description_duplicate' => 'warning', 'description_length' => 'warning',
        'no_h1' => 'warning', 'multiple_h1' => 'warning', 'duplicate_h1' => 'warning',
        'canonical_missing' => 'warning', 'canonical_other' => 'warning', 'noindex' => 'warning',
        'images_no_alt' => 'warning', 'images_heavy' => 'warning', 'thin_pages' => 'warning', 'slow_pages' => 'warning',
        'deep_pages' => 'warning', 'missing_from_sitemap' => 'warning', 'no_schema' => 'warning', 'no_og' => 'warning',
    ];

    private const NON_PAGE_EXT = 'jpe?g|png|gif|webp|avif|svg|ico|bmp|css|js|mjs|json|xml|txt|pdf|docx?|xlsx?|pptx?|zip|rar|7z|gz|mp3|mp4|webm|mov|avi|woff2?|ttf|otf|eot';

    private string $family = '';

    private float $started = 0;

    private float $budget = 60;

    private array $rules = ['disallow' => [], 'allow' => []];

    private array $known = [];

    private array $queue = [];

    private array $seedQueue = [];

    private array $pages = [];

    private array $queryVariants = [];

    private array $sitemapKeys = [];

    private array $resources = [];

    private array $externals = [];

    private array $issues = [];

    private int $fetched = 0;

    public function __construct(private SiteChecker $checker)
    {
    }

    /**
     * @return array{crawl: array, issues: array}  Дори при грешка се връща всичко събрано дотук (crawl.error).
     */
    public function crawl(string $startUrl, ?int $limit = null, ?int $seconds = null): array
    {
        $limit ??= (int) config('creatium.site_check.crawl_limit', 50);
        $this->budget = (float) ($seconds ?? config('creatium.site_check.crawl_seconds', 60));
        $this->started = microtime(true);
        $this->reset();

        $start = $this->normalize($startUrl, $startUrl);
        $limited = null;
        $error = null;
        $resourcesChecked = $externalChecked = 0;

        try {
            $this->family = $this->familyOf((string) parse_url($start, PHP_URL_HOST));
            $origin = parse_url($start, PHP_URL_SCHEME) . '://' . parse_url($start, PHP_URL_HOST);

            $robots = $this->fetchInternal($origin . '/robots.txt');
            $robotsBody = $this->ok($robots) ? $robots['response']->body() : '';
            $this->rules = $this->checker->robotsRules($robotsBody, 'CreatiumLabSiteCheck');

            $this->enqueue($start, 0, null);
            $this->readSitemaps($origin, $robotsBody);

            while ($this->queue || $this->seedQueue) {
                if ($this->fetched >= $limit) {
                    $limited = 'pages';
                    break;
                }
                if ($this->elapsed() > $this->budget * 0.7) {
                    $limited = 'time';
                    break;
                }
                $item = array_shift($this->queue) ?? array_shift($this->seedQueue);
                $this->visit($item);
            }

            $resourcesChecked = $this->checkResources();
            $externalChecked = $this->checkExternal();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        try {
            $this->analyze();
        } catch (\Throwable $e) {
            $error ??= $e->getMessage();
        }

        $analyzed = count($this->analyzedPages());

        return [
            'crawl' => [
                'pages' => $this->fetched,
                'analyzed' => $analyzed,
                'limit' => $limit,
                'limited' => $limited !== null,
                'limited_by' => $limited,
                'seconds' => round($this->elapsed(), 1),
                'time_budget' => (int) $this->budget,
                'resources_checked' => $resourcesChecked,
                'external_checked' => $externalChecked,
                'sitemap_urls' => count($this->sitemapKeys),
                'error' => $error,
            ],
            'issues' => $this->issueList(),
        ];
    }

    private function reset(): void
    {
        $this->known = $this->queue = $this->seedQueue = $this->pages = $this->queryVariants = [];
        $this->sitemapKeys = $this->resources = $this->externals = $this->issues = [];
        $this->fetched = 0;
    }

    private function elapsed(): float
    {
        return microtime(true) - $this->started;
    }

    private function timeLeft(): bool
    {
        return $this->elapsed() < $this->budget;
    }

    // ---------------------------------------------------------------- URLs

    private function familyOf(string $host): string
    {
        return preg_replace('/^www\./', '', strtolower($host));
    }

    /** Пълен адрес без фрагмент; null за mailto/tel/javascript, други схеми и твърде дълги адреси. */
    private function normalize(string $href, string $base): ?string
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($href === '' || $href[0] === '#' || preg_match('#^(mailto|tel|javascript|data|sms|viber|whatsapp|blob):#i', $href)) {
            return null;
        }
        $href = preg_replace('/\s/', '%20', $href);

        try {
            $uri = UriResolver::resolve(new Uri($base), new Uri($href));
        } catch (\Throwable) {
            return null;
        }
        $scheme = strtolower($uri->getScheme());
        $host = strtolower($uri->getHost());
        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return null;
        }
        $port = $uri->getPort();
        $url = $scheme . '://' . $host . ($port && ! in_array($port, [80, 443], true) ? ':' . $port : '');
        $url .= ($uri->getPath() === '' ? '/' : $uri->getPath()) . ($uri->getQuery() !== '' ? '?' . $uri->getQuery() : '');

        return strlen($url) > self::MAX_URL_LENGTH ? null : $url;
    }

    private function key(string $url): string
    {
        $p = parse_url($url);
        $path = $p['path'] ?? '/';
        $path = $path === '/' ? '/' : rtrim($path, '/');

        return $this->familyOf($p['host'] ?? '') . $path . (isset($p['query']) ? '?' . $p['query'] : '');
    }

    private function isInternal(string $url): bool
    {
        return $this->familyOf((string) parse_url($url, PHP_URL_HOST)) === $this->family;
    }

    private function pathWithQuery(string $url): string
    {
        $p = parse_url($url);

        return ($p['path'] ?? '/') . (isset($p['query']) ? '?' . $p['query'] : '');
    }

    private function allowedByRobots(string $url): bool
    {
        return SiteChecker::robotsAllows($this->rules, $this->pathWithQuery($url));
    }

    // ---------------------------------------------------------------- queue

    private function enqueue(string $url, ?int $depth, ?string $from, bool $sitemap = false): void
    {
        $key = $this->key($url);
        if (isset($this->known[$key])) {
            if ($this->known[$key]['depth'] === null && $depth !== null) {
                $this->known[$key]['depth'] = $depth;
                // Страница от картата на сайта, до която има и връзка: идва в реда на връзките, не накрая.
                if (($at = array_search($key, $this->seedQueue, true)) !== false) {
                    unset($this->seedQueue[$at]);
                    $this->queue[] = $key;
                }
            }
            $this->known[$key]['sitemap'] = $this->known[$key]['sitemap'] || $sitemap;
            $this->known[$key]['from'] ??= $from;

            return;
        }
        $path = (string) parse_url($url, PHP_URL_PATH);
        $skip = ! $this->isInternal($url)
            || ! $this->allowedByRobots($url)
            || preg_match('#\.(' . self::NON_PAGE_EXT . ')$#i', $path);

        $query = (string) parse_url($url, PHP_URL_QUERY);
        if (! $skip && $query !== '') {
            $pathKey = $this->familyOf((string) parse_url($url, PHP_URL_HOST)) . $path;
            $this->queryVariants[$pathKey][$query] = true;
            $skip = count($this->queryVariants[$pathKey]) > self::MAX_QUERY_VARIANTS;
        }

        $this->known[$key] = ['url' => $url, 'depth' => $depth, 'from' => $from, 'sitemap' => $sitemap, 'skipped' => $skip];
        if (! $skip) {
            if ($sitemap && $depth === null) {
                $this->seedQueue[] = $key;
            } else {
                $this->queue[] = $key;
            }
        }
    }

    // ---------------------------------------------------------------- fetching

    private function ok(array $r): bool
    {
        return $r['response'] !== null && $r['response']->successful();
    }

    /**
     * Тегли адрес от нашия сайт, като следва пренасочванията само ако водят към същия сайт.
     *
     * @return array{response: ?\Illuminate\Http\Client\Response, url: string, ms: int, error: ?string, redirects: int, chain: array, left_site: bool}
     */
    private function fetchInternal(string $url, string $method = 'GET'): array
    {
        $chain = [];
        for ($hop = 0; ; $hop++) {
            $r = $this->checker->fetch($url, follow: false, method: $method);
            $r['redirects'] = $hop;
            $r['chain'] = $chain;
            $r['left_site'] = false;
            $response = $r['response'];
            if (! $response || ! $response->redirect() || ($location = $response->header('Location')) === '' || $hop >= self::MAX_HOPS) {
                return $r;
            }
            $next = $this->normalize($location, $url);
            if (! $next) {
                return $r;
            }
            if (! $this->isInternal($next)) {
                $r['left_site'] = true;

                return $r;
            }
            $chain[] = $url;
            $url = $next;
        }
    }

    private function visit(string $key): void
    {
        $item = $this->known[$key];
        $this->fetched++;
        $r = $this->fetchInternal($item['url']);
        $response = $r['response'];
        $final = $r['url'];
        $finalKey = $this->key($final);

        $base = ['requested' => $item['url'], 'url' => $final, 'from' => $item['from'], 'sitemap' => $item['sitemap'], 'ms' => $r['ms'], 'html' => false, 'status' => $response?->status() ?? 0];

        if ($r['redirects'] > 1) {
            $this->add('redirect_chains', $item['url'], null, $r['redirects'] . ' → ' . $final);
        }
        if ($r['redirects'] >= 1 && $item['from'] !== null) {
            $this->add('links_to_redirects', $item['url'], $item['from'], '→ ' . $final);
        }
        if ($r['redirects'] >= 1 && $item['sitemap']) {
            $this->add('sitemap_bad', $item['url'], null, '→ ' . $final);
        }
        if ($finalKey !== $key && isset($this->pages[$finalKey])) {
            return;
        }
        if ($finalKey !== $key) {
            $this->known[$finalKey] ??= ['url' => $final, 'depth' => $item['depth'], 'from' => $item['from'], 'sitemap' => $item['sitemap'], 'skipped' => false];
        }

        if (! $response || $r['left_site']) {
            $this->pages[$finalKey] = $base;

            return;
        }
        $type = strtolower($response->header('Content-Type'));
        $body = $response->body();
        $looksHtml = str_contains($type, 'html') || ($type === '' && preg_match('/<\s*(!doctype|html|head|body)/i', substr($body, 0, 2000)));
        if (! $response->successful() || ! $looksHtml) {
            $this->pages[$finalKey] = $base;

            return;
        }

        $this->pages[$finalKey] = $base + $this->extract($body, $response, $final, $this->known[$key]['depth'] ?? $item['depth']);
        $this->pages[$finalKey]['html'] = true;
    }

    /** Чете страницата, пази данните за проверките и добавя връзките и ресурсите. */
    private function extract(string $html, $response, string $url, ?int $depth): array
    {
        $doc = $this->checker->parse($html);
        $xpath = new \DOMXPath($doc);
        $clean = fn ($s) => trim(preg_replace('/\s+/u', ' ', (string) $s));

        $title = $clean($xpath->evaluate('string(//title)'));
        $description = $clean($xpath->evaluate('string(//meta[translate(@name,"DESCRIPTION","description")="description"]/@content)'));
        $h1 = $xpath->query('//h1');
        $h1Text = $h1->length ? mb_strtolower($clean($h1->item(0)->textContent)) : '';
        $canonical = $clean($xpath->evaluate('string(//link[translate(@rel,"CANONICAL","canonical")="canonical"]/@href)'));
        $robots = strtolower($xpath->evaluate('string(//meta[translate(@name,"ROBOTS","robots")="robots"]/@content)') . ' ' . $response->header('X-Robots-Tag'));

        $text = '';
        foreach ($xpath->query('//body//text()[not(ancestor::script or ancestor::style or ancestor::noscript)]') as $node) {
            $text .= ' ' . $node->nodeValue;
        }
        $words = count(array_filter(preg_split('/\s+/u', trim($text)) ?: []));

        $images = 0;
        $noAlt = 0;
        foreach ($xpath->query('//img') as $img) {
            $images++;
            $noAlt += $img->hasAttribute('alt') ? 0 : 1;
            $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            if ($src !== '' && ($abs = $this->normalize($src, $url)) && $this->isInternal($abs)) {
                $this->resource($abs, $url, 'image');
            }
        }
        foreach ($xpath->query('//link[translate(@rel,"STYLESHEET","stylesheet")="stylesheet"]/@href') as $a) {
            if (($abs = $this->normalize($a->nodeValue, $url)) && $this->isInternal($abs)) {
                $this->resource($abs, $url, 'css');
            }
        }
        foreach ($xpath->query('//script/@src') as $a) {
            if (($abs = $this->normalize($a->nodeValue, $url)) && $this->isInternal($abs)) {
                $this->resource($abs, $url, 'js');
            }
        }

        $mixed = 0;
        if (str_starts_with($url, 'https://')) {
            foreach ($xpath->query('//img/@src | //script/@src | //iframe/@src | //source/@src | //video/@src | //audio/@src | //embed/@src | //link[translate(@rel,"STYLESHEET","stylesheet")="stylesheet"]/@href') as $a) {
                $mixed += preg_match('#^\s*http://#i', $a->nodeValue);
            }
        }

        foreach ($xpath->query('//a/@href') as $a) {
            $abs = $this->normalize($a->nodeValue, $url);
            if (! $abs) {
                continue;
            }
            if ($this->isInternal($abs)) {
                $this->enqueue($abs, $depth === null ? null : $depth + 1, $url);
            } elseif (count($this->externals) < self::MAX_EXTERNAL) {
                $this->externals[$this->key($abs)] ??= ['url' => $abs, 'from' => $url];
            }
        }

        return [
            'depth' => $depth,
            'title' => $title,
            'description' => $description,
            'h1_count' => $h1->length,
            'h1' => $h1Text,
            'canonical' => $canonical !== '' ? $this->normalize($canonical, $url) : null,
            'noindex' => str_contains($robots, 'noindex'),
            'words' => $words,
            'images' => $images,
            'images_no_alt' => $noAlt,
            'mixed' => $mixed,
            'schema' => (bool) $xpath->query('//script[@type="application/ld+json"]')->length,
            'og' => (bool) $xpath->query('//meta[@property="og:title"]')->length,
        ];
    }

    private function resource(string $url, string $from, string $type): void
    {
        $key = $this->key($url);
        if (count($this->resources) < self::MAX_RESOURCES * 3) {
            $this->resources[$key] ??= ['url' => $url, 'from' => $from, 'type' => $type];
        }
    }

    // ---------------------------------------------------------------- sitemap

    private function readSitemaps(string $origin, string $robotsBody): void
    {
        $urls = [];
        if (preg_match_all('/^\s*sitemap\s*:\s*(\S+)/im', $robotsBody, $m)) {
            foreach ($m[1] as $loc) {
                if (($abs = $this->normalize($loc, $origin . '/')) && $this->isInternal($abs)) {
                    $urls[] = $abs;
                }
            }
        }
        $urls = array_values(array_unique($urls ?: [$origin . '/sitemap.xml']));

        $found = [];
        foreach (array_slice($urls, 0, 3) as $sitemap) {
            $this->readSitemap($sitemap, 1, $found);
        }
        foreach ($found as $url) {
            $this->sitemapKeys[$this->key($url)] = $url;
            $this->enqueue($url, null, null, sitemap: true);
        }
    }

    private function readSitemap(string $url, int $level, array &$found): void
    {
        if (! $this->timeLeft() || count($found) >= self::MAX_SITEMAP_URLS) {
            return;
        }
        $r = $this->fetchInternal($url);
        if (! $this->ok($r)) {
            return;
        }
        $body = $r['response']->body();
        if (! preg_match_all('#<loc>\s*(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?\s*</loc>#is', $body, $m)) {
            return;
        }
        $isIndex = stripos($body, '<sitemapindex') !== false;
        foreach (array_slice($m[1], 0, $isIndex ? 10 : self::MAX_SITEMAP_URLS) as $loc) {
            $abs = $this->normalize($loc, $url);
            if (! $abs || ! $this->isInternal($abs)) {
                continue;
            }
            if ($isIndex) {
                if ($level < 2) {
                    $this->readSitemap($abs, $level + 1, $found);
                }
            } elseif (count($found) < self::MAX_SITEMAP_URLS) {
                $found[$this->key($abs)] = $abs;
            }
        }
    }

    // ---------------------------------------------------------------- resources and external links

    /** Вътрешни снимки, CSS и JS: счупени ли са и колко тежат (HEAD, а ако не е позволен, GET). */
    private function checkResources(): int
    {
        $checked = 0;
        foreach ($this->resources as $key => $res) {
            if ($checked >= self::MAX_RESOURCES || ! $this->timeLeft()) {
                break;
            }
            if (! $this->allowedByRobots($res['url'])) {
                continue;
            }
            $checked++;
            $r = $this->fetchInternal($res['url'], 'HEAD');
            if ($r['response'] && in_array($r['response']->status(), [400, 403, 405, 501], true)) {
                $r = $this->fetchInternal($res['url']);
            }
            $status = $r['response']?->status() ?? 0;
            if ($status === 0 || $status >= 400) {
                $this->add('broken_resources', $res['url'], $res['from'], $status ? 'HTTP ' . $status : null);

                continue;
            }
            if ($res['type'] === 'image') {
                $size = (int) $r['response']->header('Content-Length') ?: strlen($r['response']->body());
                if ($size > self::HEAVY_IMAGE_BYTES) {
                    $this->add('images_heavy', $res['url'], $res['from'], round($size / 1024) . ' KB');
                }
            }
        }

        return $checked;
    }

    /** Външни връзки: само явно счупените (404, 410, 5xx, няма отговор). 401/403/429/999 са често защита срещу ботове. */
    private function checkExternal(): int
    {
        $checked = 0;
        foreach ($this->externals as $link) {
            if ($checked >= self::MAX_EXTERNAL || ! $this->timeLeft()) {
                break;
            }
            $checked++;
            $r = $this->checker->fetch($link['url'], method: 'HEAD');
            if ($r['response'] && in_array($r['response']->status(), [400, 403, 405, 501], true)) {
                $r = $this->checker->fetch($link['url']);
            }
            if ($r['error'] === 'blocked') {
                continue;
            }
            $status = $r['response']?->status() ?? 0;
            if ($status === 0 || in_array($status, [404, 410], true) || $status >= 500) {
                $this->add('broken_external', $link['url'], $link['from'], $status ? 'HTTP ' . $status : null);
            }
        }

        return $checked;
    }

    // ---------------------------------------------------------------- checks

    private function analyzedPages(): array
    {
        return array_filter($this->pages, fn ($p) => $p['html'] && $p['status'] >= 200 && $p['status'] < 300);
    }

    private function add(string $issue, string $url, ?string $from = null, ?string $note = null): void
    {
        $this->issues[$issue]['items'][$url . '|' . $from] ??= array_filter(['url' => $url, 'from' => $from, 'note' => $note], fn ($v) => $v !== null);
    }

    private function analyze(): void
    {
        $pages = $this->analyzedPages();
        $hasSitemap = $this->sitemapKeys !== [];

        foreach ($this->pages as $key => $p) {
            $url = $p['url'];
            if ($p['status'] === 0 || $p['status'] >= 400) {
                $issue = $p['status'] >= 500 ? 'server_errors' : 'broken_pages';
                if ($p['from'] !== null || $p['status'] >= 500) {
                    $this->add($issue, $url, $p['from'], $p['status'] ? 'HTTP ' . $p['status'] : null);
                }
                if ($p['sitemap']) {
                    $this->add('sitemap_bad', $url, null, $p['status'] ? 'HTTP ' . $p['status'] : null);
                }
            }
        }

        $titles = $descriptions = $h1s = [];
        foreach ($pages as $key => $p) {
            $url = $p['url'];
            if ($p['ms'] > self::SLOW_MS) {
                $this->add('slow_pages', $url, null, number_format($p['ms'] / 1000, 1, '.', '') . ' s');
            }
            if ($p['depth'] !== null && $p['depth'] > self::MAX_DEPTH) {
                $this->add('deep_pages', $url, null, (string) $p['depth']);
            }

            $len = mb_strlen($p['title']);
            if ($len === 0) {
                $this->add('no_title', $url);
            } else {
                $titles[mb_strtolower($p['title'])][] = $url;
                if ($len < 15 || $len > 60) {
                    $this->add('title_length', $url, null, (string) $len);
                }
            }
            $len = mb_strlen($p['description']);
            if ($len === 0) {
                $this->add('no_description', $url);
            } else {
                $descriptions[mb_strtolower($p['description'])][] = $url;
                if ($len > 160) {
                    $this->add('description_length', $url, null, (string) $len);
                }
            }

            if ($p['h1_count'] === 0) {
                $this->add('no_h1', $url);
            } else {
                if ($p['h1_count'] > 1) {
                    $this->add('multiple_h1', $url, null, (string) $p['h1_count']);
                }
                if ($p['h1'] !== '') {
                    $h1s[$p['h1']][] = $url;
                }
            }

            if ($p['canonical'] === null) {
                $this->add('canonical_missing', $url);
            } elseif ($this->key($p['canonical']) !== $key) {
                $target = $this->pages[$this->key($p['canonical'])] ?? null;
                if ($target && ($target['status'] === 0 || $target['status'] >= 300)) {
                    $this->add('canonical_broken', $url, null, $p['canonical']);
                } else {
                    $this->add('canonical_other', $url, null, $p['canonical']);
                }
            }

            if ($p['noindex']) {
                $this->add('noindex', $url);
            }
            if ($p['images_no_alt'] > 0) {
                $this->add('images_no_alt', $url, null, $p['images_no_alt'] . '/' . $p['images']);
            }
            if ($p['mixed'] > 0) {
                $this->add('mixed_content', $url, null, (string) $p['mixed']);
            }
            if ($p['words'] < self::THIN_WORDS) {
                $this->add('thin_pages', $url, null, (string) $p['words']);
            }
            if (! $p['schema']) {
                $this->add('no_schema', $url);
            }
            if (! $p['og']) {
                $this->add('no_og', $url);
            }

            if ($p['sitemap'] && $p['noindex']) {
                $this->add('sitemap_bad', $url, null, 'noindex');
            }
            $canonicalSelf = $p['canonical'] === null || $this->key($p['canonical']) === $key;
            if ($hasSitemap && ! $p['sitemap'] && ! isset($this->sitemapKeys[$key]) && ! $p['noindex'] && $canonicalSelf) {
                $this->add('missing_from_sitemap', $url);
            }
        }

        foreach ([['title_duplicate', $titles], ['description_duplicate', $descriptions], ['duplicate_h1', $h1s]] as [$issue, $groups]) {
            foreach ($groups as $urls) {
                if (count($urls) > 1) {
                    foreach ($urls as $url) {
                        $this->add($issue, $url, null, (string) count($urls));
                    }
                }
            }
        }
    }

    private function issueList(): array
    {
        $list = [];
        foreach ($this->issues as $key => $data) {
            $items = array_values($data['items']);
            $list[] = [
                'key' => $key,
                'severity' => self::SEVERITY[$key] ?? 'warning',
                'count' => count($items),
                'examples' => array_slice($items, 0, self::MAX_EXAMPLES),
            ];
        }
        usort($list, fn ($a, $b) => [$a['severity'] === 'error' ? 0 : 1, -$a['count']] <=> [$b['severity'] === 'error' ? 0 : 1, -$b['count']]);

        return $list;
    }

    // ---------------------------------------------------------------- texts

    /**
     * Заглавие и обяснение на разбираем език за проблем от обхождането (на езика на текущата заявка).
     *
     * @return array{title: string, why: string}
     */
    public static function describe(string $key, int $count): array
    {
        $n = $count;
        $t = match ($key) {
            'server_errors' => [__('Страници със сървърна грешка: :n', compact('n')), __('Сървърът се чупи на тези адреси. Клиентите виждат грешка вместо страница, а Google ги маха от резултатите.')],
            'broken_pages' => [__('Счупени вътрешни връзки (404): :n', compact('n')), __('Връзки в сайта водят към страници, които не съществуват. Посетителят остава на празна страница и често си тръгва, а Google намалява доверието в сайта.')],
            'broken_resources' => [__('Счупени снимки, стилове или скриптове: :n', compact('n')), __('Част от файловете, нужни на страницата, не се зареждат. Така липсват снимки или сайтът изглежда и работи накриво.')],
            'mixed_content' => [__('Страници със смесено съдържание (http на https страница): :n', compact('n')), __('Страницата е защитена, но зарежда снимки или скриптове по незащитен адрес. Браузърът може да ги блокира или да покаже предупреждение.')],
            'canonical_broken' => [__('Канонична връзка към несъществуваща страница: :n', compact('n')), __('Страницата казва на Google, че истинската ѝ версия е на адрес, който не работи. Google може да я изхвърли от резултатите.')],
            'sitemap_bad' => [__('Грешни адреси в картата на сайта: :n', compact('n')), __('Картата на сайта съдържа адреси, които не отговарят нормално (липсват, пренасочват или са скрити от Google). Това обърква търсачката кои страници да показва.')],
            'no_title' => [__('Страници без заглавие за Google: :n', compact('n')), __('Заглавието е синият ред в резултатите на Google. Без него търсачката избира сама и клиентите по-рядко кликват.')],
            'no_description' => [__('Страници без описание за Google: :n', compact('n')), __('Описанието са двата реда текст под заглавието в Google. Без него Google избира откъс сам, често неудачен.')],
            'broken_external' => [__('Счупени връзки към други сайтове: :n', compact('n')), __('Връзки от вашия сайт водят към страници, които вече не работят. Изглежда занемарено и пречи на посетителите.')],
            'redirect_chains' => [__('Вериги от пренасочвания: :n', compact('n')), __('Адресът пренасочва през няколко стъпки, преди да стигне до страницата. Това забавя зареждането и губи част от силата на връзката за Google.')],
            'links_to_redirects' => [__('Вътрешни връзки към пренасочени адреси: :n', compact('n')), __('Връзките в сайта сочат към стар адрес, който после пренасочва. Всяка такава връзка е излишна стъпка за посетителя и за Google.')],
            'title_duplicate' => [__('Страници с еднакво заглавие: :n', compact('n')), __('Когато много страници имат едно и също заглавие, Google трудно разбира коя за какво е и клиентите не виждат разлика в резултатите.')],
            'title_length' => [__('Заглавия с неподходяща дължина: :n', compact('n')), __('Добрата дължина е 15–60 знака. По-дългото се отрязва в Google, а по-късото не казва достатъчно.')],
            'description_duplicate' => [__('Страници с еднакво описание: :n', compact('n')), __('Еднаквите описания не казват какво е различното на всяка страница и намаляват кликовете от Google.')],
            'description_length' => [__('Описания, по-дълги от 160 знака: :n', compact('n')), __('Google отрязва дългите описания, често точно където е важното.')],
            'no_h1' => [__('Страници без основно заглавие (H1): :n', compact('n')), __('Основното заглавие казва на хората и на Google за какво е страницата.')],
            'multiple_h1' => [__('Страници с няколко основни заглавия (H1): :n', compact('n')), __('Препоръчва се едно главно заглавие на страница, а останалите да са подзаглавия.')],
            'duplicate_h1' => [__('Страници с еднакво основно заглавие (H1): :n', compact('n')), __('Всяка страница трябва да има свое заглавие, иначе не личи с какво се различава.')],
            'canonical_missing' => [__('Страници без канонична връзка: :n', compact('n')), __('Каноничната връзка показва на Google коя е главната версия на страницата и предпазва от дублирано съдържание.')],
            'canonical_other' => [__('Страници с канонична връзка към друг адрес: :n', compact('n')), __('Тези страници казват на Google да показва друг адрес вместо тях. Ако не е нарочно, страниците ви няма да се появят в търсенето.')],
            'noindex' => [__('Страници, скрити от Google (noindex): :n', compact('n')), __('Тези страници казват на Google да не ги показва. Ако това не е нарочно, клиентите няма как да ги намерят.')],
            'images_no_alt' => [__('Страници със снимки без описание (alt): :n', compact('n')), __('Описанието на снимката помага на Google и на незрящите посетители да разберат какво има на нея.')],
            'images_heavy' => [__('Снимки, по-тежки от 300 KB: :n', compact('n')), __('Тежките снимки забавят страницата, особено на телефон с мобилен интернет, и хората си тръгват, преди да я видят.')],
            'thin_pages' => [__('Страници с много малко текст (под 200 думи): :n', compact('n')), __('Страница с малко текст дава на Google малко причини да я покаже и на клиента малко причини да ви повярва.')],
            'slow_pages' => [__('Бавни страници (над 1,5 секунди): :n', compact('n')), __('Всяка секунда чакане губи посетители. Причината обикновено е хостингът, тежка тема или добавки.')],
            'deep_pages' => [__('Страници, дълбоко в сайта (над 3 клика от началото): :n', compact('n')), __('Страници, до които се стига трудно, рядко се посещават и Google ги намира по-бавно.')],
            'missing_from_sitemap' => [__('Страници, които липсват в картата на сайта: :n', compact('n')), __('Картата на сайта е списък на страниците за Google. Което не е в нея, се намира по-бавно.')],
            'no_schema' => [__('Страници без данни за Google и AI (schema): :n', compact('n')), __('Специален код казва на търсачките и на AI асистентите какво е страницата. Помага в Google и в отговорите на AI.')],
            'no_og' => [__('Страници без настроено споделяне (Open Graph): :n', compact('n')), __('Когато някой изпрати линка в чат или във Facebook, няма подбрана снимка и заглавие.')],
            default => [$key, ''],
        };

        return ['title' => $t[0], 'why' => $t[1]];
    }
}
