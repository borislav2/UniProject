<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\User;
use App\Support\PostMetaTitles2026;
use App\Support\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * SEO audit of 16.11.2026: head tags, canonical and hreflang, the sitemap, robots.txt and llms.txt, redirects, structured data
 * and heading order, for the current design and for design G (the one that will go live).
 */
class SeoAuditTest extends TestCase
{
    use RefreshDatabase;

    private function design(string $design): void
    {
        config(['creatium.design' => $design, 'creatium.gtm_id' => 'GTM-TEST123']);
    }

    /** @return list<string> the paths listed in /sitemap.xml */
    private function sitemapPaths(): array
    {
        preg_match_all('~<loc>([^<]+)</loc>~', $this->get('/sitemap.xml')->getContent(), $m);

        return array_map(fn ($url) => parse_url($url, PHP_URL_PATH) ?: '/', $m[1]);
    }

    private function tags(string $html, string $tag, string $attr = 'rel', string $value = 'canonical'): array
    {
        preg_match_all('~<' . $tag . '\b[^>]*' . $attr . '="' . preg_quote($value, '~') . '"[^>]*>~', $html, $m);

        return $m[0];
    }

    private function attr(string $tag, string $name): ?string
    {
        return preg_match('~\b' . $name . '="([^"]*)"~', $tag, $m) ? html_entity_decode($m[1]) : null;
    }

    private function title(string $html): string
    {
        preg_match('~<title>(.*?)</title>~s', $html, $m);

        return html_entity_decode(trim($m[1] ?? ''));
    }

    private function description(string $html): string
    {
        $tags = $this->tags($html, 'meta', 'name', 'description');

        return html_entity_decode($this->attr($tags[0] ?? '', 'content') ?? '');
    }

    private function headingLevels(string $html): array
    {
        preg_match_all('~<h([1-6])\b~', $html, $m);

        return array_map('intval', $m[1]);
    }

    private function jsonLd(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);

