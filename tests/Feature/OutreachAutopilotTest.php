<?php

namespace Tests\Feature;

use App\Mail\OutreachMail;
use App\Models\ClientEmail;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\OutreachSearch;
use App\Models\OutreachSetting;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\Profiles;
use App\Support\Outreach\Autopilot;
use App\Support\Outreach\Drafts;
use App\Support\Outreach\EmailVerifier;
use App\Support\Outreach\Markets;
use App\Support\SiteCheck\HostResolver;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Автопилотът (намира, оценява, подготвя чернови, никога не праща) и „За одобрение“. */
class OutreachAutopilotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(HostResolver::class, new class extends HostResolver
        {
            public function publicIp(string $host): ?string
            {
                return '93.184.216.34';
            }
        });
        $this->app->instance(EmailVerifier::class, new class extends EmailVerifier
        {
            protected function acceptsMail(string $domain): bool
            {
                return true;
            }
        });
        // Сряда, 10:00 в София: работно време.
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00', 'Europe/Sofia'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function admin(string $profile = 'sales'): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['name' => 'Владимир Петров', 'profile' => $profile]);
        $user->roles()->attach(Role::where('slug', 'project-manager')->value('id'));

        return $user;
    }

    private function enable(array $plan = null, array $extra = []): void
    {
        config(['creatium.outreach.autopilot.enabled' => true, 'services.serper.key' => 'k']);
        OutreachSetting::put('autopilot', $extra + ['plan' => $plan ?? ['gb' => ['enabled' => true, 'cities' => ['Manchester'], 'categories' => ['dentist', 'builder']]]]);
    }

    private function fakeUk(): void
    {
        Http::fake([
            'google.serper.dev/places' => Http::response(['places' => [
                ['title' => 'Acme Dental', 'cid' => '901', 'website' => 'https://acmedental.co.uk', 'phoneNumber' => '0161 496 0000'],
                ['title' => 'Sole Trader Smiles', 'cid' => '902', 'website' => 'https://smiles.co.uk', 'phoneNumber' => '0161 496 0002'],
            ]]),
            'https://acmedental.co.uk/' => Http::response('<html><head><title>Acme Dental</title></head><body><p>hello@acmedental.co.uk</p> <footer>Acme Dental Ltd. Company number 12345678</footer></body></html>'),
            'https://smiles.co.uk/' => Http::response('<html><head><title>Smiles</title></head><body><p>info@smiles.co.uk</p></body></html>'),
            '*' => Http::response('', 404),
        ]);
    }

    private function prospect(array $values = []): OutreachProspect
    {
        static $n = 0;
        $n++;

        return OutreachProspect::create($values + [
            'email' => "info{$n}@firm{$n}.test", 'company' => "Firm {$n}", 'website' => "https://firm{$n}.test", 'email_status' => 'published', 'status' => 'new',
            'country' => 'ie', 'language' => 'en', 'city' => 'Dublin', 'industry' => 'dentist', 'score' => 70, 'site_checked_at' => now(),
            'findings' => [['title' => 'Не е настроен за телефон', 'detail' => 'x', 'status' => 'fail']],
            'draft_at' => now(), 'draft_subject' => 'Findings from checking the Firm website', 'draft_observation' => "We checked the website.\n– The website is not set up for mobile phones. Most customers use a phone.",
        ]);
    }

    private function configureMail(int $limit = 5): void
    {
        config(['creatium.outreach.from_address' => 'info@creativemoa.site', 'creatium.outreach.daily_limit' => $limit, 'mail.mailers.outreach.host' => 'smtp.example.test']);
    }

    // ---- Автопилот ----

    public function test_it_is_off_by_default_and_does_nothing(): void
    {
        $this->fakeUk();
        $this->assertFalse(Autopilot::enabled());
        $this->assertSame('off', app(Autopilot::class)->tick()['status']);
        $this->assertSame(0, OutreachSearch::count());
        Http::assertNothingSent();
        $this->artisan('creatium:autopilot')->assertSuccessful()->expectsOutputToContain('изключен');
    }

    public function test_a_step_searches_scores_and_drafts_but_never_sends_anything(): void
    {
        Mail::fake();
        $this->configureMail();
        $this->fakeUk();
        $this->enable();

        $result = app(Autopilot::class)->tick();

        $search = OutreachSearch::sole();
        $this->assertSame(['gb', 'en', 'Manchester', 'dentist', null], [$search->country, $search->language, $search->city, $search->category, $search->user_id]);
        $this->assertSame('done', $result['status']);
        $this->assertSame('Зъболекари, Manchester (Обединено кралство)', $search->label());

        // Фирмата (Ltd) е с чернова на английски, еднолично лице без доказателство е само за обаждане: чернова без писмо.
        $company = OutreachProspect::where('email', 'hello@acmedental.co.uk')->firstOrFail();
        $sole = OutreachProspect::where('email', 'info@smiles.co.uk')->firstOrFail();
        $this->assertNotNull($company->draft_at);
        $this->assertStringStartsWith('Findings from checking the', $company->draft_subject);
        $this->assertStringContainsString('we checked the website of Acme Dental (acmedental.co.uk)', $company->draft_observation);
        $this->assertNotNull($sole->draft_at);
        $this->assertNull($sole->draft_subject);
        $this->assertSame($search->id, $company->outreach_search_id);

        Mail::assertNothingSent();
        $this->assertSame(0, ClientEmail::count());
        $this->assertSame(['new', 'new'], [$company->status, $sole->status]);
        $this->assertSame(2, Autopilot::waiting());
        $this->assertSame(1, Autopilot::pagesToday());
        $this->assertSame(1, Autopilot::searchesToday());
    }

    public function test_it_only_runs_in_working_hours_and_once_every_ten_minutes(): void
    {
        $this->fakeUk();
        $this->enable();

        Carbon::setTestNow(Carbon::parse('2026-11-18 21:30', 'Europe/Sofia'));
        $this->assertSame('hours', app(Autopilot::class)->tick()['status']);
        Carbon::setTestNow(Carbon::parse('2026-11-18 07:59', 'Europe/Sofia'));
        $this->assertSame('hours', app(Autopilot::class)->tick()['status']);
        $this->assertSame(0, OutreachSearch::count());

        Carbon::setTestNow(Carbon::parse('2026-11-18 08:00', 'Europe/Sofia'));
        $this->assertNotContains(app(Autopilot::class)->tick()['status'], ['hours', 'wait', 'off']);
        $this->assertSame('wait', app(Autopilot::class)->tick()['status'], 'a second step within ten minutes waits');
        $this->assertSame(1, OutreachSearch::count());
        // Командата не чака и не гледа часа.
        Carbon::setTestNow(Carbon::parse('2026-11-18 23:00', 'Europe/Sofia'));
        $this->assertNotSame('hours', app(Autopilot::class)->tick(force: true)['status']);
    }

    public function test_a_running_step_holds_the_lock(): void
    {
        $this->fakeUk();
        $this->enable();
        $lock = Cache::lock('outreach-autopilot', 60);
        $this->assertTrue($lock->get());

        $this->assertSame('busy', app(Autopilot::class)->tick(force: true)['status']);
        $this->assertSame(0, OutreachSearch::count());
        $lock->release();
        $this->assertNotSame('busy', app(Autopilot::class)->tick(force: true)['status']);
    }

    public function test_the_daily_caps_stop_it(): void
    {
        $this->fakeUk();
        $this->enable(null, ['daily_new' => 2, 'daily_searches' => 1]);

        // Една търсене днес вече има: повече не се започват.
        app(Autopilot::class)->tick(force: true);
        $this->assertSame(1, OutreachSearch::count());
        $this->assertSame(2, Autopilot::newToday());
        // Две нови бизнеса днес: спира за деня, без нищо ново.
        $this->assertSame('daily_new', app(Autopilot::class)->tick(force: true)['status']);

        OutreachSetting::put('autopilot', ['daily_new' => 99, 'daily_searches' => 1, 'plan' => ['gb' => ['enabled' => true, 'cities' => ['Manchester'], 'categories' => ['dentist', 'builder']]]]);
        $this->assertSame('daily_searches', app(Autopilot::class)->tick(force: true)['status']);
        $this->assertSame(1, OutreachSearch::count());

        // Нов ден: пак може.
        Carbon::setTestNow(Carbon::parse('2026-11-19 10:00', 'Europe/Sofia'));
        $this->assertSame('done', app(Autopilot::class)->tick(force: true)['status']);
        $this->assertSame(2, OutreachSearch::count());
    }

    public function test_it_rotates_through_the_plan_and_does_not_repeat_within_thirty_days(): void
    {
        $this->fakeUk();
        $this->enable(['gb' => ['enabled' => true, 'cities' => ['Manchester', 'Leeds'], 'categories' => ['dentist']], 'nl' => ['enabled' => true, 'cities' => ['Utrecht'], 'categories' => ['dentist']],
            'ie' => ['enabled' => false, 'cities' => [], 'categories' => ['dentist']]], ['daily_new' => 100, 'daily_searches' => 10]);
        $auto = app(Autopilot::class);

        $picked = [];
        for ($i = 0; $i < 4; $i++) {
            $next = $auto->nextSearch(Autopilot::settings());
            $picked[] = $next ? $next['country'] . ':' . $next['city'] : null;
            if ($next) {
                OutreachSearch::create($next + ['language' => 'en', 'status' => 'done']);
            }
        }
        // Пазарите се редуват; Ирландия е изключена; след три търсения няма какво ново.
        $this->assertSame(['gb:Manchester', 'nl:Utrecht', 'gb:Leeds', null], $picked);

        // След 31 дни пак се търси.
        Carbon::setTestNow(now()->addDays(31));
        $this->assertSame('gb:Manchester', ($n = $auto->nextSearch(Autopilot::settings()))['country'] . ':' . $n['city']);
    }

    public function test_the_command_runs_steps_and_reports_that_nothing_was_sent(): void
    {
        Mail::fake();
        $this->fakeUk();
        $this->enable();

        $this->artisan('creatium:autopilot --steps=2')->assertSuccessful()->expectsOutputToContain('Нищо не е изпратено');
        // Първата стъпка довършва търсенето, втората започва следващото от плана.
        $this->assertSame(['dentist', 'builder'], OutreachSearch::orderBy('id')->pluck('category')->all());
        Mail::assertNothingSent();
    }

    public function test_a_visit_pokes_the_autopilot_only_when_it_is_on(): void
    {
        $this->fakeUk();
        $this->get('/uslugi')->assertOk();
        $this->assertSame(0, OutreachSearch::count());

        $this->enable();
        $this->get('/uslugi')->assertOk();
        // defer() се изпълнява след отговора; в тестовете ги пускаме веднага.
        $this->assertSame(1, OutreachSearch::count());
    }

    public function test_settings_screen_saves_plan_and_limits_and_the_pause_switches_it_off(): void
    {
        $user = $this->admin();
        config(['creatium.outreach.autopilot.enabled' => true]);

        $this->actingAs($user)->get('/admin/outreach-autopilot')->assertOk()->assertSee('Автопилот')->assertSee('никога не праща писма')->assertSee('Manchester')->assertSee('Работи');
        $this->actingAs($user)->put('/admin/outreach-autopilot', [
            'daily_new' => 12, 'daily_searches' => 3, 'paused' => 1,
            'plan' => ['gb' => ['enabled' => 1, 'cities' => ['Leeds', 'Atlantis'], 'categories' => ['dentist', 'nonsense']], 'nl' => ['enabled' => 0, 'categories' => ['dentist']]],
        ])->assertRedirect('/admin/outreach-autopilot')->assertSessionHas('success');

        $settings = Autopilot::settings();
        $this->assertSame([12, 3, true], [$settings['daily_new'], $settings['daily_searches'], $settings['paused']]);
        $this->assertSame(['enabled' => true, 'cities' => ['Leeds'], 'categories' => ['dentist']], $settings['plan']['gb']);
        $this->assertFalse($settings['plan']['nl']['enabled']);
        $this->assertFalse(Autopilot::enabled(), 'paused');
        $this->actingAs($user)->get('/admin/outreach-autopilot')->assertSee('На пауза');

        $this->actingAs($user)->post('/admin/outreach-autopilot/run')->assertSessionHas('send_error');
        $this->actingAs($user)->put('/admin/outreach-autopilot', ['daily_new' => 'x', 'daily_searches' => 3])->assertSessionHasErrors('daily_new');
    }

    public function test_health_page_shows_the_autopilot(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->get('/admin/zdrave')->assertOk()->assertSee('Автопилот')->assertSee('OUTREACH_AUTOPILOT=true');

        config(['creatium.outreach.autopilot.enabled' => true]);
        OutreachSetting::put('autopilot_state', ['last_error' => 'Ключът за Serper не е валиден', 'last_tick_at' => now()->toIso8601String()]);
        $this->actingAs($user)->get('/admin/zdrave')->assertSee('Последна грешка Ключът за Serper не е валиден')->assertSee('Днес 0 нови бизнеса от 30');
    }

    // ---- За одобрение ----

    public function test_the_queue_shows_cards_by_score_with_the_channel_rule_and_a_bulgarian_summary(): void
    {
        $user = $this->admin();
        $low = $this->prospect(['company' => 'Low Dental', 'score' => 50, 'country' => 'bg', 'language' => 'bg', 'draft_subject' => 'Тема', 'draft_observation' => 'Проверихме сайта ви и видяхме няколко неща за подобрение.']);
        $high = $this->prospect(['company' => 'High Dental', 'score' => 90, 'score_reasons' => [['label' => 'Не е настроен за телефон (грешка)', 'points' => 18]]]);
        $nl = $this->prospect(['company' => 'Utrecht Tandarts', 'score' => 80, 'country' => 'nl', 'draft_subject' => null, 'draft_observation' => null, 'phone' => '030 123 4567']);
        $this->prospect(['company' => 'Done Firm', 'status' => 'sent']);
        $this->prospect(['company' => 'No Draft Firm', 'draft_at' => null]);

        $page = $this->actingAs($user)->get('/admin/outreach-queue')->assertOk();
        $page->assertSeeInOrder(['High Dental', 'Utrecht Tandarts', 'Low Dental'])->assertDontSee('Done Firm')->assertDontSee('No Draft Firm');
        $page->assertSee('3 бизнеса чакат одобрение')->assertSee('Одобри и изпрати')->assertSee('Запази черновата')->assertSee('Пропусни')
            ->assertSee('Findings from checking the Firm website');
        $page->assertSee('Писмо на английски')->assertSee('Писмото на английски представя Creatium Lab')->assertSee('Не е настроен за телефон (грешка)')
            ->assertSee('→ Обаждане')->assertSee('→ LinkedIn')->assertSee('Telecommunicatiewet')->assertSee('Ирландия')->assertSee('tel:+31301234567', false);
        $this->actingAs($user)->get('/admin/outreach-queue?country=nl')->assertSee('Utrecht Tandarts')->assertDontSee('High Dental');
    }

    public function test_approve_sends_exactly_one_mail_and_records_who_approved(): void
    {
        Mail::fake();
        $this->configureMail();
        $user = $this->admin();
        $one = $this->prospect(['first_name' => 'Sam']);
        $other = $this->prospect();

        $this->actingAs($user)->post("/admin/outreach-queue/{$one->id}/approve", [
            'subject' => 'Findings for Firm', 'first_name' => 'Sam', 'sender' => 'Владимир', 'observation' => "We checked the website.\n– The website is not set up for mobile phones. Most customers use a phone.",
        ])->assertRedirect('/admin/outreach-queue')->assertSessionHas('success');

        Mail::assertSentCount(1);
        Mail::assertSent(OutreachMail::class, fn (OutreachMail $m) => $m->hasTo($one->email) && $m->mailSubject === 'Findings for Firm' && $m->language() === 'en');
        $one->refresh();
        $this->assertSame(['sent', $user->id], [$one->status, $one->approved_by]);
        $this->assertNull($one->draft_at);
        $this->assertSame('outreach', ClientEmail::where('outreach_prospect_id', $one->id)->value('template'));
        $this->assertSame('new', $other->fresh()->status);

        // Втори път до същия не минава, а лимитът се пази.
        $this->actingAs($user)->post("/admin/outreach-queue/{$one->id}/approve", ['subject' => 'x', 'sender' => 'В', 'observation' => str_repeat('a', 30)])->assertSessionHas('send_error');
        Mail::assertSentCount(1);
    }

    public function test_approve_without_the_outreach_mailbox_sends_nothing(): void
    {
        Mail::fake();
        $user = $this->admin();
        $p = $this->prospect();

        $this->actingAs($user)->post("/admin/outreach-queue/{$p->id}/approve", ['subject' => 'x', 'sender' => 'В', 'observation' => str_repeat('a', 30)])
            ->assertSessionHas('send_error', fn ($m) => str_contains($m, 'OUTREACH_MAIL'));
        Mail::assertNothingSent();
    }

    public function test_save_draft_and_skip(): void
    {
        $user = $this->admin();
        $p = $this->prospect();

        $this->actingAs($user)->post("/admin/outreach-queue/{$p->id}/draft", ['subject' => 'New subject', 'observation' => 'A better letter that is long enough to pass.'])
            ->assertRedirect('/admin/outreach-queue#p' . $p->id)->assertSessionHas('success');
        $this->assertSame(['New subject', 'A better letter that is long enough to pass.'], [$p->fresh()->draft_subject, $p->fresh()->draft_observation]);
        $this->actingAs($user)->post("/admin/outreach-queue/{$p->id}/draft", ['subject' => '', 'observation' => 'short'])->assertSessionHasErrors(['subject', 'observation']);

        $this->actingAs($user)->post("/admin/outreach-queue/{$p->id}/skip")->assertRedirect('/admin/outreach-queue')->assertSessionHas('success');
        $this->assertSame('skipped', $p->fresh()->status);
        $this->actingAs($user)->get('/admin/outreach-queue')->assertDontSee('id="p' . $p->id . '"', false);
        $this->assertSame(0, OutreachProspect::awaitingApproval()->count());
    }

    public function test_bulk_approve_never_goes_over_the_remaining_daily_limit(): void
    {
        Mail::fake();
        $this->configureMail(2);
        $user = $this->admin();
        $a = $this->prospect(['score' => 60]);
        $b = $this->prospect(['score' => 90]);
        $c = $this->prospect(['score' => 75]);
        $nl = $this->prospect(['score' => 99, 'country' => 'nl', 'draft_subject' => null, 'draft_observation' => null]);

        $this->actingAs($user)->post('/admin/outreach-queue/approve-selected', ['ids' => [$a->id, $b->id, $c->id, $nl->id]])
            ->assertRedirect('/admin/outreach-queue')->assertSessionHas('success', 'Изпратени: 2.')->assertSessionHas('send_error', fn ($m) => str_contains($m, '1 остават за утре'));

        Mail::assertSentCount(2);
        // Най-добрите по оценка отиват първи.
        $this->assertSame(['new', 'sent', 'sent'], [$a->fresh()->status, $b->fresh()->status, $c->fresh()->status]);
        $this->assertSame('new', $nl->fresh()->status);
        $this->actingAs($user)->post('/admin/outreach-queue/approve-selected', [])->assertSessionHasErrors('ids');
    }

    public function test_a_prospect_without_email_goes_to_a_call_or_to_linkedin(): void
    {
        $user = $this->admin();
        $nl = $this->prospect(['country' => 'nl', 'company' => 'Tandarts Utrecht', 'phone' => '030 123 4567', 'city' => 'Utrecht', 'draft_subject' => null, 'draft_observation' => null]);

        $this->actingAs($user)->post("/admin/outreach-queue/{$nl->id}/route", ['to' => 'call'])->assertRedirect('/admin/outreach-queue')->assertSessionHas('success');
        $call = OutreachCall::sole();
        $this->assertSame(['prospect:' . $nl->id, 'Tandarts Utrecht', 'nl', 'en', '030 123 4567', 'Utrecht'], [$call->external_id, $call->name, $call->country, $call->language, $call->phone, $call->city]);
        $this->assertStringContainsString('Finding The website is not set up for mobile phones', $call->note);
        $this->assertSame('routed', $nl->fresh()->status);

        // Втори път не прави второ обаждане.
        $nl->forceFill(['status' => 'new', 'draft_at' => now()])->save();
        $this->actingAs($user)->post("/admin/outreach-queue/{$nl->id}/route", ['to' => 'call']);
        $this->assertSame(1, OutreachCall::count());

        $other = $this->prospect(['country' => 'gb', 'company' => 'Sole Smiles', 'website' => 'https://smiles.co.uk', 'draft_subject' => null, 'draft_observation' => null]);
        $response = $this->actingAs($user)->post("/admin/outreach-queue/{$other->id}/route", ['to' => 'linkedin']);
        $response->assertRedirectContains('/admin/linkedin?')->assertSessionHas('success');
        $this->actingAs($user)->get($response->headers->get('Location'))->assertOk()->assertSee('value="Sole Smiles"', false)->assertSee('value="https://smiles.co.uk"', false);
        $this->assertSame('routed', $other->fresh()->status);
    }

    public function test_drafts_are_prepared_only_for_prospects_who_can_be_emailed(): void
    {
        $nl = $this->prospect(['country' => 'nl', 'draft_at' => null, 'draft_subject' => null, 'draft_observation' => null]);
        $this->assertTrue(Drafts::prepare($nl));
        $this->assertNotNull($nl->fresh()->draft_at);
        $this->assertNull($nl->fresh()->draft_subject);

        $ie = $this->prospect(['draft_at' => null, 'draft_subject' => null, 'draft_observation' => null]);
        $this->assertTrue(Drafts::prepare($ie));
        $this->assertStringContainsString('The website is not set up for mobile phones', $ie->fresh()->draft_observation);

        $unknown = $this->prospect(['draft_at' => null, 'draft_subject' => null, 'draft_observation' => null, 'findings' => [['title' => 'Нещо ново', 'detail' => 'x']]]);
        $this->assertFalse(Drafts::prepare($unknown), 'nothing to write about in English');
        $this->assertNull($unknown->fresh()->draft_at);
        $this->assertNull(Drafts::summaryBg($this->prospect(['country' => 'bg', 'language' => 'bg'])));
    }

    public function test_the_queue_is_in_the_sales_menu_today_and_the_badge(): void
    {
        $user = $this->admin();
        $this->prospect(['company' => 'Menu Firm']);
        $this->prospect(['company' => 'Second Firm']);

        $menu = collect(Profiles::menu('sales'))->flatten(1)->pluck('route')->all();
        $this->assertContains('admin.outreach.queue', $menu);
        $this->assertGreaterThan(array_search('admin.outreach.index', $menu), array_search('admin.outreach.queue', $menu));
        $this->assertSame('sales-queue', Profiles::todaySections('sales')[''][0]);

        $today = $this->actingAs($user)->get('/admin/dnes?user=all')->assertOk();
        $today->assertSee('За одобрение')->assertSee('Menu Firm')->assertSee('нищо не се праща без вас');
        $this->assertSame(2, \App\Support\Admin\Badges::get('queue'));
        $this->actingAs($user)->get('/admin/outreach-queue')->assertSee('За одобрение');
    }

    public function test_guests_cannot_reach_the_queue_or_the_autopilot(): void
    {
        $p = $this->prospect();
        $this->get('/admin/outreach-queue')->assertRedirect('/login');
        $this->post("/admin/outreach-queue/{$p->id}/approve", [])->assertRedirect('/login');
        $this->post('/admin/outreach-queue/approve-selected', [])->assertRedirect('/login');
        $this->get('/admin/outreach-autopilot')->assertRedirect('/login');
        $this->put('/admin/outreach-autopilot', [])->assertRedirect('/login');
        $this->assertTrue(Markets::exists('gb'));
    }
}
