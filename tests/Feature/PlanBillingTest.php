<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Support\Chat\FaqResponder;
use App\Support\Chat\SiteKnowledge;
use App\Support\PlanBilling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Цените на собствениците: един модел от 17.11.2026 (само месечен абонамент, без стартова цена и капаро), годишно плащане 10 месеца (plans.billing). */
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
        // One price model (17.11.2026): one price line, the monthly subscription, no start price.
        $items = $this->plans()['items'];
        foreach ([[500, 0], [800, 1], [1200, 2]] as [$monthly, $i]) {
            $this->assertNull(PlanBilling::startPrice($items[$i]));
            $this->assertSame($monthly, PlanBilling::monthlyPrice($items[$i]));
        }
        $this->assertSame(2000, PlanBilling::amount('€2 000 / година'));
        $this->assertNull(PlanBilling::amount('без цена'));

        // An old saved plan (a start line and a subscription line) still reads: the first line is the start, the second the subscription.
        $old = ['prices' => ['€400 еднократно за изграждането и първия месец', 'след това €200 / месец']];
        $this->assertSame(400, PlanBilling::startPrice($old));
        $this->assertSame(200, PlanBilling::monthlyPrice($old));
        $this->assertSame(1200, PlanBilling::monthlyPrice(['prices' => ['Индивидуална оферта', 'от €1 200 / месец']]));
        $this->assertNull(PlanBilling::monthlyPrice(['prices' => []]));
        $this->assertNull(PlanBilling::monthlyPrice([]));
    }

    public function test_figures_for_monthly_and_yearly_billing(): void
    {
        $billing = PlanBilling::config($this->plans());
        $items = $this->plans()['items'];

        // Without a start price the year is twelve months; yearly billing is 10 months (2 months free), the same figures as design G.
        $this->assertSame(['monthly' => 500, 'once' => null, 'instead' => null, 'year' => 6000], PlanBilling::figures($billing, $items[0], 0, 'monthly'));
        foreach ([[5000, 6000], [8000, 9600], [12000, 14400]] as $i => [$once, $instead]) {
            $yearly = PlanBilling::figures($billing, $items[$i], $i, 'yearly');
            $this->assertSame([$once, $instead, $once], [$yearly['once'], $yearly['instead'], $yearly['year']]);
            $this->assertSame($this->plans()['g_items'][$i]['yearly'], $yearly['once']);
        }

        // An old saved plan with a start price keeps its old arithmetic.
        $old = ['prices' => ['€400 еднократно', 'след това €200 / месец']];
        $this->assertSame(2600, PlanBilling::figures($billing, $old, 0, 'monthly')['year']);
        $this->assertSame(2400, PlanBilling::figures($billing, $old, 0, 'yearly')['year']);

        $this->assertNull(PlanBilling::figures($billing, ['prices' => ['Индивидуална оферта']], 2, 'yearly'));
        $this->assertFalse(PlanBilling::cell($billing, ['prices' => []], 2, 'monthly')['priced']);
    }

    public function test_cell_strings(): void
    {
        $billing = PlanBilling::config($this->plans());
        $item = $this->plans()['items'][0];

        $yearly = PlanBilling::cell($billing, $item, 0, 'yearly');
        $this->assertSame('€5' . self::TS . '000', $yearly['price']);
        $this->assertSame('/ година', $yearly['suffix']);
        $this->assertSame('вместо €6' . self::TS . '000 при месечно плащане', $yearly['sub']);
        $this->assertSame('€5' . self::TS . '000 за абонамент за 12 месеца', $yearly['year']);

        // Без цена на старта: 12 × месеца; текстът от пакета има предимство за месечния вариант.
        $this->assertSame('€6' . self::TS . '000 за 12 месеца (12 × €500)', PlanBilling::cell($billing, $item, 0, 'monthly')['year']);
        $item['year_total'] = 'Моят текст';
        $this->assertSame('Моят текст', PlanBilling::cell($billing, $item, 0, 'monthly')['year']);

        $old = ['prices' => ['€400 еднократно', 'след това €200 / месец']];
        $this->assertSame('€2' . self::TS . '600 за първата година (€400 + 11 × €200)', PlanBilling::cell($billing, $old, 0, 'monthly')['year']);
    }

    public function test_the_toggle_is_off_without_a_discount(): void
    {
        $this->assertTrue(PlanBilling::config($this->plans())['enabled']);
        $this->assertFalse(PlanBilling::config(['billing' => ['yearly_months' => 12]])['enabled']);
        $this->assertSame(10, PlanBilling::config([])['yearly_months']);
    }

    public function test_text_lines_state_the_monthly_subscription_and_the_yearly_option(): void
    {
        $lines = implode("\n", PlanBilling::textLines($this->plans(), true));
        $this->assertStringContainsString('Core Изграждане. от €500 / месец', $lines);
        $this->assertStringContainsString('Pro Ускорение. от €800 / месец', $lines);
        $this->assertStringContainsString('Enterprise Партньорство. от €1 200 / месец', $lines);
        $this->assertStringContainsString('€5' . self::TS . '000 вместо €6' . self::TS . '000', $lines);
        $this->assertStringContainsString('€8' . self::TS . '000 вместо €9' . self::TS . '600', $lines);
        $this->assertStringContainsString('€12' . self::TS . '000 вместо €14' . self::TS . '400', $lines);
        $this->assertStringContainsString('Всичко необходимо, за да присъства бизнесът ви професионално онлайн.', $lines);
        $this->assertDoesNotMatchRegularExpression(\App\Support\OnePriceModel2026::OLD_PATTERN, $lines);
    }

    public function test_kreo_the_faq_answer_and_llms_use_the_one_price_model(): void
    {
        $knowledge = SiteKnowledge::text();
        foreach (['от €500 / месец', 'от €800 / месец', 'от €1 200 / месец', 'само месечен абонамент, без еднократна стартова цена', 'Работен капацитет на месец'] as $text) {
            $this->assertStringContainsString($text, $knowledge);
        }
        $this->assertDoesNotMatchRegularExpression(\App\Support\OnePriceModel2026::OLD_PATTERN, $knowledge);
        $this->assertStringNotContainsString('плъзгач', $knowledge);

        $answer = FaqResponder::answer('Колко струва сайт?');
        $this->assertStringContainsString('от €500 / месец', $answer);
        $this->assertStringContainsString('от €800 / месец', $answer);
        $this->assertStringContainsString('/paketi', $answer);

        $this->get('/llms.txt')->assertOk()
            ->assertSee('- Core Изграждане. от €500 / месец', false)
            ->assertSee('- Enterprise Партньорство. от €1 200 / месец', false)
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
