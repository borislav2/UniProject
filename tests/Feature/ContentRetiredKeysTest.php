<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\Content;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Keys that nothing public reads any more (home_trust.hero_trust and parts, home_problem.stats_label) were removed from
 * the config, but older admin saves in content_blocks may still carry them.
 */
class ContentRetiredKeysTest extends TestCase
{
    use RefreshDatabase;

    private const RETIRED = [
        'home_trust' => ['hero_trust' => ['x'], 'parts' => [['label' => 'x', 'text' => 'y', 'service' => '']]],
        'home_problem' => ['stats_label' => 'Старо'],
    ];

    public function test_retired_keys_are_gone_from_the_config_and_the_labels(): void
    {
        foreach (self::RETIRED as $block => $keys) {
            foreach (array_keys($keys) as $key) {
                $this->assertNull(config("creatium.$block.$key"), "$block.$key is still in config/creatium.php");
                $this->assertNull(config("creatium_en.$block.$key"), "$block.$key is still in config/creatium_en.php");
            }
        }

        $labels = (new \ReflectionClassConstant(Content::class, 'FIELD_LABELS'))->getValue();
        foreach (['hero_trust', 'parts'] as $key) {
            $this->assertArrayNotHasKey($key, $labels);
        }
    }

    public function test_saved_overrides_with_retired_keys_do_not_break_the_site_or_the_form(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        $content = app(Content::class);
        foreach (self::RETIRED as $block => $keys) {
            foreach (['bg', 'en'] as $locale) {
                $content->save($block, $locale, ['title' => 'Записан заглавие'] + $keys, $user->id);
            }
        }

        // withDefaults keeps the saved extras and still fills the keys it lacks.
        $merged = $content->withDefaults('home_trust', 'bg');
        $this->assertSame(['x'], $merged['hero_trust']);
        $this->assertArrayHasKey('g_parts', $merged);

        foreach (['/', '/paketi', '/uslugi', '/za-nas', '/en', '/en/packages'] as $path) {
            $this->get($path)->assertOk();
        }

        foreach (['home_trust', 'home_problem'] as $block) {
            foreach (['bg', 'en'] as $locale) {
                $this->actingAs($user)->get(route('admin.content.edit', ['key' => $block, 'lang' => $locale]))->assertOk();
            }
        }

        // Saving the form drops what the config no longer has, and does not fail.
        $this->actingAs($user)->put(route('admin.content.update', 'home_problem'), ['locale' => 'bg', 'content' => ['solution_title' => 'Нов заглавие']])
            ->assertRedirect();
        $this->assertArrayNotHasKey('stats_label', (array) $content->override('home_problem', 'bg'));
    }
}
