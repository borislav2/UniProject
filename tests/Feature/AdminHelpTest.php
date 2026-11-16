<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\Admin\Help;
use App\Support\Admin\Profiles;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Всеки екран на админа обяснява с прост български какво е и защо го има („Какво е това?“ и „Как работи админът“). */
class AdminHelpTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $profile = 'all'): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['profile' => $profile]);
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    /** @return list<string> */
    private function menuRoutes(): array
    {
        $routes = [];
        foreach (Profiles::keys() as $key) {
            foreach ([Profiles::menu($key, true), Profiles::others($key, true)] as $menu) {
                foreach ($menu as $items) {
                    foreach ($items as $item) {
                        $routes[] = $item['route'];
                    }
                }
            }
            foreach (Profiles::phoneNav($key) as $item) {
                $routes[] = $item['route'];
            }
        }

        return array_values(array_unique($routes));
    }

    public function test_every_menu_route_of_every_profile_shows_its_help_block(): void
    {
        $user = $this->admin();
        $routes = $this->menuRoutes();
        $this->assertContains('admin.today', $routes);
        $this->assertContains('admin.outreach.autopilot', $routes);

        foreach ($routes as $route) {
            $help = Help::forRoute($route);
            $this->assertNotNull($help, "Няма обяснение за $route");

            $this->actingAs($user)->get(route($route))
                ->assertOk()
                ->assertSee('data-adm-help', false)
                ->assertSee('Какво е това?')
                ->assertSee($help['what'])
                ->assertSee($help['why']);
        }
    }

    public function test_other_screens_have_help_too(): void
    {
        $user = $this->admin();
        foreach (['admin.seo.posts', 'admin.seo.health', 'admin.seo.keywords', 'admin.seo.ai', 'admin.projects.create', 'admin.posts.create', 'admin.profile.edit', 'admin.users.create'] as $route) {
            $help = Help::forRoute($route);
            $this->assertNotNull($help, $route);
            $this->actingAs($user)->get(route($route))->assertOk()->assertSee($help['what']);
        }
    }

    public function test_the_longest_prefix_wins(): void
    {
        $this->assertSame('admin.seo.health', Help::forRoute('admin.seo.health.run')['key']);
        $this->assertSame('admin.seo', Help::forRoute('admin.seo.index')['key']);
        $this->assertSame('admin.outreach.calls.next', Help::forRoute('admin.outreach.calls.next')['key']);
        $this->assertSame('admin.outreach.calls', Help::forRoute('admin.outreach.calls.index')['key']);
        $this->assertSame('admin.outreach', Help::forRoute('admin.outreach.index')['key']);
        $this->assertSame('admin.projects.show', Help::forRoute('admin.projects.show')['key']);
        $this->assertNull(Help::forRoute('admin.help'));
        $this->assertNull(Help::forRoute(null));
    }

    public function test_every_help_text_is_complete_and_has_no_dashes_or_colons(): void
    {
        $this->assertNotEmpty(Help::MAP);
        foreach (Help::MAP as $key => $entry) {
            foreach (['title', 'what', 'why'] as $field) {
                $this->assertNotSame('', trim($entry[$field] ?? ''), "$key $field");
            }
            $this->assertGreaterThanOrEqual(2, count($entry['steps']), $key);
            $this->assertLessThanOrEqual(4, count($entry['steps']), $key);

            $text = implode(' ', array_merge([$entry['title'], $entry['what'], $entry['why']], $entry['steps']));
            $this->assertSame([], PlainCopyTest::offences($text), "$key: " . implode(' | ', PlainCopyTest::offences($text)));
        }
    }

    public function test_the_help_page_lists_every_menu_section(): void
    {
        $user = $this->admin();
        $response = $this->actingAs($user)->get(route('admin.help'))->assertOk()->assertSee('Как работи админът');

        $items = collect(Help::sections())->flatten(1);
        foreach (Profiles::menu('all', true) as $menuItems) {
            foreach ($menuItems as $item) {
                $this->assertTrue($items->contains(fn ($i) => $i['route'] === $item['route']), $item['route']);
            }
        }
        foreach ($items as $item) {
            $response->assertSee($item['help']['what']);
        }

        // Само съдържанието на страницата (менюто има свои етикети).
        $html = $response->getContent();
        $start = strpos($html, 'adm-help-intro');
        $text = PlainCopyTest::visibleText(substr($html, $start, strpos($html, '</main>') - $start));
        $this->assertSame([], PlainCopyTest::offences($text));
    }

    public function test_the_help_page_needs_a_login(): void
    {
        $this->get('/admin/pomosht')->assertRedirect('/login');
    }
}
