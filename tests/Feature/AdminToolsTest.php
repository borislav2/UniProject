<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $name = 'Борислав'): User
    {
        if (! Role::exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CategorySeeder::class);
        }
        $user = User::factory()->create(['name' => $name]);
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    private function lead(array $values = []): Project
    {
        return Project::create($values + [
            'name' => 'Запитване от Мария', 'description' => 'Искам сайт', 'start_date' => now()->toDateString(),
            'status' => 'Planning', 'manager' => 'Сайт', 'category_id' => Category::first()->id, 'source' => 'website',
        ]);
    }

    public function test_search_finds_everything_by_name_and_by_phone_digits(): void
    {
        $admin = $this->admin();
        $this->lead(['client_phone' => '0888 12 34 56', 'client_email' => 'maria@abv.bg']);
        OutreachProspect::create(['email' => 'info@lozata.bg', 'company' => 'Ресторант Лозата', 'phone' => '+359 32 123 456', 'status' => 'new']);
        OutreachCall::create(['external_id' => 'c1', 'name' => 'Автосервиз Лозата', 'phone' => '032/123-456']);
        $chat = ChatConversation::create(['locale' => 'bg', 'page' => '/paketi', 'ip_hash' => 'x', 'user_messages' => 1]);
        ChatMessage::create(['chat_conversation_id' => $chat->id, 'role' => 'user', 'text' => 'Имате ли пакет за Лозата?', 'source' => 'user']);

        $this->actingAs($admin)->get('/admin/tarsene?q=0888123456')->assertOk()->assertSee('Запитване от Мария')->assertDontSee('Ресторант Лозата');
        $this->actingAs($admin)->get('/admin/tarsene?q=32123456')->assertOk()->assertSee('Ресторант Лозата')->assertSee('Автосервиз Лозата');
        $this->actingAs($admin)->get('/admin/tarsene?q=Лозата')->assertOk()
            ->assertSeeInOrder(['Потенциални клиенти', 'Ресторант Лозата', 'Обаждания', 'Автосервиз Лозата', 'Разговори с Крео']);
        $this->actingAs($admin)->get('/admin/tarsene?q=maria@abv')->assertSee('Запитване от Мария');
        $this->actingAs($admin)->get('/admin/tarsene?q=няма-такова')->assertSee('Няма нищо за „няма-такова“');
    }

    public function test_layout_has_search_bottom_menu_and_app_manifest(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/dnes')->assertOk()
            ->assertSee('data-global-search', false)->assertSee('aria-label="Бързо меню"', false)
            ->assertSee('admin.webmanifest', false);
        $manifest = json_decode(file_get_contents(public_path('admin.webmanifest')), true);
        $this->assertSame('/admin/dnes', $manifest['start_url']);
        $this->assertFileExists(public_path('images/icon-192.png'));
    }

    public function test_leads_can_be_assigned_and_filtered_as_mine(): void
    {
        $me = $this->admin('Борислав');
        $partner = $this->admin('Владимир');
        $mine = $this->lead(['name' => 'Запитване от Моят клиент']);
        $theirs = $this->lead(['name' => 'Запитване от Неговият клиент']);
        $this->lead(['name' => 'Запитване от Ничий клиент']);

        $this->actingAs($me)->patch("/admin/projects/{$mine->id}/pipeline", ['stage' => 'contacted', 'assigned_to' => $me->id]);
        $this->actingAs($me)->patch("/admin/projects/{$theirs->id}/pipeline", ['stage' => 'contacted', 'assigned_to' => $partner->id]);
        $this->assertSame([$me->id, $partner->id], [$mine->fresh()->assigned_to, $theirs->fresh()->assigned_to]);

        $this->actingAs($me)->get('/admin/dnes?mine=1')->assertSee('Моят клиент')->assertSee('Ничий клиент')->assertDontSee('Неговият клиент');
        $this->actingAs($me)->get('/admin/projects?filter=mine')->assertSee('Моят клиент')->assertDontSee('Неговият клиент')->assertDontSee('Ничий клиент');
        $this->actingAs($me)->get('/admin/projects')->assertSee('Владимир');
    }

    public function test_next_call_screen_shows_keyboard_shortcuts(): void
    {
        $admin = $this->admin();
        OutreachCall::create(['external_id' => 'c1', 'name' => 'Фирма', 'phone' => '0888111222']);

        $this->actingAs($admin)->followingRedirects()->get('/admin/outreach-calls/next')->assertOk()
            ->assertSee('data-call-form', false)->assertSee('data-skip-form', false)->assertSee('резултат · ', false);
    }

    public function test_health_page_lists_what_is_configured(): void
    {
        $admin = $this->admin();
        config(['mail.default' => 'log', 'services.telegram.bot_token' => null, 'services.telegram.chat_id' => null, 'creatium.outreach.from_address' => null]);

        $this->actingAs($admin)->get('/admin/zdrave')->assertOk()
            ->assertSee('Имейл при ново запитване')->assertSee('bash deploy/configure-mail.sh')
            ->assertSee('php artisan creatium:telegram-connect')->assertSee('bash deploy/configure-outreach-mail.sh')
            ->assertSee('Свободно място')->assertSee('Резервно копие');

        config(['services.telegram.bot_token' => 'x', 'services.telegram.chat_id' => '1']);
        $this->actingAs($admin)->get('/admin/zdrave')->assertSee('Новите запитвания и кликовете идват в Telegram.');
    }
}
