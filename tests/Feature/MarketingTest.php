<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Support\LeadAttribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MarketingTest extends TestCase
{
    use RefreshDatabase;

    private function submitLead(?array $attribution = null)
    {
        Mail::fake();

        $request = $attribution ? $this->withSession([LeadAttribution::SESSION_KEY => $attribution]) : $this;

        return $request->post('/kontakti', [
            'name' => 'Мария', 'contact' => 'maria@example.com', 'message' => 'Здравейте', 'consent' => '1',
        ]);
    }

    public function test_utm_parameters_are_remembered_for_the_visit(): void
    {
        $this->get('/?utm_source=instagram&utm_medium=bio&utm_campaign=launch')
            ->assertSessionHas(LeadAttribution::SESSION_KEY . '.utm_source', 'instagram')
            ->assertSessionHas(LeadAttribution::SESSION_KEY . '.utm_campaign', 'launch');
    }

    public function test_external_referrer_is_remembered_but_own_site_is_not(): void
    {
        $this->get('/', ['Referer' => 'https://www.google.com/search?q=x'])
            ->assertSessionHas(LeadAttribution::SESSION_KEY . '.referrer', 'www.google.com');

        $this->flushSession();
        $this->get('/uslugi', ['Referer' => 'http://localhost/'])
            ->assertSessionMissing(LeadAttribution::SESSION_KEY);
    }

    public function test_admin_pages_do_not_overwrite_attribution(): void
    {
        $this->get('/login?utm_source=test')->assertSessionMissing(LeadAttribution::SESSION_KEY);
    }

    public function test_lead_gets_channel_and_utm_fields(): void
    {
        $this->submitLead(['utm_source' => 'instagram', 'utm_medium' => 'bio', 'utm_campaign' => 'launch', 'referrer' => null, 'click_id' => null])
            ->assertSessionHas('lead_created', true);

        $lead = Project::where('source', 'website')->firstOrFail();
        $this->assertSame('Instagram', $lead->lead_channel);
        $this->assertSame('bio', $lead->utm_medium);
        $this->assertSame('launch', $lead->utm_campaign);
    }

    public function test_lead_without_attribution_is_direct(): void
    {
        $this->submitLead();

        $this->assertSame('Директно', Project::where('source', 'website')->value('lead_channel'));
    }

    public function test_channel_detection(): void
    {
        $this->assertSame('Google', LeadAttribution::channel(['referrer' => 'www.google.bg']));
        $this->assertSame('Google', LeadAttribution::channel(['click_id' => 'gclid']));
        $this->assertSame('Facebook', LeadAttribution::channel(['referrer' => 'l.facebook.com']));
        $this->assertSame('Instagram', LeadAttribution::channel(['referrer' => 'l.instagram.com']));
        $this->assertSame('Meta реклама', LeadAttribution::channel(['click_id' => 'fbclid']));
        $this->assertSame('Newsletter', LeadAttribution::channel(['utm_source' => 'newsletter']));
        $this->assertSame('example.com', LeadAttribution::channel(['referrer' => 'example.com']));
        $this->assertSame('Директно', LeadAttribution::channel(null));
    }

    public function test_dashboard_shows_leads_by_channel(): void
    {
        $this->submitLead(['utm_source' => 'google', 'utm_medium' => null, 'utm_campaign' => null, 'referrer' => null, 'click_id' => null]);

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $admin = \App\Models\User::factory()->create();
        $admin->roles()->attach(\App\Models\Role::where('slug', 'admin')->value('id'));

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Запитвания от сайта по канал')->assertSee('Google');
    }

    public function test_no_pixel_or_banner_without_pixel_id(): void
    {
        config(['creatium.meta_pixel_id' => null]);

        $this->get('/')->assertDontSee('cookie-banner')->assertDontSee('fbevents.js');
        $this->get('/poveritelnost')->assertSee('Не използваме аналитични или рекламни бисквитки');
    }

    public function test_pixel_is_behind_consent_banner_when_configured(): void
    {
        config(['creatium.meta_pixel_id' => '123456789012345']);

        $this->get('/')
            ->assertSee('cookie-banner', false)
            ->assertSee('123456789012345')
            ->assertSee('Настройки за бисквитки');
        $this->get('/poveritelnost')->assertSee('Meta Pixel');
    }

    public function test_pixel_id_must_be_numeric(): void
    {
        putenv('META_PIXEL_ID=12345</script><script>alert(1)');
        $config = require base_path('config/creatium.php');
        putenv('META_PIXEL_ID');

        $this->assertNull($config['meta_pixel_id']);
    }
}
