<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OutreachCall;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Админът по хора (ноември 2026): запитвания и проверки отделно, етапи, непрочетени, „Днес“ на всеки, таблото. */
class AdminTeamTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $name): User
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

    public function test_inquiries_show_only_the_forms_and_site_checks_have_their_own_page_with_a_summary(): void
    {
        $admin = $this->admin('Борислав');
        $this->lead(['name' => 'Запитване от Формата', 'service' => 'new-system']);
        $this->lead(['name' => 'Запитване за оферта от Офертата', 'service' => 'Оферта: Стартов двигател']);
        $this->lead(['name' => 'Запитване от Чатов', 'service' => Project::CHAT_SERVICE]);
        $this->lead(['name' => 'Проверка на сайт: firma.bg', 'service' => Project::SITE_CHECK_SERVICE, 'website_url' => 'https://firma.bg'])->forceFill([
            'site_check_report' => ['domain' => 'firma.bg', 'score' => 42, 'sent' => true, 'checks' => [
                ['status' => 'fail', 'title' => 'Сайтът е скрит от Google', 'detail' => 'noindex'],
                ['status' => 'warn', 'title' => 'Няма описание', 'detail' => ''],
                ['status' => 'ok', 'title' => 'HTTPS', 'detail' => ''],
            ]]])->save();
        // Стара проверка без записан резултат: оценката идва от описанието.
        $this->lead(['name' => 'Проверка на сайт: stara.bg', 'service' => Project::SITE_CHECK_SERVICE,
            'description' => "Проверка на сайт stara.bg: оценка 70 от 100.\n\nЗа оправяне:\n- Няма sitemap\n\nОтчетът е изпратен на a@b.bg.", 'read_at' => now()]);

        $this->actingAs($admin)->get('/admin/inquiries')->assertOk()
            ->assertSee('Формата')->assertSee('Офертата')->assertSee('Оферта за пакет')
            ->assertDontSee('Чатов')->assertDontSee('firma.bg')
            ->assertSee('data-unread', false);

        $this->actingAs($admin)->get('/admin/proverki')->assertOk()
            ->assertSee('Обобщение на проверката')
            ->assertSeeInOrder(['firma.bg', 'Обобщение на проверката', 'Сайтът е скрит от Google', 'Няма описание'])->assertSee('<span class="text-xl font-extrabold leading-none">42</span>', false)
            ->assertSee('1 проблем, 1 за подобряване, 1 наред.')
            ->assertSee('Няма sitemap')->assertSee('70')
            ->assertDontSee('Формата');

        $this->actingAs($admin)->get('/admin')->assertSee('Проверки на сайт');
    }

    public function test_stage_tabs_and_unread_filter(): void
    {
        $admin = $this->admin('Борислав');
        $this->lead(['name' => 'Запитване от Новак']);
        $this->lead(['name' => 'Запитване от Свързан', 'stage' => 'contacted', 'read_at' => now()]);

        $this->actingAs($admin)->get('/admin/inquiries?tab=new')->assertSee('Новак')->assertDontSee('Свързан');
        $this->actingAs($admin)->get('/admin/inquiries?tab=contacted')->assertSee('Свързан')->assertDontSee('Новак');
        $this->actingAs($admin)->get('/admin/inquiries?filter=unread')->assertSee('Новак')->assertDontSee('Свързан');
    }

    public function test_taking_a_lead_makes_it_mine_and_today_shows_each_persons_day(): void
    {
        $boris = $this->admin('Борислав');
        $maria = $this->admin('Мария');
        $lead = $this->lead(['name' => 'Запитване от Петров']);

        $this->actingAs($maria)->post("/admin/inquiries/{$lead->id}/take")->assertRedirect();
        $lead->refresh();
        $this->assertSame($maria->id, $lead->assigned_to);
        $this->assertNotNull($lead->read_at);

        $this->actingAs($maria)->patch("/admin/projects/{$lead->id}/pipeline", ['stage' => 'contacted', 'note' => 'Говорихме, иска оферта.']);
        $call = OutreachCall::create(['external_id' => 'c1', 'name' => 'Автосервиз Иванов', 'phone' => '0888111222']);
        $this->actingAs($boris)->patch("/admin/outreach-calls/{$call->id}", ['status' => 'interested']);
        $this->assertSame($boris->id, $call->fresh()->called_by);
        $this->flushSession();

        // Денят на Мария: какво е направила; на Борислав: неговото обаждане.
        $this->actingAs($maria)->get('/admin/dnes')->assertOk()
            ->assertSee('Моят ден')->assertSee('Направено днес')
            ->assertSee('Говорихме, иска оферта.')->assertDontSee('Автосервиз Иванов');
        $this->actingAs($maria)->get('/admin/dnes?user=' . $boris->id)->assertOk()
            ->assertSee('Денят на Борислав')->assertSee('Автосервиз Иванов: Интерес')->assertDontSee('Говорихме, иска оферта.');
        $this->actingAs($maria)->get('/admin/dnes?user=all')->assertSee('Денят на екипа')->assertSee('Говорихме, иска оферта.')->assertSee('Автосервиз Иванов');

        // Таблото: екипът с обажданията тази седмица.
        $this->actingAs($boris)->get('/admin')->assertOk()
            ->assertSeeInOrder(['Екипът', 'Борислав', 'Мария'])
            ->assertSee('Продажбите този месец');
    }
}
