<?php

namespace Tests\Feature;

use App\Mail\NewLeadMail;
use App\Mail\SiteCheckReportMail;
use App\Models\Project;
use App\Support\SiteCheck\HostResolver;
use App\Support\SiteCheck\SiteChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SiteCheckTest extends TestCase
{
    use RefreshDatabase;

    private const GOOD_PAGE = '<!doctype html><html><head><meta name="viewport" content="width=device-width"><title>Ремонт на покриви в Пловдив | Здрав покрив</title>'
        . '<meta name="description" content="Оглед и оферта до 48 часа. Работим в Пловдив и областта. Обадете се или изпратете снимка на покрива и ще ви кажем какво трябва.">'
        . '<meta property="og:title" content="Здрав покрив"><meta property="og:image" content="https://firma.bg/og.png"><script type="application/ld+json">{}</script></head>'
        . '<body><h1>Ремонт на покриви</h1><a href="tel:+359888000000">0888 000 000</a><img src="a.webp" alt="Покрив"></body></html>';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutDefer();
        $this->app->instance(HostResolver::class, new class extends HostResolver
        {
            public function publicIp(string $host): ?string
            {
                return str_contains($host, 'vatreshen') ? null : '93.184.216.34';
            }
        });
    }

    private function fakeSite(string $html = self::GOOD_PAGE, bool $redirect = true, string $robots = "User-agent: *\nDisallow:\nSitemap: https://firma.bg/sitemap.xml", bool $sitemap = true): void
    {
        Http::fake([
            'https://firma.bg/robots.txt' => Http::response($robots),
            'https://firma.bg/sitemap.xml' => Http::response('<urlset/>', $sitemap ? 200 : 404),
            'https://firma.bg/' => Http::response($html),
            'http://firma.bg/' => $redirect ? Http::response('', 301, ['Location' => 'https://firma.bg/']) : Http::response($html),
            '*' => Http::response('', 404),
        ]);
    }

    public function test_page_loads_in_both_languages_and_is_in_the_menu_and_sitemap(): void
    {
        $this->get('/proverka-na-sait')->assertOk()->assertSee('Проверка на сайт')->assertSee('Адрес на сайта')->assertSee('name="consent"', false);
        $this->get('/en/website-check')->assertOk()->assertSee('Website check')->assertSee('Website address');
        $this->get('/')->assertSee('href="' . url('/proverka-na-sait') . '"', false);
        $this->get('/uslugi')->assertSee('Проверете сайта си за минута');
        $this->get('/sitemap.xml')->assertSee('<loc>' . url('/proverka-na-sait') . '</loc>', false);
    }

    public function test_domains_are_normalized_and_internal_addresses_are_refused(): void
    {
        $this->assertSame('www.firma.bg', SiteChecker::normalizeDomain(' https://WWW.Firma.bg/za-nas?x=1 '));
        $this->assertSame('firma.bg', SiteChecker::normalizeDomain('firma.bg'));
        $this->assertMatchesRegularExpression('/^xn--[a-z0-9-]+\.xn--90ae$/', (string) SiteChecker::normalizeDomain('пчелина.бг'));
        foreach (['', 'localhost', '127.0.0.1', 'http://10.0.0.5/', 'firma', 'server.local', 'javascript:alert(1)'] as $bad) {
            $this->assertNull(SiteChecker::normalizeDomain($bad), $bad);
        }
    }

    public function test_a_robots_txt_that_blocks_ai_assistants_is_reported(): void
    {
        Mail::fake();
        $this->fakeSite(robots: "User-agent: GPTBot\nUser-agent: OAI-SearchBot\nUser-agent: PerplexityBot\nDisallow: /\n\nUser-agent: *\nDisallow: /admin\nSitemap: https://firma.bg/sitemap.xml");

        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1']);

        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $check = collect($mail->report['checks'])->firstWhere('title', 'AI асистентите са спрени');
            $this->assertNotNull($check, json_encode($mail->report['checks'], JSON_UNESCAPED_UNICODE));
            $this->assertStringContainsString('OAI-SearchBot, PerplexityBot', $check['detail']);
            $this->assertStringNotContainsString('GPTBot', $check['detail']);
            // Google още може да показва сайта: спрени са само AI ботовете.
            $this->assertTrue(collect($mail->report['checks'])->contains('title', 'Google може да показва сайта'));

            return true;
        });
    }

    public function test_llms_txt_describes_the_business_for_ai_assistants(): void
    {
        $this->get('/llms.txt')->assertOk()
            ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
            ->assertSee('# Creatium Lab', false)
            ->assertSee('## Услуги', false)
            ->assertSee('(' . url('/uslugi/google-profil-i-otzivi') . ')', false)
            ->assertSee('**Стартов двигател**: €450', false)
            ->assertSee(config('creatium.contact.email'));
    }

    public function test_good_site_gets_a_high_score_and_the_report_is_emailed(): void
    {
        Mail::fake();
        $this->fakeSite();

        $this->post('/proverka-na-sait', ['domain' => 'https://firma.bg/', 'email' => 'Ivan@Firma.bg', 'consent' => '1'])
            ->assertRedirect('/proverka-na-sait')
            ->assertSessionHas('site_check_sent', 'ivan@firma.bg')
            ->assertSessionHas('lead_service', 'Проверка на сайт');

        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $this->assertSame('firma.bg', $mail->report['domain']);
            $this->assertSame(100, $mail->report['score'], json_encode($mail->report['checks'], JSON_UNESCAPED_UNICODE));
            $html = $mail->render();
            $this->assertStringContainsString('Искам подробен одит', $html);
            $this->assertStringContainsString('Заглавие за Google', $html);

            return $mail->hasTo('ivan@firma.bg') && $mail->hasReplyTo(config('creatium.contact.email'));
        });
        Mail::assertSent(NewLeadMail::class, fn ($mail) => $mail->hasTo(config('creatium.notify_email')));

        $lead = Project::firstOrFail();
        $this->assertSame('website', $lead->source);
        $this->assertSame('ivan@firma.bg', $lead->client_email);
        $this->assertSame('https://firma.bg', $lead->website_url);
        $this->assertStringContainsString('оценка 100 от 100', $lead->description);
        $this->assertNotNull($lead->email_sent_at);
        $this->get('/proverka-na-sait')->assertSee('Проверката започна');
    }

    public function test_problems_are_reported(): void
    {
        Mail::fake();
        $this->fakeSite('<html><head><meta name="robots" content="noindex"></head><body><img src="a.jpg"><h1>a</h1><h1>b</h1></body></html>', redirect: false, robots: '', sitemap: false);

        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1']);

        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $titles = collect($mail->report['checks'])->where('status', '!=', 'ok')->pluck('title')->all();
            foreach (['Адресът с http:// не води към https://', 'Няма заглавие за Google', 'Няма описание за Google', 'Няколко основни заглавия (2 × H1)',
                'Не е настроен за телефон', 'Сайтът е скрит от Google', 'Няма карта на сайта (sitemap)', 'Снимки без описание (1 от 1)'] as $expected) {
                $this->assertContains($expected, $titles);
            }
            $this->assertLessThan(50, $mail->report['score']);
            $this->assertStringContainsString('Какво да оправите', $mail->render());

            return true;
        });
        $this->assertStringContainsString('Сайтът е скрит от Google', Project::firstOrFail()->description);
    }

    public function test_unreachable_or_internal_site_gets_a_short_report(): void
    {
        Mail::fake();
        Http::fake();

        $this->post('/proverka-na-sait', ['domain' => 'vatreshen.bg', 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertRedirect();

        Http::assertNothingSent();
        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $this->assertFalse($mail->report['reachable']);
            $this->assertSame(0, $mail->report['score']);
            $this->assertStringContainsString('Не успяхме да отворим сайта.', $mail->render());

            return true;
        });
    }

    public function test_redirects_to_internal_addresses_are_not_followed(): void
    {
        Mail::fake();
        Http::fake([
            'https://firma.bg/' => Http::response('', 302, ['Location' => 'http://vatreshen.bg/admin']),
            '*' => Http::response('', 404),
        ]);

        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1']);

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'vatreshen'));
    }

    public function test_validation_and_honeypot(): void
    {
        Mail::fake();
        $this->fakeSite();

        $this->post('/proverka-na-sait', ['domain' => 'localhost', 'email' => 'не е имейл'])
            ->assertSessionHasErrors(['domain', 'email', 'consent']);

        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'bot@spam.bg', 'consent' => '1', 'website' => 'x'])->assertRedirect();
        $this->assertSame(0, Project::count());
        Mail::assertNothingSent();
    }

    public function test_daily_limit_per_email(): void
    {
        Mail::fake();
        $this->fakeSite();

        foreach (['a.bg', 'b.bg', 'c.bg'] as $domain) {
            $this->post('/proverka-na-sait', ['domain' => $domain, 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertSessionHasNoErrors();
        }
        $this->post('/proverka-na-sait', ['domain' => 'd.bg', 'email' => 'IVAN@firma.bg', 'consent' => '1'])->assertSessionHasErrors('email');
        $this->assertSame(3, Project::count());
    }

    public function test_english_visitors_get_the_report_in_english(): void
    {
        Mail::fake();
        $this->fakeSite();

        $this->post('/en/website-check', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertRedirect('/en/website-check');

        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $this->assertStringContainsString('I want a detailed audit', $mail->render());
            $this->assertSame('Check of firma.bg: score 100 out of 100', $mail->envelope()->subject);

            return true;
        });
    }
}
