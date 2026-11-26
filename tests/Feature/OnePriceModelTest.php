<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\ClientEmails;
use App\Support\Chat\FaqResponder;
use App\Support\Chat\SiteKnowledge;
use App\Support\OnePriceModel2026;
use App\Support\PlanBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Един модел на цените навсякъде (17.11.2026, решение на собствениците): като в дизайн Г, само месечно, без капаро.
 * Core Изграждане от €500, Pro Ускорение от €800, Enterprise Партньорство от €1 200 на месец; годишно €5 000, €8 000, €12 000.
 */
class OnePriceModelTest extends TestCase
{
    use RefreshDatabase;

    private function urls(): array
    {
        preg_match_all('#<loc>([^<]+)</loc>#', $this->get('/sitemap.xml')->getContent(), $m);
        $paths = array_map(fn ($url) => parse_url($url, PHP_URL_PATH) ?: '/', $m[1]);

        return array_values(array_unique(array_merge($paths, [
            '/llms.txt', '/en/llms.txt', '/paketi', '/en/packages', '/usloviya', '/en/terms', '/vaprosi', '/en/faq', '/za-nas', '/en/about',
            '/prezentatsiya', '/en/presentation', '/kontakti', '/en/contact', '/uslugi', '/en/services', '/proekti', '/en/projects',
        ], array_map(fn ($slug) => '/izrabotka-na-sait-za-' . $slug, array_keys(config('creatium.industry_pages'))))));
    }

    /** The text of a page without the form budget options („€500 до €800“, „€2 000 до €5 000“ are budget ranges, not prices). */
    private function body(string $path): ?string
    {
        $response = $this->get($path);
        if ($response->status() === 404) {
            return null;
        }
        $body = preg_replace('#<option\b[^>]*>.*?</option>#si', ' ', $response->getContent());
        // The website check page names the one time price of the separate offer „Оправяме трите най-важни проблема“ (not a package price).
        $body = str_replace(\App\Support\SiteCheck\Offers::priceLine('fix'), ' ', $body);

        return $body;
    }

    public function test_no_page_of_either_design_and_language_mentions_the_old_prices_or_a_deposit(): void
    {
        $failures = [];
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            foreach ($this->urls() as $path) {
                $body = $this->body($path);
                if ($body === null) {
                    continue;
                }
                if (preg_match_all(OnePriceModel2026::OLD_PATTERN, $body, $m)) {
                    $failures[] = "$design $path " . implode(', ', array_unique($m[0]));
                }
                foreach (['капаро', 'депозит', 'deposit', 'не се връща', 'non-refundable', 'one-off', 'еднократно'] as $word) {
                    if (mb_stripos($body, $word) !== false) {
                        $failures[] = "$design $path $word";
                    }
                }
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_the_pages_that_show_prices_show_the_design_g_figures_in_both_designs(): void
    {
        $norm = fn (string $html) => preg_replace('/[\x{00A0}\x{202F}\s]+/u', ' ', \Tests\Feature\PlainCopyTest::visibleText($html));

        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            $bg = $norm($this->get('/paketi')->assertOk()->getContent());
            foreach (['€500', '€800', '€1 200', '€5 000', '€8 000', '€12 000'] as $price) {
                $this->assertStringContainsString($price, $bg, "$design /paketi $price");
            }
            $en = $norm($this->get('/en/packages')->assertOk()->getContent());
            foreach (['€500', '€800', '€5,000', '€8,000', '€12,000'] as $price) {
                $this->assertStringContainsString($price, $en, "$design /en/packages $price");
            }
        }

        // Both designs: the FAQ, the terms, llms.txt and the presentation carry the same model.
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            $faq = $norm($this->get('/vaprosi')->assertOk()->getContent());
            $this->assertStringContainsString('Core Изграждане е от €500 на месец', $faq);
            $this->assertStringContainsString('Плащането е месечно, по абонамент.', $faq);
            $this->assertStringContainsString('€5 000, €8 000 и €12 000 на година', $faq);
            $terms = $norm($this->get('/usloviya')->assertOk()->getContent());
            $this->assertStringContainsString('Плащате само месечен абонамент според избрания пакет.', $terms);
            $this->assertStringContainsString('€5 000 вместо €6 000, €8 000 вместо €9 600 и €12 000 вместо €14 400', $terms);
            $this->assertStringContainsString('Последна промяна 17.11.2026', $terms);
            $enTerms = $norm($this->get('/en/terms')->assertOk()->getContent());
            $this->assertStringContainsString('You pay only a monthly subscription according to the package you choose.', $enTerms);
            $this->assertStringContainsString('Last updated 17 November 2026', $enTerms);
            $llms = $this->get('/llms.txt')->assertOk()->getContent();
            $this->assertStringContainsString('от €500', $llms);
            $this->assertStringContainsString('от €800', $llms);
            $this->assertStringContainsString('от €1', $llms);
            $deck = $norm($this->get('/prezentatsiya')->assertOk()->getContent());
            $this->assertStringContainsString('€500', $deck);
        }
    }

