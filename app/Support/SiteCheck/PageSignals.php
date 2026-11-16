<?php

namespace App\Support\SiteCheck;

/**
 * Допълнителните данни от всяка обходена страница за по-дълбоката проверка (стъпка 3 от SEO плана):
 * заглавия, hreflang, структурирани данни, снимки, връзки, проследяване, местни сигнали, език, подпис на текста.
 * Чисти функции върху вече изтеглената страница, без допълнителни заявки.
 */
class PageSignals
{
    public const GENERIC_ANCHORS = [
        'тук', 'виж повече', 'вижте повече', 'прочети повече', 'прочетете повече', 'научи повече', 'научете повече', 'разбери повече',
        'още', 'повече', 'виж още', 'кликнете тук', 'натисни тук', 'натиснете тук', 'детайли', 'подробности',
        'here', 'click here', 'read more', 'learn more', 'more', 'see more', 'view more', 'find out more', 'details', 'link',
    ];

    private const ORG_TYPES = ['Organization', 'Corporation', 'LocalBusiness', 'ProfessionalService', 'Store', 'OnlineStore', 'OnlineBusiness', 'Restaurant', 'Dentist', 'LegalService', 'Physician', 'MedicalBusiness', 'RealEstateAgent', 'TravelAgency', 'EmploymentAgency'];

    /** Име (за показване, никога адресът или номерът) и откриване в HTML. */
    private const TRACKERS = [
        'ga4' => ['Google Analytics 4', '/googletagmanager\.com\/gtag\/js\?id=g-|gtag\(\s*[\'"]config[\'"]\s*,\s*[\'"]g-/i'],
        'gtm' => ['Google Tag Manager', '/googletagmanager\.com\/(gtm\.js|ns\.html)|[\'"]gtm-[a-z0-9]{4,}/i'],
        'ga_old' => ['Universal Analytics', '/google-analytics\.com\/(analytics|ga)\.js|[\'"]ua-\d{4,}-\d+/i'],
        'meta' => ['Meta Pixel', '/connect\.facebook\.net\/[a-z_]+\/fbevents\.js|\bfbq\(\s*[\'"]init/i'],
        'ads' => ['Google Ads', '/googleadservices\.com|googleads\.g\.doubleclick|[\'"]aw-\d{6,}/i'],
        'clarity' => ['Microsoft Clarity', '/clarity\.ms\/tag|clarity\(\s*[\'"]/i'],
        'hotjar' => ['Hotjar', '/static\.hotjar\.com|hj\(\s*[\'"]identify/i'],
        'tiktok' => ['TikTok Pixel', '/analytics\.tiktok\.com/i'],
        'linkedin' => ['LinkedIn Insight', '/snap\.licdn\.com|px\.ads\.linkedin\.com/i'],
    ];

    /** Друга статистика, която не се нуждае от банер в повечето случаи: брои се като измерване. */
    private const OTHER_ANALYTICS = '/plausible\.io\/js|matomo\.js|piwik\.js|cdn\.matomo|usefathom\.com|cdn\.usefathom|umami\.is|cloudflareinsights\.com\/beacon|simpleanalytics/i';

    private const CONSENT_TOOLS = [
        'Cookiebot' => '/consent\.cookiebot\.com|cookiebot/i',
        'CookieYes' => '/cookieyes\.com|cky-consent|cookie-law-info/i',
        'OneTrust' => '/cookielaw\.org|onetrust/i',
        'Usercentrics' => '/usercentrics/i',
        'Osano' => '/osano\.com|cookieconsent/i',
        'Iubenda' => '/iubenda/i',
        'Termly' => '/termly\.io/i',
        'Complianz' => '/complianz|cmplz/i',
        'Borlabs' => '/borlabs/i',
        'Klaro' => '/klaro\.(js|css)|kiprotect/i',
        'Consent Mode' => '/gtag\(\s*[\'"]consent[\'"]/i',
    ];

