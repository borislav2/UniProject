<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostCategory;
use App\Support\Geo\TeamFacts;
use App\Support\PostMetaTitles2026;
use App\Support\PostPlainMeta2026;
use App\Support\Schema;
use App\Support\SearchConsoleWins2026;
use App\Support\StarterBlogPosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * GEO/SEO audit of 25.11.2026, six fixes: the team size, a stable Person id, the packages as structured data, the price article,
 * the back-filled updated_at dates and the titles and descriptions.
 */
class AuditFixes2511Test extends TestCase
{
    use RefreshDatabase;

    private function design(string $design): void
    {
        config(['creatium.design' => $design, 'creatium.gtm_id' => 'GTM-TEST123']);
    }

    private function jsonLd(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);

        return array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    private function pageHead(string $html, string $what): string
    {
        if ($what === 'title') {
            preg_match('~<title>(.*?)</title>~s', $html, $m);
        } else {
            preg_match('~<meta name="description" content="([^"]*)"~', $html, $m);
        }

        return html_entity_decode(trim($m[1] ?? ''));
    }

    // 1. Team size

    public function test_about_page_in_design_g_says_how_many_we_are_in_both_languages(): void
    {
        $this->design('g');
        $this->get('/za-nas')->assertOk()->assertSee('Creatium Lab е екип от трима души в София, Борислав, Владимир и Мария.', false);
        $this->get('/en/about')->assertOk()->assertSee('Creatium Lab is a team of three people in Sofia, Borislav, Vladimir and Maria.', false);

        foreach ([config('creatium.about.g_team_line'), config('creatium_en.about.g_team_line')] as $line) {
            $this->assertDoesNotMatchRegularExpression('/[–—:]| - /u', str_replace(['{count}', '{names}'], '', $line));
        }
    }

    public function test_the_team_line_follows_the_team_block(): void
    {
        $this->assertSame('трима', TeamFacts::word(3, 'bg'));
        $this->assertSame('three', TeamFacts::word(3, 'en'));
        $this->assertSame('12', TeamFacts::word(12, 'bg'));
        $this->assertSame(3, TeamFacts::count());

        config(['creatium.team' => array_slice(config('creatium.team'), 0, 2)]);
        $this->assertSame('Creatium Lab е екип от двама души в София, Борислав и Владимир.', TeamFacts::line());
    }

    public function test_the_team_line_is_an_editable_key_with_a_label(): void
    {
        $this->assertArrayHasKey('g_team_line', \App\Support\Content::FIELD_LABELS);
        $this->assertNotEmpty(config('creatium.about.g_team_line'));
        $this->assertNotEmpty(config('creatium_en.about.g_team_line'));
        // An owner's own sentence replaces the template as it is.
        \App\Models\ContentBlock::create(['key' => 'about', 'locale' => 'bg', 'value' => ['g_team_line' => 'Ние сме малък екип.']]);
        Cache::flush();
        $this->assertSame('Ние сме малък екип.', TeamFacts::line());
    }

