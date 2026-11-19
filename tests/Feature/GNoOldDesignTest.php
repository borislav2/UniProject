<?php

namespace Tests\Feature;

use App\Models\OutreachProspect;
use App\Support\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Design G is the live design: nothing a visitor, a client or the team sees may still carry the old look
 * (navy #0f1a2b, blue gradients, Manrope, the old logo files, the old share image). The old design stays available
 * with CREATIUM_DESIGN=current, so these tests set G explicitly.
 */
class GNoOldDesignTest extends TestCase
{
    use RefreshDatabase;

    private const OLD = ['#0f1a2b', '0f1a2b', 'Manrope', 'manrope', 'images/logo.png', 'images/logo.webp', 'images/logo-white', 'og-image.png', 'bg-brand-gradient', 'from-brand-700', 'rgb(15 26 43', 'rgb(15_26_43', 'rgb(45_99_163'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['creatium.design' => 'g', 'creatium.gtm_id' => 'GTM-TEST123']);
    }

    private function assertNoOldLook(string $html, string $where): void
    {
        foreach (self::OLD as $needle) {
            $this->assertStringNotContainsString($needle, $html, "$where still contains $needle");
        }
    }

    private function meta(string $html, string $attr, string $name): ?string
    {
        return preg_match('/<meta ' . $attr . '="' . preg_quote($name, '/') . '" content="([^"]*)"/', $html, $m) ? html_entity_decode($m[1]) : null;
    }

    public function test_g_pages_carry_none_of_the_old_look(): void
    {
        foreach (['/', '/en', '/paketi', '/uslugi', '/kontakti', '/proekti', '/za-nas', '/vaprosi', '/proverka-na-sait', '/en/packages', '/blog', '/poveritelnost'] as $url) {
            $response = $this->get($url);
            $this->assertSame(200, $response->getStatusCode(), $url);
            $this->assertNoOldLook($response->getContent(), $url);
            $this->assertStringContainsString('content="#ECEEF0"', $response->getContent());
            $this->assertStringNotContainsString('content="#0f1a2b"', $response->getContent());
        }
    }

    public function test_the_hero_subtitle_is_a_real_class_with_a_strong_colour_and_enough_contrast(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<p class="g-lead" data-g-clear>[^<]+<\/p>/u', $html);
        $this->assertStringContainsString('class="g-trust"', $html);

        $css = file_get_contents(resource_path('css/g.css'));
        $this->assertMatchesRegularExpression('/\.design-g \.g-hero \.g-lead, \.design-g \.g-hero \.g-trust \{ color: var\(--g-ink\); text-shadow:[^}]*var\(--g-bg\)/', $css, 'the hero subtitle uses the ink colour with a halo in the page colour');

        // Contrast of the ink on the page colour, light and dark, from the tokens themselves (WCAG 2.x).
        $token = fn (string $block, string $name) => preg_match('/' . preg_quote($name, '/') . ': (#[0-9A-Fa-f]{6})/', $block, $m) ? $m[1] : $this->fail("missing $name");
        $light = [1 => substr($css, 0, (int) strpos($css, 'html.design-g.dark {'))];
        $dark = [1 => substr($css, (int) strpos($css, 'html.design-g.dark {'), 900)];
        $ratio = function (string $a, string $b) {
            $lum = function (string $hex) {
                $c = array_map(fn ($v) => ($v /= 255) <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, sscanf($hex, '#%2x%2x%2x'));

                return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
            };
            [$x, $y] = [$lum($a), $lum($b)];

            return (max($x, $y) + 0.05) / (min($x, $y) + 0.05);
        };
        $this->assertGreaterThanOrEqual(4.5, $ratio($token($light[1], '--g-ink'), $token($light[1], '--g-bg')));
        $this->assertGreaterThanOrEqual(4.5, $ratio($token($dark[1], '--g-ink'), $token($dark[1], '--g-bg')));
    }

    public function test_the_share_image_is_the_new_g_file_in_both_languages(): void
    {
        foreach (['/' => 'og-g.png', '/en' => 'og-g-en.png', '/paketi' => 'og-g.png', '/en/packages' => 'og-g-en.png', '/blog' => 'og-g.png'] as $url => $file) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(asset('images/' . $file), $this->meta($html, 'property', 'og:image'), $url);
            $this->assertSame(asset('images/' . $file), $this->meta($html, 'name', 'twitter:image'), $url);
            $this->assertSame('1200', $this->meta($html, 'property', 'og:image:width'));
            $this->assertSame('630', $this->meta($html, 'property', 'og:image:height'));
            $this->assertSame('image/png', $this->meta($html, 'property', 'og:image:type'));
            $this->assertNotEmpty($this->meta($html, 'property', 'og:image:alt'));
            $this->assertNotEmpty($this->meta($html, 'name', 'twitter:image:alt'));
            $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
        }

        foreach (['og-g.png', 'og-g-en.png'] as $file) {
            $path = public_path('images/' . $file);
            $this->assertFileExists($path);
            $this->assertSame([1200, 630], array_slice(getimagesize($path), 0, 2), $file);
            $this->assertLessThan(300 * 1024, filesize($path), "$file must stay under 300 KB");
        }
    }

    public function test_the_organization_logo_and_image_in_json_ld_are_g(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertMatchesRegularExpression('#"logo":\s*"[^"]*images\\\\?/logo-g\.png"#', $html);
        $this->assertStringContainsString('"image":"' . asset('images/og-g.png') . '"', $html);
        $this->assertFileExists(public_path('images/logo-g.png'));
        $this->assertSame(asset('images/logo-g.png'), Schema::logoUrl());
    }

    public function test_no_old_share_image_is_referenced_by_the_code(): void
    {
        foreach (['app', 'resources/views', 'routes', 'config'] as $dir) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($dir), \FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (str_contains($file->getPathname(), 'views/demos') || ! $file->isFile()) {
                    continue;
                }
                $this->assertStringNotContainsString('og-image.png', file_get_contents($file->getPathname()), $file->getPathname());
            }
        }
    }

    public function test_the_share_image_template_uses_the_texts_of_the_home_hero(): void
    {
        $script = file_get_contents(base_path('tools/og/render.mjs'));
        foreach (['bg' => config('creatium'), 'en' => config('creatium_en')] as $lang => $cfg) {
            $this->assertStringContainsString($cfg['home_hero']['title'], $script, "tools/og/render.mjs: the $lang headline drifted from config home_hero.title");
            $this->assertStringContainsString($cfg['home_hero']['accent'][0], $script);
        }
    }

    public function test_the_chat_widget_and_the_cookie_banner_are_g(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-chat-panel', $html);
        $this->assertStringContainsString('id="cookie-banner"', $html);
        $this->assertNoOldLook($html, 'G home with chat and cookie banner');
        $this->assertStringNotContainsString('bg-gradient-to-br', $html);

        // The shared Tailwind setup of G maps the old brand and ink tokens to the G palette.
        $shared = file_get_contents(resource_path('css/g-shared.css'));
        foreach (['#0f1a2b', '#274f84', '#3b77bd', '#070d18', '#0b1424', '#111e33', 'rgb(93 145 205'] as $old) {
            $this->assertStringNotContainsStringIgnoringCase($old, $shared);
        }
        $this->assertStringContainsString('--color-brand-950: #0C0E12', $shared);
    }

    public function test_the_not_found_and_session_expired_pages_are_g(): void
    {
        $html = $this->get('/ne-sashtestvuva')->assertNotFound()->getContent();
        $this->assertStringContainsString('class="g-ph g-404"', $html);
        $this->assertNoOldLook($html, '404');

        \Illuminate\Support\Facades\Route::middleware('web')->post('/_expired', fn () => abort(419));
        $html = $this->post('/_expired')->assertStatus(419)->getContent();
        $this->assertStringContainsString('class="g-ph g-404"', $html);
        $this->assertStringContainsString('class="g-mono g-code">419', $html);
        $this->assertNoOldLook($html, '419');
        $this->assertStringNotContainsString('text-2xl', $html);
    }

    public function test_the_standalone_error_pages_are_g_in_both_languages(): void
    {
        foreach (['500', '503', '429', '403'] as $code) {
            foreach (['/x' => 'Creatium Lab', '/en/x' => 'Creatium Lab'] as $url => $brand) {
                $this->app->instance('request', \Illuminate\Http\Request::create($url));
                $html = view('errors.' . $code)->render();
                $this->assertStringContainsString('class="design-g"', $html, "$code $url");
                $this->assertStringContainsString('--accent: #E8431B', $html);
                $this->assertStringContainsString('images/logo-g.webp', $html);
                $this->assertDoesNotMatchRegularExpression('/border-radius:\s*[1-9]/', $html, "$code is square");
                $this->assertNoOldLook($html, "error $code $url");
                $this->assertStringContainsString('noindex', $html);
            }
        }

        $this->app->instance('request', \Illuminate\Http\Request::create('/en/x'));
        $this->assertStringContainsString('Back to the home page', view('errors.500')->render());
        $this->assertStringContainsString('We are improving the site', view('errors.503')->render());
    }

    public function test_the_error_pages_of_the_current_design_are_unchanged(): void
    {
        config(['creatium.design' => 'current']);
        $this->app->instance('request', \Illuminate\Http\Request::create('/x'));
        $this->assertStringContainsString('#274f84', view('errors.500')->render());
    }

    public function test_the_unsubscribe_page_is_g(): void
    {
        foreach (['bg' => 'Отписахте се', 'en' => 'You are unsubscribed'] as $language => $heading) {
            $prospect = new OutreachProspect(['email' => 'info@example.bg', 'language' => $language, 'country' => $language === 'en' ? 'gb' : 'bg']);
            $html = view('outreach-unsubscribed', ['prospect' => $prospect])->render();
            $this->assertStringContainsString($heading, $html);
            $this->assertStringContainsString('class="design-g"', $html);
            $this->assertStringContainsString('info@example.bg', $html);
            $this->assertNoOldLook($html, 'unsubscribe');
            $this->assertStringNotContainsString('border-radius:14px', $html);
        }
    }

    public function test_the_admin_and_login_use_the_g_logo_and_tokens(): void
    {
        $login = $this->get('/login')->assertOk()->getContent();
        $this->assertStringContainsString('logo-g-dark-352.webp', $login);
        $this->assertStringContainsString('logo-g-352.webp', $login);
        $this->assertNoOldLook($login, 'login');

        $manifest = json_decode(file_get_contents(public_path('admin.webmanifest')), true);
        $this->assertSame('#0C0E12', $manifest['theme_color']);
        $this->assertSame('#ECEEF0', $manifest['background_color']);

        $admin = file_get_contents(resource_path('css/admin.css'));
        $this->assertStringNotContainsStringIgnoringCase('#0f1a2b', $admin);
        $this->assertStringNotContainsStringIgnoringCase('manrope', $admin);
    }

    public function test_the_presentation_pdfs_are_g(): void
    {
        foreach (['creatium-lab-prezentatsiya.pdf', 'creatium-lab-presentation.pdf'] as $pdf) {
            $bytes = file_get_contents(public_path('files/' . $pdf));
            $this->assertStringContainsString('Geologica', $bytes, $pdf);
            $this->assertStringNotContainsString('Manrope', $bytes, $pdf);
        }
    }
}
