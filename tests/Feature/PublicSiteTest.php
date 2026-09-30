<?php

namespace Tests\Feature;

use App\Mail\NewLeadMail;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Иван Иванов',
            'contact' => 'ivan@example.com',
            'message' => 'Искам сайт за моя ресторант.',
            'consent' => '1',
        ], $overrides);
    }

    public function test_public_pages_load(): void
    {
        foreach (['/', '/uslugi', '/proekti', '/za-nas', '/kontakti', '/poveritelnost', '/usloviya', '/sitemap.xml', '/robots.txt'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_old_urls_redirect_permanently(): void
    {
        $this->get('/about')->assertStatus(301)->assertRedirect('/za-nas');
        $this->get('/contact')->assertStatus(301)->assertRedirect('/kontakti');
    }

    public function test_unknown_page_shows_404(): void
    {
        $this->get('/nyama-takava-stranica')->assertNotFound()->assertSee('Страницата не е намерена');
    }

    public function test_public_registration_is_gone(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'x', 'email' => 'x@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertNotFound();
    }

    public function test_robots_blocks_admin_and_lists_sitemap(): void
    {
        $this->get('/robots.txt')->assertSee('Disallow: /admin')->assertSee('/sitemap.xml');
    }

    public function test_portfolio_only_shows_public_completed_projects(): void
    {
        $category = Category::create(['name' => 'Ресторанти']);
        $base = ['description' => 'd', 'start_date' => '2026-01-01', 'manager' => 'm', 'category_id' => $category->id];

        Project::create($base + ['name' => 'Видим проект', 'status' => 'Completed', 'is_public' => true]);
        Project::create($base + ['name' => 'Скрит проект', 'status' => 'Completed', 'is_public' => false]);
        Project::create($base + ['name' => 'Незавършен проект', 'status' => 'In Progress', 'is_public' => true]);

        $this->get('/proekti')->assertSee('Видим проект')->assertDontSee('Скрит проект')->assertDontSee('Незавършен проект');
    }

    public function test_empty_portfolio_shows_coming_soon(): void
    {
        $this->get('/proekti')->assertSee('Първите проекти идват скоро');
    }

    public function test_contact_form_creates_lead_and_notifies_team(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload())->assertRedirect()->assertSessionHas('success');

        $lead = Project::where('source', 'website')->firstOrFail();
        $this->assertSame('Запитване от Иван Иванов', $lead->name);
        $this->assertSame('ivan@example.com', $lead->client_email);
        $this->assertNull($lead->client_phone);
        $this->assertSame('Planning', $lead->status);
        $this->assertFalse($lead->is_public);

        Mail::assertSent(NewLeadMail::class);
    }

    public function test_contact_form_stores_phone_numbers(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload(['contact' => '+359 888 123 456']))->assertSessionHasNoErrors();

        $lead = Project::where('source', 'website')->firstOrFail();
        $this->assertSame('+359 888 123 456', $lead->client_phone);
        $this->assertNull($lead->client_email);
    }

    public function test_contact_form_validation(): void
    {
        $this->post('/kontakti', [])->assertSessionHasErrors(['name', 'contact', 'message', 'consent']);
        $this->post('/kontakti', $this->payload(['contact' => 'not-an-email@']))->assertSessionHasErrors('contact');
        $this->post('/kontakti', $this->payload(['contact' => 'abc']))->assertSessionHasErrors('contact');
        $this->post('/kontakti', $this->payload(['consent' => null]))->assertSessionHasErrors('consent');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_honeypot_drops_spam_silently(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload(['website' => 'http://spam.example']))->assertSessionHas('success');

        $this->assertDatabaseCount('projects', 0);
        Mail::assertNothingSent();
    }

    public function test_mail_failure_does_not_lose_the_lead(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->post('/kontakti', $this->payload())->assertSessionHas('success');

        $this->assertDatabaseCount('projects', 1);
    }

    public function test_contact_form_is_rate_limited(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/kontakti', $this->payload())->assertSessionHasNoErrors();
        }

        $this->post('/kontakti', $this->payload())->assertStatus(429);
    }
}
