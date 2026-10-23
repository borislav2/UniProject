<?php

namespace Tests\Feature;

use App\Mail\OutreachMail;
use App\Models\OutreachBusiness;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\OutreachSearch;
use App\Models\Role;
use App\Models\User;
use App\Support\Outreach\EmailVerifier;
use App\Support\SiteCheck\HostResolver;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OutreachSearchTest extends TestCase
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
                return $domain !== 'nomail.bg';
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

    private function fakeWorld(): void
    {
        $k = 0x42;
        $cf = sprintf('%02x', $k) . implode('', array_map(fn ($c) => sprintf('%02x', ord($c) ^ $k), str_split('info@lozata.bg')));

        Http::fake([
            'overpass-api.de/*' => Http::response(['elements' => [
                ['type' => 'node', 'id' => 1, 'tags' => ['name' => 'Ресторант Лозата', 'amenity' => 'restaurant', 'website' => 'lozata.bg', 'phone' => '+359 32 111 222']],
                ['type' => 'node', 'id' => 2, 'tags' => ['name' => 'Механа Без Сайт', 'amenity' => 'restaurant', 'phone' => '+359 88 123 4567']],
                ['type' => 'node', 'id' => 3, 'tags' => ['name' => 'Restaurant Giurgiu', 'amenity' => 'restaurant', 'phone' => '+40 762 942 283']],
                ['type' => 'way', 'id' => 4, 'tags' => ['name' => 'Бистро Тихо', 'amenity' => 'restaurant', 'website' => 'https://tiho.bg/', 'phone' => '0888 222 333']],
                ['type' => 'node', 'id' => 5, 'tags' => ['amenity' => 'restaurant']],
            ]]),
            'https://lozata.bg/' => Http::response('<html><head><title>Лозата</title></head><body><a href="/kontakti">Контакти</a> Изработка: studio@webagency.bg</body></html>'),
            'https://lozata.bg/kontakti' => Http::response('<p>Пишете ни: <span data-cfemail="' . $cf . '"></span></p>'),
            'https://tiho.bg/' => Http::response('<html><body>Обадете се: 0888 222 333</body></html>'),
            '*' => Http::response('', 404),
        ]);
    }

    private function runToEnd(User $user, OutreachSearch $search): array
    {
        for ($i = 0; $i < 10; $i++) {
            $progress = $this->actingAs($user)->postJson("/admin/outreach-search/{$search->id}/work")->assertOk()->json();
            if (! in_array($progress['status'], ['queued', 'processing'], true)) {
                return $progress;
            }
        }
        $this->fail('Търсенето не приключи.');
    }

    public function test_a_search_finds_emails_on_sites_and_sends_the_rest_to_the_call_list(): void
    {
        $this->fakeWorld();
        $user = $this->admin();

        $this->actingAs($user)->get('/admin/outreach-search')->assertOk()->assertSee('Намери бизнеси')->assertSee('Велико Търново');
        $this->actingAs($user)->post('/admin/outreach-search', ['city' => 'Пловдив', 'category' => 'restaurant'])
            ->assertRedirect('/admin/outreach-search')->assertSessionHas('success');
        $search = OutreachSearch::firstOrFail();
        $this->actingAs($user)->get('/admin/outreach-search')->assertSee('data-search-work', false)->assertSee('Ресторанти, Пловдив');

        $progress = $this->runToEnd($user, $search);
        $this->assertSame(['done', 4, 4, 1, 2], [$progress['status'], $progress['found'], $progress['processed'], $progress['emails'], $progress['calls']]);

        $prospect = OutreachProspect::sole();
        $this->assertSame(['info@lozata.bg', 'published', 'restaurant', 'Пловдив'], [$prospect->email, $prospect->email_status, $prospect->industry, $prospect->city]);
        $this->assertTrue($prospect->canBeEmailed());
        $this->assertSame(['Няма описание за Google', 'Не е настроен за телефон', 'Адресът с http:// не води към https://'], array_column($prospect->findings, 'title'));
        $this->assertStringContainsString("\n– Сайтът не е пригоден за телефон. Повечето клиенти ви търсят от телефона", $prospect->suggestedObservation());
        $this->assertStringStartsWith('При преглед на бизнесите в Пловдив проверихме и сайта на Ресторант Лозата (lozata.bg)', $prospect->suggestedObservation());
        $this->assertCount(3, $prospect->findings);

        // Без имейл, но с български телефон: за обаждане. Румънският е пропуснат.
        $this->assertEqualsCanonicalizing(['Механа Без Сайт', 'Бистро Тихо'], OutreachCall::pluck('name')->all());

        // Повторно търсене не проверява наново вече намерените.
        $this->actingAs($user)->post('/admin/outreach-search', ['city' => 'Пловдив', 'category' => 'restaurant']);
        $again = $this->runToEnd($user, OutreachSearch::latest('id')->first());
        $this->assertSame([0, 0], [$again['emails'], $again['calls']]);
        $this->assertSame(4, OutreachBusiness::count());
    }

    public function test_the_first_email_is_written_from_the_site_check_and_sent_to_selected(): void
    {
        $this->fakeWorld();
        Mail::fake();
        config(['creatium.outreach.from_address' => 'info@creativemoa.site', 'mail.mailers.outreach.host' => 'smtp.example.test', 'creatium.outreach.daily_limit' => 5]);
        $user = $this->admin();
        $this->actingAs($user)->post('/admin/outreach-search', ['city' => 'Пловдив', 'category' => 'restaurant']);
        $this->runToEnd($user, OutreachSearch::firstOrFail());
        $prospect = OutreachProspect::sole();

        $this->actingAs($user)->get('/admin/outreach')->assertSee('name="ids[]"', false)->assertSee('Ресторант · Пловдив')->assertSee('публикуван от бизнеса');
        $this->actingAs($user)->get("/admin/outreach/{$prospect->id}")->assertSee('проверихме и сайта на Ресторант Лозата (lozata.bg)');

        $this->actingAs($user)->post('/admin/outreach/send-selected', ['ids' => [$prospect->id]])
            ->assertRedirect('/admin/outreach')->assertSessionHas('success', 'Изпратени: 1.');

        Mail::assertSent(OutreachMail::class, function (OutreachMail $mail) {
            $html = $mail->render();
            $this->assertStringContainsString('проверихме и сайта на Ресторант Лозата (lozata.bg)', $html);
            $this->assertStringContainsString('Липсва описание в резултатите на Google', $html);
            $this->assertStringContainsString('/proverka-na-sait?utm_source=outreach', $html);
            $this->assertStringContainsString('/kontakti?utm_source=outreach&amp;utm_medium=email&amp;utm_campaign=first-email#konsultatsiya', $html);
            $this->assertStringContainsString('Запазете час за разговор', $html);
            $this->assertStringContainsString('публикуван като контакт на Ресторант Лозата', $html);

            return $mail->hasTo('info@lozata.bg') && $mail->mailSubject === 'Резултати от проверка на сайта на Ресторант Лозата';
        });
        $this->assertSame('sent', $prospect->fresh()->status);

        // Второ изпращане до същия бизнес не минава.
        $this->actingAs($user)->post('/admin/outreach/send-selected', ['ids' => [$prospect->id]])->assertSessionHas('send_error');
        Mail::assertSentCount(1);
    }

    public function test_a_map_outage_is_explained_and_bad_input_is_refused(): void
    {
        Http::fake(['*' => Http::response('busy', 504)]);
        $user = $this->admin();

        $this->actingAs($user)->post('/admin/outreach-search', ['city' => 'Атлантида', 'category' => 'restaurant'])->assertSessionHasErrors('city');

        $this->actingAs($user)->post('/admin/outreach-search', ['city' => 'Русе', 'category' => 'hotel']);
        $progress = $this->runToEnd($user, OutreachSearch::firstOrFail());
        $this->assertSame('failed', $progress['status']);
        $this->assertStringContainsString('не отговаря', $progress['error']);
    }

    public function test_with_a_serper_key_the_search_uses_google_maps(): void
    {
        Http::fake([
            'google.serper.dev/places' => Http::response(['places' => [
                ['position' => 1, 'title' => 'Ресторант Лозата', 'address' => 'ул. Главна 1, Пловдив', 'category' => 'Ресторант', 'phoneNumber' => '032 111 222', 'website' => 'https://lozata.bg', 'cid' => '111'],
                ['position' => 2, 'title' => 'Механа Без Сайт', 'address' => 'Пловдив', 'category' => 'Ресторант', 'phoneNumber' => '088 123 4567', 'cid' => '222'],
            ]]),
        ]);
        $this->fakeWorld();
        config(['services.serper.key' => 'test-key']);
        $user = $this->admin();

        $this->actingAs($user)->get('/admin/outreach-search')->assertSee('Google Maps');
        $this->actingAs($user)->post('/admin/outreach-search', ['city' => 'Пловдив', 'category' => 'restaurant']);
        $progress = $this->runToEnd($user, OutreachSearch::firstOrFail());

        $this->assertSame(['done', 2, 1, 1], [$progress['status'], $progress['found'], $progress['emails'], $progress['calls']]);
        $this->assertSame('info@lozata.bg', OutreachProspect::sole()->email);
        $this->assertSame('g:111', OutreachBusiness::where('name', 'Ресторант Лозата')->value('external_id'));
        Http::assertSent(fn ($request) => $request->url() === 'https://google.serper.dev/places'
            && $request->hasHeader('X-API-KEY', 'test-key')
            && $request['q'] === 'ресторант Пловдив' && $request['gl'] === 'bg' && $request['page'] === 1);
    }

    public function test_reset_clears_everything_except_contacted_and_unsubscribed(): void
    {
        $user = $this->admin();
        OutreachSearch::create(['city' => 'Пловдив', 'category' => 'restaurant', 'status' => 'done']);
        OutreachBusiness::create(['external_id' => 'osm:node/1', 'name' => 'Х']);
        OutreachCall::create(['external_id' => 'osm:node/2', 'name' => 'Y', 'phone' => '0888']);
        OutreachProspect::create(['email' => 'new@a.bg', 'status' => 'new']);
        OutreachProspect::create(['email' => 'sent@a.bg', 'status' => 'sent', 'sent_at' => now()]);
        OutreachProspect::create(['email' => 'unsub@a.bg', 'status' => 'unsubscribed', 'sent_at' => now()]);
        \App\Models\OutreachSuppression::add('unsub@a.bg', 'unsubscribe');

        $this->actingAs($user)->get('/admin/outreach-search')->assertSee('Изчисти и започни отначало');
        $this->actingAs($user)->post('/admin/outreach-search/reset')
            ->assertRedirect('/admin/outreach-search')->assertSessionHas('success', 'Изчистено: 1 контакта без изпратено писмо и 1 от списъка за обаждане. Изпратените и отписаните са запазени.');

        $this->assertSame(['sent@a.bg', 'unsub@a.bg'], OutreachProspect::orderBy('email')->pluck('email')->all());
        $this->assertSame([0, 0, 0], [OutreachSearch::count(), OutreachBusiness::count(), OutreachCall::count()]);
        $this->assertTrue(\App\Models\OutreachSuppression::where('email', 'unsub@a.bg')->exists());
    }

    public function test_guests_cannot_search(): void
    {
        $this->get('/admin/outreach-search')->assertRedirect('/login');
        $this->post('/admin/outreach-search', ['city' => 'Русе', 'category' => 'hotel'])->assertRedirect('/login');
        $this->post('/admin/outreach-search/reset')->assertRedirect('/login');
    }

    public function test_the_first_email_explains_findings_and_handles_a_site_without_problems(): void
    {
        $parts = OutreachMail::observationParts("Увод.\n– Сайтът се зарежда бавно. Хората си тръгват. Още.\n- Без точка\nФинал.");
        $this->assertSame(['Увод.'], $parts['intro']);
        $this->assertSame([['title' => 'Сайтът се зарежда бавно', 'detail' => 'Хората си тръгват. Още.'], ['title' => 'Без точка', 'detail' => '']], $parts['items']);
        $this->assertSame(['Финал.'], $parts['outro']);
        $this->assertSame(['Само ръчен текст без списък.'], OutreachMail::observationParts('Само ръчен текст без списък.')['intro']);

        $slow = new OutreachProspect(['company' => 'Салон Роза', 'website' => 'https://www.roza.bg', 'city' => 'Варна', 'industry' => 'beauty',
            'findings' => [['title' => 'Сървърът отговаря много бавно (3.4 s)', 'detail' => 'x'], ['title' => 'Нещо ново', 'detail' => 'Първо. Второ.']]]);
        $this->assertStringContainsString('– Сайтът започва да се зарежда след 3,4 секунди. Голяма част', $slow->suggestedObservation());
        $this->assertStringContainsString('– Нещо ново. Първо.', $slow->suggestedObservation());

        $fine = new OutreachProspect(['company' => 'Салон Роза', 'website' => 'https://www.roza.bg', 'city' => 'Варна', 'industry' => 'beauty', 'findings' => []]);
        $this->assertStringContainsString('в добро състояние', $fine->suggestedObservation());
        $this->assertStringContainsString('потърсете в Google „козметичен салон Варна“', $fine->suggestedObservation());
        $this->assertSame('Видимост на Салон Роза в Google и Google Maps', \App\Http\Controllers\Admin\OutreachController::defaultSubject($fine));
    }
}
