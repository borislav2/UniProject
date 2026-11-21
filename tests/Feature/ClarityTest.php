<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Microsoft Clarity is in the code but loads only after consent for analytics cookies (21.11.2026). */
class ClarityTest extends TestCase
{
    use RefreshDatabase;

    public function test_clarity_is_consent_gated_and_present_with_gtm(): void
    {
        config(['creatium.gtm_id' => 'GTM-TEST123', 'creatium.clarity_id' => 'z4gl536rzv']);
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            $html = $this->get('/')->assertOk()->getContent();
            $this->assertStringContainsString('window.creatiumClarity = function', $html, $design);
            $this->assertStringContainsString('"z4gl536rzv"', $html, $design);
            $this->assertStringContainsString('c.analytics', $html, $design);
            $this->assertStringContainsString('if (analytics && window.creatiumClarity)', $html, $design);
            // The loader is only defined, never called unconditionally.
            $this->assertDoesNotMatchRegularExpression('/\}\)\(\);\s*window\.creatiumClarity\(\);/', $html);
        }
    }

    public function test_no_clarity_without_id_or_without_gtm(): void
    {
        config(['creatium.gtm_id' => 'GTM-TEST123', 'creatium.clarity_id' => null]);
        $this->assertStringNotContainsString('clarity.ms', $this->get('/')->getContent());
        config(['creatium.gtm_id' => null, 'creatium.clarity_id' => 'z4gl536rzv']);
        $this->assertStringNotContainsString('clarity.ms', $this->get('/')->getContent());
    }
}
