<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Design G home, the scroll opening (resources/js/g-scroll.js, resources/css/g-scroll.css): server-rendered headline, four steps from
 * home_hero.g_story, both buttons, the same markup in English, and no change for the current design.
 */
class GScrollHeroTest extends TestCase
{
    use RefreshDatabase;

    private function home(string $url, string $design = 'g'): string
    {
        config(['creatium.design' => $design]);

        return $this->get($url)->assertOk()->getContent();
    }

    public function test_bulgarian_home_has_the_headline_as_the_only_h1_the_four_steps_and_both_links(): void
    {
        $html = $this->home('/');

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertMatchesRegularExpression('/<h1 class="g-h">Дигитални решения, изградени с мисъл за вашия <em>успех<\/em>\.<\/h1>/u', $html);
        foreach (['Клиентът ви търси', 'Намира точно вас', 'Харесва това, което вижда', 'Пише ви'] as $i => $title) {
            $this->assertStringContainsString('<span class="g-mono">0' . ($i + 1) . '</span><h2>' . $title . '</h2>', $html);
        }
        $this->assertStringContainsString('Хората търсят услугата ви в Google, в Google Maps и вече в ChatGPT.', $html);
        $this->assertStringContainsString('счетоводна кантора София', $html);
        $this->assertStringContainsString('Скролнете', $html);
        $this->assertStringContainsString('href="#kontakt">Изпратете запитване', $html);
        $this->assertMatchesRegularExpression('~href="[^"]*/proverka-na-sait">Проверете сайта си~u', $html);
        $this->assertStringContainsString("resources/js/g-scroll.js", file_get_contents(resource_path('views/g/home.blade.php')));
        // The headline is plain text in the first paint, the old canvas is gone.
        $this->assertStringNotContainsString('g-topo', $html);
        $this->assertStringNotContainsString('data-g-clear', $html);
        $this->assertStringContainsString('aria-hidden="true"><div class="g-sc-cam">', $html);
    }

    public function test_english_home_uses_the_english_story(): void
    {
        $html = $this->home('/en');

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        foreach (['Your customer is searching', 'They find you', 'They like what they see', 'They write to you'] as $title) {
            $this->assertStringContainsString('<h2>' . $title . '</h2>', $html);
        }
        $this->assertStringContainsString('accountant Sofia', $html);
        $this->assertStringContainsString('>Scroll<', $html);
        $this->assertMatchesRegularExpression('~href="[^"]*/en/website-check">Check your website~', $html);
        $this->assertStringNotContainsString('Клиентът ви търси', $html);
    }

    public function test_an_admin_edit_of_the_story_is_shown_and_an_old_save_still_gets_the_default_steps(): void
    {
        \App\Models\ContentBlock::create(['key' => 'home_hero', 'locale' => 'bg', 'value' => ['title' => 'Стар заглавие', 'highlight' => 'Стар текст']]);
        app(\App\Support\Content::class)->flush();
        $html = $this->home('/');
        $this->assertStringContainsString('<h2>Клиентът ви търси</h2>', $html);
        $this->assertStringContainsString('Стар заглавие', $html);
    }

    public function test_the_current_design_home_is_unchanged(): void
    {
        $html = $this->home('/', 'current');
        foreach (['g-sc', 'data-g-scene', 'g-scroll'] as $marker) {
            $this->assertStringNotContainsString($marker, $html);
        }
        $this->assertStringNotContainsString('Клиентът ви търси', $html);
    }

    public function test_the_script_and_styles_are_small_plain_and_free_of_libraries(): void
    {
        $js = file_get_contents(resource_path('js/g-scroll.js'));
        $this->assertLessThan(14000, strlen($js));
        $this->assertStringNotContainsString('import ', $js);
        $this->assertStringContainsString('{ passive: true }', $js);
        $this->assertStringContainsString("'./g-scroll.css'", file_get_contents(resource_path('css/g-lean.css')));
        $this->assertStringNotContainsString('g-home.js', file_get_contents(base_path('vite.config.js')));
        $this->assertStringContainsString('resources/js/g-scroll.js', file_get_contents(base_path('vite.config.js')));
    }
}
