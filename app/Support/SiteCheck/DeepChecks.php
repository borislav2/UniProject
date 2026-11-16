<?php

namespace App\Support\SiteCheck;

/**
 * По-дълбоките проверки на обхождането (стъпка 3 от SEO плана): дубликати на адреси, canonical, hreflang, структурирани данни,
 * връзки между страниците, robots.txt и AI ботове, карта на сайта, сигурност, проследяване, местни сигнали, език, дублирано съдържание.
 *
 * Всичко се смята от страниците, които обхождането вече е прочело (PageSignals). Допълнителни заявки са най-много 8
 * (меки 404, llms.txt, варианти на адреса) и влизат в същия бюджет на времето.
 * Използва се само от SiteCrawler (ползва неговите add(), key(), normalize(), fetchInternal(), pages и т.н.).
 */
trait DeepChecks
{
    public const MAX_EXTRA_REQUESTS = 8;

    public const MAX_SITEMAP_URLS_META = 1000;

    /** Целева страница => кои страници сочат към нея (ключове на адреси). */
    private array $inlinks = [];

    /** Поискан адрес (ключ) => крайният адрес след пренасочване. */
    private array $redirects = [];

    private array $signals = [];

    private array $sitemapInfo = [];

    private array $probes = [];

    private bool $complete = false;

    private string $startUrl = '';

    private ?array $robotsInfo = null;

    private int $extraRequests = 0;

    private function resetDeep(): void
    {
        $this->inlinks = $this->redirects = $this->probes = [];
        $this->signals = ['tracking' => [], 'consent' => [], 'other_analytics' => false,
            'flags' => ['privacy' => false, 'terms' => false, 'contact' => false, 'map' => false, 'tel' => false],
            'local' => ['address' => false, 'hours' => false, 'map' => false]];
        $this->sitemapInfo = ['urls' => 0, 'with_lastmod' => 0, 'future' => [], 'foreign' => [], 'locs' => 0, 'files' => []];
        $this->complete = false;
        $this->startUrl = '';
        $this->robotsInfo = null;
        $this->extraRequests = 0;
    }

    /** Обобщение на открити сигнали за отчета: само имена, никога идентификатори. */
    private function signalSummary(): array
    {
        return [
            'tracking' => array_values($this->signals['tracking']),
            'consent' => array_values($this->signals['consent']),
            'other_analytics' => $this->signals['other_analytics'],
            'local' => $this->signals['local'] + ['phone' => $this->signals['flags']['tel']],
            'llms' => $this->probes['llms'] ?? null,
        ];
    }

    /** Събира сигналите от една страница: проследяване, местни данни и езикови версии, които още не са обходени. */
    private function absorb(array $x, string $url, ?int $depth): void
    {
        foreach ($x['hreflang'] as $target) {
            if ($target && $this->isInternal($target)) {
                $this->enqueue($target, null, $url);
            }
        }
        foreach ($x['tracking']['names'] as $k => $label) {
            $this->signals['tracking'][$k] = $label;
        }
        foreach ($x['tracking']['consent'] as $c) {
            $this->signals['consent'][$c] = $c;
        }
        $this->signals['other_analytics'] = $this->signals['other_analytics'] || $x['tracking']['other_analytics'];
        foreach (['privacy', 'terms', 'map', 'tel'] as $f) {
            $this->signals['flags'][$f] = $this->signals['flags'][$f] || $x['flags'][$f];
        }
        if ($depth === 0) {
            $this->signals['flags']['contact'] = (bool) $x['flags']['contact'];
        }
        foreach (['address', 'hours', 'map'] as $f) {
            $this->signals['local'][$f] = $this->signals['local'][$f] || $x['local'][$f];
        }
    }

    private function addSite(string $issue, string $url, int $count, ?string $note = null, array $more = []): void
    {
        $this->issues[$issue]['count'] = $count;
        $this->issues[$issue]['items'][$url . '|'] = array_filter(['url' => $url, 'note' => $note], fn ($v) => $v !== null);
        foreach ($more as $extra) {
            $this->issues[$issue]['items'][$extra['url'] . '|'] ??= $extra;
        }
    }

