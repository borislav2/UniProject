<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSitesTest extends TestCase
{
    use RefreshDatabase;

    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b.*?</\1>#si', '', $html);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html))));
    }

    public function test_every_industry_site_loads_without_any_label_and_is_not_indexed(): void
    {
        foreach (config('creatium.demos') as $demo) {
            $response = $this->get('/demo/' . $demo['slug'])
                ->assertOk()
                ->assertSee('<meta name="robots" content="noindex">', false)
                ->assertSee('Изработка <strong class="font-semibold text-white">Creatium Lab</strong>', false)
                ->assertSee('Формите и бутоните за връзка на тази страница не изпращат данни.')
                ->assertSee('data-demo-form', false);

            // Nothing on the page (title and toast included) calls it a demo, a concept, a sample or made up.
            $html = $response->getContent();
            $text = mb_strtolower($this->visibleText($html) . ' ' . (preg_match('#<title>(.*?)</title>#s', $html, $m) ? $m[1] : '') . ' ' . (preg_match('#id="demo-toast"[^>]*>(.*?)</div>#s', $html, $t) ? $t[1] : ''));
            foreach (['демо', 'концепц', 'измисл', 'примерен', 'примерн'] as $word) {
                $this->assertStringNotContainsString($word, $text, $demo['slug'] . ': ' . $word);
            }
            $this->assertStringNotContainsString('#demo', $html);
        }
        $this->assertCount(10, config('creatium.demos'));
        $this->assertSame(array_column(config('creatium.demos'), 'slug'), array_column(config('creatium_en.demos'), 'slug'));
    }

    public function test_unknown_demo_is_404(): void
    {
        $this->get('/demo/nyama-takova')->assertNotFound();
    }

    public function test_projects_page_is_one_grid_of_twelve_cards_without_labels(): void
    {
        $category = \App\Models\Category::create(['name' => 'Уебсайт']);
        \App\Models\Project::create([
            'name' => 'Истински проект', 'description' => 'Описание.', 'start_date' => now(), 'end_date' => now(), 'status' => 'Completed',
            'manager' => 'Екип', 'category_id' => $category->id, 'is_public' => true, 'website_url' => 'https://www.example.bg/',
        ]);

        $page = $this->get('/proekti')->assertOk()
            ->assertDontSee('Демо проекти')->assertDontSee('id="demo"', false)->assertDontSee('Тук скоро ще има проекти');
        $html = $page->getContent();

        $this->assertStringNotContainsString('демо', mb_strtolower($this->visibleText($html)));
        $this->assertSame(1, substr_count($html, 'data-portfolio-grid'));
        $this->assertStringNotContainsString('data-concept-chip', $html);
        $this->assertStringNotContainsString('концепц', mb_strtolower($this->visibleText($html)));
        $this->assertSame(count(config('creatium.demos')) + 1, substr_count($html, '<article'));

        // Real projects first, then the industry sites.
        $page->assertSeeInOrder(['Истински проект', config('creatium.demos.0.name')]);

        foreach (config('creatium.demos') as $demo) {
            $page->assertSee($demo['name'])->assertSee(route('demo.show', $demo['slug']), false)->assertSee('images/demos/' . $demo['slug'] . '.webp', false);
            $this->assertFileExists(public_path('images/demos/' . $demo['slug'] . '.webp'));
        }

        $en = $this->get('/en/projects')->assertOk()->assertDontSee('Concept')->assertSee('Lozata Restaurant')->assertSee('Byala Reka Guest House')->assertSee('The industry websites are in Bulgarian.');
        $this->assertStringNotContainsString('demo', mb_strtolower($this->visibleText($en->getContent())));
    }

    public function test_no_public_page_calls_the_sample_businesses_fictional(): void
    {
        $urls = ['/llms.txt', '/en/projects'];
        foreach (config('creatium.industry_pages') as $slug => $page) {
            $urls[] = '/izrabotka-na-sait-za-' . $slug;
        }
        preg_match_all('#<loc>(.*?)</loc>#', $this->get('/sitemap.xml')->getContent(), $m);
        foreach (array_merge($urls, array_map(fn ($u) => parse_url($u, PHP_URL_PATH) ?: '/', $m[1])) as $path) {
            $text = mb_strtolower($this->get($path)->getContent());
            $this->assertStringNotContainsString('измисл', $text, $path);
            $this->assertStringNotContainsString('fictional', $text, $path);
        }
    }

    public function test_concept_wording_migration_replaces_only_exact_old_texts_and_backs_up(): void
    {
        $old = 'Разработихме демонстрационни платформи за ключови сектори. Всяка от тях адресира специфичните предизвикателства на бранша, пътя на купувача и необходимите интеграции за генериране на запитвания.';
        \App\Models\ContentBlock::create(['key' => 'home_trust', 'locale' => 'bg', 'value' => [
            'demos_text' => $old, 'demos_count_label' => 'Свой етикет с демо', 'demos_title' => 'Моето заглавие',
        ]]);
        \App\Models\ContentBlock::create(['key' => 'demos', 'locale' => 'bg', 'value' => [
            ['slug' => 'restorant', 'faq' => [['Въпрос?', 'Колкото ви трябват. Демото е малък магазин, но каталогът може да расте.'], ['Друг?', 'Собствен текст с демото.']]],
        ]]);
        \App\Models\ContentBlock::create(['key' => 'questions', 'locale' => 'bg', 'value' => [['q' => 'Q', 'a' => 'Без промяна']]]);

        $this->assertSame(['demos/bg', 'home_trust/bg'], \App\Support\ConceptWording2026::apply());

        $trust = \App\Models\ContentBlock::where('key', 'home_trust')->first()->value;
        $this->assertStringStartsWith('Разработихме концепции', $trust['demos_text']);
        $this->assertSame('Свой етикет с демо', $trust['demos_count_label']);
        $this->assertSame('Моето заглавие', $trust['demos_title']);
        $faq = \App\Models\ContentBlock::where('key', 'demos')->first()->value[0]['faq'];
        $this->assertSame('Колкото ви трябват. Концепцията е малък магазин, но каталогът може да расте.', $faq[0][1]);
        $this->assertSame('Собствен текст с демото.', $faq[1][1]);
        $this->assertSame($old, \App\Models\ContentBlock::where('key', 'home_trust:backup-2026-11-08-d')->first()->value['demos_text']);
        $this->assertNull(\App\Models\ContentBlock::where('key', 'questions:backup-2026-11-08-d')->first());

        // A second run changes nothing and never overwrites the backup.
        $this->assertSame([], \App\Support\ConceptWording2026::apply());
        $this->assertSame($old, \App\Models\ContentBlock::where('key', 'home_trust:backup-2026-11-08-d')->first()->value['demos_text']);
    }

    public function test_demos_are_not_in_the_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/demo/', false);
    }

    public function test_industry_sites_wording_migration_replaces_only_exact_old_texts_and_backs_up(): void
    {
        $old = 'Разработихме концепции за ключови сектори. Всяка от тях адресира специфичните предизвикателства на бранша, пътя на купувача и необходимите интеграции за генериране на запитвания.';
        \App\Models\ContentBlock::create(['key' => 'home_trust', 'locale' => 'bg', 'value' => [
            'demos_text' => $old, 'demos_count_label' => 'Концепции за сектори', 'demos_subtitle' => 'Мое подзаглавие с концепции',
        ]]);

        $this->assertSame(['home_trust/bg'], \App\Support\IndustrySitesWording2026::apply());

        $trust = \App\Models\ContentBlock::where('key', 'home_trust')->first()->value;
        $this->assertSame(config('creatium.home_trust.demos_text'), $trust['demos_text']);
        $this->assertSame('Сайта за сектори', $trust['demos_count_label']);
        $this->assertSame('Мое подзаглавие с концепции', $trust['demos_subtitle']);
        $this->assertSame($old, \App\Models\ContentBlock::where('key', 'home_trust:backup-2026-11-11')->first()->value['demos_text']);
        $this->assertSame([], \App\Support\IndustrySitesWording2026::apply());

        // Every new text is the current default in one of the two languages.
        $defaults = array_merge(array_values(config('creatium.home_trust')), array_values(config('creatium_en.home_trust')));
        foreach (\App\Support\IndustrySitesWording2026::REPLACE as $new) {
            $this->assertContains($new, $defaults);
        }
    }
}
