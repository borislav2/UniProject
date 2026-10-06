<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', $slug)->value('id'));

        return $user;
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/projects')->assertRedirect('/login');
    }

    public function test_user_without_role_cannot_enter_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertRedirect('/');
    }

    public function test_developer_can_use_panel_but_not_manage_users(): void
    {
        $developer = $this->userWithRole('developer');

        $this->actingAs($developer)->get('/admin')->assertOk();
        $this->actingAs($developer)->get('/admin/users')->assertForbidden();
    }

    public function test_admin_can_manage_users(): void
    {
        $this->actingAs($this->userWithRole('admin'))->get('/admin/users')->assertOk();
    }

    public function test_project_search_route_works(): void
    {
        $this->seed(CategorySeeder::class);
        Project::create([
            'name' => 'Сайт за Вкусотия', 'description' => 'd', 'start_date' => '2026-01-01',
            'status' => 'Planning', 'manager' => 'm', 'category_id' => Category::first()->id,
        ]);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/admin/projects/search?search=Вкусотия')
            ->assertOk()
            ->assertSee('Сайт за Вкусотия');
    }

    public function test_admin_can_save_a_project_website_address(): void
    {
        $this->seed(CategorySeeder::class);
        $admin = $this->actingAs($this->userWithRole('admin'));
        $base = [
            'name' => 'Сайт на клиент', 'description' => 'Описание.', 'start_date' => '2026-01-01', 'end_date' => '2026-02-01',
            'status' => 'Completed', 'manager' => 'Екип', 'category_id' => Category::first()->id, 'is_public' => '1',
        ];

        $admin->get('/admin/projects/create')->assertOk()->assertSee('Адрес на сайта');

        $admin->post('/admin/projects', $base + ['website_url' => 'javascript:alert(1)'])->assertSessionHasErrors('website_url');
        $admin->post('/admin/projects', $base + ['website_url' => 'https://example.bg'])->assertSessionHasNoErrors();

        $this->assertSame('https://example.bg', Project::where('name', 'Сайт на клиент')->value('website_url'));
    }

    public function test_login_works_and_wrong_password_shows_error(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-horse-battery')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'correct-horse-battery'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'a@example.com', 'password' => 'x']);
        }

        $this->post('/login', ['email' => 'a@example.com', 'password' => 'x'])->assertStatus(429);
    }

    public function test_make_admin_command_creates_admin(): void
    {
        $this->artisan('creatium:make-admin', ['email' => 'boss@example.com', '--name' => 'Boss'])
            ->expectsQuestion('Парола (минимум 12 символа)', 'a-long-enough-password')
            ->assertSuccessful();

        $this->assertTrue(User::where('email', 'boss@example.com')->firstOrFail()->isAdmin());
    }

    public function test_make_admin_rejects_short_passwords(): void
    {
        $this->artisan('creatium:make-admin', ['email' => 'boss@example.com'])
            ->expectsQuestion('Парола (минимум 12 символа)', 'short')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'boss@example.com']);
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(CategorySeeder::class);

        $this->assertSame(3, Role::count());
        $this->assertSame(1, Category::where('name', 'Ново запитване')->count());
    }
}
