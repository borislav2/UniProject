<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Http\Controllers\PageController;
use App\Support\PlanBilling;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
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

    /** Replaces the no-op Vite of the test case with one that names the entry files it was asked for. */
    private function fakeViteEntries(): void
    {
        $this->app->instance(Vite::class, new class extends Vite {
            public function __invoke($entrypoints, $buildDirectory = null)
            {
                return new HtmlString('<!--vite:' . implode(',', (array) $entrypoints) . '-->');
            }
        });
        \Illuminate\Support\Facades\Vite::clearResolvedInstance(Vite::class);
    }

    public function test_the_phone_bar_exists_in_design_g_only(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('data-g-bar', $html);

        config(['creatium.design' => 'g']);
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-g-bar', $html);
        $this->assertStringContainsString('Обадете се', $html);
        $this->assertMatchesRegularExpression('/class="g-bar-call" href="tel:\+?[0-9]+"/', $html);
        $this->assertStringContainsString('class="g-bar-offer" href="' . url('/paketi') . '#oferta"', $html);

        // Inner pages carry it too, in English as well.
        $this->get('/paketi')->assertOk()->assertSee('data-g-bar', false);
        $this->get('/en')->assertOk()->assertSee('Call us')->assertSee('Get a quote');
    }

    public function test_the_hero_has_a_trust_line_and_a_link_to_the_website_check(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['Фиксирана цена в офертата', 'Отговор до един работен ден', 'Месечен отчет'] as $line) {
            $this->assertStringContainsString("<li>{$line}</li>", $html);
        }
        $this->assertStringContainsString('href="' . url('/proverka-na-sait') . '">Проверете сайта си безплатно', $html);
        $this->get('/en')->assertSee('Check your website for free');
    }

    public function test_the_six_parts_link_to_their_service_pages_with_a_short_line(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('website') . '"><span class="g-mono">02</span><b>Сайт</b><small>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('google') . '"><span class="g-mono">03</span><b>Google</b>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('seo') . '"><span class="g-mono">04</span><b>Реклами</b>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('email') . '"><span class="g-mono">05</span><b>Имейли</b>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('support') . '"><span class="g-mono">06</span><b>Грижа</b>', $html);
        $this->assertStringContainsString('<a href="' . url('/uslugi') . '"><span class="g-mono">01</span><b>Бранд</b>', $html);

        $en = $this->get('/en')->assertOk()->getContent();
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('website', 'en') . '"><span class="g-mono">02</span><b>Website</b>', $en);
    }

    public function test_the_proof_block_is_hidden_without_data_and_shown_with_a_review_count(): void
    {
        config(['creatium.design' => 'g', 'creatium.google_reviews' => ['rating' => 0, 'count' => 0, 'url' => null]]);
        $this->get('/')->assertOk()->assertDontSee('id="dokazatelstva"', false)->assertDontSee('g-proof', false);

        // A rating without a count is not shown either.
        config(['creatium.google_reviews' => ['rating' => 4.9, 'count' => 0, 'url' => null]]);
        $this->get('/')->assertDontSee('id="dokazatelstva"', false);

        config(['creatium.google_reviews' => ['rating' => 4.9, 'count' => 37, 'url' => 'https://g.page/r/example']]);
        $this->get('/')->assertOk()
            ->assertSee('id="dokazatelstva"', false)
            ->assertSee('4,9 / 5')
            ->assertSee('Оценка в Google от 37 отзива')
            ->assertSee('href="https://g.page/r/example"', false);
        $this->get('/en')->assertSee('4.9 / 5')->assertSee('Google rating from 37 reviews');
    }

    public function test_the_proof_block_shows_testimonials_and_results_only_when_filled(): void
    {
        config(['creatium.design' => 'g', 'creatium.google_reviews' => ['rating' => 0, 'count' => 0, 'url' => null]]);

        // The default is one empty item: it must not render.
        $this->assertSame('', config('creatium.home_trust.testimonials.0.quote'));
        $this->get('/')->assertDontSee('id="dokazatelstva"', false);

        config([
            'creatium.home_trust.testimonials' => [['quote' => 'Получихме запитвания още първата седмица', 'name' => 'Мария', 'business' => 'Студио Лавандула']],
            'creatium.home_trust.results' => [['value' => '12', 'text' => 'запитвания за първия месец']],
        ]);
        $this->get('/')->assertOk()
            ->assertSee('id="dokazatelstva"', false)
            ->assertSee('Получихме запитвания още първата седмица')
            ->assertSee('Студио Лавандула')
            ->assertSee('запитвания за първия месец');
    }

    public function test_the_g_home_loads_its_own_lean_stylesheet_and_not_app_css(): void
    {
        $this->fakeViteEntries();
        config(['creatium.design' => 'g']);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('resources/css/g-lean.css', $html);
        $this->assertStringContainsString('resources/js/g-bar.js', $html);
        $this->assertStringContainsString('resources/js/g-home.js', $html);
        $this->assertStringNotContainsString('resources/css/app.css', $html);
        $this->assertStringNotContainsString('resources/js/app.js', $html);

        // A page whose body is still the old one keeps app.css (and g.css on top).
        $inner = $this->get('/paketi')->assertOk()->getContent();
        $this->assertStringContainsString('resources/css/app.css', $inner);
        $this->assertStringContainsString('resources/css/g.css', $inner);
        $this->assertStringContainsString('resources/js/g-bar.js', $inner);
    }

    public function test_the_current_design_still_loads_app_css_only(): void
    {
        $this->fakeViteEntries();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<!--vite:resources/css/app.css,resources/js/app.js-->', $html);
        $this->assertStringNotContainsString('g-lean', $html);
        $this->assertStringNotContainsString('g-bar', $html);
    }

    public function test_the_menu_does_not_mark_about_us_on_the_contact_page(): void
    {
        config(['creatium.design' => 'g']);

        $contact = $this->get('/kontakti')->assertOk()->getContent();
        $this->assertStringNotContainsString('aria-current="page"', $contact);
        $this->assertMatchesRegularExpression('/aria-current="page"\s*>За нас/u', $this->get('/za-nas')->assertOk()->getContent());
        $this->assertMatchesRegularExpression('/aria-current="page"\s*>Пакети/u', $this->get('/paketi')->assertOk()->getContent());
    }
}
