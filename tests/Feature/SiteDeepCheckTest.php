<?php

namespace Tests\Feature;

use App\Mail\SiteCheckReportMail;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\SiteCheck\Areas;
use App\Support\SiteCheck\HostResolver;
use App\Support\SiteCheck\IssueCatalog;
use App\Support\SiteCheck\PageSignals;
use App\Support\SiteCheck\SiteAudit;
use App\Support\SiteCheck\SiteCrawler;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Стъпка 3 от SEO плана: по-дълбоката безплатна проверка на сайт (области, нови проверки, оценка, имейл). */
class SiteDeepCheckTest extends TestCase
{
    use RefreshDatabase;

    private const HTML = ['Content-Type' => 'text/html; charset=utf-8'];

    private bool $secure = false;

    private const SECURE = ['Strict-Transport-Security' => 'max-age=31536000', 'X-Content-Type-Options' => 'nosniff', 'X-Frame-Options' => 'DENY', 'Referrer-Policy' => 'same-origin'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutDefer();
        config(['services.pagespeed.key' => null]);
        $this->app->instance(HostResolver::class, new class extends HostResolver
        {
            public function publicIp(string $host): ?string
            {
                return '93.184.216.34';
            }
        });
    }

    /** Различен дълъг текст за всяка страница (за да не са дубликати): думи w1_x, w2_x ... */
    private function words(string $seed, int $n = 220): string
    {
        $map = array_combine(str_split('abcdef0123456789'), mb_str_split('абвгдежзийклмноп'));
        $seed = strtr(strtolower($seed), $map);
        $digits = array_combine(str_split('0123456789'), mb_str_split('абвгдежзий'));

        return implode(' ', array_map(fn ($i) => $seed . strtr((string) $i, $digits), range(1, $n)));
    }

    private function doc(string $head, string $body, string $lang = 'bg'): string
    {
        return '<!doctype html><html' . ($lang ? ' lang="' . $lang . '"' : '') . '><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $head . '</head><body>' . $body . '</body></html>';
    }

