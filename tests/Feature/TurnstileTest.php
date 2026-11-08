<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Support\Turnstile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Иван Иванов',
            'phone' => '+359 888 123 456',
            'email' => 'ivan@example.bg',
            'service' => 'new-system',
            'message' => 'Искам сайт.',
            'consent' => '1',
        ], $overrides);
    }

    private function enable(): void
    {
        config(['services.turnstile.site_key' => '1x00000000000000000000AA', 'services.turnstile.secret_key' => 'test-secret']);
    }

    public function test_without_keys_forms_work_and_no_widget_is_shown(): void
    {
        Mail::fake();
        Http::fake();

        $this->get('/kontakti')->assertOk()->assertDontSee('challenges.cloudflare.com', false);
        $this->post('/kontakti', $this->payload())->assertSessionHasNoErrors();

        $this->assertSame(1, Project::count());
        Http::assertNothingSent();
    }

    public function test_widget_is_rendered_on_all_three_forms_when_enabled(): void
    {
        $this->enable();

        foreach (['/kontakti', '/paketi', '/proverka-na-sait', '/en/contact'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('class="cf-turnstile"', false)
                ->assertSee('data-sitekey="1x00000000000000000000AA"', false)
                ->assertSee('challenges.cloudflare.com/turnstile/v0/api.js', false);
        }
    }

    public function test_missing_token_is_rejected(): void
    {
        $this->enable();
        Mail::fake();
        Http::fake();

        $this->from('/kontakti')->post('/kontakti', $this->payload())->assertSessionHasErrors(Turnstile::FIELD);
        $this->from('/paketi')->post('/paketi/oferta', ['plan' => 0, 'name' => 'Иван', 'business' => 'Фирма', 'email' => 'a@b.bg', 'consent' => '1'])
            ->assertSessionHasErrors(Turnstile::FIELD);
        $this->from('/proverka-na-sait')->post('/proverka-na-sait', ['domain' => 'example.bg', 'email' => 'a@b.bg', 'consent' => '1'])
            ->assertSessionHasErrors(Turnstile::FIELD);

        $this->assertSame(0, Project::count());
    }

    public function test_failed_verification_is_rejected_and_passed_one_creates_the_lead(): void
    {
        $this->enable();
        Mail::fake();

        Http::fake([Turnstile::VERIFY_URL => Http::sequence()
            ->push(['success' => false, 'error-codes' => ['invalid-input-response']])
            ->push(['success' => true])]);
        $this->from('/kontakti')->post('/kontakti', $this->payload([Turnstile::FIELD => 'bad']))->assertSessionHasErrors(Turnstile::FIELD);
        $this->assertSame(0, Project::count());

        $this->post('/kontakti', $this->payload([Turnstile::FIELD => 'good']))->assertSessionHasNoErrors();
        $this->assertSame(1, Project::count());

        Http::assertSent(fn ($request) => $request->url() === Turnstile::VERIFY_URL
            && $request['secret'] === 'test-secret' && $request['response'] === 'good');
    }

    public function test_cloudflare_outage_does_not_lose_a_real_lead(): void
    {
        $this->enable();
        Mail::fake();
        Http::fake([Turnstile::VERIFY_URL => Http::response('down', 503)]);

        $this->post('/kontakti', $this->payload([Turnstile::FIELD => 'token']))->assertSessionHasNoErrors();
        $this->assertSame(1, Project::count());
    }
}
