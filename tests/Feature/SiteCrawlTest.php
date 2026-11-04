<?php

namespace Tests\Feature;

use App\Mail\SiteCheckReportMail;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\SiteCheck\HostResolver;
use App\Support\SiteCheck\SiteCrawler;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SiteCrawlTest extends TestCase
{
    use RefreshDatabase;

    private function page(string $title, string $h1 = '<h1>Заглавие</h1>', string $body = '', string $head = ''): string
    {
        return '<!doctype html><html><head><meta name="viewport" content="width=device-width"><title>' . $title . '</title>'
            . '<meta name="description" content="Описание на страницата, което е достатъчно дълго, за да е смислено за хората в Google.">' . $head . '</head>'
            . '<body>' . $h1 . $body . '<p>' . str_repeat('дума ', 250) . '</p></body></html>';
    }

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

        $home = $this->page('Начална страница на фирмата', '<h1>Фирма</h1>', '
            <a href="/uslugi">Услуги</a> <a href="https://www.firma.bg/za-nas#top">За нас</a> <a href="/kontakti?x=1">Контакти</a>
            <a href="/stara">Стара</a> <a href="/staro">Старо</a> <a href="/admin/secret">Таен</a> <a href="mailto:a@firma.bg">Поща</a>
            <a href="https://partner.bg/">Партньор</a> <a href="https://dead-link.bg/gone">Мъртва</a>
            <img src="/img/nealt.jpg"><img src="/img/big.jpg" alt="Голяма"><script src="http://cdn.firma.bg/x.js"></script>
            <img src="http://firma.bg/img/insecure.jpg" alt="x">');

        Http::fake(function (Request $request) use ($home) {
            $json = ['Content-Type' => 'text/html; charset=utf-8'];
            if (str_starts_with($request->url(), 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed')) {
                return Http::response([
                    'lighthouseResult' => ['categories' => ['performance' => ['score' => 0.62]], 'audits' => [
                        'largest-contentful-paint' => ['numericValue' => 3400], 'cumulative-layout-shift' => ['numericValue' => 0.12],
                    ]],
                    'loadingExperience' => ['metrics' => ['INTERACTION_TO_NEXT_PAINT' => ['percentile' => 240]]],
                ]);
            }

            return match ($request->url()) {
                'https://firma.bg/' => Http::response($home, 200, $json),
                'http://firma.bg/' => Http::response('', 301, ['Location' => 'https://firma.bg/']),
                'https://firma.bg/robots.txt' => Http::response("User-agent: *\nDisallow: /admin\nSitemap: https://firma.bg/sitemap.xml"),
                'https://firma.bg/sitemap.xml' => Http::response('<urlset><url><loc>https://firma.bg/</loc></url><url><loc>https://firma.bg/uslugi</loc></url>'
                    . '<url><loc>https://firma.bg/gone-page</loc></url><url><loc>https://firma.bg/kontakti?x=1</loc></url></urlset>'),
                'https://firma.bg/uslugi' => Http::response($this->page('Страница на фирмата', '', '<a href="/za-nas">назад</a>'), 200, $json),
                'https://firma.bg/za-nas', 'https://www.firma.bg/za-nas' => Http::response($this->page('Страница на фирмата', '<h1>За нас</h1>'), 200, $json),
                'https://firma.bg/kontakti?x=1' => Http::response($this->page('Контакти на фирмата', '<h1>Контакти</h1>', '', '<meta name="robots" content="noindex">'), 200, $json),
                'https://firma.bg/gone-page' => Http::response('Не е намерено', 404, $json),
                'https://firma.bg/stara' => Http::response('Не е намерено', 404, $json),
                'https://firma.bg/staro' => Http::response('', 301, ['Location' => '/междинно']),
                'https://firma.bg/междинно', 'https://firma.bg/%D0%BC%D0%B5%D0%B6%D0%B4%D0%B8%D0%BD%D0%BD%D0%BE' => Http::response('', 301, ['Location' => '/novo']),
                'https://firma.bg/novo' => Http::response($this->page('Новата страница на фирмата', '<h1>Ново</h1>'), 200, $json),
                'https://firma.bg/img/big.jpg' => Http::response('', 200, ['Content-Length' => '450000', 'Content-Type' => 'image/jpeg']),
                'https://firma.bg/img/nealt.jpg' => Http::response('', 200, ['Content-Length' => '1000', 'Content-Type' => 'image/jpeg']),
                'http://firma.bg/img/insecure.jpg' => Http::response('', 404),
                'https://partner.bg/' => Http::response('', 200),
                'https://dead-link.bg/gone' => Http::response('', 404),
                default => Http::response('unexpected', 500),
            };
        });
    }

    private function issues(array $result): array
    {
        return collect($result['issues'])->keyBy('key')->all();
    }

    public function test_crawler_finds_the_issues_of_a_small_site(): void
    {
        $result = app(SiteCrawler::class)->crawl('https://firma.bg/', 50, 60);
        $issues = $this->issues($result);

        $this->assertFalse($result['crawl']['limited']);
        $this->assertNull($result['crawl']['error']);
        // /, /uslugi, /za-nas, /kontakti?x=1, /stara, /staro (-> /novo), /gone-page от sitemap
        $this->assertSame(7, $result['crawl']['pages']);
        $this->assertSame(5, $result['crawl']['analyzed']);

        $this->assertSame(1, $issues['broken_pages']['count']);
        $this->assertSame(['url' => 'https://firma.bg/stara', 'from' => 'https://firma.bg/', 'note' => 'HTTP 404'], $issues['broken_pages']['examples'][0]);
        $this->assertSame(1, $issues['links_to_redirects']['count']);
        $this->assertSame(1, $issues['redirect_chains']['count']);
        $this->assertSame(2, $issues['title_duplicate']['count']);
        $this->assertSame(1, $issues['no_h1']['count']);
        $this->assertSame('https://firma.bg/uslugi', $issues['no_h1']['examples'][0]['url']);
        $this->assertSame(1, $issues['noindex']['count']);
        $this->assertSame(1, $issues['images_no_alt']['count']);
        $this->assertSame(1, $issues['images_heavy']['count']);
        $this->assertSame('439 KB', $issues['images_heavy']['examples'][0]['note']);
        $this->assertSame(1, $issues['mixed_content']['count']);
        $this->assertSame('2', $issues['mixed_content']['examples'][0]['note']);
        $this->assertSame(1, $issues['broken_external']['count']);
        $this->assertSame('https://dead-link.bg/gone', $issues['broken_external']['examples'][0]['url']);
        $this->assertSame(1, $issues['broken_resources']['count']);
        // В картата на сайта: gone-pageта страница и страницата с noindex; /za-nas и /novo липсват в нея.
        $this->assertSame(2, $issues['sitemap_bad']['count']);
        $this->assertSame(2, $issues['missing_from_sitemap']['count']);
        $this->assertArrayNotHasKey('thin_pages', $issues);
        $this->assertArrayNotHasKey('server_errors', $issues, json_encode($issues['server_errors'] ?? null));
        $this->assertArrayNotHasKey('slow_pages', $issues);
        $this->assertArrayNotHasKey('deep_pages', $issues);
        $this->assertArrayNotHasKey('canonical_broken', $issues);
        $this->assertSame(4, $issues['canonical_missing']['count']); // the noindex page is not checked further
        $this->assertSame('error', $issues['broken_pages']['severity']);
        $this->assertSame('error', $result['issues'][0]['severity']);

        // robots.txt, външните адреси: нищо извън сайта освен проверката на външните връзки.
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/admin'));
        Http::assertNotSent(fn (Request $r) => ! preg_match('#^https?://(www\.)?firma\.bg|^https://(partner|dead-link)\.bg/#', $r->url()) && ! str_contains($r->url(), 'cdn.firma.bg'));
        Http::assertSent(fn (Request $r) => $r->method() === 'HEAD' && $r->url() === 'https://partner.bg/');
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'googleapis'));
    }

    public function test_crawler_stops_at_the_page_limit_and_says_so(): void
    {
        $result = app(SiteCrawler::class)->crawl('https://firma.bg/', 3, 60);

        $this->assertSame(3, $result['crawl']['pages']);
        $this->assertTrue($result['crawl']['limited']);
        $this->assertSame('pages', $result['crawl']['limited_by']);
        Http::assertNotSent(fn (Request $r) => $r->url() === 'https://firma.bg/stara');
    }

    public function test_crawler_stops_at_the_time_budget(): void
    {
        $result = app(SiteCrawler::class)->crawl('https://firma.bg/', 50, 0);

        $this->assertTrue($result['crawl']['limited']);
        $this->assertSame('time', $result['crawl']['limited_by']);
        $this->assertLessThan(2, $result['crawl']['pages']);
    }

    public function test_report_email_and_admin_show_the_crawl(): void
    {
        Mail::fake();
        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertRedirect();

        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $this->assertSame(5, $mail->report['crawl']['analyzed']);
            $this->assertLessThan(100, $mail->report['score']);
            $html = $mail->render();
            $this->assertStringContainsString('Какво да оправите първо', $html);
            $this->assertStringContainsString('Счупени вътрешни връзки (404): 1', $html);
            $this->assertStringContainsString('https://firma.bg/stara', $html);
            $this->assertStringContainsString('Проверихме 5 страници', $html);
            $this->assertStringContainsString('Искам подробен одит', $html);

            return true;
        });

        $lead = Project::firstOrFail();
        $this->assertSame(5, $lead->site_check_report['crawl']['analyzed']);
        $this->assertNotEmpty($lead->site_check_report['issues']);
        $summary = $lead->siteCheckSummary();
        $this->assertSame(5, $summary['crawl']['analyzed']);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));
        $this->actingAs($user)->get('/admin/proverki')->assertOk()
            ->assertSee('Обходени страници')
            ->assertSee('Проблеми по целия сайт')
            ->assertSee('Счупени вътрешни връзки (404): 1');
    }

    public function test_english_report_explains_issues_in_english(): void
    {
        Mail::fake();
        $this->post('/en/website-check', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertRedirect();

        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $html = $mail->render();
            $this->assertStringContainsString('Broken internal links (404): 1', $html);
            $this->assertStringContainsString('What to fix first', $html);
            $this->assertStringNotContainsString('Счупени', $html);

            return true;
        });
    }

    public function test_a_crawl_error_still_sends_what_was_collected(): void
    {
        Mail::fake();
        $this->app->bind(SiteCrawler::class, fn () => new class(app(\App\Support\SiteCheck\SiteChecker::class)) extends SiteCrawler
        {
            public function crawl(string $startUrl, ?int $limit = null, ?int $seconds = null): array
            {
                throw new \RuntimeException('boom');
            }
        });

        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertRedirect();

        Mail::assertSent(SiteCheckReportMail::class, fn ($mail) => $mail->report['crawl']['error'] === 'boom' && $mail->report['score'] > 0);
    }

    public function test_pagespeed_is_added_to_the_report_only_with_a_key(): void
    {
        Mail::fake();
        config(['services.pagespeed.key' => 'test-key']);

        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1']);

        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed')
            && $r['strategy'] === 'mobile' && $r['category'] === 'performance' && $r['key'] === 'test-key' && $r['url'] === 'https://firma.bg/');
        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $this->assertSame(['score' => 62, 'lcp' => 3.4, 'cls' => 0.12, 'inp' => 240, 'source' => 'lab'], $mail->report['pagespeed']);
            $html = $mail->render();
            $this->assertStringContainsString('Скорост на телефон (Google PageSpeed)', $html);
            $this->assertStringContainsString('LCP 3,4 s', $html);

            return true;
        });

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));
        $this->actingAs($user)->get('/admin/zdrave')->assertSee('Скорост на телефон в проверката на сайт')->assertSee('LCP, CLS и INP');
    }

    public function test_health_page_explains_the_missing_pagespeed_key(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        $this->actingAs($user)->get('/admin/zdrave')->assertSee('PAGESPEED_API_KEY=');
    }

    public function test_noindex_pages_query_variants_and_social_links_are_not_reported_as_problems(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        $canonical = '<link rel="canonical" href="https://firma.bg/paketi">';
        Http::fake(function (Request $request) use ($canonical) {
            $html = ['Content-Type' => 'text/html; charset=utf-8'];

            return match ($request->url()) {
                'https://firma.bg/' => Http::response($this->page('Начална страница на фирмата', '<h1>Фирма</h1>', '
                    <a href="/paketi">Пакети</a> <a href="/paketi?paket=1">Пакет 1</a> <a href="/paketi?paket=2">Пакет 2</a>
                    <a href="/demo">Демо</a> <a href="https://www.linkedin.com/in/someone/">LinkedIn</a> <a href="https://slow.bg/">Бавен</a>',
                    '<link rel="canonical" href="https://firma.bg/">'), 200, $html),
                'https://firma.bg/paketi', 'https://firma.bg/paketi?paket=1', 'https://firma.bg/paketi?paket=2' => Http::response($this->page('Пакети и цени на фирмата', '<h1>Пакети</h1>', '', $canonical), 200, $html),
                'https://firma.bg/demo' => Http::response('<html><head><meta name="robots" content="noindex"></head><body>Демо</body></html>', 200, $html),
                'https://slow.bg/' => throw new \Illuminate\Http\Client\ConnectionException('timeout'),
                default => Http::response('', 404),
            };
        });

        $result = app(SiteCrawler::class)->crawl('https://firma.bg/');
        $issues = $this->issues($result);

        $this->assertSame(1, $issues['noindex']['count']);
        // Само noindex се отбелязва за демото; вариантите ?paket= (с canonical към /paketi) не се броят никъде.
        foreach ($issues as $key => $issue) {
            foreach ($issue['examples'] as $example) {
                $this->assertFalse($key !== 'noindex' && str_contains($example['url'], '/demo'), $key);
                $this->assertStringNotContainsString('?paket=', $example['url'], $key);
            }
        }
        // LinkedIn не се проверява, а сайт без отговор не е „счупена връзка“.
        $this->assertArrayNotHasKey('broken_external', $issues);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'linkedin.com'));
    }
}
