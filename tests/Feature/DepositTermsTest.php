<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\DepositTerms2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositTermsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_copy_of_plans_gets_the_non_refundable_deposit_and_monthly_capacity(): void
    {
        ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => [
            'intro' => 'Собствен текст',
            'items' => [['deposit' => '€100 при заявка за оферта и демо, останалите €300 при съгласие.']],
            'compare' => [['label' => 'Работен капацитет', 'values' => ['До 2 ч.']], ['label' => 'SEO', 'values' => ['Базово']]],
        ]]);

        $this->assertSame(['plans/bg'], DepositTerms2026::apply());

        $value = ContentBlock::where('key', 'plans')->first()->value;
        $this->assertSame('Собствен текст', $value['intro']);
        $this->assertSame('€100 при заявка за оферта и демо (не се връщат), останалите €300 при съгласие.', $value['items'][0]['deposit']);
        $this->assertSame('Работен капацитет на месец', $value['compare'][0]['label']);
        $this->assertSame('SEO', $value['compare'][1]['label']);
        $this->assertNotNull(ContentBlock::where('key', 'plans:backup-2026-11-08-b')->first());

        // A second run changes nothing.
        $this->assertSame([], DepositTerms2026::apply());
    }

    public function test_site_has_no_deposit_any_more_and_capacity_is_monthly(): void
    {
        // Since 17.11.2026 (OnePriceModel2026) there is no deposit; the monthly capacity label stays.
        $this->get('/paketi')->assertOk()->assertDontSee('не се връщат')->assertSee('Работен капацитет на месец');
        $this->get('/en/packages')->assertOk()->assertDontSee('non-refundable')->assertSee('Working capacity per month');
        $this->get('/usloviya')->assertOk()->assertDontSee('не се връща, ако решите да не продължите');
    }

    public function test_admin_copy_gets_the_individual_plan_deposit_once(): void
    {
        ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => ['items' => [
            ['name' => 'Стартов двигател', 'deposit' => 'x'],
            ['name' => 'Индивидуален маркетинг', 'deposit' => ''],
        ]]]);
        ContentBlock::create(['key' => 'questions', 'locale' => 'bg', 'value' => [
            ['q' => 'Как протича процесът на плащане?', 'a' => 'Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите. Край.'],
        ]]);

        $this->assertSame(['plans/bg', 'questions/bg'], DepositTerms2026::applyIndividual());

        $plans = ContentBlock::where('key', 'plans')->first()->value;
        $this->assertSame('x', $plans['items'][0]['deposit']);
        $this->assertStringContainsString('не се връща', $plans['items'][1]['deposit']);
        $this->assertStringContainsString('При Индивидуален маркетинг също има капаро', ContentBlock::where('key', 'questions')->first()->value[0]['a']);
        $this->assertSame([], DepositTerms2026::applyIndividual());
    }

    public function test_the_terms_describe_the_monthly_subscription_of_the_individual_plan(): void
    {
        $this->get('/paketi')->assertOk()->assertSee('Enterprise Партньорство');
        $this->get('/usloviya')->assertOk()->assertSee('Enterprise Партньорство е от €1 200 на месец според индивидуалната оферта')->assertDontSee('капаро');
    }
}
