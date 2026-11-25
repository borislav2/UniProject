<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\SearchConsoleWins2026;
use App\Support\StarterBlogPosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchConsoleWinsTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(string $slug, array $extra = []): Post
    {
        return Post::create($extra + [
            'title' => ['bg' => 'Тест'], 'slug' => ['bg' => $slug], 'body' => ['bg' => 'x'], 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
    }

    public function test_meta_texts_fit_and_have_no_dashes_or_colons(): void
    {
        foreach (SearchConsoleWins2026::POSTS as $slug => [$title, $description]) {
            $this->assertLessThanOrEqual(60, mb_strlen($title), $slug);
            $this->assertLessThanOrEqual(155, mb_strlen($description), $slug);
            $this->assertDoesNotMatchRegularExpression('/[–—:]| - /u', $title . $description, $slug);
        }
        $black = SearchConsoleWins2026::POSTS['cheren-petak-2026-danni-za-balgarskiya-kupuvach'];
        $this->assertStringStartsWith('Black Friday 2026', $black[0]);
        $this->assertStringContainsString('България', $black[0]);
    }

    public function test_meta_fills_empty_fields_and_the_old_default_but_never_owner_edits(): void
    {
        $empty = $this->makePost('cheren-petak-2026-danni-za-balgarskiya-kupuvach');
        $oldDefault = $this->makePost('kak-da-izberete-domeyn-za-biznesa-si', ['meta_title' => ['bg' => 'Моето заглавие'], 'meta_description' => ['bg' => 'Моето описание']]);

        $this->assertSame(2, SearchConsoleWins2026::apply());
        $this->assertSame(SearchConsoleWins2026::POSTS[$empty->tr('slug')][0], $empty->fresh()->tr('meta_title', 'bg'));
        $this->assertNotEmpty($empty->fresh()->tr('meta_description', 'bg'));
        $this->assertSame('Моето заглавие', $oldDefault->fresh()->tr('meta_title', 'bg'));
        $this->assertSame('Моето описание', $oldDefault->fresh()->tr('meta_description', 'bg'));
        $this->assertSame(0, SearchConsoleWins2026::apply(), 'Running twice changes nothing');

        // The earlier default of ours (PostMetaTitles2026) is replaced, an owner's own title never.
        $empty->update(['meta_title' => ['bg' => 'Черен петък 2026 и какво показват данните за купувача']]);
        $this->assertSame(1, SearchConsoleWins2026::apply());
        $empty->update(['meta_title' => ['bg' => 'Собствено заглавие']]);
        $this->assertSame(0, SearchConsoleWins2026::apply());
        $this->assertSame('Собствено заглавие', $empty->fresh()->tr('meta_title', 'bg'));
    }

    public function test_domain_article_replaces_only_the_original_or_the_previous_expanded_text(): void
    {
        $slug = 'kak-da-izberete-domeyn-za-biznesa-si';
        $original = collect(StarterBlogPosts::data()['posts'])->firstWhere('slug', $slug)['body'];
        $previous = StarterBlogPosts::previousBody($slug);
        $new = trim(file_get_contents(database_path("data/blog-expanded/$slug.md")));
        $this->assertNotNull($previous);
        $this->assertNotSame($previous, $new);

        foreach ([trim($original), $previous] as $start) {
            $post = $this->makePost($slug, ['body' => ['bg' => $start]]);
            StarterBlogPosts::expand();
            $this->assertSame($new, $post->fresh()->body['bg']);
            $post->forceDelete();
        }

        $post = $this->makePost($slug, ['body' => ['bg' => 'Нашият текст.']]);
        StarterBlogPosts::expand();
        $this->assertSame('Нашият текст.', $post->fresh()->body['bg']);
    }

    public function test_domain_article_has_the_search_words_headings_and_two_service_links(): void
    {
        $text = file_get_contents(database_path('data/blog-expanded/kak-da-izberete-domeyn-za-biznesa-si.md'));
        $this->assertGreaterThan(900, preg_match_all('/\pL{2,}/u', $text));
        $this->assertMatchesRegularExpression('/^##.*[Ии]збор на домейн/mu', $text);
        $this->assertMatchesRegularExpression('/^##.*домейн \.bg/miu', $text);
        $this->assertStringContainsString('избор на домейн', mb_strtolower($text));
        $this->assertStringContainsString('(/uslugi/izrabotka-na-sait)', $text);
        $this->assertStringContainsString('(/uslugi/seo-i-ai-vidimost)', $text);
        $this->assertDoesNotMatchRegularExpression('/[–—:]| - /u', preg_replace('~\(/[^)]*\)~', '', $text));
    }

    public function test_the_seo_service_page_answers_geo_chatgpt_and_what_is_included(): void
    {
        foreach (['bg' => '/uslugi/seo-i-ai-vidimost', 'en' => '/en/services/seo-and-ai-visibility'] as $locale => $path) {
            foreach (['current', 'g'] as $design) {
                config(['creatium.design' => $design]);
                $html = $this->get($path)->assertOk()->getContent();
                $needles = $locale === 'bg' ? ['Какво е GEO?', 'Как да съм видим в ChatGPT?', 'Какво включва SEO и AI видимост?'] : ['What is GEO?', 'How do I become visible in ChatGPT?', 'What does SEO and AI visibility include?'];
                foreach ($needles as $needle) {
                    $this->assertStringContainsString($needle, $html, "$locale $design");
                    $this->assertMatchesRegularExpression('/"name":"' . preg_quote($needle, '/') . '"/u', $html, "$locale $design json-ld");
                }
            }
        }
        $this->assertStringContainsString('SEO и AI видимост', config('creatium.service_pages.seo.lead'));
        $this->assertStringContainsString('SEO и AI видимост', config('creatium.service_pages.seo.seo_title'));
        $this->assertLessThanOrEqual(60, mb_strlen(config('creatium.service_pages.seo.seo_title')));
    }

    public function test_the_small_business_page_has_no_demo_and_is_listed_everywhere(): void
    {
        $page = config('creatium.industry_pages.malak-biznes');
        $this->assertArrayNotHasKey('demo', $page);
        $url = url('/izrabotka-na-sait-za-malak-biznes');

        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            $this->get('/izrabotka-na-sait-za-malak-biznes')->assertOk()
                ->assertSee($page['title'])
                ->assertSee('Core Изграждане')
                ->assertSee('от €500 / месец')
                ->assertSee($page['faq'][0][0])
                ->assertSee('"@type":"Service"', false)
                ->assertSee('"@type":"FAQPage"', false)
                ->assertDontSee('/demo/', false);
        }

        $this->get('/sitemap.xml')->assertSee('<loc>' . $url . '</loc>', false);
        $this->get('/llms.txt')->assertSee('[' . $page['title'] . '](' . $url . ')', false);
        $this->get('/llms-full.txt')->assertSee('[' . $page['title'] . '](' . $url . ')', false);
        $this->get('/proekti')->assertOk();
        $this->get('/en')->assertDontSee('izrabotka-na-sait-za-malak-biznes', false);
    }
}
