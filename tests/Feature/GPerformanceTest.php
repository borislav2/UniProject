<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Design G, first-load performance (Lighthouse mobile, Slow 4G): no forced reflow from our startup scripts, every font of the first screen
 * preloaded. No visible changes. (The cookie banner stays at the end of the page: moving it up showed it about 100 ms earlier, not worth a new tab order.)
 */
class GPerformanceTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(base_path($path));
    }

    public function test_every_font_of_the_first_screen_is_preloaded_with_its_built_url(): void
    {
        $layout = $this->source('resources/views/layouts/public.blade.php');
        $css = $this->source('resources/css/g.css');
        preg_match_all("~url\('\.\./fonts/g/([a-z0-9-]+)\.woff2'\)~", $css, $m);

        $this->assertCount(6, array_unique($m[1]));
        foreach (array_unique($m[1]) as $file) {
            $this->assertStringContainsString("'" . $file . "'", $layout, $file . ' is declared in g.css and must be preloaded');
        }
        // The URL comes from the Vite manifest (hashed), as a font with crossorigin, never a hand-written path.
        $this->assertStringContainsString("Vite::asset('resources/fonts/g/' . \$file . '.woff2')", $layout);
        $this->assertStringContainsString('rel="preload" href="{{ $font }}" as="font" type="font/woff2" crossorigin', $layout);
    }

    public function test_the_phone_package_row_is_positioned_without_a_layout_read_at_startup(): void
    {
        $js = $this->source('resources/js/g-bar.js');
        $block = substr($js, strpos($js, "document.querySelectorAll('.g-plans')"), 900);

        $this->assertStringContainsString('new ResizeObserver(', $block);
        // The geometry is read inside the observer callback (after the browser's layout), not in the loop body that runs while the page starts.
        $this->assertMatchesRegularExpression('~const center = \(\) => \{.*scrollWidth.*offsetLeft.*\};\s*if \(\'ResizeObserver\' in window\)~s', $block);
    }

    public function test_the_globe_measures_nothing_while_the_page_starts(): void
    {
        $js = $this->source('resources/js/g-3d.js');
        $globe = substr($js, strpos($js, 'function globe()'));

        $this->assertStringNotContainsString('window.innerWidth', $globe);
        $this->assertStringContainsString("mq('(max-width: 759px)')", $globe);
        // getComputedStyle and clientWidth sit in build() and size(), which start() calls from the first IntersectionObserver callback.
        $this->assertStringContainsString('const near = new IntersectionObserver', $globe);
        $this->assertLessThan(strpos($globe, 'const near = new IntersectionObserver'), strpos($globe, 'const start = () => {'));
        $this->assertLessThan(strpos($globe, 'const start = () => {'), strpos($globe, 'getComputedStyle(band)'));
        $this->assertLessThan(strpos($globe, 'const start = () => {'), strpos($globe, 'cv.clientWidth'));
        $this->assertStringContainsString('if (!live.length) return;', $js);
        // The set-up call for the globe itself only registers the observer.
        $this->assertDoesNotMatchRegularExpression('~\nglobe\(\);\n.*getComputedStyle~s', $js);
    }

    public function test_the_kreo_story_probes_webgl_only_when_the_scene_could_start(): void
    {
        $js = $this->source('resources/js/g-kreo.js');

        $this->assertStringContainsString('const hasGl = () => {', $js);
        $this->assertStringContainsString('wide.matches && !calm.matches && !broken && hasGl()', $js);
        // The 3D chunk stays a dynamic import.
        $this->assertStringContainsString("await import('./kreo/stage.js')", $js);
        $this->assertStringNotContainsString("import { mount }", explode('async function load', $js)[0]);
    }
}