    // ---------------------------------------------------------------- sitemap

    private function readSitemapMeta(string $body, string $url, int $locs): void
    {
        $this->sitemapInfo['files'][] = $url;
        $this->sitemapInfo['locs'] = max($this->sitemapInfo['locs'], $locs);
        if (strlen($body) > 50 * 1024 * 1024) {
            $this->sitemapInfo['locs'] = max($this->sitemapInfo['locs'], 50001);
        }
        if (! preg_match_all('#<url>(.*?)</url>#is', $body, $blocks)) {
            return;
        }
        foreach (array_slice($blocks[1], 0, self::MAX_SITEMAP_URLS_META) as $block) {
            $this->sitemapInfo['urls']++;
            if (preg_match('#<lastmod>\s*([^<\s]+)\s*</lastmod>#i', $block, $d)) {
                $this->sitemapInfo['with_lastmod']++;
                $ts = strtotime($d[1]);
                if ($ts !== false && $ts > time() + 86400) {
                    $loc = preg_match('#<loc>\s*(?:<!\[CDATA\[)?(.*?)(?:\]\]>)?\s*</loc>#is', $block, $l) ? trim($l[1]) : $url;
                    $this->sitemapInfo['future'][] = ['url' => $loc, 'note' => substr($d[1], 0, 10)];
                }
            }
        }
    }

    // ---------------------------------------------------------------- probes (extra requests)

    private function canProbe(): bool
    {
        return $this->extraRequests < self::MAX_EXTRA_REQUESTS && $this->timeLeft();
    }

    private function probeFetch(string $url): array
    {
        $this->extraRequests++;

        return $this->fetchInternal($url);
    }

    private function looksLikeHtml(array $r): bool
    {
        if (! $this->ok($r)) {
            return false;
        }
        $type = strtolower($r['response']->header('Content-Type'));

        return str_contains($type, 'html') || ($type === '' && preg_match('/<\s*(!doctype|html|head|body)/i', substr($r['response']->body(), 0, 2000)));
    }

