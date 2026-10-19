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
        $this->assertStringContainsString("\n– Не е настроен за телефон. Липсва настройката viewport", $prospect->suggestedObservation());
        $this->assertStringStartsWith("Разгледахме сайта ви lozata.bg и забелязахме", $prospect->suggestedObservation());
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
        $this->actingAs($user)->get("/admin/outreach/{$prospect->id}")->assertSee('Разгледахме сайта ви lozata.bg');

        $this->actingAs($user)->post('/admin/outreach/send-selected', ['ids' => [$prospect->id]])
            ->assertRedirect('/admin/outreach')->assertSessionHas('success', 'Изпратени: 1.');

        Mail::assertSent(OutreachMail::class, function (OutreachMail $mail) {
            $html = $mail->render();
            $this->assertStringContainsString('Разгледахме сайта ви lozata.bg', $html);
            $this->assertStringContainsString('публикуван като контакт на Ресторант Лозата', $html);

            return $mail->hasTo('info@lozata.bg') && $mail->mailSubject === 'Въпрос за сайта на Ресторант Лозата';
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

    public function test_guests_cannot_search(): void
    {
        $this->get('/admin/outreach-search')->assertRedirect('/login');
        $this->post('/admin/outreach-search', ['city' => 'Русе', 'category' => 'hotel'])->assertRedirect('/login');
    }
}
