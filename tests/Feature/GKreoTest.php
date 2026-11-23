<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Design G home, „Как работим“ (the Kreo story) and the AI agents team image: the four website steps from the config in both languages, stills with
 * real sizes, an aria-hidden stage, a lazily imported 3D script, no new copy, and nothing at all in the current design.
 */
class GKreoTest extends TestCase
{
    use RefreshDatabase;

    private function g(): void
    {
        config(['creatium.design' => 'g']);
    }

    private function section(string $html): string
    {
        $this->assertSame(1, preg_match('#<section class="g-s g-kreo".*?</section>#s', $html, $m), 'the Kreo section is on the page');

        return $m[0];
    }

    /** @return list<array{title: string, text: string}> */
    private function steps(string $lang): array
    {
        return config($lang === 'en' ? 'creatium_en.service_pages.website.g_process' : 'creatium.service_pages.website.g_process');
    }

    public function test_the_g_home_has_the_section_after_the_projects_with_the_four_steps_in_both_languages(): void
    {
        $this->g();

        foreach (['bg' => '/', 'en' => '/en'] as $lang => $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $projects = strpos($html, 'id="proekti"');
            $section = strpos($html, 'id="kak-rabotim"');
            $packages = strpos($html, 'id="paketi"');
            $this->assertNotFalse($projects);
            $this->assertTrue($projects < $section && $section < $packages, "$lang: projects, then the story, then the packages");

            $block = $this->section($html);
            $key = $lang === 'en' ? 'creatium_en' : 'creatium';
            $this->assertStringContainsString(e(config("$key.service_pages.website.g_process_title")), $block);
            $this->assertSame(4, substr_count($block, 'data-g-step'));
            $this->assertSame(1, preg_match('#<ol class="g-kreo-steps">.*?</ol>#s', $block), 'the steps are a real ordered list');
            foreach ($this->steps($lang) as $step) {
                $this->assertStringContainsString('<h3>' . e($step['title']) . '</h3>', $block);
                $this->assertStringContainsString('<p>' . e($step['text']) . '</p>', $block);
            }
        }
    }

    public function test_the_stills_exist_are_small_and_are_referenced_with_width_and_height(): void
    {
        $this->g();
        $names = [];
        foreach (['kreo-1-bg', 'kreo-1-en', 'kreo-2', 'kreo-3', 'kreo-4'] as $base) {
            foreach (['960', '480'] as $size) {
                foreach (['', '-dark'] as $theme) {
                    $names[] = "$base-$size$theme.webp";
                }
            }
        }
        foreach (['team-bg', 'team-en'] as $base) {
            foreach (['1200', '600'] as $size) {
                foreach (['', '-dark'] as $theme) {
                    $names[] = "$base-$size$theme.webp";
                }
            }
        }
        foreach ($names as $name) {
            $file = public_path('images/kreo/' . $name);
            $this->assertFileExists($file);
            $this->assertLessThan(70 * 1024, filesize($file), "$name is under 70 KB");
            $this->assertSame('RIFF', substr(file_get_contents($file, false, null, 0, 4), 0, 4));
        }

        foreach (['bg' => ['/', 'kreo-1-bg'], 'en' => ['/en', 'kreo-1-en']] as [$path, $first]) {
            $block = $this->section($this->get($path)->getContent());
            $this->assertSame(0, preg_match('#<img(?![^>]*\swidth="\d+"[^>]*\sheight="\d+")[^>]*>#', $block), 'every still has width and height');
            foreach ([$first, 'kreo-2', 'kreo-3', 'kreo-4'] as $base) {
                $this->assertMatchesRegularExpression('#<img class="g-kreo-light" src="[^"]*/images/kreo/' . $base . '-960\.webp\?v=\d+" srcset="[^"]*' . $base . '-480\.webp\?v=\d+ 480w, [^"]*' . $base . '-960\.webp\?v=\d+ 960w"[^>]*alt="[^"]+" width="960" height="720"#', $block, $base);
                $this->assertStringContainsString($base . '-960-dark.webp', $block);
            }
            $this->assertStringNotContainsString('<canvas', $block, 'the canvas is made by the script, never by the page');
        }
    }