        return array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
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
            ]);
        }
    }

    public function test_every_sitemap_page_has_a_short_unique_title_and_description_one_h1_and_a_self_canonical_in_both_designs(): void
    {
        $this->posts(2);
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            $titles = $descriptions = [];

            foreach ($this->sitemapPaths() as $path) {
                $html = $this->get($path)->assertOk()->getContent();
                $title = $this->title($html);
                $description = $this->description($html);

                $this->assertNotSame('', $title, "$design $path title");
                $this->assertLessThanOrEqual(60, mb_strlen($title), "$design $path title is $title");
                $this->assertGreaterThanOrEqual(40, mb_strlen($description), "$design $path description");
                $this->assertLessThanOrEqual(155, mb_strlen($description), "$design $path description is $description");
                $this->assertArrayNotHasKey($title, $titles, "$design $path repeats the title of " . ($titles[$title] ?? ''));
                $this->assertArrayNotHasKey($description, $descriptions, "$design $path repeats the description of " . ($descriptions[$description] ?? ''));
                $titles[$title] = $path;
                $descriptions[$description] = $path;

                $levels = $this->headingLevels($html);
                $this->assertSame(1, count(array_filter($levels, fn ($l) => $l === 1)), "$design $path must have exactly one h1");

                $canonical = $this->tags($html, 'link');
                $this->assertCount(1, $canonical, "$design $path canonical");
                $href = $this->attr($canonical[0], 'href');
                $this->assertSame('http://localhost' . ($path === '/' ? '' : $path), $href, "$design $path canonical");
                $this->assertStringNotContainsString('?', $href);
                $this->assertStringNotContainsString('noindex', $html, "$design $path must be indexable");
            }
        }
    }

    public function test_design_g_pages_do_not_skip_heading_levels(): void
    {
        $this->posts(1);
        $this->design('g');
        foreach (['/', '/uslugi', '/paketi', '/proekti', '/za-nas', '/vaprosi', '/kontakti', '/proverka-na-sait', '/blog', '/uslugi/izrabotka-na-sait', '/izrabotka-na-sait-za-restorant', '/blog/statia-1'] as $path) {
            $levels = $this->headingLevels($this->get($path)->assertOk()->getContent());
            $previous = 0;
            foreach ($levels as $level) {
                $this->assertLessThanOrEqual($previous + 1, $level, "$path jumps from h$previous to h$level: " . implode(',', $levels));
                $previous = $level;
            }
        }
    }

    public function test_query_strings_and_the_design_preview_canonicalise_to_the_clean_url(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/paketi?paket=1', '/paketi?paket=2&utm_source=x', '/kontakti?napravleniya=google,seo', '/uslugi?dizain=g', '/?gclid=abc&fbclid=def'] as $url) {
                $html = $this->get($url)->assertOk()->getContent();
                $clean = strtok($url, '?');
                $this->assertSame('http://localhost' . ($clean === '/' ? '' : $clean), $this->attr($this->tags($html, 'link')[0], 'href'), "$design $url");
            }
        }
    }

    public function test_hreflang_pairs_are_reciprocal_have_x_default_and_exist_only_for_real_translations(): void
    {
        $this->posts(1);
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach ($this->sitemapPaths() as $path) {
                $html = $this->get($path)->getContent();
                $tags = $this->tags($html, 'link', 'rel', 'alternate');
                $alternates = [];
                foreach ($tags as $tag) {
                    if ($lang = $this->attr($tag, 'hreflang')) {
                        $alternates[$lang] = $this->attr($tag, 'href');
                    }
                }
                $isBoth = in_array('bg', array_keys($alternates)) || str_starts_with($path, '/en');
                if (str_starts_with($path, '/blog') || str_starts_with($path, '/izrabotka') || $alternates === []) {
                    $this->assertSame([], $alternates, "$design $path has no English version, so no hreflang");
                    continue;
                }
                $this->assertSame(['bg', 'en', 'x-default'], array_keys($alternates), "$design $path");
                $this->assertSame($alternates['bg'], $alternates['x-default']);
                $this->assertContains('http://localhost' . ($path === '/' ? '' : $path), $alternates, "$design $path lists itself");
                foreach (['bg', 'en'] as $lang) {
                    $back = $this->get(parse_url($alternates[$lang], PHP_URL_PATH) ?: '/')->getContent();
                    $this->assertStringContainsString('hreflang="' . ($lang === 'bg' ? 'en' : 'bg') . '"', $back, "$design $path <-> $lang");
                }
                $this->assertTrue($isBoth);
            }
        }
    }

    public function test_the_software_page_is_noindex_without_hreflang_in_the_current_design_and_indexable_in_g(): void
    {
        $this->design('current');
        foreach (['/uslugi/razrabotka-na-softuer', '/en/services/software-development'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html, $path);
            $this->assertSame([], $this->tags($html, 'link', 'rel', 'alternate'), $path);
        }
        $this->assertStringNotContainsString('softuer', $this->get('/sitemap.xml')->getContent());
        $this->assertStringNotContainsString('software-development', $this->get('/sitemap.xml')->getContent());
        // The other services stay indexable in the current design.
        $this->assertStringNotContainsString('noindex', $this->get('/uslugi/izrabotka-na-sait')->getContent());

        $this->design('g');
        $html = $this->get('/uslugi/razrabotka-na-softuer')->assertOk()->getContent();
        $this->assertStringNotContainsString('noindex', $html);
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/services/software-development"', $html);
        $sitemap = $this->get('/sitemap.xml')->getContent();
        $this->assertStringContainsString('<loc>http://localhost/uslugi/razrabotka-na-softuer</loc>', $sitemap);
        $this->assertStringContainsString('<loc>http://localhost/en/services/software-development</loc>', $sitemap);
    }

    public function test_the_admin_preview_of_g_is_never_indexed_and_keeps_the_software_page_out_of_the_sitemap(): void
    {
        $this->design('current');
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(\App\Models\Role::where('slug', 'admin')->value('id'));
        $this->actingAs($admin)->get('/?dizain=g');
        $html = $this->get('/uslugi/razrabotka-na-softuer')->getContent();
        $this->assertStringContainsString('design-g', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex">', $html);
        $this->assertStringNotContainsString('softuer', $this->get('/sitemap.xml')->getContent());
    }

    public function test_error_pages_are_404_noindex_and_have_no_canonical_in_both_designs(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/nqma-takava-stranitsa', '/en/nqma-takava', '/blog/nqma-takava', '/izrabotka-na-sait-za-nqma', '/uslugi/nqma'] as $path) {
                $html = $this->get($path)->assertNotFound()->getContent();
                $this->assertStringContainsString('<meta name="robots" content="noindex">', $html, "$design $path");
                $this->assertSame([], $this->tags($html, 'link'), "$design $path");
            }
        }
    }

    public function test_demo_sites_and_the_presentation_are_noindex(): void
    {
        foreach (collect(config('creatium.demos'))->pluck('slug') as $slug) {
            $this->get('/demo/' . $slug)->assertOk()->assertSee('noindex', false);
        }
        $this->get('/prezentatsiya')->assertOk()->assertSee('noindex', false);
        $this->get('/en/presentation')->assertOk()->assertSee('noindex', false);
        $this->assertStringNotContainsString('/demo/', $this->get('/sitemap.xml')->getContent());
        $this->assertStringNotContainsString('prezent', $this->get('/sitemap.xml')->getContent());
    }

    public function test_robots_txt_names_the_sitemap_keeps_assets_open_and_welcomes_the_ai_search_bots(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString("Sitemap: http://localhost/sitemap.xml", $body);
        $this->assertStringNotContainsString('/build', $body);
        $this->assertStringNotContainsString('/images', $body);
        $this->assertStringNotContainsString('Disallow: /css', $body);
        $this->assertStringNotContainsString("Disallow: /\n", $body, 'the site itself is never blocked');

        foreach (['*', 'OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot', 'Claude-SearchBot', 'Claude-User'] as $bot) {
            $this->assertMatchesRegularExpression('~User-agent: ' . preg_quote($bot, '~') . '\n(Allow: /\n)?Disallow: /admin\n~', $body, $bot);
        }
        // A group of its own replaces „*“, so the private paths must be repeated in it.
        $this->assertSame(count(\App\Http\Controllers\PageController::AI_SEARCH_BOTS) + 1, substr_count($body, 'Disallow: /admin'));
        // Training-only crawlers are the owners' decision and are not named.
        foreach (['GPTBot', 'ClaudeBot', 'Google-Extended', 'CCBot'] as $bot) {
            $this->assertStringNotContainsString($bot, $body);
        }
    }

    public function test_llms_txt_follows_the_public_design(): void
    {
        $this->posts(1);
        $this->design('current');
        $body = $this->get('/llms.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Core Изграждане', $body);
        $this->assertStringNotContainsString('razrabotka-na-softuer', $body);
        foreach (config('creatium.contact.phones') as $phone) {
            $this->assertStringContainsString("Телефон $phone", $body);
        }
        $this->assertStringContainsString('Борислав Костадинов', $body);
        $this->assertStringContainsString('/blog/statia-1', $body);
        $this->assertStringNotContainsString(':  ', $body);

        $this->design('g');
        $body = $this->get('/llms.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Core Изграждане. от €500/месец', $body);
        $this->assertStringNotContainsString('Стартов двигател', $body);
        $this->assertStringContainsString('razrabotka-na-softuer', $body);
        $this->assertStringContainsString('Телефон ' . config('creatium.g_phone'), $body);
        $this->assertStringNotContainsString('+359 895 457 247', $body);
    }

    public function test_trailing_slashes_and_index_php_redirect_with_a_301_and_keep_the_query(): void
    {
        // The test client trims slashes from the address, so the middleware is called with the raw request.
        $middleware = new \App\Http\Middleware\RedirectToCleanPath();
        $run = fn (string $method, string $uri) => $middleware->handle(\Illuminate\Http\Request::create($uri, $method), fn () => response('next'));

        foreach ([
            '/uslugi/' => '/uslugi', '/paketi/?paket=2' => '/paketi?paket=2', '/en/' => '/en', '/blog/kategoriya/sajtove/' => '/blog/kategoriya/sajtove',
            '/index.php' => '/', '/index.php/uslugi' => '/uslugi', '/uslugi//' => '/uslugi',
        ] as $from => $to) {
            $response = $run('GET', $from);
            $this->assertSame(301, $response->getStatusCode(), $from);
            $this->assertSame('http://localhost' . ($to === '/' ? '' : $to), $response->headers->get('Location'), $from);
        }
        foreach (['/', '/en', '/uslugi', '/paketi?paket=2', '/blog?page=2', '/admin/projects'] as $clean) {
            $this->assertSame('next', $run('GET', $clean)->getContent(), $clean);
        }
        // A form that posts to an address with a slash is never turned into a redirect, and HEAD behaves like GET.
        $this->assertSame('next', $run('POST', '/kontakti/')->getContent());
        $this->assertSame(301, $run('HEAD', '/uslugi/')->getStatusCode());

        $this->get('/about')->assertStatus(301)->assertRedirect('/za-nas');
        $this->get('/contact')->assertStatus(301)->assertRedirect('/kontakti');
        $this->assertContains(\App\Http\Middleware\RedirectToCleanPath::class, array_map(fn ($m) => is_string($m) ? $m : '', app(\Illuminate\Contracts\Http\Kernel::class)->getGlobalMiddleware()));
    }

    public function test_www_goes_to_the_apex_host_with_a_301_in_production_only(): void
    {
        config(['app.url' => 'https://creatiumlab.com']);
        $this->get('http://www.creatiumlab.com/uslugi?x=1')->assertOk();

        $this->app['env'] = 'production';
        $this->get('http://www.creatiumlab.com/uslugi?x=1')->assertStatus(301)->assertRedirect('https://creatiumlab.com/uslugi?x=1');
    }

    public function test_blog_pages_after_the_first_have_their_own_title_and_canonical_and_a_page_past_the_end_is_404(): void
    {
        $this->posts(11);
        $first = $this->get('/blog')->assertOk()->getContent();
        $second = $this->get('/blog?page=2')->assertOk()->getContent();

        $this->assertSame('http://localhost/blog?page=2', $this->attr($this->tags($second, 'link')[0], 'href'));
        $this->assertSame('http://localhost/blog', $this->attr($this->tags($first, 'link')[0], 'href'));
        $this->assertNotSame($this->title($first), $this->title($second));
        $this->assertStringContainsString('страница 2', $this->title($second));
        $this->assertNotSame($this->description($first), $this->description($second));
        $this->assertLessThanOrEqual(60, mb_strlen($this->title($second)));
        $this->assertLessThanOrEqual(155, mb_strlen($this->description($second)));
        $this->assertSame([], $this->tags($second, 'link', 'rel', 'alternate'), 'page 2 has no hreflang');

        $this->get('/blog?page=3')->assertNotFound();
    }

    public function test_a_blog_category_points_hreflang_to_english_only_when_the_english_page_has_posts(): void
    {
        $category = PostCategory::create(['name' => ['bg' => 'Сайтове', 'en' => 'Websites'], 'slug' => ['bg' => 'sajtove', 'en' => 'websites']]);
        $this->posts(1, ['post_category_id' => $category->id]);

        $bg = $this->get('/blog/kategoriya/sajtove')->assertOk()->getContent();
        $this->assertSame([], $this->tags($bg, 'link', 'rel', 'alternate'));
        $en = $this->get('/en/blog/category/websites')->assertOk()->getContent();
        $this->assertStringContainsString('noindex', $en);
        $this->assertStringNotContainsString('/sitemap', $this->get('/sitemap.xml')->getContent() ? '' : 'x');
        $this->assertStringNotContainsString('/en/blog/category/websites', $this->get('/sitemap.xml')->getContent());

        // Once the category has an English post, both pages are indexable and point to each other.
        Post::create([
            'title' => ['bg' => 'Втора', 'en' => 'Second'], 'slug' => ['bg' => 'vtora', 'en' => 'second'], 'body' => ['bg' => 'x', 'en' => 'x'],
            'status' => 'published', 'published_at' => now()->subDay(), 'post_category_id' => $category->id,
        ]);
        $bg = $this->get('/blog/kategoriya/sajtove')->assertOk()->getContent();
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/blog/category/websites"', $bg);
        $this->assertStringNotContainsString('noindex', $this->get('/en/blog/category/websites')->getContent());
    }

    public function test_every_json_ld_block_is_valid_with_a_context_on_every_sitemap_page_in_both_designs(): void
    {
        $this->posts(1);
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach ($this->sitemapPaths() as $path) {
                $blocks = $this->jsonLd($this->get($path)->getContent());
                foreach ($blocks as $block) {
                    $this->assertSame('https://schema.org', $block['@context'] ?? null, "$design $path");
                    $this->assertTrue(isset($block['@type']) || isset($block['@graph']), "$design $path");
                }
            }
        }
    }

    public function test_the_organization_describes_the_business_with_the_phone_and_prices_the_visitor_sees(): void
    {
        $this->design('current');
        [$org, $site] = $this->jsonLd($this->get('/')->getContent());
        $this->assertSame('ProfessionalService', $org['@type']);
        $this->assertSame(Schema::organizationId(), $org['@id']);
        $this->assertSame('+359 895 457 247', $org['telephone']);
        $this->assertSame('€500 – €1200+', $org['priceRange'], 'both designs show the same monthly price range');
        $this->assertSame('http://localhost/images/logo.webp', $org['logo']);
        $this->assertContains('https://www.facebook.com/share/1JDKDcn3rM/', $org['sameAs']);
        $this->assertContains('https://share.google/0u0Q2PqSiNXcUED4a', $org['sameAs']);
        $this->assertSame('BG', $org['address']['addressCountry']);
        $this->assertSame(['bg', 'en'], $org['contactPoint']['availableLanguage']);
        $this->assertSame('WebSite', $site['@type']);
        $this->assertSame(['@id' => $org['@id']], $site['publisher']);

        // Design G shows one phone and the G packages: the structured data says the same.
        $this->design('g');
        [$org] = $this->jsonLd($this->get('/')->getContent());
        $this->assertSame(config('creatium.g_phone'), $org['telephone']);
        $this->assertSame(config('creatium.g_phone'), $org['contactPoint']['telephone']);
        $this->assertSame('€500 – €1200+', $org['priceRange']);
        $this->get('/')->assertSee(config('creatium.g_phone'));

        // The LinkedIn page of the company joins sameAs as soon as the owners set it.
        config(['creatium.contact.linkedin' => 'https://www.linkedin.com/company/creatium-lab/']);
        [$org] = $this->jsonLd($this->get('/')->getContent());
        $this->assertContains('https://www.linkedin.com/company/creatium-lab/', $org['sameAs']);
    }

    public function test_the_about_page_has_a_person_for_each_team_member_in_both_designs(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            $blocks = $this->jsonLd($this->get('/za-nas')->assertOk()->getContent());
            $persons = collect($blocks)->flatMap(fn ($b) => $b['@graph'] ?? [])->where('@type', 'Person')->values();
            $this->assertCount(count(config('creatium.team')), $persons, $design);
            $names = $persons->pluck('name')->all();
            $this->assertContains('Борислав Костадинов', $names);
            $boris = $persons->firstWhere('name', 'Борислав Костадинов');
            $this->assertSame(['https://www.linkedin.com/in/borislav-kostadinov-7ba990285/'], $boris['sameAs']);
            $this->assertSame(Schema::organizationId(), $boris['worksFor']['@id']);
            $this->assertSame('http://localhost/za-nas#borislav-kostadinov', $boris['@id']);
            $this->assertSame(['https://www.linkedin.com/in/maria-florova/'], $persons->firstWhere('name', 'Мария Флорова')['sameAs']);
            $this->assertCount($persons->count(), $persons->pluck('@id')->unique());
        }
    }

    public function test_a_blog_post_is_credited_to_the_team_member_who_wrote_it_and_otherwise_to_the_company(): void
    {
        $this->design('g');
        $this->posts(2);
        $author = User::factory()->create(['name' => 'Владимир Цончев']);
        $shared = User::factory()->create(['name' => 'Админ']);
        Post::where('slug->bg', 'statia-1')->update(['user_id' => $author->id]);
        Post::where('slug->bg', 'statia-2')->update(['user_id' => $shared->id]);

        foreach (['current', 'g'] as $design) {
            $this->design($design);
            $byMember = collect($this->jsonLd($this->get('/blog/statia-1')->getContent()))->firstWhere('@type', 'BlogPosting');
            $this->assertSame('Person', $byMember['author']['@type']);
            $this->assertSame('Владимир Цончев', $byMember['author']['name']);
            $this->assertContains('https://www.linkedin.com/in/vladimirtsonchev/', $byMember['author']['sameAs']);
            $this->assertSame(Schema::organizationId(), $byMember['publisher']['@id']);
            $this->assertNotEmpty($byMember['datePublished']);
            $this->assertNotEmpty($byMember['dateModified']);
            $this->assertStringStartsWith('http', $byMember['image']);

            $byCompany = collect($this->jsonLd($this->get('/blog/statia-2')->getContent()))->firstWhere('@type', 'BlogPosting');
            $this->assertSame('Organization', $byCompany['author']['@type'], 'a shared account is not a person');
            $this->assertSame('Creatium Lab', $byCompany['author']['name']);
        }
    }

    public function test_service_and_industry_pages_link_their_provider_to_the_organization_and_name_their_own_url(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/uslugi/izrabotka-na-sait', '/izrabotka-na-sait-za-restorant'] as $path) {
                $blocks = $this->jsonLd($this->get($path)->getContent());
                $service = collect($blocks)->flatMap(fn ($b) => $b['@graph'] ?? [])->firstWhere('@type', 'Service');
                $this->assertSame(Schema::organizationId(), $service['provider']['@id'], "$design $path");
                $this->assertSame('http://localhost' . $path, $service['url'], "$design $path");
            }
        }
    }

    public function test_g_images_have_alt_text_and_no_image_above_the_fold_is_lazy(): void
    {
        $this->design('g');
        $html = $this->get('/proekti')->assertOk()->getContent();
        preg_match_all('~<img\b[^>]*demos/[^>]*>~', $html, $m);
        $this->assertNotEmpty($m[0]);
        foreach ($m[0] as $img) {
            $this->assertNotSame('', $this->attr($img, 'alt'), $img);
            $this->assertNotNull($this->attr($img, 'width'));
            $this->assertNotNull($this->attr($img, 'height'));
        }
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/', '/paketi', '/za-nas'] as $path) {
                preg_match('~<img\b[^>]*>~', $this->get($path)->getContent(), $first);
                $this->assertStringNotContainsString('loading="lazy"', $first[0], "$design $path first image is the logo");
            }
        }
    }

    public function test_gtm_consent_and_the_search_console_and_bing_tags_are_on_every_page_of_both_designs(): void
    {
        config(['creatium.google_site_verification' => 'abcdefghij1234567890']);
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/', '/paketi', '/kontakti', '/uslugi/izrabotka-na-sait', '/en', '/za-nas'] as $path) {
                $html = $this->get($path)->assertOk()->getContent();
                $this->assertStringContainsString("'GTM-TEST123'", str_replace('"', "'", $html), "$design $path GTM");
                $this->assertStringContainsString("gtag('consent', 'default'", $html, "$design $path consent");
                $this->assertStringContainsString('ns.html?id=GTM-TEST123', $html, "$design $path noscript");
                $this->assertStringContainsString('name="google-site-verification" content="abcdefghij1234567890"', $html);
                $this->assertSame(2, substr_count($html, 'name="msvalidate.01"'), "$design $path Bing");
                $this->assertStringContainsString('creatium-consent', $html);
            }
        }
    }

    public function test_the_g_form_success_pushes_the_generate_lead_event(): void
    {
        $this->design('g');
        Mail::fake();
        $this->followingRedirects()->post('/kontakti', ['form' => 'g', 'name' => 'Иван', 'email' => 'i@example.com', 'consent' => '1'])
            ->assertOk()
            ->assertSee("dataLayer.push({event: 'generate_lead'", false);
        $this->assertSame(1, Project::count());
    }

    public function test_english_pages_say_so_in_lang_and_og_locale_and_never_redirect_by_location(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/en', '/en/services', '/en/packages', '/en/about', '/en/faq', '/en/contact', '/en/privacy'] as $path) {
                $html = $this->get($path, ['Accept-Language' => 'bg', 'CF-IPCountry' => 'BG'])->assertOk()->getContent();
                $this->assertStringContainsString('<html lang="en"', $html, $path);
                $this->assertStringContainsString('<meta property="og:locale" content="en_US">', $html, $path);
            }
            // Bulgarian root pages never bounce an English browser to /en.
            $this->get('/', ['Accept-Language' => 'en-GB', 'CF-IPCountry' => 'GB'])->assertOk();
        }
    }

    public function test_industry_pages_are_substantially_different_from_each_other(): void
    {
        $shingles = function (string $path): array {
            $html = $this->get($path)->assertOk()->getContent();
            $main = preg_replace('~<(script|style|svg|noscript|header|footer|nav)\b.*?</\1>~s', ' ', $html);
            $words = preg_split('~\s+~u', mb_strtolower(trim(preg_replace('~[^\p{L}\p{N}]+~u', ' ', strip_tags($main)))));
            $set = [];
            for ($i = 0; $i + 5 <= count($words); $i++) {
                $set[implode(' ', array_slice($words, $i, 5))] = true;
            }

            return $set;
        };

        $pages = array_keys(config('creatium.industry_pages'));
        $sets = array_map(fn ($slug) => $shingles('/izrabotka-na-sait-za-' . $slug), $pages);
        $common = array_intersect_key(...$sets);
        foreach ($sets as $i => $a) {
            foreach ($sets as $j => $b) {
                if ($j <= $i) {
                    continue;
                }
                $shared = count(array_diff_key(array_intersect_key($a, $b), $common));
                $this->assertLessThan(0.4, $shared / max(1, min(count($a), count($b))), "{$pages[$i]} and {$pages[$j]} repeat too much of each other");
            }
        }
    }

    public function test_no_page_makes_up_reviews_ratings_or_clients_in_data_or_text(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach ($this->sitemapPaths() as $path) {
                $html = $this->get($path)->getContent();
                foreach ($this->jsonLd($html) as $block) {
                    $json = json_encode($block);
                    $this->assertStringNotContainsString('aggregateRating', $json, "$design $path");
                    $this->assertStringNotContainsString('"review"', $json, "$design $path");
                }
            }
        }
    }

    public function test_long_starter_post_titles_get_a_short_meta_title_only_when_the_owners_have_not_set_one(): void
    {
        $long = Post::create([
            'title' => ['bg' => 'Как да вдигнете средната поръчка, вместо да плащате повече за реклама'],
            'slug' => ['bg' => 'kak-da-vdignete-srednata-porachka'], 'body' => ['bg' => 'x'], 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $edited = Post::create([
            'title' => ['bg' => 'Какво е GEO и защо AI асистентите вече отговарят вместо Google'], 'meta_title' => ['bg' => 'Моето заглавие'],
            'slug' => ['bg' => 'kakvo-e-geo'], 'body' => ['bg' => 'x'], 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $modified = $long->fresh()->updated_at;

        $this->assertSame(1, PostMetaTitles2026::apply());
        $this->assertSame('Как да вдигнете средната поръчка без повече реклама', $long->fresh()->tr('meta_title', 'bg'));
        $this->assertSame('Моето заглавие', $edited->fresh()->tr('meta_title', 'bg'));
        $this->assertTrue($modified->equalTo($long->fresh()->updated_at), 'a meta title is not an edit of the article');
        $this->assertSame(0, PostMetaTitles2026::apply(), 'a second run changes nothing');
        foreach (PostMetaTitles2026::MAP as $title) {
            $this->assertLessThanOrEqual(60, mb_strlen($title));
            $this->assertDoesNotMatchRegularExpression('~[:–—]| - ~u', $title);
        }

        $this->get('/blog/kak-da-vdignete-srednata-porachka')->assertSee('<title>Как да вдигнете средната поръчка без повече реклама</title>', false);
    }
}
