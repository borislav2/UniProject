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

class AdminUsabilityTest extends TestCase
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

    private function project(array $values = []): Project
    {
        return Project::create($values + [
            'name' => 'Проект', 'description' => 'Описание', 'start_date' => '2026-10-01',
            'status' => 'Planning', 'manager' => 'Екип', 'category_id' => Category::first()->id,
        ]);
    }

    public function test_dashboard_shows_what_needs_doing_today(): void
    {
        $admin = $this->admin();
        $this->project(['name' => 'Запитване: Мария', 'source' => 'website', 'service' => 'new-system', 'client_phone' => '0888123456']);
        $this->project(['name' => 'Запитване: Петър', 'source' => 'website', 'read_at' => now(), 'consultation_at' => now('Europe/Sofia')->addDay()->setTime(11, 0)->utc()]);
        OutreachCall::create(['external_id' => 'x1', 'name' => 'Фирма', 'phone' => '0888111222', 'status' => 'interested', 'called_at' => now()]);

        $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('Табло')
            ->assertSee('Непрочетени запитвания')
            ->assertSee('Запитване: Мария')
            ->assertSee('Изграждане на нова дигитална система')
            ->assertSee('tel:0888123456', false)
            ->assertSee('Утре, 11:00')
            ->assertSee('Обаждания днес')
            ->assertDontSee('Total Projects')
            ->assertDontSee('Back to Home');
    }

    public function test_projects_list_filters_and_shows_statuses_in_bulgarian(): void
    {
        $admin = $this->admin();
        $this->project(['name' => 'Ново запитване от сайта', 'source' => 'website']);
        $this->project(['name' => 'Сайт в работа', 'status' => 'In Progress']);
        $this->project(['name' => 'Готов сайт', 'status' => 'Completed']);

        $this->actingAs($admin)->get('/admin/projects')->assertOk()
            ->assertSee('Нови запитвания (1)')->assertSee('В работа (1)')->assertSee('Завършени (1)')
            ->assertSee('Завършен')->assertDontSee('>Completed<', false);

        $this->actingAs($admin)->get('/admin/projects?filter=active')->assertOk()
            ->assertSee('Сайт в работа')->assertDontSee('Готов сайт')->assertDontSee('Ново запитване от сайта');
    }

    public function test_project_search_finds_the_client_phone(): void
    {
        $admin = $this->admin();
        $this->project(['name' => 'Ресторант Лозата', 'client_phone' => '0899555444']);

        $this->actingAs($admin)->get('/admin/projects/search?search=0899555444')->assertOk()->assertSee('Ресторант Лозата');
    }

    public function test_project_page_shows_client_contact_and_edit_saves_it(): void
    {
        $admin = $this->admin();
        $project = $this->project(['name' => 'Салон Лавандула', 'client_phone' => '0888 12 34 56', 'client_email' => 'salon@abv.bg', 'status' => 'Completed']);

        $this->actingAs($admin)->get("/admin/projects/{$project->id}")->assertOk()
            ->assertSee('tel:0888123456', false)->assertSee('salon@abv.bg')->assertSee('Завършен')->assertSee('Редактирай');

        $this->actingAs($admin)->put("/admin/projects/{$project->id}", [
            'name' => 'Салон Лавандула', 'description' => 'Описание', 'start_date' => '2026-10-01', 'status' => 'In Progress',
            'manager' => 'Екип', 'category_id' => Category::first()->id, 'client_phone' => '0877000111', 'client_email' => 'nov@abv.bg',
        ])->assertRedirect("/admin/projects/{$project->id}");

        $project->refresh();
        $this->assertSame(['0877000111', 'nov@abv.bg', 'In Progress'], [$project->client_phone, $project->client_email, $project->status]);

        $this->actingAs($admin)->put("/admin/projects/{$project->id}", [
            'name' => 'Салон Лавандула', 'description' => 'Описание', 'start_date' => '2026-10-01', 'status' => 'In Progress',
            'manager' => 'Екип', 'category_id' => Category::first()->id, 'client_email' => 'не-е-имейл',
        ])->assertSessionHasErrors('client_email');
    }

    public function test_menu_is_grouped_in_bulgarian_with_callbacks_due(): void
    {
        $admin = $this->admin();
        OutreachCall::create(['external_id' => 'x2', 'name' => 'Фирма', 'phone' => '0888111222', 'status' => 'callback', 'callback_on' => now()->addDay()]);
        OutreachCall::query()->update(['callback_on' => today()]);

        $this->actingAs($admin)->get('/admin/projects')->assertOk()
            ->assertSee('Клиенти')->assertSee('Продажби')->assertSee('Настройки')
            ->assertSee('Категории на проекти')->assertSee('Потребители')->assertSee('Към сайта')
            ->assertDontSee('>Projects<', false);
    }
}
