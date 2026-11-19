<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Design G home, the 3D touches: laptops in #proekti, tilted packages in #paketi and the globe in #tsel (resources/js/g-3d.js, resources/css/g-3d.css).
 * They exist on the G home only; /proekti, /paketi and the current design stay as they were, and so does the hero.
 */
class GHome3dTest extends TestCase
{
    use RefreshDatabase;

    private function home(string $design = 'g'): string
    {
        config(['creatium.design' => $design]);

        return $this->get('/')->assertOk()->getContent();
    }

    private function section(string $html, string $open): string
    {
        $start = strpos($html, $open);
        $this->assertNotFalse($start, $open . ' is missing');
        $end = strpos($html, '</section>', $start);

        return substr($html, $start, $end - $start);
    }

    public function test_home_loads_the_3d_script_with_the_other_home_scripts(): void
    {
        $view = file_get_contents(resource_path('views/g/home.blade.php'));
        $this->assertStringContainsString("@vite(['resources/js/g-home.js', 'resources/js/g-3d.js'])", $view);
        $this->assertStringContainsString('resources/js/g-3d.js', file_get_contents(base_path('vite.config.js')));
        $this->assertStringContainsString('./g-3d.css', file_get_contents(resource_path('css/g-lean.css')));
        // Small, dependency free and not a bundle of its own libraries.
        $js = file_get_contents(resource_path('js/g-3d.js'));
        $this->assertLessThan(12000, strlen($js));
        $this->assertDoesNotMatchRegularExpression('/^\s*import\s/m', $js);
    }

    public function test_g_home_projects_are_laptops(): void
    {
        $html = $this->home();
        $projects = $this->section($html, 'id="proekti"');
        $this->assertStringContainsString('g-laptops', $projects);
        $this->assertStringContainsString('data-g-laptops', $projects);
        $this->assertSame(9, substr_count($projects, '<article class="g-card'));
        $this->assertSame(9, substr_count($projects, 'class="g-frame"'));
        // The images keep their responsive and lazy attributes.
        $this->assertStringContainsString('srcset="', $projects);
        $this->assertStringContainsString('loading="lazy"', $projects);
        $this->assertStringContainsString('width="960" height="600"', $projects);
    }

    public function test_g_home_packages_are_tilted_and_the_globe_is_in_the_goal_band(): void
    {
        $html = $this->home();
        $packages = $this->section($html, 'id="paketi"');
        $this->assertStringContainsString('class="g-plans" data-g-tilt', $packages);

        $goal = $this->section($html, 'id="tsel"');
        $this->assertSame(1, substr_count($goal, '<canvas class="g-globe" data-g-globe aria-hidden="true"></canvas>'));
        $this->assertSame(1, substr_count($html, 'data-g-globe'));
    }

    public function test_packages_and_portfolio_pages_do_not_get_the_home_only_tilt(): void
    {
        config(['creatium.design' => 'g']);
        foreach (['/paketi', '/proekti'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringNotContainsString('data-g-tilt', $html, $path);
            $this->assertStringNotContainsString('g-laptops', $html, $path);
            $this->assertStringNotContainsString('data-g-globe', $html, $path);
        }
        // /paketi keeps the exact markup of the shared plan cards.
        $this->assertStringContainsString('<div class="g-plans">', $this->get('/paketi')->getContent());
    }

    public function test_current_design_home_has_none_of_it(): void
    {
        $html = $this->home('current');
        foreach (['g-laptops', 'data-g-laptops', 'data-g-tilt', 'data-g-globe', 'g-globe', 'g-3d'] as $marker) {
            $this->assertStringNotContainsString($marker, $html, $marker);
        }
    }

    public function test_the_hero_markup_is_unchanged(): void
    {
        $hero = $this->section($this->home(), '<section class="g-hero"') . '</section>';
        // The structure with every text and link value removed: it must stay exactly as it was before the 3D work.
        $shape = preg_replace('/\s+/', ' ', preg_replace(['/>[^<]+</u', '/(aria-label|href)="[^"]*"/u'], ['><', '$1=""'], $hero));
        $this->assertSame(
            '<section class="g-hero" aria-label=""><canvas id="g-topo" aria-hidden="true"></canvas><div class="g-wrap"><h1 class="g-h" data-g-clear><em></em></h1>'
            . '<p class="g-lead" data-g-clear></p><div class="g-cta"><a class="g-btn" data-g-clear href=""><i></i></a><a class="g-btn g-ghost" data-g-clear href=""></a></div>'
            . '<ul class="g-trust" data-g-clear><li></li><li></li><li></li></ul></div></section>',
            trim($shape)
        );
        foreach (['g-laptops', 'g-globe', 'data-g-tilt'] as $marker) {
            $this->assertStringNotContainsString($marker, $hero);
        }
    }
}
