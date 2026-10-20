<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\StarterBlogPosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StarterBlogAndDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_is_on_by_default(): void
    {
        $this->assertTrue((require base_path('config/creatium.php'))['blog_enabled']);
    }

    public function test_starter_posts_are_published_three_now_and_the_rest_every_two_to_four_days(): void
    {
        $start = Carbon::parse('2026-10-14 15:00', 'UTC');
        $this->travelTo($start);
        config(['creatium.blog_enabled' => true]);

        $added = StarterBlogPosts::import($start);
        $this->assertSame(count(StarterBlogPosts::data()['posts']), $added);
        $this->assertGreaterThanOrEqual(15, $added);
        $this->assertSame(0, StarterBlogPosts::import($start), 'Importing twice adds nothing');

        $this->assertSame(StarterBlogPosts::PUBLISH_NOW, Post::visible('bg')->count());
        $dates = Post::orderBy('published_at')->pluck('published_at')->values();
        for ($i = StarterBlogPosts::PUBLISH_NOW; $i < $dates->count(); $i++) {
            $gap = $dates[$i - 1]->copy()->startOfDay()->diffInDays($dates[$i]->copy()->startOfDay());
            $this->assertContains((int) round($gap), [2, 3, 4], "Gap before post $i");
            $this->assertSame('09:00', $dates[$i]->copy()->timezone('Europe/Sofia')->format('H:i'));
        }

        $page = $this->get('/blog')->assertOk();
        foreach (Post::visible('bg')->get() as $post) {
            $page->assertSee($post->tr('title'));
        }
        $scheduled = Post::orderByDesc('published_at')->first();
        $this->get('/blog/' . $scheduled->tr('slug'))->assertNotFound();
        $page->assertDontSee($scheduled->tr('title'));

        // Every article becomes visible on its own once its date comes, and every one renders.
        $this->travelTo($dates->last()->copy()->addMinute());
        $this->assertSame($added, Post::visible('bg')->count());
        foreach (Post::all() as $post) {
            $this->get('/blog/' . $post->tr('slug'))->assertOk()->assertSee($post->tr('title'))->assertDontSee('<h1>', false);
        }
    }

    public function test_links_inside_the_starter_posts_lead_to_existing_pages(): void
    {
        $links = [];
        foreach (StarterBlogPosts::data()['posts'] as $post) {
            preg_match_all('/\]\((\/[^)]*)\)/', $post['body'], $m);
            $links = array_merge($links, $m[1]);
            $this->assertLessThanOrEqual(160, mb_strlen($post['excerpt']), $post['slug']);
            $this->assertArrayHasKey($post['category'], StarterBlogPosts::data()['categories']);
        }
        $this->assertNotEmpty($links);
        foreach (array_unique($links) as $link) {
            $this->get($link)->assertOk();
        }
    }

    public function test_english_blog_points_to_the_bulgarian_articles_while_none_are_translated(): void
    {
        config(['creatium.blog_enabled' => true]);
        StarterBlogPosts::import(now());

        $this->get('/en/blog')->assertOk()->assertSee('Our articles are in Bulgarian for now')->assertSee('href="' . url('/blog') . '"', false);
    }

    public function test_every_demo_explains_the_industry_problems_package_and_faq(): void
    {
        $packages = collect(config('creatium.plans.items'))->pluck('name')->all();

        foreach (config('creatium.demos') as $demo) {
            $page = $this->get('/demo/' . $demo['slug'])->assertOk()
                ->assertSee('Какво решава този сайт за бранша')
                ->assertSee('Проблемите на бранша')
                ->assertSee('Как ги решаваме')
                ->assertSee('Въпроси и отговори')
                ->assertSee('href="#za-bransha"', false);

            foreach (['problems', 'solutions', 'faq'] as $key) {
                $this->assertGreaterThanOrEqual(3, count($demo[$key]), "{$demo['slug']}.$key");
                foreach ($demo[$key] as [$title]) {
                    $page->assertSee($title);
                }
            }

            $package = $demo['package']['name'];
            if ($package) {
                $this->assertContains($package, $packages, $demo['slug']);
                $page->assertSee($package);
            } else {
                $page->assertSee('Индивидуална оферта');
            }
        }
    }

    public function test_summit_articles_come_in_as_hidden_drafts_with_working_links(): void
    {
        $added = StarterBlogPosts::import(file: 'summit-posts.php', status: 'draft');
        $data = StarterBlogPosts::data('summit-posts.php');

        $this->assertSame(count($data['posts']), $added);
        $this->assertSame(0, StarterBlogPosts::import(file: 'summit-posts.php', status: 'draft'));
        $this->assertSame(count($data['posts']), \App\Models\Post::where('status', 'draft')->whereNull('published_at')->count());

        foreach ($data['posts'] as $post) {
            $this->get('/blog/' . $post['slug'])->assertNotFound();
            preg_match_all('/\]\((\/[^)]*)\)/', $post['body'], $links);
            foreach ($links[1] as $link) {
                $this->get($link)->assertOk();
            }
            $this->assertStringContainsString('eCommerce Annual Summit 2026', $post['body'], $post['title']);
        }
    }

    public function test_summit_drafts_can_be_scheduled_without_touching_ones_already_handled_in_admin(): void
    {
        config(['creatium.blog_enabled' => true]);
        StarterBlogPosts::import(file: 'summit-posts.php', status: 'draft');
        $slugs = array_column(StarterBlogPosts::data('summit-posts.php')['posts'], 'slug');
        $handled = Post::where('slug->bg', $slugs[3])->first();
        $handled->update(['status' => 'published', 'published_at' => now()->subDay()]);

        $at = now()->addDay();
        $this->assertSame(3, StarterBlogPosts::publishDraftsAt($at, 'summit-posts.php'));
        $this->assertSame(0, StarterBlogPosts::publishDraftsAt($at, 'summit-posts.php'));
        $this->assertTrue($handled->fresh()->published_at->lt(now()));

        $this->get('/blog/' . $slugs[0])->assertNotFound();
        $this->travelTo($at->copy()->addMinutes(5));
        foreach ($slugs as $slug) {
            $this->get('/blog/' . $slug)->assertOk();
        }
        $this->assertSame($slugs[0], Post::visible('bg')->orderByDesc('published_at')->first()->tr('slug'));
    }

    public function test_expanded_posts_replace_only_untouched_texts_and_add_covers(): void
    {
        StarterBlogPosts::import(now());
        StarterBlogPosts::import(file: 'summit-posts.php', status: 'draft');
        $all = array_merge(StarterBlogPosts::data()['posts'], StarterBlogPosts::data('summit-posts.php')['posts']);

        // Собствениците са редактирали една статия и са сложили своя снимка на друга.
        $edited = Post::where('slug->bg', 'otzivi-v-google')->first();
        $edited->update(['body' => ['bg' => 'Нашият текст.']]);
        $withPhoto = Post::where('slug->bg', 'kakvo-e-geo')->first();
        $withPhoto->update(['cover_image' => 'uploads/blog/nasha-snimka.jpg']);

        $done = StarterBlogPosts::expand();
        $this->assertSame(count($all) - 1, $done['bodies']);
        $this->assertSame(count($all) - 1, $done['covers']);
        $this->assertSame(['bodies' => 0, 'covers' => 0], StarterBlogPosts::expand(), 'Running twice changes nothing');

        $this->assertSame('Нашият текст.', $edited->fresh()->body['bg']);
        $this->assertSame('uploads/blog/nasha-snimka.jpg', $withPhoto->fresh()->cover_image);

        $order = array_flip(array_column(StarterBlogPosts::data()['posts'], 'slug'));
        foreach ($all as $original) {
            $file = database_path("data/blog-expanded/{$original['slug']}.md");
            $this->assertFileExists($file);
            $this->assertFileExists(public_path("images/blog/{$original['slug']}.webp"));
            $text = file_get_contents($file);
            $this->assertGreaterThan(2 * mb_strlen($original['body']) * 0.8, mb_strlen($text), $original['slug']);

            // Линковете водят до страници на сайта или до по-ранна статия (вече публикувана), никога до чернова или по-късна.
            preg_match_all('/\]\((\/[^)]*)\)/', $text, $links);
            foreach ($links[1] as $link) {
                if (str_starts_with($link, '/blog/')) {
                    $target = substr($link, 6);
                    $this->assertArrayHasKey($target, $order, $link);
                    $this->assertLessThan($order[$original['slug']] ?? -1, $order[$target], "{$original['slug']} → $link");
                    continue;
                }
                $this->get($link)->assertOk();
            }
        }

        $post = Post::where('slug->bg', 'kakvo-tryabva-da-ima-saytat-na-malak-biznes')->first();
        $this->assertSame('images/blog/kakvo-tryabva-da-ima-saytat-na-malak-biznes.webp', $post->cover_image);
        config(['creatium.blog_enabled' => true]);
        $this->get('/blog/' . $post->tr('slug'))->assertOk()
            ->assertSee('images/blog/kakvo-tryabva-da-ima-saytat-na-malak-biznes.webp', false)
            ->assertSee('Какво НЕ е нужно в началото');
    }
}
