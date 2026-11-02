<?php

namespace Tests\Feature;

use App\Mail\NewLeadMail;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'plan' => 1,
            'name' => 'Мария Петрова',
            'email' => 'Maria@Example.com',
            'phone' => '0888 123 456',
            'position' => 'Собственик',
            'business' => 'Студио Лале',
            'sector' => 'Козметичен салон',
            'city' => 'Бургас',
            'site_url' => 'lale.bg',
            'goal' => 'google',
            'timeline' => 'month',
            'consent' => '1',
        ], $overrides);
    }

    public function test_package_buttons_open_the_quote_form_with_the_plan_selected(): void
    {
        $plans = config('creatium.plans.items');

        $this->get('/paketi')->assertOk()
            ->assertSee('/paketi?paket=1#oferta', false)
            ->assertSee('id="oferta"', false)
            ->assertSee('Получете оферта');

        $this->get('/paketi?paket=1')->assertOk()
            ->assertSee('<option value="1" selected>' . e($plans[1]['name']) . '</option>', false);
        $this->get('/en/packages')->assertOk()->assertSee('Get a quote')->assertSee('Business sector');
    }

    public function test_a_quote_request_becomes_a_lead_and_shows_the_thank_you(): void
    {
        Mail::fake();
        $plan = config('creatium.plans.items')[1]['name'];

        $this->post('/paketi/oferta', $this->payload())->assertRedirect(url('/paketi') . '#oferta');

        $lead = Project::sole();
        $this->assertSame('Запитване за оферта от Мария Петрова', $lead->name);
        $this->assertSame('Оферта: ' . $plan, $lead->service);
        $this->assertSame('maria@example.com', $lead->client_email);
        $this->assertSame('0888 123 456', $lead->client_phone);
        $this->assertSame('https://lale.bg', $lead->website_url);
        $this->assertSame('website', $lead->source);
        $this->assertStringContainsString('Сфера: Козметичен салон', $lead->description);
        $this->assertStringContainsString('Град: Бургас', $lead->description);
        $this->assertStringContainsString('Позиция: Собственик', $lead->description);
        $this->assertNotNull($lead->email_sent_at);
        Mail::assertSent(NewLeadMail::class);

        $this->get('/paketi')
            ->assertSee('Благодарим ви, че избрахте нас!')
            ->assertSee('Нашият екип ще се свърже с вас в рамките на един работен ден. Благодарим ви.')
            ->assertDontSee('Стъпка 1 от 2');
    }

    public function test_quote_form_requires_the_client_profile(): void
    {
        $this->from('/paketi')->post('/paketi/oferta', $this->payload(['business' => '', 'sector' => '', 'city' => '', 'goal' => 'nope', 'consent' => null]))
            ->assertSessionHasErrors(['business', 'sector', 'city', 'goal', 'consent']);
        $this->assertSame(0, Project::count());
    }

    public function test_the_honeypot_fakes_success_and_stores_nothing(): void
    {
        Mail::fake();
        $this->post('/paketi/oferta', $this->payload(['website' => 'spam']))->assertSessionHas('quote_sent');
        $this->assertSame(0, Project::count());
        Mail::assertNothingSent();
    }
}
