<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Design G, expert audit fixes A (16.11.2026): form errors, project images, fonts, hero canvas. No visible copy changes. */
class GFixATest extends TestCase
{
    use RefreshDatabase;

    private function g(): void
    {
        config(['creatium.design' => 'g']);
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function project(string $name, string $screenshot): Project
    {
        $category = Category::firstOrCreate(['name' => 'Уебсайт']);
        $project = new Project();
        $project->forceFill([
            'name' => $name, 'description' => 'Сайт за клиент.', 'status' => 'Completed', 'is_public' => true, 'website_url' => 'https://example.com',
            'screenshot' => $screenshot, 'category_id' => $category->id, 'manager' => 'Борислав', 'start_date' => now()->subMonths(3), 'end_date' => now()->subMonth(),
        ])->save();

        return $project;
    }

    public function test_a_failed_g_contact_post_marks_the_invalid_fields_and_announces_the_existing_messages(): void
    {
        $this->g();
        $html = $this->from('/kontakti')->followingRedirects()->post('/kontakti', ['form' => 'g'])->assertOk()->getContent();

        foreach (['name', 'email', 'consent'] as $field) {
            $this->assertMatchesRegularExpression('~<input[^>]*name="' . $field . '"[^>]*aria-invalid="true"[^>]*aria-describedby="err-' . $field . '"~', $html, $field);
            $this->assertMatchesRegularExpression('~<p class="g-err" id="err-' . $field . '">[^<]+</p>~u', $html, $field);
        }
        // A field without an error stays clean.
        $this->assertDoesNotMatchRegularExpression('~<input[^>]*name="business_site"[^>]*aria-invalid~', $html);
        // The live region repeats exactly the existing messages (no new text) and the script moves the focus to the first invalid field.
        $this->assertMatchesRegularExpression('~<div class="g-sr" role="alert" data-g-errors>\s*<ul>(\s*<li>[^<]+</li>)+\s*</ul>~u', $html);
        $this->assertSame(3, preg_match_all('~<li>[^<]+</li>~u', substr($html, strpos($html, 'data-g-errors'), 900)));
        $this->assertStringContainsString('[aria-invalid="true"], [data-g-invalid]', $html);
        $this->assertStringContainsString('.focus({ preventScroll: true })', $html);

        // Without errors there is no region and no marker.
        $clean = $this->get('/kontakti')->assertOk()->getContent();
        $this->assertStringNotContainsString('aria-invalid', $clean);
        $this->assertStringNotContainsString('data-g-errors', $clean);
    }

    public function test_a_booked_call_without_a_phone_points_the_phone_field_and_a_bad_slot_points_the_booking(): void
    {
        $this->g();
        $slot = \App\Support\Booking::days()[0]['slots'][0]['value'];
        $html = $this->from('/kontakti')->followingRedirects()->post('/kontakti', ['form' => 'g', 'name' => 'А', 'email' => 'a@b.bg', 'consent' => '1', 'consultation' => $slot])->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<input[^>]*name="phone"[^>]*aria-invalid="true"[^>]*aria-describedby="err-phone"~', $html);
        $this->assertStringContainsString('data-booking-details open', $html);

        $html = $this->from('/kontakti')->followingRedirects()->post('/kontakti', ['form' => 'g', 'name' => 'А', 'email' => 'a@b.bg', 'consent' => '1', 'phone' => '0888123456', 'consultation' => '2020-01-01 10:00'])->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<fieldset id="konsultatsiya"[^>]*data-g-invalid tabindex="-1" aria-describedby="err-consultation"~', $html);
        $this->assertMatchesRegularExpression('~<p class="g-err" id="err-consultation">~', $html);
    }

    public function test_the_site_check_form_connects_its_errors_too(): void
    {
        $this->g();
        $html = $this->from('/proverka-na-sait')->followingRedirects()->post('/proverka-na-sait', [])->assertOk()->getContent();
        foreach (['domain', 'email', 'consent'] as $field) {
            $this->assertMatchesRegularExpression('~<input[^>]*name="' . $field . '"[^>]*aria-invalid="true"[^>]*aria-describedby="err-' . $field . '"~', $html, $field);
            $this->assertStringContainsString('id="err-' . $field . '"', $html);
        }
        $this->assertStringContainsString('data-g-errors', $html);
    }

    public function test_the_error_styles_and_the_scroll_margin_are_in_the_shared_g_css(): void
    {
        $css = file_get_contents(resource_path('css/g.css'));
        $this->assertStringContainsString('scroll-margin-top: 110px', $css);
        $this->assertStringContainsString('.design-g .g-in[aria-invalid="true"]', $css);
        $this->assertStringContainsString('.design-g .g-sr {', $css, 'the home page loads g.css without g-pages.css');
    }

    public function test_the_first_project_image_is_high_priority_and_the_rest_are_lazy_with_srcset_and_sizes(): void
    {
        $this->g();
        $this->project('Lasepro', 'images/projects/lasepro-2026-11.webp');
        $this->project('Покриви Бургас', 'images/projects/pokrivi-burgas-2026-11.webp');
        $html = $this->get('/proekti')->assertOk()->getContent();
        preg_match_all('~<img [^>]*>~', substr($html, strpos($html, 'data-portfolio-grid')), $m);
        $images = $m[0];
        $this->assertGreaterThanOrEqual(3, count($images));

        $this->assertStringContainsString('fetchpriority="high"', $images[0]);
        $this->assertStringNotContainsString('loading="lazy"', $images[0]);
        $this->assertStringContainsString('lasepro-2026-11-640.webp 640w', $images[0]);
        $this->assertStringContainsString('lasepro-2026-11-960.webp 960w', $images[0]);
        $this->assertStringContainsString('lasepro-2026-11.webp 1600w', $images[0]);
        $this->assertStringContainsString('sizes="(min-width: 1100px) min(33vw, 400px), (min-width: 760px) 50vw, calc(100vw - 50px)"', $images[0]);
        $this->assertSame(1, substr_count(implode('', $images), 'fetchpriority="high"'));
        foreach (array_slice($images, 1) as $image) {
            $this->assertStringContainsString('loading="lazy"', $image);
            $this->assertStringNotContainsString('fetchpriority', $image);
        }
        // The industry sites keep their own srcset and now have the same correct sizes.
        $this->assertStringContainsString('sizes="(min-width: 1100px) min(33vw, 400px), (min-width: 760px) 50vw, calc(100vw - 50px)"', $images[2]);
    }

    public function test_without_real_projects_the_first_industry_site_is_the_high_priority_image(): void
    {
        $this->g();
        $html = $this->get('/proekti')->assertOk()->getContent();
        preg_match_all('~<img [^>]*>~', substr($html, strpos($html, 'data-portfolio-grid')), $m);
        $this->assertStringContainsString('fetchpriority="high"', $m[0][0]);
        $this->assertStringNotContainsString('loading="lazy"', $m[0][0]);
        $this->assertStringContainsString('loading="lazy"', $m[0][1]);
    }

    public function test_project_srcset_uses_only_the_copies_that_exist(): void
    {
        $this->assertStringContainsString('-640.webp 640w', project_srcset('images/projects/lasepro.webp'));
        $this->assertSame('', project_srcset('uploads/portfolio/not-there.webp'), 'an upload without smaller copies uses the plain src');
        $this->assertSame('', project_srcset(null));
        foreach (['lasepro', 'lasepro-2026-11', 'pokrivi-burgas', 'pokrivi-burgas-2026-11'] as $name) {
            foreach ([640, 960] as $width) {
                $this->assertFileExists(public_path("images/projects/{$name}-{$width}.webp"));
            }
        }
    }

    public function test_home_projects_also_get_srcset_and_correct_sizes(): void
    {
        $this->g();
        $this->project('Lasepro', 'images/projects/lasepro-2026-11.webp');
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<img src="[^"]*lasepro-2026-11\.webp" srcset="[^"]*640w[^"]*" sizes="\(min-width: 1100px\)[^"]*" alt="Lasepro"[^>]*loading="lazy"~', $html);
        $this->assertStringNotContainsString('sizes="(min-width: 760px) 33vw, 100vw"', $html);
    }

    public function test_the_g_fonts_are_three_faces_in_six_small_files(): void
    {
        $css = file_get_contents(resource_path('css/g.css'));
        $this->assertStringNotContainsString('@fontsource', $css);
        $this->assertSame(6, substr_count($css, '@font-face'));
        $this->assertSame(6, substr_count($css, 'font-display: swap'));
        preg_match_all("~url\('\.\./fonts/g/([a-z0-9-]+\.woff2)'\)~", $css, $m);
        $this->assertCount(6, array_unique($m[1]));
        $total = 0;
        foreach ($m[1] as $file) {
            $this->assertFileExists(resource_path('fonts/g/' . $file));
            $total += filesize(resource_path('fonts/g/' . $file));
        }
        $this->assertLessThan(60 * 1024, $total, 'about 55 KB for all six');
        // Mono keeps its Cyrillic file (Bulgarian labels). All six files paint the first screen, so all six are preloaded (see GPerformanceTest).
        $this->assertStringContainsString('jetbrains-mono-cyrillic-500.woff2', $css);
        $layout = file_get_contents(resource_path('views/layouts/public.blade.php'));
        $this->assertStringContainsString("['geologica-cyrillic-300', 'onest-cyrillic-400', 'geologica-latin-300', 'onest-latin-400', 'jetbrains-mono-cyrillic-500', 'jetbrains-mono-latin-500']", $layout);
        // The current design still preloads Manrope only.
        $this->assertStringContainsString('manrope-{$subset}-wght-normal.woff2', $layout);
    }
}
