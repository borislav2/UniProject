<?php

namespace Tests\Feature;

use App\Support\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_plan_has_the_same_six_parts_and_the_compare_table_lists_them(): void
    {
        $parts = ['Брандиране', 'Уеб изработка', 'Видимост', 'Маркетинг', 'Клиенти, които се връщат', 'Грижа всеки месец'];
        $plans = config('creatium.plans');
        $this->assertCount(3, $plans['items']);
        foreach ($plans['items'] as $plan) {
            $heads = array_map(fn ($line) => explode(': ', $line, 2)[0], $plan['build_items']);
            $this->assertSame($parts, $heads, $plan['name']);
            $this->assertSame([], $plan['monthly_items']);
        }
        $this->assertSame(['€600 старт: изграждаме всичко и първия месец', 'след това от €150 / месец'], $plans['items'][0]['prices']);
        $this->assertSame(['€900 старт: изграждаме всичко и първия месец', 'след това от €400 / месец'], $plans['items'][1]['prices']);

        $labels = array_column($plans['compare'], 'label');
        foreach ($parts as $part) {
            $this->assertContains($part, $labels);
        }
        foreach ($plans['compare'] as $row) {
            $this->assertCount(3, $row['values']);
            $this->assertNotContains('no', $row['values'], $row['label']);
        }

        $page = $this->get('/paketi')->assertOk();
        foreach ($parts as $part) {
            $page->assertSee($part);
        }
        $page->assertSee('+ Google Maps, отзиви, съдържание и статии')->assertSee('Google профил и основа за търсачки и AI')
            ->assertSee('от €600 старт');

        $this->get('/en/packages')->assertOk()->assertSee('€600')->assertSee('then')->assertSee('€150')
            ->assertSee('€5,850 for the first year')->assertSee('Returning customers');
        $this->get('/')->assertSee('€600 – €900+', false);
    }

    public function test_plan_names_trust_strip_year_totals_and_compare_table(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertSee('Активен маркетинг и растеж')
            ->assertSee('Индивидуален маркетинг и мащабиране')
            ->assertSee('Препоръчваме')
            ->assertDontSee('Най-предпочитан')
            ->assertSee('Плащате на две части')->assertSee('50% при договор, 50% след одобрено демо')
            ->assertSee('Фиксирана цена в офертата')
            ->assertSee("€2\u{202F}800 за първата година", false)
            ->assertSee("€5\u{202F}850 за първата година", false)
            ->assertSee('Всеки пакет включва цялата система')
            ->assertDontSee('изработка на сайта')
            ->assertSee('Какво включва всеки пакет')
            ->assertSee('онлайн плащания и резервации')
            ->assertSee('Как започваме')
            ->assertSee('Заявете разговор')
            ->assertSee('Предпочитате разговор?')
            ->assertSee('/kontakti#konsultatsiya', false);
        $this->get('/en/packages')->assertOk()
            ->assertSee('Active Marketing and Growth')->assertSee('€5,850 for the first year');
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
        unset($plans['pricing']);
        config(['creatium.plans' => $plans]);

        $this->get('/paketi')->assertOk()
            ->assertDontSee('Какво включва всеки пакет')
            ->assertDontSee('Как започваме')
            ->assertDontSee('Общо за първата година: €2 250')
            ->assertDontSee('id="izbor"', false);
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
    }
}
