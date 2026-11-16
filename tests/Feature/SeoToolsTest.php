<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\SeoAiCheck;
use App\Models\SeoKeyword;
use App\Models\SeoKeywordPosition;
use App\Models\SeoReport;
use App\Models\User;
use App\Support\Admin\Profiles;
use App\Support\Admin\SystemHealth;
use App\Support\Content;
use App\Support\Schema;
use App\Support\Seo\InternalSite;
use App\Support\Seo\KeywordImport;
use App\Support\Seo\PageInspector;
use App\Support\Seo\PageMeta;
use App\Support\Seo\PostSeo;
use App\Support\Seo\SiteHealth;
use App\Support\Seo\Snippet;
use App\Support\Seo\Sparkline;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/** SEO инструменти в админа: страници, статии, SEO здраве (вътрешно обхождане), ключови думи, AI споменавания. */
class SeoToolsTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $profile = 'sales', string $name = 'Владимир'): User
    {
        if (! Role::exists()) {
            $this->seed(RoleSeeder::class);
        }
        $user = User::factory()->create(['name' => $name, 'profile' => $profile]);
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    private function makePost(array $values = []): Post
    {
        static $n = 0;
        $n++;

        return Post::create($values + [
            'title' => ['bg' => "Статия номер $n за сайтове"],
            'slug' => ['bg' => "statia-$n"],
            'body' => ['bg' => "Изработка на сайт е важна.\n\n## Подзаглавие за сайт\n\nТекст."],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }

    // ------------------------------------------------------------ дължини и повторения

    public function test_counters_states_and_google_truncation(): void
    {
        $this->assertSame('missing', Snippet::titleState(''));
        $this->assertSame('short', Snippet::titleState(str_repeat('а', 29)));
        $this->assertSame('ok', Snippet::titleState(str_repeat('а', 30)));
        $this->assertSame('ok', Snippet::titleState(str_repeat('а', 60)));
        $this->assertSame('long', Snippet::titleState(str_repeat('а', 61)));
        $this->assertSame('short', Snippet::descriptionState(str_repeat('а', 69)));
        $this->assertSame('ok', Snippet::descriptionState(str_repeat('а', 155)));
        $this->assertSame('long', Snippet::descriptionState(str_repeat('а', 156)));

        $long = str_repeat('Изработка на сайт ', 8);
        $cut = Snippet::truncateTitle($long);
        $this->assertStringEndsWith('…', $cut);
        $this->assertLessThanOrEqual(Snippet::TITLE_PX, Snippet::width(rtrim($cut, '…')));
        $this->assertSame('Кратко', Snippet::truncateTitle('Кратко'));
        $this->assertSame(155, mb_strlen(Snippet::truncateDescription(str_repeat('я', 300))));
        $this->assertSame('creatiumlab.com › uslugi › sait', Snippet::breadcrumb('https://creatiumlab.com/uslugi/sait'));
    }

    public function test_duplicates_are_found_ignoring_case_and_spaces(): void
    {
        $dups = Snippet::duplicates(['a' => 'Изработка на сайт', 'b' => ' изработка на САЙТ ', 'c' => 'Друго', 'd' => '']);
        $this->assertSame([['a', 'b']], array_values($dups));
    }

    // ------------------------------------------------------------ ефективно заглавие и описание

    public function test_effective_title_and_description_come_from_the_rendered_page(): void
    {
        $default = $this->get('/paketi')->assertOk();
        preg_match('#<title>(.*?)</title>#su', $default->getContent(), $m);
        $defaultTitle = html_entity_decode($m[1]);

        $info = PageInspector::pages(['packages', 'about']);
        $this->assertSame($defaultTitle, $info['packages']['bg']['title']);
        $this->assertFalse($info['packages']['bg']['title_own']);
        $this->assertNotEmpty($info['packages']['bg']['description']);
        $this->assertSame('self', $info['packages']['bg']['canonical_state']);
        $this->assertTrue($info['packages']['bg']['in_sitemap']);
        $this->assertFalse($info['packages']['bg']['noindex']);
        $this->assertSame(200, $info['packages']['en']['status']);

        app(Content::class)->save('seo:packages', 'bg', ['meta_title' => 'Мой нов заглавен текст', 'meta_description' => 'Мое описание.'], null);
        request()->attributes->remove('seo.inspector');
        $own = PageInspector::pages(['packages'])['packages']['bg'];
        $this->assertSame('Мой нов заглавен текст', $own['title']);
        $this->assertTrue($own['title_own']);
        $this->assertSame('Мое описание.', $own['description']);
        $this->assertTrue($own['description_own']);
        // английската страница не е пипната
        $this->assertFalse(PageInspector::pages(['packages'])['packages']['en']['title_own']);
    }

    public function test_page_duplicates_are_detected_across_pages(): void
    {
        app(Content::class)->save('seo:faq', 'bg', ['meta_title' => 'Едно и също заглавие за две страници'], null);
        app(Content::class)->save('seo:about', 'bg', ['meta_title' => 'Едно и също заглавие за две страници'], null);
        $pages = PageInspector::pages(['faq', 'about', 'home']);
        $dups = PageInspector::duplicates($pages);
        $this->assertEqualsCanonicalizing(['faq|bg', 'about|bg'], $dups['title']);

        $html = $this->actingAs($this->person())->get('/admin/seo')->assertOk()->getContent();
        $this->assertStringContainsString('повтаря се', $html);
        $this->assertStringContainsString('Ваш текст', $html);
        $this->assertStringContainsString('по подразбиране', $html);
        $this->assertStringContainsString('/ 60', $html);
    }

    public function test_page_editor_shows_snippet_preview_and_still_saves_as_before(): void
    {
        $user = $this->person();
        $this->actingAs($user)->get('/admin/seo/services')->assertOk()
            ->assertSee('Как изглежда в Google')->assertSee('data-snippet', false)->assertSee('data-limit="155"', false)->assertSee('canonical', false);

        $this->actingAs($user)->put('/admin/seo/services', ['seo' => ['bg' => ['meta_title' => 'Нашите услуги за сайтове', 'meta_description' => 'Описание на услугите.']]])
            ->assertRedirect('/admin/seo/services');
        $this->assertSame('Нашите услуги за сайтове', app(Content::class)->seo('services', 'bg')['meta_title']);
        $this->get('/uslugi')->assertSee('<title>Нашите услуги за сайтове</title>', false);
    }

    public function test_inspector_reads_inlinks_from_the_last_report(): void
    {
        SeoReport::create(['ran_at' => now(), 'score' => 90, 'data' => ['inlinks' => ['/paketi' => 7]]]);
        $this->assertSame(7, PageInspector::pages(['packages'])['packages']['bg']['inlinks']);
    }

    // ------------------------------------------------------------ статии

    public function test_post_checks_with_focus_keyword(): void
    {
        $body = "Изработка на сайт започва с въпрос какво искате.\n\n## Защо сайт\n\n" . str_repeat('дума ', 310) . "\n\n[Цени](/paketi) и [услуги](/uslugi).\n\n![Екран](/x.png)";
        $post = $this->makePost([
            'title' => ['bg' => 'Изработка на сайт за малък бизнес'],
            'slug' => ['bg' => \Illuminate\Support\Str::slug('Изработка на сайт за малък бизнес', '-', 'bg')],
            'body' => ['bg' => $body],
            'meta_description' => ['bg' => 'Изработка на сайт за малък бизнес: какво включва, колко отнема и как да изберете, за да не губите пари.'],
            'focus_keyword' => ['bg' => 'изработка на сайт'],
        ]);
        $checks = collect(PostSeo::checks($post, 'bg'))->keyBy('id');
        $this->assertSame('ok', $checks['kw_title']['state']);
        $this->assertSame('ok', $checks['kw_intro']['state']);
        // подзаглавието е „Защо сайт“ и не съдържа цялата фраза
        $this->assertSame('warn', $checks['kw_heading']['state']);
        $this->assertSame('ok', $checks['kw_slug']['state']);
        $this->assertSame('ok', $checks['kw_description']['state']);
        $this->assertSame('ok', $checks['length']['state']);
        $this->assertSame('ok', $checks['links']['state']);
        $this->assertSame('ok', $checks['images']['state']);
        $this->assertSame('ok', $checks['title']['state']);
        $this->assertSame('ok', $checks['description']['state']);

        [$ok, $total] = PostSeo::score(PostSeo::checks($post, 'bg'));
        $this->assertSame(11, $total);
        $this->assertSame(10, $ok);
    }

    public function test_post_checks_flag_missing_things(): void
    {
        $post = $this->makePost(['body' => ['bg' => "Кратък текст.\n\n![](/a.png) [навън](https://example.com)"]]);
        $checks = collect(PostSeo::checks($post, 'bg'))->keyBy('id');
        $this->assertSame('warn', $checks['keyword']['state']);
        $this->assertSame('warn', $checks['kw_title']['state']);
        $this->assertSame('bad', $checks['length']['state']);
        $this->assertSame('bad', $checks['links']['state']);
        $this->assertSame('bad', $checks['images']['state']);

        $overview = PostSeo::overview($post);
        $this->assertSame('bg', $overview['locale']);
        $this->assertContains($overview['tone'], ['bad', 'warn']);
    }

    public function test_post_overview_is_red_when_title_or_description_is_too_long(): void
    {
        $long = $this->makePost(['meta_title' => ['bg' => str_repeat('Д', 70)], 'meta_description' => ['bg' => str_repeat('о', 100)]]);
        $this->assertTrue(PostSeo::overview($long)['red']);
        $this->assertSame('bad', PostSeo::overview($long)['tone']);

        $fine = $this->makePost(['meta_title' => ['bg' => str_repeat('Д', 40)], 'meta_description' => ['bg' => str_repeat('о', 100)]]);
        $this->assertFalse(PostSeo::overview($fine)['red']);

        $noDescription = $this->makePost(['excerpt' => null, 'body' => ['bg' => '']]);
        $this->assertTrue(PostSeo::overview($noDescription)['red']);
    }

    public function test_posts_form_saves_focus_keyword_author_and_shows_the_seo_panel(): void
    {
        $user = $this->person('brand', 'Мария Тестова');
        $post = $this->makePost();
        $this->actingAs($user)->get("/admin/posts/{$post->id}/edit")->assertOk()
            ->assertSee('Ключова дума')->assertSee('Проверки за SEO')->assertSee('name="user_id"', false)->assertSee('data-snippet', false);

        $this->actingAs($user)->put("/admin/posts/{$post->id}", [
            'status' => 'published', 'user_id' => $user->id,
            'title' => ['bg' => 'Статия за сайтове'], 'slug' => ['bg' => 'statia-za-saitove'],
            'focus_keyword' => ['bg' => '  сайтове  '],
        ])->assertRedirect();
        $post->refresh();
        $this->assertSame('сайтове', $post->tr('focus_keyword', 'bg'));
        $this->assertSame($user->id, $post->user_id);

        $this->get('/admin/seo/statii')->assertOk()->assertSee('Статия за сайтове')->assertSee('сайтове');
        $this->get('/admin/posts')->assertOk()->assertSee('/11')->assertSee('SEO на статиите');
    }

    public function test_updating_a_post_without_an_author_keeps_the_old_one(): void
    {
        $user = $this->person();
        $other = User::factory()->create();
        $post = $this->makePost(['user_id' => $other->id]);
        $this->actingAs($user)->put("/admin/posts/{$post->id}", ['status' => 'published', 'title' => ['bg' => 'Нов заглавен текст']])->assertRedirect();
        $this->assertSame($other->id, $post->fresh()->user_id);
    }

    public function test_chosen_author_goes_into_the_blogposting_json_ld(): void
    {
        config(['creatium.blog_enabled' => true]);
        $maria = User::factory()->create(['name' => 'Мария Тестова']);
        $admin = User::factory()->create(['name' => 'Админ']);
        $post = $this->makePost(['user_id' => $admin->id]);

        $html = $this->get($post->url('bg'))->assertOk()->getContent();
        $this->assertStringContainsString('"author":{"@type":"Organization"', $html);

        $post->update(['user_id' => $maria->id]);
        $html = $this->get($post->url('bg'))->assertOk()->getContent();
        $this->assertStringContainsString('"author":{"@type":"Person"', $html);
        $this->assertSame('Person', Schema::postAuthor($post->fresh())['@type']);
        $this->assertStringContainsString('(от екипа)', PostSeo::authorLabel($post->fresh()));
        $this->assertStringContainsString('фирмата', PostSeo::authorLabel($this->makePost(['user_id' => $admin->id])));
    }

    // ------------------------------------------------------------ обхождане на сайта

    private function fakeSite(array $pages): InternalSite
    {
        return new class($pages) extends InternalSite {
            public function __construct(private array $pages) {}

            public function open(): static { return $this; }

            public function close(): void {}

            public function root(): string { return 'https://example.test'; }

            public function get(string $url): array
            {
                $path = parse_url($this->absolute($url), PHP_URL_PATH) ?: '/';
                $query = parse_url($url, PHP_URL_QUERY);
                $key = $path . ($query ? '?' . $query : '');
                $page = $this->pages[$key] ?? $this->pages[$path] ?? null;
                if ($page === null) {
                    return ['status' => 404, 'location' => null, 'type' => 'text/html', 'body' => '<html><head><title>404</title></head></html>'];
                }
                if (is_array($page)) {
                    return ['status' => $page[0], 'location' => $page[1] ?? null, 'type' => 'text/html', 'body' => ''];
                }

                return ['status' => 200, 'location' => null, 'type' => str_ends_with($path, '.xml') ? 'application/xml' : 'text/html', 'body' => $page];
            }
        };
    }

    private function html(string $title, string $desc, string $body, array $head = []): string
    {
        return '<!doctype html><html lang="bg"><head><title>' . $title . '</title><meta name="description" content="' . $desc . '">' . implode('', $head) . '</head><body><main>' . $body . '</main></body></html>';
    }

    public function test_site_health_finds_the_problems_on_a_small_site(): void
    {
        $goodTitle = 'Изработка на сайт за малък бизнес';
        $goodDesc = str_repeat('Описание на страницата. ', 4);
        $self = fn ($p) => '<link rel="canonical" href="https://example.test' . $p . '">';
        $pages = [
            '/sitemap.xml' => '<urlset><url><loc>https://example.test/</loc></url><url><loc>https://example.test/a</loc></url><url><loc>https://example.test/orphan</loc></url><url><loc>https://example.test/dup</loc></url></urlset>',
            '/' => $this->html($goodTitle, $goodDesc, '<h1>Начало</h1><a href="/a">A</a> <a href="/dup">dup</a> <a href="/gone">счупена</a> <a href="/moved">преместена</a> <a href="/hidden">скрита</a> <a href="/extra">извън sitemap</a>', [$self('/')]),
            '/a' => $this->html('Страница А за тестове и проверки', $goodDesc, '<h1>А</h1><img src="/x.png"><img src="/y.png" alt="" width="10" height="10"><p>' . str_repeat('дума ', 10) . '</p>', [$self('/a')]),
            '/dup' => $this->html('Страница А за тестове и проверки', $goodDesc, '<h1>Дубликат</h1><h3>Прескочено</h3>', [$self('/dup')]),
            '/orphan' => $this->html('Сираче страница за проверка', $goodDesc, '<h1>Сираче</h1>', [$self('/orphan')]),
            '/extra' => $this->html('Страница извън sitemap за проба', $goodDesc, '<h1>Извън</h1>', [$self('/extra')]),
            '/hidden' => $this->html('Скрита страница от Google търсачката', $goodDesc, '<h1>Скрита</h1>', ['<meta name="robots" content="noindex">', $self('/hidden')]),
            '/moved' => [301, 'https://example.test/moved2'],
            '/moved2' => [301, 'https://example.test/a'],
            '/gone' => [404],
        ];

        $report = (new SiteHealth)->run($this->fakeSite($pages));
        $issues = $report->data['issues'];
        $urls = fn (string $code) => array_column($issues[$code] ?? [], 'url');

        $this->assertContains('/gone', $urls('broken_links'));
        $this->assertContains('/orphan', $urls('orphans'));
        $this->assertNotContains('/a', $urls('orphans'));
        $this->assertContains('/a', $urls('images_no_alt'));
        $this->assertContains('/a', $urls('images_no_size'));
        $this->assertContains('/hidden', $urls('noindex_pages'));
        $this->assertEqualsCanonicalizing(['/a', '/dup'], $urls('title_duplicate'));
        $this->assertEqualsCanonicalizing(['/', '/a', '/dup', '/orphan', '/hidden'], array_values(array_intersect(array_column($report->data['rows'], 'p'), ['/', '/a', '/dup', '/orphan', '/hidden'])));
        $this->assertContains('/dup', $urls('heading_skip'));
        $this->assertContains('/moved', $urls('redirect_chains'));
        $this->assertContains('/', $urls('links_to_redirects'));
        $this->assertContains('/a', $urls('thin_pages'));
        $this->assertSame(['/extra'], $urls('missing_from_sitemap'));
        $this->assertContains('/hidden', $urls('noindex_pages'));
        $this->assertGreaterThan(0, $report->errors);
        $this->assertGreaterThan(0, $report->warnings);
        $this->assertLessThan(100, $report->score);
        $this->assertSame($report->id, SiteHealth::latest()->id);

        $rows = collect($report->data['rows'])->keyBy('p');
        $this->assertSame(0, $rows['/']['d']);
        $this->assertSame(1, $rows['/a']['d']);
        $this->assertNull($rows['/orphan']['d']);
        $this->assertSame(1, $report->data['inlinks']['/dup']);
        // sitemap 4 адреса, но /hidden и пренасочванията са обходени заради връзките
        $this->assertSame(4, $report->data['sitemap']);
    }

    public function test_site_health_checks_hreflang_json_ld_and_sitemap_errors(): void
    {
        $desc = str_repeat('Описание на страницата. ', 4);
        $self = fn ($p) => '<link rel="canonical" href="https://example.test' . $p . '">';
        $hl = fn ($bg, $en) => '<link rel="alternate" hreflang="bg" href="https://example.test' . $bg . '"><link rel="alternate" hreflang="en" href="https://example.test' . $en . '">';
        $pages = [
            '/sitemap.xml' => '<urlset><url><loc>https://example.test/</loc></url><url><loc>https://example.test/en</loc></url><url><loc>https://example.test/missing</loc></url></urlset>',
            '/' => $this->html('Начална страница на сайта ни', $desc, '<h1>Начало</h1><a href="/en">EN</a>', [$self('/'), $hl('/', '/en'), '<script type="application/ld+json">{"name":"без контекст"}</script>']),
            '/en' => $this->html('Home page of our website', $desc, '<h1>Home</h1><a href="/">BG</a>', [$self('/en'), '<script type="application/ld+json">{ счупен</script>']),
        ];
        $issues = (new SiteHealth)->run($this->fakeSite($pages))->data['issues'];
        $this->assertContains('/', array_column($issues['json_ld_invalid'], 'url'));
        $this->assertContains('/en', array_column($issues['json_ld_invalid'], 'url'));
        $this->assertContains('/missing', array_column($issues['sitemap_bad'], 'url'));
        // /en не сочи обратно към / (няма hreflang), а / го иска
        $this->assertStringContainsString('не сочи обратно', collect($issues['hreflang'])->firstWhere('url', '/')['detail']);
    }

    public function test_page_meta_parser(): void
    {
        $meta = PageMeta::parse($this->html('Заглавие', 'Описание', '<h1>Едно</h1><h3>Три</h3><a href="/x">x</a><a href="mailto:a@b.c">m</a><a href="#top">t</a><img src="/q.png">', ['<link rel="alternate" hreflang="en" href="/en">']));
        $this->assertSame('Заглавие', $meta['title']);
        $this->assertSame('Описание', $meta['description']);
        $this->assertSame(['/x'], $meta['links']);
        $this->assertSame(1, $meta['images']['no_alt']);
        $this->assertSame(['en' => '/en'], $meta['hreflang']);
        $this->assertSame(1, PageMeta::headingSkips($meta['headings']));
        $this->assertSame(0, PageMeta::headingSkips([1, 2, 3, 2, 3]));
    }

    public function test_real_internal_crawl_of_the_seeded_site_and_the_app_state_survives(): void
    {
        config(['creatium.blog_enabled' => true]);
        $broken = $this->makePost(['title' => ['bg' => 'Статия със счупена връзка'], 'slug' => ['bg' => 'sas-schupena-vruzka'], 'body' => ['bg' => 'Вижте [тази страница](/nyama-takava-stranitsa) за повече.']]);
        $user = $this->person();
        $this->actingAs($user);
        $app = app();
        $request = request();

        $report = (new SiteHealth)->run();

        $this->assertSame($app, app(), 'приложението е същото след обхождането');
        $this->assertSame($app, \Illuminate\Container\Container::getInstance());
        $this->assertSame($request, request());
        $this->assertSame($user->id, Auth::id());
        $this->assertSame(\Illuminate\Database\Eloquent\Model::getConnectionResolver(), $app['db']);

        $this->assertGreaterThan(30, $report->pages);
        $rows = collect($report->data['rows'])->keyBy('p');
        $this->assertSame(200, $rows['/']['s']);
        $this->assertSame(200, $rows['/en']['s']);
        $this->assertSame(0, $rows['/']['d']);
        $this->assertArrayHasKey('/blog/sas-schupena-vruzka', $rows->all());
        $this->assertContains('/nyama-takava-stranitsa', array_column($report->data['issues']['broken_links'] ?? [], 'url'));
        // демо страниците са нарочно noindex
        $this->assertNotEmpty($report->data['issues']['noindex_pages'] ?? []);
        // обхождането не оставя нищо след себе си: нито сесии, нито нови записи освен отчета
        $this->assertSame(1, SeoReport::count());
    }

    public function test_run_now_button_runs_after_the_response_and_keeps_the_session(): void
    {
        $user = $this->person();
        $this->actingAs($user)->post('/admin/seo/zdrave/proveri')->assertRedirect('/admin/seo/zdrave')->assertSessionHas('success');
        $this->assertSame(1, SeoReport::count());
        $this->actingAs($user)->get('/admin/seo/zdrave')->assertOk()->assertSee('Оценка')->assertSee('Обходени адреси');
    }

    public function test_health_screen_without_a_report_and_with_one(): void
    {
        $user = $this->person();
        $this->actingAs($user)->get('/admin/seo/zdrave')->assertOk()->assertSee('Още няма проверка')->assertSee('Провери сега');

        SeoReport::create(['ran_at' => now()->subDay(), 'score' => 80, 'pages' => 10, 'errors' => 1, 'warnings' => 2, 'data' => [
            'root' => 'https://example.test', 'sitemap' => 9, 'crawled' => 10, 'max_depth' => 2, 'counts' => ['broken_links' => 1], 'rows' => [],
            'issues' => ['broken_links' => [['url' => '/paketi', 'detail' => 'отговор 404, връзка от /']]],
        ]]);
        SeoReport::create(['ran_at' => now(), 'score' => 85, 'pages' => 10, 'errors' => 0, 'warnings' => 2, 'data' => [
            'root' => 'https://example.test', 'sitemap' => 9, 'crawled' => 10, 'max_depth' => 2, 'counts' => ['title_length' => 1], 'rows' => [],
            'issues' => ['title_length' => [['url' => '/paketi', 'detail' => '70 знака']]],
        ]]);
        $this->get('/admin/seo/zdrave')->assertOk()->assertSee('Заглавия извън 30 до 60 знака')->assertSee('/seo/packages', false)->assertSee('+5')->assertSee('1</b> поправени', false);
        $this->assertSame(['score' => 5, 'errors' => -1, 'warnings' => 0, 'new' => 1, 'fixed' => 1], SiteHealth::trend());
    }

    public function test_score_has_caps_per_group(): void
    {
        $this->assertSame(100, SiteHealth::score([]));
        $this->assertSame(96, SiteHealth::score(['broken_links' => 1]));
        $this->assertSame(70, SiteHealth::score(['broken_links' => 50]));
        $this->assertSame(100, SiteHealth::score(['thin_pages' => 40]));
        $this->assertSame(90, SiteHealth::score(['title_length' => 40]));
    }

    // ------------------------------------------------------------ автоматичен старт

    public function test_auto_run_is_due_after_a_day_and_never_runs_twice_at_once(): void
    {
        config(['creatium.seo_health.auto' => true]);
        $this->assertTrue(SiteHealth::due());

        SeoReport::create(['ran_at' => now()->subHours(2), 'score' => 90, 'data' => []]);
        $this->assertFalse(SiteHealth::due());
        $this->assertNull(SiteHealth::tick());

        SeoReport::query()->update(['ran_at' => now()->subHours(25)]);
        $this->assertTrue(SiteHealth::due());

        $lock = Cache::lock('seo-health:run', 60);
        $this->assertTrue($lock->get());
        $this->assertTrue(SiteHealth::running());
        $this->assertNull(SiteHealth::tick(), 'докато върви друга проверка, нова не тръгва');
        $this->assertSame(1, SeoReport::count());
        $lock->release();

        $report = SiteHealth::tick();
        $this->assertNotNull($report);
        $this->assertSame(2, SeoReport::count());
        $this->assertFalse(SiteHealth::running());
    }

    public function test_auto_run_is_off_by_flag_and_waits_an_hour_after_an_error(): void
    {
        config(['creatium.seo_health.auto' => false]);
        $this->assertNull(SiteHealth::tick());
        $this->assertSame(0, SeoReport::count());

        config(['creatium.seo_health.auto' => true]);
        \App\Models\OutreachSetting::put(SiteHealth::STATE_KEY, ['last_attempt_at' => now()->subMinutes(10)->toIso8601String(), 'last_error' => 'x']);
        $this->assertFalse(SiteHealth::due());
        \App\Models\OutreachSetting::put(SiteHealth::STATE_KEY, ['last_attempt_at' => now()->subMinutes(90)->toIso8601String(), 'last_error' => 'x']);
        $this->assertTrue(SiteHealth::due());
    }

    public function test_a_visit_starts_the_check_once_and_never_breaks_the_page(): void
    {
        config(['creatium.seo_health.auto' => true]);
        $this->get('/')->assertOk();
        $this->assertSame(1, SeoReport::count(), 'първото посещение пуска проверката след отговора');

        SeoReport::query()->update(['ran_at' => now()->subDays(2)]);
        $this->get('/paketi')->assertOk();
        $this->assertSame(1, SeoReport::count(), 'най-много веднъж на 10 минути от посещенията');
    }

    public function test_internal_crawl_does_not_trigger_the_background_publishers(): void
    {
        $skipped = (new \ReflectionClassConstant(InternalSite::class, 'SKIP_MIDDLEWARE'))->getValue();
        foreach ([\App\Http\Middleware\PublishBlogToFacebook::class, \App\Http\Middleware\PublishBlogToLinkedIn::class, \App\Http\Middleware\RunOutreachAutopilot::class, \App\Http\Middleware\CollectOpportunities::class, \App\Http\Middleware\MonitorClientSites::class, \App\Http\Middleware\RunSeoHealth::class] as $class) {
            $this->assertContains($class, $skipped);
        }
    }

    // ------------------------------------------------------------ внасяне от Search Console

    public function test_csv_import_comma_english(): void
    {
        $parsed = KeywordImport::parse("Top queries,Clicks,Impressions,CTR,Position\nизработка на сайт,12,840,1.4%,8.3\n\"сайт, цена\",\"1,234\",\"12,000\",10%,2.5\n");
        $this->assertSame(2, count($parsed['rows']));
        $this->assertEquals(['query' => 'изработка на сайт', 'clicks' => 12, 'impressions' => 840, 'position' => 8.3], $parsed['rows'][0]);
        $this->assertSame('сайт, цена', $parsed['rows'][1]['query']);
        $this->assertSame(1234, $parsed['rows'][1]['clicks']);
        $this->assertSame(12000, $parsed['rows'][1]['impressions']);
    }

    public function test_csv_import_semicolon_bulgarian_with_decimal_commas(): void
    {
        $parsed = KeywordImport::parse("\xEF\xBB\xBFТоп заявки;Кликвания;Показвания;CTR;Позиция\nизработка на сайт;12;1 240;1,4 %;8,3\nсео цена;3;90;3,3 %;12,75\n");
        $this->assertSame(2, count($parsed['rows']));
        $this->assertSame(1240, $parsed['rows'][0]['impressions']);
        $this->assertSame(8.3, $parsed['rows'][0]['position']);
        $this->assertSame(12.75, $parsed['rows'][1]['position']);
    }

    public function test_csv_import_tab_separated_pasted_from_sheets_and_reordered_columns(): void
    {
        $parsed = KeywordImport::parse("Position\tQuery\tImpressions\tClicks\n4.2\tуеб дизайн\t300\t9\n");
        $this->assertSame(1, count($parsed['rows']));
        $this->assertEquals(['query' => 'уеб дизайн', 'clicks' => 9, 'impressions' => 300, 'position' => 4.2], $parsed['rows'][0]);
    }

    public function test_csv_import_without_header_and_with_junk_rows(): void
    {
        $parsed = KeywordImport::parse("сайт за ресторант;4;100;4%;7,5\n\n;;;;\nлоша;x;y;z;—\n");
        $this->assertSame(1, count($parsed['rows']));
        $this->assertSame(7.5, $parsed['rows'][0]['position']);
        $this->assertGreaterThanOrEqual(1, $parsed['skipped']);
        $this->assertSame([], KeywordImport::parse('')['rows']);
    }

    public function test_number_parser_variants(): void
    {
        foreach (['12,5' => 12.5, '12.5' => 12.5, '1 234' => 1234.0, '1,234' => 1234.0, '1.234,5' => 1234.5, '1,234.5' => 1234.5, '8,3 %' => 8.3, '2.345.678' => 2345678.0] as $in => $out) {
            $this->assertSame($out, KeywordImport::number((string) $in), $in);
        }
        $this->assertSame(1.5, KeywordImport::number('1,5', ';'));
        $this->assertSame(1.234, KeywordImport::number('1,234', ';'), 'при точка и запетая като разделител запетаята е десетична');
        $this->assertNull(KeywordImport::number('—'));
        $this->assertNull(KeywordImport::number(''));
    }

    public function test_import_updates_known_keywords_for_the_date_and_can_add_unknown(): void
    {
        $known = SeoKeyword::create(['keyword' => 'Изработка на сайт', 'locale' => 'bg', 'country' => 'bg']);
        $other = SeoKeyword::create(['keyword' => 'изработка на сайт', 'locale' => 'en', 'country' => 'gb']);
        $parsed = KeywordImport::parse("Query,Clicks,Impressions,CTR,Position\nизработка  на сайт,5,100,5%,6.5\nнепозната дума,1,10,10%,30\n");

        $r = KeywordImport::apply($parsed, 'bg', 'bg', '2026-11-16');
        $this->assertSame(1, $r['updated']);
        $this->assertSame(['непозната дума'], $r['unknown']);
        $this->assertSame(6.5, $known->positions()->first()->position);
        $this->assertSame(0, $other->positions()->count(), 'друг език и държава не се пипат');

        // втори внос за същия ден обновява, не удвоява
        $parsed['rows'][0]['position'] = 5.0;
        KeywordImport::apply($parsed, 'bg', 'bg', '2026-11-16');
        $this->assertSame(1, $known->positions()->count());
        $this->assertSame(5.0, $known->positions()->first()->position);

        $r = KeywordImport::apply($parsed, 'bg', 'bg', '2026-11-17', true);
        $this->assertSame(1, $r['created']);
        $this->assertSame('low', SeoKeyword::where('keyword', 'непозната дума')->first()->priority);
    }

    // ------------------------------------------------------------ ключови думи и AI

    public function test_keywords_crud_positions_change_and_sparkline(): void
    {
        $user = $this->person();
        $this->actingAs($user)->get('/admin/seo/klyuchovi-dumi')->assertOk()->assertSee('Няма автоматично следене');

        $this->post('/admin/seo/klyuchovi-dumi', ['keyword' => 'изработка на сайт', 'locale' => 'bg', 'country' => 'bg', 'priority' => 'high', 'target' => 'services', 'note' => 'основна'])->assertRedirect('/admin/seo/klyuchovi-dumi');
        $k = SeoKeyword::first();
        $this->assertSame('high', $k->priority);

        $this->post('/admin/seo/klyuchovi-dumi', ['keyword' => ' Изработка  на сайт ', 'locale' => 'bg', 'country' => 'bg', 'priority' => 'low'])->assertSessionHas('error');
        $this->assertSame(1, SeoKeyword::count());

        foreach (['2026-11-01' => 14.2, '2026-11-08' => 11.0, '2026-11-15' => 8.5] as $date => $pos) {
            $this->post("/admin/seo/klyuchovi-dumi/{$k->id}/pozitsii", ['date' => $date, 'position' => $pos, 'impressions' => 100, 'clicks' => 3])->assertRedirect();
        }
        $this->post("/admin/seo/klyuchovi-dumi/{$k->id}/pozitsii", ['date' => '2026-11-15', 'position' => 8.0])->assertRedirect();
        $this->assertSame(3, $k->positions()->count());
        $k = $k->fresh()->load('positions');
        $this->assertSame(8.0, $k->last()->position);
        $this->assertSame(3.0, $k->change(), 'от 11 на 8: по-добре с 3');

        $html = $this->get('/admin/seo/klyuchovi-dumi')->assertOk()->getContent();
        $this->assertStringContainsString('<svg class="sx-spark up"', $html);
        $this->assertStringContainsString('▲ 3,0', $html);

        $this->post("/admin/seo/klyuchovi-dumi/{$k->id}/pozitsii", ['date' => '2026-11-15'])->assertSessionHas('error');
        $this->post("/admin/seo/klyuchovi-dumi/{$k->id}/pozitsii", ['date' => '2099-01-01', 'position' => 3])->assertSessionHasErrors('date');

        $this->put("/admin/seo/klyuchovi-dumi/{$k->id}", ['keyword' => 'сайт за ресторант', 'locale' => 'bg', 'country' => 'bg', 'priority' => 'medium', 'target' => 'post:99'])->assertRedirect();
        $this->assertSame('сайт за ресторант', $k->fresh()->keyword);

        $this->delete("/admin/seo/klyuchovi-dumi/{$k->id}/pozitsii/{$k->positions->first()->id}")->assertRedirect();
        $this->assertSame(2, SeoKeywordPosition::count());
        $this->delete("/admin/seo/klyuchovi-dumi/{$k->id}")->assertRedirect();
        $this->assertSame(0, SeoKeyword::count());
        $this->assertSame(0, SeoKeywordPosition::count(), 'записите се трият заедно с думата');
    }

    public function test_keyword_import_endpoint(): void
    {
        $user = $this->person();
        SeoKeyword::create(['keyword' => 'сео цена', 'locale' => 'bg', 'country' => 'bg']);
        $this->actingAs($user)->post('/admin/seo/klyuchovi-dumi/vnasyane', ['csv' => "Заявка;Кликвания;Показвания;CTR;Позиция\nсео цена;2;50;4 %;9,1\nдруга;1;5;20 %;40", 'date' => '2026-11-16', 'locale' => 'bg', 'country' => 'bg'])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame(9.1, SeoKeywordPosition::first()->position);
        $this->post('/admin/seo/klyuchovi-dumi/vnasyane', ['csv' => 'нищо смислено', 'date' => '2026-11-16', 'locale' => 'bg', 'country' => 'bg'])->assertSessionHas('error');
    }

    public function test_sparkline_needs_two_points(): void
    {
        $this->assertStringContainsString('sx-nodata', Sparkline::svg([5.0]));
        $this->assertStringContainsString('class="sx-spark down"', Sparkline::svg([3.0, 9.0]));
        $this->assertStringContainsString('class="sx-spark up"', Sparkline::svg([9.0, 3.0]));
        $this->assertStringContainsString('class="sx-spark flat"', Sparkline::svg([3.0, 3.0]));
    }

    public function test_ai_mentions_log_and_summary(): void
    {
        $user = $this->person();
        $this->actingAs($user)->get('/admin/seo/ai')->assertOk()->assertSee('10 въпроса')->assertSee('коя агенция в България прави сайтове и SEO за малък бизнес')->assertSee('Още няма проверки');

        $data = ['checked_on' => '2026-11-10', 'assistant' => 'ChatGPT', 'prompt' => 'коя агенция в България прави сайтове', 'mentioned' => '1', 'position_note' => 'трети', 'link' => 'https://example.com/a'];
        $this->post('/admin/seo/ai', $data)->assertRedirect('/admin/seo/ai');
        $this->post('/admin/seo/ai', ['mentioned' => '0', 'prompt' => 'друг въпрос'] + $data);
        $this->post('/admin/seo/ai', ['assistant' => 'Gemini', 'mentioned' => '0', 'checked_on' => '2026-10-05'] + $data);
        $this->assertSame(3, SeoAiCheck::count());

        $html = $this->get('/admin/seo/ai')->assertOk()->getContent();
        $this->assertStringContainsString('50%', $html);
        $this->assertStringContainsString('(1 от 2)', $html);
        $this->assertStringContainsString('Ноември 2026', $html);
        $this->assertStringContainsString('Октомври 2026', $html);

        $this->post('/admin/seo/ai', ['assistant' => 'Непознат'] + $data)->assertSessionHasErrors('assistant');
        $this->post('/admin/seo/ai', ['link' => 'javascript:alert(1)'] + $data)->assertSessionHasErrors('link');
        $this->delete('/admin/seo/ai/' . SeoAiCheck::first()->id)->assertRedirect();
        $this->assertSame(2, SeoAiCheck::count());
        $this->assertCount(10, \App\Support\Seo\AiPrompts::SUGGESTED);
    }

    // ------------------------------------------------------------ менюта, Днес, здраве на системата

    private function nav(User $user, string $path = '/admin/dnes'): array
    {
        $html = $this->actingAs($user)->get($path)->assertOk()->getContent();
        $nav = substr($html, strpos($html, 'class="adm-nav"'));
        $more = strpos($nav, 'adm-more');

        return [$more === false ? $nav : substr($nav, 0, $more), $more === false ? '' : substr($nav, $more)];
    }

    public function test_seo_menu_per_profile(): void
    {
        $seo = route('admin.seo.index');

        [$main, $more] = $this->nav($this->person('sales'));
        $this->assertStringContainsString('href="' . $seo . '"', $main);
        $this->assertStringContainsString('SEO', $main);

        [$main] = $this->nav($this->person('all', 'Админ'));
        $this->assertStringContainsString('href="' . $seo . '"', $main);

        [$main, $more] = $this->nav($this->person('brand', 'Мария'));
        $this->assertStringNotContainsString('href="' . $seo . '"', $main);
        $this->assertStringContainsString('href="' . $seo . '"', $more);

        [$main, $more] = $this->nav($this->person('dev', 'Борислав'));
        $this->assertStringNotContainsString('href="' . $seo . '"', $main);

        // Продажби: SEO стои в групата Продажби
        $groups = Profiles::menu('sales');
        $this->assertContains('SEO', array_column($groups['Продажби'], 'label'));
        $this->assertContains('SEO', array_column(Profiles::menu('all')['Продажби'], 'label'));

        // всички раздели на SEO държат менюто светнато
        $html = $this->actingAs($this->person('sales'))->get('/admin/seo/ai')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#href="' . preg_quote($seo, '#') . '"[^>]*aria-current="page"#', $html);
    }

    public function test_every_seo_tab_opens_for_every_profile_and_brand_reaches_posts_seo_from_blog(): void
    {
        foreach (['sales', 'all', 'brand', 'dev'] as $profile) {
            $user = $this->person($profile, $profile === 'brand' ? 'Мария' : 'Тест ' . $profile);
            foreach (['/admin/seo', '/admin/seo/statii', '/admin/seo/zdrave', '/admin/seo/klyuchovi-dumi', '/admin/seo/ai', '/admin/seo/home'] as $url) {
                $this->actingAs($user)->get($url)->assertOk();
            }
        }
        $this->actingAs($this->person('brand', 'Мария'))->get('/admin/posts')->assertOk()->assertSee(route('admin.seo.posts'), false);
        $this->get('/admin/seo/statii')->assertOk()->assertSee('aria-current="true"', false);
        $this->get('/admin/seo/nyama-takava')->assertNotFound();
    }

    public function test_guests_cannot_open_seo_tools(): void
    {
        foreach (['/admin/seo', '/admin/seo/statii', '/admin/seo/zdrave', '/admin/seo/klyuchovi-dumi', '/admin/seo/ai'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/admin/seo/zdrave/proveri')->assertRedirect('/login');
    }

    public function test_today_shows_seo_errors_for_sales_and_stays_quiet_otherwise(): void
    {
        $sales = $this->person('sales');
        $this->assertContains('sales-seo', Profiles::todaySections('sales')['']);
        $this->actingAs($sales)->get('/admin/dnes')->assertOk()->assertSee('data-block="sales-seo"', false)->assertSee('Още няма проверка на сайта');

        SeoReport::create(['ran_at' => now(), 'score' => 70, 'pages' => 20, 'errors' => 2, 'warnings' => 1, 'data' => ['counts' => ['broken_links' => 2], 'issues' => ['broken_links' => [['url' => '/x', 'detail' => 'a'], ['url' => '/y', 'detail' => 'b']]]]]);
        $this->get('/admin/dnes')->assertOk()->assertSee('Счупени вътрешни връзки')->assertSee('2 адреса')->assertSee('оценка 70/100');

        SeoReport::query()->update(['errors' => 0, 'data' => ['counts' => [], 'issues' => []]]);
        $this->get('/admin/dnes')->assertOk()->assertSee('Няма грешки. Оценка 70/100.');
    }

    public function test_system_health_has_a_seo_row(): void
    {
        $row = fn () => collect(SystemHealth::checks()['Сайт'])->firstWhere('name', 'SEO');
        $this->assertNotNull($row());
        $this->assertSame('off', $row()['state'], 'без отчет и с изключено автоматично пускане');

        SeoReport::create(['ran_at' => now(), 'score' => 88, 'pages' => 60, 'errors' => 0, 'warnings' => 3, 'data' => []]);
        $this->assertSame('ok', $row()['state']);
        $this->assertStringContainsString('оценка 88/100', $row()['text']);
        $this->assertStringContainsString('предупреждения 3', $row()['text']);

        SeoReport::query()->update(['errors' => 2]);
        $this->assertSame('warn', $row()['state']);
    }

    public function test_migration_adds_nullable_focus_keyword_without_touching_existing_posts(): void
    {
        $post = $this->makePost(['user_id' => User::factory()->create()->id]);
        $before = $post->fresh()->getAttributes();
        $this->assertNull($before['focus_keyword']);
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('posts', 'focus_keyword'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('seo_reports'));
    }
}
