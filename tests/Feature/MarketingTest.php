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
            'name' => 'Мария', 'phone' => '0888123456', 'service' => 'GEO & SEO видимост', 'message' => 'Здравейте', 'consent' => '1',
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

    public function test_no_tracking_or_banner_without_gtm_id(): void
    {
        config(['creatium.gtm_id' => null]);

        $this->get('/')->assertDontSee('googletagmanager.com')->assertDontSee('cookie-banner')->assertDontSee('Настройки за бисквитки');
        $this->get('/poveritelnost')->assertSee('Не използваме аналитични или рекламни бисквитки');
    }

    public function test_gtm_loads_after_consent_mode_defaults(): void
    {
        config(['creatium.gtm_id' => 'GTM-5QRGW6DP']);

        $html = $this->get('/')
            ->assertSee('cookie-banner', false)
            ->assertSee('ns.html?id=GTM-5QRGW6DP', false)
            ->assertSee('Настройки за бисквитки')
            ->getContent();

        $default = strpos($html, "gtag('consent', 'default'");
        $this->assertNotFalse($default);
        $this->assertStringContainsString("analytics_storage: 'denied'", $html);
        $this->assertLessThan(strpos($html, 'googletagmanager.com/gtm.js'), $default);
        $this->assertLessThan(strpos($html, '<title>'), strpos($html, '<!-- Google Tag Manager -->'));

        $this->get('/biskvitki')->assertSee('Google Analytics 4')->assertSee('Meta Pixel');
        $this->get('/poveritelnost')->assertSee('Google Tag Manager');
    }

    public function test_lead_is_pushed_to_the_data_layer(): void
    {
        config(['creatium.gtm_id' => 'GTM-5QRGW6DP']);

        $this->followingRedirects()->post('/kontakti', [
            'name' => 'Мария', 'phone' => '0888123456', 'service' => 'GEO & SEO видимост', 'consent' => '1',
        ])->assertSee("event: 'generate_lead'", false)->assertSee('GEO \u0026 SEO', false);

        $this->get('/')->assertDontSee('generate_lead');
    }

    public function test_gtm_id_is_validated_and_off_outside_production(): void
    {
        putenv('GTM_ID=GTM-1</script><script>alert(1)');
        $config = require base_path('config/creatium.php');
        $this->assertNull($config['gtm_id']);

        putenv('GTM_ID');
        $config = require base_path('config/creatium.php');
        $this->assertNull($config['gtm_id']);

        putenv('GTM_ID=GTM-ABC1234');
        $config = require base_path('config/creatium.php');
        putenv('GTM_ID');
        $this->assertSame('GTM-ABC1234', $config['gtm_id']);
    }

    public function test_search_console_verification_meta(): void
    {
        config(['creatium.google_site_verification' => 'abcDEF123_-xyz']);

        $this->get('/')->assertSee('<meta name="google-site-verification" content="abcDEF123_-xyz">', false);
    }
}
