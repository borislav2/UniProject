<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\PlanBilling;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Design G (concept G) is a second design: only logged-in admins can preview it with ?dizain=g (session, noindex, exit pill),
 * or everyone when CREATIUM_DESIGN=g. The current design stays the default.
 */
class DesignGTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    public function test_a_guest_cannot_switch_to_design_g(): void
    {
        $this->get('/?dizain=g')->assertOk()->assertDontSee('design-g', false)->assertDontSee('g-preview', false);
        $this->get('/paketi')->assertOk()->assertDontSee('design-g', false);
    }

    public function test_a_user_without_admin_access_cannot_switch_either(): void
    {
        $this->actingAs(User::factory()->create())->get('/?dizain=g')->assertOk()->assertDontSee('design-g', false);
    }

    public function test_an_admin_previews_design_g_and_keeps_it_until_leaving(): void
    {
        $this->actingAs($this->admin());

        $this->get('/')->assertOk()->assertDontSee('design-g', false);

        $this->get('/?dizain=g')->assertOk()
            ->assertSee('<html lang="bg" class="design-g">', false)
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertSee('class="g-preview"', false)
            ->assertSee('Преглед на новия дизайн')
            ->assertSee('href="?dizain=star"', false);

        // The next page keeps it: the g header and footer around the old body.
        $this->get('/paketi')->assertOk()
            ->assertSee('class="design-g"', false)
            ->assertSee('class="g-hd"', false)
            ->assertSee('class="g-ft"', false)
            ->assertSee('<meta name="robots" content="noindex">', false);

        $this->get('/?dizain=star')->assertOk()
            ->assertDontSee('design-g', false)
            ->assertDontSee('g-preview', false)
            ->assertDontSee('<meta name="robots" content="noindex">', false);
        $this->get('/')->assertDontSee('design-g', false);
    }

    public function test_the_config_value_shows_design_g_to_everyone_without_preview_marks(): void
    {
        config(['creatium.design' => 'g']);

        $this->get('/')->assertOk()
            ->assertSee('class="design-g"', false)
            ->assertSee('class="g-hd"', false)
            ->assertDontSee('name="robots"', false)
            ->assertDontSee('g-preview', false);
    }

    public function test_design_g_home_in_bulgarian(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['Бранд', 'Сайт', 'Google', 'Реклами', 'Имейли', 'Грижа'] as $part) {
            $this->assertStringContainsString("<b>{$part}</b>", $html);
        }
        $this->assertStringContainsString('Една система. Шест части.', $html);
        $this->assertStringContainsString('Успешен бизнес', $html);
        $this->assertSame(4, substr_count($html, 'data-concept-chip'));
        $this->assertStringContainsString('Концепция', $html);
        $this->assertStringContainsString('href="' . url('/demo/restorant') . '"', $html);
        $this->assertSame(3, substr_count($html, 'class="g-pk'));
        foreach (['от €400', 'от €800', 'от €1' . PlanBilling::THIN_SPACE . '200'] as $price) {
            $this->assertStringContainsString($price, $html);
        }
        $this->assertStringContainsString('€200 / месец', $html);
        $this->assertStringContainsString(PlanBilling::money(2000) . ' / година', $html);
        $this->assertStringContainsString('€650 / месец', $html);
        $this->assertStringContainsString(PlanBilling::money(6500) . ' / година', $html);
        $this->assertStringContainsString('Стратегия, дизайн, технологии и маркетинг <em>под един покрив</em>.', $html);
        $this->assertStringContainsString('Фиксирана цена', $html);
        $this->assertStringContainsString('Запазете час за разговор', $html);
        $this->assertStringContainsString('data-contact-form', $html);
        $this->assertStringContainsString('<em>успех</em>.', $html);
        // SEO of the home page is the same: the JSON-LD blocks are still there.
        $this->assertSame(2, substr_count($html, 'application/ld+json'));
    }

    public function test_design_g_home_in_english(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/en')->assertOk()->getContent();

        foreach (['Brand', 'Website', 'Google', 'Ads', 'Emails', 'Care'] as $part) {
            $this->assertStringContainsString("<b>{$part}</b>", $html);
        }
        $this->assertStringContainsString('One system. Six parts.', $html);
        $this->assertSame(4, substr_count($html, 'data-concept-chip'));
        $this->assertStringContainsString('Concept', $html);
        $this->assertStringContainsString('from €400', $html);
        $this->assertStringContainsString('Monthly', $html);
        $this->assertStringContainsString('Yearly', $html);
        $this->assertStringContainsString('€2,000', $html);
        $this->assertStringContainsString('Strategy, design, technology and marketing <em>under one roof</em>.', $html);
        $this->assertStringContainsString('Book a call', $html);
        // No Bulgarian text on the English page (the footer's industry row is Bulgarian only and is not shown in English).
        $this->assertDoesNotMatchRegularExpression('/[А-Яа-я]/u', PlainCopyTest::visibleText($html));
    }

    public function test_design_g_home_has_no_dashes_or_colons_in_visible_text(): void
    {
        config(['creatium.design' => 'g']);
        foreach (['/', '/en'] as $path) {
            $text = PlainCopyTest::visibleText($this->get($path)->assertOk()->getContent());
            $this->assertSame([], PlainCopyTest::offences($text), $path);
        }
    }

    public function test_design_g_does_not_change_the_current_home_page(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('g-hero', $html);
        $this->assertStringContainsString('surface-night', $html);
    }
}
