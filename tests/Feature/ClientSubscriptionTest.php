<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClientPayment;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\SiteMonitor;
use App\Support\SiteCheck\HostResolver;
use App\Support\SiteCheck\SiteChecker;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClientSubscriptionTest extends TestCase
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
        // Без истински SSL/RDAP заявки: датите идват от теста.
        $this->app->bind(SiteMonitor::class, fn ($app) => new class($app->make(SiteChecker::class), $app->make(HostResolver::class)) extends SiteMonitor
        {
            public static ?Carbon $ssl = null;

            protected function sslExpiry(string $host): ?Carbon
            {
                return self::$ssl;
            }

            protected function domainExpiry(string $host): ?Carbon
            {
                return null;
            }
        });
    }

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CategorySeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    private function client(array $values = []): Project
    {
        return Project::create($values + [
            'name' => 'Ресторант Лозата', 'description' => 'Сайт и поддръжка', 'start_date' => '2026-09-01', 'status' => 'Completed',
            'manager' => 'Екип', 'category_id' => Category::first()->id, 'package' => 'Стартов двигател', 'monthly_fee' => 150, 'billing_day' => 5,
            'website_url' => 'https://lozata.bg', 'client_phone' => '0888123456',
        ]);
    }

    public function test_payment_state_is_upcoming_paid_or_late(): void
    {
        $this->admin();
        $this->travelTo(Carbon::parse('2026-10-03 12:00', 'Europe/Sofia'));
        $client = $this->client();
        $client->forceFill(['created_at' => now()->subMonths(2)])->save();
        ClientPayment::create(['project_id' => $client->id, 'period' => '2026-09', 'amount' => 150, 'paid_on' => '2026-09-05']);

        $this->assertSame(['upcoming', '2026-10', 2], array_values(array_intersect_key($client->fresh()->paymentState(), array_flip(['state', 'period', 'days']))));

        $this->travelTo(Carbon::parse('2026-10-08 12:00', 'Europe/Sofia'));
        $this->assertSame(['late', 3], [$client->fresh()->paymentState()['state'], $client->fresh()->paymentState()['days']]);

        ClientPayment::create(['project_id' => $client->id, 'period' => '2026-10', 'amount' => 150, 'paid_on' => '2026-10-08']);
        $this->assertSame('paid', $client->fresh()->paymentState()['state']);

        // Пропуснат минал месец се брои като закъснение, дори текущият да е платен.
        $this->travelTo(Carbon::parse('2026-11-20 12:00', 'Europe/Sofia'));
        $this->assertSame(['late', '2026-11'], [$client->fresh()->paymentState()['state'], $client->fresh()->paymentState()['period']]);
        $this->travelTo(Carbon::parse('2026-12-02 12:00', 'Europe/Sofia'));
        $this->assertSame(['late', '2026-11'], [$client->fresh()->paymentState()['state'], $client->fresh()->paymentState()['period']]);
    }

    public function test_clients_page_shows_revenue_and_marks_a_payment(): void
    {
        $admin = $this->admin();
        $this->travelTo(Carbon::parse('2026-10-10 12:00', 'Europe/Sofia'));
        $client = $this->client();
        $this->client(['name' => 'Салон Лавандула', 'monthly_fee' => 400, 'billing_day' => 20, 'website_url' => null]);
        $this->client(['name' => 'Без абонамент', 'monthly_fee' => null]);

        $this->actingAs($admin)->get('/admin/abonamenti')->assertOk()
            ->assertSee('550 €')->assertSee('Ресторант Лозата')->assertSee('Салон Лавандула')->assertDontSee('Без абонамент')
            ->assertSee('Закъснява 5 дни')->assertSee('Плаща след 10 дни')->assertSee('Платено за октомври');
        $this->actingAs($admin)->get('/admin/projects')->assertSeeInOrder(['Абонаменти', '1']);

        $this->actingAs($admin)->post("/admin/abonamenti/{$client->id}/paid", ['period' => '2026-10'])->assertSessionHas('success');
        $this->assertSame('paid', $client->fresh()->paymentState()['state']);
        $this->assertSame('150.00', ClientPayment::sole()->amount);
        $this->assertStringContainsString('Платено за октомври 2026: 150,00 €', $client->notes()->value('body'));
    }

    public function test_monitor_marks_a_down_site_and_alerts_once_a_day(): void
    {
        config(['services.telegram.bot_token' => 'TEST', 'services.telegram.chat_id' => '42']);
        Http::fake([
            'https://lozata.bg*' => Http::response('boom', 500),
            'https://salon.bg*' => Http::response('<html>ok</html>', 200),
            'api.telegram.org/*' => Http::response(['ok' => true]),
        ]);
        $this->admin();
        $down = $this->client();
        $up = $this->client(['name' => 'Салон', 'website_url' => 'https://salon.bg']);
        SiteMonitor::watched()->get()->each(fn ($p) => app(SiteMonitor::class)->check($p));

        $this->assertSame(['down', 'Сайтът връща грешка 500'], [$down->fresh()->monitor_status, $down->fresh()->monitor_error]);
        $this->assertSame('up', $up->fresh()->monitor_status);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'sendMessage') && str_contains($r['text'], 'Ресторант Лозата') && str_contains($r['text'], 'грешка 500'));

        // Втора проверка в същия ден не праща ново известие.
        app(SiteMonitor::class)->check($down->fresh());
        Http::assertSentCount(4);
    }

    public function test_ssl_expiring_soon_is_a_problem_shown_on_today(): void
    {
        Http::fake(['https://lozata.bg*' => Http::response('ok', 200)]);
        $admin = $this->admin();
        $client = $this->client();
        $client->forceFill(['created_at' => now()->subMonths(3)])->save();
        $monitor = app(SiteMonitor::class);
        $monitor::$ssl = now()->addDays(5);
        $monitor->check($client);

        $this->assertStringContainsString('SSL сертификатът изтича', implode(' ', SiteMonitor::problems($client->fresh())));
        $this->actingAs($admin)->get('/admin/dnes')->assertSee('Клиенти на абонамент')->assertSee('SSL сертификатът изтича');
        $monitor::$ssl = null;
    }

    public function test_monthly_report_is_prefilled_and_renders(): void
    {
        $admin = $this->admin();
        $client = $this->client();
        $client->forceFill(['monitor_status' => 'up', 'monitor_checked_at' => now(), 'ssl_expires_at' => now()->addMonths(2)])->save();

        $this->actingAs($admin)->get("/admin/emails/monthly-report?project={$client->id}")->assertOk()
            ->assertSee('Месечен отчет')->assertSee('Сайтът работи без прекъсвания');

        $html = $this->actingAs($admin)->post('/admin/emails/monthly-report/preview', ['data' => [
            'client_name' => 'Мария', 'business' => 'Ресторант Лозата', 'sender' => 'Екипът на Creatium Lab', 'month' => 'октомври 2026',
            'done' => "– Обновихме менюто\n– Нов профил в Google", 'results' => '12 запитвания от сайта', 'site_status' => 'Сайтът работи', 'next' => 'Кампания за Коледа',
        ]])->assertOk()->getContent();

        foreach (['Ресторант Лозата: октомври 2026', 'Обновихме менюто', 'Нов профил в Google', '12 запитвания от сайта', 'Кампания за Коледа'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }
}