    /** @return array{names: list<string>, consent: list<string>, other_analytics: bool} */
    public static function tracking(string $html): array
    {
        $names = [];
        foreach (self::TRACKERS as $key => [$label, $re]) {
            if (preg_match($re, $html)) {
                $names[$key] = $label;
            }
        }
        $consent = [];
        foreach (self::CONSENT_TOOLS as $label => $re) {
            if (preg_match($re, $html)) {
                $consent[$label] = $label;
            }
        }
        if (! $consent && preg_match('/(?:class|id)\s*=\s*["\'][^"\']*(?:cookie[-_ ]?(?:banner|consent|notice|bar|law|popup|policy-bar)|cc-(?:window|banner)|gdpr[-_]?(?:banner|consent))[^"\']*["\']/i', $html)) {
            $consent['banner'] = 'banner';
        }

        return ['names' => $names, 'consent' => array_values($consent), 'other_analytics' => (bool) preg_match(self::OTHER_ANALYTICS, $html)];
    }

    /**
     * @param  callable(string): ?string  $abs  превръща връзка от страницата в пълен адрес (или null)
     * @return array<string, mixed>
     */
    public static function collect(\DOMXPath $xpath, string $html, string $fullText, string $url, callable $abs, $response, bool $isStart): array
    {
        $clean = fn ($s) => trim(preg_replace('/\s+/u', ' ', (string) $s));

        // Текстът на страницата без менюто, долния колонтитул и страничните блокове: за дублираното съдържание и езика.
        $main = '';
        foreach ($xpath->query('//body//text()[not(ancestor::script or ancestor::style or ancestor::noscript or ancestor::nav or ancestor::header or ancestor::footer or ancestor::aside)]') as $node) {
            $main .= ' ' . $node->nodeValue;
            if (strlen($main) > 60000) {
                break;
            }
        }
        $main = $clean($main);

        $out = [
            'lang' => strtolower($clean($xpath->evaluate('string(/html/@lang)'))),
            'cyr' => self::cyrillicShare($main),
            'main_words' => 0,
            'sketch' => [],
        ];
        [$out['main_words'], $out['sketch']] = self::sketch($main);

        // Заглавия: пропуснати нива (h1 към h3).
        $skips = 0;
        $prev = 0;
        foreach ($xpath->query('//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]') as $h) {
            $level = (int) substr($h->nodeName, 1);
            if ($prev > 0 && $level > $prev + 1) {
                $skips++;
            }
            $prev = $level;
        }
        $out['heading_skips'] = $skips;

        // hreflang.
        $out['hreflang'] = [];
        foreach ($xpath->query('//link[translate(@rel,"ALTERNATE","alternate")="alternate"][@hreflang]') as $link) {
            $code = strtolower($clean($link->getAttribute('hreflang')));
            $href = $abs($link->getAttribute('href'));
            $out['hreflang'][$code] = $href;
        }

        $out['ld'] = self::structuredData($xpath, $fullText);
        $out += self::images($xpath, $abs, $url);
        $out += self::anchors($xpath, $abs, $url);

        $out['tracking'] = self::tracking($html);
        $out['local'] = self::local($main, $out['ld'], $html);
        if ($isStart) {
            $out['headers'] = [
                'hsts' => $response->header('Strict-Transport-Security') !== '',
                'nosniff' => stripos($response->header('X-Content-Type-Options'), 'nosniff') !== false,
                'frame' => $response->header('X-Frame-Options') !== '' || stripos($response->header('Content-Security-Policy'), 'frame-ancestors') !== false,
                'referrer' => $response->header('Referrer-Policy') !== '',
            ];
        }

        return $out;
    }

    // ---------------------------------------------------------------- text

    public static function cyrillicShare(string $text): ?float
    {
        $text = mb_substr($text, 0, 20000);
        $cyr = preg_match_all('/\p{Cyrillic}/u', $text);
        $lat = preg_match_all('/\p{Latin}/u', $text);

        return $cyr + $lat < 60 ? null : round($cyr / ($cyr + $lat), 2);
    }