    /** @param  array<string, mixed>  $routes  url => string (html 200) | Response */
    private function fake(array $routes): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(function (Request $request) use ($routes) {
            $url = $request->url();
            if (array_key_exists($url, $routes)) {
                $r = $routes[$url];

                return is_string($r) ? Http::response($r, 200, self::HTML + ($this->secure && preg_match('#^https://[^/]+/$#', $url) ? self::SECURE : [])) : $r;
            }

            return Http::response('', 404);
        });
    }

    private function crawl(string $url, array $routes): array
    {
        $this->fake($routes);
        $result = app(SiteCrawler::class)->crawl($url, 50, 60);

        return $result + ['by' => collect($result['issues'])->keyBy('key')->all()];
    }

    // ------------------------------------------------------------------ a site with many problems

    private function badRoutes(): array
    {
        $long = $this->words('a');
        $home = $this->doc(
            '<title>Фирма</title><meta name="description" content="Кратко.">'
            . '<link rel="canonical" href="https://bad.bg/"><link rel="alternate" hreflang="en-uk" href="https://bad.bg/en/"><link rel="alternate" hreflang="de" href="https://bad.bg/de/">'
            . '<script type="application/ld+json">{bad json</script><script type="application/ld+json">{"name":"Без тип"}</script>'
            . '<script async src="https://www.googletagmanager.com/gtag/js?id=G-ABC123"></script><script>fbq(\'init\', \'123\');</script>'
            . '<link rel="stylesheet" href="/assets/x.css">',
            '<h1>Фирма</h1><h3>Скочено ниво</h3><img src="/a.jpg" alt="a" loading="lazy"><img src="/b.png" alt="b"><img src="/c.jpg" alt="c"><img src="/d.jpg" alt="d"><img src="/e.jpg" alt="e">'
            . '<a href="/uslugi">тук</a> <a href="/uslugi/">Услуги</a> <a href="/chain-a">A</a> <a href="/ext">Ext</a> <a href="/ni">Ni</a> <a href="/dup1">D1</a> <a href="/dup2">D2</a> <a href="/?sort=1">Сорт</a> <a href="/en/">EN</a>'
            . '<p>' . $this->words('home') . '</p>',
            lang: '',
        );
        $homeCopy = str_replace('<link rel="canonical" href="https://bad.bg/">', '', $home);
        $page = fn (string $title, string $h1, string $head = '', string $body = '', string $text = '') => $this->doc(
            '<title>' . $title . '</title><meta name="description" content="Описание на страницата, което е достатъчно дълго, за да е смислено за хората в Google и в AI.">' . $head,
            '<h1>' . $h1 . '</h1>' . $body . '<p>' . ($text ?: $this->words(md5($title), 180)) . '</p>'
        );

        return [
            'https://bad.bg/' => $home,
            'https://bad.bg/?sort=1' => $this->doc('<title>Фирма</title>', '<h1>Фирма</h1><p>' . $this->words('home') . '</p>', lang: ''),
            'http://bad.bg/' => $homeCopy,
            'https://www.bad.bg/' => $homeCopy,
            'https://bad.bg/index.php' => $homeCopy,
            'https://bad.bg/uslugi/' => $page('Услуги на фирмата за клиенти', 'Услуги'),
            'https://bad.bg/uslugi' => $page('Услуги на фирмата за клиенти', 'Услуги', ''
                . '<script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Колко струва скритият въпрос?"}]}</script>'),
            'https://bad.bg/en/' => $this->doc('<title>English version of the company page</title><meta name="description" content="Описание на страницата, което е достатъчно дълго, за да е смислено за хората в Google и в AI."><link rel="alternate" hreflang="en" href="https://bad.bg/en/">',
                '<h1>English</h1><p>Български текст в английска страница ' . $this->words('bg', 120) . '</p>', 'en'),
            'https://bad.bg/chain-a' => $page('Верига А на фирмата тук', 'A', '<link rel="canonical" href="https://bad.bg/chain-b">'),
            'https://bad.bg/chain-b' => $page('Верига Б на фирмата тук', 'B', '<link rel="canonical" href="https://bad.bg/chain-c">'),
            'https://bad.bg/chain-c' => $page('Верига В на фирмата тук', 'C', '<link rel="canonical" href="https://bad.bg/chain-c">'),
            'https://bad.bg/ext' => $page('Външен canonical на фирмата', 'Ext', '<link rel="canonical" href="https://other.com/ext">'),
            'https://bad.bg/ni' => $page('Скрита страница на фирмата', 'Ni', '<meta name="robots" content="noindex"><link rel="canonical" href="https://bad.bg/">'),
            'https://bad.bg/dup1' => $page('Първа почти същата страница', 'Dup1', '', '', $long),
            'https://bad.bg/dup2' => $page('Втора почти същата страница', 'Dup2', '', '', $long),
            'https://bad.bg/orphan' => $page('Страница сираче в картата', 'Orphan'),
            'https://bad.bg/robots.txt' => "User-agent: *\nDisallow: /assets/\n",
            'https://bad.bg/sitemap.xml' => '<urlset><url><loc>https://bad.bg/</loc><lastmod>2031-01-01</lastmod></url><url><loc>https://bad.bg/orphan</loc></url><url><loc>https://other.com/x</loc></url></urlset>',
        ];
    }

    public function test_a_site_with_many_problems_gets_every_new_check(): void
    {
        $result = $this->crawl('https://bad.bg/', $this->badRoutes());
        $by = $result['by'];

        foreach ([
            'dup_www', 'dup_http', 'dup_index', 'dup_slash', 'dup_query',
            'canonical_other_host', 'canonical_noindex', 'canonical_chain', 'hreflang_invalid', 'hreflang_no_self', 'hreflang_bad_target', 'hreflang_no_return',
            'schema_invalid', 'schema_incomplete', 'schema_faq_mismatch', 'schema_no_org', 'heading_skips', 'weak_anchors', 'title_length', 'description_short',
            'images_no_size', 'images_lcp_lazy', 'images_legacy', 'orphan_pages', 'duplicate_content', 'lang_missing', 'lang_mismatch',
            'robots_no_sitemap', 'robots_blocks_assets', 'llms_missing', 'sitemap_lastmod_future', 'sitemap_other_host', 'no_hsts', 'no_security_headers',
            'tracking_no_consent', 'local_no_address', 'local_no_map', 'local_no_hours', 'trust_no_privacy', 'trust_no_terms', 'trust_no_contact', 'title_duplicate',
        ] as $key) {
            $this->assertArrayHasKey($key, $by, "missing {$key}; got " . implode(',', array_keys($by)));
        }

        $this->assertSame('https://www.bad.bg/', $by['dup_www']['examples'][0]['url']);
        $this->assertSame('http://bad.bg/', $by['dup_http']['examples'][0]['url']);
        $this->assertSame('https://bad.bg/index.php', $by['dup_index']['examples'][0]['url']);
        $this->assertSame('en-uk', $by['hreflang_invalid']['examples'][0]['note']);
        $this->assertSame('https://bad.bg/de/', $by['hreflang_bad_target']['examples'][0]['url']);
        $this->assertSame('https://bad.bg/orphan', $by['orphan_pages']['examples'][0]['url']);
        $this->assertSame('2031-01-01', $by['sitemap_lastmod_future']['examples'][0]['note']);
        $this->assertSame('https://other.com/x', $by['sitemap_other_host']['examples'][0]['url']);
        $this->assertSame('X-Content-Type-Options, X-Frame-Options, Referrer-Policy', $by['no_security_headers']['examples'][0]['note']);
        $this->assertSame(3, $by['no_security_headers']['count']);
        $this->assertSame('Google Analytics 4, Meta Pixel', $by['tracking_no_consent']['examples'][0]['note']);
        $this->assertStringNotContainsString('ABC123', json_encode($result));
        $this->assertStringNotContainsString('123', $by['tracking_no_consent']['examples'][0]['note']);
        $this->assertSame('notice', $by['llms_missing']['severity']);
        $this->assertSame('warning', $by['dup_www']['severity']);
        $this->assertSame('тук', $by['weak_anchors']['examples'][0]['note']);
        $this->assertSame('100%', $by['duplicate_content']['examples'][0]['note']);

        // Областите са познати и всички текстове са описани (не остава суров ключ).
        foreach ($result['issues'] as $issue) {
            $this->assertContains(IssueCatalog::area($issue['key']), IssueCatalog::AREAS);
            $this->assertNotSame($issue['key'], SiteCrawler::describe($issue['key'], 2)['title'], $issue['key']);
            $this->assertNotSame('', SiteCrawler::describe($issue['key'], 2)['why'], $issue['key']);
        }
        $this->assertSame(['error', 'warning', 'notice'], array_values(array_unique(array_column($result['issues'], 'severity'))));
    }

    // ------------------------------------------------------------------ a good site (negatives)

    private function goodRoutes(): array
    {
        $nav = '<nav><a href="/">Начало</a> <a href="/uslugi">Нашите услуги</a> <a href="/kontakti">Контакти</a> <a href="/poveritelnost">Поверителност</a> <a href="/usloviya">Общи условия</a> <a href="/en/">English</a></nav>';
        $alt = '<link rel="alternate" hreflang="bg" href="https://good.bg/"><link rel="alternate" hreflang="en" href="https://good.bg/en/"><link rel="alternate" hreflang="x-default" href="https://good.bg/">';
        $head = fn (string $title, string $extra = '', bool $both = false) => '<title>' . $title . '</title><meta name="description" content="Описание на страницата ' . $title . ' е достатъчно дълго, за да е смислено за хората, които търсят услугата.">'
            . '<meta property="og:title" content="' . $title . '"><meta property="og:image" content="https://good.bg/og.png">' . $extra . ($both ? $alt : '')
            . '<script src="https://consent.cookiebot.com/uc.js"></script><script async src="https://www.googletagmanager.com/gtm.js?id=GTM-ABCD12"></script>';
        $org = '<script type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"LocalBusiness","name":"Добра фирма","url":"https://good.bg/","telephone":"+359888000000","address":{"@type":"PostalAddress","streetAddress":"ул. Витоша 15"},"openingHours":"Mo-Fr 09:00-18:00"},{"@type":"BreadcrumbList","itemListElement":[]}]}</script>';
        $img = '<img src="/i1.webp" alt="Една" width="400" height="300"><img src="/i2.webp" alt="Две" width="400" height="300">';
        $page = fn (string $path, string $title, string $h1, string $extra = '', string $body = '') => $this->doc(
            $head($title, '<link rel="canonical" href="https://good.bg' . $path . '">' . $org . $extra, $path === '/'),
            $nav . '<h1>' . $h1 . '</h1><h2>Подзаглавие</h2>' . $img . $body . '<p>' . $this->words(md5($path), 220) . '</p>'
        );
        return [
            'https://good.bg/' => $page('/', 'Добра фирма за ремонти в София и областта', 'Ремонти без излишни грижи', '', '<address>ул. Витоша 15, София</address><p>Работно време понеделник до петък</p><iframe src="https://www.google.com/maps/embed?pb=1"></iframe>'),
            'https://good.bg/uslugi' => $page('/uslugi', 'Нашите услуги по ремонт на жилища в София', 'Какво правим'),
            'https://good.bg/kontakti' => $page('/kontakti', 'Контакти и адрес на добрата фирма в София', 'Свържете се с нас'),
            'https://good.bg/poveritelnost' => $page('/poveritelnost', 'Политика за поверителност на добрата фирма', 'Поверителност'),
            'https://good.bg/usloviya' => $page('/usloviya', 'Общи условия за ползване на услугите на фирмата', 'Условия'),
            'https://good.bg/en/' => $this->doc($head('English page of the good company in Sofia', '<link rel="canonical" href="https://good.bg/en/">' . $org, true),
                $nav . '<h1>Repairs without worries</h1><h2>Sub</h2>' . $img . '<p>' . implode(' ', array_map(fn ($i) => 'word' . chr(97 + $i % 26) . chr(97 + intdiv($i, 26) % 26), range(1, 220))) . '</p>', 'en'),
            'https://good.bg/robots.txt' => "User-agent: *\nDisallow: /admin\nSitemap: https://good.bg/sitemap.xml\n",
            'https://good.bg/sitemap.xml' => '<urlset><url><loc>https://good.bg/</loc><lastmod>2026-01-01</lastmod></url><url><loc>https://good.bg/uslugi</loc></url><url><loc>https://good.bg/kontakti</loc></url><url><loc>https://good.bg/poveritelnost</loc></url><url><loc>https://good.bg/usloviya</loc></url><url><loc>https://good.bg/en/</loc></url></urlset>',
            'https://good.bg/llms.txt' => Http::response("# Добра фирма\n", 200, ['Content-Type' => 'text/plain']),
            'https://www.good.bg/' => Http::response('', 301, ['Location' => 'https://good.bg/']),
            'http://good.bg/' => Http::response('', 301, ['Location' => 'https://good.bg/']),
            'https://good.bg/uslugi/' => Http::response('', 301, ['Location' => 'https://good.bg/uslugi']),
            'https://good.bg/i1.webp' => Http::response('', 200, ['Content-Length' => '1000', 'Content-Type' => 'image/webp']),
            'https://good.bg/i2.webp' => Http::response('', 200, ['Content-Length' => '1000', 'Content-Type' => 'image/webp']),
        ];
    }

    public function test_a_well_kept_site_has_none_of_the_new_problems(): void
    {
        $this->secure = true;
        $result = $this->crawl('https://good.bg/', $this->goodRoutes());

        $this->assertNull($result['crawl']['error']);
        $this->assertSame([], array_keys($result['by']), json_encode($result['issues'], JSON_UNESCAPED_UNICODE));
        $this->assertSame(['Google Tag Manager'], $result['crawl']['signals']['tracking']);
        $this->assertSame(['Cookiebot'], $result['crawl']['signals']['consent']);
        $this->assertTrue($result['crawl']['signals']['llms']);
        $this->assertLessThanOrEqual(8, $result['crawl']['extra_requests']);
    }

    // ------------------------------------------------------------------ single checks

    public function test_soft_404_is_reported_and_stops_the_index_and_slash_variants(): void
    {
        $this->secure = true;
        $routes = $this->goodRoutes();
        $routes['https://good.bg/robots.txt'] = "User-agent: *\nSitemap: https://good.bg/sitemap.xml";
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(function (Request $request) use ($routes) {
            $r = $routes[$request->url()] ?? $this->doc('<title>Добра фирма за ремонти в София и областта</title>', '<h1>x</h1>');

            return is_string($r) ? Http::response($r, 200, self::HTML) : $r;
        });
        $by = collect(app(SiteCrawler::class)->crawl('https://good.bg/', 50, 60)['issues'])->keyBy('key');

        $this->assertTrue($by->has('soft_404'));
        $this->assertFalse($by->has('dup_index'));
        $this->assertFalse($by->has('dup_slash'));
    }

    public function test_a_redirect_of_unknown_addresses_to_the_home_page_is_a_soft_404(): void
    {
        $routes = $this->goodRoutes();
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(function (Request $request) use ($routes) {
            if (str_contains($request->url(), '-proverka-404')) {
                return Http::response('', 301, ['Location' => 'https://good.bg/']);
            }
            $r = $routes[$request->url()] ?? Http::response('', 404);

            return is_string($r) ? Http::response($r, 200, self::HTML) : $r;
        });
        $result = app(SiteCrawler::class)->crawl('https://good.bg/', 50, 60);

        $this->assertContains('soft_404', array_column($result['issues'], 'key'));
    }

    public function test_variants_with_a_canonical_to_the_main_address_are_fine(): void
    {
        $routes = $this->goodRoutes();
        $routes['https://www.good.bg/'] = $this->doc('<title>Добра фирма за ремонти в София и областта</title><link rel="canonical" href="https://good.bg/">', '<h1>x</h1>');
        $routes['http://good.bg/'] = $this->doc('<title>Добра фирма за ремонти в София и областта</title><meta name="robots" content="noindex">', '<h1>x</h1>');
        $by = $this->crawl('https://good.bg/', $routes)['by'];

        $this->assertArrayNotHasKey('dup_www', $by);
        $this->assertArrayNotHasKey('dup_http', $by);
    }

    public function test_missing_robots_txt_and_a_sitemap_without_dates(): void
    {
        $routes = $this->goodRoutes();
        unset($routes['https://good.bg/robots.txt']);
        $routes['https://good.bg/sitemap.xml'] = '<urlset><url><loc>https://good.bg/</loc></url><url><loc>https://good.bg/uslugi</loc></url></urlset>';
        $by = $this->crawl('https://good.bg/', $routes)['by'];

        $this->assertArrayHasKey('robots_missing', $by);
        $this->assertArrayNotHasKey('robots_no_sitemap', $by);
        $this->assertSame(2, $by['sitemap_no_lastmod']['count']);
        $this->assertArrayNotHasKey('sitemap_lastmod_future', $by);
    }

    public function test_a_sitemap_over_the_limit_is_reported(): void
    {
        $routes = $this->goodRoutes();
        $routes['https://good.bg/sitemap.xml'] = '<urlset>' . str_repeat('<url><loc>https://good.bg/p</loc><lastmod>2026-01-01</lastmod></url>', 50001) . '</urlset>';
        $by = $this->crawl('https://good.bg/', $routes)['by'];

        $this->assertSame(50001, $by['sitemap_too_big']['count']);
    }

    public function test_hreflang_without_x_default_is_a_notice_and_a_proper_set_is_clean(): void
    {
        $routes = $this->goodRoutes();
        $routes['https://good.bg/en/'] = str_replace('<link rel="alternate" hreflang="x-default" href="https://good.bg/">', '', $routes['https://good.bg/en/']);
        $by = $this->crawl('https://good.bg/', $routes)['by'];

        $this->assertArrayHasKey('hreflang_no_default', $by);
        $this->assertSame('notice', $by['hreflang_no_default']['severity']);
        $this->assertSame(1, $by['hreflang_no_default']['count']);
    }

    public function test_canonical_to_a_redirecting_address_is_reported(): void
    {
        $routes = $this->goodRoutes();
        $this->secure = true;
        $routes['https://good.bg/uslugi'] = str_replace('<link rel="canonical" href="https://good.bg/uslugi">', '<link rel="canonical" href="https://good.bg/old-uslugi">', $routes['https://good.bg/uslugi']);
        $routes['https://good.bg/old-uslugi'] = Http::response('', 301, ['Location' => 'https://good.bg/uslugi']);
        $by = $this->crawl('https://good.bg/', $routes)['by'];

        $this->assertSame('https://good.bg/old-uslugi', $by['canonical_redirect']['examples'][0]['note']);
    }

    public function test_the_organization_data_on_the_home_page_is_checked_for_fields(): void
    {
        $routes = $this->goodRoutes();
        $incomplete = '<script type="application/ld+json">{"@context":"https://schema.org","@type":"LocalBusiness","name":"Добра фирма"}</script>';
        $this->secure = true;
        $routes['https://good.bg/'] = preg_replace('#<script type="application/ld\+json">.*?</script>#s', $incomplete, $routes['https://good.bg/'], 1);
        $by = $this->crawl('https://good.bg/', $routes)['by'];

        $this->assertSame('address, telephone, url', $by['schema_org_incomplete']['examples'][0]['note']);
        $this->assertArrayNotHasKey('schema_no_org', $by);
    }

    public function test_tracking_signals_names_only_and_old_analytics(): void
    {
        $this->assertSame(['ga4' => 'Google Analytics 4', 'meta' => 'Meta Pixel'], PageSignals::tracking('<script src="https://www.googletagmanager.com/gtag/js?id=G-XYZ999"></script><script>fbq("init","1");</script>')['names']);
        $this->assertSame([], PageSignals::tracking('<p>nothing</p>')['names']);
        $this->assertSame(['Cookiebot'], PageSignals::tracking('<script src="https://consent.cookiebot.com/uc.js"></script>')['consent']);
        $this->assertTrue(PageSignals::tracking('<script src="https://plausible.io/js/script.js"></script>')['other_analytics']);

        $routes = $this->goodRoutes();
        $this->secure = true;
        $routes = array_map(fn ($r) => is_string($r) ? str_replace('https://www.googletagmanager.com/gtm.js?id=GTM-ABCD12', 'https://www.google-analytics.com/analytics.js', $r) : $r, $routes);
        $by = $this->crawl('https://good.bg/', $routes)['by'];
        $this->assertArrayHasKey('tracking_old_ga', $by);
    }

    public function test_no_tracking_at_all_is_a_soft_notice(): void
    {
        $this->secure = true;
        $routes = array_map(fn ($r) => is_string($r) ? preg_replace('#<script[^>]*(gtm|cookiebot)[^>]*></script>#', '', $r) : $r, $this->goodRoutes());
        $by = $this->crawl('https://good.bg/', $routes)['by'];

        $this->assertSame('notice', $by['tracking_none']['severity']);
        $this->assertStringContainsString('Изглежда', SiteCrawler::describe('tracking_none', 1)['title']);
        $this->assertArrayNotHasKey('tracking_no_consent', $by);
    }

    public function test_link_graph_checks_are_skipped_when_the_crawl_is_cut_short(): void
    {
        $this->fake($this->badRoutes());
        $result = app(SiteCrawler::class)->crawl('https://bad.bg/', 4, 60);
        $keys = array_column($result['issues'], 'key');

        $this->assertTrue($result['crawl']['limited']);
        $this->assertNotContains('orphan_pages', $keys);
        $this->assertNotContains('few_inlinks', $keys);
    }

    public function test_the_extra_requests_are_few_and_respect_the_time_budget(): void
    {
        $this->fake($this->badRoutes());
        $full = app(SiteCrawler::class)->crawl('https://bad.bg/', 50, 60);
        $this->assertLessThanOrEqual(8, $full['crawl']['extra_requests']);
        $this->assertGreaterThan(3, $full['crawl']['extra_requests']);

        $none = app(SiteCrawler::class)->crawl('https://bad.bg/', 50, 0);
        $this->assertSame(0, $none['crawl']['extra_requests']);
        $this->assertSame('time', $none['crawl']['limited_by']);
    }

    public function test_language_and_text_helpers(): void
    {
        $this->assertSame(1.0, PageSignals::cyrillicShare(str_repeat('Български текст ', 10)));
        $this->assertSame(0.0, PageSignals::cyrillicShare(str_repeat('English text ', 10)));
        $this->assertNull(PageSignals::cyrillicShare('кратко'));
        $a = array_map(fn ($i) => $i * 7, range(1, 100));
        $this->assertSame(1.0, PageSignals::similarity($a, $a));
        $this->assertLessThan(0.1, PageSignals::similarity($a, array_map(fn ($i) => $i * 7 + 1, range(1, 100))));
    }

    // ------------------------------------------------------------------ scoring and areas

    public function test_the_score_scale_and_weights(): void
    {
        $issue = fn ($sev, $count = 1) => ['key' => 'x', 'severity' => $sev, 'count' => $count];

        $this->assertSame(100, SiteAudit::score(100, [], 5));
        $this->assertSame(95, SiteAudit::score(100, array_fill(0, 40, $issue('notice')), 5)); // notices cost at most 5
        $this->assertSame(99, SiteAudit::score(100, [$issue('warning', 1)], 10)); // 1 + 2 * 0.1 = 1.2
        $this->assertLessThan(SiteAudit::score(100, [$issue('warning', 5)], 5), SiteAudit::score(100, [$issue('error', 5)], 5));
        $this->assertSame(40, SiteAudit::score(100, array_fill(0, 30, $issue('error', 5)), 5)); // penalty is capped at 60
    }

    public function test_area_scores_come_from_the_issues_and_keyed_checks(): void
    {
        $report = [
            'crawl' => ['analyzed' => 10],
            'issues' => [
                ['key' => 'broken_pages', 'severity' => 'error', 'count' => 10],
                ['key' => 'tracking_none', 'severity' => 'notice', 'count' => 1],
                ['key' => 'schema_no_org', 'severity' => 'warning', 'count' => 1],
            ],
            'checks' => [['key' => 'ai', 'status' => 'fail', 'title' => 'x'], ['key' => 'title', 'status' => 'fail', 'title' => 'y'], ['status' => 'warn', 'title' => 'old, no key']],
        ];
        $areas = Areas::scores($report);

        $this->assertSame(['technical', 'content', 'ai', 'local', 'tracking'], array_keys($areas));
        $this->assertSame(70, $areas['technical']['score']); // error with share 1 = 30
        $this->assertSame(100, $areas['content']['score']); // the home title check has a twin in the crawl, so it is not counted twice
        $this->assertSame(64, $areas['ai']['score']); // warning 5 + 7 * 0.1 = 5.7, failed AI check 30
        $this->assertSame(98, $areas['tracking']['score']);
        $this->assertSame(100, $areas['local']['score']);
        $this->assertSame('Видимост в AI', $areas['ai']['label']);
    }

    // ------------------------------------------------------------------ the report, email and admin

    private function fullReport(string $locale): array
    {
        app()->setLocale($locale);
        $this->fake($this->badRoutes());

        return app(SiteAudit::class)->run('bad.bg');
    }

    public function test_the_email_is_sorted_into_areas_in_both_languages_and_follows_the_copy_rules(): void
    {
        foreach (['bg' => ['Какво да оправите първо', 'Оценка по области', 'Техническо', 'Видимост в AI', 'Локално и доверие', 'Проследяване', 'Съдържание и заглавия', 'Искам подробен одит'],
            'en' => ['What to fix first', 'Score by area', 'Technical', 'AI visibility', 'Local and trust', 'Tracking', 'Content and titles', 'I want a detailed audit']] as $locale => $needles) {
            $report = $this->fullReport($locale);
            $html = (new SiteCheckReportMail($report))->render();

            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $html, "{$locale}: {$needle}");
            }
            $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($html)), $locale);
            $this->assertStringContainsString('Google Analytics 4', $html);
            $this->assertStringNotContainsString('ABC123', $html); // names only, never the ids
            $this->assertStringContainsString('width="100%"', $html);
            if ($locale === 'en') {
                $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', preg_replace(['#https?://\S+#', '/“[^”]*”/u'], '', PlainCopyTest::visibleText($html)), 'English email has no Cyrillic');
            }
            // Най-много 5 неща на област и няма твърдения за позиции или резултати.
            $this->assertStringNotContainsStringIgnoringCase('ranking', preg_replace('/not a position in Google|do not promise a result|how the site ranks in Google|affects rankings/i', '', $html));
        }
        app()->setLocale('bg');
    }

    public function test_the_admin_card_shows_area_scores_and_works_with_an_old_saved_report(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        // Стар отчет: без ключове на проверките, без области, без бележки; само каквото е записвала първата версия на обхождането.
        $old = [
            'domain' => 'stariat.bg', 'score' => 71, 'home_score' => 80, 'sent' => true, 'checked_at' => now()->toIso8601String(),
            'crawl' => ['pages' => 7, 'analyzed' => 5, 'limit' => 50, 'limited' => false, 'limited_by' => null, 'seconds' => 3.2, 'error' => null],
            'issues' => [
                ['key' => 'broken_pages', 'severity' => 'error', 'count' => 1, 'examples' => [['url' => 'https://stariat.bg/stara', 'from' => 'https://stariat.bg/', 'note' => 'HTTP 404']]],
                ['key' => 'title_duplicate', 'severity' => 'warning', 'count' => 2, 'examples' => []],
                ['key' => 'retired_unknown_key', 'severity' => 'warning', 'count' => 1, 'examples' => []],
            ],
            'checks' => [['status' => 'warn', 'title' => 'Няма карта на сайта (sitemap)', 'detail' => 'x'], ['status' => 'ok', 'title' => 'Заглавие за Google', 'detail' => 'y']],
            'pagespeed' => null, 'shopping' => null,
        ];
        $lead = Project::create(['name' => 'Проверка на сайт: stariat.bg', 'description' => 'x', 'status' => 'Planning', 'source' => 'website', 'service' => Project::SITE_CHECK_SERVICE,
            'manager' => 'Неразпределен', 'category_id' => \App\Models\Category::firstOrCreate(['name' => 'Ново запитване'], ['description' => 'x'])->id, 'client_email' => 'a@stariat.bg', 'start_date' => now()->toDateString(), 'website_url' => 'https://stariat.bg']);
        $lead->forceFill(['site_check_report' => $old])->save();

        $summary = $lead->siteCheckSummary();
        $this->assertSame(71, $summary['score']);
        $this->assertSame(['technical', 'content', 'ai', 'local', 'tracking'], array_keys($summary['areas']));
        $this->assertLessThan(100, $summary['areas']['technical']['score']);
        $this->assertSame('Счупени вътрешни връзки, 404 (1)', $summary['top'][0]['title']);
        $this->assertSame('retired_unknown_key', collect($summary['issues'])->last()['title']);

        $this->actingAs($user)->get('/admin/proverki')->assertOk()
            ->assertSee('Оценка по области')->assertSee('Техническо')->assertSee('Проблеми по целия сайт')->assertSee('Най-важното за разговора');

        // Същият стар отчет се рендира и в имейл (и на двата езика).
        foreach (['bg', 'en'] as $locale) {
            app()->setLocale($locale);
            $report = $old + ['reachable' => true, 'url' => 'https://stariat.bg/'];
            $html = (new SiteCheckReportMail($report))->render();
            $this->assertStringContainsString($locale === 'bg' ? 'Оценка по области' : 'Score by area', $html);
            $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($html)), $locale);
        }
        app()->setLocale('bg');
    }

    public function test_the_new_report_is_stored_with_keys_and_the_lead_note_lists_areas(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $this->fake($this->badRoutes());
        $this->post('/proverka-na-sait', ['domain' => 'bad.bg', 'email' => 'ivan@bad.bg', 'consent' => '1'])->assertRedirect();

        $lead = Project::firstOrFail();
        $this->assertStringContainsString('По области (само от нашите проверки)', $lead->description);
        $this->assertNotNull($lead->site_check_report['checks'][0]['key']);
        $summary = $lead->siteCheckSummary();
        $this->assertCount(5, $summary['areas']);
        $this->assertNotEmpty($summary['top']);
        $this->assertNotEmpty($lead->site_check_report['crawl']['signals']['tracking']);
    }
}
