<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Support\Chat\FaqResponder;
use App\Support\Chat\SiteKnowledge;
use App\Support\PlanBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Окончателните цени на собствениците (08.11.2026): фиксирани цени, депозит и превключвател Месечно / Годишно (plans.billing). */
class PlanBillingTest extends TestCase
{
    use RefreshDatabase;

    private const TS = "\u{202F}";

    private function plans(): array
    {
        return config('creatium.plans');
    }

    public function test_prices_are_read_from_the_plan_text(): void
    {
        $items = $this->plans()['items'];
        $this->assertSame(400, PlanBilling::startPrice($items[0]));
        $this->assertSame(200, PlanBilling::monthlyPrice($items[0]));
        $this->assertSame(800, PlanBilling::startPrice($items[1]));
        $this->assertSame(650, PlanBilling::monthlyPrice($items[1]));
        $this->assertNull(PlanBilling::startPrice($items[2])); // „Индивидуална оферта“
        $this->assertSame(1200, PlanBilling::monthlyPrice($items[2])); // „от €1 200 / месец“
        $this->assertSame(2000, PlanBilling::amount('€2 000 / година'));
        $this->assertNull(PlanBilling::amount('без цена'));
    }

    public function test_figures_for_monthly_and_yearly_billing(): void
    {
        $billing = PlanBilling::config($this->plans());
        $items = $this->plans()['items'];

        // Месечно: 400 + 11 × 200 и 800 + 11 × 650 (първият месец е в стартовата цена).
        $this->assertSame(['monthly' => 200, 'once' => null, 'instead' => null, 'year' => 2600], PlanBilling::figures($billing, $items[0], 0, 'monthly'));
        $this->assertSame(7950, PlanBilling::figures($billing, $items[1], 1, 'monthly')['year']);

        // Годишно: 2 месеца безплатно, €2 000 вместо €2 400 и €6 500 вместо €7 800.
        $first = PlanBilling::figures($billing, $items[0], 0, 'yearly');
        $this->assertSame(2000, $first['once']);
        $this->assertSame(2400, $first['instead']);
        $this->assertSame(2400, $first['year']); // €400 старт + €2 000 абонамент
        $second = PlanBilling::figures($billing, $items[1], 1, 'yearly');
        $this->assertSame([6500, 7800, 7300], [$second['once'], $second['instead'], $second['year']]);

        // Индивидуалният маркетинг няма годишна сума.
        $this->assertNull(PlanBilling::figures($billing, $items[2], 2, 'yearly'));
        $this->assertFalse(PlanBilling::cell($billing, $items[2], 2, 'monthly')['priced']);
    }

    public function test_cell_strings(): void
    {
        $billing = PlanBilling::config($this->plans());
        $item = $this->plans()['items'][0];

        $yearly = PlanBilling::cell($billing, $item, 0, 'yearly');
        $this->assertSame('€2' . self::TS . '000', $yearly['price']);
        $this->assertSame('/ година', $yearly['suffix']);
        $this->assertSame('вместо €2' . self::TS . '400 при месечно плащане', $yearly['sub']);
        $this->assertSame('€2' . self::TS . '400 общо, от които €400 старт и €2' . self::TS . '000 абонамент за 12 месеца', $yearly['year']);

        // Текстът от пакета има предимство за месечния вариант; празен текст се изчислява.
        $this->assertSame('€2 600 за първата година (€400 + 11 × €200)', PlanBilling::cell($billing, $item, 0, 'monthly')['year']);
        $item['year_total'] = '';
        $this->assertSame('€2' . self::TS . '600 за първата година (€400 + 11 × €200)', PlanBilling::cell($billing, $item, 0, 'monthly')['year']);
    }

    public function test_the_toggle_is_off_without_a_discount(): void
    {
        $this->assertTrue(PlanBilling::config($this->plans())['enabled']);
        $this->assertFalse(PlanBilling::config(['billing' => ['yearly_months' => 12]])['enabled']);
        $this->assertSame(10, PlanBilling::config([])['yearly_months']);
    }

    public function test_text_lines_state_start_deposit_subscription_and_yearly_option(): void
    {
        $lines = implode("\n", PlanBilling::textLines($this->plans(), true));
        $this->assertStringContainsString('Стартов двигател. €400 еднократно', $lines);
        $this->assertStringContainsString('€100 при заявка за оферта и демо (не се връщат), останалите €300 при съгласие.', $lines);
        $this->assertStringContainsString('€2' . self::TS . '000 вместо €2' . self::TS . '400', $lines);
        $this->assertStringContainsString('€6' . self::TS . '500 вместо €7' . self::TS . '800', $lines);
        $this->assertStringContainsString('Индивидуален маркетинг. Индивидуална оферта, от €1 200 / месец', $lines);
        $this->assertStringContainsString('Всичко необходимо, за да присъства бизнесът ви професионално онлайн.', $lines);
    }

    public function test_kreo_the_faq_answer_and_llms_use_the_final_prices(): void
    {
        $knowledge = SiteKnowledge::text();
        foreach (['€400 еднократно', '€800 еднократно', 'от €1 200 / месец', '€100 при заявка за оферта и демо', 'Първата сума не се връща', 'Работен капацитет на месец'] as $text) {
            $this->assertStringContainsString($text, $knowledge);
        }
        $this->assertStringNotContainsString('€600 старт', $knowledge);
        $this->assertStringNotContainsString('плъзгач', $knowledge);

        $answer = FaqResponder::answer('Колко струва сайт?');
        $this->assertStringContainsString('€400 еднократно', $answer);
        $this->assertStringContainsString('след това €650 / месец', $answer);
        $this->assertStringContainsString('/paketi', $answer);

        $this->get('/llms.txt')->assertOk()
            ->assertSee('- Стартов двигател. €400 еднократно', false)
            ->assertSee('€200 при заявка за оферта и демо (не се връщат), останалите €600 при съгласие.', false)
            ->assertSee('Индивидуален маркетинг. Индивидуална оферта, от €1 200 / месец', false)
            ->assertDontSee('плъзгач', false);
    }

    public function test_the_quote_form_has_no_billing_field_but_old_posts_still_work(): void
    {
        Mail::fake();
        $this->get('/paketi')->assertOk()->assertDontSee('name="billing"', false)->assertDontSee('name="volume"', false);

        $payload = ['plan' => 0, 'name' => 'Мария', 'email' => 'm@example.com', 'business' => 'Лале', 'consent' => '1'];
        $this->post('/paketi/oferta', $payload + ['billing' => 'yearly', 'volume' => '8'])->assertRedirect();
        $lead = Project::sole();
        $this->assertStringContainsString('Плащане: годишно', $lead->description);
        $this->assertStringNotContainsString('Обем', $lead->description); // плъзгачът го няма

        $lead->delete();
        $this->post('/paketi/oferta', $payload + ['billing' => 'whatever'])->assertRedirect();
        $this->assertStringNotContainsString('Плащане:', Project::sole()->description);
    }
}