    /** Брой думи и малка извадка от хешове на 4 думи (за сравнение на страници); без извадка под 100 думи. */
    private static function sketch(string $text): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $n = count($words);
        if ($n < 100) {
            return [$n, []];
        }
        $hashes = [];
        for ($i = 0, $max = min($n - 3, 4000); $i < $max; $i++) {
            $hashes[crc32($words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2] . ' ' . $words[$i + 3])] = true;
        }
        $hashes = array_keys($hashes);
        sort($hashes);

        return [$n, array_slice($hashes, 0, 100)];
    }

    /** Приблизително сходство (Jaccard) между две извадки. */
    public static function similarity(array $a, array $b): float
    {
        if (! $a || ! $b) {
            return 0.0;
        }
        $union = array_unique(array_merge($a, $b));
        sort($union);
        $union = array_slice($union, 0, min(count($a), count($b), 100));
        $sa = array_flip($a);
        $sb = array_flip($b);
        $shared = 0;
        foreach ($union as $h) {
            $shared += isset($sa[$h], $sb[$h]) ? 1 : 0;
        }

        return $union ? $shared / count($union) : 0.0;
    }

    // ---------------------------------------------------------------- structured data

    private static function structuredData(\DOMXPath $xpath, string $text): array
    {
        $ld = ['count' => 0, 'errors' => 0, 'incomplete' => 0, 'types' => [], 'org' => null, 'hours' => false, 'address' => false, 'faq_total' => 0, 'faq_missing' => 0];
        $plain = self::fold($text);

        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $ld['count']++;
            $data = json_decode(trim($script->textContent), true);
            if (! is_array($data)) {
                $ld['errors']++;

                continue;
            }
            $roots = array_is_list($data) ? $data : [$data];
            foreach ($roots as $root) {
                if (! is_array($root)) {
                    continue;
                }
                $hasContext = isset($root['@context']);
                if (! $hasContext) {
                    $ld['incomplete']++;
                }
                $nodes = isset($root['@graph']) && is_array($root['@graph']) ? $root['@graph'] : [$root];
                foreach ($nodes as $node) {
                    if (! is_array($node)) {
                        continue;
                    }
                    $types = (array) ($node['@type'] ?? []);
                    if (! $types) {
                        $ld['incomplete']++;

                        continue;
                    }
                    foreach ($types as $type) {
                        $type = is_string($type) ? basename(str_replace('\\', '/', $type)) : '';
                        $ld['types'][$type] = $type;
                        if (self::isOrg($type)) {
                            $fields = array_values(array_filter(['name', 'address', 'telephone', 'url'], fn ($f) => ! empty($node[$f])));
                            $ld['org'] = array_values(array_unique(array_merge($ld['org'] ?? [], $fields)));
                        }
                        if (! empty($node['openingHours']) || ! empty($node['openingHoursSpecification'])) {
                            $ld['hours'] = true;
                        }
                        if (! empty($node['address'])) {
                            $ld['address'] = true;
                        }
                        if ($type === 'FAQPage') {
                            foreach ((array) ($node['mainEntity'] ?? []) as $q) {
                                $name = is_array($q) ? (string) ($q['name'] ?? '') : '';
                                if ($name !== '') {
                                    $ld['faq_total']++;
                                    $ld['faq_missing'] += str_contains($plain, self::fold($name)) ? 0 : 1;
                                }
                            }
                        }
                    }
                }
            }
        }
        $ld['types'] = array_values($ld['types']);

        return $ld;
    }

    private static function isOrg(string $type): bool
    {
        return in_array($type, self::ORG_TYPES, true) || (bool) preg_match('/(Business|Store|Establishment|Service|Agency)$/', $type);
    }

    /** Малки букви без пунктуация и излишни интервали, за сравнение на текст. */
    private static function fold(string $s): string
    {
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower(html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
    }

    // ---------------------------------------------------------------- images

    private static function images(\DOMXPath $xpath, callable $abs, string $url): array
    {
        $noSize = $raster = $modern = 0;
        $firstLazy = null;
        foreach ($xpath->query('//body//img[not(ancestor::noscript)]') as $img) {
            $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
            if ($firstLazy === null) {
                $firstLazy = strtolower($img->getAttribute('loading')) === 'lazy';
            }
            if ($src === '' || str_starts_with($src, 'data:') || preg_match('/\.svg(\?|$)/i', $src)) {
                continue;
            }
            $w = $img->getAttribute('width');
            $h = $img->getAttribute('height');
            if (($w === '' || $h === '') && ! preg_match('/(?:width|height|aspect-ratio)\s*:/i', $img->getAttribute('style'))) {
                $noSize++;
            }
            $raster++;
            $parent = $img->parentNode;
            $inPicture = $parent && $parent->nodeName === 'picture'
                && $xpath->query('./source[contains(@type,"webp") or contains(@type,"avif")]', $parent)->length > 0;
            $modern += ($inPicture || preg_match('/\.(webp|avif)(\?|$)/i', $src)) ? 1 : 0;
        }

        return ['img_no_size' => $noSize, 'img_raster' => $raster, 'img_modern' => $modern, 'img_first_lazy' => (bool) $firstLazy];
    }

    // ---------------------------------------------------------------- links

    private static function anchors(\DOMXPath $xpath, callable $abs, string $url): array
    {
        $weak = 0;
        $samples = [];
        $flags = ['privacy' => false, 'terms' => false, 'contact' => false, 'map' => false, 'tel' => false];
        foreach ($xpath->query('//a[@href]') as $a) {
            $href = trim($a->getAttribute('href'));
            $hrefLow = mb_strtolower(rawurldecode($href));
            $text = trim(preg_replace('/\s+/u', ' ', $a->textContent));
            $low = mb_strtolower(preg_replace('/^[\s\p{P}\p{S}]+|[\s\p{P}\p{S}]+$/u', '', $text));

            if (str_starts_with($hrefLow, 'tel:')) {
                $flags['tel'] = true;
            }
            if (preg_match('#google\.[a-z.]+/maps|maps\.google|maps\.app\.goo\.gl|goo\.gl/maps#', $hrefLow)) {
                $flags['map'] = true;
            }
            $haystack = $hrefLow . ' ' . $low;
            $flags['privacy'] = $flags['privacy'] || preg_match('/privacy|poveritelnost|gdpr|поверителност|лични данни|личните данни|защита на данните/u', $haystack);
            $flags['terms'] = $flags['terms'] || preg_match('/terms|usloviya|uslovia|obshti-uslovi|условия|условията/u', $haystack);
            $flags['contact'] = $flags['contact'] || preg_match('/contact|kontakt|контакт|свържете се|свържи се/u', $haystack);

            if ($href === '' || $href[0] === '#' || preg_match('#^(mailto|tel|javascript|data|sms|viber|whatsapp):#i', $href)) {
                continue;
            }
            $label = $text;
            if ($label === '') {
                $label = trim($a->getAttribute('aria-label') . ' ' . $a->getAttribute('title'));
                foreach ($xpath->query('.//img/@alt', $a) as $alt) {
                    $label .= ' ' . $alt->nodeValue;
                }
                $label = trim($label);
            }
            $isEmpty = $label === '';
            if ($isEmpty || in_array($low, self::GENERIC_ANCHORS, true)) {
                $weak++;
                if (count($samples) < 3) {
                    $samples[] = ['text' => $isEmpty ? '' : $text, 'href' => $abs($href) ?? $href];
                }
            }
        }

        return ['weak_anchors' => $weak, 'weak_samples' => $samples, 'flags' => $flags];
    }

    // ---------------------------------------------------------------- local

    private static function local(string $main, array $ld, string $html): array
    {
        $address = $ld['address'] || str_contains($html, '<address')
            || preg_match('/(?:\bул\.?|улица|\bбул\.?|булевард|\bж\.?\s?к\.?|\bstreet|\broad|\bavenue)\s+[^\n,;]{2,40}?\s*(?:№|no\.?|N)?\s*\d+/iu', $main);
        $hours = $ld['hours'] || preg_match('/работно време|работим от|отворено|пон(?:еделник)?\.?\s*[-–—]\s*пет|mon(?:day)?\.?\s*[-–—]\s*fri|opening hours|business hours/iu', $main);
        $map = (bool) preg_match('#<iframe[^>]+src=["\'][^"\']*(?:google\.[a-z.]+/maps|maps\.google|openstreetmap\.org/export)#i', $html);

        return ['address' => (bool) $address, 'hours' => (bool) $hours, 'map' => $map];
    }
}
