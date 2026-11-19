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
}
