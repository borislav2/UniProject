<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Seo\PostSeo;
use App\Support\Seo\Snippet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Findings of a Screaming Frog crawl (23.11.2026): internal nofollow, links and images without text, blog pagination
 * duplicates and long descriptions. Checked on the pages of design G, which is the live design.
 */
class SeoCrawlFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['creatium.design' => 'g', 'creatium.gtm_id' => 'GTM-TEST123']);
    }

    private function posts(int $count, array $extra = []): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Post::create($extra + [
                'title' => ['bg' => "Статия номер $i"],
                'slug' => ['bg' => "statia-$i"],
                'excerpt' => ['bg' => "Кратко описание на статия номер $i, което стига за описание на страницата в Google."], 'body' => ['bg' => 'Текст на статията.'],
                'status' => 'published',
                'published_at' => now()->subDays($i),
                'cover_image' => 'images/blog/g/cover-' . $i . '-640.webp',
            ]);
        }
    }

    /** @return list<string> */
    private function pages(): array
    {
        if (Post::count() === 0) {
            $this->posts(2);
        }
        $service = 'website';

        return ['/', '/blog', '/blog/statia-1', '/uslugi', '/uslugi/' . site('service_pages')[$service]['slug'], '/uslugi/' . site('service_pages')['ai_agents']['slug'], '/paketi', '/kontakti', '/za-nas', '/proekti', '/en', '/en/services'];
    }

    private function dom(string $html): \DOMXPath
    {
        $doc = new \DOMDocument;
        @$doc->loadHTML('<?xml encoding="utf-8"?>' . $html);

        return new \DOMXPath($doc);
    }

    public function test_no_public_page_has_an_internal_nofollow_link_or_a_login_link(): void
    {
        foreach (['g', 'current'] as $design) {
            config(['creatium.design' => $design]);
            foreach ($this->pages() as $path) {
                $response = $this->get($path);
                if ($design === 'current' && $response->status() === 404 && str_starts_with($path, '/uslugi/')) {
                    continue; // the service slugs differ between the designs
                }
                $this->assertSame(200, $response->status(), "$design $path");
                $html = $response->getContent();
                $this->assertDoesNotMatchRegularExpression('/<a\b[^>]*rel="[^"]*nofollow/i', $html, "$design $path has a nofollow link");
                $this->assertStringNotContainsString('href="http://localhost/login"', $html, "$design $path links to the team login");
            }
        }
    }

    public function test_every_link_on_g_pages_has_text_or_an_image_with_alt_and_no_image_has_an_empty_alt_unless_hidden(): void
    {
        foreach ($this->pages() as $path) {
            $xp = $this->dom($this->get($path)->assertOk()->getContent());

            foreach ($xp->query('//a[@href]') as $a) {
                $text = trim(preg_replace('/\s+/u', ' ', $a->textContent));
                $hasAlt = $xp->query('.//img[normalize-space(@alt) != ""]', $a)->length > 0;
                $this->assertTrue($text !== '' || $hasAlt, "$path has a link without text or alt: " . $a->getAttribute('href'));
            }

            foreach ($xp->query('//img') as $img) {
                if (trim($img->getAttribute('alt')) !== '') {
                    continue;
                }
                $hidden = $img->getAttribute('aria-hidden') === 'true' || $xp->query('ancestor::*[@aria-hidden="true"]', $img)->length > 0;
                $this->assertTrue($hidden && $img->hasAttribute('alt'), "$path has an image with an empty alt that is not hidden: " . $img->getAttribute('src'));
            }
        }
    }

    public function test_g_images_all_carry_a_real_alt_on_the_checked_pages(): void
    {
        foreach ($this->pages() as $path) {
            $xp = $this->dom($this->get($path)->assertOk()->getContent());
            foreach ($xp->query('//img[not(@alt) or normalize-space(@alt) = ""]') as $img) {
                $this->fail("$path image without alt text: " . $img->getAttribute('src'));
            }
        }
    }

    public function test_the_phone_bar_call_link_has_hidden_text_and_no_aria_label(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<a class="g-bar-call"(?:(?!aria-label)[^>])*>.*<span class="g-sr">Обадете се<\/span><\/a>/su', $html);
    }

    public function test_blog_page_two_has_its_own_title_description_and_canonical_and_page_one_links_to_the_clean_address(): void
    {
        $this->posts(11);
        $first = $this->get('/blog')->assertOk()->getContent();
        $second = $this->get('/blog?page=2')->assertOk()->getContent();

        $title = fn (string $html) => html_entity_decode(preg_match('#<title>(.*?)</title>#su', $html, $m) ? $m[1] : '');
        $desc = fn (string $html) => html_entity_decode(preg_match('#<meta name="description" content="([^"]*)"#u', $html, $m) ? $m[1] : '');

        $this->assertStringEndsWith(', страница 2', preg_replace('/ \| Creatium Lab$/u', '', $title($second)));
        $this->assertStringContainsString('Страница 2.', $desc($second));
        $this->assertNotSame($desc($first), $desc($second));
        $this->assertLessThanOrEqual(155, mb_strlen($desc($second)));
        $this->assertStringContainsString('<link rel="canonical" href="http://localhost/blog?page=2">', $second);

        // The "newer" link on page 2 is /blog, not /blog?page=1.
        $this->assertMatchesRegularExpression('#href="http://localhost/blog" rel="prev"#', $second);
        $this->assertStringNotContainsString('page=1', $second);
    }

    public function test_blog_page_two_in_english_says_page_n(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            Post::create([
                'title' => ['bg' => "Статия $i", 'en' => "Article $i"], 'slug' => ['bg' => "statia-$i", 'en' => "article-$i"],
                'excerpt' => ['bg' => "Описание $i", 'en' => "Description of article number $i for the page in Google."], 'body' => ['bg' => 'Текст.', 'en' => 'Text.'],
                'status' => 'published', 'published_at' => now()->subDays($i),
            ]);
        }
        $html = $this->get('/en/blog?page=2')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<title>[^<]*, page 2[^<]*</title>#u', $html);
        $this->assertMatchesRegularExpression('#<meta name="description" content="[^"]*Page 2\.#u', $html);
    }

    public function test_the_description_fitter_cuts_at_a_sentence_then_a_word_and_never_ends_on_a_dash_or_colon(): void
    {
        $short = 'Кратък текст.';
        $this->assertSame($short, Snippet::fit($short));

        $text = str_repeat('Това е едно изречение с достатъчно думи вътре. ', 8);
        $fit = Snippet::fit($text);
        $this->assertLessThanOrEqual(155, mb_strlen($fit));
        $this->assertStringEndsWith('.', $fit);
        $this->assertStringNotContainsString('…', $fit);

        $words = trim(str_repeat('дума ', 80));
        $fit = Snippet::fit($words);
        $this->assertLessThanOrEqual(155, mb_strlen($fit));
        $this->assertStringEndsWith('…', $fit);
        $this->assertStringNotContainsString('дум…', $fit, 'cut on a word boundary');

        $fit = Snippet::fit(str_repeat('дума, ', 40) . 'край: още - нещо и още много думи накрая стигат до края на реда и го минават');
        $this->assertDoesNotMatchRegularExpression('/[,;:\-–—]…?$/u', $fit);
    }

    public function test_a_post_without_a_meta_description_gets_a_cut_excerpt_in_the_page_and_in_the_admin_preview(): void
    {
        $long = 'Първото изречение на статията е достатъчно дълго, за да обясни за какво е текстът и защо си струва да го прочетете. '
            . 'Второто изречение добавя още подробности, които излизат извън границата на описанието и затова не трябва да се виждат в Google.';
        Post::create([
            'title' => ['bg' => 'Дълга статия'], 'slug' => ['bg' => 'dalga-statia'], 'excerpt' => ['bg' => $long], 'body' => ['bg' => 'Текст.'],
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $post = Post::first();

        $effective = PostSeo::effectiveDescription($post, 'bg');
        $this->assertLessThanOrEqual(155, mb_strlen($effective));
        $this->assertStringEndsWith('прочетете.', $effective);

        $html = $this->get('/blog/dalga-statia')->assertOk()->getContent();
        $this->assertStringContainsString('<meta name="description" content="' . e($effective) . '">', $html);

        // An own description is kept as written.
        $post->update(['meta_description' => ['bg' => 'Собствено описание.']]);
        $this->assertSame('Собствено описание.', PostSeo::effectiveDescription($post->fresh(), 'bg'));
    }

    public function test_the_page_descriptions_named_by_the_crawl_stay_within_150_characters_in_both_languages(): void
    {
        foreach (['/proverka-na-sait', '/proekti', '/vaprosi', '/en/website-check', '/en/projects', '/en/about', '/en/faq'] as $path) {
            $response = $this->get($path);
            if ($response->status() === 404) {
                continue;
            }
            $html = $response->getContent();
            preg_match('#<meta name="description" content="([^"]*)"#u', $html, $m);
            $this->assertLessThanOrEqual(150, mb_strlen(html_entity_decode($m[1] ?? '')), "$path description");
        }
    }

    public function test_a_title_loses_the_site_suffix_when_the_whole_title_would_pass_60_characters(): void
    {
        foreach (['/paketi', '/en/packages', '/proekti', '/za-nas', '/vaprosi'] as $path) {
            $response = $this->get($path);
            if ($response->status() !== 200) {
                continue;
            }
            preg_match('#<title>(.*?)</title>#su', $response->getContent(), $m);
            $this->assertLessThanOrEqual(60, mb_strlen(html_entity_decode($m[1])), "$path title");
        }
    }
}
