<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Support\PlanPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Плъзгачът „колко съдържание ви трябва“ на /paketi (plans.pricing). */
class PlanPricingTest extends TestCase
{
    use RefreshDatabase;

    private const TS = "\u{202F}";

    public function test_the_default_step_is_rendered_without_javascript(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertSee('Изберете колко съдържание ви трябва всеки месец')
            ->assertSee('type="range"', false)
            ->assertSee('aria-valuetext="4 публикации и статии на месец"', false)
            ->assertSee('Включени: 4 публикации и статии на месец')
            ->assertSee('€600')->assertSee('€900')
            ->assertSee('data-p-price>€200<', false)
            ->assertSee('data-p-price>€450<', false)
            ->assertSee('€2' . self::TS . '800 за първата година', false)
            ->assertSee('€5' . self::TS . '850 за първата година', false)
            ->assertSee('Включени: по мярка')
            ->assertSee('data-plan-card="1" data-rec="true"', false)
            ->assertSee('data-plan-card="0" data-rec="false"', false)
            ->assertSee('Годишно: 2 месеца безплатно')
            ->assertSee('&minus;17%', false);
    }

    public function test_the_first_step_shows_the_lowest_prices(): void
    {
        $this->get('/paketi?obem=0')->assertOk()
            ->assertSee('aria-valuetext="2 публикации и статии на месец"', false)
            ->assertSee('data-p-price>€150<', false)
            ->assertSee('data-p-price>€400<', false)
            ->assertSee('€2' . self::TS . '250 за първата година', false)
            ->assertSee('€5' . self::TS . '300 за първата година', false)
            ->assertSee('data-plan-card="0" data-rec="true"', false)
            ->assertSee('data-plan-card="1" data-rec="false"', false);
    }

    public function test_the_last_step_is_a_custom_offer_for_the_first_two_plans(): void
    {
        $html = $this->get('/paketi?obem=5')->assertOk()
            ->assertSee('aria-valuetext="20+ публикации и статии на месец"', false)
            ->assertSee('data-plan-card="2" data-rec="true"', false)
            ->assertSee('data-plan-card="1" data-rec="false"', false)
            ->getContent();

        // Plans 1 and 2: no numbers, „Индивидуална оферта“ shown and the call-to-action button is „Заявете разговор“.
        $this->assertSame(2, preg_match_all('/<div data-p-numbers\s+hidden\s*>/', $html));
        $this->assertSame(2, preg_match_all('/data-p-custom\s*>Индивидуална оферта/u', $html));
        $this->assertSame(3, preg_match_all('/<span data-p-btn-custom\s*>Заявете разговор/u', $html));
        $this->assertStringContainsString('Включени: 20+ публикации и статии на месец', $html);
        $this->assertStringContainsString('data-compare-volume="0">20+ (по оферта)<', $html);
    }

    public function test_yearly_billing_shows_the_per_month_equivalent_and_the_year_totals(): void
    {
        // plan 1 at 4 posts: X = 200: 200 × 10 / 12 = 167 a month, €2 000 once instead of €2 400, 600 + round(11 × 200 × 10 / 12) = 2 433.
        // plan 2 at 4 posts: X = 450: 375 a month, €4 500 instead of €5 400, 900 + round(11 × 450 × 10 / 12) = 5 025.
        $this->get('/paketi?plashtane=godishno')->assertOk()
            ->assertSee('data-billing="yearly"', false)
            ->assertSee('data-p-price>€167<', false)
            ->assertSee('data-p-price>€375<', false)
            ->assertSee('/ месец при годишно плащане')
            ->assertSee('€2' . self::TS . '000 веднъж годишно вместо €2' . self::TS . '400', false)
            ->assertSee('€4' . self::TS . '500 веднъж годишно вместо €5' . self::TS . '400', false)
            ->assertSee('€2' . self::TS . '433 за първата година', false)
            ->assertSee('€5' . self::TS . '025 за първата година', false);

        $this->get('/paketi?obem=0&plashtane=godishno')->assertOk()
            ->assertSee('data-p-price>€125<', false)   // 150 × 10 / 12
            ->assertSee('data-p-price>€333<', false)   // 400 × 10 / 12 = 333.3
            ->assertSee('€1' . self::TS . '975 за първата година', false)  // 600 + round(1 375)
            ->assertSee('€4' . self::TS . '567 за първата година', false); // 900 + round(3 666.7)
    }