    public function test_the_stage_is_hidden_from_assistive_technology_and_the_script_is_lazy(): void
    {
        $this->g();
        $block = $this->section($this->get('/')->getContent());
        $this->assertMatchesRegularExpression('#<div class="g-kreo-stage" data-g-kreo-stage aria-hidden="true">#', $block);
        $this->assertStringNotContainsString('<script', $block, 'no script inside the section');
        $this->assertStringNotContainsString('aria-hidden="true"><ol', $block);

        $page = $this->get('/')->getContent();
        $this->assertDoesNotMatchRegularExpression('#<script(?![^>]*type="module")[^>]*(three|kreo)#i', $page, 'no blocking script for the 3D code');

        $loader = file_get_contents(base_path('resources/js/g-kreo.js'));
        $this->assertStringContainsString("import('./kreo/stage.js')", $loader, 'the 3D code is a dynamic import');
        $this->assertStringNotContainsString("from 'three", $loader, 'the loader itself never imports three');
        $this->assertStringContainsString('IntersectionObserver', $loader);
        $this->assertStringContainsString("rootMargin: '100% 0px'", $loader, 'loaded within about one screen');
        $this->assertStringContainsString('(prefers-reduced-motion: reduce)', $loader);
        $this->assertStringContainsString('(min-width: 1024px)', $loader);
        $this->assertStringContainsString("getContext('webgl2')", $loader, 'no WebGL2 means the stills stay');

        $vite = file_get_contents(base_path('vite.config.js'));
        $this->assertStringContainsString("'resources/js/g-kreo.js'", $vite);
        $this->assertSame('0.169.0', json_decode(file_get_contents(base_path('package.json')), true)['dependencies']['three'], 'three is pinned to an exact version');

        // Tree shaking: named imports only, never the whole library.
        foreach (glob(base_path('resources/js/kreo/*.js')) as $file) {
            $this->assertDoesNotMatchRegularExpression('#import \* as \w+ from \'three\'#', file_get_contents($file), basename($file));
        }
        $stage = file_get_contents(base_path('resources/js/kreo/stage.js'));
        $this->assertStringContainsString('document.hidden', $stage, 'it pauses in a hidden tab');
        $this->assertStringContainsString("'pagehide'", $loader . $stage, 'it is disposed on pagehide');
        $this->assertStringContainsString('Math.min(window.devicePixelRatio || 1, 1.5)', $stage);
    }

    public function test_no_new_copy_no_cyrillic_on_en_and_the_copy_rule(): void
    {
        $this->g();
        foreach (['/', '/en'] as $path) {
            $block = $this->section($this->get($path)->getContent());
            $text = PlainCopyTest::visibleText($block);
            $this->assertSame([], PlainCopyTest::offences($text), "$path: no dashes or colons");
            // Only the heading, the numbers and the four steps: nothing else is written in the section.
            $lang = $path === '/en' ? 'en' : 'bg';
            $expected = [config(($lang === 'en' ? 'creatium_en' : 'creatium') . '.service_pages.website.g_process_title')];
            foreach ($this->steps($lang) as $i => $step) {
                array_push($expected, '0' . ($i + 1), $step['title'], $step['text']);
            }
            $this->assertSame(preg_replace('/\s+/u', ' ', implode(' ', $expected)), trim($text), "$path: the section says only what the config says");
        }
        $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', PlainCopyTest::visibleText($this->section($this->get('/en')->getContent())));
    }

    public function test_the_ai_agents_page_shows_the_team_image_and_the_current_design_has_nothing(): void
    {
        $this->g();
        foreach (['bg', 'en'] as $lang) {
            $html = $this->get(PageController::serviceUrl('ai_agents', $lang))->assertOk()->getContent();
            $this->assertMatchesRegularExpression('#<figure class="g-svc-fig g-svc-team">\s*<img class="g-kreo-light" src="[^"]*/images/kreo/team-' . $lang . '-1200\.webp\?v=\d+" srcset="[^"]*team-' . $lang . '-600\.webp\?v=\d+ 600w, [^"]*team-' . $lang . '-1200\.webp\?v=\d+ 1200w"[^>]*alt="[^"]+" width="1200" height="720"#', $html);
            $this->assertStringContainsString('team-' . $lang . '-1200-dark.webp', $html);
            $this->assertStringNotContainsString('data-g-kreo', $html, 'no WebGL on the AI agents page');
            // The department names on the plates are the ones of the map on the same page.
            $this->assertStringContainsString(config(($lang === 'en' ? 'creatium_en' : 'creatium') . '.service_pages.ai_agents.g_vis.v2.modules.0.name'), $html);
        }

        config(['creatium.design' => 'current']);
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('kak-rabotim', $home);
        $this->assertStringNotContainsString('g-kreo', $home);
        $this->assertStringNotContainsString('images/kreo', $home);
    }
}
