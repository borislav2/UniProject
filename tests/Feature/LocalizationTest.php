<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_pages_load_in_english(): void
    {
        foreach (['/en', '/en/services', '/en/packages', '/en/projects', '/en/about', '/en/contact', '/en/privacy', '/en/terms', '/en/cookies'] as $url) {
            $this->get($url)->assertOk()->assertSee('<html lang="en">', false)->assertDontSee('Безплатна консултация');
        }

        $this->get('/en')
            ->assertSee('Be recognizable.')
            ->assertSee('How we can help you stand out')
            ->assertSee('The customer is king')
            ->assertSee('Website monitoring &amp; health', false);
    }

    public function test_bulgarian_stays_the_default(): void
    {
        $this->get('/')->assertSee('<html lang="bg">', false)->assertSee('Бъди разпознаваем.');
    }

    public function test_pages_link_to_their_other_language(): void
    {
        $this->get('/uslugi')
            ->assertSee('<link rel="alternate" hreflang="en" href="' . url('/en/services') . '">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="' . url('/uslugi') . '">', false);

        $this->get('/en/services')
            ->assertSee('href="' . url('/uslugi') . '" hreflang="bg"', false)
            ->assertSee('href="' . url('/en/contact') . '"', false);
    }

    public function test_english_contact_form_creates_lead_with_bulgarian_service_label(): void
    {
        Mail::fake();

        $this->from('/en/contact')->post('/en/contact', [
            'name' => 'John Smith', 'phone' => '+44 20 7946 0958', 'service' => 'monitoring', 'consent' => '1',
        ])->assertRedirect('/en/contact')->assertSessionHas('success', 'We have received your inquiry. We will call you within one business day.');

        $lead = Project::where('source', 'website')->firstOrFail();
        $this->assertSame('Мониторинг и здраве на сайта', $lead->service);
        $this->assertSame('en', $lead->locale);
    }

    public function test_english_validation_messages(): void
    {
        $this->from('/en/contact')->post('/en/contact', [])
            ->assertSessionHasErrors(['name' => 'Please enter your name.', 'phone' => 'Please enter a phone number we can call you on.']);
    }

    public function test_english_404(): void
    {
        $this->get('/en/no-such-page')->assertNotFound()->assertSee('Page not found');
        $this->get('/nyama-takava')->assertNotFound()->assertSee('Страницата не е намерена');
    }

    public function test_sitemap_has_both_languages_with_alternates(): void
    {
        $this->get('/sitemap.xml')
            ->assertSee('<loc>' . url('/en/packages') . '</loc>', false)
            ->assertSee('<loc>' . url('/paketi') . '</loc>', false)
            ->assertSee('hreflang="en" href="' . url('/en/cookies') . '"', false);
    }

    public function test_every_translation_key_has_an_english_value(): void
    {
        $translations = json_decode(file_get_contents(lang_path('en.json')), true);

        foreach ($translations as $bg => $en) {
            $this->assertNotSame('', trim($en), "Empty translation for: $bg");
            $this->assertDoesNotMatchRegularExpression('/[А-Яа-я]/u', $en, "Cyrillic left in translation for: $bg");
        }
    }

    public function test_theme_toggle_is_rendered_and_applied_before_paint(): void
    {
        $html = $this->get('/')->assertSee('data-theme-toggle', false)->getContent();

        $this->assertLessThan(strpos($html, '<style>') ?: strpos($html, '<link rel="icon"'), strpos($html, "localStorage.getItem('creatium-theme')"));
    }
}
