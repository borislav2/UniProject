<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Design G, UX round (17.11.2026): package cards slide sideways on a phone, a link to the /uslugi panel, the „За нас“ dropdown with correct
 * current marks, a next step after a post, a skip link and aria-hidden panel numbers. The current design stays as it was.
 */
class GUxTest extends TestCase
{
    use RefreshDatabase;

    private function g(): void
    {
        config(['creatium.design' => 'g']);
    }

    /** The header's desktop nav, as one string, without the phone menu. */
    private function nav(string $html): string
    {
        $start = strpos($html, 'class="g-nav"');
        $this->assertNotFalse($start);

        return substr($html, $start, strpos($html, 'class="g-tools"') - $start);
    }

    /** The label of every item of the nav that carries aria-current="page". */
    private function current(string $html): array
    {
        preg_match_all('/<a [^>]*aria-current="page"[^>]*>([^<]+)<\/a>/u', $this->nav($html), $m);

        return array_map('trim', $m[1]);
    }

    public function test_plan_cards_are_a_swipeable_row_on_a_phone_and_keep_the_switch(): void
    {
        $this->g();
        foreach (['/', '/paketi'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertSame(3, substr_count($html, '<article id="paket-'), $path);
            $this->assertStringContainsString('data-g-billing-root', $html, $path);
            $this->assertStringContainsString('data-b="y"', $html, $path);
        }

        $css = file_get_contents(resource_path('css/g.css'));
        $this->assertMatchesRegularExpression('/@media \(max-width: 759px\)\s*\{[^}]*\.g-plans \{[^}]*scroll-snap-type: x mandatory/s', $css);
        $this->assertStringContainsString('scroll-snap-align: center', $css);
        // The reading order is the markup order: no CSS order or reversed flex.
        $this->assertStringNotContainsString('row-reverse', $css);
    }

    public function test_services_page_links_to_the_panel_with_its_own_heading(): void
    {
        $this->g();
        $html = $this->get('/uslugi')->assertOk()->getContent();

        $this->assertStringContainsString('id="sistema"', $html);
        $this->assertMatchesRegularExpression('/<a class="g-link g-jump" href="#sistema"><span>Проверете как работи системата ви в момента<\/span>/u', $html);
        // The link is in the page head, before the first service.
        $this->assertLessThan(strpos($html, 'data-g-service='), strpos($html, 'href="#sistema"'));
        // Moved nowhere: the panel is still after the services.
        $this->assertGreaterThan(strpos($html, 'data-g-service='), strpos($html, 'id="sistema"'));

        $this->get('/en/services')->assertOk()->assertSee('href="#sistema"', false);
    }

    public function test_the_panel_numbers_are_hidden_from_screen_readers(): void
    {
        $this->g();
        $html = $this->get('/uslugi')->assertOk()->getContent();

        $this->assertSame(7, substr_count($html, 'class="g-sw-n g-mono" aria-hidden="true"'));
        $this->assertStringNotContainsString('class="g-sw-n g-mono">', $html);
    }

    public function test_about_us_is_a_dropdown_with_who_news_and_questions(): void
    {
        $this->g();
        $html = $this->get('/kontakti')->assertOk()->getContent();
        $nav = $this->nav($html);

        $this->assertSame(2, substr_count($nav, 'data-g-dd'));
        $this->assertStringContainsString('id="g-dd-about"', $nav);
        $about = substr($nav, strpos($nav, 'id="g-dd-about"'));
        foreach ([lroute('about'), lroute('blog.index'), lroute('faq')] as $href) {
            $this->assertStringContainsString('href="' . $href . '"', $about);
        }
        // The services dropdown ends with the free check.
        $services = substr($nav, strpos($nav, 'id="g-dd-services"'), strpos($nav, 'id="g-dd-about-link"') - strpos($nav, 'id="g-dd-services"'));
        $this->assertStringContainsString('href="' . lroute('site-check') . '"', $services);
        $this->assertGreaterThan(strpos($services, 'href="' . lroute('services') . '/'), strpos($services, 'href="' . lroute('site-check') . '"'));
        $this->assertSame([], $this->current($html));

        // The phone menu lists the items too.
        $mobile = substr($html, strpos($html, 'id="mobile-menu"'));
        $this->assertStringContainsString('href="' . lroute('blog.index') . '"', $mobile);
        $this->assertStringContainsString('href="' . lroute('faq') . '"', $mobile);
        $this->assertStringContainsString('href="' . lroute('site-check') . '"', $mobile);
    }

    public function test_the_menu_marks_the_right_current_item(): void
    {
        $this->g();
        PostCategory::create(['name' => ['bg' => 'Сайтове'], 'slug' => ['bg' => 'sajtove']]);
        Post::create(['title' => ['bg' => 'Първа статия'], 'slug' => ['bg' => 'parva'], 'body' => ['bg' => 'Текст.'], 'status' => 'published', 'published_at' => now()->subDay()]);

        // Parent and the item itself are current on /blog, /vaprosi and /za-nas.
        $this->assertSame(['За нас', 'Новини'], $this->current($this->get('/blog')->assertOk()->getContent()));
        $this->assertSame(['За нас', 'Новини'], $this->current($this->get('/blog/parva')->assertOk()->getContent()));
        $this->assertSame(['За нас', 'Често задавани въпроси'], $this->current($this->get('/vaprosi')->assertOk()->getContent()));
        $this->assertSame(['За нас', 'Кои сме ние'], $this->current($this->get('/za-nas')->assertOk()->getContent()));
        // The free check is an item of Услуги.
        $this->assertSame(['Услуги', 'Безплатна проверка на сайт'], $this->current($this->get('/proverka-na-sait')->assertOk()->getContent()));
        $this->assertSame(['Услуги'], $this->current($this->get('/uslugi')->assertOk()->getContent()));
        $this->assertSame(['Пакети'], $this->current($this->get('/paketi')->assertOk()->getContent()));
    }

    public function test_news_is_left_out_of_the_menu_when_the_blog_is_off(): void
    {
        $this->g();
        config(['creatium.blog_enabled' => false]);
        $html = $this->get('/za-nas')->assertOk()->getContent();

        $this->assertStringNotContainsString('href="' . lroute('blog.index') . '"', $html);
        $this->assertStringContainsString('href="' . lroute('faq') . '"', $this->nav($html));
    }

    public function test_the_english_menu_has_the_same_dropdowns(): void
    {
        $this->g();
        $nav = $this->nav($this->get('/en/faq')->assertOk()->getContent());

        $this->assertStringContainsString('id="g-dd-about"', $nav);
        $this->assertStringContainsString('Frequently asked questions', $nav);
        $this->assertStringContainsString('Free website check', $nav);
    }

    public function test_a_post_ends_with_a_next_step(): void
    {
        $this->g();
        Post::create(['title' => ['bg' => 'Първа статия'], 'slug' => ['bg' => 'parva'], 'body' => ['bg' => 'Текст.'], 'status' => 'published', 'published_at' => now()->subDay()]);
        $html = $this->get('/blog/parva')->assertOk()->getContent();

        $this->assertStringContainsString('class="g-closer"', $html);
        $closer = substr($html, strpos($html, 'class="g-closer"'));
        $this->assertStringContainsString(lroute('contact') . '#konsultatsiya', $closer);
        $this->assertStringContainsString('href="' . lroute('site-check') . '"', substr($closer, 0, strpos($closer, '</section>')));
        // After the article, before the footer.
        $this->assertGreaterThan(strpos($html, 'class="g-prose g-article"'), strpos($html, 'class="g-closer"'));
    }

    public function test_the_skip_link_is_the_first_focusable_element_in_g_only(): void
    {
        $this->g();
        $html = $this->get('/paketi')->assertOk()->getContent();

        $this->assertStringContainsString('<a class="g-skip" href="#main">Към съдържанието</a>', $html);
        $this->assertStringContainsString('<main id="main" tabindex="-1">', $html);
        $body = substr($html, strpos($html, '<body'));
        $this->assertLessThan(strpos($body, '<a ', strpos($body, 'g-skip') + 10), strpos($body, 'g-skip'));
        $this->assertSame(strpos($body, '<a '), strpos($body, '<a class="g-skip"'));

        $this->get('/en/packages')->assertOk()->assertSee('Skip to content');
    }

    public function test_the_current_design_has_no_skip_link_and_no_main_id(): void
    {
        foreach (['/', '/paketi', '/uslugi', '/blog'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringNotContainsString('g-skip', $html, $path);
            $this->assertStringNotContainsString('id="main"', $html, $path);
            $this->assertStringNotContainsString('g-dd-about', $html, $path);
            $this->assertStringContainsString('<main>', $html, $path);
        }
    }

    public function test_the_panel_button_invites_a_talk_at_six_and_seven_of_seven(): void
    {
        $this->assertFalse(\App\Support\GPanel::state([1, 2, 3, 4, 5])['nearly_full']);
        $this->assertTrue(\App\Support\GPanel::state([1, 2, 3, 4, 5, 6])['nearly_full']);
        $this->assertTrue(\App\Support\GPanel::state([1, 2, 3, 4, 5, 6, 7])['nearly_full']);

        config(['creatium.design' => 'g']);
        $this->get('/uslugi')->assertOk()
            ->assertSee('data-label-full="Поговорете с нас за фина настройка"', false)
            ->assertSee('Вижте как да допълним липсващото');
        $this->get('/en/services')->assertOk()->assertSee('data-label-full="Talk to us about fine tuning"', false);
        $this->assertStringContainsString('on.length >= total - 1 ? cta.dataset.labelFull', file_get_contents(resource_path('js/g-bar.js')));
    }

    public function test_services_and_service_pages_have_a_dark_head_in_g_only(): void
    {
        config(['creatium.design' => 'g']);
        foreach (['/uslugi', '/uslugi/izrabotka-na-sait', '/uslugi/seo-i-ai-vidimost', '/en/services'] as $path) {
            $this->assertStringContainsString('g-ph-dark', $this->get($path)->assertOk()->getContent(), $path);
        }
        foreach (['/paketi', '/za-nas', '/kontakti'] as $path) {
            $this->assertStringNotContainsString('g-ph-dark', $this->get($path)->assertOk()->getContent(), $path);
        }
        config(['creatium.design' => 'current']);
        $this->assertStringNotContainsString('g-ph-dark', $this->get('/uslugi')->getContent());
    }
}