    public function test_llms_files_state_the_team_size_list_everyone_and_name_the_founders(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            Cache::flush();
            foreach (['/llms.txt' => 'bg', '/llms-full.txt' => 'bg', '/en/llms-full.txt' => 'en'] as $path => $locale) {
                $body = $this->get($path)->getContent();
                $heading = $locale === 'en' ? '## The team (three people)' : '## Екипът (трима души)';
                $this->assertStringContainsString($heading, $body, "$design $path");
                $section = substr($body, strpos($body, $heading));
                $section = substr($section, 0, strpos($section, "\n## ", 5) ?: null);
                foreach ($locale === 'en' ? ['Borislav Kostadinov', 'Vladimir Tsonchev', 'Maria Florova'] : ['Борислав Костадинов', 'Владимир Цончев', 'Мария Флорова'] as $name) {
                    $this->assertStringContainsString($name, $section, "$design $path $name");
                }
                $note = $locale === 'en'
                    ? 'The founders and owners of Creatium Lab are Borislav and Vladimir. Maria is part of the team.'
                    : 'Основатели и собственици на Creatium Lab са Борислав и Владимир. Мария е част от екипа.';
                $this->assertStringContainsString($note, $section, "$design $path");
                $this->assertStringNotContainsString($locale === 'en' ? 'Founders and owners' : 'Колеги от екипа', $body);
                $this->assertDoesNotMatchRegularExpression($locale === 'en' ? '/Maria[^.\n]*founder/i' : '/Мария[^.\n]*(основател|собственик)/ui', $section);
            }
        }
    }

    public function test_the_organization_states_the_number_of_employees(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/', '/en'] as $path) {
                $org = collect($this->jsonLd($this->get($path)->getContent()))->firstWhere('@type', 'ProfessionalService');
                $this->assertSame(['@type' => 'QuantitativeValue', 'value' => 3], $org['numberOfEmployees'], "$design $path");
            }
        }
        config(['creatium.team' => array_slice(config('creatium.team'), 0, 2)]);
        $org = collect($this->jsonLd($this->get('/')->getContent()))->firstWhere('@type', 'ProfessionalService');
        $this->assertSame(2, $org['numberOfEmployees']['value']);
    }

    // 2. Person id

    public function test_person_id_is_the_same_on_the_bulgarian_and_the_english_page(): void
    {
        $ids = [];
        foreach (['bg' => '/za-nas', 'en' => '/en/about'] as $locale => $path) {
            $this->design('g');
            $persons = collect($this->jsonLd($this->get($path)->getContent()))->flatMap(fn ($b) => $b['@graph'] ?? [])->where('@type', 'Person')->values();
            $ids[$locale] = $persons->pluck('@id')->all();
        }
        $this->assertSame($ids['bg'], $ids['en']);
        $this->assertSame('http://localhost/za-nas#borislav-kostadinov', $ids['bg'][0]);
        $this->assertSame('http://localhost/za-nas#' . \Illuminate\Support\Str::slug('Мария Флорова', '-', 'bg'), $ids['bg'][2]);
        $this->assertSame('http://localhost/za-nas#vladimir-tsonchev', $ids['bg'][1]);
    }

    public function test_founders_employees_and_authors_use_the_same_ids_in_both_languages(): void
    {
        foreach (['/' => '/za-nas', '/en' => '/en/about'] as $home => $about) {
            $this->design('g');
            $org = collect($this->jsonLd($this->get($home)->getContent()))->firstWhere('@type', 'ProfessionalService');
            $persons = collect($this->jsonLd($this->get($about)->getContent()))->flatMap(fn ($b) => $b['@graph'] ?? [])->where('@type', 'Person')->values();
            $refs = array_merge(array_column($org['founder'], '@id'), array_column($org['employee'], '@id'));
            $this->assertSame($persons->pluck('@id')->all(), $refs, $home);
            $this->assertSame('http://localhost/za-nas#vladimir-tsonchev', $refs[1]);
            foreach ($persons as $person) {
                $this->assertSame(Schema::organizationId(), $person['worksFor']['@id']);
            }
        }

        // The author of a post written by Vladimir carries the same id in English.
        $user = \App\Models\User::factory()->create(['name' => 'Владимир Цончев']);
        Post::create([
            'title' => ['bg' => 'Статия', 'en' => 'Article'], 'slug' => ['bg' => 'statia', 'en' => 'article'], 'body' => ['bg' => 'Текст', 'en' => 'Text'],
            'status' => 'published', 'published_at' => now()->subDay(), 'user_id' => $user->id,
        ]);
        foreach (['/blog/statia', '/en/blog/article'] as $path) {
            $posting = collect($this->jsonLd($this->get($path)->getContent()))->firstWhere('@type', 'BlogPosting');
            $this->assertSame('http://localhost/za-nas#vladimir-tsonchev', $posting['author']['@id'], $path);
        }
    }

    // 3. Packages in JSON-LD

    public function test_packages_page_has_an_offer_catalog_with_monthly_and_yearly_prices(): void
    {
        $this->design('g');
        foreach (['/paketi' => ['bg', ['Core Изграждане', 'Pro Ускорение', 'Enterprise Партньорство'], 'България'], '/en/packages' => ['en', ['Core Build', 'Pro Accelerate', 'Enterprise Partnership'], 'Bulgaria']] as $path => [$locale, $names, $country]) {
            $catalog = collect($this->jsonLd($this->get($path)->assertOk()->getContent()))->firstWhere('@type', 'OfferCatalog');
            $this->assertNotNull($catalog, $path);
            $this->assertCount(3, $catalog['itemListElement']);
            $this->assertSame($names, array_column($catalog['itemListElement'], 'name'), $path);
            foreach ([500, 800, 1200] as $i => $monthly) {
                $service = $catalog['itemListElement'][$i];
                $this->assertSame('Service', $service['@type']);
                $this->assertSame(Schema::organizationId(), $service['provider']['@id']);
                $this->assertSame($country, $service['areaServed']['name']);
                $this->assertSame('EUR', $service['offers']['priceCurrency']);
                [$month, $year] = $service['offers']['priceSpecification'];
                $this->assertSame('UnitPriceSpecification', $month['@type']);
                $this->assertSame($monthly, $month['minPrice']);
                $this->assertSame('MON', $month['unitCode']);
                $this->assertSame('P1M', $month['billingDuration']);
                $this->assertSame('EUR', $month['priceCurrency']);
                $this->assertSame($monthly * 10, $year['minPrice']);
                $this->assertSame('P1Y', $year['billingDuration']);
            }
        }
    }

    public function test_a_plan_without_from_states_an_exact_price_and_the_current_design_has_no_catalog(): void
    {
        $plans = config('creatium.plans');
        $plans['g_items'][0]['from'] = '';
        $catalog = Schema::packages($plans);
        $this->assertSame(500, $catalog['itemListElement'][0]['offers']['priceSpecification'][0]['price']);
        $this->assertArrayNotHasKey('minPrice', $catalog['itemListElement'][0]['offers']['priceSpecification'][0]);

        $this->design('current');
        $this->assertNull(collect($this->jsonLd($this->get('/paketi')->getContent()))->firstWhere('@type', 'OfferCatalog'));
    }

    // 4. The price article

    private function priceSlug(): string
    {
        return 'kolko-struva-sait-za-malak-biznes-2026';
    }

    private function dataPost(): array
    {
        return collect(StarterBlogPosts::data('daily-2026-11-a.php')['posts'])->firstWhere('slug', $this->priceSlug());
    }

    public function test_the_price_article_states_our_real_prices_in_plain_sentences(): void
    {
        $data = $this->dataPost();
        foreach ([[$data['body'], ['Core Изграждане е от €500 на месец', 'Pro Ускорение е от €800 на месец', 'от €1 200 на месец', '€5 000, €8 000 и €12 000', 'Няма еднократна стартова цена и няма капаро', '(/paketi)']],
            [$data['body_en'], ['Core Build package is from €500 a month', 'Pro Accelerate package is from €800 a month', 'from €1,200 a month', '€5,000, €8,000 and €12,000', 'no one time start price and there is no deposit', '(/en/packages)']]] as [$body, $needles]) {
            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $body);
            }
            $this->assertDoesNotMatchRegularExpression('/[–—]| - |\|/u', $body, 'no dashes and no tables');
            $this->assertDoesNotMatchRegularExpression('/^[-*] /m', $body, 'no bullet lists');
        }
        // No colons outside Markdown links and the like.
        $this->assertDoesNotMatchRegularExpression('/(?<!\d):(?!\d|\/\/)/u', preg_replace('~\]\([^)]*\)~', '', $data['body']));
        $this->assertDoesNotMatchRegularExpression('/(?<!\d):(?!\d|\/\/)/u', preg_replace('~\]\([^)]*\)~', '', $data['body_en']));
    }

    public function test_previous_texts_are_kept_and_differ_from_the_new_ones(): void
    {
        $data = $this->dataPost();
        $this->assertNotSame(trim($data['body']), StarterBlogPosts::refreshedPrevious($this->priceSlug(), 'bg'));
        $this->assertNotSame(trim($data['body_en']), StarterBlogPosts::refreshedPrevious($this->priceSlug(), 'en'));
        $this->assertStringNotContainsString('€500', StarterBlogPosts::refreshedPrevious($this->priceSlug(), 'bg'));
        $this->assertStringNotContainsString('€500', StarterBlogPosts::refreshedPrevious($this->priceSlug(), 'en'));
    }

    public function test_refresh_replaces_each_language_only_where_it_is_still_the_previous_text(): void
    {
        $data = $this->dataPost();
        $slug = $this->priceSlug();
        $make = fn (array $body) => Post::create([
            'title' => ['bg' => 'Колко струва', 'en' => 'Cost'], 'slug' => ['bg' => $slug, 'en' => 'cost'], 'body' => $body,
            'status' => 'published', 'published_at' => now()->subDays(5),
        ]);
        $prev = fn ($l) => StarterBlogPosts::refreshedPrevious($slug, $l);

        $post = $make(['bg' => $prev('bg'), 'en' => $prev('en')]);
        $post->timestamps = false;
        $post->updated_at = now()->subDays(5);
        $post->save();
        $this->assertSame(['bg' => 1, 'en' => 1], StarterBlogPosts::refreshBodies());
        $post = $post->fresh();
        $this->assertSame(trim($data['body']), $post->body['bg']);
        $this->assertSame(trim($data['body_en']), $post->body['en']);
        $this->assertTrue($post->updated_at->isToday(), 'a real content change moves updated_at');
        $this->assertSame(['bg' => 0, 'en' => 0], StarterBlogPosts::refreshBodies(), 'running twice changes nothing');
        $post->forceDelete();

        // An edit by the owners in one language stays, the other language is still refreshed.
        $post = $make(['bg' => 'Нашият собствен текст.', 'en' => $prev('en')]);
        $this->assertSame(['bg' => 0, 'en' => 1], StarterBlogPosts::refreshBodies());
        $post->refresh();
        $this->assertSame('Нашият собствен текст.', $post->body['bg']);
        $this->assertSame(trim($data['body_en']), $post->body['en']);
        $post->forceDelete();

        $post = $make(['bg' => $prev('bg'), 'en' => 'Our own English text.']);
        $this->assertSame(['bg' => 1, 'en' => 0], StarterBlogPosts::refreshBodies());
        $this->assertSame('Our own English text.', $post->fresh()->body['en']);
    }

    public function test_a_fresh_install_gets_the_new_text_from_the_data_files(): void
    {
        StarterBlogPosts::import(now()->subYear(), 'daily-2026-11-a.php');
        $post = Post::where('slug->bg', $this->priceSlug())->first();
        $this->assertStringContainsString('€500 на месец', $post->body['bg']);
        $this->assertStringContainsString('€500 a month', $post->body['en']);
    }

    // 5. updated_at

    private function oldPost(string $slug, ?string $file = null, array $extra = []): Post
    {
        $post = Post::create($extra + [
            'title' => ['bg' => 'Тест ' . $slug], 'slug' => ['bg' => $slug], 'body' => ['bg' => 'x'],
            'status' => 'published', 'published_at' => '2026-11-10 09:00:00',
        ]);
        $post->timestamps = false;
        $post->created_at = '2026-11-10 08:00:00';
        $post->updated_at = '2026-11-10 09:00:00';
        $post->save();

        return $post->fresh();
    }

    public function test_back_fills_never_touch_updated_at(): void
    {
        // English back-fill
        $post = $this->oldPost($this->priceSlug());
        $this->assertSame(1, StarterBlogPosts::fillEnglish('daily-2026-11-a.php'));
        $this->assertNotEmpty($post->fresh()->tr('title', 'en'));
        $this->assertSame('2026-11-10 09:00:00', $post->fresh()->updated_at->toDateTimeString());

        // Covers back-fill
        $slug = 'kak-chatgpt-izbira-koi-firmi-da-preporacha';
        $cover = $this->oldPost($slug);
        $this->assertGreaterThanOrEqual(1, StarterBlogPosts::fillCovers('daily-2026-11-a.php'));
        $this->assertNotEmpty($cover->fresh()->cover_image);
        $this->assertSame('2026-11-10 09:00:00', $cover->fresh()->updated_at->toDateTimeString());

        // Longer starter text
        $expanded = $this->oldPost('kak-da-izberete-domeyn-za-biznesa-si', null, ['body' => ['bg' => StarterBlogPosts::previousBody('kak-da-izberete-domeyn-za-biznesa-si')]]);
        StarterBlogPosts::expand();
        $this->assertNotSame(StarterBlogPosts::previousBody('kak-da-izberete-domeyn-za-biznesa-si'), $expanded->fresh()->body['bg']);
        $this->assertSame('2026-11-10 09:00:00', $expanded->fresh()->updated_at->toDateTimeString());
    }

    public function test_publishing_drafts_and_social_stamps_keep_updated_at(): void
    {
        $slug = $this->priceSlug();
        $draft = $this->oldPost($slug, null, ['status' => 'draft', 'published_at' => null]);
        StarterBlogPosts::publishDraftsAt(now()->subDay(), 'daily-2026-11-a.php');
        $this->assertSame('published', $draft->fresh()->status);
        $this->assertSame('2026-11-10 09:00:00', $draft->fresh()->updated_at->toDateTimeString());

        config(['creatium.indexnow.enabled' => true]);
        \Illuminate\Support\Facades\Http::fake();
        \App\Support\Seo\IndexNow::submitNewPosts();
        $this->assertNotNull($draft->fresh()->indexnow_at);
        $this->assertSame('2026-11-10 09:00:00', $draft->fresh()->updated_at->toDateTimeString());
    }

    public function test_bulk_timestamps_go_back_to_the_publish_date_only_inside_the_windows(): void
    {
        $slugs = collect(StarterBlogPosts::data('daily-2026-11-a.php')['posts'])->pluck('slug')->all();
        $set = function (Post $post, string $updated) {
            $post->timestamps = false;
            $post->updated_at = $updated;
            $post->save();
        };

        $first = $this->oldPost($slugs[0]);
        $set($first, '2026-11-24 20:08:00');                  // inside the first window
        $second = $this->oldPost($slugs[1]);
        $set($second, '2026-11-25 07:44:00');                 // 9 minutes before the second window
        $third = $this->oldPost($slugs[2]);
        $set($third, '2026-11-25 11:20:00');                  // 11 minutes after the third window: an edit
        $edited = $this->oldPost($slugs[3]);
        $set($edited, '2026-11-18 15:30:00');                 // an edit on another day
        $same = $this->oldPost($slugs[4]);                    // never bumped
        $noDate = $this->oldPost($slugs[5], null, ['status' => 'draft', 'published_at' => null]);
        $set($noDate, '2026-11-25 11:09:30');                 // no publish date: the creation date
        $foreign = $this->oldPost('ne-e-ot-failovete');       // not a starter post
        $set($foreign, '2026-11-25 07:53:00');

        $this->assertSame(3, StarterBlogPosts::restoreBulkTimestamps());

        $this->assertSame('2026-11-10 09:00:00', $first->fresh()->updated_at->toDateTimeString());
        $this->assertSame('2026-11-10 09:00:00', $second->fresh()->updated_at->toDateTimeString());
        $this->assertSame('2026-11-25 11:20:00', $third->fresh()->updated_at->toDateTimeString());
        $this->assertSame('2026-11-18 15:30:00', $edited->fresh()->updated_at->toDateTimeString());
        $this->assertSame('2026-11-10 09:00:00', $same->fresh()->updated_at->toDateTimeString());
        $this->assertSame('2026-11-10 08:00:00', $noDate->fresh()->updated_at->toDateTimeString());
        $this->assertSame('2026-11-25 07:53:00', $foreign->fresh()->updated_at->toDateTimeString());
        $this->assertSame(0, StarterBlogPosts::restoreBulkTimestamps(), 'running twice changes nothing');
    }

    public function test_the_visible_updated_line_and_date_modified_are_right_after_the_fix(): void
    {
        $this->design('g');
        $post = $this->oldPost($this->priceSlug());
        $post->timestamps = false;
        $post->updated_at = '2026-11-25 11:10:00';
        $post->save();
        $this->assertStringContainsString('Обновено', $this->get('/blog/' . $this->priceSlug())->getContent());

        StarterBlogPosts::restoreBulkTimestamps();
        $html = $this->get('/blog/' . $this->priceSlug())->getContent();
        $this->assertStringNotContainsString('Обновено', $html);
        $posting = collect($this->jsonLd($html))->firstWhere('@type', 'BlogPosting');
        $this->assertStringStartsWith('2026-11-10', $posting['dateModified']);
    }

    // 6. Titles and descriptions

    public function test_the_short_titles_and_descriptions_are_long_enough(): void
    {
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/usloviya', '/en/cookies', '/en/privacy'] as $path) {
                $title = $this->pageHead($this->get($path)->assertOk()->getContent(), 'title');
                $this->assertGreaterThanOrEqual(30, mb_strlen($title), "$design $path $title");
                $this->assertLessThanOrEqual(60, mb_strlen($title), "$design $path $title");
            }
            $this->assertStringStartsWith('Общи условия', $this->pageHead($this->get('/usloviya')->getContent(), 'title'));
            $this->assertStringStartsWith('Cookie policy', $this->pageHead($this->get('/en/cookies')->getContent(), 'title'));
            $this->assertStringStartsWith('Privacy policy', $this->pageHead($this->get('/en/privacy')->getContent(), 'title'));

            foreach (['/en/terms', '/uslugi/ai-agenti', '/en/services/ai-agents'] as $path) {
                $description = $this->pageHead($this->get($path)->assertOk()->getContent(), 'description');
                $this->assertGreaterThanOrEqual(120, mb_strlen($description), "$design $path $description");
                $this->assertLessThanOrEqual(155, mb_strlen($description), "$design $path $description");
            }
        }
    }

    public function test_blog_category_descriptions_name_the_topic_and_the_languages(): void
    {
        $category = PostCategory::create(['name' => ['bg' => 'Поддръжка и сигурност', 'en' => 'Maintenance and security'], 'slug' => ['bg' => 'poddrazhka-i-sigurnost', 'en' => 'maintenance-and-security']]);
        Post::create([
            'post_category_id' => $category->id, 'title' => ['bg' => 'Статия', 'en' => 'Article'], 'slug' => ['bg' => 'statia', 'en' => 'article'],
            'body' => ['bg' => 'Текст', 'en' => 'Text'], 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        foreach (['current', 'g'] as $design) {
            $this->design($design);
            $bg = $this->pageHead($this->get('/blog/kategoriya/poddrazhka-i-sigurnost')->assertOk()->getContent(), 'description');
            $en = $this->pageHead($this->get('/en/blog/category/maintenance-and-security')->assertOk()->getContent(), 'description');
            foreach ([$bg, $en] as $description) {
                $this->assertGreaterThanOrEqual(110, mb_strlen($description), $description);
                $this->assertLessThanOrEqual(155, mb_strlen($description), $description);
            }
            $this->assertStringContainsString('Поддръжка и сигурност', $bg);
            $this->assertStringContainsString('на български и на английски', $bg);
            $this->assertStringContainsString('Maintenance and security', $en);
            $this->assertStringContainsString('in English and in Bulgarian', $en);
        }
    }

    public function test_the_home_descriptions_have_no_dash_and_every_default_title_and_description_is_plain(): void
    {
        foreach (glob(database_path('data/*.php')) as $path) {
            StarterBlogPosts::import(now()->subYear(), basename($path));
        }
        Post::query()->update(['status' => 'published', 'published_at' => now()->subDay()]);
        StarterBlogPosts::expand();
        PostMetaTitles2026::apply();
        SearchConsoleWins2026::apply();
        PostPlainMeta2026::apply();

        foreach (['current', 'g'] as $design) {
            $this->design($design);
            foreach (['/', '/en', '/en/faq', '/faq'] as $path) {
                $description = $this->pageHead($this->get($path)->getContent(), 'description');
                $this->assertDoesNotMatchRegularExpression('/[–—]/u', $description, "$design $path");
            }
            preg_match_all('~<loc>([^<]+)</loc>~', $this->get('/sitemap.xml')->getContent(), $m);
            foreach (array_unique(array_map(fn ($u) => parse_url($u, PHP_URL_PATH) ?: '/', $m[1])) as $path) {
                $html = $this->get($path)->getContent();
                $this->assertDoesNotMatchRegularExpression('/[–—:]| - /u', $this->pageHead($html, 'title') . ' ' . $this->pageHead($html, 'description'), "$design $path");
            }
        }
    }

    public function test_plain_post_meta_fills_empty_fields_only_and_never_owner_edits(): void
    {
        foreach (PostPlainMeta2026::POSTS as $slug => $fields) {
            foreach ($fields as $field => [$new]) {
                $this->assertDoesNotMatchRegularExpression('/[–—:]| - /u', $new, "$slug $field");
                $max = $field === 'meta_title' ? 60 : 155;
                $this->assertLessThanOrEqual($max, mb_strlen($new), "$slug $field");
                $this->assertGreaterThanOrEqual($field === 'meta_title' ? 30 : 70, mb_strlen($new), "$slug $field");
            }
        }

        $empty = Post::create(['title' => ['bg' => 'Имейл: добре'], 'slug' => ['bg' => 'imeyl-za-dobre-doshli'], 'body' => ['bg' => 'x'], 'status' => 'published', 'published_at' => '2026-11-10 09:00:00']);
        $owned = Post::create(['title' => ['bg' => 'Отзиви: как'], 'slug' => ['bg' => 'otzivi-v-google'], 'body' => ['bg' => 'x'], 'meta_title' => ['bg' => 'Моето заглавие'], 'status' => 'published', 'published_at' => '2026-11-10 09:00:00']);
        $before = $empty->fresh()->updated_at->toDateTimeString();

        $this->assertSame(1, PostPlainMeta2026::apply());
        $this->assertSame('Имейл за добре дошли, първото писмо към нов абонат', $empty->fresh()->tr('meta_title', 'bg'));
        $this->assertSame('Моето заглавие', $owned->fresh()->tr('meta_title', 'bg'));
        $this->assertSame('Имейл: добре', $empty->fresh()->tr('title', 'bg'), 'the h1 title is untouched');
        $this->assertSame($before, $empty->fresh()->updated_at->toDateTimeString());
        $this->assertSame(0, PostPlainMeta2026::apply(), 'running twice changes nothing');
    }

    public function test_the_black_friday_description_never_exceeds_the_limit(): void
    {
        StarterBlogPosts::import(now()->subYear(), 'summit-posts.php');
        Post::query()->update(['status' => 'published', 'published_at' => now()->subDay()]);
        SearchConsoleWins2026::apply();
        $html = $this->get('/blog/cheren-petak-2026-danni-za-balgarskiya-kupuvach')->assertOk()->getContent();
        $this->assertLessThanOrEqual(155, mb_strlen($this->pageHead($html, 'description')));
        $this->assertGreaterThanOrEqual(70, mb_strlen($this->pageHead($html, 'description')));
    }
}
