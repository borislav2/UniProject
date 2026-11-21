<?php

namespace Tests\Feature;

use App\Mail\ClientTemplateMail;
use App\Mail\NewLeadMail;
use App\Mail\SiteCheckReportMail;
use App\Models\Category;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\Badges;
use App\Support\ClientEmails;
use App\Support\SiteCheck\HostResolver;
use App\Support\SiteCheck\ReviewDue;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Безплатната проверка на сайт в дизайн Г (поръчка на собствениците, 18.11.2026): страницата, формата с цел, и двете обещания
 * (автоматичен отчет до минути и личен преглед до два работни дни), срокът, писмото „Личен преглед“ и „Днес“.
 */
class SiteCheckReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutDefer();
        $this->app->instance(HostResolver::class, new class extends HostResolver
        {
            public function publicIp(string $host): ?string
            {
                return '93.184.216.34';
            }
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function fakeSite(): void
    {
        $html = '<!doctype html><html lang="bg"><head><meta name="viewport" content="width=device-width"><title>Покриви</title></head><body><h1>Ремонт на покриви</h1><p>' . str_repeat('покрив ', 120) . '</p></body></html>';
        Http::fake([
            'https://firma.bg/robots.txt' => Http::response("User-agent: *\nDisallow:"),
            'https://firma.bg/' => Http::response($html),
            'http://firma.bg/' => Http::response('', 301, ['Location' => 'https://firma.bg/']),
            '*' => Http::response('', 404),
        ]);
    }

    private function team(string $profile = 'dev'): User
    {
        if (! Role::exists()) {
            $this->seed(RoleSeeder::class);
        }
        $user = User::factory()->create(['name' => 'Борислав Костадинов', 'profile' => $profile]);
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    /** Проверка на сайт със срок и записан отчет, както я оставя формата. */
    private function check(array $overrides = []): Project
    {
        if (! Category::exists()) {
            $this->seed(CategorySeeder::class);
        }

        $report = [
            'domain' => 'firma.bg', 'score' => 62, 'home_score' => 62, 'crawl' => null, 'issues' => [], 'sent' => true,
            'checks' => [
                ['key' => 'https', 'status' => 'fail', 'title' => 'Сайтът няма защитена връзка', 'detail' => 'Браузърите го показват като несигурен. Посетителите се отказват.'],
                ['key' => 'meta_description', 'status' => 'warn', 'title' => 'Липсва описание за Google', 'detail' => 'Без него Google избира случаен текст.'],
                ['key' => 'viewport', 'status' => 'ok', 'title' => 'Телефон', 'detail' => ''],
            ],
        ];

        $project = Project::create(array_merge([
            'name' => 'Проверка на сайт: firma.bg', 'description' => 'Проверка', 'start_date' => now(), 'status' => 'Planning', 'manager' => 'Неразпределен',
            'category_id' => Category::first()->id, 'client_email' => 'ivan@firma.bg', 'website_url' => 'https://firma.bg', 'source' => 'website',
            'service' => 'Проверка на сайт', 'review_due_at' => now()->addDay(), 'check_goal' => 'google',
        ], $overrides));
        $report['domain'] = parse_url($project->website_url, PHP_URL_HOST);
        $project->forceFill(['site_check_report' => $report])->save();

        return $project;
    }

    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    // ---- Страницата ----

    public function test_g_page_shows_the_brief_in_bulgarian(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/proverka-na-sait')->assertOk()->getContent();

        foreach ([
            'Разберете точно защо сайтът ви не носи очакваните клиенти и как да го промените',
            'без празни приказки',
            'Адрес на вашия уебсайт', 'Служебен имейл', 'Коя е основната ви цел в момента',
            'Повече запитвания', 'По-добро класиране в Google', 'По-бърз и модерен сайт',
            'Проверете сайта ми',
            'Скорост на зареждане', 'Мобилна използваемост', 'Техническо SEO', 'Видимост в AI търсачките',
            'Защо този преглед е различен от автоматичните онлайн инструменти',
            'Поглед през очите на вашия реален клиент', 'Диагностика на техническите спирачки', 'Тест за видимост в новите AI търсачки', 'Конкретен план с приоритети',
            'Мобилно преживяване', 'тест на палеца', 'Core Web Vitals', 'Google и Bing индексация', 'AI цитиране', 'ChatGPT и Gemini проверка',
            'Често задавани въпроси', 'Защо правите този анализ безплатно?', 'Колко време отнема изготвянето на анализа?',
            'до два работни дни',
        ] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        foreach (['name="domain"', 'name="email"', 'name="goal"', 'name="consent"', 'name="website"'] as $field) {
            $this->assertStringContainsString($field, $html, $field);
        }
        $this->assertSame(3, substr_count($html, 'name="goal"'));
        $this->assertSame(1, preg_match_all('#<h1[\s>]#', $html));
        $this->assertSame(1, preg_match_all('#<div id="site-check-form"#', $html));
        $this->assertStringContainsString('g-ph-dark', $html);
        $this->assertStringContainsString('class="g-sv g-sv-report"', $html);
        $this->assertStringContainsString('class="g-sv g-sv-points"', $html);

        $faq = collect($this->jsonLd($html))->firstWhere('@type', 'FAQPage');
        $this->assertNotNull($faq);
        $this->assertCount(2, $faq['mainEntity']);
        $this->assertStringContainsString('до два работни дни', $faq['mainEntity'][1]['acceptedAnswer']['text']);
        $this->assertStringContainsString('до няколко минути', $faq['mainEntity'][1]['acceptedAnswer']['text']);

        $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($html)));
    }

    public function test_g_page_works_in_english(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/en/website-check')->assertOk()->getContent();

        foreach ([
            'Find out exactly why your website is not bringing the customers you expect', 'Your website address', 'Business email', 'What is your main goal right now',
            'More enquiries', 'Better ranking in Google', 'A faster, more modern website', 'Check my website',
            'Loading speed', 'Mobile usability', 'Technical SEO', 'Visibility in AI search',
            'Why this review is different from automatic online tools', 'Mobile experience', 'AI citation', 'Frequently asked questions',
            'within two working days',
        ] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        $this->assertSame(1, preg_match_all('#<h1[\s>]#', $html));
        $this->assertSame(0, preg_match('/\p{Cyrillic}/u', PlainCopyTest::visibleText($html)) ? 1 : 0, 'English page has Cyrillic text');
        $faq = collect($this->jsonLd($html))->firstWhere('@type', 'FAQPage');
        $this->assertNotNull($faq);
        $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($html)));
    }

    public function test_current_design_page_is_unchanged(): void
    {
        config(['creatium.design' => 'current']);
        $html = $this->get('/proverka-na-sait')->assertOk()->getContent();

        $this->assertStringContainsString('Адрес на сайта', $html);
        $this->assertStringContainsString('Проверете сайта', $html);
        $this->assertStringNotContainsString('name="goal"', $html);
        $this->assertStringNotContainsString('g-sv-report', $html);
    }

    public function test_the_goal_is_checked_and_errors_are_announced(): void
    {
        config(['creatium.design' => 'g']);
        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1', 'goal' => 'nonsense'])->assertSessionHasErrors('goal');

        $html = $this->from('/proverka-na-sait')->followingRedirects()->post('/proverka-na-sait', ['domain' => '', 'email' => '', 'goal' => 'nonsense'])->getContent();
        $this->assertStringContainsString('aria-invalid="true" aria-describedby="err-domain"', $html);
        $this->assertStringContainsString('aria-describedby="err-email"', $html);
        $this->assertStringContainsString('aria-describedby="err-consent"', $html);
        $this->assertStringContainsString('data-g-invalid', $html);
        $this->assertStringContainsString('data-g-errors', $html);
        $this->assertStringContainsString('Моля, изберете една от целите в списъка.', $html);
    }

    // ---- Заявката и срокът ----

    public function test_a_request_creates_the_lead_with_goal_and_due_date_and_still_sends_the_automatic_report(): void
    {
        config(['creatium.design' => 'g', 'services.telegram.bot_token' => 'TEST', 'services.telegram.chat_id' => '42']);
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00', 'Europe/Sofia')); // петък
        Mail::fake();
        $this->fakeSite();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true]), 'https://firma.bg/robots.txt' => Http::response("User-agent: *\nDisallow:"), 'https://firma.bg/' => Http::response('<html><head><title>Покриви</title></head><body><h1>Покриви</h1></body></html>'), '*' => Http::response('', 404)]);

        $response = $this->post('/proverka-na-sait', ['domain' => 'https://firma.bg/', 'email' => 'Ivan@Firma.bg', 'consent' => '1', 'goal' => 'speed']);
        $response->assertRedirect('/proverka-na-sait')->assertSessionHas('site_check_sent', 'ivan@firma.bg')->assertSessionHas('lead_created', true);

        $lead = Project::siteChecks()->firstOrFail();
        $this->assertSame('speed', $lead->check_goal);
        $this->assertNull($lead->reviewed_at);
        $this->assertSame('2026-11-24', $lead->review_due_at->timezone('Europe/Sofia')->toDateString()); // вторник
        $this->assertSame('23:59', $lead->review_due_at->timezone('Europe/Sofia')->format('H:i'));

        // Автоматичният отчет е същият като преди и описанието пази срока и целта.
        Mail::assertSent(SiteCheckReportMail::class, fn ($mail) => $mail->hasTo('ivan@firma.bg'));
        $lead->refresh();
        $this->assertNotNull($lead->site_check_report['score'] ?? null);
        $this->assertStringContainsString('Личен преглед до 24.11.2026.', $lead->description);
        $this->assertStringContainsString('„По-бърз и модерен сайт“', $lead->description);

        // Екипът вижда срока в писмото и в Telegram.
        Mail::assertSent(NewLeadMail::class, function ($mail) {
            $html = $mail->render();
            $this->assertStringContainsString('Личен преглед до', $html);
            $this->assertStringContainsString('24.11.2026', $html);
            $this->assertStringContainsString('По-бърз и модерен сайт', $html);
            return true;
        });
        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'api.telegram.org') && str_contains($request['text'] ?? '', 'Личен преглед до') && str_contains($request['text'], '24.11.2026'));

        // Страницата след заявката казва и двете неща.
        $this->get('/proverka-na-sait')->assertOk()
            ->assertSee('Проверката започна')->assertSee('ivan@firma.bg')->assertSee('firma.bg')->assertSee('до два работни дни')->assertDontSee('name="goal"', false);
    }

    public function test_a_request_without_a_goal_still_works_and_the_limits_stay(): void
    {
        Mail::fake();
        $this->fakeSite();

        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertSessionHasNoErrors();
        $lead = Project::siteChecks()->firstOrFail();
        $this->assertNull($lead->check_goal);
        $this->assertNotNull($lead->review_due_at);

        foreach ([2, 3] as $n) {
            $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertSessionHasNoErrors();
        }
        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'ivan@firma.bg', 'consent' => '1'])->assertSessionHasErrors('email');
        $this->assertSame(3, Project::siteChecks()->count());
    }

    public function test_the_honeypot_creates_nothing(): void
    {
        Mail::fake();
        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'bot@firma.bg', 'consent' => '1', 'website' => 'spam', 'goal' => 'leads'])->assertRedirect();
        $this->assertSame(0, Project::count());
    }

    public function test_the_due_date_is_two_working_days_in_sofia_time(): void
    {
        $cases = [
            '2026-11-16 09:00' => '2026-11-18', // понеделник
            '2026-11-18 14:00' => '2026-11-20', // сряда
            '2026-11-19 14:00' => '2026-11-23', // четвъртък
            '2026-11-20 10:00' => '2026-11-24', // петък
            '2026-11-20 23:30' => '2026-11-24', // петък вечер още е петък
            '2026-11-21 00:30' => '2026-11-24', // събота
            '2026-11-22 12:00' => '2026-11-24', // неделя
        ];
        foreach ($cases as $sofia => $due) {
            $this->assertSame($due, ReviewDue::after(Carbon::parse($sofia, 'Europe/Sofia'))->timezone('Europe/Sofia')->toDateString(), $sofia);
        }
        // 00:30 в събота по софийско време е още петък по UTC: пак вторник.
        $this->assertSame('2026-11-24', ReviewDue::after(Carbon::parse('2026-11-20 22:30', 'UTC'))->timezone('Europe/Sofia')->toDateString());

        // Заявка в събота през формата.
        Carbon::setTestNow(Carbon::parse('2026-11-21 11:00', 'Europe/Sofia'));
        Mail::fake();
        $this->fakeSite();
        $this->post('/proverka-na-sait', ['domain' => 'firma.bg', 'email' => 'sat@firma.bg', 'consent' => '1', 'goal' => 'leads']);
        $this->assertSame('24.11.2026', ReviewDue::label(Project::siteChecks()->firstOrFail()->review_due_at));
    }

    public function test_the_automatic_email_mentions_the_personal_review_in_both_languages(): void
    {
        $report = ['domain' => 'firma.bg', 'score' => 70, 'reachable' => true, 'checks' => [], 'issues' => [], 'crawl' => null];
        $bg = (new SiteCheckReportMail($report))->render();
        $this->assertStringContainsString('личен преглед на сайта до два работни дни', $bg);
        $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($bg)));

        app()->setLocale('en');
        $en = (new SiteCheckReportMail($report))->render();
        $this->assertStringContainsString('personal review of your website within two working days', $en);
    }

    // ---- Админ ----

    public function test_admin_list_shows_due_and_overdue_reviews_with_the_button(): void
    {
        $admin = $this->team();
        $overdue = $this->check(['name' => 'Проверка на сайт: zakasnyalo.bg', 'website_url' => 'https://zakasnyalo.bg', 'review_due_at' => now()->subDays(2), 'check_goal' => 'leads']);
        $soon = $this->check(['name' => 'Проверка на сайт: skoro.bg', 'website_url' => 'https://skoro.bg', 'review_due_at' => now()->addDays(2)]);
        $done = $this->check(['name' => 'Проверка на сайт: gotovo.bg', 'website_url' => 'https://gotovo.bg', 'review_due_at' => now()->subDay(), 'reviewed_at' => now()]);

        $html = $this->actingAs($admin)->get('/admin/proverki')->assertOk()->getContent();

        $this->assertStringContainsString('Личен преглед до ' . ReviewDue::label($overdue->review_due_at) . ' · просрочен', $html);
        $this->assertStringContainsString('data-review="overdue"', $html);
        $this->assertStringContainsString('border-red-300 bg-red-50', $html);
        $this->assertStringContainsString('data-review="due"', $html);
        $this->assertStringContainsString('Личен преглед до ' . ReviewDue::label($soon->review_due_at), $html);
        $this->assertStringContainsString('data-review="done"', $html);
        $this->assertStringContainsString('Личният преглед е изпратен', $html);
        $this->assertStringContainsString('Изпрати личния преглед', $html);
        $this->assertStringContainsString(route('admin.client-emails.create', ['template' => 'site-review', 'project' => $overdue->id]), str_replace('&amp;', '&', $html));
        $this->assertStringContainsString('Повече запитвания', $html); // целта на клиента
        $this->assertStringContainsString('По-добро класиране в Google', $html);
        $this->assertSame(1, substr_count($html, 'data-review="overdue"'));

        // Същото е и на страницата на проверката.
        $this->actingAs($admin)->get(route('admin.projects.show', $overdue))->assertOk()->assertSee('просрочен')->assertSee('Изпрати личния преглед');
        $this->actingAs($admin)->get('/admin/proverki')->assertSee('Личен преглед до');
        // Запитване без срок (старо) не показва лента.
        $old = $this->check(['name' => 'Проверка на сайт: staro.bg', 'review_due_at' => null, 'check_goal' => null]);
        $this->actingAs($admin)->get(route('admin.projects.show', $old))->assertOk()->assertDontSee('data-review', false);
    }

    public function test_the_compose_screen_is_prefilled_from_the_saved_report(): void
    {
        $admin = $this->team();
        $lead = $this->check();

        $html = $this->actingAs($admin)->get(route('admin.client-emails.create', ['template' => 'site-review', 'project' => $lead->id]))->assertOk()->getContent();

        $this->assertStringContainsString('Личен преглед на сайт', $html);
        $this->assertStringContainsString('value="ivan@firma.bg"', $html);
        $this->assertStringContainsString('Личен преглед на сайта на {business}', $html);
        $this->assertStringContainsString('value="firma.bg"', $html);
        $this->assertStringContainsString('оценка 62 от 100', $html);
        $this->assertStringContainsString('„По-добро класиране в Google“', $html);
        $this->assertStringContainsString('Сайтът няма защитена връзка: Браузърите го показват като несигурен.', $html);
        $this->assertStringContainsString('Липсва описание за Google: Без него Google избира случаен текст.', $html);
        $this->assertStringNotContainsString('Телефон: ', strip_tags(explode('name="data[items]"', $html)[1] ?? ''));
        $this->assertStringContainsString('<iframe name="email-preview"', $html);

        // Живият преглед: така го вижда клиентът, с удебелени заглавия и без двоеточия.
        $data = ['client_name' => 'Иван', 'business' => 'firma.bg', 'sender' => 'Борислав', 'summary' => 'Накратко за {business}.',
            'items' => "Сайтът няма защитена връзка: Браузърите го показват като несигурен.\nЛипсва описание за Google: Без него Google избира случаен текст.", 'next' => 'Отговорете на този имейл.'];
        $preview = $this->actingAs($admin)->post(route('admin.client-emails.preview', 'site-review'), ['data' => $data])->assertOk()->getContent();
        $this->assertStringContainsString('<strong style="color:#0C0E12;">Сайтът няма защитена връзка</strong>', $preview);
        $this->assertStringContainsString('Накратко за firma.bg.', $preview);
        $this->assertStringContainsString('Какво да оправите първо', $preview);
        $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($preview)));
    }

    public function test_sending_the_review_marks_it_as_done(): void
    {
        $admin = $this->team();
        $lead = $this->check();
        Mail::fake();

        $payload = ['to' => 'ivan@firma.bg', 'subject' => 'Личен преглед на сайта на firma.bg | Creatium Lab', 'project_id' => $lead->id, 'data' => [
            'client_name' => '', 'business' => 'firma.bg', 'sender' => 'Борислав Костадинов', 'summary' => 'Ето най-важното.',
            'items' => "Защитена връзка: Сложете сертификат.\nОписание за Google: Добавете го.\nСкорост: Смалете снимките.", 'next' => '',
        ]];
        $this->actingAs($admin)->post(route('admin.client-emails.store', 'site-review'), $payload)->assertRedirect(route('admin.client-emails.index'))->assertSessionHas('success');

        Mail::assertSent(ClientTemplateMail::class, fn ($mail) => $mail->hasTo('ivan@firma.bg') && str_contains($mail->render(), 'Защитена връзка'));
        $lead->refresh();
        $this->assertNotNull($lead->reviewed_at);
        $this->assertSame('done', $lead->reviewState());
        $this->assertTrue($lead->notes()->where('body', 'like', '%личен преглед%')->exists());
        $this->assertDatabaseHas('client_emails', ['project_id' => $lead->id, 'template' => 'site-review']);

        $this->actingAs($admin)->get('/admin/proverki')->assertSee('data-review="done"', false);
    }

    public function test_a_failed_send_does_not_mark_the_review(): void
    {
        $admin = $this->team();
        $lead = $this->check();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->actingAs($admin)->post(route('admin.client-emails.store', 'site-review'), ['to' => 'ivan@firma.bg', 'subject' => 'Тема', 'project_id' => $lead->id, 'data' => [
            'business' => 'firma.bg', 'sender' => 'Екипът', 'summary' => 'Накратко.', 'items' => 'Точка едно',
        ]])->assertSessionHas('send_error');

        $this->assertNull($lead->fresh()->reviewed_at);
    }

    public function test_today_lists_reviews_due_today_or_overdue_for_the_dev_profile_and_the_badge_counts_them(): void
    {
        $admin = $this->team('dev');
        $overdue = $this->check(['name' => 'Проверка на сайт: zakasnyalo.bg', 'website_url' => 'https://zakasnyalo.bg', 'review_due_at' => now()->subDay(), 'read_at' => now()]);
        $today = $this->check(['name' => 'Проверка на сайт: dnes.bg', 'website_url' => 'https://dnes.bg', 'review_due_at' => now('Europe/Sofia')->endOfDay()->utc(), 'read_at' => now()]);
        $later = $this->check(['name' => 'Проверка на сайт: kasno.bg', 'website_url' => 'https://kasno.bg', 'review_due_at' => now()->addDays(4), 'read_at' => now()]);
        $done = $this->check(['name' => 'Проверка на сайт: gotovo.bg', 'website_url' => 'https://gotovo.bg', 'review_due_at' => now()->subDay(), 'reviewed_at' => now(), 'read_at' => now()]);

        $html = $this->actingAs($admin)->get('/admin/dnes')->assertOk()->getContent();
        $this->assertStringContainsString('Лични прегледи за изпращане', $html);
        $this->assertStringContainsString('zakasnyalo.bg', $html);
        $this->assertStringContainsString('dnes.bg', $html);
        $this->assertStringNotContainsString('kasno.bg', $html);
        $this->assertStringNotContainsString('gotovo.bg', $html);
        $this->assertStringContainsString('Просрочен', $html);
        $this->assertStringContainsString('Лични прегледи', $html); // числото най-горе
        $this->assertStringContainsString('Изпрати личния преглед', $html);

        // Профилът за продажби не получава този блок.
        $this->actingAs($this->team('sales'))->get('/admin/dnes')->assertOk()->assertDontSee('Лични прегледи за изпращане');

        request()->attributes->remove('admin.badges');
        $this->assertSame(2, Badges::get('site_checks'));
    }
}
