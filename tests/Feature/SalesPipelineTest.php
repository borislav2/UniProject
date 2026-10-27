<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\Notify\Telegram;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SalesPipelineTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CategorySeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    private function lead(array $values = []): Project
    {
        return Project::create($values + [
            'name' => 'Запитване от Мария', 'description' => 'Искам сайт', 'start_date' => now()->toDateString(),
            'status' => 'Planning', 'manager' => 'Сайт', 'category_id' => Category::first()->id,
            'source' => 'website', 'client_phone' => '0888123456', 'service' => 'new-system',
        ]);
    }

    public function test_new_website_leads_start_as_new(): void
    {
        $this->admin();
        $lead = $this->lead();

        $this->assertSame('new', $lead->stage);
        $this->assertTrue($lead->isOpenLead());
        $this->assertNull(Project::create(['name' => 'Вътрешен', 'description' => 'd', 'start_date' => '2026-10-01', 'status' => 'Planning', 'manager' => 'm', 'category_id' => Category::first()->id])->stage);
    }

    public function test_stage_and_next_step_are_saved_in_sofia_time(): void
    {
        $admin = $this->admin();
        $lead = $this->lead();

        $this->actingAs($admin)->from('/admin/inquiries')->patch("/admin/projects/{$lead->id}/pipeline", [
            'stage' => 'offer', 'next_action' => 'Обади се за офертата', 'next_action_at' => '2026-11-03T10:00',
        ])->assertRedirect('/admin/inquiries');

        $lead->refresh();
        $this->assertSame(['offer', 'Обади се за офертата'], [$lead->stage, $lead->next_action]);
        $this->assertSame('2026-11-03 10:00', $lead->next_action_at->timezone('Europe/Sofia')->format('Y-m-d H:i'));
        $this->assertNotNull($lead->read_at);
        $this->assertNotNull($lead->stage_changed_at);
    }

    public function test_won_starts_work_and_lost_cancels_and_clears_the_next_step(): void
    {
        $admin = $this->admin();
        $won = $this->lead(['next_action' => 'x', 'next_action_at' => now()]);
        $lost = $this->lead(['name' => 'Запитване от Петър']);

        $this->actingAs($admin)->patch("/admin/projects/{$won->id}/pipeline", ['stage' => 'won', 'next_action' => 'x', 'next_action_at' => '2026-11-03T10:00']);
        $this->actingAs($admin)->patch("/admin/projects/{$lost->id}/pipeline", ['stage' => 'lost', 'lost_reason' => 'Избра по-евтино']);

        $won->refresh();
        $lost->refresh();
        $this->assertSame(['won', 'In Progress', null, null], [$won->stage, $won->status, $won->next_action, $won->next_action_at]);
        $this->assertSame(['lost', 'Cancelled', 'Избра по-евтино'], [$lost->stage, $lost->status, $lost->lost_reason]);

        $this->actingAs($admin)->patch("/admin/projects/{$won->id}/pipeline", ['stage' => 'nonsense'])->assertSessionHasErrors('stage');
    }

    public function test_today_lists_unread_due_and_forgotten_leads_and_hot_clicks(): void
    {
        $admin = $this->admin();
        $this->lead(['name' => 'Запитване от Непрочетен']);
        $this->lead(['name' => 'Запитване от Просрочен', 'read_at' => now(), 'stage' => 'offer', 'next_action' => 'Обади се за офертата', 'next_action_at' => now()->subDays(2)]);
        $this->lead(['name' => 'Запитване от Забравен', 'read_at' => now(), 'stage' => 'contacted']);
        $this->lead(['name' => 'Запитване от Бъдещ', 'read_at' => now(), 'stage' => 'contacted', 'next_action_at' => now()->addDays(5)]);
        $this->lead(['name' => 'Запитване от Затворен', 'read_at' => now(), 'stage' => 'won']);
        OutreachProspect::create(['email' => 'info@lozata.bg', 'company' => 'Лозата', 'status' => 'sent', 'sent_at' => now()->subDay(), 'clicked_at' => now(), 'last_clicked_at' => now(), 'click_count' => 1, 'last_click_link' => 'booking']);
        OutreachCall::create(['external_id' => 'c1', 'name' => 'Фирма', 'phone' => '0888111222', 'callback_on' => now()->addDay()]);
        OutreachCall::query()->update(['callback_on' => today()]);

        $this->actingAs($admin)->get('/admin/dnes')->assertOk()
            ->assertSeeInOrder(['Нови запитвания', 'Непрочетен', 'Следващи стъпки за днес', 'Просрочен', 'Обади се за офертата', 'Кликнаха по писмото', 'Лозата', 'Без следваща стъпка', 'Забравен'])
            ->assertSee('Запазване на час')
            ->assertSee('1 отново днес')
            ->assertDontSee('Бъдещ')->assertDontSee('Затворен');

        // Менюто брои непрочетените, просрочените и обажданията отново: 1 + 1 + 1.
        $this->actingAs($admin)->get('/admin/projects')->assertSeeInOrder(['Днес', '3']);
    }

    public function test_login_goes_to_today(): void
    {
        $admin = $this->admin();
        $admin->forceFill(['password' => bcrypt('correct-horse-battery')])->save();

        $this->post('/login', ['email' => $admin->email, 'password' => 'correct-horse-battery'])->assertRedirect('/admin/dnes');
    }

    public function test_telegram_gets_new_leads_and_first_clicks_when_configured(): void
    {
        config(['services.telegram.bot_token' => 'TEST', 'services.telegram.chat_id' => '42']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $this->admin();
        $lead = $this->lead(['client_email' => 'maria@abv.bg', 'consultation_at' => now('Europe/Sofia')->addDay()->setTime(11, 0)->utc()]);

        $this->assertTrue(Telegram::send('проба'));
        Telegram::newLead($lead, 'Ново запитване', defer: false);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/botTEST/sendMessage')
            && $r['chat_id'] === '42' && $r['parse_mode'] === 'HTML'
            && str_contains($r['text'], 'Ново запитване') && str_contains($r['text'], 'Мария')
            && str_contains($r['text'], '0888123456') && str_contains($r['text'], 'Консултация')
            && str_contains($r['text'], 'Изграждане на нова дигитална система')
            && str_contains($r['text'], "/admin/projects/{$lead->id}"));

        $prospect = OutreachProspect::create(['email' => 'info@lozata.bg', 'company' => 'Лозата', 'status' => 'sent', 'sent_at' => now()->subHour()]);
        $link = URL::signedRoute('outreach.click', ['prospect' => $prospect->id, 'link' => 'check']);
        $browser = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0 Safari/537.36';
        $this->withHeader('User-Agent', $browser)->get($link)->assertRedirect();
        $this->withHeader('User-Agent', $browser)->get($link)->assertRedirect();

        // Само първият клик (вторият е към същия линк, не е „Запазване на час“).
        Http::assertSentCount(3);
        Http::assertSent(fn (Request $r) => str_contains($r['text'] ?? '', 'Кликна по писмото') && str_contains($r['text'], 'Безплатна проверка'));
    }

    public function test_nothing_is_sent_without_telegram_settings(): void
    {
        config(['services.telegram.bot_token' => null, 'services.telegram.chat_id' => null]);
        Http::fake();
        $this->admin();

        $this->assertFalse(Telegram::send('проба'));
        Telegram::newLead($this->lead(), defer: false);
        Http::assertNothingSent();
    }
}
