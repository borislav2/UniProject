<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Support\LinkedIn\LinkedInPublisher;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkedInPublishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-04 08:00:00'); // 10:00 в София
        config([
            'creatium.blog_enabled' => true,
            'services.linkedin.organization_id' => '777', 'services.linkedin.token' => 'li-token', 'services.linkedin.token_expires' => '2027-01-01',
            'services.linkedin.version' => '202608', 'services.linkedin.api_url' => 'https://api.linkedin.com/rest',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function article(string $slug, string $publishedAt, array $extra = []): Post
    {
        return Post::create($extra + [
            'title' => ['bg' => 'Статия ' . $slug],
            'slug' => ['bg' => $slug],
            'excerpt' => ['bg' => 'Кратко описание на ' . $slug . '.'],
            'body' => ['bg' => 'Текст.'],
            'status' => 'published',
            'published_at' => $publishedAt,
        ]);
    }

    private function fakeApi(): void
    {
        $n = 0;
        Http::fake(['api.linkedin.com/*' => function () use (&$n) {
            return Http::response('', 201, ['x-restli-id' => 'urn:li:share:' . (++$n)]);
        }]);
    }

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $admin;
    }

    public function test_it_posts_the_article_with_the_right_url_headers_and_body_and_stores_the_id_from_the_header(): void
    {
        $this->fakeApi();
        $post = $this->article('nova', '2026-11-04 06:00:00');

        $this->assertTrue(app(LinkedInPublisher::class)->run()->is($post));

        $post->refresh();
        $this->assertSame('urn:li:share:1', $post->linkedin_post_id);
        $this->assertNotNull($post->linkedin_posted_at);
        $this->assertNull($post->linkedin_error);

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.linkedin.com/rest/posts'
                && $request->header('Authorization') === ['Bearer li-token']
                && $request->header('LinkedIn-Version') === ['202608']
                && $request->header('X-Restli-Protocol-Version') === ['2.0.0']
                && $request->data() === [
                    'author' => 'urn:li:organization:777',
                    'commentary' => "Статия nova\n\nКратко описание на nova.",
                    'visibility' => 'PUBLIC',
                    'distribution' => ['feedDistribution' => 'MAIN_FEED', 'targetEntities' => [], 'thirdPartyDistributionChannels' => []],
                    'content' => ['article' => [
                        'source' => url('/blog/nova') . '?utm_source=linkedin&utm_medium=social&utm_campaign=blog',
                        'title' => 'Статия nova',
                        'description' => 'Кратко описание на nova.',
                    ]],
                    'lifecycleState' => 'PUBLISHED',
                    'isReshareDisabledByAuthor' => false,
                ];
        });
        $this->assertSame('https://www.linkedin.com/feed/update/urn:li:share:1', LinkedInPublisher::postUrl($post));
    }

    public function test_commentary_escapes_the_little_text_characters(): void
    {
        $this->assertSame('\\\\ \\| \\{ \\} \\@ \\[ \\] \\( \\) \\< \\> \\# \\* \\_ \\~', LinkedInPublisher::escape('\\ | { } @ [ ] ( ) < > # * _ ~'));
        $this->assertSame('Сайт за бизнеса ви (6 стъпки) #1', str_replace('\\', '', LinkedInPublisher::escape('Сайт за бизнеса ви (6 стъпки) #1')));

        $this->fakeApi();
        $this->article('spetsialna', '2026-11-04 06:00:00', ['title' => ['bg' => 'Сайт (или @магазин) #1 за 5*'], 'excerpt' => ['bg' => 'Вижте_това [тук] ~сега']]);
        app(LinkedInPublisher::class)->run();

        Http::assertSent(fn (Request $request) => $request['commentary'] === "Сайт \\(или \\@магазин\\) \\#1 за 5\\*\n\nВижте\\_това \\[тук\\] \\~сега"
            // Заглавието и описанието на картата не са в „little text“ и не се екранират.
            && $request['content']['article']['title'] === 'Сайт (или @магазин) #1 за 5*'
            && $request['content']['article']['description'] === 'Вижте_това [тук] ~сега');
    }

    public function test_new_posts_go_first_and_older_ones_one_a_day_and_only_visible_bulgarian_posts(): void
    {
        $this->fakeApi();
        $old1 = $this->article('stara-1', '2026-10-10 06:00:00');
        $old2 = $this->article('stara-2', '2026-10-12 06:00:00');
        $new = $this->article('nova', '2026-11-04 06:00:00');
        $this->article('nasrochena', '2026-11-06 06:00:00');
        $this->article('chernova', '2026-10-01 06:00:00', ['status' => 'draft']);
        $publisher = app(LinkedInPublisher::class);

        $this->assertTrue($publisher->run()->is($new));
        $this->assertTrue($publisher->run()->is($old1));
        $this->assertNull($publisher->run(), 'Only one older post a day');

        Carbon::setTestNow('2026-11-04 19:30:00'); // 21:30 в София
        $this->assertNull($publisher->run());
        Carbon::setTestNow('2026-11-05 07:00:00');
        $this->assertTrue($publisher->run()->is($old2));
        Http::assertSentCount(3);
    }

    public function test_a_401_pauses_publishing_for_twelve_hours_and_marks_the_post(): void
    {
        $post = $this->article('nova', '2026-11-04 06:00:00');
        $other = $this->article('vtora', '2026-11-04 07:00:00');
        Http::fake(['api.linkedin.com/*' => Http::response(['status' => 401, 'code' => 'REVOKED_ACCESS_TOKEN', 'message' => 'The token used in the request has been revoked by the user'], 401)]);

        $this->assertNull(app(LinkedInPublisher::class)->run());
        $this->assertStringContainsString('The token used in the request has been revoked by the user (код 401)', $post->fresh()->linkedin_error);
        $this->assertSame('The token used in the request has been revoked by the user', LinkedInPublisher::paused());
        $this->assertNull($post->fresh()->linkedin_post_id);

        // На паузата не се опитва нищо повече, и другата статия не е маркирана.
        $this->assertNull(app(LinkedInPublisher::class)->run());
        Http::assertSentCount(1);
        $this->assertNull($other->fresh()->linkedin_error);

        Carbon::setTestNow('2026-11-04 21:00:00'); // след 12 часа паузата изтича
        $this->assertNull(LinkedInPublisher::paused());
    }

    public function test_a_failed_post_is_not_retried_automatically_and_other_errors_do_not_pause(): void
    {
        $post = $this->article('nova', '2026-11-04 06:00:00');
        Http::fake(['api.linkedin.com/*' => Http::sequence()->push(['message' => 'Not enough permissions'], 403)->push('', 201, ['x-restli-id' => 'urn:li:share:9'])]);
        $publisher = app(LinkedInPublisher::class);

        $this->assertNull($publisher->run());
        $this->assertStringContainsString('Not enough permissions (код 403)', $post->fresh()->linkedin_error);
        $this->assertNull(LinkedInPublisher::paused());
        $this->assertNull($publisher->run(), 'Not retried by itself');
        Http::assertSentCount(1);

        $this->assertNull($publisher->publish($post->fresh()));
        $this->assertSame('urn:li:share:9', $post->fresh()->linkedin_post_id);
        $this->assertNull($post->fresh()->linkedin_error);
    }

    public function test_a_201_without_the_id_header_is_an_error(): void
    {
        $post = $this->article('nova', '2026-11-04 06:00:00');
        Http::fake(['api.linkedin.com/*' => Http::response('', 201)]);

        $this->assertNotNull(app(LinkedInPublisher::class)->publish($post));
        $this->assertNull($post->fresh()->linkedin_post_id);
        $this->assertNotNull($post->fresh()->linkedin_error);
    }

    public function test_nothing_is_sent_without_settings(): void
    {
        $this->fakeApi();
        $post = $this->article('nova', '2026-11-04 06:00:00');

        foreach (['services.linkedin.token', 'services.linkedin.organization_id'] as $key) {
            $old = config($key);
            config([$key => null]);
            $this->assertNull(app(LinkedInPublisher::class)->run());
            $this->assertNotNull(app(LinkedInPublisher::class)->publish($post));
            $this->get('/blog')->assertOk();
            Cache::forget('linkedin:tick');
            config([$key => $old]);
        }
        config(['services.linkedin.token' => null]);
        $this->artisan('creatium:linkedin-publish')->expectsOutputToContain('LinkedIn не е свързан')->assertFailed();
        Http::assertNothingSent();
        $this->assertNull($post->fresh()->linkedin_error, 'A missing setting does not mark the post');
    }

    public function test_an_expired_token_sends_nothing_and_the_last_day_still_works(): void
    {
        $this->fakeApi();
        $post = $this->article('nova', '2026-11-04 06:00:00');

        config(['services.linkedin.token_expires' => '2026-11-03']);
        $this->assertTrue(LinkedInPublisher::expired());
        $this->assertNull(app(LinkedInPublisher::class)->run());
        $this->assertStringContainsString('изтекъл', app(LinkedInPublisher::class)->publish($post));
        $this->get('/blog')->assertOk();
        $this->artisan('creatium:linkedin-publish')->expectsOutputToContain('изтекъл')->assertFailed();
        Http::assertNothingSent();
        $this->assertNull($post->fresh()->linkedin_error);

        config(['services.linkedin.token_expires' => '2026-11-04']);
        $this->assertFalse(LinkedInPublisher::expired());
        $this->assertSame(0, LinkedInPublisher::daysLeft());
        $this->assertTrue(app(LinkedInPublisher::class)->run()->is($post));

        config(['services.linkedin.token_expires' => '']);
        $this->assertNull(LinkedInPublisher::daysLeft());
        $this->assertFalse(LinkedInPublisher::expired());
    }

    public function test_visits_trigger_publishing_at_most_every_ten_minutes(): void
    {
        $this->fakeApi();
        $this->article('nova', '2026-11-04 06:00:00');
        $this->article('vtora', '2026-11-04 07:00:00');

        $this->get('/blog')->assertOk();
        $this->get('/blog')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_admin_button_publishes_a_post_and_blocks_repeats_and_hidden_posts(): void
    {
        $admin = $this->admin();
        Http::fake(['api.linkedin.com/*' => Http::sequence()
            ->push(['message' => 'Not enough permissions'], 403)
            ->push('', 201, ['x-restli-id' => 'urn:li:share:55'])]);
        $post = $this->article('nova', '2026-11-04 06:00:00');
        $draft = $this->article('chernova', '2026-11-04 06:00:00', ['status' => 'draft']);

        $this->post("/admin/posts/{$post->id}/linkedin")->assertRedirect('/login');
        $this->actingAs($admin)->post("/admin/posts/{$post->id}/linkedin")->assertSessionHas('error');
        $this->actingAs($admin)->get('/admin/posts')->assertSee('Опитай пак')->assertSee('Not enough permissions');

        $this->actingAs($admin)->post("/admin/posts/{$post->id}/linkedin")->assertSessionHas('success', 'Статията е публикувана в LinkedIn.');
        $this->assertSame('urn:li:share:55', $post->fresh()->linkedin_post_id);
        $this->actingAs($admin)->get('/admin/posts')->assertSee('https://www.linkedin.com/feed/update/urn:li:share:55', false)->assertSee('Свързано с LinkedIn страницата')->assertSee('Ключът изтича след');
        $this->actingAs($admin)->post("/admin/posts/{$post->id}/linkedin")->assertSessionHas('error', 'Статията вече е в LinkedIn.');
        $this->actingAs($admin)->post("/admin/posts/{$draft->id}/linkedin")->assertSessionHas('error', 'Статията още не е публикувана на сайта на български.');
        Http::assertSentCount(2);

    }

    public function test_blog_page_and_health_show_the_connection_state(): void
    {
        $admin = $this->admin();

        config(['services.linkedin.token' => null]);
        $this->actingAs($admin)->get('/admin/posts')->assertSee('LinkedIn не е свързан');
        $this->actingAs($admin)->get('/admin/zdrave')->assertOk()->assertSee('Блог в LinkedIn')->assertSee('Не е свързан: статиите не излизат в LinkedIn сами.');

        config(['services.linkedin.token' => 'li-token', 'services.linkedin.token_expires' => '2026-11-10']);
        $this->actingAs($admin)->get('/admin/posts')->assertSee('Ключът изтича след')->assertSee('creatium:linkedin-connect');
        $this->actingAs($admin)->get('/admin/zdrave')->assertSee('Ключът изтича след 6 дни: подновете го.')->assertSee('creatium:linkedin-connect');

        config(['services.linkedin.token_expires' => '2027-01-01']);
        $this->actingAs($admin)->get('/admin/zdrave')->assertSee('ключът изтича след 58 дни');

        config(['services.linkedin.token_expires' => '2026-11-01']);
        $this->actingAs($admin)->get('/admin/posts')->assertSee('Ключът за LinkedIn е изтекъл');
        $this->actingAs($admin)->get('/admin/zdrave')->assertSee('Ключът е изтекъл на 01.11.2026');
    }

    public function test_connect_stores_the_settings_in_env_without_any_request(): void
    {
        $env = base_path('.env');
        $backup = is_file($env) ? file_get_contents($env) : null;
        Http::fake();

        try {
            $this->artisan('creatium:linkedin-connect')
                ->expectsQuestion('Ключ за достъп (access token от Developer Portal → Tools → OAuth token generator; не се показва)', 'AQ-secret-token')
                ->expectsQuestion('Номер на организацията (числото в адреса на страницата: linkedin.com/company/<число>/admin)', '12345678')
                ->expectsQuestion('До кога важи ключът (ГГГГ-ММ-ДД)', '2027-01-03')
                ->expectsOutputToContain('Записано.')
                ->assertSuccessful();

            $written = file_get_contents($env);
            $this->assertStringContainsString('LINKEDIN_ORGANIZATION_ID="12345678"', $written);
            $this->assertStringContainsString('LINKEDIN_ACCESS_TOKEN="AQ-secret-token"', $written);
            $this->assertStringContainsString('LINKEDIN_TOKEN_EXPIRES="2027-01-03"', $written);
            Http::assertNothingSent();

            $this->artisan('creatium:linkedin-connect')
                ->expectsQuestion('Ключ за достъп (access token от Developer Portal → Tools → OAuth token generator; не се показва)', 'x')
                ->expectsQuestion('Номер на организацията (числото в адреса на страницата: linkedin.com/company/<число>/admin)', 'creatium-lab')
                ->expectsQuestion('До кога важи ключът (ГГГГ-ММ-ДД)', '2027-01-03')
                ->assertFailed();
            $this->artisan('creatium:linkedin-connect')
                ->expectsQuestion('Ключ за достъп (access token от Developer Portal → Tools → OAuth token generator; не се показва)', 'x')
                ->expectsQuestion('Номер на организацията (числото в адреса на страницата: linkedin.com/company/<число>/admin)', '123')
                ->expectsQuestion('До кога важи ключът (ГГГГ-ММ-ДД)', '03.01.2027')
                ->assertFailed();
        } finally {
            $backup === null ? @unlink($env) : file_put_contents($env, $backup);
        }
    }

    public function test_manual_command_publishes_the_given_post(): void
    {
        $this->fakeApi();
        $post = $this->article('stara', '2026-09-01 06:00:00');

        $this->artisan('creatium:linkedin-publish', ['--post' => $post->id])->expectsOutputToContain('Публикувано: Статия stara')->assertSuccessful();
        $this->assertSame('urn:li:share:1', $post->fresh()->linkedin_post_id);
    }
}