    /** Заглавие и canonical на вариант на адрес (за да видим дали е покрит с canonical към основния). */
    private function variantMeta(array $r, string $base): array
    {
        $xpath = new \DOMXPath($this->checker->parse($r['response']->body()));
        $canonical = trim($xpath->evaluate('string(//link[translate(@rel,"CANONICAL","canonical")="canonical"]/@href)'));
        $robots = strtolower($xpath->evaluate('string(//meta[translate(@name,"ROBOTS","robots")="robots"]/@content)') . ' ' . $r['response']->header('X-Robots-Tag'));

        return [
            'title' => mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $xpath->evaluate('string(//title)')))),
            'canonical' => $canonical !== '' ? $this->normalize($canonical, $base) : null,
            'noindex' => str_contains($robots, 'noindex'),
        ];
    }

    /** Отговаря ли вариантът със същото съдържание като основния адрес и без да е обединен (пренасочване, canonical или noindex). */
    private function isDuplicateOf(array $r, string $variantUrl, string $preferredUrl, string $preferredTitle): bool
    {
        if (! $this->looksLikeHtml($r) || $r['redirects'] > 0 || $r['left_site']) {
            return false;
        }
        $meta = $this->variantMeta($r, $variantUrl);
        if ($meta['noindex'] || ($meta['canonical'] !== null && $meta['canonical'] === $preferredUrl)) {
            return false;
        }

        return $preferredTitle !== '' && $meta['title'] === $preferredTitle;
    }

    private function homePage(): ?array
    {
        foreach ($this->pages as $p) {
            if (($p['depth'] ?? null) === 0 && ! empty($p['html'])) {
                return $p;
            }
        }

        return null;
    }

    private function probe(string $origin, string $start): void
    {
        $this->probes = ['soft404' => null, 'llms' => null];
        $home = $this->homePage();
        if (! $home) {
            return;
        }
        $scheme = (string) parse_url($start, PHP_URL_SCHEME);
        $host = (string) parse_url($start, PHP_URL_HOST);
        $preferred = $home['url'];
        $title = mb_strtolower($home['title']);

        // 1. Несъществуващ адрес: трябва да е 404, не нормална страница и не пренасочване към началото.
        if ($this->canProbe()) {
            $r = $this->probeFetch($origin . '/' . bin2hex(random_bytes(4)) . '-proverka-404');
            $toHome = $this->ok($r) && $r['redirects'] >= 1 && ($this->pathWithQuery($r['url']) === '/');
            $soft = $this->ok($r) && ($this->looksLikeHtml($r) || $toHome);
            $this->probes['soft404'] = $soft;
            if ($soft) {
                $this->addSite('soft_404', $origin . '/…-proverka-404', 1, 'HTTP ' . $r['response']->status() . ($toHome ? ' → /' : ''));
            }
        }

        // 2. llms.txt (по желание).
        if ($this->canProbe()) {
            $r = $this->probeFetch($origin . '/llms.txt');
            $body = $this->ok($r) ? ltrim($r['response']->body()) : '';
            $this->probes['llms'] = $body !== '' && ! str_starts_with($body, '<') && ! $this->looksLikeHtml($r);
        }

        // 3. www и без www.
        if ($this->canProbe()) {
            $alt = str_starts_with($host, 'www.') ? substr($host, 4) : 'www.' . $host;
            $url = $scheme . '://' . $alt . '/';
            $r = $this->probeFetch($url);
            if ($this->isDuplicateOf($r, $url, $preferred, $title)) {
                $this->add('dup_www', $url, null, 'HTTP 200');
            }
        }

        // 4. http вместо https.
        if ($scheme === 'https' && $this->canProbe()) {
            $url = 'http://' . $host . '/';
            $r = $this->probeFetch($url);
            if ($this->isDuplicateOf($r, $url, $preferred, $title)) {
                $this->add('dup_http', $url, null, 'HTTP 200');
            }
        }

        // 5. и 6. /index.php и /index.html (не ако сайтът връща 200 за всичко).
        foreach (['/index.php', '/index.html'] as $path) {
            if ($this->probes['soft404'] === true || ! $this->canProbe()) {
                break;
            }
            $url = $origin . $path;
            $r = $this->probeFetch($url);
            if ($this->isDuplicateOf($r, $url, $preferred, $title)) {
                $this->add('dup_index', $url, null, 'HTTP 200');
            }
        }

        // 7. Една вътрешна страница със и без наклонена черта в края.
        if ($this->probes['soft404'] !== true && $this->canProbe()) {
            foreach ($this->pages as $p) {
                $path = (string) parse_url($p['url'], PHP_URL_PATH);
                if (empty($p['html']) || ($p['depth'] ?? 0) < 1 || $p['noindex'] || parse_url($p['url'], PHP_URL_QUERY) || $path === '' || $path === '/'
                    || preg_match('#\.[a-z0-9]{2,5}$#i', $path) || ($p['canonical'] !== null && $p['canonical'] !== $p['url'])) {
                    continue;
                }
                $variantPath = str_ends_with($path, '/') ? rtrim($path, '/') : $path . '/';
                $url = $origin . $variantPath;
                $r = $this->probeFetch($url);
                if ($this->isDuplicateOf($r, $url, $p['url'], mb_strtolower($p['title']))) {
                    $this->add('dup_slash', $url, null, 'HTTP 200');
                }
                break;
            }
        }
    }

    // ---------------------------------------------------------------- canonical

    /** Канонична връзка към друг адрес: какъв е проблемът (друг домейн, пренасочване, счупен адрес, верига) или обикновено „друг адрес“. */
    private function canonicalIssue(array $p): void
    {
        $url = $p['url'];
        $c = $p['canonical'];
        $ck = $this->key($c);

        if (! $this->isInternal($c)) {
            $this->add('canonical_other_host', $url, null, $c);

            return;
        }
        if (isset($this->redirects[$ck])) {
            $this->add('canonical_redirect', $url, null, $c);

            return;
        }
        $target = $this->pages[$ck] ?? null;
        if ($target && ($target['status'] === 0 || $target['status'] >= 300)) {
            $this->add('canonical_broken', $url, null, $c);

            return;
        }
        if ($target && ! empty($target['html']) && ! empty($target['canonical']) && $this->key($target['canonical']) !== $ck) {
            $this->add('canonical_chain', $url, null, $c . ' → ' . $target['canonical']);

            return;
        }
        $this->add('canonical_other', $url, null, $c);
    }

    private function inbound(string $key): int
    {
        $sources = $this->inlinks[$key] ?? [];
        foreach ($this->redirects as $from => $to) {
            if ($to === $key && isset($this->inlinks[$from])) {
                $sources += $this->inlinks[$from];
            }
        }
        unset($sources[$key]);

        return count($sources);
    }

    // ---------------------------------------------------------------- analysis

    private function analyzeDeep(array $pages): void
    {
        $home = $this->homePage();
        $homeKey = $home ? $this->key($home['url']) : null;

        // Страниците, които Google може да покаже: без noindex и без вариантите, които сочат с canonical към същия адрес без параметри.
        $idx = array_filter($pages, fn ($p) => ! $p['noindex'] && ! ($p['canonical'] !== null && $this->key($p['canonical']) !== $this->key($p['url']) && $this->samePath($p['canonical'], $p['url'])));
        $count = count($idx);

        $this->checkCanonicalNoindex($pages, $idx);
        $this->checkDuplicateQuery($idx);
        $this->checkHreflang($idx);
        $this->checkStructuredData($idx, $home);
        $this->checkContentSignals($idx, $count);
        $this->checkImages($idx);
        if ($this->complete) {
            $this->checkLinks($idx, $homeKey);
        }
        $this->checkRobots();
        $this->checkSitemapHygiene();
        if ($home) {
            $this->checkSecurity($home);
            $this->checkSiteSignals($home);
        }
        $this->checkLanguage($idx);
    }

    private function checkCanonicalNoindex(array $pages, array $idx): void
    {
        foreach ($pages as $key => $p) {
            if ($p['canonical'] === null) {
                continue;
            }
            $ck = $this->key($p['canonical']);
            if ($p['noindex'] && $ck !== $key) {
                $this->add('canonical_noindex', $p['url'], null, $p['canonical']);
            } elseif (! $p['noindex'] && $ck !== $key && ($target = $this->pages[$ck] ?? null) && ! empty($target['noindex'])) {
                $this->add('canonical_noindex', $p['url'], null, $p['canonical']);
            }
        }
    }

    /** Адреси с параметри, които повтарят страница без параметри (или една с друга) и не сочат с canonical към чистия адрес. */
    private function checkDuplicateQuery(array $idx): void
    {
        $byPath = [];
        foreach ($idx as $key => $p) {
            $byPath[strtok($key, '?')][] = $p;
        }
        foreach ($byPath as $variants) {
            if (count($variants) < 2) {
                continue;
            }
            foreach ($variants as $p) {
                if (! str_contains((string) $p['url'], '?')) {
                    continue;
                }
                foreach ($variants as $other) {
                    if ($other['url'] !== $p['url'] && $other['title'] !== '' && mb_strtolower($other['title']) === mb_strtolower($p['title'])) {
                        $this->add('dup_query', $p['url'], $other['url']);

                        break;
                    }
                }
            }
        }
    }

    private function checkHreflang(array $idx): void
    {
        $resolve = fn ($k) => $this->redirects[$k] ?? $k;
        $sets = array_filter($idx, fn ($p) => ! empty($p['hreflang']));
        $langs = [];
        foreach ($sets as $key => $p) {
            $url = $p['url'];
            $bad = array_filter(array_keys($p['hreflang']), fn ($code) => ! $this->validLang($code));
            if ($bad) {
                $this->add('hreflang_invalid', $url, null, implode(', ', array_slice($bad, 0, 3)));
            }
            $self = false;
            $codes = [];
            foreach ($p['hreflang'] as $code => $target) {
                if ($target === null) {
                    continue;
                }
                $tk = $resolve($this->key($target));
                $self = $self || $tk === $key;
                if ($code !== 'x-default') {
                    $codes[$code] = true;
                }
                if ($tk === $key) {
                    continue;
                }
                $t = $this->pages[$tk] ?? null;
                if ($t && ($t['status'] === 0 || $t['status'] >= 300 || ! empty($t['noindex']))) {
                    $this->add('hreflang_bad_target', $t['url'], $url, ! empty($t['noindex']) ? 'noindex' : 'HTTP ' . $t['status']);
                } elseif ($t && ! empty($t['html'])) {
                    $backs = array_filter(array_map(fn ($u) => $u ? $resolve($this->key($u)) : null, $t['hreflang'] ?? []));
                    if (! in_array($key, $backs, true)) {
                        $this->add('hreflang_no_return', $t['url'], $url);
                    }
                }
            }
            if (! $self) {
                $this->add('hreflang_no_self', $url, null, implode(', ', array_slice(array_keys($p['hreflang']), 0, 4)));
            }
            if (count($codes) >= 2 && ! isset($p['hreflang']['x-default'])) {
                $langs[] = $url;
            }
        }
        // x-default: само когато сайтът има поне две езикови версии на страница.
        foreach (array_slice($langs, 0, 50) as $url) {
            $this->add('hreflang_no_default', $url);
        }
    }

    private function validLang(string $code): bool
    {
        if ($code === 'x-default') {
            return true;
        }
        if (! preg_match('/^([a-z]{2,3})(-[a-z]{4})?(-([a-z]{2}|\d{3}))?$/', $code, $m)) {
            return false;
        }

        return ! in_array($m[1], ['jp', 'cn', 'kr', 'gr', 'dk', 'cz', 'ua', 'us'], true) && ($m[4] ?? '') !== 'uk';
    }

    private function checkStructuredData(array $idx, ?array $home): void
    {
        $inner = 0;
        $withLd = false;
        foreach ($idx as $p) {
            $ld = $p['ld'] ?? null;
            if (! $ld) {
                continue;
            }
            $withLd = $withLd || $ld['count'] > 0;
            $inner += ($p['depth'] ?? 0) >= 1 ? 1 : 0;
            if ($ld['errors'] > 0) {
                $this->add('schema_invalid', $p['url'], null, (string) $ld['errors']);
            }
            if ($ld['incomplete'] > 0) {
                $this->add('schema_incomplete', $p['url'], null, (string) $ld['incomplete']);
            }
            if ($ld['faq_missing'] > 0) {
                $this->add('schema_faq_mismatch', $p['url'], null, $ld['faq_missing'] . '/' . $ld['faq_total']);
            }
        }
        if ($home && ($ld = $home['ld'] ?? null) && $ld['count'] > 0 && $ld['errors'] < $ld['count']) {
            if ($ld['org'] === null) {
                $this->add('schema_no_org', $home['url']);
            } elseif ($missing = array_diff(['name', 'address', 'telephone', 'url'], $ld['org'])) {
                $this->add('schema_org_incomplete', $home['url'], null, implode(', ', $missing));
            }
        }
        if ($withLd && $inner >= 2) {
            foreach ($idx as $p) {
                if (($p['depth'] ?? 0) >= 1 && isset($p['ld']) && ! in_array('BreadcrumbList', $p['ld']['types'], true)) {
                    $this->add('schema_no_breadcrumb', $p['url']);
                }
            }
        }
    }

    private function checkContentSignals(array $idx, int $count): void
    {
        // Заглавието е същото като H1 почти навсякъде.
        $withBoth = array_filter($idx, fn ($p) => $p['title'] !== '' && $p['h1'] !== '');
        $same = array_filter($withBoth, fn ($p) => mb_strtolower($p['title']) === $p['h1']);
        if (count($withBoth) >= 3 && count($same) / count($withBoth) >= 0.8) {
            foreach ($same as $p) {
                $this->add('title_equals_h1', $p['url'], null, (string) count($same));
            }
        }

        foreach ($idx as $p) {
            if (($p['heading_skips'] ?? 0) > 0) {
                $this->add('heading_skips', $p['url'], null, (string) $p['heading_skips']);
            }
            if (($p['weak_anchors'] ?? 0) > 0) {
                $first = $p['weak_samples'][0]['text'] ?? '';
                $this->add('weak_anchors', $p['url'], null, $first !== '' ? $first : 'без текст');
            }
        }

        // Почти еднакъв текст между две страници.
        $seen = [];
        $withSketch = array_filter($idx, fn ($p) => ! empty($p['sketch']) && ($p['canonical'] === null || $this->key($p['canonical']) === $this->key($p['url'])));
        foreach ($withSketch as $p) {
            foreach ($seen as $other) {
                $sim = PageSignals::similarity($p['sketch'], $other['sketch']);
                if ($sim >= 0.9) {
                    $this->add('duplicate_content', $p['url'], $other['url'], round($sim * 100) . '%');

                    break;
                }
            }
            $seen[] = $p;
        }
    }

    private function checkImages(array $idx): void
    {
        $raster = array_sum(array_column($idx, 'img_raster'));
        $modern = array_sum(array_column($idx, 'img_modern'));
        foreach ($idx as $p) {
            if (($p['img_no_size'] ?? 0) > 0) {
                $this->add('images_no_size', $p['url'], null, $p['img_no_size'] . '/' . $p['img_raster']);
            }
            if (! empty($p['img_first_lazy'])) {
                $this->add('images_lcp_lazy', $p['url']);
            }
        }
        if ($raster >= 5 && $modern / $raster < 0.3) {
            foreach ($idx as $p) {
                if (($p['img_raster'] ?? 0) > 0 && $p['img_modern'] === 0) {
                    $this->add('images_legacy', $p['url'], null, (string) $p['img_raster']);
                }
            }
        }
    }

    /** Сираци и малко входящи връзки: само когато цялият сайт е обходен (иначе картата на връзките е непълна). */
    private function checkLinks(array $idx, ?string $homeKey): void
    {
        foreach ($idx as $key => $p) {
            if ($key === $homeKey) {
                continue;
            }
            $in = $this->inbound($key);
            if ($in === 0 && $p['sitemap']) {
                $this->add('orphan_pages', $p['url']);
            } elseif ($in < 2 && count($idx) >= 6) {
                $this->add('few_inlinks', $p['url'], null, (string) $in);
            }
        }
    }

    private function checkRobots(): void
    {
        $robots = $this->robotsInfo;
        if ($robots) {
            if (in_array($robots['status'], [404, 410], true)) {
                $this->addSite('robots_missing', $robots['url'], 1);
            } elseif ($robots['status'] >= 200 && $robots['status'] < 300 && ! preg_match('/^\s*sitemap\s*:/im', $robots['body'])) {
                $this->addSite('robots_no_sitemap', $robots['url'], 1);
            }
        }
        foreach ($this->resources as $res) {
            if (in_array($res['type'], ['css', 'js'], true) && ! $this->allowedByRobots($res['url'])) {
                $this->add('robots_blocks_assets', $res['url'], $res['from']);
            }
        }
        if (($this->probes['llms'] ?? null) === false && $this->probes['soft404'] !== true) {
            $this->addSite('llms_missing', ($this->robotsInfo['url'] ?? '') ? str_replace('robots.txt', 'llms.txt', $this->robotsInfo['url']) : '/llms.txt', 1);
        } elseif (($this->probes['llms'] ?? null) === false) {
            $this->addSite('llms_missing', str_replace('robots.txt', 'llms.txt', $this->robotsInfo['url'] ?? '/robots.txt'), 1);
        }
    }

    private function checkSitemapHygiene(): void
    {
        $info = $this->sitemapInfo;
        if ($info['urls'] > 0 && $info['with_lastmod'] === 0) {
            $this->addSite('sitemap_no_lastmod', $info['files'][0] ?? '/sitemap.xml', $info['urls']);
        }
        if ($info['future']) {
            $this->issues['sitemap_lastmod_future']['count'] = count($info['future']);
            foreach (array_slice($info['future'], 0, self::MAX_EXAMPLES) as $f) {
                $this->issues['sitemap_lastmod_future']['items'][$f['url'] . '|'] = $f;
            }
        }
        if ($info['locs'] > 50000) {
            $this->addSite('sitemap_too_big', $info['files'][0] ?? '/sitemap.xml', $info['locs']);
        }
        if ($info['foreign']) {
            $this->issues['sitemap_other_host']['count'] = count($info['foreign']);
            foreach (array_slice($info['foreign'], 0, self::MAX_EXAMPLES) as $u) {
                $this->issues['sitemap_other_host']['items'][$u . '|'] = ['url' => $u];
            }
        }
    }

    private function checkSecurity(array $home): void
    {
        $h = $home['headers'] ?? null;
        if (! $h || ! str_starts_with($home['url'], 'https://')) {
            return;
        }
        if (! $h['hsts']) {
            $this->addSite('no_hsts', $home['url'], 1);
        }
        $missing = array_filter([
            ! $h['nosniff'] ? 'X-Content-Type-Options' : null,
            ! $h['frame'] ? 'X-Frame-Options' : null,
            ! $h['referrer'] ? 'Referrer-Policy' : null,
        ]);
        if ($missing) {
            $this->addSite('no_security_headers', $home['url'], count($missing), implode(', ', $missing));
        }
    }

    /** Проследяване, местни сигнали и доверие: от всички обходени страници, отбелязани на началната. */
    private function checkSiteSignals(array $home): void
    {
        $url = $home['url'];
        $s = $this->signals;
        $trackers = array_diff_key($s['tracking'], ['ga_old' => 1]);

        if (! $trackers && ! $s['other_analytics'] && ! isset($s['tracking']['ga_old'])) {
            $this->addSite('tracking_none', $url, 1);
        }
        if (isset($s['tracking']['ga_old']) && ! isset($s['tracking']['ga4']) && ! isset($s['tracking']['gtm'])) {
            $this->addSite('tracking_old_ga', $url, 1, $s['tracking']['ga_old']);
        }
        if ($trackers && ! $s['consent']) {
            $this->addSite('tracking_no_consent', $url, 1, implode(', ', $trackers));
        }

        if (! $s['local']['address']) {
            $this->addSite('local_no_address', $url, 1);
        }
        if (! $s['local']['map'] && ! $s['flags']['map']) {
            $this->addSite('local_no_map', $url, 1);
        }
        if (! $s['local']['hours']) {
            $this->addSite('local_no_hours', $url, 1);
        }
        if (! $s['flags']['privacy']) {
            $this->addSite('trust_no_privacy', $url, 1);
        }
        if (! $s['flags']['terms']) {
            $this->addSite('trust_no_terms', $url, 1);
        }
        if (! $s['flags']['contact']) {
            $this->addSite('trust_no_contact', $url, 1);
        }
    }

    private function checkLanguage(array $idx): void
    {
        $cyrLangs = ['bg', 'ru', 'uk', 'sr', 'mk', 'be'];
        foreach ($idx as $p) {
            $lang = explode('-', (string) ($p['lang'] ?? ''))[0];
            if ($lang === '') {
                $this->add('lang_missing', $p['url']);

                continue;
            }
            $cyr = $p['cyr'] ?? null;
            if ($cyr === null) {
                continue;
            }
            $mismatch = in_array($lang, $cyrLangs, true) ? $cyr < 0.2 : $cyr > 0.6;
            if ($mismatch) {
                $this->add('lang_mismatch', $p['url'], null, $p['lang'] . ' · ' . round($cyr * 100) . '%');
            }
        }
    }
}
