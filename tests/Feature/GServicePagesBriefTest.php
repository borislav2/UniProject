<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** The service pages of design G built from the owners' brief (18.11.2026): eight services, the strip of platforms, the website form. */
class GServicePagesBriefTest extends TestCase
{
    use RefreshDatabase;

    private const KEYS = ['website', 'seo', 'google', 'email', 'support', 'shopping', 'software', 'ai_agents'];

    private function g(): void
    {
        config(['creatium.design' => 'g']);
    }

    private function page(string $key, string $lang): string
    {
        $path = parse_url(PageController::serviceUrl($key, $lang), PHP_URL_PATH);

        return $this->get($path)->assertOk()->getContent();
    }

    public function test_each_g_service_page_shows_the_brief_in_both_languages(): void
    {
        $this->g();
        foreach (self::KEYS as $key) {
            foreach (['bg', 'en'] as $lang) {
                $cfg = $lang === 'en' ? config("creatium_en.service_pages.$key") : config("creatium.service_pages.$key");
                $html = $this->page($key, $lang);
                $label = "$key $lang";

                $this->assertSame(1, preg_match_all('/<h1[ >]/', $html), "one h1 on $label");
                $this->assertStringContainsString('<h1 class="g-h">' . e($cfg['g_hero_title']) . '</h1>', $html, $label);
                $this->assertStringContainsString(e($cfg['g_problems_title']), $html, $label);
                foreach ($cfg['g_problems'] as $problem) {
                    $this->assertStringContainsString(e($problem['title']), $html, $label);
                }
                if (! empty($cfg['g_process'])) {
                    $this->assertStringContainsString(e($cfg['g_process_title']), $html, $label);
                    foreach ($cfg['g_process'] as $step) {
                        $this->assertStringContainsString(e($step['title']), $html, $label);
                    }
                }
                foreach ($cfg['g_faq'] as $qa) {
                    $this->assertStringContainsString(e($qa['q']), $html, $label);
                }
                $this->assertStringContainsString('data-g-plat', $html, "$label marquee");
                $this->assertStringContainsString(' g-bv', $html, "$label visual");

                // Valid FAQPage JSON-LD built from the new questions.
                preg_match('#<script type="application/ld\+json">(.*?)</script>#s', substr($html, strrpos($html, '"@graph"') - 80), $m);
                $this->assertNotEmpty($m, "$label json-ld");
                $graph = collect(json_decode($m[1], true, 512, JSON_THROW_ON_ERROR)['@graph']);
                $faq = $graph->firstWhere('@type', 'FAQPage');
                $this->assertSame(array_column($cfg['g_faq'], 'q'), array_column($faq['mainEntity'], 'name'), $label);

                $this->assertSame([], PlainCopyTest::offences(PlainCopyTest::visibleText($html)), "$label has dashes or colons");
                if ($lang === 'en') {
                    $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', PlainCopyTest::visibleText($html), "$label is not fully English");
                }
            }
        }
    }

    public function test_the_marquee_lists_the_platforms_once_visibly_and_once_hidden(): void
    {
        $this->g();
        $html = $this->get('/uslugi')->assertOk()->getContent();
        $this->assertStringContainsString('Работим с водещите платформи', $html);
        $this->assertSame(1, substr_count($html, 'data-g-plat>'));
        foreach (config('creatium.platforms') as $name) {
            $this->assertSame(1, substr_count($html, '<li>' . e($name) . '</li>'), $name);
            $this->assertSame(1, substr_count($html, '<li aria-hidden="true">' . e($name) . '</li>'), $name);
        }
        $this->assertStringContainsString('We work with the leading platforms', $this->get('/en/services')->getContent());
        $css = file_get_contents(resource_path('css/g-svc.css'));
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('animation-play-state: paused', $css);
    }

