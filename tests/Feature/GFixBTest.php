<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Design G, fix round B (look only): theme-color follows light and dark in G only, related links stay as tiles with an arrow. */
class GFixBTest extends TestCase
{
    use RefreshDatabase;

    public function test_design_g_has_two_theme_color_metas(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/paketi')->assertOk()->getContent();
        $this->assertStringContainsString('<meta name="theme-color" content="#ECEEF0" media="(prefers-color-scheme: light)">', $html);
        $this->assertStringContainsString('<meta name="theme-color" content="#0B0C0F" media="(prefers-color-scheme: dark)">', $html);
        $this->assertStringContainsString("querySelectorAll('meta[name=\"theme-color\"]')", $html);
    }

    public function test_current_design_keeps_its_single_theme_color(): void
    {
        $html = $this->get('/paketi')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'name="theme-color"'));
        $this->assertStringContainsString('<meta name="theme-color" content="#0f1a2b">', $html);
        $this->assertStringNotContainsString('prefers-color-scheme: light)">', $html);
    }

    public function test_service_and_industry_pages_keep_their_related_links_with_an_arrow(): void
    {
        config(['creatium.design' => 'g']);
        $service = $this->get('/uslugi/izrabotka-na-sait')->assertOk()->getContent();
        $this->assertStringContainsString('/izrabotka-na-sait-za-restorant', $service);
        $this->assertStringContainsString('/uslugi/google-profil-i-otzivi', $service);
        $this->assertGreaterThanOrEqual(8, substr_count($service, 'g-ar"'));

        $industry = $this->get('/izrabotka-na-sait-za-avtoservis')->assertOk()->getContent();
        $this->assertStringContainsString('/izrabotka-na-sait-za-restorant', $industry);
        $this->assertStringContainsString('/uslugi/izrabotka-na-sait', $industry);
        $this->assertStringContainsString('g-ar', $industry);
    }
}