    public function test_the_numbers_are_the_documented_formulas_for_every_step(): void
    {
        $plans = config('creatium.plans');
        $pricing = PlanPricing::config($plans);
        $this->assertSame(10, $pricing['yearly_months']);
        foreach ([[0, 600], [1, 900]] as [$i, $start]) {
            foreach ($pricing['steps'] as $s => $step) {
                $x = $step['monthly'][$i];
                $month = PlanPricing::figures($pricing, $plans['items'][$i], $i, $s, 'monthly');
                $year = PlanPricing::figures($pricing, $plans['items'][$i], $i, $s, 'yearly');
                if ($x === null) {
                    $this->assertNull($month);
                    $this->assertNull($year);

                    continue;
                }
                $this->assertSame($x, $month['monthly']);
                $this->assertSame($start + 11 * $x, $month['year']);
                $this->assertSame((int) round($x * 10 / 12), $year['monthly']);
                $this->assertSame($start + (int) round(11 * $x * 10 / 12), $year['year']);
                $this->assertSame($x * 10, $year['once']);
                $this->assertSame($x * 12, $year['instead']);
            }
        }
    }

    public function test_invalid_query_values_fall_back_to_the_default(): void
    {
        foreach (['?obem=99', '?obem=-1', '?obem=abc', '?obem[]=1', '?obem=1.5', '?obem=', '?obem=1;drop'] as $query) {
            $this->get('/paketi' . $query . '&plashtane=nope')->assertOk()
                ->assertSee('aria-valuetext="4 публикации и статии на месец"', false)
                ->assertSee('data-billing="monthly"', false)
                ->assertSee('data-p-price>€200<', false);
        }
    }

    public function test_english_page_uses_english_texts_and_comma_thousands(): void
    {
        $this->get('/en/packages?obem=3&plashtane=godishno')->assertOk()
            ->assertSee('Choose how much content you need every month')
            ->assertSee('Included: 12 posts and articles a month')
            ->assertSee('/ month with yearly billing')
            ->assertSee('Yearly: 2 months free')
            ->assertSee('A custom quote')
            // plan 1 at 12 posts: X = 400 → 333 a month, 600 + round(11 × 400 × 10 / 12) = 4 267; plan 2: 650 → 542, 900 + 5 958 = 6 858.
            ->assertSee('data-p-price>€333<', false)
            ->assertSee('€4,267 for the first year')
            ->assertSee('€6,858 for the first year');
    }

    public function test_quote_buttons_and_form_carry_the_chosen_volume_and_billing(): void
    {
        $this->get('/paketi?obem=2&plashtane=godishno')->assertOk()
            ->assertSee('/paketi?paket=0&amp;obem=2&amp;plashtane=godishno#oferta', false)
            ->assertSee('/paketi?paket=2&amp;obem=2&amp;plashtane=godishno#oferta', false)
            ->assertSee('name="volume" value="8"', false)
            ->assertSee('name="billing" value="yearly"', false);
    }

    public function test_the_compare_table_has_a_volume_row_that_follows_the_step(): void
    {
        $this->get('/paketi?obem=3')->assertOk()
            ->assertSee('Публикации и статии на месец')
            ->assertSee('data-compare-volume="0">12<', false)
            ->assertSee('data-compare-volume="2">по мярка<', false);
    }

    public function test_the_quote_stores_volume_and_billing_in_bulgarian(): void
    {
        Mail::fake();
        $this->post('/en/packages/quote', [
            'plan' => 1, 'name' => 'Ann', 'business' => 'Shop', 'email' => 'ann@example.com', 'consent' => '1',
            'volume' => '8', 'billing' => 'yearly',
        ])->assertRedirect();

        $lead = Project::sole();
        $this->assertStringContainsString("Обем: 8 публикации и статии на месец", $lead->description);
        $this->assertStringContainsString('Плащане: годишно', $lead->description);
    }

    public function test_an_unknown_volume_or_billing_is_ignored(): void
    {
        Mail::fake();
        $this->post('/paketi/oferta', [
            'plan' => 0, 'name' => 'Мария', 'business' => 'Лале', 'phone' => '0888 123 456', 'consent' => '1',
            'volume' => '999', 'billing' => 'weekly',
        ])->assertSessionHasNoErrors();

        $lead = Project::sole();
        $this->assertStringNotContainsString('Обем:', $lead->description);
        $this->assertStringNotContainsString('Плащане:', $lead->description);
    }

    public function test_without_the_pricing_block_the_page_is_the_old_one(): void
    {
        $plans = config('creatium.plans');
        unset($plans['pricing']);
        config(['creatium.plans' => $plans]);

        $this->get('/paketi')->assertOk()
            ->assertDontSee('type="range"', false)
            ->assertDontSee('name="volume"', false)
            ->assertSee('след това')->assertSee('€150')->assertSee('data-plan-card="1" data-rec="true"', false);
    }

    public function test_kreo_knows_the_steps_and_the_text_prices_say_from(): void
    {
        $knowledge = \App\Support\Chat\SiteKnowledge::text();
        $this->assertStringContainsString('8 публикации и статии на месец', $knowledge);
        $this->assertStringContainsString('€300 / месец', $knowledge);
        $this->assertStringContainsString('след това от €150 / месец', $knowledge);

        $this->get('/llms.txt')->assertOk()->assertSee('4 публикации и статии на месец', false);
    }
}
