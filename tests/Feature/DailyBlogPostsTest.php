<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\StarterBlogPosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The daily articles (database/data/daily-*.php, three a day): own publish_at, the owners' copy rules
 * (no dashes, no colons, full sentences) and only links to real pages.
 */
class DailyBlogPostsTest extends TestCase
{
    use RefreshDatabase;

    private function files(): array
    {
        return array_map('basename', glob(database_path('data/daily-*.php')));
    }

    public function test_publish_at_is_used_in_sofia_time_and_posts_show_by_themselves(): void
    {
        foreach ($this->files() as $file) {
            StarterBlogPosts::import(file: $file);
        }
        $first = StarterBlogPosts::data($this->files()[0])['posts'][0];
        $post = Post::where('slug->bg', $first['slug'])->firstOrFail();

        $this->assertTrue($post->published_at->equalTo(Carbon::parse($first['publish_at'], 'Europe/Sofia')));
        $this->assertSame('published', $post->status);

        Carbon::setTestNow(Carbon::parse($first['publish_at'], 'Europe/Sofia')->subMinute());
        $this->assertFalse(Post::visible('bg')->whereKey($post->id)->exists(), 'Not visible before its time');
        Carbon::setTestNow(Carbon::parse($first['publish_at'], 'Europe/Sofia')->addMinute());
        $this->assertTrue(Post::visible('bg')->whereKey($post->id)->exists(), 'Visible after its time');
        Carbon::setTestNow();
    }

    public function test_articles_follow_the_owners_copy_rules(): void
    {
        $this->assertNotEmpty($this->files());
        $allowedLinks = ['/proverka-na-sait', '/uslugi/izrabotka-na-sait', '/uslugi/seo-i-ai-vidimost', '/uslugi/google-profil-i-otzivi',
            '/uslugi/imeyl-marketing', '/uslugi/poddrazhka-i-hosting', '/uslugi/podgotovka-za-google-shopping', '/uslugi/ai-agenti', '/paketi', '/kontakti'];
        $perDay = [];
        $slugs = [];

        foreach ($this->files() as $file) {
            $data = StarterBlogPosts::data($file);
            foreach ($data['posts'] as $post) {
                $id = $file . ' ' . $post['slug'];
                $this->assertArrayHasKey($post['category'], $data['categories'], $id);
                $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $post['slug'], $id);
                $this->assertNotContains($post['slug'], $slugs, "Duplicate slug $id");
                $slugs[] = $post['slug'];
                $perDay[substr($post['publish_at'], 0, 10)][] = $id;

                // Links: only real pages, never blog posts; then strip URLs before the punctuation checks.
                preg_match_all('/\]\(([^)]+)\)/', $post['body'], $links);
                $this->assertGreaterThanOrEqual(1, count(array_filter($links[1], fn ($l) => str_starts_with($l, '/'))), "$id has no internal link");
                foreach ($links[1] as $link) {
                    // News articles may also link to their real source (external https); internal links stay on the allowed pages.
                    if (str_starts_with($link, 'https://') && ! str_contains($link, 'creatiumlab.com')) {
                        continue;
                    }
                    $this->assertContains($link, $allowedLinks, "$id links to $link");
                }
                $text = $post['title'] . "\n" . $post['excerpt'] . "\n" . preg_replace('/\]\([^)]+\)/', ']', $post['body']);

                $this->assertDoesNotMatchRegularExpression('/[–—]|\s-\s|^\s*-\s/mu', $text, "$id has a dash");
                $this->assertDoesNotMatchRegularExpression('/(?<!\d):(?!\d)/u', $text, "$id has a colon");
                $this->assertStringNotContainsString('=', $text, "$id has =");
                $this->assertDoesNotMatchRegularExpression('/[*-] /m', preg_replace('/\*\*[^*]+\*\*/', '', $post['body']), "$id has bullet fragments");
                foreach (['В днешния дигитален свят', 'Нека разгледаме', 'В заключение', 'Не е тайна, че'] as $cliche) {
                    $this->assertStringNotContainsString($cliche, $text, "$id uses „{$cliche}“");
                }
                $this->assertLessThanOrEqual(160, mb_strlen($post['excerpt']), "$id excerpt too long");
                $words = str_word_count(strip_tags($post['body']), 0, 'АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЬЮЯабвгдежзийклмнопрстуфхцчшщъьюя');
                $this->assertGreaterThanOrEqual(600, $words, "$id is too short ($words words)");
            }
        }

        foreach ($perDay as $day => $ids) {
            $this->assertCount(3, $ids, "$day should have three articles");
        }
    }

    public function test_every_daily_article_has_a_cover_and_a_g_version(): void
    {
        foreach ($this->files() as $file) {
            foreach (StarterBlogPosts::data($file)['posts'] as $post) {
                $this->assertSame('images/blog/' . $post['slug'] . '.webp', $post['cover'] ?? null, $post['slug']);
                $this->assertFileExists(public_path($post['cover']), $post['slug']);
                $this->assertFileExists(public_path('images/blog/g/' . $post['slug'] . '.webp'), $post['slug']);
                $this->assertFileExists(public_path('images/blog/g/' . $post['slug'] . '-640.webp'), $post['slug']);
            }
        }
    }

    public function test_fill_covers_fills_blank_and_never_overwrites(): void
    {
        $file = $this->files()[0];
        $posts = StarterBlogPosts::data($file)['posts'];
        // Already imported without a cover, as on the live site.
        StarterBlogPosts::import(file: $file);
        Post::query()->update(['cover_image' => null]);
        $kept = Post::where('slug->bg', $posts[0]['slug'])->firstOrFail();
        $kept->update(['cover_image' => 'uploads/blog/own.webp']);

        $count = StarterBlogPosts::fillCovers($file);

        $this->assertSame(count($posts) - 1, $count);
        $this->assertSame('uploads/blog/own.webp', $kept->fresh()->cover_image, 'An existing cover is never overwritten');
        $second = Post::where('slug->bg', $posts[1]['slug'])->firstOrFail();
        $this->assertSame($posts[1]['cover'], $second->cover_image);
        $this->assertSame(0, StarterBlogPosts::fillCovers($file), 'A second run changes nothing');
    }
}
