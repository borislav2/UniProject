<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Geo\LlmsFull;
use App\Support\Seo\IndexNow;
use App\Support\StarterBlogPosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Every blog article also exists in English (owners, 25.11.2026): the texts sit in database/data/en/{Bulgarian slug}.md and are
 * merged into the data files as title_en, slug_en, excerpt_en and body_en. They follow the same copy rules as the Bulgarian ones.
 */
class EnglishBlogPostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['creatium.blog_enabled' => true]);
    }

    private function files(): array
    {
        return array_map('basename', glob(database_path('data/*.php')));
    }

    private function dailyFiles(): array
    {
        return array_map('basename', glob(database_path('data/daily-*.php')));
    }

    /** @return array<string, array> every post of every data file, by Bulgarian slug */
    private function allPosts(): array
    {
        $all = [];
        foreach ($this->files() as $file) {
            foreach (StarterBlogPosts::data($file)['posts'] as $post) {
                $all[$post['slug']] = $post + ['_file' => $file];
            }
        }

        return $all;
    }

    public function test_every_data_post_has_all_four_english_fields(): void
    {
        $posts = $this->allPosts();
        $this->assertGreaterThanOrEqual(49, count($posts));

        foreach ($posts as $slug => $post) {
            foreach (['title_en', 'slug_en', 'excerpt_en', 'body_en'] as $field) {
                $this->assertNotEmpty($post[$field] ?? null, "$slug has no $field");
            }
        }
        foreach ($this->dailyFiles() as $file) {
            foreach (StarterBlogPosts::data($file)['posts'] as $post) {
                $this->assertNotEmpty($post['title_en'] ?? null, $post['slug'] . ' (daily) has no English');
            }
        }
    }

    public function test_every_english_text_file_belongs_to_a_post(): void
    {
        $posts = $this->allPosts();
        foreach (glob(database_path('data/en/*.md')) as $path) {
            $this->assertArrayHasKey(basename($path, '.md'), $posts, basename($path) . ' belongs to no post');
        }
    }

    public function test_english_titles_slugs_and_excerpts_have_the_right_shape(): void
    {
        $slugs = [];
        foreach ($this->allPosts() as $id => $post) {
            $this->assertGreaterThanOrEqual(45, mb_strlen($post['title_en']), "$id title is short (" . mb_strlen($post['title_en']) . ')');
            $this->assertLessThanOrEqual(65, mb_strlen($post['title_en']), "$id title is long (" . mb_strlen($post['title_en']) . ')');
            $this->assertGreaterThanOrEqual(120, mb_strlen($post['excerpt_en']), "$id excerpt is short (" . mb_strlen($post['excerpt_en']) . ')');
            $this->assertLessThanOrEqual(155, mb_strlen($post['excerpt_en']), "$id excerpt is long (" . mb_strlen($post['excerpt_en']) . ')');
            $this->assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $post['slug_en'], $id);
            $this->assertLessThanOrEqual(80, strlen($post['slug_en']), "$id slug is long");
            $this->assertNotContains($post['slug_en'], $slugs, "Duplicate English slug $id");
            $slugs[] = $post['slug_en'];
            $this->assertDoesNotMatchRegularExpression('/\p{Cyrillic}/u', $post['title_en'] . $post['excerpt_en'] . $post['body_en'], "$id has Cyrillic text");
        }
    }

    public function test_english_articles_follow_the_owners_copy_rules(): void
    {
        $allowedInternal = ['/en/website-check', '/en/services', '/en/packages', '/en/contact', '/en/projects',
            '/en/services/website-design', '/en/services/seo-and-ai-visibility', '/en/services/google-business-profile',
            '/en/services/email-marketing', '/en/services/maintenance-and-hosting', '/en/services/google-shopping-readiness', '/en/services/ai-agents'];
        $posts = $this->allPosts();
        $englishSlugs = array_column($posts, 'slug_en');

        foreach ($posts as $id => $post) {
            preg_match_all('/\]\(([^)]+)\)/', $post['body_en'], $links);
            $internal = array_filter($links[1], fn ($l) => str_starts_with($l, '/'));
            $this->assertGreaterThanOrEqual(1, count($internal), "$id has no internal link");
            foreach ($links[1] as $link) {
                if (str_starts_with($link, 'https://')) {
                    continue;
                }
                if (str_starts_with($link, '/en/blog/')) {
                    $this->assertContains(substr($link, 9), $englishSlugs, "$id links to a post that does not exist ($link)");
                    $this->assertNotSame($post['slug_en'], substr($link, 9), "$id links to itself");
                    continue;
                }
                $this->assertContains($link, $allowedInternal, "$id links to $link");
            }

            $text = $post['title_en'] . "\n" . $post['excerpt_en'] . "\n" . preg_replace('/\]\([^)]+\)/', ']', $post['body_en']);
            $this->assertDoesNotMatchRegularExpression('/[–—]|\s-\s|^\s*-\s/mu', $text, "$id has a dash");
            $this->assertDoesNotMatchRegularExpression('/(?<!\d):(?!\d)/u', $text, "$id has a colon");
            $this->assertStringNotContainsString('=', $text, "$id has =");
            $this->assertStringNotContainsString('→', $text, "$id has an arrow");
            $this->assertDoesNotMatchRegularExpression('/[*-] /m', preg_replace('/\*\*[^*]+\*\*/', '', $post['body_en']), "$id has bullet fragments");
            $this->assertDoesNotMatchRegularExpression('/^\s*\|/m', $post['body_en'], "$id has a table");
            $this->assertDoesNotMatchRegularExpression('/\bfree (consultation|call|meeting)\b/i', $text, "$id calls a consultation free");
            foreach (['In today\'s digital world', 'Let us dive', 'In conclusion', 'It is no secret that', 'game changer'] as $cliche) {
                $this->assertStringNotContainsStringIgnoringCase($cliche, $text, "$id uses \"$cliche\"");
            }

            $headings = preg_match_all('/^## /m', $post['body_en']);
            $this->assertGreaterThanOrEqual(3, $headings, "$id has too few headings");
            $words = str_word_count(strip_tags($post['body_en']));
            $this->assertGreaterThanOrEqual(480, $words, "$id is too short ($words words)");
        }
    }

    public function test_import_gives_a_new_post_both_languages(): void
    {
        $file = $this->dailyFiles()[0];
        StarterBlogPosts::import(file: $file);
        $first = StarterBlogPosts::data($file)['posts'][0];

        $post = Post::where('slug->bg', $first['slug'])->firstOrFail();
        $this->assertSame($first['title_en'], $post->tr('title', 'en'));
        $this->assertSame($first['slug_en'], $post->tr('slug', 'en'));
        $this->assertSame($first['excerpt_en'], $post->tr('excerpt', 'en'));
        $this->assertSame(trim($first['body_en']), $post->tr('body', 'en'));
        $this->assertSame($first['title'], $post->tr('title', 'bg'));
        $this->assertSame(['bg', 'en'], $post->fresh()->locales());
    }

    public function test_fill_english_fills_blanks_only_and_never_overwrites(): void
    {
        $file = $this->dailyFiles()[0];
        $posts = StarterBlogPosts::data($file)['posts'];
        // Imported earlier without English, as on the live site.
        StarterBlogPosts::import(file: $file);
        Post::query()->get()->each(fn (Post $p) => $p->forceFill(['title' => ['bg' => $p->tr('title', 'bg')], 'slug' => ['bg' => $p->tr('slug', 'bg')],
            'excerpt' => ['bg' => $p->tr('excerpt', 'bg')], 'body' => ['bg' => $p->tr('body', 'bg')]])->save());
        $this->assertSame(['bg'], Post::first()->locales(), 'no English yet');

        // The owners wrote their own English for the first post and edited the Bulgarian title of the second.
        $own = Post::where('slug->bg', $posts[0]['slug'])->firstOrFail();
        $own->forceFill(['title' => ['bg' => $own->tr('title', 'bg'), 'en' => 'Our own English title'], 'slug' => ['bg' => $own->tr('slug', 'bg'), 'en' => 'our-own-slug'],
            'body' => ['bg' => $own->tr('body', 'bg'), 'en' => 'Our own English text.']])->save();
        $edited = Post::where('slug->bg', $posts[1]['slug'])->firstOrFail();
        $edited->forceFill(['title' => ['bg' => 'Заглавие, сменено от собствениците']])->save();
        // Another post already uses the English address of the third one.
        $taken = Post::where('slug->bg', $posts[2]['slug'])->firstOrFail();
        $other = Post::where('slug->bg', $posts[3]['slug'])->firstOrFail();
        $other->forceFill(['slug' => ['bg' => $other->tr('slug', 'bg'), 'en' => $posts[2]['slug_en']]])->save();

        $count = StarterBlogPosts::fillEnglish($file);

        $this->assertSame(count($posts) - 1, $count);
        $own = $own->fresh();
        $this->assertSame('Our own English title', $own->tr('title', 'en'), 'English written by the owners stays');
        $this->assertSame('our-own-slug', $own->tr('slug', 'en'));
        $this->assertSame('Our own English text.', $own->tr('body', 'en'));
        $this->assertSame($posts[0]['title'], $own->tr('title', 'bg'), 'Bulgarian is never changed');

        $edited = $edited->fresh();
        $this->assertSame('Заглавие, сменено от собствениците', $edited->tr('title', 'bg'), 'An edited Bulgarian title stays');
        $this->assertSame($posts[1]['title_en'], $edited->tr('title', 'en'));

        $this->assertSame($posts[2]['slug_en'] . '-2', $taken->fresh()->tr('slug', 'en'), 'A taken English slug is never used twice');
        $this->assertSame($posts[2]['slug_en'], $other->fresh()->tr('slug', 'en'));

        $this->assertSame(0, StarterBlogPosts::fillEnglish($file), 'A second run changes nothing');
        $slugs = Post::all()->map(fn (Post $p) => $p->tr('slug', 'en'))->filter();
        $this->assertSame($slugs->count(), $slugs->unique()->count());
    }

    public function test_fill_english_skips_posts_that_are_not_in_the_database(): void
    {
        $this->assertSame(0, StarterBlogPosts::fillEnglish($this->dailyFiles()[0]));
        $this->assertSame(0, Post::count());
    }

    private function importAll(): void
    {
        foreach ($this->files() as $file) {
            StarterBlogPosts::import(file: $file);
        }
    }

    public function test_english_post_page_renders_in_design_g_with_cover_byline_hreflang_and_json_ld(): void
    {
        config(['creatium.design' => 'g']);
        StarterBlogPosts::import(file: 'daily-2026-11-a.php');
        $data = StarterBlogPosts::data('daily-2026-11-a.php')['posts'][0];
        Post::where('slug->bg', $data['slug'])->update(['published_at' => now()->subDay()]);

        $html = $this->get('/en/blog/' . $data['slug_en'])->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="en"', $html);
        $this->assertStringContainsString($data['title_en'], $html);
        $this->assertStringContainsString('In short', $html, 'the short answer box');
        $this->assertStringContainsString('hreflang="bg" href="http://localhost/blog/' . $data['slug'] . '"', $html);
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/blog/' . $data['slug_en'] . '"', $html);
        $this->assertStringContainsString('rel="canonical" href="http://localhost/en/blog/' . $data['slug_en'] . '"', $html);
        $this->assertStringContainsString('/images/blog/g/' . $data['slug'], $html, 'the same cover is shared');
        $this->assertStringContainsString('/en/blog/' . $data['slug_en'] . '.md', $html, 'the markdown twin is linked');

        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);
        $ld = collect($m[1])->map(fn ($j) => json_decode($j, true))->flatten(1)->filter(fn ($x) => is_array($x));
        $posting = collect($m[1])->map(fn ($j) => json_decode($j, true))->first(fn ($x) => is_array($x) && ($x['@type'] ?? '') === 'BlogPosting');
        $this->assertNotNull($posting);
        $this->assertSame('en', $posting['inLanguage']);
        $this->assertSame($data['title_en'], $posting['headline']);

        // The author is the team member of the category (sajtove), shown with the English role.
        $team = collect(config('creatium_en.team'))->first(fn ($t) => str_starts_with($t['name'], 'Borislav'));
        $this->assertNotNull($team);
        $role = $team['g_role'] ?? $team['role'];
        $this->assertStringContainsString('rel="author">' . e($team['name']) . '</a>, ' . e($role), $html);
        $this->assertSame($team['name'], $posting['author']['name']);
    }

    public function test_english_post_page_renders_in_the_current_design_too(): void
    {
        config(['creatium.design' => 'current']);
        StarterBlogPosts::import(file: 'daily-2026-11-b.php');
        $data = StarterBlogPosts::data('daily-2026-11-b.php')['posts'][1];
        Post::query()->update(['published_at' => now()->subDay()]);

        $this->get('/en/blog/' . $data['slug_en'])->assertOk()->assertSee($data['title_en'], false)
            ->assertSee('hreflang="bg"', false)->assertSee('hreflang="en"', false);
    }

    public function test_the_markdown_twin_exists_in_english(): void
    {
        StarterBlogPosts::import(file: 'daily-2026-11-c.php');
        $data = StarterBlogPosts::data('daily-2026-11-c.php')['posts'][0];
        Post::query()->update(['published_at' => now()->subDay()]);

        $response = $this->get('/en/blog/' . $data['slug_en'] . '.md')->assertOk();
        $this->assertStringContainsString('text/markdown', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<http://localhost/en/blog/' . $data['slug_en'] . '>; rel="canonical"', $response->headers->get('Link'));
        $this->assertStringContainsString($data['title_en'], $response->getContent());
    }

    public function test_english_blog_is_indexable_listed_in_the_sitemap_and_in_llms_full(): void
    {
        $this->importAll();
        Post::query()->update(['published_at' => now()->subDays(5)]);
        $data = StarterBlogPosts::data('daily-2026-11-a.php')['posts'][0];
        Post::where('slug->bg', $data['slug'])->update(['published_at' => now()->subDay()]);
        LlmsFull::flush();

        $list = $this->get('/en/blog')->assertOk()->assertSee($data['title_en'], false)->getContent();
        $this->assertStringNotContainsString('noindex', $list, 'the English blog is indexable once posts have English');
        $this->assertStringContainsString('hreflang="en"', $list);

        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('<loc>http://localhost/en/blog/' . $data['slug_en'] . '</loc>', $sitemap);
        $this->assertStringContainsString('<loc>http://localhost/blog/' . $data['slug'] . '</loc>', $sitemap);
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/blog/' . $data['slug_en'] . '"', $sitemap);

        $full = $this->get('/en/llms-full.txt')->assertOk()->getContent();
        $this->assertStringContainsString($data['title_en'], $full);
        $this->assertStringContainsString('/en/blog/' . $data['slug_en'], $full);
        $this->assertStringNotContainsString('/blog/' . $data['slug'], $full, 'the English file lists English addresses only');
    }

    public function test_indexnow_submits_both_languages_of_an_english_post(): void
    {
        StarterBlogPosts::import(file: 'daily-2026-11-a.php');
        $post = Post::firstOrFail();
        $urls = IndexNow::postUrls($post);

        $this->assertContains('http://localhost/blog/' . $post->tr('slug', 'bg'), $urls);
        $this->assertContains('http://localhost/en/blog/' . $post->tr('slug', 'en'), $urls);
    }
}
