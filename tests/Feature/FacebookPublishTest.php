<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Support\Facebook\FacebookPublisher;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FacebookPublishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['creatium.blog_enabled' => true, 'services.facebook.page_id' => '123', 'services.facebook.page_token' => 'page-token', 'services.facebook.page_name' => 'Creatium Lab']);
    }

    private function article(string $slug, string $publishedAt, string $status = 'published'): Post
    {
        return Post::create([
            'title' => ['bg' => 'Статия ' . $slug],
            'slug' => ['bg' => $slug],
            'excerpt' => ['bg' => 'Кратко описание на ' . $slug . '.'],
            'body' => ['bg' => 'Текст.'],
            'status' => $status,
            'published_at' => $publishedAt,
        ]);
    }

    private function fakeGraph(): void
    {
        $n = 0;
        Http::fake(['graph.facebook.com/*' => function () use (&$n) {
            return Http::response(['id' => '123_' . (++$n)]);
        }]);
    }

    public function test_new_posts_go_out_at_once_and_older_ones_one_a_day(): void
    {
        Carbon::setTestNow('2026-10-27 08:00:00'); // 10:00 в София
        $this->fakeGraph();
        $old1 = $this->article('stara-1', '2026-10-10 06:00:00');
        $old2 = $this->article('stara-2', '2026-10-12 06:00:00');
        $new = $this->article('nova', '2026-10-27 06:00:00');
        $this->article('nasrochena', '2026-10-29 06:00:00');
        $this->article('chernova', '2026-10-01 06:00:00', 'draft');
        $publisher = app(FacebookPublisher::class);

        $this->assertTrue($publisher->run()->is($new), 'The new post goes first');
        $this->assertTrue($publisher->run()->is($old1), 'Then the oldest of the backlog');
        $this->assertNull($publisher->run(), 'Only one older post a day');
        $this->assertSame('123_1', $new->fresh()->facebook_post_id);

        Http::assertSent(fn (Request $request) => $request->url() === 'https://graph.facebook.com/123/feed'
            && $request['access_token'] === 'page-token'
            && $request['link'] === url('/blog/nova') . '?utm_source=facebook&utm_medium=social&utm_campaign=blog'
            && $request['message'] === "Статия nova\n\nКратко описание на nova.");

        Carbon::setTestNow('2026-10-28 19:30:00'); // 21:30: извън часовете за по-старите
        $this->assertNull($publisher->run());
        Carbon::setTestNow('2026-10-29 07:00:00'); // 09:00 на следващия ден; насрочената вече е излязла и е нова
        $this->assertSame('nasrochena', $publisher->run()->tr('slug', 'bg'));
        $this->assertTrue($publisher->run()->is($old2));
        $this->assertNull($publisher->run());
        Carbon::setTestNow();
    }

    public function test_an_invalid_token_pauses_publishing_and_the_admin_can_retry(): void
    {
        Carbon::setTestNow('2026-10-27 08:00:00');
        $post = $this->article('nova', '2026-10-27 06:00:00');
        Http::fake(['graph.facebook.com/*' => Http::sequence()
            ->push(['error' => ['message' => 'Error validating access token', 'code' => 190]], 400)
            ->push(['id' => '123_9'])]);

        $this->assertNull(app(FacebookPublisher::class)->run());
        $this->assertStringContainsString('Error validating access token (код 190)', $post->fresh()->facebook_error);
        $this->assertSame('Error validating access token', FacebookPublisher::paused());

        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'admin')->value('id'));
        $this->actingAs($admin)->get('/admin/posts')->assertSee('Публикуването във Facebook е спряно')->assertSee('Опитай пак');

        $this->actingAs($admin)->post("/admin/posts/{$post->id}/facebook")->assertSessionHas('success', 'Статията е публикувана във Facebook.');
        $this->assertSame('123_9', $post->fresh()->facebook_post_id);
        $this->assertNull(FacebookPublisher::paused());
        $this->actingAs($admin)->get('/admin/posts')->assertSee('https://www.facebook.com/123_9', false)->assertSee('Свързано с Facebook страницата');
        $this->actingAs($admin)->post("/admin/posts/{$post->id}/facebook")->assertSessionHas('error', 'Статията вече е във Facebook.');
        Carbon::setTestNow();
    }

    public function test_visits_trigger_publishing_at_most_every_ten_minutes_and_nothing_without_a_page(): void
    {
        Carbon::setTestNow('2026-10-27 08:00:00');
        $this->fakeGraph();
        $this->article('nova', '2026-10-27 06:00:00');
        $this->article('vtora', '2026-10-27 07:00:00');

        $this->get('/blog')->assertOk();
        $this->get('/blog')->assertOk();
        Http::assertSentCount(1);

        Cache::forget('facebook:tick');
        config(['services.facebook.page_token' => null]);
        $this->get('/blog')->assertOk();
        Http::assertSentCount(1);
        $this->artisan('creatium:facebook-publish')->expectsOutputToContain('Facebook не е свързан')->assertFailed();
        Carbon::setTestNow();
    }

    public function test_connect_exchanges_the_token_and_stores_the_page_in_env(): void
    {
        $env = base_path('.env');
        $backup = is_file($env) ? file_get_contents($env) : null;
        Http::fake([
            'graph.facebook.com/oauth/access_token*' => Http::response(['access_token' => 'long-user']),
            'graph.facebook.com/me/accounts*' => Http::response(['data' => [['id' => '777', 'name' => 'Creatium Lab', 'access_token' => 'forever-page', 'tasks' => ['CREATE_CONTENT']]]]),
        ]);

        try {
            $this->artisan('creatium:facebook-connect')
                ->expectsQuestion('App ID (developers.facebook.com → вашето приложение → App settings → Basic)', '42')
                ->expectsQuestion('App Secret (същата страница, „Show“; не се показва)', 'secret')
                ->expectsQuestion('User access token от Graph API Explorer (не се показва)', 'short-user')
                ->expectsOutputToContain('Свързано със страницата „Creatium Lab“')
                ->assertSuccessful();

            $written = file_get_contents($env);
            $this->assertStringContainsString('FACEBOOK_PAGE_ID="777"', $written);
            $this->assertStringContainsString('FACEBOOK_PAGE_TOKEN="forever-page"', $written);
            $this->assertStringNotContainsString('secret', $written);
            Http::assertSent(fn (Request $request) => str_contains($request->url(), 'fb_exchange_token=short-user') && str_contains($request->url(), 'client_id=42'));
            Http::assertSent(fn (Request $request) => str_contains($request->url(), 'me/accounts') && str_contains($request->url(), 'access_token=long-user'));
        } finally {
            $backup === null ? @unlink($env) : file_put_contents($env, $backup);
        }
    }
}