    public function test_the_website_form_creates_a_lead_with_the_message(): void
    {
        $this->g();
        Mail::fake();
        $html = $this->page('website', 'bg');
        $this->assertStringContainsString('name="g_directions[]" value="website"', $html);
        foreach (['Име и фамилия', 'Служебен имейл', 'Телефон', 'Текущ сайт (ако имате)', 'Кратко описание на проекта'] as $label) {
            $this->assertStringContainsString($label, $html);
        }

        $this->post('/kontakti', [
            'form' => 'g', 'g_directions' => ['website'], 'name' => 'Мария Иванова', 'email' => 'Maria@Firma.bg', 'phone' => '0888123456',
            'business_site' => 'firma.bg', 'message' => 'Искаме нов сайт за фирмата ни', 'consent' => '1',
        ])->assertSessionHasNoErrors()->assertSessionHas('lead_created');

        $lead = Project::latest('id')->first();
        $this->assertSame('maria@firma.bg', $lead->client_email);
        $this->assertSame('Уебсайт с чист код и поддръжка', $lead->service);
        $this->assertStringContainsString('Искаме нов сайт за фирмата ни', $lead->description);
        $this->assertStringContainsString('Бизнес или сайт firma.bg', $lead->description);
        Mail::assertSent(\App\Mail\NewLeadMail::class);

        // The honeypot, the consent and the required fields still work.
        $count = Project::count();
        $this->post('/kontakti', ['form' => 'g', 'name' => 'Х', 'email' => 'x@y.bg', 'consent' => '1', 'website' => 'spam'])->assertSessionHasNoErrors();
        $this->assertSame($count, Project::count());
        $this->post('/kontakti', ['form' => 'g', 'name' => 'Х', 'email' => 'x@y.bg', 'message' => 'a'])->assertSessionHasErrors('consent');
        // The main G form has no message box and is unchanged without one.
        $this->post('/kontakti', ['form' => 'g', 'name' => 'Ц', 'email' => 'c@y.bg', 'consent' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('Без съобщение. Запитване от сайта.', Project::latest('id')->first()->description);
    }

    public function test_ai_agents_is_g_only_like_software(): void
    {
        $this->assertTrue(config('creatium.service_pages.ai_agents.g_only'));
        $this->assertSame('ai-agenti', config('creatium.service_pages.ai_agents.slug'));
        $this->assertSame('ai-agents', config('creatium_en.service_pages.ai_agents.slug'));
        $this->assertNull(config('creatium.service_pages.ai_agents.package'));
        $this->assertSame('software', config('creatium.service_pages.ai_agents.g_direction'));
        $this->assertArrayHasKey('software', config('creatium.contact_directions_g'));

        // Current design: the page renders but is noindex without hreflang, out of the sitemap, llms.txt, the menu and the other services.
        foreach (['/uslugi/ai-agenti', '/en/services/ai-agents'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html, $path);
            $this->assertDoesNotMatchRegularExpression('/<link[^>]+hreflang=/', $html, $path);
        }
        foreach (['/', '/uslugi', '/uslugi/izrabotka-na-sait'] as $path) {
            $this->assertStringNotContainsString('ai-agenti', $this->get($path)->getContent(), $path);
        }
        $this->assertStringNotContainsString('ai-agenti', $this->get('/sitemap.xml')->getContent());
        $this->assertStringNotContainsString('ai-agenti', $this->get('/llms.txt')->getContent());
        $this->assertStringContainsString('AI агенти, които поемат оперативната работа', \App\Support\Chat\SiteKnowledge::text());

        $this->g();
        $html = $this->page('ai_agents', 'bg');
        $this->assertStringNotContainsString('noindex', $html);
        $this->assertMatchesRegularExpression('#<link[^>]+hreflang="en"[^>]+/en/services/ai-agents#', $html);
        $this->assertStringContainsString('Цена по оферта', $html);
        $this->assertStringContainsString('<loc>http://localhost/uslugi/ai-agenti</loc>', $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString('ai-agenti', $this->get('/llms.txt')->getContent());
        $this->assertStringContainsString('data-g-service="ai_agents"', $this->get('/uslugi')->getContent());
        $this->assertStringContainsString(PageController::serviceUrl('ai_agents'), $this->get('/')->getContent());
        // The form direction of the page is the software one.
        $this->assertStringContainsString('napravleniya=software', $html);
    }

    public function test_the_current_design_service_pages_are_unchanged(): void
    {
        foreach (self::KEYS as $key) {
            foreach (['bg', 'en'] as $lang) {
                $html = $this->page($key, $lang);
                foreach (['g-bv', 'g-plat', 'g-svc-sec', 'data-g-plat', 'g-ph-vis'] as $marker) {
                    $this->assertStringNotContainsString($marker, $html, "$key $lang $marker");
                }
                $this->assertStringNotContainsString(config("creatium.service_pages.$key.g_hero_title"), $html);
            }
        }
        $this->assertStringNotContainsString('g-plat', $this->get('/uslugi')->getContent());
    }

    public function test_the_softened_claims_are_not_in_the_texts(): void
    {
        foreach (['creatium', 'creatium_en'] as $file) {
            $text = json_encode(config("$file.service_pages"), JSON_UNESCAPED_UNICODE);
            foreach (['доминация', 'безспорния', 'класира на върха', 'всяко отваряне', 'Европа и Америка', 'Никакъв.', 'every open', 'Europe and America', 'undisputed', 'domination', 'to the top'] as $phrase) {
                $this->assertStringNotContainsString($phrase, $text, "$file still says $phrase");
            }
        }
    }
}
