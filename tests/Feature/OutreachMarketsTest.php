<?php

namespace Tests\Feature;

use App\Mail\OutreachFollowUpMail;
use App\Mail\OutreachMail;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\OutreachSearch;
use App\Models\Role;
use App\Models\User;
use App\Support\Outreach\CompanyEvidence;
use App\Support\Outreach\EmailVerifier;
use App\Support\Outreach\LeadScore;
use App\Support\Outreach\Markets;
use App\Support\Phone;
use App\Support\SiteCheck\HostResolver;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** Пазарите (България, Великобритания, Ирландия, Нидерландия): правила за канала, търсене, английското писмо, оценка и обаждания. */
class OutreachMarketsTest extends TestCase
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
    }

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['name' => 'Борислав']);
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        return $user;
    }

    private function configure(int $limit = 5): void
    {
        config(['creatium.outreach.from_address' => 'info@creativemoa.site', 'creatium.outreach.daily_limit' => $limit, 'mail.mailers.outreach.host' => 'smtp.example.test']);
    }

    private function prospect(string $country, array $values = []): OutreachProspect
    {
        static $n = 0;
        $n++;

        return OutreachProspect::create($values + [
            'email' => "info{$n}@firm{$n}.test", 'company' => "Firm {$n}", 'website' => "https://firm{$n}.test", 'email_status' => 'published', 'status' => 'new',
            'country' => $country, 'language' => Markets::language($country), 'city' => 'Manchester', 'industry' => 'dentist', 'findings' => [
                ['title' => 'Не е настроен за телефон', 'detail' => 'x', 'status' => 'fail'], ['title' => 'Няма описание за Google', 'detail' => 'y', 'status' => 'fail'],
            ], 'site_checked_at' => now(),
        ]);
    }

    private function body(): array
    {
        return ['subject' => 'Findings from checking the Firm website', 'first_name' => 'Sam', 'observation' => "We checked the website.\n– The website is not set up for mobile phones. Most customers use a phone.", 'sender' => 'Borislav'];
    }

    // ---- Правила по пазари ----

    public function test_the_market_rules_decide_who_can_be_emailed(): void
    {
        $bg = new OutreachProspect(['email_status' => 'published', 'status' => 'new']);
        $this->assertTrue(Markets::emailAllowed($bg), 'no country means Bulgaria, as before the markets existed');
        $this->assertTrue(Markets::emailAllowed($this->prospect('bg')));
        $this->assertTrue(Markets::emailAllowed($this->prospect('ie')));

        $sole = $this->prospect('gb');
        $this->assertFalse(Markets::emailAllowed($sole));
        $this->assertStringContainsString('Ltd, LLP, PLC', Markets::emailReason($sole));
        $this->assertTrue(Markets::emailAllowed($this->prospect('gb', ['company_evidence' => 'Acme Dental Ltd'])));

        $nl = $this->prospect('nl', ['company_evidence' => 'Acme BV']);
        $this->assertFalse(Markets::emailAllowed($nl));
        $this->assertStringContainsString('Telecommunicatiewet', Markets::emailReason($nl));
        $this->assertFalse(Markets::emailAllowed(new OutreachProspect(['country' => 'xx'])));
        $this->assertNull(Markets::emailReason($this->prospect('ie')));
    }

    public function test_company_evidence_is_found_only_for_real_registration_signs(): void
    {
        $this->assertSame('Smith Plumbing Ltd.', CompanyEvidence::find('<footer>© Smith Plumbing Ltd. All rights reserved</footer>'));
        $this->assertSame('Registered in England and Wales No. 12345678', CompanyEvidence::find('<p>Registered in England and Wales No. 12345678</p>'));
        $this->assertSame('Company No: 08123456', CompanyEvidence::find('<p>Company No: 08123456 VAT reg</p>'));
        $this->assertSame('Registered in Scotland SC123456', CompanyEvidence::find('<p>Registered in Scotland SC123456</p>'));
        $this->assertSame('Acme Services LLP', CompanyEvidence::find('<p>Acme Services LLP</p>'));
        $this->assertNull(CompanyEvidence::find('<p>Limited time offer on all haircuts</p>'));
        $this->assertNull(CompanyEvidence::find('<p>Registered in England and Wales</p>'));
        $this->assertNull(CompanyEvidence::find('<script>var a = "Foo Ltd";</script><p>Dr Jones Dental Practice</p>'));
    }

    public function test_every_send_path_enforces_the_market_rules(): void
    {
        Mail::fake();
        $this->configure();
        $user = $this->admin();
        $nl = $this->prospect('nl');
        $sole = $this->prospect('gb');
        $company = $this->prospect('gb', ['company_evidence' => 'Firm Ltd']);

        // Ръчно писмо, избраните, одобрение и напомняне: нищо не излиза за Нидерландия и за еднолично лице във Великобритания.
        $this->actingAs($user)->post("/admin/outreach/{$nl->id}/send", $this->body())->assertSessionHas('send_error');
        $this->actingAs($user)->post("/admin/outreach/{$sole->id}/send", $this->body())->assertSessionHas('send_error');
        $this->actingAs($user)->post('/admin/outreach/send-selected', ['ids' => [$nl->id, $sole->id]])->assertSessionHas('send_error');
        $nl->forceFill(['draft_at' => now(), 'draft_subject' => 'Hello there', 'draft_observation' => $this->body()['observation']])->save();
        $this->actingAs($user)->post("/admin/outreach-queue/{$nl->id}/approve", $this->body())->assertSessionHas('send_error');
        $this->actingAs($user)->post('/admin/outreach-queue/approve-selected', ['ids' => [$nl->id]])->assertSessionHas('success', 'Изпратени: 0.');
        $old = $this->prospect('nl', ['status' => 'sent', 'sent_at' => now()->subDays(10)]);
        $this->actingAs($user)->post("/admin/outreach/{$old->id}/follow-up")->assertSessionHas('send_error');
        Mail::assertNothingSent();
        $this->assertSame('new', $nl->fresh()->status);

        // Нужният текст за грешката е на български и казва какво да се направи.
        $this->actingAs($user)->post("/admin/outreach/{$sole->id}/send", $this->body())->assertSessionHas('send_error', fn ($m) => str_contains($m, 'Обадете се или пишете в LinkedIn'));

        // Фирма във Великобритания минава, с английско писмо.
        $this->actingAs($user)->post("/admin/outreach/{$company->id}/send", $this->body())->assertSessionHas('success');
        Mail::assertSent(OutreachMail::class, fn (OutreachMail $m) => $m->language() === 'en' && $m->hasTo($company->email));
        $this->assertSame($user->id, $company->fresh()->approved_by);
    }

    // ---- Търсене в други пазари ----

    private function fakeUk(): void
    {
        Http::fake([
            'google.serper.dev/places' => Http::response(['places' => [
                ['title' => 'Acme Dental', 'cid' => '901', 'website' => 'https://acmedental.co.uk', 'phoneNumber' => '0161 496 0000'],
                ['title' => 'Booking Listing', 'cid' => '902', 'website' => 'https://www.booking.com/hotel/x', 'phoneNumber' => '0161 496 0001'],
            ]]),
            'https://acmedental.co.uk/' => Http::response('<html><head><title>Acme Dental</title></head><body><p>Contact hello@acmedental.co.uk</p> <footer>Acme Dental Ltd. Company number 12345678</footer></body></html>'),
            'https://www.booking.com/*' => Http::response('<html><body>info@booking.com</body></html>'),
            '*' => Http::response('', 404),
        ]);
    }

    public function test_a_search_in_the_uk_uses_the_market_language_and_stores_the_evidence_and_score(): void
    {
        $this->fakeUk();
        config(['services.serper.key' => 'k']);
        $user = $this->admin();

        $this->actingAs($user)->get('/admin/outreach-search')->assertOk()->assertSee('Manchester')->assertSee('Обединено кралство')->assertSee('Utrecht');
        $this->actingAs($user)->post('/admin/outreach-search', ['country' => 'gb', 'city' => 'Пловдив', 'category' => 'dentist'])->assertSessionHasErrors('city');
        $this->actingAs($user)->post('/admin/outreach-search', ['country' => 'gb', 'city' => 'Manchester', 'category' => 'dentist'])->assertSessionHas('success');
        $search = OutreachSearch::sole();
        $this->assertSame(['gb', 'en'], [$search->country, $search->language]);
        $this->assertStringContainsString('(Обединено кралство)', $search->label());

        for ($i = 0; $i < 5; $i++) {
            $progress = $this->actingAs($user)->postJson("/admin/outreach-search/{$search->id}/work")->assertOk()->json();
            if ($progress['status'] === 'done') {
                break;
            }
        }

        Http::assertSent(fn ($r) => $r->url() === 'https://google.serper.dev/places' && $r['q'] === 'dentist Manchester' && $r['gl'] === 'gb' && $r['hl'] === 'en');
        $this->assertSame(1, $search->fresh()->serper_pages);
        $prospect = OutreachProspect::where('email', 'hello@acmedental.co.uk')->firstOrFail();
        $this->assertSame(['gb', 'en', $search->id], [$prospect->country, $prospect->language, $prospect->outreach_search_id]);
        $this->assertStringContainsString('Company number 12345678', $prospect->company_evidence);
        $this->assertTrue($prospect->canBeEmailed());
        $this->assertGreaterThan(0, $prospect->score);
        $this->assertNotEmpty($prospect->score_reasons);
        // Голяма платформа (Booking) е с наказание и не става подходящ клиент.
        $booking = OutreachProspect::where('email', 'info@booking.com')->first();
        $this->assertTrue($booking === null || $booking->score < $prospect->score);
    }

    public function test_dutch_search_uses_dutch_words_and_netherlands_prospects_are_never_emailable(): void
    {
        Http::fake([
            'google.serper.dev/places' => Http::response(['places' => [['title' => 'Tandarts Utrecht', 'cid' => '5', 'website' => 'https://tandarts.nl', 'phoneNumber' => '030 123 4567']]]),
            'https://tandarts.nl/' => Http::response('<html><head><title>Tandarts</title></head><body>info@tandarts.nl</body></html>'),
            '*' => Http::response('', 404),
        ]);
        config(['services.serper.key' => 'k']);
        $user = $this->admin();
        $this->actingAs($user)->post('/admin/outreach-search', ['country' => 'nl', 'city' => 'Utrecht', 'category' => 'dentist']);
        $search = OutreachSearch::sole();
        $this->actingAs($user)->postJson("/admin/outreach-search/{$search->id}/work")->assertOk();

        Http::assertSent(fn ($r) => $r->url() === 'https://google.serper.dev/places' && $r['q'] === 'tandarts Utrecht' && $r['gl'] === 'nl' && $r['hl'] === 'nl');
        $prospect = OutreachProspect::sole();
        $this->assertSame('nl', $prospect->country);
        $this->assertFalse($prospect->canBeEmailed());
        $this->assertSame('en', $prospect->mailLanguage());
    }

    public function test_the_osm_search_uses_the_market_city_centre(): void
    {
        Http::fake(['overpass-api.de/*' => Http::response(['elements' => []]), '*' => Http::response('', 404)]);
        $user = $this->admin();
        $this->actingAs($user)->post('/admin/outreach-search', ['country' => 'ie', 'city' => 'Cork', 'category' => 'dentist']);
        $this->actingAs($user)->postJson('/admin/outreach-search/' . OutreachSearch::sole()->id . '/work')->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), 'overpass-api.de') && str_contains((string) $r['data'], 'around:6000,51.8985,-8.4756'));
    }

    // ---- Английското писмо ----

    public function test_the_english_email_has_the_right_links_unsubscribe_and_plain_copy(): void
    {
        $prospect = $this->prospect('ie', ['email' => 'hello@firm.test', 'company' => 'Firm Dental', 'phone' => '01 234 5678']);
        $data = ['first_name' => 'Sam', 'sender' => 'Borislav', 'observation' => $prospect->suggestedObservation()];
        $mail = new OutreachMail($prospect, $data, $prospect->defaultSubject());

        $html = $mail->render();
        $this->assertStringContainsString('lang="en"', $html);
        $this->assertStringContainsString('Hello Sam,', $html);
        $this->assertStringContainsString('My name is Borislav and I am with Creatium Lab, a small digital agency.', $html);
        $this->assertStringContainsString('The website is not set up for mobile phones', $html);
        $this->assertStringContainsString('Book a call', $html);
        $this->assertStringContainsString('unsubscribe here', $html);
        $this->assertStringContainsString('found the business on Google Maps and on its public website', $html);
        foreach (['site', 'check', 'booking'] as $link) {
            $this->assertStringContainsString("/o/{$prospect->id}/{$link}?signature=", $html);
        }
        $this->assertStringNotContainsString('Здравейте', $html);

        $this->assertSame('Findings from checking the Firm Dental website', $mail->mailSubject);
        $headers = $mail->headers()->text;
        $this->assertStringContainsString('/otpisvane/' . $prospect->id, $headers['List-Unsubscribe']);
        $this->assertSame('List-Unsubscribe=One-Click', $headers['List-Unsubscribe-Post']);

        $text = view('emails.outreach-en-text', [
            'data' => $data, 'parts' => OutreachMail::observationParts($data['observation']), 'links' => $mail->links(), 'plain' => OutreachMail::targets('en'),
            'prospect' => $prospect, 'unsubscribeUrl' => $mail->unsubscribeUrl(),
        ])->render();
        $this->assertStringContainsString(route('en.site-check') . '?utm_source=outreach', $text);
        $this->assertStringContainsString(route('en.contact') . '?utm_source=outreach&utm_medium=email&utm_campaign=first-email#konsultatsiya', $text);
        $this->assertStringContainsString('privacy policy at ' . route('en.privacy'), $text);

        // Правилото на собствениците: без тирета и двоеточия в писмото (освен часове и адреси).
        $failures = [];
        foreach ([PlainCopyTest::visibleText($html), preg_replace('/\s+/u', ' ', $text)] as $t) {
            $failures = array_merge($failures, PlainCopyTest::offences($t));
        }
        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_the_english_follow_up_and_clicks_and_unsubscribe_page_are_english(): void
    {
        $prospect = $this->prospect('ie', ['status' => 'sent', 'sent_at' => now()->subDays(6), 'email' => 'hello@firm.test']);
        $data = ['first_name' => null, 'sender' => 'Creatium Lab', 'first_date' => '9 November 2026', 'finding' => 'The website is not set up for mobile phones'];
        $mail = new OutreachFollowUpMail($prospect, $data, 'Re: Hello');
        $html = $mail->render();
        $this->assertStringContainsString('This is a short follow up to our email of 9 November 2026', $html);
        $this->assertStringContainsString('Hello,', $html);
        $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($html)));

        $url = URL::signedRoute('outreach.click', ['prospect' => $prospect->id, 'link' => 'check']);
        $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0) Chrome/120')->get($url)
            ->assertRedirect(route('en.site-check') . '?utm_source=outreach&utm_medium=email&utm_campaign=first-email');

        $this->get(URL::signedRoute('outreach.unsubscribe', ['prospect' => $prospect->id]))->assertOk()->assertSee('You are unsubscribed')->assertDontSee('Отписахте се');
        $this->assertSame('unsubscribed', $prospect->fresh()->status);
    }

    public function test_the_english_observation_is_translated_and_bulgarian_is_unchanged(): void
    {
        $en = $this->prospect('gb', ['findings' => [
            ['title' => 'Сървърът отговаря много бавно (3.4 s)', 'detail' => 'x'], ['title' => 'Нещо ново', 'detail' => 'Първо. Второ.'], ['title' => 'Няма карта на сайта', 'detail' => 'z'],
        ]]);
        $text = $en->suggestedObservation();
        $this->assertStringStartsWith('While looking at local businesses in Manchester we checked the website of Firm', $text);
        $this->assertStringContainsString("\n– The website starts loading after 3.4 seconds. Many visitors do not wait", $text);
        $this->assertStringContainsString("\n– There is no sitemap. Google finds your pages more slowly", $text);
        $this->assertStringNotContainsString('Нещо ново', $text, 'a finding without an English text is left out');
        $this->assertSame([], PlainCopyTest::offences(str_replace('– ', '', $text)));

        $fine = $this->prospect('gb', ['findings' => []]);
        $this->assertStringContainsString('search Google for "dentist Manchester"', $fine->suggestedObservation());
        $this->assertSame('', $this->prospect('gb', ['findings' => null])->suggestedObservation());
        $this->assertSame('How ' . $fine->displayName() . ' appears on Google and Google Maps', $fine->defaultSubject());

        $bg = $this->prospect('bg', ['findings' => [['title' => 'Няма карта на сайта', 'detail' => 'z']]]);
        $this->assertStringContainsString('– Липсва карта на сайта (sitemap). Google открива страниците ви по-бавно', $bg->suggestedObservation());
        $this->assertSame('Резултати от проверка на сайта на ' . $bg->displayName(), $bg->defaultSubject());
    }

    // ---- Оценка ----

    public function test_the_score_rewards_need_contact_and_fit_and_excludes_the_contacted(): void
    {
        $needy = $this->prospect('gb', ['phone' => '0161 496 0000', 'company_evidence' => 'X Ltd']);
        $good = $this->prospect('gb', ['findings' => [], 'phone' => null, 'email_status' => 'risky', 'industry' => 'unknown']);
        $a = LeadScore::for($needy);
        $b = LeadScore::for($good);

        // 2 грешки (2 x 18) + имейл 16 + телефон 8 + стоматолог 12 + пазар 8 = 80.
        $this->assertSame(80, $a['score']);
        $this->assertGreaterThan($b['score'], $a['score']);
        $this->assertSame(6 + 4 + 8, $b['score'], 'a healthy site only scores for the address, the category and the market');
        $this->assertContains('Сайтът е в добро състояние, нуждата е по-малка', array_column($b['reasons'], 'label'));

        // Таванът за нуждата и разликата между грешка и предупреждение.
        $many = $this->prospect('gb', ['findings' => [['title' => 'A', 'detail' => '', 'status' => 'fail'], ['title' => 'B', 'detail' => '', 'status' => 'fail'], ['title' => 'C', 'detail' => '', 'status' => 'fail']]]);
        $this->assertSame(50 + 16 + 12 + 8, LeadScore::for($many)['score']);
        $warn = $this->prospect('gb', ['findings' => [['title' => 'A', 'detail' => '', 'status' => 'warn']]]);
        $this->assertSame(9 + 16 + 12 + 8, LeadScore::for($warn)['score']);
        // Стари записи без статус: известните грешки тежат като грешки.
        $old = $this->prospect('gb', ['findings' => [['title' => 'Няма заглавие за Google', 'detail' => '']]]);
        $this->assertSame(18 + 16 + 12 + 8, LeadScore::for($old)['score']);

        LeadScore::apply($needy);
        $this->assertSame(80, $needy->fresh()->score);
        $this->assertNotEmpty($needy->fresh()->score_reasons);

        // Пазарът е с тежест от настройките.
        config(['creatium.outreach.scoring.market.gb' => 0]);
        $this->assertSame(72, LeadScore::for($needy)['score']);

        // Голяма платформа, вече писано и отписано.
        $platform = $this->prospect('bg', ['website' => 'https://www.booking.com/hotel/x']);
        $this->assertLessThan(LeadScore::for($this->prospect('bg'))['score'], LeadScore::for($platform)['score']);
        $sent = $this->prospect('bg', ['status' => 'sent']);
        $this->assertSame(0, LeadScore::for($sent)['score']);
        $unsub = $this->prospect('bg', ['email' => 'gone@x.test']);
        \App\Models\OutreachSuppression::add('gone@x.test', 'unsubscribed');
        $this->assertSame(0, LeadScore::for($unsub)['score']);
    }

    public function test_the_list_shows_the_score_with_reasons_and_sorts_and_filters(): void
    {
        $user = $this->admin();
        $low = $this->prospect('bg', ['company' => 'Ниска Фирма', 'score' => 20, 'score_reasons' => [['label' => 'Имейл потвърден', 'points' => 20]]]);
        $high = $this->prospect('gb', ['company' => 'High Firm', 'score' => 88, 'score_reasons' => [['label' => 'Не е настроен за телефон (грешка)', 'points' => 18]]]);

        $this->actingAs($user)->get('/admin/outreach')->assertOk()->assertSeeInOrder(['High Firm', 'Ниска Фирма'])->assertSee('Не е настроен за телефон (грешка)')->assertSee('88');
        $this->actingAs($user)->get('/admin/outreach?country=gb')->assertSee('High Firm')->assertDontSee('Ниска Фирма');
        $this->actingAs($user)->get('/admin/outreach?sort=date')->assertOk();
        $this->actingAs($user)->get('/admin/outreach')->assertSee('Без имейл: обаждане или LinkedIn');
    }

    // ---- Обаждания в чужбина ----

    public function test_foreignness_depends_on_the_market_of_the_search(): void
    {
        $this->assertTrue(OutreachCall::isForeign('+40 762 942 283', 'Русе'));
        $this->assertFalse(OutreachCall::isForeign('+359 2 123 456', 'Велико Търново'));
        $this->assertFalse(OutreachCall::isForeign('+44 161 496 0000', 'Manchester', 'gb'));
        $this->assertFalse(OutreachCall::isForeign('0161 496 0000', 'Manchester', 'gb'));
        $this->assertTrue(OutreachCall::isForeign('+359 2 123 456', 'Manchester', 'gb'));
        $this->assertTrue(OutreachCall::isForeign('+44 161 496 0000', 'Sofia', 'bg'));
        $this->assertFalse(OutreachCall::isForeign('+353 1 234 5678', 'Dublin', 'ie'));
        $this->assertTrue(OutreachCall::isForeign('+353 1 234 5678', 'Utrecht', 'nl'));
        $this->assertFalse(OutreachCall::isForeign('030 123 4567', 'Utrecht', 'nl'), 'a Latin city name is only foreign for Bulgaria');
    }

    public function test_phone_links_keep_the_international_number_outside_bulgaria(): void
    {
        $this->assertSame('tel:+441614960000', Phone::tel('0161 496 0000', 'gb'));
        $this->assertSame('tel:+441614960000', Phone::tel('+44 161 496 0000', 'gb'));
        $this->assertSame('tel:+31301234567', Phone::tel('030 123 4567', 'nl'));
        $this->assertSame('tel:+353123456789', Phone::tel('00353 1 234 56789', 'ie'));
        $this->assertSame('viber://chat?number=%2B447911123456', Phone::viber('07911 123456', 'gb'));
        $this->assertSame('https://wa.me/353851234567', Phone::whatsapp('085 123 4567', 'ie'));
        $this->assertSame('tel:+359888123456', Phone::tel('+359 888 123 456', 'bg'));
        $this->assertSame('tel:0888123456', Phone::tel('0888 123 456'));
        $this->assertSame('359888123456', Phone::international('0888 12 34 56'));

        $this->assertTrue(OutreachCall::isMobileNumber('07911 123456', 'gb'));
        $this->assertTrue(OutreachCall::isMobileNumber('+44 7911 123456', 'gb'));
        $this->assertFalse(OutreachCall::isMobileNumber('0161 496 0000', 'gb'));
        $this->assertTrue(OutreachCall::isMobileNumber('06 12345678', 'nl'));
        $this->assertTrue(OutreachCall::isMobileNumber('087 123 4567', 'ie'));
        $this->assertTrue(OutreachCall::isMobileNumber('0888 123 456'));

        $html = view('components.phone', ['number' => '0161 496 0000', 'market' => 'gb', 'button' => false, 'attributes' => new \Illuminate\View\ComponentAttributeBag])->render();
        $this->assertStringContainsString('tel:+441614960000', $html);
        $this->assertStringContainsString('wa.me/441614960000', $html);
    }

    public function test_the_call_list_filters_by_country_and_shows_the_right_script(): void
    {
        $user = $this->admin();
        $bg = OutreachCall::create(['external_id' => 'a', 'name' => 'Зъболекар Русе', 'phone' => '0888 123 456', 'city' => 'Русе', 'industry' => 'dentist']);
        $gb = OutreachCall::create(['external_id' => 'b', 'name' => 'Acme Dental', 'phone' => '0161 496 0000', 'city' => 'Manchester', 'industry' => 'dentist', 'country' => 'gb', 'language' => 'en',
            'note' => "Сайт acme.test\nFinding The website is not set up for mobile phones"]);
        $this->assertTrue(OutreachCall::create(['external_id' => 'c', 'name' => 'Mobile', 'phone' => '07911 123456', 'country' => 'gb'])->is_mobile);

        $this->actingAs($user)->get('/admin/outreach-calls?country=gb')->assertOk()->assertSee('Acme Dental')->assertDontSee('Зъболекар Русе')->assertSee('tel:+441614960000', false);
        $this->actingAs($user)->get('/admin/outreach-calls?country=bg')->assertSee('Зъболекар Русе')->assertDontSee('Acme Dental');

        $this->actingAs($user)->get("/admin/outreach-calls/next?country=gb&call={$gb->id}")->assertOk()
            ->assertSee('Call script in English')->assertSee('I looked at your website and noticed one thing. The website is not set up for mobile phones.')
            ->assertSee('Which email address should I send it to?')->assertSee('tel:+441614960000', false)->assertSee('Обединено кралство')->assertDontSee('Скрипт за разговора');
        $this->actingAs($user)->get("/admin/outreach-calls/next?call={$bg->id}")->assertOk()->assertSee('Скрипт за разговора')->assertDontSee('Call script in English');
    }

    // ---- Страници на админа и публични ----

    public function test_privacy_pages_mention_the_new_markets_and_how_to_object(): void
    {
        $this->get('/poveritelnost')->assertOk()->assertSee('Великобритания, Ирландия и Нидерландия', false)->assertSee('в Нидерландия не пишем по имейл без предварително съгласие', false)
            ->assertSee('можете да възразите по всяко време', false);
        $this->get('/en/privacy')->assertOk()->assertSee('United Kingdom, Ireland and the Netherlands', false)->assertSee('you can object at any time', false)->assertSee('Autoriteit Persoonsgegevens', false);
    }

    public function test_stats_can_be_filtered_by_country(): void
    {
        $user = $this->admin();
        $this->prospect('gb', ['status' => 'sent', 'sent_at' => now()->subDay()]);
        $this->prospect('ie', ['status' => 'sent', 'sent_at' => now()->subDay(), 'clicked_at' => now()]);
        $this->prospect('ie', ['status' => 'sent', 'sent_at' => now()->subDay()]);

        $all = $this->actingAs($user)->get('/admin/statistika')->assertOk()->assertSee('Писма и обаждания по държава')->viewData('emails');
        $ie = $this->actingAs($user)->get('/admin/statistika?country=ie')->assertOk()->assertSee('Само Ирландия')->viewData('emails');
        $this->assertSame(3, $all[0][1]);
        $this->assertSame([2, 1], [$ie[0][1], $ie[1][1]]);
    }
}
