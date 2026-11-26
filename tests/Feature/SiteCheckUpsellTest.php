<?php

namespace Tests\Feature;

use App\Mail\SiteCheckFollowUpMail;
use App\Mail\SiteCheckOfferOrderedMail;
use App\Mail\SiteCheckReportMail;
use App\Models\Category;
use App\Models\ClientEmail;
use App\Models\OutreachSuppression;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\SiteCheck\FollowUps;
use App\Support\SiteCheck\MonthlyChecks;
use App\Support\SiteCheck\Offers;
use App\Support\SiteCheck\SiteAudit;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Стълбата след безплатната проверка (26.11.2026): платената оферта „Оправяме трите най-важни проблема“ с поръчка с един клик,
 * трите писма след отчета, следенето на сайта с месечна проверка, и текстовете им (правилото без тире и двоеточие, EN преводи).
 */
class SiteCheckUpsellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutDefer();
        config(['creatium.notify_email' => 'team@creatium.test', 'services.telegram.bot_token' => 'TEST', 'services.telegram.chat_id' => '42']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function report(array $extra = []): array
    {
        return $extra + [
            'domain' => 'firma.bg', 'score' => 62, 'home_score' => 62, 'crawl' => null, 'sent' => true, 'checked_at' => now()->toIso8601String(),
            'issues' => [
                ['key' => 'no_title', 'severity' => 'error', 'count' => 3, 'examples' => []],
                ['key' => 'no_description', 'severity' => 'error', 'count' => 2, 'examples' => []],
                ['key' => 'broken_pages', 'severity' => 'error', 'count' => 1, 'examples' => []],
            ],
            'checks' => [],
        ];
    }

    private function lead(array $values = [], ?array $report = null): Project
    {
        if (! Category::exists()) {
            $this->seed(CategorySeeder::class);
        }
        $project = Project::create($values + [
            'name' => 'Проверка на сайт: firma.bg', 'description' => 'Проверка', 'start_date' => now(), 'status' => 'Planning', 'manager' => 'Неразпределен',
            'category_id' => Category::first()->id, 'client_email' => 'ivan@firma.bg', 'website_url' => 'https://firma.bg', 'source' => 'website',
            'service' => 'Проверка на сайт', 'locale' => 'bg',
        ]);
        $project->forceFill(['site_check_report' => $report ?? $this->report(), 'followup_stopped_at' => null])->save();

        return $project->fresh();
    }

    private function admin(string $profile = 'all'): User
    {
        if (! Role::exists()) {
            $this->seed(RoleSeeder::class);
        }
        $user = User::factory()->create(['name' => 'Борислав Костадинов', 'profile' => $profile]);
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    /** @return list<\Symfony\Component\Mime\Email> */
    private function sent(): array
    {
        return array_map(fn ($m) => $m->getOriginalMessage(), app('mail.manager')->mailer('array')->getSymfonyTransport()->messages()->all());
    }

    // ---- A. Offer and one-click order ----

    public function test_the_offer_config_has_the_owners_numbers_in_both_languages(): void
    {
        $this->assertSame(190, config('creatium.site_check.offers.fix.price'));
        $this->assertSame(7, config('creatium.site_check.offers.fix.days'));
        $this->assertSame(30, config('creatium.site_check.offers.fix.credit_days'));
        $this->assertSame(29, config('creatium.site_check.offers.monitor.price'));
        $this->assertSame(array_keys(config('creatium.site_check_offers')), array_keys(config('creatium_en.site_check_offers')));
        foreach (['fix', 'monitor'] as $offer) {
            $this->assertSame(array_keys(config("creatium.site_check_offers.$offer")), array_keys(config("creatium_en.site_check_offers.$offer")));
        }
    }

    public function test_the_order_link_must_be_signed_and_a_get_changes_nothing(): void
    {
        $lead = $this->lead();
        $this->get("/proverka-na-sait/{$lead->id}/fix")->assertForbidden();
        $this->get(Offers::url($lead, 'fix') . 'x')->assertForbidden();
        $this->post("/proverka-na-sait/{$lead->id}/fix")->assertForbidden();

        Mail::fake();
        $response = $this->get(Offers::url($lead, 'fix'))->assertOk()
            ->assertSee('Оправяме трите най-важни проблема')->assertSee('€190, еднократно')->assertSee('Потвърдете')->assertSee('name="_token"', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->assertStringNotContainsString('Благодарим ви', $response->getContent());
        $fresh = $lead->fresh();
        $this->assertNull($fresh->site_check_offer);
        $this->assertSame(0, $fresh->notes()->count());
        Mail::assertNothingSent();

        // Expired after 60 days.
        $url = Offers::url($lead, 'fix');
        $this->travel(61)->days();
        $this->get($url)->assertForbidden();
    }

    public function test_unknown_offers_and_other_leads_are_not_found(): void
    {
        $lead = $this->lead();
        $this->get(URL::temporarySignedRoute('site-check.offer', now()->addDay(), ['project' => $lead->id, 'offer' => 'free']))->assertNotFound();
        $other = $this->lead(['service' => 'Форма', 'name' => 'Запитване от Иван']);
        $this->get(Offers::url($other, 'fix'))->assertNotFound();
    }

    public function test_post_records_the_interest_once_and_notifies_the_team(): void
    {
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $lead = $this->lead();
        $url = Offers::url($lead, 'fix');

        $this->post($url)->assertOk()->assertSee('Благодарим ви')->assertSee('в рамките на един работен ден');

        $lead = $lead->fresh();
        $this->assertSame('fix', $lead->site_check_offer);
        $this->assertNotNull($lead->site_check_offer_at);
        $this->assertSame('new', $lead->stage, 'the stage is not moved');
        $this->assertSame('Свържете се за оферта „трите проблема“', $lead->next_action);
        $this->assertTrue($lead->next_action_at->lte(now('Europe/Sofia')->endOfDay()->utc()) && $lead->next_action_at->isToday() || $lead->next_action_at->gte(now()->subHour()));
        $this->assertSame(1, $lead->notes()->count());
        $this->assertSame('note', $lead->notes()->first()->kind);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'sendMessage') && str_contains($r['text'], 'Оправяме трите най-важни проблема') && str_contains($r['text'], 'firma.bg'));
        Mail::assertSent(SiteCheckOfferOrderedMail::class, fn ($m) => $m->hasTo('team@creatium.test'));

        // A second click shows the same page and adds nothing.
        $this->post($url)->assertOk()->assertSee('Благодарим ви');
        $this->get($url)->assertOk()->assertSee('Благодарим ви');
        $this->assertSame(1, $lead->fresh()->notes()->count());
        Mail::assertSent(SiteCheckOfferOrderedMail::class, 1);
        Http::assertSentCount(1);
    }

    public function test_an_existing_next_step_is_kept_and_a_different_offer_is_a_new_request(): void
    {
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $lead = $this->lead(['next_action' => 'Обадете се във вторник', 'next_action_at' => now()->addDays(2)]);

        $this->post(Offers::url($lead, 'fix'));
        $this->assertSame('Обадете се във вторник', $lead->fresh()->next_action);

        $this->post(Offers::url($lead, 'monitor'))->assertOk()->assertSee('Следене на сайта');
        $this->assertSame('monitor', $lead->fresh()->site_check_offer);
        $this->assertSame(2, $lead->fresh()->notes()->count());
    }

    public function test_the_order_pages_work_in_english_and_in_both_designs(): void
    {
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            $lead = $this->lead(['locale' => 'en', 'client_email' => "x{$design}@firma.bg"]);
            $url = Offers::url($lead, 'fix');
            $this->assertStringContainsString('/en/website-check/' . $lead->id . '/fix', $url);
            $html = $this->get($url)->assertOk()->assertSee('We fix your three most important issues')->assertSee('€190, one time')->assertSee('Confirm')->getContent();
            $this->assertStringContainsString('noindex', $html);
            $this->assertStringNotContainsString('class="design-g"', $design === 'g' ? '' : $html);
            $this->post($url)->assertOk()->assertSee('Thank you')->assertSee('within one working day');
            $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', PlainCopyTest::visibleText($this->get($url)->getContent()));

            $bg = $this->lead(['client_email' => "bg{$design}@firma.bg"]);
            $this->get(Offers::url($bg, 'monitor'))->assertOk()->assertSee('Следене на сайта')->assertSee('€29 на месец')->assertSee('Потвърдете');
        }
    }

    public function test_the_report_email_has_the_offer_and_the_signed_link_in_both_languages(): void
    {
        $lead = $this->lead();
        $report = $this->report(['reachable' => true, 'checks' => [['key' => 'https', 'status' => 'ok', 'title' => 'HTTPS', 'detail' => '']]]);

        app()->setLocale('bg');
        $html = (new SiteCheckReportMail($report, $lead))->render();
        $text = PlainCopyTest::visibleText($html);
        $this->assertStringContainsString('Оправяме трите най-важни проблема', $text);
        $this->assertStringContainsString('€190, еднократно', $text);
        $this->assertStringContainsString('Ако започнете някой от пакетите ни в рамките на 30 дни, €190 се приспадат от цената му.', $text);
        $this->assertStringContainsString('Поръчайте оправянето', $text);
        $this->assertStringContainsString('€29 на месец', $text);
        $this->assertMatchesRegularExpression('#href="[^"]*/proverka-na-sait/' . $lead->id . '/fix\?[^"]*signature=#', $html);
        $this->assertMatchesRegularExpression('#href="[^"]*/proverka-na-sait/' . $lead->id . '/monitor\?[^"]*signature=#', $html);
        $this->assertStringContainsString('padding:30px 28px 0', $html);
        // The offer sits after the top three and before the "who we are" part.
        $this->assertTrue(strpos($html, 'Най-важното, което открихме') < strpos($html, 'Поръчайте оправянето') && strpos($html, 'Поръчайте оправянето') < strpos($html, 'Кои сме'));

        app()->setLocale('en');
        $enLead = $this->lead(['locale' => 'en', 'client_email' => 'en@firma.bg']);
        $html = (new SiteCheckReportMail($report, $enLead))->render();
        $text = PlainCopyTest::visibleText($html);
        $this->assertStringContainsString('We fix your three most important issues', $text);
        $this->assertStringContainsString('€190, one time', $text);
        $this->assertStringContainsString('If you start one of our packages within 30 days, €190 is deducted from its price.', $text);
        $this->assertStringContainsString('Order the fix', $text);
        $this->assertMatchesRegularExpression('#href="[^"]*/en/website-check/' . $enLead->id . '/fix\?[^"]*signature=#', $html);

        // Without a lead (a preview) there is no offer; with no problems there is none either.
        app()->setLocale('bg');
        $this->assertStringNotContainsString('Поръчайте оправянето', (new SiteCheckReportMail($report))->render());
        $this->assertStringNotContainsString('Поръчайте оправянето', (new SiteCheckReportMail(array_merge($report, ['issues' => []]), $lead))->render());
    }

    public function test_the_real_flow_sends_the_report_with_the_order_link(): void
    {
        config(['creatium.design' => 'current']);
        $lead = $this->lead();
        Mail::fake();
        $this->app->instance(SiteAudit::class, $this->fakeAudit($this->report(['reachable' => true, 'url' => 'https://firma.bg', 'checks' => []])));
        app(\App\Support\SiteCheck\SendSiteCheckReport::class)($lead, 'firma.bg', 'ivan@firma.bg', 'bg');
        Mail::assertSent(SiteCheckReportMail::class, fn ($m) => $m->hasTo('ivan@firma.bg') && $m->lead?->is($lead) && str_contains($m->render(), '/proverka-na-sait/' . $lead->id . '/fix'));
    }

    private function fakeAudit(array $report): SiteAudit
    {
        $audit = \Mockery::mock(SiteAudit::class);
        $audit->shouldReceive('run')->andReturn($report);

        return $audit;
    }

    public function test_the_page_explains_the_two_paid_steps_in_both_designs_and_languages(): void
    {
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            $bg = $this->get('/proverka-na-sait')->assertOk()->getContent();
            foreach (['След проверката', 'Оправяме трите най-важни проблема', '€190, еднократно', 'Готово до 7 дни', '30 дни', 'Следене на сайта', '€29 на месец', 'на всеки 6 часа'] as $needle) {
                $this->assertStringContainsStringIgnoringCase($needle, $bg, "$design bg $needle");
            }
            $this->assertStringNotContainsString('signature=', $bg, 'no order button without a report');
            $this->assertStringContainsString('до три кратки писма', $bg);
            $this->assertStringContainsString('с едно кликване', $bg);

            $en = $this->get('/en/website-check')->assertOk()->getContent();
            foreach (['After the check', 'We fix your three most important issues', '€190, one time', 'Done within 7 days', 'Website monitoring', '€29 a month', 'up to three short emails', 'unsubscribe with one click'] as $needle) {
                $this->assertStringContainsStringIgnoringCase($needle, $en, "$design en $needle");
            }
            $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', PlainCopyTest::visibleText($en), "$design english page");
            $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($bg)), "$design bg copy");
            $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($en)), "$design en copy");
        }
    }

    public function test_the_domain_can_be_prefilled_from_the_link_in_the_third_email(): void
    {
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            $this->get('/proverka-na-sait?site=Firma.BG')->assertOk()->assertSee('value="firma.bg"', false);
            $html = $this->get('/proverka-na-sait?site=' . urlencode('"><script>alert(1)</script>'))->assertOk()->getContent();
            $this->assertStringNotContainsString('<script>alert(1)', $html);
            $this->get('/proverka-na-sait')->assertOk()->assertSee('name="domain"', false);
        }
    }

    // ---- B. Follow-ups ----

    public function test_the_right_step_is_due_on_the_right_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 12:00', 'Europe/Sofia'));
        $lead = $this->lead(['created_at' => now()], $this->report());
        $lead->forceFill(['site_check_report' => $this->report()])->save();
        $lead = $lead->fresh();

        $this->assertNull(FollowUps::due($lead));
        Carbon::setTestNow(now()->addDay());
        $this->assertNull(FollowUps::due($lead->fresh()), 'day 1');
        Carbon::setTestNow(now()->addDay());
        $this->assertSame(1, FollowUps::due($lead->fresh()), 'day 2');

        Mail::fake();
        $this->assertTrue(app(FollowUps::class)->send($lead->fresh(), 1));
        $lead = $lead->fresh();
        $this->assertSame(1, $lead->followup_step);
        $this->assertNotNull($lead->followup_sent_at);

        Carbon::setTestNow(now()->addDay());
        $this->assertNull(FollowUps::due($lead->fresh()), 'day 3');
        Carbon::setTestNow(now()->addDays(2));
        $this->assertSame(2, FollowUps::due($lead->fresh()), 'day 5');
        app(FollowUps::class)->send($lead->fresh(), 2);
        Carbon::setTestNow(now()->addDays(8));
        $this->assertNull(FollowUps::due($lead->fresh()), 'day 13');
        Carbon::setTestNow(now()->addDay());
        $this->assertSame(3, FollowUps::due($lead->fresh()), 'day 14');
        app(FollowUps::class)->send($lead->fresh(), 3);
        Carbon::setTestNow(now()->addDays(3));
        $this->assertNull(FollowUps::due($lead->fresh()), 'all three were sent');

        // The log: three rows in client_emails.
        $this->assertSame(['site-check-followup-1', 'site-check-followup-2', 'site-check-followup-3'], ClientEmail::orderBy('id')->pluck('template')->all());
    }

    public function test_a_late_server_does_not_send_two_emails_at_once_or_very_old_ones(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 12:00', 'Europe/Sofia'));
        $lead = $this->lead();
        Mail::fake();
        Carbon::setTestNow(now()->addDays(6));
        $this->assertSame(1, FollowUps::due($lead->fresh()));
        app(FollowUps::class)->send($lead->fresh(), 1);
        $this->assertNull(FollowUps::due($lead->fresh()), 'step 2 waits at least a day after step 1');
        Carbon::setTestNow(now()->addDay()->addMinute());
        $this->assertSame(2, FollowUps::due($lead->fresh()));
        Carbon::setTestNow(now()->addDays(40));
        $this->assertNull(FollowUps::due($lead->fresh()), 'a report older than 30 days gets nothing');
    }

    public function test_every_skip_condition(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 12:00', 'Europe/Sofia'));
        $make = fn (array $values = [], ?array $report = null) => $this->lead($values + ['client_email' => 'p' . Project::count() . '@firma.bg'], $report);
        $okay = $make();
        Carbon::setTestNow(now()->addDays(3));
        $this->assertSame(1, FollowUps::due($okay->fresh()), 'control');

        $cases = [
            'stage won' => $make(['stage' => 'won']),
            'stage lost' => $make(['stage' => 'lost']),
            'stage offer' => $make(['stage' => 'offer']),
            'stopped' => tap($make())->forceFill(['followup_stopped_at' => now()])->save(),
            'offer chosen' => tap($make())->forceFill(['site_check_offer' => 'fix'])->save(),
            'report not sent' => $make([], $this->report(['sent' => false])),
            'no issues' => $make([], $this->report(['issues' => []])),
            'only notices' => $make([], $this->report(['issues' => [['key' => 'llms_missing', 'severity' => 'notice', 'count' => 1, 'examples' => []]]])),
            'failed report' => $make([], ['domain' => 'firma.bg', 'failed' => true, 'checked_at' => now()->toIso8601String()]),
            'not a site check' => $make(['service' => 'Форма']),
            'no email' => $make(['client_email' => null]),
            'three sent' => tap($make())->forceFill(['followup_step' => 3])->save(),
        ];
        $projects = [
            'stage won' => Project::where('stage', 'won')->first(), 'stage lost' => Project::where('stage', 'lost')->first(), 'stage offer' => Project::where('stage', 'offer')->first(),
            'stopped' => Project::whereNotNull('followup_stopped_at')->first(), 'offer chosen' => Project::whereNotNull('site_check_offer')->first(),
            'three sent' => Project::where('followup_step', 3)->first(), 'no email' => Project::whereNull('client_email')->first(), 'not a site check' => Project::where('service', 'Форма')->first(),
        ];
        foreach ($projects as $name => $project) {
            $this->assertNull(FollowUps::due($project), $name);
        }
        $byEmail = fn ($n) => Project::where('client_email', $n)->first();
        foreach (Project::all() as $project) {
            if (in_array($project->id, array_map(fn ($p) => $p->id, array_filter($projects)), true) || $project->is($okay)) {
                continue;
            }
            $this->assertNull(FollowUps::due($project), 'report based case ' . $project->id);
        }

        // A suppressed address (an earlier unsubscribe, also from the outreach emails) is skipped.
        OutreachSuppression::add('IVAN@firma.bg', 'unsubscribed');
        $suppressed = $make(['client_email' => 'ivan@firma.bg']);
        $this->assertNull(FollowUps::due($suppressed));
    }

    public function test_the_tick_sends_in_the_time_window_only_and_at_most_ten(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-12-01 08:00', 'Europe/Sofia'));
        foreach (range(1, 12) as $i) {
            $this->lead(['client_email' => "p{$i}@firma.bg"]);
        }
        Carbon::setTestNow(Carbon::parse('2026-12-03 08:59', 'Europe/Sofia'));
        $this->assertSame(0, app(FollowUps::class)->run(), 'before 09:00');
        Carbon::setTestNow(Carbon::parse('2026-12-03 19:00', 'Europe/Sofia'));
        $this->assertSame(0, app(FollowUps::class)->run(), 'from 19:00');
        Carbon::setTestNow(Carbon::parse('2026-12-03 09:00', 'Europe/Sofia'));
        $this->assertSame(10, app(FollowUps::class)->run());
        $this->assertSame(2, Project::where('followup_step', 0)->count());
        $this->assertSame(10, ClientEmail::count());
    }

    public function test_the_middleware_runs_the_tick_once_per_ten_minutes_and_only_when_enabled(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 10:00', 'Europe/Sofia'));
        $this->lead();
        Carbon::setTestNow(Carbon::parse('2026-12-03 10:00', 'Europe/Sofia'));

        Mail::fake();
        $this->get('/uslugi');
        Mail::assertNothingSent();

        config(['creatium.site_check.followups.enabled' => true]);
        $this->post('/uslugi');
        Mail::assertNothingSent();
        $this->get('/uslugi');
        Mail::assertSent(SiteCheckFollowUpMail::class, 1);
        $this->get('/uslugi');
        Mail::assertSent(SiteCheckFollowUpMail::class, 1);
    }

    public function test_the_emails_content_headers_and_plain_text(): void
    {
        config(['mail.default' => 'array']);
        Carbon::setTestNow(Carbon::parse('2026-12-01 10:00', 'Europe/Sofia'));
        $lead = $this->lead();
        $fresh = fn () => $lead->fresh();
        $send = function (int $step) use ($fresh) {
            app(FollowUps::class)->send($fresh(), $step);

            return collect($this->sent())->last();
        };

        $one = $send(1);
        $this->assertSame('Едно конкретно нещо за firma.bg', $one->getSubject());
        $this->assertSame('ivan@firma.bg', $one->getTo()[0]->getAddress());
        $this->assertStringContainsString('Страници без заглавие за Google (3)', $one->getHtmlBody());
        $this->assertStringNotContainsString('Страници без описание за Google (2)', $one->getHtmlBody(), 'only the first issue');
        $this->assertStringContainsString('Страници без заглавие за Google (3)', $one->getTextBody(), 'a plain text part exists');
        $unsubscribe = $one->getHeaders()->get('List-Unsubscribe')->getBodyAsString();
        $this->assertStringContainsString('/otpisvane/proverka/' . $lead->id, $unsubscribe);
        $this->assertSame('List-Unsubscribe=One-Click', $one->getHeaders()->get('List-Unsubscribe-Post')->getBodyAsString());
        $this->assertStringContainsString('Отпишете се с едно кликване', $one->getHtmlBody());
        $this->assertStringContainsString('/otpisvane/proverka/' . $lead->id, $one->getTextBody());

        $two = $send(2);
        $html = $two->getHtmlBody();
        $this->assertSame('Оправяме трите най-важни проблема на firma.bg', $two->getSubject());
        foreach (['Страници без заглавие за Google (3)', 'Страници без описание за Google (2)', 'Счупени вътрешни връзки, 404 (1)', '€190, еднократно', 'Готово до 7 дни', 'Поръчайте оправянето'] as $needle) {
            $this->assertStringContainsString($needle, PlainCopyTest::visibleText($html), $needle);
        }
        $this->assertMatchesRegularExpression('#/proverka-na-sait/' . $lead->id . '/fix\?[^"]*signature=#', $html);

        $three = $send(3);
        $this->assertSame('Да проверим ли пак firma.bg?', $three->getSubject());
        $this->assertStringContainsString('/proverka-na-sait?site=firma.bg', $three->getHtmlBody());
        $this->assertStringContainsString('€29 на месец', PlainCopyTest::visibleText($three->getHtmlBody()));
        $this->assertMatchesRegularExpression('#/proverka-na-sait/' . $lead->id . '/monitor\?#', $three->getHtmlBody());
    }

    public function test_the_emails_are_in_the_language_of_the_lead(): void
    {
        config(['mail.default' => 'array']);
        $lead = $this->lead(['locale' => 'en']);
        app()->setLocale('bg');
        foreach ([1, 2, 3] as $step) {
            app(FollowUps::class)->send($lead->fresh(), $step);
            $mail = collect($this->sent())->last();
            $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', $mail->getSubject());
            $text = PlainCopyTest::visibleText($mail->getHtmlBody());
            $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', $text, "step $step");
            $this->assertSame([], PlainCopyTest::offences($text . ' ' . $mail->getSubject()), "step $step");
            $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', $mail->getTextBody(), "step $step plain");
        }
        $this->assertSame('bg', app()->getLocale(), 'the locale is restored');
        $this->assertStringContainsString('/en/website-check/' . $lead->id . '/monitor', collect($this->sent())->last()->getHtmlBody());
    }

    public function test_the_followup_emails_have_no_dashes_or_colons_in_bulgarian(): void
    {
        config(['mail.default' => 'array']);
        $lead = $this->lead();
        foreach ([1, 2, 3] as $step) {
            app(FollowUps::class)->send($lead->fresh(), $step);
            $mail = collect($this->sent())->last();
            $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($mail->getHtmlBody()) . ' ' . $mail->getSubject()), "step $step");
        }
    }

    public function test_one_click_unsubscribe_stops_the_emails_and_suppresses_the_address(): void
    {
        $lead = $this->lead();
        $url = Offers::unsubscribeUrl($lead);

        $this->post("/otpisvane/proverka/{$lead->id}")->assertForbidden();
        $this->assertNull($lead->fresh()->followup_stopped_at);

        // The one-click POST comes from the mail provider with no session or CSRF token: the path is exempt.
        $route = app('router')->getRoutes()->getByName('site-check.unsubscribe');
        $this->assertTrue(\Illuminate\Support\Str::is('otpisvane/*', $route->uri()));
        $this->assertContains('otpisvane/*', (new class(app(), app('encrypter')) extends \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken { public function excluded() { return $this->except; } })->excluded() ?: ['otpisvane/*']);

        $this->post($url)->assertNoContent();
        $lead = $lead->fresh();
        $this->assertNotNull($lead->followup_stopped_at);
        $this->assertTrue(OutreachSuppression::where('email', 'ivan@firma.bg')->exists());
        $this->assertSame(1, $lead->notes()->count());

        $this->get($url)->assertOk()->assertSee('Отписахте се');
        $this->assertSame(1, $lead->fresh()->notes()->count(), 'a second click adds nothing');
        $this->assertSame(1, OutreachSuppression::count());

        Carbon::setTestNow(now()->addDays(3));
        $this->assertNull(FollowUps::due($lead->fresh()));

        $en = $this->lead(['locale' => 'en', 'client_email' => 'en@firma.bg']);
        $this->get(Offers::unsubscribeUrl($en))->assertOk()->assertSee('You are unsubscribed');
    }

    public function test_failures_are_logged_do_not_advance_and_stop_after_three(): void
    {
        $lead = $this->lead();
        Mail::shouldReceive('to')->times(3)->andThrow(new \RuntimeException('smtp down'));

        $this->assertFalse(app(FollowUps::class)->send($lead->fresh(), 1));
        $fresh = $lead->fresh();
        $this->assertSame([0, 1], [$fresh->followup_step, $fresh->followup_failures]);
        $this->assertNull($fresh->followup_stopped_at);
        $this->assertNull($fresh->followup_sent_at);
        $this->assertStringContainsString('smtp down', ClientEmail::first()->error);
        $this->assertNull(ClientEmail::first()->sent_at);

        app(FollowUps::class)->send($lead->fresh(), 1);
        $this->assertNull($lead->fresh()->followup_stopped_at);
        app(FollowUps::class)->send($lead->fresh(), 1);
        $fresh = $lead->fresh();
        $this->assertSame([0, 3], [$fresh->followup_step, $fresh->followup_failures]);
        $this->assertNotNull($fresh->followup_stopped_at);
        $this->assertSame(1, $fresh->notes()->where('body', 'like', '%спрени%')->count());
        Carbon::setTestNow(now()->addDays(3));
        $this->assertNull(FollowUps::due($lead->fresh()), 'it stays stopped');
    }

    public function test_the_migration_marks_old_site_checks_as_stopped(): void
    {
        $this->seed(CategorySeeder::class);
        $old = Project::create(['name' => 'Проверка', 'description' => 'x', 'start_date' => now(), 'status' => 'Planning', 'manager' => 'X', 'category_id' => Category::first()->id, 'service' => 'Проверка на сайт', 'source' => 'website']);
        $migration = require base_path('database/migrations/2026_11_26_000001_add_site_check_upsell_to_projects.php');
        \Illuminate\Support\Facades\Schema::table('projects', fn ($t) => $t->dropColumn(['site_check_offer', 'site_check_offer_at', 'followup_step', 'followup_sent_at', 'followup_stopped_at', 'followup_failures', 'monthly_check', 'monthly_check_at']));
        $migration->up();
        $this->assertNotNull($old->fresh()->followup_stopped_at);
    }

    // ---- Admin ----

    public function test_the_admin_shows_the_step_stops_the_emails_and_lists_orders_on_today(): void
    {
        $admin = $this->admin();
        $lead = $this->lead(['followup_step' => 2, 'read_at' => now()]);
        $this->actingAs($admin)->get('/admin/proverki')->assertOk()->assertSee('Писма след проверката')->assertSee('2 от 3 изпратени')->assertSee('Спри писмата');
        $this->actingAs($admin)->get(route('admin.projects.show', $lead))->assertOk()->assertSee('2 от 3 изпратени')->assertSee('Спри писмата');

        $this->actingAs($admin)->post(route('admin.projects.followup-stop', $lead))->assertSessionHas('success');
        $this->assertNotNull($lead->fresh()->followup_stopped_at);
        $this->actingAs($admin)->get('/admin/proverki')->assertSee('Спрени')->assertDontSee('Спри писмата');

        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $ordered = $this->lead(['client_email' => 'o@firma.bg', 'website_url' => 'https://porachka.bg']);
        $this->post(Offers::url($ordered, 'fix'));
        $this->actingAs($admin)->get('/admin/proverki')->assertSee('Избрана оферта')->assertSee('Поиска „Оправяме трите най-важни проблема“');
        $this->actingAs($admin)->get('/admin/dnes')->assertOk()->assertSee('Поръчани след проверка')->assertSee('porachka.bg');
        $this->actingAs($this->admin('brand'))->get('/admin/dnes')->assertDontSee('Поръчани след проверка');
        $this->assertContains('sales-offers', \App\Support\Admin\Profiles::todaySections('sales')['']);
    }

    public function test_the_project_form_has_the_monthly_check_box(): void
    {
        $this->seed(CategorySeeder::class);
        $admin = $this->admin();
        $base = ['name' => 'Лозата', 'description' => 'Сайт', 'start_date' => '2026-11-01', 'status' => 'In Progress', 'manager' => 'Екип', 'category_id' => Category::first()->id,
            'website_url' => 'https://lozata.bg', 'client_email' => 'l@lozata.bg', 'monthly_fee' => 29];
        $this->actingAs($admin)->get('/admin/projects/create')->assertSee('Месечна проверка на сайта');
        $this->actingAs($admin)->post('/admin/projects', $base + ['monthly_check' => '1'])->assertSessionHasNoErrors();
        $project = Project::where('name', 'Лозата')->first();
        $this->assertTrue($project->monthly_check);
        $this->actingAs($admin)->get(route('admin.projects.edit', $project))->assertSee('name="monthly_check"', false)->assertSee('checked', false);
        $this->actingAs($admin)->put(route('admin.projects.update', $project), $base)->assertSessionHasNoErrors();
        $this->assertFalse($project->fresh()->monthly_check);
    }

    // ---- C. Monthly check ----

    private function subscriber(array $values = []): Project
    {
        return $this->lead($values + ['name' => 'Лозата', 'service' => 'Поддръжка', 'source' => 'manual', 'monthly_fee' => 29, 'monthly_check' => true, 'status' => 'In Progress', 'client_email' => 'l@lozata.bg', 'website_url' => 'https://lozata.bg'], $this->report(['domain' => 'lozata.bg', 'score' => 62]));
    }

    public function test_the_monthly_check_takes_one_project_and_compares_the_scores(): void
    {
        config(['mail.default' => 'array']);
        Carbon::setTestNow(Carbon::parse('2026-12-01 10:00', 'Europe/Sofia'));
        $due = $this->subscriber(['monthly_check_at' => now()->subDays(31)]);
        $never = $this->subscriber(['name' => 'Нов', 'client_email' => 'n@lozata.bg', 'website_url' => 'https://nov.bg']);
        $recent = $this->subscriber(['name' => 'Скорошен', 'monthly_check_at' => now()->subDays(10), 'website_url' => 'https://skoroshen.bg']);
        $cancelled = $this->subscriber(['name' => 'Спрян', 'status' => 'Cancelled', 'website_url' => 'https://spryan.bg']);
        $noSite = $this->subscriber(['name' => 'Без сайт', 'website_url' => null]);
        $off = $this->subscriber(['name' => 'Без отметка', 'monthly_check' => false, 'website_url' => 'https://off.bg']);

        $this->assertEqualsCanonicalizing([$due->id, $never->id], MonthlyChecks::due()->pluck('id')->all());

        $this->app->instance(SiteAudit::class, $this->fakeAudit($this->report(['reachable' => true, 'url' => 'https://lozata.bg', 'score' => 80, 'domain' => 'lozata.bg', 'checks' => [['key' => 'https', 'status' => 'ok', 'title' => 'HTTPS', 'detail' => '']]])));
        $this->assertSame(1, app(MonthlyChecks::class)->run(1), 'one project per tick');

        // The project that never had a monthly report is first, then the oldest.
        $this->assertNotNull($never->fresh()->monthly_check_at);
        $this->assertSame(80, $never->fresh()->site_check_report['score']);
        $this->assertSame($due->monthly_check_at->timestamp, $due->fresh()->monthly_check_at->timestamp);

        $this->assertSame(1, app(MonthlyChecks::class)->run(1));
        $fresh = $due->fresh();
        $this->assertSame(80, $fresh->site_check_report['score']);
        $this->assertTrue($fresh->monthly_check_at->isToday());
        $this->assertSame(0, app(MonthlyChecks::class)->run(1), 'nothing else is due');
        $this->assertNull($recent->fresh()->monthly_check_at->lt(now()->subDays(11)) ? 'changed' : null);

        $mails = array_values(array_filter($this->sent(), fn ($m) => $m->getTo()[0]->getAddress() === 'l@lozata.bg'));
        $this->assertCount(1, $mails);
        $text = PlainCopyTest::visibleText($mails[0]->getHtmlBody());
        $this->assertStringContainsString('Резултатът е 80 от 100, преди месец беше 62.', $text);
        $this->assertStringContainsString('Месечна проверка на lozata.bg, оценка 80 от 100', $mails[0]->getSubject());
        $this->assertStringNotContainsString('Кои сме', $text);
        $this->assertStringNotContainsString('Поръчайте оправянето', $text);
        $this->assertSame([], PlainCopyTest::offences($text . ' ' . $mails[0]->getSubject()));
        $this->assertSame('monthly-site-check', ClientEmail::where('to_email', 'l@lozata.bg')->value('template'));

        // 30 days later it is due again, and the previous score is now 80.
        Carbon::setTestNow(now()->addDays(30)->addMinute());
        $this->assertContains($due->id, MonthlyChecks::due()->pluck('id')->all());
    }

    public function test_a_failed_monthly_check_is_retried_tomorrow_not_every_tick(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 10:00', 'Europe/Sofia'));
        $project = $this->subscriber();
        $audit = \Mockery::mock(SiteAudit::class);
        $audit->shouldReceive('run')->once()->andThrow(new \RuntimeException('boom'));
        $this->app->instance(SiteAudit::class, $audit);
        Mail::fake();

        $this->assertSame(1, app(MonthlyChecks::class)->run(1));
        $this->assertSame(62, $project->fresh()->site_check_report['score'], 'the old report stays');
        $this->assertSame(0, MonthlyChecks::due()->count());
        Mail::assertNothingSent();
        Carbon::setTestNow(now()->addDays(1)->addMinute());
        $this->assertSame(1, MonthlyChecks::due()->count());
    }

    public function test_the_middleware_also_runs_the_monthly_check_when_enabled(): void
    {
        config(['creatium.site_check.monthly.enabled' => true]);
        $project = $this->subscriber();
        $this->app->instance(SiteAudit::class, $this->fakeAudit($this->report(['reachable' => true, 'score' => 70, 'domain' => 'lozata.bg', 'checks' => []])));
        Mail::fake();
        $this->get('/uslugi');
        $this->assertSame(70, $project->fresh()->site_check_report['score']);
        $this->get('/uslugi');
        Mail::assertSent(SiteCheckReportMail::class, 1);
    }

    public function test_the_artisan_commands_exist(): void
    {
        $this->artisan('creatium:site-check-followups', ['--dry-run' => true])->assertSuccessful();
        $this->artisan('creatium:site-check-followups', ['--ignore-hours' => true])->assertSuccessful();
        $this->artisan('creatium:monthly-checks')->assertSuccessful();
    }

    // ---- Copy ----

    public function test_the_new_pages_and_mails_follow_the_copy_rule(): void
    {
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $report = $this->report(['reachable' => true, 'checks' => [['key' => 'https', 'status' => 'ok', 'title' => 'HTTPS', 'detail' => '']]]);
        foreach (['bg', 'en'] as $locale) {
            app()->setLocale($locale);
            $lead = $this->lead(['locale' => $locale, 'client_email' => "c{$locale}@firma.bg"]);
            $mails = [new SiteCheckReportMail($report, $lead), new SiteCheckReportMail($report, null, true, 55)];
            foreach ($mails as $mail) {
                $text = PlainCopyTest::visibleText($mail->render()) . ' ' . $mail->envelope()->subject;
                $this->assertSame([], PlainCopyTest::offences($text), "$locale report email");
            }
            foreach (['fix', 'monitor'] as $offer) {
                foreach (['current', 'g'] as $design) {
                    config(['creatium.design' => $design]);
                    $page = PlainCopyTest::visibleText($this->get(Offers::url($lead, $offer))->getContent());
                    $thanks = PlainCopyTest::visibleText($this->post(Offers::url($lead, $offer))->getContent());
                    $this->assertSame([], PlainCopyTest::offences($page . ' ' . $thanks), "$locale $offer $design");
                }
            }
            $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($this->get(Offers::unsubscribeUrl($lead))->getContent())), "$locale unsubscribed");
        }
        $this->assertSame([], PlainCopyTest::offences(strip_tags((new SiteCheckOfferOrderedMail($lead, 'fix'))->render())));
        foreach (['bg', 'en'] as $locale) {
            foreach (['fix', 'monitor'] as $offer) {
                $block = config(($locale === 'en' ? 'creatium_en' : 'creatium') . '.site_check_offers');
                array_walk_recursive($block, function ($text) use ($locale) {
                    $text = strtr($text, [':price' => '€1', ':days' => '7', ':credit_days' => '30']);
                    $this->assertSame([], PlainCopyTest::offences($text), "config $locale $text");
                    if ($locale === 'en') {
                        $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', $text);
                    }
                });
            }
        }
        // The consultation is never called free here.
        $this->assertStringNotContainsString('безплатна консултация', mb_strtolower(json_encode(config('creatium.site_check_offers'), JSON_UNESCAPED_UNICODE)));
    }
}
