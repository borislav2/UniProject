<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSitesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_demo_site_loads_marked_as_demo_and_not_indexed(): void
    {
        foreach (config('creatium.demos') as $demo) {
            $this->get('/demo/' . $demo['slug'])
                ->assertOk()
                ->assertSee('<meta name="robots" content="noindex">', false)
                ->assertSee('Примерен сайт от Creatium Lab. Бизнесът е измислен.')
                ->assertSee('data-demo-form', false);
        }
        $this->assertCount(6, config('creatium.demos'));
    }

    public function test_unknown_demo_is_404(): void
    {
        $this->get('/demo/nyama-takova')->assertNotFound();
    }

    public function test_projects_page_lists_the_demos_with_a_demo_label(): void
    {
        $page = $this->get('/proekti')->assertOk()->assertSee('Демо проекти')->assertDontSee('Тук скоро ще има проекти');

        foreach (config('creatium.demos') as $demo) {
            $page->assertSee($demo['name'])->assertSee(route('demo.show', $demo['slug']), false)->assertSee('images/demos/' . $demo['slug'] . '.webp', false);
            $this->assertFileExists(public_path('images/demos/' . $demo['slug'] . '.webp'));
        }

        $this->get('/en/projects')->assertOk()->assertSee('Demo projects')->assertSee('Open the demo')->assertSee('Lozata Restaurant')->assertSee('The demos are in Bulgarian.');
    }

    public function test_demos_are_not_in_the_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/demo/', false);
    }
}