    public function test_the_guarantee_stays_and_no_minimum_term_is_invented(): void
    {
        $this->assertStringContainsString('60 дни', config('creatium.plans.guarantee.text'));
        $this->assertStringContainsString('60 days', config('creatium_en.plans.guarantee.text'));
        $this->get('/usloviya')->assertOk()->assertSee('Ако 60 дни след пускането на сайта той няма трафик');

        $all = json_encode([config('creatium.plans'), config('creatium_en.plans'), config('creatium.questions'), config('creatium_en.questions')], JSON_UNESCAPED_UNICODE);
        foreach (['минимален срок', 'minimum term', 'минимум 12', 'договор за 12'] as $invented) {
            $this->assertStringNotContainsStringIgnoringCase($invented, $all);
        }
    }

    public function test_kreo_knowledge_and_the_faq_answer_state_the_model_in_both_designs_and_languages(): void
    {
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            foreach (['bg', 'en'] as $locale) {
                app()->setLocale($locale);
                $knowledge = SiteKnowledge::text();
                $this->assertDoesNotMatchRegularExpression(OnePriceModel2026::OLD_PATTERN, $knowledge, "$design $locale");
                $this->assertStringNotContainsStringIgnoringCase('deposit', $knowledge);
                $this->assertStringContainsString($locale === 'en' ? 'from €500 / month' : 'от €500 / месец', $knowledge);
                $this->assertStringContainsString($locale === 'en' ? 'monthly subscription only, with no one-time start price' : 'само месечен абонамент, без еднократна стартова цена', mb_strtolower($knowledge));
                $answer = FaqResponder::answer($locale === 'en' ? 'How much does a website cost?' : 'Колко струва сайт?');
                $this->assertDoesNotMatchRegularExpression(OnePriceModel2026::OLD_PATTERN, $answer, "$design $locale");
            }
        }
    }

    public function test_the_offer_defaults_have_no_start_price_and_no_deposit_and_the_old_saved_sends_still_render(): void
    {
        foreach (['offer-start' => ['500', '€5 000 вместо €6 000'], 'offer-active' => ['800', '€8 000 вместо €9 600'], 'offer-individual' => ['от €1 200', '']] as $key => [$monthly, $yearly]) {
            $fields = ClientEmails::find($key)['fields'];
            $this->assertSame('', $fields['price']['default'], $key);
            $this->assertFalse($fields['price']['required'], $key);
            $this->assertSame($monthly, $fields['monthly_price']['default'], $key);
            $this->assertTrue($fields['monthly_price']['required'], $key);
            $this->assertStringStartsWith('Плащането е месечно, по абонамент', $fields['payment']['default'], $key);
            $this->assertStringContainsString($yearly, $fields['yearly_terms']['default'], $key);

            $data = collect($fields)->map(fn ($f) => $f['default'] ?? '')->all() + ['client_name' => 'Мария', 'business' => 'Студио'];
            $html = view('emails.client.offer', ['data' => $data, 'package' => ClientEmails::find($key)['package'], 'subject' => 'x'])->render();
            $text = \App\Support\PlainText::fromHtml($html);
            $this->assertDoesNotMatchRegularExpression(OnePriceModel2026::OLD_PATTERN, $html, $key);
            $this->assertStringNotContainsStringIgnoringCase('остатък от старта', $html);
            $this->assertStringContainsString('/ месец', $text);
            // Only the monthly cell: the single price box is full width.
            $this->assertSame(1, substr_count($html, 'width="100%" valign="top" align="center"'), $key);
        }

        // An old send (reused through „Използвай отново“) keeps both prices.
        $old = view('emails.client.offer', ['data' => ['client_name' => 'Ивон', 'business' => 'Pet Bowls', 'price' => '400', 'monthly_price' => '200', 'price_label' => 'Изграждане и първи месец'], 'package' => 'start', 'subject' => 'x'])->render();
        $this->assertStringContainsString('€400', $old);
        $this->assertStringContainsString('€200', $old);
        $this->assertSame(2, substr_count($old, 'width="50%" valign="top" align="center"'));
    }

    public function test_the_inquiry_reply_and_the_other_client_emails_have_no_old_prices(): void
    {
        foreach (array_keys(ClientEmails::all()) as $key) {
            $fields = ClientEmails::find($key)['fields'];
            $data = collect($fields)->map(fn ($f) => $f['default'] ?? '')->all() + ['client_name' => 'Мария', 'business' => 'Студио'];
            $template = ClientEmails::find($key);
            $html = view($template['view'], ['data' => $data, 'package' => $template['package'] ?? null, 'subject' => 'x'])->render();
            $this->assertDoesNotMatchRegularExpression(OnePriceModel2026::OLD_PATTERN, $html, $key);
        }
    }

    public function test_the_card_helper_and_the_plan_data_have_no_start_price(): void
    {
        $plans = config('creatium.plans');
        $billing = PlanBilling::config($plans);
        foreach ($plans['items'] as $i => $item) {
            $this->assertNull(PlanBilling::startPrice($item));
            $this->assertSame('', $item['deposit']);
            $this->assertSame($plans['g_items'][$i]['monthly'], PlanBilling::monthlyPrice($item));
            $this->assertSame($plans['g_items'][$i]['yearly'], PlanBilling::figures($billing, $item, $i, 'yearly')['once']);
        }
        foreach (config('creatium_en.plans.items') as $i => $item) {
            $this->assertSame(config('creatium.plans.g_items.' . $i . '.monthly'), PlanBilling::monthlyPrice($item));
        }
    }

    public function test_the_service_and_industry_pages_name_only_the_monthly_price(): void
    {
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            foreach (array_keys(config('creatium.industry_pages')) as $slug) {
                $body = $this->get('/izrabotka-na-sait-za-' . $slug)->assertOk()->getContent();
                $this->assertDoesNotMatchRegularExpression(OnePriceModel2026::OLD_PATTERN, $body, "$design $slug");
            }
        }
        config(['creatium.design' => 'current']);
        $text = \Tests\Feature\PlainCopyTest::visibleText($this->get('/izrabotka-na-sait-za-kozmetichen-salon')->assertOk()->getContent());
        $this->assertMatchesRegularExpression('/от €500 \/ месец/u', $text);
    }

    // ---- the migration logic ------------------------------------------------------------------------------------------------------------

    public function test_every_replacement_ends_in_a_current_default_free_of_old_prices_dashes_and_colons(): void
    {
        $defaults = [];
        foreach (['creatium' => 'bg', 'creatium_en' => 'en'] as $file => $locale) {
            $config = (array) config($file);
            array_walk_recursive($config, function ($item) use (&$defaults, $locale) {
                if (is_string($item)) {
                    $defaults[$locale][$item] = true;
                }
            });
        }
        // Texts that live in the views (page titles and descriptions of /paketi and the home page, saved by the SEO form).
        $views = ['bg' => [], 'en' => []];
        foreach (['bg' => 'bg', 'en' => 'en'] as $locale => $_) {
            foreach (['resources/views/packages.blade.php', 'resources/views/home.blade.php'] as $view) {
                preg_match_all("/@section\\('(?:title|meta_description)', __\\('([^']+)'\\)\\)/u", file_get_contents(base_path($view)), $m);
                foreach ($m[1] as $bgText) {
                    $views[$locale][$locale === 'en' ? (json_decode(file_get_contents(lang_path('en.json')), true)[$bgText] ?? $bgText) : $bgText] = true;
                }
            }
        }

        foreach (OnePriceModel2026::REPLACEMENTS as $locale => $map) {
            foreach ($map as $old => $new) {
                $this->assertNotSame($old, $new);
                $this->assertSame([], PlainCopyTest::offences($new), $new);
                $this->assertSame([], OnePriceModel2026::leftovers([$new]), "New text still has an old price: $new");
                $this->assertTrue(isset($defaults[$locale][$new]) || isset($views[$locale][$new]), "Not a current default ($locale): $new");
                $this->assertFalse(isset($defaults[$locale][$old]), "Still a current default ($locale): $old");
            }
        }
        foreach (OnePriceModel2026::LISTS as $locale => $lists) {
            foreach ($lists as [$old, $new]) {
                $flat = json_encode($new, JSON_UNESCAPED_UNICODE);
                $this->assertSame([], OnePriceModel2026::leftovers($new));
                $this->assertNotSame($old, $new);
                $walked = $new;
                array_walk_recursive($walked, function ($item) use ($locale) {
                    $this->assertSame([], PlainCopyTest::offences($item), $item);
                });
                $this->assertNotSame('', $flat);
            }
        }
    }

    public function test_the_old_defaults_are_replaced_with_a_backup_and_edited_text_is_left_alone(): void
    {
        $oldQuestion = array_key_first(array_filter(OnePriceModel2026::REPLACEMENTS['bg'], fn ($new, $old) => str_starts_with($old, 'Цената е фиксирана'), ARRAY_FILTER_USE_BOTH));
        $oldPlans = [
            'intro' => 'Моят собствен увод',
            'items' => [
                ['name' => 'Core Изграждане', 'prices' => ['€400 еднократно за изграждането и първия месец', 'след това €200 / месец'], 'deposit' => '€100 при заявка за оферта и демо (не се връщат), останалите €300 при съгласие.', 'year_total' => '€2 600 за първата година (€400 + 11 × €200)', 'badge' => 'Хит'],
                ['name' => 'Pro Ускорение', 'prices' => ['Моя цена €800', 'след това €650 / месец'], 'deposit' => 'Моето капаро, ред на собствениците.', 'year_total' => ''],
                ['name' => 'Enterprise Партньорство', 'prices' => ['Индивидуална оферта', 'от €1 200 / месец'], 'deposit' => 'Капаро при заявка за оферта и демо (не се връща), сумата е в индивидуалната оферта; останалото при съгласие.'],
            ],
            'billing' => ['yearly_months' => 10, 'note' => 'Годишното плащане е за 12 месеца абонамент, платени като 10. Абонаментът започва от втория месец, а стартовата цена се плаща отделно. Enterprise Партньорство е по индивидуална оферта.'],
            'trust' => [['icon' => 'file-contract', 'text' => 'Плащане на две части. Депозит преди офертата и демото, остатъкът при съгласие'], ['icon' => 'lock', 'text' => 'Моят текст']],
            'terms' => OnePriceModel2026::LISTS['bg'][3][0],
        ];
        $this->assertStringStartsWith('Цената е фиксирана', $oldQuestion);
        ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => $oldPlans]);
        ContentBlock::create(['key' => 'questions', 'locale' => 'bg', 'value' => [
            ['q' => 'Как се определя цената на проекта?', 'a' => $oldQuestion, 'group' => 'Цени и плащане'],
            ['q' => 'Моят въпрос', 'a' => 'Моят отговор със стара цена €600.'],
        ]]);
        ContentBlock::create(['key' => 'seo:packages', 'locale' => 'bg', 'value' => ['title' => 'Цена за изработка на сайт с пакети от €400', 'description' => 'Мое описание']]);
        ContentBlock::create(['key' => 'home_hero', 'locale' => 'bg', 'value' => ['title' => 'Моето заглавие']]);
        // A saved backup of an earlier change is never touched.
        ContentBlock::create(['key' => 'plans:backup-2026-11-17b', 'locale' => 'bg', 'value' => ['items' => [['deposit' => '€100 стар архив']]]]);

        $result = OnePriceModel2026::apply();

        $this->assertEqualsCanonicalizing(['plans/bg', 'questions/bg', 'seo:packages/bg'], $result['changed']);
        // The plan of the owners with their own price and deposit is reported for review (it still holds old amounts), the other blocks are clean.
        $this->assertContains('plans/bg', $result['review']);
        $this->assertContains('questions/bg', $result['review']); // „Моят отговор със стара цена €600“

        $plans = ContentBlock::where(['key' => 'plans', 'locale' => 'bg'])->sole()->value;
        $this->assertSame('Моят собствен увод', $plans['intro']);
        $this->assertSame(['от €500 / месец'], $plans['items'][0]['prices']);
        $this->assertSame('', $plans['items'][0]['deposit']);
        $this->assertSame('', $plans['items'][0]['year_total']);
        $this->assertSame('Хит', $plans['items'][0]['badge']);
        $this->assertSame(['Моя цена €800', 'след това €650 / месец'], $plans['items'][1]['prices']); // edited: left as it is
        $this->assertSame('Моето капаро, ред на собствениците.', $plans['items'][1]['deposit']);
        $this->assertSame(['от €1 200 / месец'], $plans['items'][2]['prices']);
        $this->assertSame('', $plans['items'][2]['deposit']);
        $this->assertSame('Годишното плащане е за 12 месеца абонамент, платени като 10. Enterprise Партньорство е по индивидуална оферта.', $plans['billing']['note']);
        $this->assertSame('Месечен абонамент. Плащате само месечната такса на избрания пакет', $plans['trust'][0]['text']);
        $this->assertSame('Моят текст', $plans['trust'][1]['text']);
        $this->assertSame(config('creatium.plans.terms'), $plans['terms']);

        $questions = ContentBlock::where(['key' => 'questions', 'locale' => 'bg'])->sole()->value;
        $this->assertSame(config('creatium.questions.0.a'), $questions[0]['a']);
        $this->assertSame('Моят отговор със стара цена €600.', $questions[1]['a']);

        $seo = ContentBlock::where(['key' => 'seo:packages', 'locale' => 'bg'])->sole()->value;
        $this->assertSame('Цена за изработка на сайт с пакети от €500', $seo['title']);
        $this->assertSame('Мое описание', $seo['description']);

        // Backups: the first state of every changed block, an untouched block gets none, an older backup is not overwritten.
        $backup = ContentBlock::where(['key' => 'plans:' . OnePriceModel2026::BACKUP, 'locale' => 'bg'])->sole()->value;
        $this->assertSame($oldPlans, $backup);
        $this->assertTrue(ContentBlock::where(['key' => 'questions:' . OnePriceModel2026::BACKUP, 'locale' => 'bg'])->exists());
        $this->assertTrue(ContentBlock::where(['key' => 'seo:packages:' . OnePriceModel2026::BACKUP, 'locale' => 'bg'])->exists());
        $this->assertFalse(ContentBlock::where('key', 'home_hero:' . OnePriceModel2026::BACKUP)->exists());
        $this->assertSame('€100 стар архив', ContentBlock::where(['key' => 'plans:backup-2026-11-17b', 'locale' => 'bg'])->sole()->value['items'][0]['deposit']);

        // A second run changes nothing and the backup stays the first one.
        $count = ContentBlock::count();
        $this->assertSame([], OnePriceModel2026::apply()['changed']);
        $this->assertSame($count, ContentBlock::count());
        $this->assertSame($oldPlans, ContentBlock::where(['key' => 'plans:' . OnePriceModel2026::BACKUP, 'locale' => 'bg'])->sole()->value);

        // The public page shows the new price from the saved (now migrated) copy, and the content cache was flushed.
        $this->assertSame(['от €500 / месец'], app(\App\Support\Content::class)->get('plans.items.0.prices'));
    }

    public function test_the_english_copy_and_a_list_that_was_edited_are_handled_too(): void
    {
        $oldEn = OnePriceModel2026::LISTS['en'][0][0];
        $edited = OnePriceModel2026::LISTS['en'][1][0];
        $edited[1] = 'then €250 / month (my own)';
        ContentBlock::create(['key' => 'plans', 'locale' => 'en', 'value' => ['items' => [['prices' => $oldEn], ['prices' => $edited]]]]);

        $result = OnePriceModel2026::apply();
        $this->assertSame(['plans/en'], $result['changed']);

        $items = ContentBlock::where(['key' => 'plans', 'locale' => 'en'])->sole()->value['items'];
        $this->assertSame(['from €500 / month'], $items[0]['prices']);
        $this->assertSame($edited, $items[1]['prices']); // an edited list is never replaced
        $this->assertSame(['plans/en'], $result['review']);
    }

    public function test_transform_replaces_whole_strings_only(): void
    {
        $old = array_key_first(array_filter(OnePriceModel2026::REPLACEMENTS['en'], fn ($new, $old) => str_starts_with($old, 'The price is fixed'), ARRAY_FILTER_USE_BOTH));
        $value = ['a' => $old, 'b' => $old . ' And my own sentence.', 'c' => ['nested' => $old]];
        $out = OnePriceModel2026::transform($value, 'en');

        $this->assertSame(OnePriceModel2026::REPLACEMENTS['en'][$old], $out['a']);
        $this->assertSame($old . ' And my own sentence.', $out['b']);
        $this->assertSame(OnePriceModel2026::REPLACEMENTS['en'][$old], $out['c']['nested']);
        $this->assertSame(['x' => 1, 'y' => null, 'z' => true], OnePriceModel2026::transform(['x' => 1, 'y' => null, 'z' => true]));
    }

    public function test_the_migration_is_skipped_in_tests_and_leaves_the_data_alone(): void
    {
        ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => ['items' => [['prices' => OnePriceModel2026::LISTS['bg'][0][0]]]]]);
        $migration = require base_path('database/migrations/2026_11_17_000004_one_price_model.php');
        $migration->up();

        $this->assertSame(OnePriceModel2026::LISTS['bg'][0][0], ContentBlock::where('key', 'plans')->sole()->value['items'][0]['prices']);
        $this->assertSame(0, ContentBlock::where('key', 'like', '%:backup-%')->count());
    }
}
