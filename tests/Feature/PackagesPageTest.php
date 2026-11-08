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
        $this->assertSame(['€400 старт: изграждаме всичко и първия месец', 'след това €200 / месец'], $plans['items'][0]['prices']);
        $this->assertSame(['€800 старт: изграждаме или поправяме сегашния ви сайт и първия месец', 'след това €650 / месец'], $plans['items'][1]['prices']);
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
            ['Брандиране', 'Уебсайт', 'SEO', 'Google профил', 'Съдържание месечно', 'Рекламни кампании', 'Имейл маркетинг', 'Автоматизации', 'Отчет', 'Работен капацитет'],
            array_column($plans['compare'], 'label')
        );
        foreach ($plans['compare'] as $row) {
            $this->assertCount(3, $row['values'], $row['label']);
        }

        $page = $this->get('/paketi')->assertOk();
        $page->assertSee('€400')->assertSee('€800')->assertSee('€1 200')
            ->assertSee('Работен капацитет')->assertSee('До 2 ч.')->assertSee('Няколко канала');
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
            ->assertSee('€100 при заявка за оферта и демо, останалите €300 при съгласие.')
            ->assertSee('€200 при заявка за оферта и демо, останалите €600 при съгласие.')
            ->assertSee('Първата сума се плаща преди да подготвим офертата и демото.')
            ->assertSee('Фиксирана цена в офертата')
            ->assertSee('€2 600 за първата година (€400 + 11 × €200)')
            ->assertSee('€7 950 за първата година (€800 + 11 × €650)')
            ->assertDontSee('50% при договор')
            ->assertDontSee('безплатно демо')
            ->assertSee('Какво включва всеки пакет')
            ->assertSee('Как започваме')
            ->assertSee('Заявете разговор')
            ->assertSee('Предпочитате разговор?')
            ->assertSee('/kontakti#konsultatsiya', false);
        $this->get('/en/packages')->assertOk()
            ->assertSee('Active Marketing and Growth')->assertSee('Custom Marketing')->assertDontSee('Scaling')
            ->assertSee('€2,600 for the first year')->assertSee('€7,950 for the first year')
            ->assertSee('€100 when you request a quote and demo, the remaining €300 when you agree.');
    }

    public function test_the_deposit_wording_does_not_promise_a_refund_and_the_demo_is_not_called_free(): void
    {
        $plans = json_encode([config('creatium.plans'), config('creatium_en.plans'), config('creatium.questions'), config('creatium_en.questions')], JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsStringIgnoringCase('безплатно демо', $plans);
        $this->assertStringNotContainsStringIgnoringCase('free demo', $plans);
        $this->assertStringNotContainsString('връща се', $plans);
        $this->assertStringNotContainsString('невъзвръщаем', $plans);
        $this->assertSame('Фиксирана цена, записана в офертата', config('creatium.home_problem.system_trust')[1]['text']);

        $this->get('/usloviya')->assertOk()
            ->assertSee('Първата част се плаща преди да подготвим офертата и демото.')
            ->assertSee('Последна промяна: 08.11.2026');
        $this->get('/en/terms')->assertOk()
            ->assertSee('The first part is paid before we prepare the offer and the demo.')
            ->assertSee('Last updated: 8 November 2026');
    }

    public function test_yearly_billing_gives_two_months_free_and_the_toggle_has_no_slider(): void
    {
        $html = $this->get('/paketi')->assertOk()
            ->assertSee('Годишно: 2 месеца безплатно')
            ->assertSee('&minus;17%', false)
            ->assertDontSee('type="range"', false)
            ->assertDontSee('Изберете колко съдържание')
            ->getContent();
        // И двата варианта са в страницата; без избор месечният се вижда, годишният е скрит.
        $this->assertMatchesRegularExpression('/data-billing-view="yearly"\s+hidden/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-billing-view="monthly"\s+hidden/', $html);
        $this->assertStringContainsString("€2\u{202F}000", $html);
        $this->assertStringContainsString("€6\u{202F}500", $html);

        $yearly = $this->get('/paketi?plashtane=godishno')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/data-billing-view="monthly"\s+hidden/', $yearly);
        $this->assertDoesNotMatchRegularExpression('/data-billing-view="yearly"\s+hidden/', $yearly);
        $this->assertStringContainsString("вместо €2\u{202F}400", $yearly);
        $this->assertStringContainsString("вместо €7\u{202F}800", $yearly);
        $this->assertStringContainsString('?paket=0&amp;plashtane=godishno#oferta', $yearly);
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
            ->assertSee('Гаранция: връщаме парите')
            ->assertSee('Ако 60 дни след пускането на сайта нямате трафик, връщаме парите, които сте платили.')
            ->assertSee('Абонаментната цена е фиксирана за първите 6 месеца.')
            ->assertSee('Бюджетът за реклама във Facebook и Instagram се плаща от вас директно на Meta.')
            ->assertSee('Рекламният бюджет включен ли е в цената?');
        $this->get('/en/packages')->assertOk()
            ->assertSee('Guarantee: we refund you')
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
}
