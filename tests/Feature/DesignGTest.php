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

        // No „Една система“ section and no news list: the projects come right after the hero.
        $this->assertStringNotContainsString('Една система', $html);
        $this->assertStringNotContainsString('id="sistema"', $html);
        $this->assertStringNotContainsString('g-news', $html);
        $this->assertStringContainsString('Проекти, създадени с мисъл за растеж', $html);
        $this->assertStringContainsString('Разгледайте част от нашите персонализирани решения за различни индустрии.', $html);
        $this->assertLessThan(strpos($html, 'id="paketi"'), strpos($html, 'id="proekti"'));

        // All ten industry sites, each with its micro description and a link to its page; none carries a label.
        $this->assertStringNotContainsString('data-concept-chip', $html);
        $this->assertStringNotContainsString('Концепция', $html);
        $this->assertSame(10, substr_count($html, 'class="g-micro"'));
        foreach (collect(config('creatium.demos')) as $demo) {
            $this->assertStringContainsString('href="' . url('/demo/' . $demo['slug']) . '"', $html);
            $this->assertStringContainsString($demo['g_micro'], $html);
        }

        // Packages: the owners' names, prices (monthly and yearly in the page), groups that open on demand.
        $this->assertStringContainsString('План за всеки етап от вашия бизнес', $html);
        $this->assertStringContainsString('Ясна структура за изграждане, поддръжка и устойчив растеж.', $html);
        $this->assertSame(3, substr_count($html, 'class="g-plan '));
        foreach (['Дигитален старт', 'Активен маркетинг', 'Стратегически маркетинг'] as $name) {
            $this->assertStringContainsString("<h3>{$name}</h3>", $html);
        }
        $nb = "\u{00A0}";
        foreach (['от</span> €500', 'от</span> €800', "от</span> €1{$nb}200"] as $price) {
            $this->assertStringContainsString($price, $html);
        }
        $this->assertStringContainsString('/месец', $html);
        $this->assertStringContainsString("€5{$nb}000<span class=\"g-per\">/година", $html);
        $this->assertStringContainsString('2 месеца безплатно', $html);
        $this->assertStringContainsString('<details class="g-grp">', $html);
        $this->assertStringNotContainsString('<details class="g-grp" open', $html);
        $this->assertStringContainsString('<b>Разбор на бизнеса</b>', $html);

        $this->assertStringContainsString('Стратегия, дизайн, технологии и маркетинг <em>под един покрив</em>.', $html);
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

        foreach (['Brand positioning', 'Digital platforms and websites', 'Online reputation and location', 'Digital support and protection'] as $part) {
            $this->assertStringContainsString("<b>{$part}</b>", $html);
        }
        $this->assertStringNotContainsString('data-concept-chip', $html);
        $this->assertSame(10, substr_count($html, 'class="g-micro"'));
        $this->assertStringContainsString('Projects built with growth in mind', $html);
        $this->assertStringContainsString('A plan for every stage of your business', $html);
        $this->assertStringContainsString('>from</span> €500', $html);
        $this->assertStringContainsString('Monthly', $html);
        $this->assertStringContainsString('Yearly', $html);
        $this->assertStringContainsString('/month', $html);
        $this->assertStringContainsString('€5,000', $html);
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
        $this->assertStringContainsString('class="g-bar-offer" href="' . url('/paketi') . '#oferta">Изпратете запитване', $html);
        $this->assertStringNotContainsString('Получете оферта', $html);

        // Inner pages carry it too, in English as well.
        $this->get('/paketi')->assertOk()->assertSee('data-g-bar', false);
        $this->get('/en')->assertOk()->assertSee('Call us')->assertSee('Send inquiry');
    }

    public function test_the_hero_has_the_three_new_points_and_no_eyebrow_or_site_check_link(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['Прозрачно бюджетно планиране', 'AI за маркетинг и криейтив', 'Стратегически брандинг'] as $line) {
            $this->assertStringContainsString("<li>{$line}</li>", $html);
        }
        $hero = substr($html, strpos($html, 'class="g-hero"'), strpos($html, 'id="proekti"') - strpos($html, 'class="g-hero"'));
        $this->assertStringNotContainsString('g-eyebrow', $hero);
        $this->assertStringNotContainsString('Дигитална агенция', $hero);
        $this->assertStringNotContainsString('Проверете сайта си безплатно', $hero);
        $this->assertStringNotContainsString('/proverka-na-sait', $hero);
        $this->assertStringContainsString('Изпратете запитване', $hero);

        // The header uses the logo image (a white one for the dark theme), and every CTA reads „Изпратете запитване“.
        $this->assertStringContainsString('class="g-logo-d" src="' . asset('images/logo.webp') . '"', $html);
        $this->assertStringContainsString('class="g-logo-w" src="' . asset('images/logo-white.webp') . '"', $html);
        $this->assertStringNotContainsString('Получете оферта', $html);
        $this->assertStringContainsString('class="g-btn g-sm g-offer" href="' . url('/paketi') . '#oferta">Изпратете запитване', $html);
    }

    public function test_the_six_services_have_the_owners_titles_and_link_to_their_service_pages(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<a href="' . url('/uslugi') . '"><span class="g-mono">01</span><b>Бранд позициониране</b>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('website') . '"><span class="g-mono">02</span><b>Дигитални платформи &amp; Сайтове</b><small>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('google') . '"><span class="g-mono">03</span><b>Онлайн репутация &amp; Локация</b>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('seo') . '"><span class="g-mono">04</span><b>Кампании за запитвания &amp; Продажби</b>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('email') . '"><span class="g-mono">05</span><b>Задържане &amp; Връзка с клиентите</b>', $html);
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('support') . '"><span class="g-mono">06</span><b>Дигитална поддръжка &amp; Защита</b>', $html);
        $this->assertStringContainsString('стимулират повторни поръчки', $html);

        $en = $this->get('/en')->assertOk()->getContent();
        $this->assertStringContainsString('<a href="' . PageController::serviceUrl('website', 'en') . '"><span class="g-mono">02</span><b>Digital platforms and websites</b>', $en);
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

        // The inner pages have their own g bodies and load g-inner.css (the lean setup plus the page components), never app.css.
        $inner = $this->get('/paketi')->assertOk()->getContent();
        $this->assertStringContainsString('resources/css/g-inner.css', $inner);
        $this->assertStringNotContainsString('resources/css/app.css', $inner);
        $this->assertStringNotContainsString('resources/js/app.js', $inner);
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

    /** @return array<string, array{string}> */
    public static function innerPages(): array
    {
        $pages = ['/paketi', '/proekti', '/kontakti', '/uslugi', '/uslugi/izrabotka-na-sait', '/uslugi/seo-i-ai-vidimost', '/izrabotka-na-sait-za-restorant',
            '/za-nas', '/vaprosi', '/blog', '/proverka-na-sait', '/poveritelnost', '/usloviya', '/biskvitki',
            '/en/packages', '/en/projects', '/en/contact', '/en/services', '/en/services/website-design', '/en/about', '/en/faq', '/en/blog',
            '/en/website-check', '/en/privacy', '/en/terms', '/en/cookies'];

        return array_combine($pages, array_map(fn ($p) => [$p], $pages));
    }

    /** @dataProvider innerPages */
    public function test_every_inner_page_has_a_g_body_and_loads_the_lean_inner_css_without_app_css(string $path): void
    {
        $this->fakeViteEntries();
        config(['creatium.design' => 'g']);

        $html = $this->get($path)->assertOk()->getContent();
        $this->assertStringContainsString('class="design-g"', $html);
        $this->assertStringContainsString('class="g-hd"', $html);
        $this->assertStringContainsString('class="g-ph', $html, $path . ' has no g page head');
        $this->assertStringContainsString('resources/css/g-inner.css', $html);
        $this->assertStringNotContainsString('resources/css/app.css', $html);
        $this->assertStringNotContainsString('resources/js/app.js', $html);
        // No blue gradients, rounded cards or old kickers from the current design in the page body (between the g header and footer).
        $body = substr($html, strpos($html, 'class="g-hd"'), strpos($html, 'class="g-ft"') - strpos($html, 'class="g-hd"'));
        foreach (['bg-brand-gradient', 'rounded-3xl', 'rounded-2xl', 'surface-night', 'kicker', 'shadow-xl'] as $old) {
            $this->assertStringNotContainsString($old, $body, $path . ' still has ' . $old);
        }
    }

    public function test_g_blog_post_and_category_pages_and_the_404_page_use_g(): void
    {
        $this->fakeViteEntries();
        config(['creatium.design' => 'g']);
        $category = \App\Models\PostCategory::create(['name' => ['bg' => 'Сайтове'], 'slug' => ['bg' => 'sajtove']]);
        $post = \App\Models\Post::create([
            'title' => ['bg' => 'Профил в Google: как да го попълните'],
            'slug' => ['bg' => 'profil-v-google'],
            'body' => ['bg' => "## Първо\n\nТекст **удебелен**."],
            'post_category_id' => $category->id,
            'status' => 'published',
            'published_at' => now()->subDays(3),
        ]);

        $show = $this->get('/blog/profil-v-google')->assertOk()
            ->assertSee('class="g-ph g-post-head"', false)
            ->assertSee('class="g-prose g-article"', false)
            ->assertSee('Профил в Google: как да го попълните')
            ->assertSee('<time datetime="' . $post->published_at->toDateString() . '">', false);
        $this->assertStringNotContainsString('resources/css/app.css', $show->getContent());

        $this->get('/blog/kategoriya/sajtove')->assertOk()->assertSee('class="g-posts"', false)->assertSee('class="g-post"', false);
        $this->get('/blog')->assertOk()->assertSee('aria-current="page"', false);

        $missing = $this->get('/nope')->assertNotFound()->assertSee('Страницата не е намерена')->assertSee('class="g-ph g-404"', false);
        $this->assertStringContainsString('resources/css/g-inner.css', $missing->getContent());
        $this->assertStringNotContainsString('resources/css/app.css', $missing->getContent());
    }

    public function test_the_current_design_keeps_app_css_and_its_old_bodies_on_every_inner_page(): void
    {
        $this->fakeViteEntries();

        foreach (['/paketi' => 'data-packages-hero', '/proekti' => 'data-portfolio-grid', '/kontakti' => 'id="contact-form"', '/uslugi' => 'kicker kicker-light',
            '/za-nas' => 'kicker kicker-light', '/vaprosi' => 'kicker', '/blog' => 'text-lg leading-relaxed text-gray-600', '/proverka-na-sait' => 'id="site-check-form"', '/poveritelnost' => 'space-y-8'] as $path => $oldMarker) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('<!--vite:resources/css/app.css,resources/js/app.js-->', $html, $path);
            $this->assertStringContainsString($oldMarker, $html, $path);
            $this->assertStringNotContainsString('g-inner', $html, $path);
            $this->assertStringNotContainsString('class="g-ph', $html, $path);
        }
        $this->get('/nope')->assertNotFound()->assertSee('text-6xl', false);
    }

    public function test_g_packages_has_the_new_plans_five_steps_and_only_the_quote_form_at_the_end(): void
    {
        config(['creatium.design' => 'g']);

        $html = $this->get('/paketi')->assertOk()
            ->assertSee('data-g-billing-root', false)
            ->assertSee('Месечно')->assertSee('Годишно')
            ->assertSee('План за всеки етап от вашия бизнес')
            ->assertSee('Дигитален старт')->assertSee('Стратегически маркетинг')
            ->assertSee('id="oferta"', false)
            ->assertSee('Нашите 5 стъпки')
            ->assertSee('Първичен контакт и заявка')->assertSee('Финални тестове и официален старт')
            ->assertDontSee('Всичко включено')->assertDontSee('Как започваме')
            ->assertDontSee('Предпочитате разговор?')->assertDontSee('Условия')->assertDontSee('Въпроси')
            ->assertDontSee('data-sticky-cta', false)
            ->getContent();
        $this->assertSame(3, substr_count($html, 'class="g-plan '));
        $this->assertStringContainsString('class="g-steps"', $html);
        $this->assertSame(5, substr_count($html, '<span class="g-n g-mono">0'));
        $this->assertStringContainsString('data-bv="y"', $html);
        $this->assertStringContainsString('data-bv="m"', $html);
        $this->assertStringNotContainsString('FAQPage', $html);
        $this->assertStringNotContainsString('<table', $html);
        $this->assertStringContainsString('<details class="g-grp">', $html);
        $this->assertGreaterThanOrEqual(7, substr_count($html, '<details class="g-grp">'));
        $nb = "\u{00A0}";
        $this->assertStringContainsString('от</span> €500', $html);
        $this->assertStringContainsString('от</span> €800', $html);
        $this->assertStringContainsString("от</span> €1{$nb}200", $html);
        // Yearly default is the monthly figure times ten (2 months free) until the owners confirm their own.
        $this->assertStringContainsString("€5{$nb}000<span class=\"g-per\">/година", $html);
        $this->assertStringContainsString("€12{$nb}000<span class=\"g-per\">/година", $html);
        $this->assertStringContainsString('2 месеца безплатно', $html);
        // The quote form's plan list uses the new names.
        $this->assertStringContainsString('>Активен маркетинг</option>', $html);
    }

    public function test_the_yearly_figure_is_the_saved_one_or_the_monthly_one_times_the_free_months_rule(): void
    {
        $plans = ['g_items' => [['name' => 'A', 'monthly' => 500, 'yearly' => 4800], ['name' => 'B', 'monthly' => 800, 'yearly' => '']], 'billing' => ['yearly_months' => 10]];
        $cards = \App\Support\GPlans::cards($plans);
        $this->assertSame("€4\u{00A0}800", $cards[0]['yearly']);
        $this->assertSame("€8\u{00A0}000", $cards[1]['yearly']);
        $this->assertSame(0, \App\Support\GPlans::indexFor(['g_items' => [1, 2, 3], 'items' => [['name' => 'Стартов двигател'], ['name' => 'Активен маркетинг и растеж']]], 'Стартов двигател'));
        $this->assertSame(2, \App\Support\GPlans::indexFor(['g_items' => [1, 2, 3], 'items' => []], null));
    }

    public function test_the_concept_page_in_g_ends_with_four_blocks_and_no_faq_while_the_current_design_is_unchanged(): void
    {
        $current = $this->get('/demo/restorant')->assertOk()->getContent();
        $this->assertStringContainsString('Въпроси и отговори', $current);
        $this->assertStringContainsString('Проблемите на бранша', $current);
        $this->assertStringNotContainsString('Анализ на бранша', $current);

        config(['creatium.design' => 'g']);
        $html = $this->get('/demo/restorant')->assertOk()->getContent();
        foreach (['Анализ на бранша', 'Нашето решение', 'Какво се случва след пускането на сайта?', 'Дигитален старт'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString('Заявките пристигат при вас', $html);
        $this->assertStringContainsString('href="' . url('/paketi') . '?paket=0#oferta"', $html);
        $this->assertStringNotContainsString('Въпроси и отговори', $html);
        $this->assertStringNotContainsString('<details', $html);

        // A concept without a standard package points to the last plan.
        $shop = $this->get('/demo/onlain-magazin')->assertOk()->getContent();
        $this->assertStringContainsString('Стратегически маркетинг', $shop);
        $this->assertStringContainsString('?paket=2#oferta', $shop);
    }

    public function test_g_projects_is_one_grid_with_real_projects_first_and_labelled_concepts(): void
    {
        config(['creatium.design' => 'g']);
        $category = \App\Models\Category::create(['name' => 'Уебсайт']);
        \App\Models\Project::create(['name' => 'Реален клиент ООД', 'description' => 'Нов сайт', 'status' => 'Completed', 'is_public' => true, 'category_id' => $category->id, 'start_date' => now()->subMonth(), 'end_date' => now(), 'manager' => 'Борислав']);

        $html = $this->get('/proekti')->assertOk()->assertDontSee('data-concept-chip', false)->assertDontSee('Концепция')->getContent();
        $this->assertSame(1, substr_count($html, 'data-portfolio-grid'));
        $this->assertLessThan(strpos($html, config('creatium.demos.0.name')), strpos($html, 'Реален клиент ООД'));
        $this->assertSame(11, substr_count($html, 'class="g-case"'));
        $this->assertSame(substr_count($html, 'class="g-frame"'), substr_count($html, 'class="g-case"'));
    }

    public function test_g_contact_form_has_labels_hints_a_promise_and_phone_links_and_the_same_fields(): void
    {
        config(['creatium.design' => 'g']);

        $html = $this->get('/kontakti')->assertOk()
            ->assertSee('id="g-contact-form"', false)->assertSee('id="contact-form"', false)->assertSee('id="konsultatsiya"', false)
            ->assertSee('Как да се обръщаме към вас.')->assertSee('Тук ще получите отговора.')
            ->assertSee('Ще ви отговорим до един работен ден.')->assertSee('Отговаряме до един работен ден.')
            ->assertSee('Viber')->assertSee('WhatsApp')
            ->getContent();
        foreach (['name', 'site_url', 'email', 'phone', 'message', 'consent', 'website', 'consultation'] as $field) {
            $this->assertStringContainsString('name="' . $field . '"', $html, $field);
        }
        $this->assertStringContainsString('name="interests[]"', $html);
        $this->assertMatchesRegularExpression('/<label for="name">Име и фамилия/u', $html);
        $this->assertMatchesRegularExpression('/href="tel:\+?[0-9]+"/', $html);
        // The promise comes before the button.
        $this->assertLessThan(strpos($html, 'class="g-btn g-big">Изпратете'), strpos($html, 'Ще ви отговорим до един работен ден.'));
        // The booking stays in the collapsed block.
        $this->assertMatchesRegularExpression('/<details class="g-more-fields" data-booking-details>/', $html);
    }

    public function test_g_contact_form_still_creates_the_lead_and_shows_a_thank_you_state(): void
    {
        config(['creatium.design' => 'g']);
        \Illuminate\Support\Facades\Mail::fake();

        $this->post('/kontakti', [
            'name' => 'Иван Петров', 'phone' => '0888123456', 'email' => 'ivan@example.com', 'interests' => ['website'], 'consent' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('projects', ['client_email' => 'ivan@example.com']);

        $this->withSession(['success' => 'Благодарим ви! Ще се свържем с вас.'])->get('/kontakti')->assertOk()
            ->assertSee('class="g-thanks"', false)->assertSee('Благодарим!')->assertSee('Благодарим ви! Ще се свържем с вас.')
            ->assertSee('Отговаряме до един работен ден')->assertDontSee('name="phone"', false);
    }

    public function test_g_quote_form_and_site_check_show_a_thank_you_state(): void
    {
        config(['creatium.design' => 'g']);

        $this->withSession(['quote_sent' => true])->get('/paketi')->assertOk()->assertSee('Получихме запитването ви.')->assertSee('class="g-thanks"', false);
        $this->withSession(['site_check_sent' => 'a@b.bg'])->get('/proverka-na-sait')->assertOk()->assertSee('Проверката започна')->assertSee('class="g-thanks"', false);
    }

    public function test_g_about_speaks_as_an_agency_with_method_standards_and_founders(): void
    {
        config(['creatium.design' => 'g']);

        $html = $this->get('/za-nas')->assertOk()
            ->assertSee('Дигитална агенция за бизнеси')
            ->assertSee('Как работим')->assertSee('Разговор и преглед')->assertSee('Грижа и растеж')
            ->assertSee('Нашите стандарти')->assertSee('Фиксирана цена')
            ->assertSee('Основатели')->assertSee('Борислав Костадинов')->assertSee('Владимир Цончев')
            ->getContent();
        $this->assertStringNotContainsString('двама', $html);
        $this->assertStringNotContainsString('Сайтът е готов, рекламата върви', $html, 'the personal story stays out of the g page');

        $en = $this->get('/en/about')->assertOk()->assertSee('A digital agency for businesses')->assertSee('How we work')->assertSee('Founders');
        $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', PlainCopyTest::visibleText($en->getContent()));
    }

    public function test_the_new_about_keys_exist_in_both_languages_and_in_the_admin_block(): void
    {
        foreach (['creatium', 'creatium_en'] as $file) {
            $about = config("$file.about");
            $this->assertCount(2, $about['g_intro'], $file);
            $this->assertCount(4, $about['g_method'], $file);
            $this->assertGreaterThanOrEqual(3, count($about['g_standards']), $file);
            foreach (array_merge($about['g_method'], $about['g_standards']) as $item) {
                $this->assertNotSame('', trim($item['title']));
                $this->assertNotSame('', trim($item['text']));
            }
        }
        $this->assertArrayHasKey('g_intro', app(\App\Support\Content::class)->withDefaults('about'));
    }

    public function test_g_services_keeps_the_funnel_and_the_calculator(): void
    {
        config(['creatium.design' => 'g']);

        $this->get('/uslugi')->assertOk()
            ->assertSee('data-funnel', false)->assertSee('data-funnel-canvas', false)->assertSee('data-funnel-toggle="0"', false)
            ->assertSee('id="kalkulator"', false)->assertSee('data-calc-input', false)->assertSee('data-calc-bar="leads"', false)
            ->assertSee('class="g-svc"', false)->assertSee('Какво получавате')
            ->assertDontSee('bg-white/[0.03]', false);
    }

    public function test_g_service_and_industry_pages_keep_json_ld_faq_and_the_package(): void
    {
        config(['creatium.design' => 'g']);

        $service = $this->get('/uslugi/izrabotka-na-sait')->assertOk()->assertSee('"@type":"FAQPage"', false)->assertSee('class="g-acc"', false)->assertSee('Включено в пакет')->getContent();
        $this->assertGreaterThan(0, preg_match_all('/<details class="g-acc"/', $service));
        $this->get('/izrabotka-na-sait-za-restorant')->assertOk()->assertSee('"@type":"Service"', false)->assertDontSee('data-concept-chip', false)->assertSee('Подходящ пакет');
    }

    public function test_g_legal_pages_keep_their_text_in_g_typography(): void
    {
        config(['creatium.design' => 'g']);

        $this->get('/poveritelnost')->assertOk()->assertSee('class="g-prose g-legal"', false)->assertSee('Политика за поверителност')->assertSee('Кой обработва данните ви')->assertSee('<title>', false);
        $this->get('/en/terms')->assertOk()->assertSee('class="g-prose g-legal"', false);
        $this->get('/biskvitki')->assertOk()->assertSee('<table', false)->assertSee('Строго необходими');
    }

    public function test_g_inner_pages_have_no_dashes_or_colons_in_visible_text(): void
    {
        config(['creatium.design' => 'g']);
        foreach (['/paketi', '/proekti', '/kontakti', '/uslugi', '/uslugi/izrabotka-na-sait', '/izrabotka-na-sait-za-restorant', '/za-nas', '/vaprosi', '/proverka-na-sait',
            '/en/packages', '/en/projects', '/en/contact', '/en/services', '/en/about', '/en/faq', '/en/website-check'] as $path) {
            $text = PlainCopyTest::visibleText($this->get($path)->assertOk()->getContent());
            $this->assertSame([], PlainCopyTest::offences($text), $path);
        }
    }

    public function test_english_g_pages_have_no_bulgarian_text(): void
    {
        config(['creatium.design' => 'g']);
        foreach (['/en/packages', '/en/contact', '/en/services', '/en/about', '/en/faq', '/en/website-check', '/en/privacy', '/en/terms', '/en/cookies', '/en/nope'] as $path) {
            $response = $this->get($path);
            $text = preg_replace('/[„“]/u', '', PlainCopyTest::visibleText($response->getContent()));
            // Bulgarian legal names and the Bulgarian concept sites are allowed; the interface text is not.
            $this->assertDoesNotMatchRegularExpression('/\b[А-Яа-я]{4,}\b/u', preg_replace('/Борислав|Костадинов|Владимир|Цончев|Креo/u', '', $text), $path);
        }
    }
}
