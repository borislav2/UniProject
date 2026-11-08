<?php

namespace Tests\Feature;

use App\Support\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_prices_and_the_owners_lists(): void
    {
        $plans = config('creatium.plans');
        $this->assertCount(3, $plans['items']);
        $this->assertSame(['€400 еднократно за изграждането и първия месец', 'след това €200 / месец'], $plans['items'][0]['prices']);
        $this->assertSame(['€800 еднократно за изграждането или поправката на сегашния ви сайт и първия месец', 'след това €650 / месец'], $plans['items'][1]['prices']);
        $this->assertSame(['Индивидуална оферта', 'от €1 200 / месец'], $plans['items'][2]['prices']);
        $this->assertSame('Индивидуален маркетинг', $plans['items'][2]['name']);
        $this->assertArrayNotHasKey('pricing', $plans);
        $this->assertSame(10, $plans['billing']['yearly_months']);

        // Списъците на собствениците: 8 точки в Стартов двигател, 9 в Активен, 10 в Индивидуален.
        $this->assertCount(8, $plans['items'][0]['build_items']);
        $this->assertContains('Настройка на Google Analytics и Meta Pixel', $plans['items'][0]['build_items']);
        $this->assertContains('До 8 съдържателни единици месечно', $plans['items'][1]['build_items']);
        $this->assertContains('Приоритетна комуникация', $plans['items'][2]['build_items']);

        // Сравнителната таблица на собствениците: 10 реда по 3 стойности.
        $this->assertSame(
            ['Брандиране', 'Уебсайт', 'SEO', 'Google профил', 'Съдържание месечно', 'Рекламни кампании', 'Имейл маркетинг', 'Автоматизации', 'Отчет', 'Работен капацитет на месец'],
            array_column($plans['compare'], 'label')
        );
        foreach ($plans['compare'] as $row) {
            $this->assertCount(3, $row['values'], $row['label']);
        }

        $page = $this->get('/paketi')->assertOk();
        $page->assertSee('€400')->assertSee('€800')->assertSee('€1 200')
            ->assertSee('Работен капацитет на месец')->assertSee('До 2 ч.')->assertSee('Няколко канала');
    }

    public function test_plan_names_trust_strip_year_totals_deposit_and_compare_table(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertSee('Активен маркетинг и растеж')
            ->assertSee('Индивидуален маркетинг')
            ->assertDontSee('мащабиране')
            ->assertSee('Препоръчваме')
            ->assertDontSee('Най-предпочитан')
            ->assertSee('Всичко необходимо, за да присъства бизнесът ви професионално онлайн.')
            ->assertSee('Получавате маркетингов партньор, който изгражда и управлява стратегията за растеж на бизнеса ви.')
            ->assertSee('Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите.')
            ->assertSee('Фиксирана цена в офертата')
            ->assertDontSee('50% при договор')
            ->assertDontSee('безплатно демо')
            ->assertSee('Какво включва всеки пакет')
            ->assertSee('Как започваме')
            ->assertSee('Заявете разговор')
            ->assertSee('Предпочитате разговор?')
            ->assertSee('/kontakti#konsultatsiya', false);
        $this->get('/en/packages')->assertOk()
            ->assertSee('Active Marketing and Growth')->assertSee('Custom Marketing')->assertDontSee('Scaling')
            ->assertSee('from €400')->assertSee('€2,000 a year')->assertSee('€6,500 a year')->assertSee('from €1,200');
    }

    public function test_the_deposit_is_not_refundable_and_the_demo_is_not_called_free(): void
    {
        $plans = json_encode([config('creatium.plans'), config('creatium_en.plans'), config('creatium.questions'), config('creatium_en.questions')], JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsStringIgnoringCase('безплатно демо', $plans);
        $this->assertStringNotContainsStringIgnoringCase('free demo', $plans);
        $this->assertStringContainsString('не се връща, ако решите да не продължите', $plans);
        $this->assertSame('Фиксирана цена, записана в офертата', config('creatium.home_problem.system_trust')[1]['text']);

        $this->get('/usloviya')->assertOk()
            ->assertSee('Първата част се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите.')
            ->assertSee('Последна промяна 08.11.2026');
        $this->get('/en/terms')->assertOk()
            ->assertSee('The first part is paid before we prepare the offer and the demo and is not refunded if you decide not to continue.')
            ->assertSee('Last updated 8 November 2026');
    }

    /** The packages section of the hero (everything before the first section after it). */
    private function hero(string $html): string
    {
        $this->assertSame(1, preg_match('#<section data-packages-hero.*?</section>#s', $html, $m));

        return $m[0];
    }

    public function test_cards_show_from_prices_and_only_the_yearly_subscription(): void
    {
        $html = $this->get('/paketi')->assertOk()->getContent();
        $text = \Tests\Feature\PlainCopyTest::visibleText($this->hero($html));
        $ts = ' '; // visibleText() collapses the narrow no-break space of the prices

        foreach (['от €400', 'от €800', "от €1{$ts}200", "€2{$ts}000 на година", "€6{$ts}500 на година", 'Индивидуална оферта'] as $expected) {
            $this->assertStringContainsString($expected, $text);
        }
        $this->assertSame(2, substr_count($text, 'абонамент, 2 месеца безплатно'));
        // Без месечна цена, без „след това“ и без плащане на старта на картите.
        foreach (['след това', '/ месец', "€200{$ts}/", 'Плащане на старта', '€100 при заявка', '€600 при съгласие'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $text, $forbidden);
        }
    }

    public function test_there_is_no_billing_toggle_and_the_old_query_is_ignored(): void
    {
        foreach (['/paketi', '/paketi?plashtane=godishno', '/paketi?plashtane=mesechno'] as $url) {
            $html = $this->get($url)->assertOk()
                ->assertDontSee('data-plan-billing', false)
                ->assertDontSee('data-billing-view', false)
                ->assertDontSee('name="plashtane"', false)
                ->assertDontSee('name="billing"', false)
                ->assertDontSee('Годишно, 2 месеца безплатно')
                ->assertDontSee('type="range"', false)
                ->getContent();
            $this->assertStringNotContainsString('plashtane=', preg_replace('#<(script|style).*?</\1>#s', '', $html));
        }
    }

    public function test_the_cards_sit_in_the_hero_under_the_heading_with_the_trust_strip_below(): void
    {
        $hero = $this->hero($this->get('/paketi')->assertOk()->getContent());
        $this->assertSame(3, substr_count($hero, 'data-plan-card='));
        $this->assertSame(3, substr_count($hero, ' data-plan '));
        $positions = array_map(fn ($needle) => strpos($hero, $needle), ['<h1', 'data-plan-card="0"', 'data-plan-card="2"', 'Фиксирана цена в офертата']);
        $this->assertNotContains(false, $positions);
        $this->assertSame($positions, collect($positions)->sort()->values()->all());
        // Картите са с равна височина: общата мрежа дава редовете на подмрежите.
        $this->assertStringContainsString('lg:grid-rows-subgrid', $hero);
        // Всяка карта е кратка: най-много пет точки и връзка към пълния списък.
        $this->assertSame(3, substr_count($hero, 'Вижте всичко включено'));
        $this->get('/paketi')->assertSee('id="vsichko-0"', false)->assertSee('Лого, цветове и базова визуална идентичност.');
    }

    public function test_the_recommended_card_is_first_on_phones_and_keeps_its_highlight(): void
    {
        $hero = $this->hero($this->get('/paketi')->assertOk()->getContent());
        $this->assertMatchesRegularExpression('/id="paket-1"[^>]*order-first lg:order-none/', $hero);
        $this->assertStringContainsString('data-rec="true"', $hero);
        $this->assertSame(1, substr_count($hero, 'data-rec="true"'));
    }

    public function test_the_card_price_helper_follows_the_prices_text(): void
    {
        $plans = config('creatium.plans');
        $billing = \App\Support\PlanBilling::config($plans);
        $card = fn ($i) => \App\Support\PlanBilling::cardPrice($billing, $plans['items'][$i], $i);
        $this->assertSame('от €400', $card(0)['from']);
        $this->assertSame('еднократно за изграждането и първия месец', $card(0)['caption']);
        $this->assertSame("€2\u{202F}000 на година", $card(0)['second']);
        $this->assertSame("от €1\u{202F}200", $card(2)['from']);
        $this->assertSame('Индивидуална оферта', $card(2)['second']);
        // Без отстъпка за годишно плащане годишният ред изчезва.
        $billing['enabled'] = false;
        $this->assertSame('', \App\Support\PlanBilling::cardPrice($billing, $plans['items'][0], 0)['second']);
        // Старо запазено в админа без card_items: първите пет заглавия.
        unset($plans['items'][0]['card_items']);
        $this->assertSame(['Брандиране', 'Изработка на уебсайт', 'Google Business Profile', 'Базова SEO оптимизация', 'Настройка на Google Analytics и Meta Pixel'], \App\Support\PlanBilling::cardItems($plans['items'][0]));
    }

    public function test_faq_json_ld_is_valid(): void
    {
        $html = $this->get('/paketi')->assertOk()->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $faq = collect($m[1])->map(fn ($j) => json_decode($j, true))->firstWhere('@type', 'FAQPage');
        $this->assertNotNull($faq);
        $this->assertSame('https://schema.org', $faq['@context']);
        $this->assertNotEmpty($faq['mainEntity']);
        $this->assertStringContainsString('Цената е фиксирана', json_encode($faq, JSON_UNESCAPED_UNICODE));
    }

    public function test_demo_package_lookup_accepts_old_and_new_names(): void
    {
        $items = config('creatium.plans.items');
        foreach (['Активен маркетинг и растеж', 'Активен Маркетинг & Растеж', ' активен  маркетинг и растеж '] as $name) {
            $this->assertSame($items[1]['name'], Plans::find($items, $name)['name'], $name);
        }
        // Старото име на третия пакет още се разпознава (стари записи в админа и демота).
        $this->assertSame('Индивидуален маркетинг', Plans::find($items, 'Индивидуален маркетинг и мащабиране')['name']);
        $this->assertNull(Plans::find($items, null));
        $this->assertNull(Plans::find($items, 'Няма такъв'));
    }

    public function test_new_sections_hide_when_their_keys_are_empty(): void
    {
        $plans = config('creatium.plans');
        foreach (['trust', 'compare', 'steps', 'terms', 'faq_groups', 'faq_questions'] as $key) {
            $plans[$key] = [];
        }
        $plans['items'][0]['year_total'] = '';
        $plans['billing']['yearly_months'] = 12; // без отстъпка за годишно плащане превключвателят се скрива
        config(['creatium.plans' => $plans]);

        $this->get('/paketi')->assertOk()
            ->assertDontSee('Какво включва всеки пакет')
            ->assertDontSee('Как започваме')
            ->assertDontSee('data-plan-billing', false)
            ->assertDontSee('€2 600 за първата година');
    }

    public function test_guarantee_six_month_price_and_ad_budget_terms_are_shown(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertSee('Гаранция за връщане на парите')
            ->assertSee('Ако 60 дни след пускането на сайта нямате трафик, връщаме парите, които сте платили.')
            ->assertSee('Абонаментната цена е фиксирана за първите 6 месеца.')
            ->assertSee('Бюджетът за реклама във Facebook и Instagram се плаща от вас директно на Meta.')
            ->assertSee('Рекламният бюджет включен ли е в цената?');
        $this->get('/en/packages')->assertOk()
            ->assertSee('Money-back guarantee')
            ->assertSee('The subscription price is fixed for the first 6 months.');
        $this->get('/usloviya')->assertOk()
            ->assertSee('3. Цена, плащане, абонамент, гаранция и реклама')
            ->assertSee('е фиксирана за първите 6 месеца', false)
            ->assertSee('Плащате го вие директно на Meta.');
        $this->get('/en/terms')->assertOk()->assertSee('3. Price, payment, subscription, guarantee and ads');

        $fields = \App\Support\ClientEmails::find('offer-active')['fields'];
        $this->assertStringContainsString('60 дни', $fields['guarantee']['default']);
        $this->assertStringContainsString('6 месеца', $fields['subscription_terms']['default']);
        $this->assertStringContainsString('Meta', $fields['ad_budget']['default']);
    }

    public function test_the_cards_migration_only_adds_card_items_to_default_packages_and_keeps_a_backup(): void
    {
        $default = config('creatium.plans.items');
        $saved = ['intro' => 'Моето въведение', 'items' => [
            ['name' => $default[0]['name'], 'prices' => ['€500 моя цена', 'след това €250 / месец'], 'build_items' => ['Моя точка']],
            ['name' => 'Моят пакет', 'prices' => ['€1']],
            ['name' => $default[2]['name'], 'card_items' => ['Моят кратък списък']],
        ]];
        \App\Models\ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => $saved]);

        $this->assertSame(['plans/bg'], \App\Support\PackagesCards2026::apply());

        $value = \App\Models\ContentBlock::where('key', 'plans')->where('locale', 'bg')->first()->value;
        $this->assertSame($default[0]['card_items'], $value['items'][0]['card_items']);
        $this->assertSame(['€500 моя цена', 'след това €250 / месец'], $value['items'][0]['prices']); // цените на собствениците остават
        $this->assertArrayNotHasKey('card_items', $value['items'][1]);
        $this->assertSame(['Моят кратък списък'], $value['items'][2]['card_items']);
        $this->assertSame('Моето въведение', $value['intro']);
        $this->assertSame($saved, \App\Models\ContentBlock::where('key', 'plans:backup-2026-11-08-h')->where('locale', 'bg')->first()->value);

        // Втори път не променя нищо и не презаписва копието.
        $this->assertSame([], \App\Support\PackagesCards2026::apply());
    }
}
