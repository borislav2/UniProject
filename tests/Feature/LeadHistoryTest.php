<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClientEmail;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\Phone;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CategorySeeder::class);
        $user = User::factory()->create(['name' => 'Борислав']);
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    private function lead(): Project
    {
        return Project::create([
            'name' => 'Запитване от Мария', 'description' => 'Искам сайт', 'start_date' => now()->toDateString(),
            'status' => 'Planning', 'manager' => 'Сайт', 'category_id' => Category::first()->id,
            'source' => 'website', 'client_phone' => '0888 12 34 56', 'lead_channel' => 'Google',
        ]);
    }

    public function test_phone_links_use_the_international_format(): void
    {
        $this->assertSame('359888123456', Phone::international('0888 12 34 56'));
        $this->assertSame('359888123456', Phone::international('+359 888 123 456'));
        $this->assertSame('359888123456', Phone::international('00359888123456'));
        $this->assertSame('viber://chat?number=%2B359888123456', Phone::viber('088 812 3456'));
        $this->assertSame('https://wa.me/359888123456', Phone::whatsapp('0888123456'));
        $this->assertNull(Phone::viber(''));
    }

    public function test_pipeline_note_and_stage_change_go_into_the_history(): void
    {
        $admin = $this->admin();
        $lead = $this->lead();
        ClientEmail::create(['project_id' => $lead->id, 'template' => 'inquiry-reply', 'to_email' => 'maria@abv.bg', 'subject' => 'Отговор на запитването', 'data' => [], 'sent_at' => now()]);

        $this->actingAs($admin)->patch("/admin/projects/{$lead->id}/pipeline", [
            'stage' => 'offer', 'next_action' => 'Обади се за офертата', 'next_action_at' => '2026-11-03T10:00', 'note' => 'Иска онлайн резервации, бюджет до 1500 €',
        ])->assertRedirect();
        $this->actingAs($admin)->post("/admin/projects/{$lead->id}/notes", ['kind' => 'note', 'body' => 'Прати ни снимки на ресторанта'])->assertRedirect();
        $this->actingAs($admin)->post("/admin/projects/{$lead->id}/notes", ['kind' => 'note', 'body' => ''])->assertSessionHasErrors('body');

        $this->assertSame(3, $lead->notes()->count());
        $this->actingAs($admin)->get("/admin/projects/{$lead->id}")->assertOk()
            ->assertSee('История')
            ->assertSee('Иска онлайн резервации, бюджет до 1500 €')
            ->assertSee('Етап: Оферта изпратена. Следваща стъпка: Обади се за офертата на 03.11.2026 10:00')
            ->assertSee('Прати ни снимки на ресторанта')
            ->assertSee('Изпратен: Отговор на запитването')
            ->assertSee('Запитване от сайта (Google)')
            ->assertSee('Борислав')
            ->assertSee('viber://chat?number=%2B359888123456', false)
            ->assertSee('https://wa.me/359888123456', false);

        // Само бележка, без смяна на етапа: един нов запис.
        $this->actingAs($admin)->patch("/admin/projects/{$lead->id}/pipeline", [
            'stage' => 'offer', 'next_action' => 'Обади се за офертата', 'next_action_at' => '2026-11-03T10:00', 'note' => 'Не вдигна',
        ]);
        $this->assertSame(4, $lead->notes()->count());
    }

    public function test_a_prospect_who_clicked_becomes_a_lead_once(): void
    {
        $admin = $this->admin();
        $prospect = OutreachProspect::create([
            'email' => 'info@lozata.bg', 'phone' => '0887 111 222', 'company' => 'Ресторант Лозата', 'website' => 'https://lozata.bg',
            'status' => 'sent', 'sent_at' => now()->subDay(), 'clicked_at' => now(), 'last_clicked_at' => now(), 'click_count' => 1, 'last_click_link' => 'check',
            'findings' => [['title' => 'Няма HTTPS', 'detail' => '']],
        ]);

        $this->actingAs($admin)->get('/admin/outreach')->assertSee('→ Запитване')->assertSee('0887 111 222');
        $response = $this->actingAs($admin)->post("/admin/outreach/{$prospect->id}/lead");
        $lead = Project::where('source', 'outreach')->sole();
        $response->assertRedirect("/admin/projects/{$lead->id}");

        $this->assertSame(['Запитване от Ресторант Лозата', 'contacted', 'info@lozata.bg', '0887 111 222', 'https://lozata.bg', 'Писма до бизнеси'],
            [$lead->name, $lead->stage, $lead->client_email, $lead->client_phone, $lead->website_url, $lead->lead_channel]);
        $this->assertStringContainsString('Няма HTTPS', $lead->description);
        $this->assertSame($lead->id, $prospect->fresh()->project_id);

        $this->actingAs($admin)->post("/admin/outreach/{$prospect->id}/lead")->assertRedirect("/admin/projects/{$lead->id}");
        $this->assertSame(1, Project::where('source', 'outreach')->count());
        $this->actingAs($admin)->get("/admin/projects/{$lead->id}")->assertSee('Първо писмо до info@lozata.bg')->assertSee('Кликна: Безплатна проверка');
        $this->actingAs($admin)->get('/admin/dnes')->assertSee('Без следваща стъпка')->assertSee('Ресторант Лозата');
    }

    public function test_a_call_with_interest_becomes_a_lead(): void
    {
        $admin = $this->admin();
        $call = OutreachCall::create(['external_id' => 'osm:1', 'name' => 'Автосервиз Иванов', 'phone' => '0888999000', 'industry' => 'car_repair', 'city' => 'Пловдив', 'note' => 'Да звъннем след 15-ти']);

        $this->actingAs($admin)->post("/admin/outreach-calls/{$call->id}/lead")->assertRedirect();
        $lead = Project::where('source', 'outreach')->sole();

        $this->assertSame(['Запитване от Автосервиз Иванов', '0888999000', 'Обаждания', 'contacted'], [$lead->name, $lead->client_phone, $lead->lead_channel, $lead->stage]);
        $this->assertStringContainsString('Да звъннем след 15-ти', $lead->description);
        $this->assertSame(['interested', $lead->id], [$call->fresh()->status, $call->fresh()->project_id]);
    }
}
