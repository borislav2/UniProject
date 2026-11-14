<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\Badges;
use App\Support\Admin\Profiles;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminProfilesTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $profile, string $role = 'admin', string $name = 'Тест'): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['name' => $name, 'profile' => $profile]);
        $user->roles()->attach(Role::where('slug', $role)->value('id'));

        return $user;
    }

    private function category(): int
    {
        $this->seed(CategorySeeder::class);

        return \App\Models\Category::value('id');
    }

    /** @return array{0: string, 1: string} основното меню и „Всички раздели“ */
    private function menus(User $user, string $path = '/admin/dnes'): array
    {
        $html = $this->actingAs($user)->get($path)->assertOk()->getContent();
        $nav = substr($html, strpos($html, 'class="adm-nav"'));
        $nav = substr($nav, 0, strpos($nav, 'adm-side-foot'));
        $parts = explode('class="adm-more"', $nav, 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    public function test_each_profile_menu_has_its_sections_and_the_rest_sit_under_all_sections(): void
    {
        $cases = [
            'dev' => [['Проекти', 'Абонаменти', 'Проверки на сайт', 'Здраве на системата'], ['Запитвания', 'Блог', 'Имейли до клиенти', 'Потенциални клиенти']],
            'sales' => [['Запитвания', 'Потенциални клиенти', 'Търсене на бизнеси', 'Без сайт, за обаждане', 'Следващо обаждане', 'Чат разговори', 'Статистика', 'SEO'], ['Блог', 'Абонаменти', 'Здраве на системата', 'LinkedIn']],
            'brand' => [['Блог', 'Категории на блога', 'Имейли до клиенти', 'LinkedIn', 'Отговори на отзиви', 'Текстове по страниците'], ['Запитвания', 'Проекти', 'Потенциални клиенти', 'Статистика']],
        ];

        foreach ($cases as $profile => [$own, $hidden]) {
            [$main, $more] = $this->menus($this->person($profile, 'admin', "Човек $profile"));
            $this->assertStringContainsString('Днес', $main);
            foreach ($own as $label) {
                $this->assertStringContainsString($label, $main, "$profile: $label should be in the main menu");
                $this->assertStringNotContainsString($label, $more, "$profile: $label should not be repeated under all sections");
            }
            foreach ($hidden as $label) {
                $this->assertStringNotContainsString($label, $main, "$profile: $label must not be in the main menu");
                $this->assertStringContainsString($label, $more, "$profile: $label should be under all sections");
            }
            $this->assertStringContainsString('Всички раздели', $main . 'class="adm-more"' . $more);
        }
    }

    public function test_master_profile_shows_everything_grouped_without_the_extra_group(): void
    {
        [$main, $more] = $this->menus($this->person('all'));

        foreach (['Запитвания', 'Проекти', 'Блог', 'Потребители', 'Здраве на системата', 'LinkedIn', 'Статистика'] as $label) {
            $this->assertStringContainsString($label, $main);
        }
        $this->assertSame('', $more);
        $this->assertStringNotContainsString('Всички раздели', $main);
    }

    public function test_user_management_link_needs_the_admin_role_even_when_the_profile_is_all(): void
    {
        [$main, $more] = $this->menus($this->person('all', 'developer'));
        $this->assertStringNotContainsString('Потребители', $main . $more);
        [$main, $more] = $this->menus($this->person('dev', 'admin', 'Друг'));
        $this->assertStringContainsString('Потребители', $more);
    }

    public function test_sections_outside_the_profile_are_hidden_but_not_locked(): void
    {
        $dev = $this->person('dev', 'developer');
        foreach (['/admin/inquiries', '/admin/posts', '/admin/outreach', '/admin/statistika', '/admin/emails'] as $url) {
            $this->actingAs($dev)->get($url)->assertOk();
        }
        // Правата остават: потребителите са само за роля admin.
        $this->actingAs($dev)->get('/admin/users')->assertForbidden();
        // Скритият раздел се отваря, а групата „Всички раздели“ го маркира като текущ и е отворена.
        $html = $this->actingAs($dev)->get('/admin/inquiries')->getContent();
        $this->assertMatchesRegularExpression('/<details class="adm-more"\s+open/', $html);
    }

    public function test_profile_select_is_only_in_the_user_forms_and_is_saved(): void
    {
        $admin = $this->person('all');
        $this->actingAs($admin)->get('/admin/users/create')->assertOk()->assertSee('name="profile"', false)->assertSee('Профил в админа');
        $this->actingAs($admin)->post('/admin/users', ['name' => 'Нов Човек', 'email' => 'nov@example.com', 'password' => 'long-enough-1', 'password_confirmation' => 'long-enough-1', 'profile' => 'brand'])->assertRedirect();
        $new = User::where('email', 'nov@example.com')->firstOrFail();
        $this->assertSame('brand', $new->profile);

        $this->actingAs($admin)->get("/admin/users/{$new->id}/edit")->assertOk()->assertSee('name="profile"', false);
        $this->actingAs($admin)->put("/admin/users/{$new->id}", ['name' => 'Нов Човек', 'email' => 'nov@example.com', 'profile' => 'sales'])->assertRedirect();
        $this->assertSame('sales', $new->fresh()->profile);
        $this->actingAs($admin)->put("/admin/users/{$new->id}", ['name' => 'Нов Човек', 'email' => 'nov@example.com', 'profile' => 'hacker'])->assertSessionHasErrors('profile');

        // Не-администратор няма достъп до формите, а „Моят профил“ показва профила само за четене.
        $dev = $this->person('dev', 'developer', 'Разработчик');
        $this->actingAs($dev)->get('/admin/users/create')->assertForbidden();
        $this->actingAs($dev)->get('/admin/profile')->assertOk()->assertSee(Profiles::description('dev'))->assertDontSee('name="profile"', false);
        $this->actingAs($dev)->put('/admin/profile', ['name' => 'Разработчик', 'email' => $dev->email, 'profile' => 'all'])->assertRedirect();
        $this->assertSame('dev', $dev->fresh()->profile);
    }

    public function test_unknown_or_empty_profile_behaves_as_all(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['profile' => null]);
        $this->assertSame('all', $user->profile());
        $this->assertFalse($user->isMaster());
        $user->forceFill(['profile' => 'nonsense'])->save();
        $this->assertSame('all', $user->fresh()->profile());
    }

    public function test_migration_maps_existing_users_by_name(): void
    {
        $migration = require database_path('migrations/2026_11_14_000001_add_profile_to_users.php');
        $this->assertSame('dev', $migration::profileFor('Борислав Иванов'));
        $this->assertSame('dev', $migration::profileFor('Borislav'));
        $this->assertSame('sales', $migration::profileFor('Владимир Цончев'));
        $this->assertSame('sales', $migration::profileFor('Влади'));
        $this->assertSame('sales', $migration::profileFor('Vladi'));
        $this->assertSame('brand', $migration::profileFor('Мария Флорова'));
        $this->assertSame('brand', $migration::profileFor('maria'));
        $this->assertSame('all', $migration::profileFor('Админ'));

        foreach (['Борислав', 'Мария', 'Някой Друг'] as $i => $name) {
            DB::table('users')->insert(['name' => $name, 'email' => "m$i@example.com", 'password' => 'x', 'profile' => null]);
        }
        $migration->up();
        $this->assertSame(['dev', 'brand', 'all'], DB::table('users')->orderBy('id')->pluck('profile')->all());
    }

    public function test_today_shows_the_blocks_of_the_profile(): void
    {
        Project::create(['name' => 'Запитване от Нов Клиент', 'description' => 'x', 'start_date' => now(), 'status' => 'Planning', 'manager' => 'x', 'category_id' => $this->category(), 'source' => 'website', 'stage' => 'new', 'client_phone' => '0888111222']);
        Project::create(['name' => 'Сайт Строй', 'description' => 'x', 'start_date' => now(), 'status' => 'In Progress', 'manager' => 'x', 'category_id' => $this->category()]);

        $dev = $this->person('dev', 'admin', 'Борислав');
        $this->actingAs($dev)->get('/admin/dnes')->assertOk()
            ->assertSee('Проекти в работа')->assertSee('Сайт Строй')->assertSee('Нови проверки на сайт')->assertSee('Здраве на системата')
            ->assertDontSee('Нови запитвания')->assertDontSee('Месечни отчети за изпращане');

        $sales = $this->person('sales', 'admin', 'Владимир');
        $this->actingAs($sales)->get('/admin/dnes')->assertOk()
            ->assertSee('Нови запитвания')->assertSee('Нов Клиент')->assertSee('Следващо обаждане')->assertSee('Консултации')
            ->assertDontSee('Проекти в работа')->assertDontSee('Блог: насрочени и чернови');

        $brand = $this->person('brand', 'admin', 'Мария');
        $this->actingAs($brand)->get('/admin/dnes')->assertOk()
            ->assertSee('Блог: насрочени и чернови')->assertSee('LinkedIn: време е да напишете')->assertSee('Месечни отчети за изпращане')
            ->assertDontSee('Нови запитвания')->assertDontSee('Проекти в работа');

        // Празен блок се свива до един ред.
        $this->actingAs($brand)->get('/admin/dnes')->assertSee('Няма насрочени статии и чернови.');
    }

    public function test_master_sees_the_whole_team_by_default_and_everything_in_compact_form(): void
    {
        $master = $this->person('all', 'admin', 'Админ');
        $this->person('dev', 'developer', 'Борислав');
        Project::create(['name' => 'Сайт Строй', 'description' => 'x', 'start_date' => now(), 'status' => 'In Progress', 'manager' => 'x', 'category_id' => $this->category()]);

        $this->actingAs($master)->get('/admin/dnes')->assertOk()
            ->assertSee('Денят на екипа')->assertSee('Целият екип')
            ->assertSee('Проекти в работа')->assertSee('Нови запитвания')->assertSee('Блог: насрочени и чернови')->assertSee('Направено днес');
        // Таб на човек показва блоковете на неговия профил.
        $boris = User::where('name', 'Борислав')->first();
        $this->actingAs($master)->get('/admin/dnes?user=' . $boris->id)->assertSee('Денят на Борислав')->assertDontSee('Нови запитвания');
    }

    public function test_badges_are_computed_once_per_request(): void
    {
        $user = $this->person('all');
        $this->actingAs($user)->get('/admin/dnes')->assertOk();
        $queries = 0;
        DB::listen(function ($q) use (&$queries) { if (str_contains($q->sql, 'from "projects"') && str_contains($q->sql, '"read_at" is null')) { $queries++; } });
        $this->actingAs($user)->get('/admin/projects')->assertOk();
        // Непрочетените запитвания и проверките на сайт: по една заявка, не по една на всяко място в менюто.
        $this->assertLessThanOrEqual(3, $queries);
        $this->assertArrayHasKey('inquiries', Badges::all());
    }

    public function test_make_admin_takes_a_profile(): void
    {
        $this->artisan('creatium:make-admin', ['email' => 'maria@example.com', '--name' => 'Мария', '--profile' => 'brand'])
            ->expectsQuestion('Парола (минимум 12 символа)', 'a-long-enough-password')
            ->assertSuccessful();
        $this->assertSame('brand', User::where('email', 'maria@example.com')->firstOrFail()->profile);
        $this->assertTrue(User::where('email', 'maria@example.com')->firstOrFail()->isAdmin());

        $this->artisan('creatium:make-admin', ['email' => 'x@example.com', '--profile' => 'boss'])->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => 'x@example.com']);

        $this->artisan('creatium:make-admin', ['email' => 'default@example.com'])
            ->expectsQuestion('Парола (минимум 12 символа)', 'a-long-enough-password')->assertSuccessful();
        $this->assertSame('all', User::where('email', 'default@example.com')->firstOrFail()->profile);
    }

    public function test_a_user_created_in_the_admin_can_open_the_panel(): void
    {
        $admin = $this->person('all', 'admin', 'Главен');
        $form = ['name' => 'Мария Флорова', 'email' => 'maria@example.com', 'password' => 'long-enough-1', 'password_confirmation' => 'long-enough-1', 'profile' => 'brand'];
        $this->actingAs($admin)->post('/admin/users', $form)->assertRedirect();

        $maria = User::where('email', 'maria@example.com')->firstOrFail();
        $this->assertTrue($maria->canAccessAdminPanel());
        $this->assertFalse($maria->isAdmin());
        $this->actingAs($maria)->get('/admin/dnes')->assertOk();
        $this->actingAs($maria)->get('/admin/users')->assertForbidden();

        // С отметката акаунтът управлява и потребителите; без нея отново не.
        $this->actingAs($admin)->put("/admin/users/{$maria->id}", ['name' => $maria->name, 'email' => $maria->email, 'profile' => 'brand', 'manages_users' => '1'])->assertRedirect();
        $this->assertTrue($maria->fresh()->isAdmin());
        $this->actingAs($admin)->put("/admin/users/{$maria->id}", ['name' => $maria->name, 'email' => $maria->email, 'profile' => 'brand', 'manages_users' => '0'])->assertRedirect();
        $this->assertFalse($maria->fresh()->isAdmin());
        $this->assertTrue($maria->fresh()->canAccessAdminPanel());

        // Никой не си отнема сам управлението.
        $this->actingAs($admin)->put("/admin/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'profile' => 'all', 'manages_users' => '0'])->assertRedirect();
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_migration_gives_access_to_accounts_that_were_saved_without_a_role(): void
    {
        \App\Models\Role::firstOrCreate(['slug' => 'project-manager'], ['name' => 'Project Manager']);
        $lost = User::create(['name' => 'Владимир Цончев', 'email' => 'vladi@example.com', 'password' => bcrypt('long-enough-1')]);
        $this->assertFalse($lost->canAccessAdminPanel());

        (require database_path('migrations/2026_11_14_000002_give_roleless_users_admin_access.php'))->up();

        $this->assertTrue($lost->fresh()->canAccessAdminPanel());
        $this->assertFalse($lost->fresh()->isAdmin());
    }
}
