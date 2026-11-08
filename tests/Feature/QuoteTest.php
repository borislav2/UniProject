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
        $this->get('/en/packages')->assertOk()->assertSee('Get a quote')->assertSee('What your business does');
    }

    public function test_a_quote_request_becomes_a_lead_and_shows_the_thank_you(): void
    {
        Mail::fake();
        $plan = config('creatium.plans.items')[1]['name'];

        $this->post('/paketi/oferta', $this->payload())->assertRedirect(url('/paketi') . '#oferta');

        $lead = Project::sole();
        $this->assertSame('Запитване за оферта от Мария Петрова', $lead->name);
        $this->assertSame('Оферта за ' . $plan, $lead->service);
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
            ->assertSee('Получихме запитването ви.')
            ->assertSee('Ще ви изпратим оферта до един работен ден на имейла или телефона, които оставихте.')
            ->assertDontSee('Стъпка 1 от 2');
    }

    public function test_quote_form_requires_only_name_business_contact_and_consent(): void
    {
        $this->from('/paketi')->post('/paketi/oferta', $this->payload(['business' => '', 'goal' => 'nope', 'consent' => null]))
            ->assertSessionHasErrors(['business', 'goal', 'consent']);
        $this->assertSame(0, Project::count());
    }

    public function test_a_quote_with_only_name_business_and_phone_creates_a_lead(): void
    {
        Mail::fake();
        $this->post('/paketi/oferta', ['plan' => 0, 'name' => 'Иван', 'business' => 'Фирма', 'phone' => '0888 111 222', 'consent' => '1'])
            ->assertSessionHasNoErrors()->assertSessionHas('quote_sent');

        $lead = Project::sole();
        $this->assertNull($lead->client_email);
        $this->assertSame('0888 111 222', $lead->client_phone);
        $this->assertNotNull($lead->email_sent_at);
        Mail::assertSent(NewLeadMail::class);
    }

    public function test_a_quote_with_only_an_email_creates_a_lead(): void
    {
        Mail::fake();
        $this->post('/paketi/oferta', ['plan' => 1, 'name' => 'Иван', 'business' => 'Фирма', 'email' => 'Iv@Example.com', 'consent' => '1'])
            ->assertSessionHasNoErrors();

        $lead = Project::sole();
        $this->assertSame('iv@example.com', $lead->client_email);
        $this->assertNull($lead->client_phone);
        Mail::assertSent(NewLeadMail::class);
    }

    public function test_a_quote_without_email_and_phone_is_rejected(): void
    {
        $this->from('/paketi')->post('/paketi/oferta', ['plan' => 0, 'name' => 'Иван', 'business' => 'Фирма', 'consent' => '1'])
            ->assertSessionHasErrors(['email', 'phone']);
        $this->assertSame(0, Project::count());
    }

    public function test_the_quote_form_is_short_and_the_extra_fields_sit_in_a_collapsible(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertDontSee('Стъпка 1 от 2')
            ->assertSee('По желание, за по-точна оферта')
            ->assertSee('Изпратете запитването')
            ->assertSee('Без ангажимент. Отговор до 1 работен ден.')
            ->assertSee('data-sticky-cta', false);
    }

    public function test_the_honeypot_fakes_success_and_stores_nothing(): void
    {
        Mail::fake();
        $this->post('/paketi/oferta', $this->payload(['website' => 'spam']))->assertSessionHas('quote_sent');
        $this->assertSame(0, Project::count());
        Mail::assertNothingSent();
    }
}
