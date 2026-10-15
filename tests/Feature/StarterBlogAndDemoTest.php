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
}
