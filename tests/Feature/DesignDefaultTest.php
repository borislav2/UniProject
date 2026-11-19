<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Design G is the main public design since 19.11.2026: the config default is g, CREATIUM_DESIGN=current brings the old one back. */
class DesignDefaultTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_config_default_is_design_g(): void
    {
        $this->assertStringContainsString("'design' => env('CREATIUM_DESIGN', 'g')", file_get_contents(config_path('creatium.php')));
    }

    public function test_with_design_g_the_home_is_the_g_home_without_the_preview_pill(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('design-g', $html);
        $this->assertStringNotContainsString('<meta name="robots" content="noindex">', $html);
    }

    public function test_the_g_favicon_is_linked_and_present(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('favicon.svg', $html);
        $this->assertStringContainsString('favicon.ico', $html);
        $svg = file_get_contents(public_path('favicon.svg'));
        $this->assertStringContainsString('#0C0E12', $svg);
        $this->assertStringContainsString('#E8431B', $svg);
        foreach (['favicon.ico', 'favicon-64.png', 'apple-touch-icon.png', 'images/icon-192.png', 'images/icon-512.png'] as $file) {
            $this->assertFileExists(public_path($file));
        }
    }
}
