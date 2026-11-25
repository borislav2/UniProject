<?php

namespace Tests\Feature;

use App\Models\OutreachSetting;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Support\Geo\LlmsFull;
use App\Support\Schema;
use App\Support\Seo\IndexNow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** GEO pass (24.11.2026): snippet directives, llms-full.txt, IndexNow, answer-first posts, Markdown twins, entity data. */
class GeoTest extends TestCase
{
    use RefreshDatabase;

    private const ROBOTS = '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['creatium.blog_enabled' => true]);
    }

    private function admin(): User
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = User::factory()->create(['name' => 'Борислав']);
        $user->roles()->attach(\App\Models\Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    private function design(string $design): void
    {
        config(['creatium.design' => $design, 'creatium.gtm_id' => 'GTM-TEST123']);
    }

    private function makePost(string $slug = 'geo-statia', array $extra = []): Post
    {
        return Post::create($extra + [
            'title' => ['bg' => 'Как да ви намерят в ChatGPT'],
            'slug' => ['bg' => $slug],
            'excerpt' => ['bg' => 'Кратък отговор на въпроса, без излишни думи.'],
            'body' => ['bg' => "## Първо\n\nТова е текстът на статията с няколко думи в него.\n\nВторият абзац."],
            'status' => 'published',
            'published_at' => now()->subDays(10),
        ]);
    }

    private function jsonLd(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);

        return array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    // 1. Snippet directives

    public function test_indexable_pages_get_the_snippet_robots_meta_in_both_designs_and_noindex_pages_keep_noindex(): void
    {
        $this->makePost();
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/', '/uslugi', '/paketi', '/za-nas', '/vaprosi', '/kontakti', '/blog', '/blog/geo-statia', '/en/about', '/en/faq'] as $path) {
                $this->get($path)->assertOk()->assertSee(self::ROBOTS, false);
            }
            $this->assertSame(1, substr_count($this->get('/')->getContent(), 'name="robots"'));
        }

        $this->design('current');
        // /en/blog has no English posts yet, the g_only service is not public in the current design.
        $this->get('/en/blog')->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false)->assertDontSee(self::ROBOTS, false);
        $slug = config('creatium.service_pages.software.slug');
        $this->get('/uslugi/' . $slug)->assertOk()->assertSee('content="noindex, follow"', false)->assertDontSee(self::ROBOTS, false);
        // Errors, the demo sites and the presentation are noindex and never get the snippet directives.
        $this->get('/nqma-takava-stranitsa')->assertNotFound()->assertSee('content="noindex"', false)->assertDontSee(self::ROBOTS, false);
        $demo = config('creatium.demos')[0]['slug'];
        $this->get('/demo/' . $demo)->assertOk()->assertSee('noindex', false)->assertDontSee(self::ROBOTS, false);
        $this->get('/prezentatsiya')->assertOk()->assertSee('noindex', false)->assertDontSee(self::ROBOTS, false);
    }

    public function test_the_design_preview_stays_noindex(): void
    {
        $this->design('current');
        $admin = $this->admin();
        $html = $this->actingAs($admin)->withSession(['design' => 'g'])->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<meta name="robots" content="noindex">', $html);
        $this->assertStringNotContainsString(self::ROBOTS, $html);
    }

    // 2. llms-full.txt

    public function test_llms_full_is_complete_plain_markdown_in_both_languages_and_both_designs(): void
    {
        $this->makePost();
        Post::create([
            'title' => ['bg' => 'Втора статия', 'en' => 'Second article'], 'slug' => ['bg' => 'vtora', 'en' => 'second'],
            'excerpt' => ['bg' => 'Описание: с двоеточие и – тире.', 'en' => 'English summary of the second article.'],
            'body' => ['bg' => 'Текст.', 'en' => 'Text.'], 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            Cache::flush();
            foreach (['/llms-full.txt' => 'bg', '/en/llms-full.txt' => 'en'] as $path => $locale) {
                $response = $this->get($path)->assertOk();
                $this->assertStringContainsString('charset=UTF-8', $response->headers->get('Content-Type'));
                $this->assertMatchesRegularExpression('~^(text/markdown|text/plain)~', $response->headers->get('Content-Type'));
                $body = $response->getContent();
                $this->assertStringStartsWith('# Creatium Lab', $body);

                // No Blade or PHP left over, no HTML.
                $this->assertDoesNotMatchRegularExpression('/\{\{|\}\}|@php|@foreach|@if\b|<\?php|<\/?(div|p|span|a)\b/', $body, "$design $path");

                // Who, team, services, prices, FAQ, industries and posts, all from the content.
                foreach (array_column(config($locale === 'en' ? 'creatium_en.team' : 'creatium.team'), 'name') as $name) {
                    $this->assertStringContainsString($name, $body, "$design $path team");
                }
                $this->assertStringContainsString(site('contact.email') ?? config('creatium.contact.email'), $body);
                foreach (array_slice((array) site('service_pages'), 0, 3) as $service) {
                    $this->assertStringContainsString($service['title'], $body);
                    $this->assertStringContainsString($service['lead'], $body);
                    $this->assertStringContainsString($service['includes'][0], $body);
                }
                $this->assertStringContainsString(site('questions')[0]['q'], $body);
                $this->assertStringContainsString(site('questions')[0]['a'], $body);
                $this->assertStringContainsString('500', $body);
                $this->assertStringContainsString('800', $body);
                $this->assertStringContainsString($locale === 'en' ? 'monthly subscription' : 'месечен абонамент', $body);
                $this->assertStringNotContainsString($locale === 'en' ? 'Втора статия' : 'Second article', $body);
                $this->assertStringContainsString($locale === 'en' ? '[Second article](http://localhost/en/blog/second)' : '[Втора статия](http://localhost/blog/vtora)', $body);
                $this->assertStringContainsString($locale === 'en' ? 'English summary of the second article' : 'Описание', $body);
                $this->assertStringContainsString($locale === 'en' ? 'founders and owners' : 'Основатели и собственици', $body);
                if ($locale === 'bg') {
                    $this->assertStringContainsString('/izrabotka-na-sait-za-', $body);
                }
            }
            // English file: no Cyrillic outside names, URLs and the shared team text is out of scope, but the headings are English.
            $this->assertStringContainsString('## Packages and prices', $this->get('/en/llms-full.txt')->getContent());
        }
    }

    public function test_llms_full_follows_the_copy_rules_and_states_the_same_prices_as_llms_txt(): void
    {
        $this->makePost('prosta', ['excerpt' => ['bg' => 'Първо: второ – трето.']]);
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            Cache::flush();
            foreach (['/llms-full.txt', '/en/llms-full.txt'] as $path) {
                $offences = [];
                foreach (explode("\n", $this->get($path)->getContent()) as $line) {
                    if (str_starts_with($line, '#')) {
                        continue;
                    }
                    $line = preg_replace('/^- /', '', $line);
                    $line = preg_replace('/\]\([^)]*\)/', ']', $line);
                    $offences = array_merge($offences, PlainCopyTest::offences($line));
                }
                $this->assertSame([], $offences, "$design $path");
            }

            // The price lines of /llms.txt appear unchanged in /llms-full.txt (one monthly price model).
            $llms = $this->get('/llms.txt')->getContent();
            $full = $this->get('/llms-full.txt')->getContent();
            preg_match('/## Пакети и цени\n\n(.*?)\n- Всички пакети/s', $llms, $m);
            $this->assertNotEmpty($m[1]);
            foreach (explode("\n", $m[1]) as $priceLine) {
                $this->assertStringContainsString($priceLine, $full, $design);
            }
            $this->assertStringContainsString('[Всичко на едно място в обикновен текст](http://localhost/llms-full.txt)', $llms);
            $this->assertStringContainsString('(http://localhost/en/llms-full.txt)', $llms);
        }
    }

    public function test_llms_full_is_cached_for_an_hour_and_flushed_when_a_post_is_saved(): void
    {
        $this->assertSame(3600, LlmsFull::TTL);
        $post = $this->makePost();
        $this->assertStringContainsString('Как да ви намерят в ChatGPT', $this->get('/llms-full.txt')->getContent());
        $this->assertTrue(Cache::has(LlmsFull::cacheKey('bg')));

        $post->update(['title' => ['bg' => 'Нов заглавен текст']]);
        $this->assertFalse(Cache::has(LlmsFull::cacheKey('bg')));
        $this->assertStringContainsString('Нов заглавен текст', $this->get('/llms-full.txt')->getContent());

        $post->delete();
        $this->assertFalse(Cache::has(LlmsFull::cacheKey('bg')));
        $this->assertStringNotContainsString('Нов заглавен текст', $this->get('/llms-full.txt')->getContent());
    }

    public function test_llms_full_lists_at_most_fifty_posts_and_robots_does_not_block_it(): void
    {
        for ($i = 1; $i <= 52; $i++) {
            $this->makePost("statia-$i", ['title' => ['bg' => "Статия $i"], 'published_at' => now()->subHours($i)]);
        }
        $body = $this->get('/llms-full.txt')->getContent();
        $this->assertSame(50, preg_match_all('~^- \[Статия \d+\]~m', $body));
        $this->assertStringContainsString('[Статия 1](', $body);
        $this->assertStringNotContainsString('[Статия 52](', $body);
        $robots = $this->get('/robots.txt')->getContent();
        $this->assertStringNotContainsString('llms', $robots);
    }

    // 3. IndexNow

    private function fakeIndexNow(mixed $response): void
    {
        app()->forgetInstance(\Illuminate\Http\Client\Factory::class);
        Http::clearResolvedInstances();
        Http::fake(['api.indexnow.org/*' => $response]);
    }

    private function indexNowOn(): void
    {
        config(['creatium.indexnow.enabled' => true, 'creatium.indexnow.key' => str_repeat('ab', 16)]);
    }

    public function test_the_key_file_serves_the_key_only(): void
    {
        $key = config('creatium.indexnow.key');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $key);
        $response = $this->get("/$key.txt")->assertOk();
        $this->assertSame($key, $response->getContent());
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $this->get('/' . str_repeat('0', 32) . '.txt')->assertNotFound();
        $this->assertFalse((bool) config('creatium.indexnow.enabled'), 'off in phpunit.xml');
    }

    public function test_submit_posts_the_json_with_host_key_location_and_urls(): void
    {
        $this->indexNowOn();
        $this->fakeIndexNow(Http::response('', 200));

        $this->assertTrue(IndexNow::submit(['http://localhost/a', 'http://localhost/b', 'http://localhost/a']));
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.indexnow.org/indexnow' && $request->method() === 'POST'
                && $request['host'] === 'localhost' && $request['key'] === str_repeat('ab', 16)
                && $request['keyLocation'] === 'http://localhost/' . str_repeat('ab', 16) . '.txt'
                && $request['urlList'] === ['http://localhost/a', 'http://localhost/b'];
        });
        $state = IndexNow::state();
        $this->assertNull($state['last_error']);
        $this->assertSame(2, $state['last_count']);
        $this->assertSame(200, $state['last_status']);
    }

    public function test_submit_never_throws_and_records_the_error(): void
    {
        $this->indexNowOn();
        $this->fakeIndexNow(Http::response('', 403));
        $this->assertFalse(IndexNow::submit(['http://localhost/a']));
        $this->assertSame('IndexNow върна 403', IndexNow::state()['last_error']);

        $this->fakeIndexNow(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));
        $this->assertFalse(IndexNow::submit(['http://localhost/a']));
        $this->assertStringContainsString('timeout', IndexNow::state()['last_error']);

        $this->fakeIndexNow(Http::response('', 202));
        $this->assertTrue(IndexNow::submit(['http://localhost/a']));
        $this->assertNull(IndexNow::state()['last_error']);
    }

    public function test_submit_does_nothing_when_disabled_and_splits_large_lists(): void
    {
        Http::fake();
        $this->assertFalse(IndexNow::submit(['http://localhost/a']));
        Http::assertNothingSent();

        $this->indexNowOn();
        $this->fakeIndexNow(Http::response('', 200));
        $urls = array_map(fn ($i) => "http://localhost/p$i", range(1, 10001));
        $this->assertTrue(IndexNow::submit($urls));
        Http::assertSentCount(2);
    }

    public function test_new_visible_posts_are_submitted_once_with_their_english_urls(): void
    {
        $this->indexNowOn();
        $this->fakeIndexNow(Http::response('', 200));
        $both = $this->makePost('dvuezichna', ['title' => ['bg' => 'Две', 'en' => 'Two'], 'slug' => ['bg' => 'dvuezichna', 'en' => 'bilingual'], 'body' => ['bg' => 'Т', 'en' => 'T']]);
        $bg = $this->makePost('samo-bg');
        $draft = $this->makePost('cherna', ['status' => 'draft']);
        $future = $this->makePost('badeshta', ['published_at' => now()->addDay()]);

        $this->assertSame(2, IndexNow::submitNewPosts());
        Http::assertSent(fn (Request $r) => $r['urlList'] === ['http://localhost/blog/dvuezichna', 'http://localhost/en/blog/bilingual', 'http://localhost/blog/samo-bg']
            || $r['urlList'] === ['http://localhost/blog/samo-bg', 'http://localhost/blog/dvuezichna', 'http://localhost/en/blog/bilingual']);
        $this->assertNotNull($both->fresh()->indexnow_at);
        $this->assertNotNull($bg->fresh()->indexnow_at);
        $this->assertNull($draft->fresh()->indexnow_at);
        $this->assertNull($future->fresh()->indexnow_at);

        $this->fakeIndexNow(Http::response('', 200));
        $this->assertSame(0, IndexNow::submitNewPosts());
        Http::assertNothingSent();
    }

    public function test_a_failed_submit_leaves_posts_for_the_next_try(): void
    {
        $this->indexNowOn();
        $this->fakeIndexNow(Http::response('', 500));
        $post = $this->makePost();
        $this->assertSame(0, IndexNow::submitNewPosts());
        $this->assertNull($post->fresh()->indexnow_at);
    }

    public function test_a_page_visit_submits_new_posts_at_most_every_ten_minutes(): void
    {
        $this->indexNowOn();
        $this->fakeIndexNow(Http::response('', 200));
        $post = $this->makePost();

        $this->get('/vaprosi')->assertOk();
        $this->assertNotNull($post->fresh()->indexnow_at);
        Http::assertSentCount(1);

        $later = $this->makePost('vtora-novina');
        $this->get('/vaprosi')->assertOk();
        $this->assertNull($later->fresh()->indexnow_at, 'the 10 minute lock holds');
        Http::assertSentCount(1);

        Cache::forget('indexnow:tick');
        $this->get('/vaprosi')->assertOk();
        $this->assertNotNull($later->fresh()->indexnow_at);

        // POST requests and a disabled IndexNow never submit.
        config(['creatium.indexnow.enabled' => false]);
        Cache::forget('indexnow:tick');
        $third = $this->makePost('treta');
        $this->get('/vaprosi')->assertOk();
        $this->assertNull($third->fresh()->indexnow_at);
    }

    public function test_the_command_submits_new_posts_or_every_sitemap_url(): void
    {
        $this->artisan('creatium:indexnow')->assertFailed();

        $this->indexNowOn();
        $this->fakeIndexNow(Http::response('', 200));
        $post = $this->makePost();
        $this->artisan('creatium:indexnow')->assertSuccessful();
        $this->assertNotNull($post->fresh()->indexnow_at);

        $this->fakeIndexNow(Http::response('', 200));
        $this->artisan('creatium:indexnow', ['--all' => true])->assertSuccessful();
        Http::assertSent(function (Request $r) {
            return in_array('http://localhost', $r['urlList'], true) || in_array('http://localhost/', $r['urlList'], true)
                ? in_array('http://localhost/blog/geo-statia', $r['urlList'], true) && in_array('http://localhost/uslugi', $r['urlList'], true) && ! collect($r['urlList'])->contains(fn ($u) => str_ends_with($u, '.md'))
                : false;
        });

        $this->fakeIndexNow(Http::response('', 500));
        $this->artisan('creatium:indexnow', ['--all' => true])->assertFailed();
    }

    public function test_system_health_has_an_indexnow_row(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/zdrave')->assertOk()->assertSee('IndexNow')->assertSee('Изключен (INDEXNOW_ENABLED)');

        $this->indexNowOn();
        OutreachSetting::put(IndexNow::STATE_KEY, ['last_error' => 'IndexNow върна 403']);
        $this->actingAs($admin)->get('/admin/zdrave')->assertSee('IndexNow върна 403')->assertSee('php artisan creatium:indexnow');
    }

    // 4. Answer first posts in design G

    public function test_g_post_shows_the_short_answer_dates_and_a_complete_blogposting(): void
    {
        $this->design('g');
        $category = PostCategory::create(['name' => ['bg' => 'Видимост'], 'slug' => ['bg' => 'vidimost']]);
        $post = $this->makePost('geo-statia', ['post_category_id' => $category->id, 'focus_keyword' => ['bg' => 'видимост в ChatGPT']]);
        $post->forceFill(['updated_at' => now()->subDays(2)])->saveQuietly();

        $html = $this->get('/blog/geo-statia')->assertOk()->assertSee('Накратко')->assertSee('Публикувано')->assertSee('Обновено')->getContent();
        $this->assertLessThan(strpos($html, 'class="g-prose g-article"'), strpos($html, 'data-tldr'));
        $this->assertGreaterThan(strpos($html, '<h1'), strpos($html, 'data-tldr'));
        $this->assertStringContainsString('Кратък отговор на въпроса, без излишни думи.', substr($html, strpos($html, 'data-tldr'), 400));
        $this->assertSame(1, substr_count($html, 'class="g-tldr-text"'));
        $this->assertStringNotContainsString('g-post-lead', $html, 'the excerpt is not repeated below the title');

        $ld = collect($this->jsonLd($html))->firstWhere('@type', 'BlogPosting');
        $this->assertSame($post->published_at->toAtomString(), $ld['datePublished']);
        $this->assertSame($post->fresh()->updated_at->toAtomString(), $ld['dateModified']);
        $this->assertSame(['@type' => 'WebPage', '@id' => 'http://localhost/blog/geo-statia'], $ld['mainEntityOfPage']);
        $this->assertSame('bg', $ld['inLanguage']);
        $this->assertSame(13, $ld['wordCount']);
        $this->assertSame('Видимост', $ld['articleSection']);
        $this->assertSame('видимост в ChatGPT', $ld['keywords']);
        $this->assertSame('видимост в ChatGPT', $ld['about']['name']);
        $this->assertSame(Schema::websiteId(), $ld['isPartOf']['@id']);
        $this->assertSame('WebSite', $ld['isPartOf']['@type']);
        $this->assertSame('Как да ви намерят в ChatGPT', $ld['headline']);
    }

    public function test_updated_date_shows_only_when_more_than_a_day_after_publishing(): void
    {
        $this->design('g');
        $post = $this->makePost();
        $post->forceFill(['updated_at' => $post->published_at->copy()->addHours(5)])->saveQuietly();
        $this->get('/blog/geo-statia')->assertOk()->assertSee('Публикувано')->assertDontSee('Обновено');

        $post->forceFill(['updated_at' => $post->published_at->copy()->addDays(3)])->saveQuietly();
        $this->get('/blog/geo-statia')->assertSee('Обновено');
    }

    public function test_blogposting_is_complete_in_the_current_design_too_and_without_keyword_or_category(): void
    {
        $this->design('current');
        $this->makePost();
        $ld = collect($this->jsonLd($this->get('/blog/geo-statia')->assertOk()->getContent()))->firstWhere('@type', 'BlogPosting');
        foreach (['datePublished', 'dateModified', 'mainEntityOfPage', 'inLanguage', 'wordCount', 'isPartOf'] as $key) {
            $this->assertArrayHasKey($key, $ld);
        }
        $this->assertArrayNotHasKey('keywords', $ld);
        $this->assertArrayNotHasKey('articleSection', $ld);
    }

    public function test_g_post_content_is_not_changed(): void
    {
        $this->design('g');
        $this->makePost();
        $this->get('/blog/geo-statia')->assertSee('<h2>Първо</h2>', false)->assertSee('Това е текстът на статията с няколко думи в него.', false);
    }

    // 5. Markdown twins

    public function test_a_post_has_a_markdown_twin_for_visible_posts_only(): void
    {
        $author = User::factory()->create(['name' => 'Владимир Цончев']);
        $post = $this->makePost('geo-statia', ['user_id' => $author->id, 'title' => ['bg' => 'Заглавие', 'en' => 'Title'], 'slug' => ['bg' => 'geo-statia', 'en' => 'geo-article'], 'excerpt' => ['bg' => 'Кратко.', 'en' => 'Short.'], 'body' => ['bg' => "Тяло.\n\n## Подзаглавие", 'en' => 'Body.']]);
        $this->makePost('cherna', ['status' => 'draft']);
        $this->makePost('badeshta', ['published_at' => now()->addDay()]);

        $response = $this->get('/blog/geo-statia.md')->assertOk();
        $this->assertStringContainsString('text/markdown', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertSame('<http://localhost/blog/geo-statia>; rel="canonical"', $response->headers->get('Link'));
        $body = $response->getContent();
        $this->assertStringStartsWith("# Заглавие\n", $body);
        $this->assertStringContainsString('- Публикувана ' . $post->published_at->toDateString(), $body);
        $this->assertStringContainsString('- Автор Владимир Цончев', $body);
        $this->assertStringContainsString('> **Накратко** Кратко.', $body);
        $this->assertStringContainsString("Тяло.\n\n## Подзаглавие", $body);

        $en = $this->get('/en/blog/geo-article.md')->assertOk()->getContent();
        $this->assertStringStartsWith("# Title\n", $en);
        $this->assertStringContainsString('> **In short** Short.', $en);

        $this->get('/blog/cherna.md')->assertNotFound();
        $this->get('/blog/badeshta.md')->assertNotFound();
        $this->get('/blog/nyama.md')->assertNotFound();
        $this->get('/en/blog/geo-statia.md')->assertNotFound(); // no English version of this slug
        config(['creatium.blog_enabled' => false]);
        $this->get('/blog/geo-statia.md')->assertNotFound();
    }

    public function test_the_html_page_links_its_twin_and_the_twin_stays_out_of_the_sitemap_and_clean_path_redirects(): void
    {
        $this->makePost();
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            $this->get('/blog/geo-statia')->assertOk()->assertSee('<link rel="alternate" type="text/markdown" href="http://localhost/blog/geo-statia.md">', false);
        }
        $sitemap = $this->get('/sitemap.xml')->getContent();
        $this->assertStringContainsString('<loc>http://localhost/blog/geo-statia</loc>', $sitemap);
        $this->assertStringNotContainsString('.md', $sitemap);
        // The test client trims slashes, so the middleware is called with the raw request: the .md address is left alone, a trailing slash goes to it.
        $middleware = new \App\Http\Middleware\RedirectToCleanPath();
        $run = fn (string $uri) => $middleware->handle(\Illuminate\Http\Request::create($uri, 'GET'), fn () => response('next'));
        $this->assertSame('next', $run('/blog/geo-statia.md')->getContent());
        $this->assertSame('http://localhost/blog/geo-statia.md', $run('/blog/geo-statia.md/')->headers->get('Location'));
        $this->get('/blog/geo-statia')->assertOk()->assertSee('<h2>Първо</h2>', false);
    }

    // 6. Entity strength

    public function test_organization_has_entities_slogan_catalog_and_knows_about_in_the_page_language(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/' => 'bg', '/en' => 'en'] as $path => $locale) {
                $html = $this->get($path)->assertOk()->getContent();
                $org = collect($this->jsonLd($html))->firstWhere('@type', 'ProfessionalService');
                $this->assertNotNull($org, "$design $path");

                $about = $this->jsonLd($this->get($locale === 'en' ? '/en/about' : '/za-nas')->getContent());
                $persons = collect($about)->flatMap(fn ($b) => $b['@graph'] ?? [])->where('@type', 'Person')->pluck('@id')->all();
                $this->assertCount(2, $org['founder']);
                $this->assertSame(array_slice($persons, 0, 2), array_column($org['founder'], '@id'));
                $this->assertSame(array_slice($persons, 2), array_column($org['employee'], '@id'));
                $names = collect($about)->flatMap(fn ($b) => $b['@graph'] ?? [])->where('@type', 'Person')->pluck('name')->all();
                $this->assertSame(array_slice($names, 0, 2), array_column($org['founder'], 'name'));

                $this->assertSame(config('creatium.home_hero.title') === null ? null : site('home_hero')['title'], $org['slogan']);
                foreach (['Google Ads', 'Meta Ads', 'Google Shopping'] as $topic) {
                    $this->assertContains($topic, $org['knowsAbout']);
                }
                $this->assertContains($locale === 'en' ? 'AI agents' : 'AI агенти', $org['knowsAbout']);
                $this->assertContains($locale === 'en' ? 'Technical SEO' : 'Техническо SEO', $org['knowsAbout']);
                $this->assertContains($locale === 'en' ? 'Email automation' : 'Имейл автоматизации', $org['knowsAbout']);
                $this->assertContains($locale === 'en' ? 'Generative engine optimisation (GEO)' : 'Оптимизация за AI търсене (GEO)', $org['knowsAbout']);

                $catalog = $org['hasOfferCatalog'];
                $this->assertSame('OfferCatalog', $catalog['@type']);
                $expected = collect((array) site('service_pages'))->reject(fn ($s) => ! empty($s['g_only']) && $design !== 'g')->count();
                $this->assertCount($expected, $catalog['itemListElement']);
                $first = $catalog['itemListElement'][0]['itemOffered'];
                $this->assertSame(site('service_pages')['website']['title'], $first['name']);
                $this->assertStringStartsWith($locale === 'en' ? 'http://localhost/en/services/' : 'http://localhost/uslugi/', $first['url']);

                // Nothing invented: no founding date, ratings or reviews.
                foreach (['foundingDate', 'aggregateRating', 'review'] as $forbidden) {
                    $this->assertArrayNotHasKey($forbidden, $org);
                }
            }
        }
    }

    // 7. Sitemap

    public function test_sitemap_lastmod_is_the_real_post_date_and_never_before_publishing(): void
    {
        $post = $this->makePost();
        $post->forceFill(['updated_at' => now()->subDays(3)])->saveQuietly();
        $old = $this->makePost('stara', ['published_at' => now()->subDays(20)]);
        $old->forceFill(['updated_at' => now()->subDays(30)])->saveQuietly(); // before it was published: not a real date

        $sitemap = $this->get('/sitemap.xml')->getContent();
        $block = fn (string $loc) => substr($sitemap, strpos($sitemap, "<loc>$loc</loc>"), 200);
        $this->assertStringContainsString('<lastmod>' . $post->fresh()->updated_at->toAtomString() . '</lastmod>', $block('http://localhost/blog/geo-statia'));
        $this->assertStringContainsString('<lastmod>' . $old->fresh()->published_at->toAtomString() . '</lastmod>', $block('http://localhost/blog/stara'));
        $this->assertStringContainsString('<lastmod>' . $post->fresh()->updated_at->toAtomString() . '</lastmod>', $block('http://localhost/blog'));
    }

    // Follow-up: authors by category, facts for assistants, disambiguation

    private function categoryPost(string $categorySlug, ?User $user, string $slug = 'avtor-statia'): Post
    {
        $category = PostCategory::firstOrCreate(['slug->bg' => $categorySlug], ['name' => ['bg' => 'Категория ' . $categorySlug], 'slug' => ['bg' => $categorySlug]]);

        return $this->makePost($slug, ['post_category_id' => $category->id, 'user_id' => $user?->id]);
    }

    public function test_the_author_comes_from_the_category_for_the_shared_account_and_for_no_user(): void
    {
        $this->assertSame([
            'google-i-ai-tarsene' => 'Владимир', 'platena-reklama' => 'Владимир', 'sajtove' => 'Борислав',
            'poddrazhka-i-sigurnost' => 'Борислав', 'imeyl-kampanii' => 'Мария', 'onlayn-targoviya' => 'Мария',
        ], config('creatium.blog_authors'));
        $shared = User::factory()->create(['name' => 'Админ']);
        $about = collect($this->jsonLd($this->get('/za-nas')->getContent()))->flatMap(fn ($b) => $b['@graph'] ?? [])->where('@type', 'Person')->keyBy('name');

        $expected = [
            'google-i-ai-tarsene' => 'Владимир Цончев', 'platena-reklama' => 'Владимир Цончев', 'sajtove' => 'Борислав Костадинов',
            'poddrazhka-i-sigurnost' => 'Борислав Костадинов', 'imeyl-kampanii' => 'Мария Флорова', 'onlayn-targoviya' => 'Мария Флорова',
        ];
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach ([$shared, null] as $user) {
                foreach ($expected as $slug => $name) {
                    Post::query()->delete();
                    $this->categoryPost($slug, $user);
                    $html = $this->get('/blog/avtor-statia')->assertOk()->getContent();
                    $ld = collect($this->jsonLd($html))->firstWhere('@type', 'BlogPosting');
                    $this->assertSame('Person', $ld['author']['@type'], "$design $slug");
                    $this->assertSame($name, $ld['author']['name']);
                    $this->assertSame($about[$name]['@id'], $ld['author']['@id'], 'the same @id as on /za-nas');
                    $this->assertNotEmpty($ld['author']['sameAs']);
                    $this->assertSame($about[$name]['sameAs'], $ld['author']['sameAs']);
                    // The visible byline: name and role, linking to /za-nas.
                    $this->assertMatchesRegularExpression('~<a href="http://localhost/za-nas" rel="author">' . preg_quote($name, '~') . '</a>, ~u', $html);
                    $this->assertStringContainsString('- Автор ' . $name, $this->get('/blog/avtor-statia.md')->getContent());
                }
            }
        }
    }

    public function test_a_team_members_own_account_keeps_the_person_whatever_the_category(): void
    {
        $maria = User::factory()->create(['name' => 'Мария Флорова']);
        $this->categoryPost('sajtove', $maria);
        $ld = collect($this->jsonLd($this->get('/blog/avtor-statia')->getContent()))->firstWhere('@type', 'BlogPosting');
        $this->assertSame('Мария Флорова', $ld['author']['name']);
        $this->assertSame('Мария Флорова', \App\Support\Schema::postAuthor(Post::first())['name']);
    }

    public function test_an_unknown_or_missing_category_falls_back_to_the_company_without_a_byline(): void
    {
        $shared = User::factory()->create(['name' => 'Админ']);
        $this->categoryPost('nyama-takava', $shared, 'nepoznata');
        $this->makePost('bez-kategoriya', ['user_id' => $shared->id]);
        foreach (['/blog/nepoznata', '/blog/bez-kategoriya'] as $path) {
            foreach (['current', 'g'] as $design) {
                $this->design($design);
                $html = $this->get($path)->assertOk()->getContent();
                $ld = collect($this->jsonLd($html))->firstWhere('@type', 'BlogPosting');
                $this->assertSame('Organization', $ld['author']['@type']);
                $this->assertSame('Creatium Lab', $ld['author']['name']);
                $this->assertStringNotContainsString('data-byline', $html);
            }
        }
    }

    public function test_the_admin_seo_preview_shows_the_category_author(): void
    {
        $shared = User::factory()->create(['name' => 'Админ']);
        $post = $this->categoryPost('platena-reklama', $shared);
        $this->assertSame('Владимир Цончев (от екипа)', \App\Support\Seo\PostSeo::authorLabel($post->load('category', 'author')));
        $post2 = $this->categoryPost('nyama-takava', $shared, 'druga');
        $this->assertSame('Creatium Lab (фирмата)', \App\Support\Seo\PostSeo::authorLabel($post2->load('category', 'author')));
    }

    public function test_g_byline_uses_the_g_role_and_links_to_the_team_page(): void
    {
        $this->design('g');
        $this->categoryPost('imeyl-kampanii', null);
        $member = collect(config('creatium.team'))->firstWhere('name', 'Мария Флорова');
        $role = $member['g_role'] ?? $member['role'];
        $this->get('/blog/avtor-statia')->assertSee('rel="author">Мария Флорова</a>, ' . e($role), false);
    }

    public function test_llms_files_open_with_the_disambiguation_line_and_llms_full_has_the_evaluation_facts(): void
    {
        $bg = 'Creatium Lab е агенция за уеб сайтове и дигитален маркетинг в София, България (не е марка за хранителни добавки).';
        $en = 'Creatium Lab is a web design and digital marketing agency in Sofia, Bulgaria (not a food supplement brand).';
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            Cache::flush();
            $this->assertStringContainsString("\n\n$bg\n\n", $this->get('/llms.txt')->getContent());
            $full = $this->get('/llms-full.txt')->getContent();
            $this->assertStringContainsString("\n\n$bg\n\n", $full);
            $this->assertStringContainsString("\n\n$en\n\n", $this->get('/en/llms-full.txt')->getContent());

            $section = substr($full, strpos($full, '## Как да ни оцените'));
            $section = substr($section, 0, strpos($section, "\n## ", 5));
            $plans = config('creatium.plans');
            $this->assertStringContainsString('Домейнът, профилите и достъпите са на ваше име', $section);
            $this->assertStringContainsString($plans['guarantee']['text'], $section);
            $this->assertStringContainsString('месечен отчет', $section);
            $this->assertStringContainsString('директно на Meta', $section);
            $this->assertStringContainsString('Фиксирана цена в офертата', $section);
            $this->assertStringContainsString('Екипът е от ' . count(config('creatium.team')) . ' души', $section);
            $this->assertStringContainsString('Намираме се в София, България', $section);
            $this->assertDoesNotMatchRegularExpression('/минимален срок|основана|година на основаване|отзив/ui', $section, 'nothing invented');

            $enFull = $this->get('/en/llms-full.txt')->getContent();
            $this->assertStringContainsString('## How to evaluate us', $enFull);
            $this->assertStringContainsString('The domain, profiles and access are in your name', $enFull);
            $this->assertStringContainsString('The team is ' . count(config('creatium_en.team')) . ' people', $enFull);
            $this->assertStringContainsString('We are based in Sofia, Bulgaria', $enFull);
        }
    }

    public function test_organization_has_an_alternate_name_and_says_what_it_is(): void
    {
        foreach (['/' => ['Creatium Lab агенция', 'агенция за уеб сайтове и дигитален маркетинг в София'], '/en' => ['Creatium Lab agency', 'web design and digital marketing agency in Sofia']] as $path => [$alt, $text]) {
            $org = collect($this->jsonLd($this->get($path)->getContent()))->firstWhere('@type', 'ProfessionalService');
            $this->assertSame($alt, $org['alternateName']);
            $this->assertStringContainsString($text, $org['description']);
            $this->assertSame([], PlainCopyTest::offences($org['description']));
        }
    }

    // 8. Bing

    public function test_bing_verification_is_untouched(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<meta name="msvalidate.01" content="A903648D9DC376E5148713950DAF4D25">', false)
            ->assertSee('<meta name="msvalidate.01" content="B58AE7F5ECB72BF413DDE741CBF38418">', false);
        $xml = file_get_contents(public_path('BingSiteAuth.xml'));
        $this->assertStringContainsString('A903648D9DC376E5148713950DAF4D25', $xml);
        $this->assertStringContainsString('B58AE7F5ECB72BF413DDE741CBF38418', $xml);
    }
}
