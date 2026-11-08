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

    public function test_site_says_the_deposit_is_not_refunded_and_capacity_is_monthly(): void
    {
        $this->get('/paketi')->assertOk()->assertSee('не се връщат')->assertSee('Работен капацитет на месец');
        $this->get('/en/packages')->assertOk()->assertSee('non-refundable')->assertSee('Working capacity per month');
        $this->get('/usloviya')->assertOk()->assertSee('не се връща, ако решите да не продължите');
    }
}
