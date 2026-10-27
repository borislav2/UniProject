<?php

namespace Tests\Feature;

use App\Mail\OutreachMail;
use App\Models\OutreachProspect;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OutreachClickTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

    private function prospect(array $values = []): OutreachProspect
    {
        return OutreachProspect::create($values + [
            'email' => 'info@lozata.bg', 'company' => 'Ресторант Лозата', 'website' => 'https://lozata.bg',
            'email_status' => 'published', 'status' => 'sent', 'sent_at' => now()->subHour(),
        ]);
    }

    private function link(OutreachProspect $prospect, string $link): string
    {
        return URL::signedRoute('outreach.click', ['prospect' => $prospect->id, 'link' => $link]);
    }

    public function test_email_links_go_through_click_tracking(): void
    {
        $prospect = $this->prospect();
        $links = (new OutreachMail($prospect, ['observation' => 'Текст'], 'Тема'))->links();

        $this->assertStringContainsString("/o/{$prospect->id}/check?signature=", $links['check']);
        $this->assertStringContainsString("/o/{$prospect->id}/booking?signature=", $links['booking']);
        $this->assertStringContainsString("/o/{$prospect->id}/site?signature=", $links['site']);
    }

    public function test_a_click_is_recorded_and_redirects_to_the_page(): void
    {
        $prospect = $this->prospect();

        $this->withHeader('User-Agent', self::BROWSER)->get($this->link($prospect, 'check'))
            ->assertRedirect(route('site-check') . '?utm_source=outreach&utm_medium=email&utm_campaign=first-email');
        $this->withHeader('User-Agent', self::BROWSER)->get($this->link($prospect, 'booking'))
            ->assertRedirect(route('contact') . '?utm_source=outreach&utm_medium=email&utm_campaign=first-email#konsultatsiya');

        $prospect->refresh();
        $this->assertSame(2, $prospect->click_count);
        $this->assertSame('booking', $prospect->last_click_link);
        $this->assertNotNull($prospect->clicked_at);
    }

    public function test_bots_scanners_early_clicks_and_admins_are_not_counted(): void
    {
        $prospect = $this->prospect();
        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Microsoft Office SafeLinks Scanner)')->get($this->link($prospect, 'site'))->assertRedirect();
        $this->withHeader('User-Agent', '')->get($this->link($prospect, 'site'))->assertRedirect();

        $fresh = $this->prospect(['email' => 'info@salon.bg', 'sent_at' => now()->subSeconds(10)]);
        $this->withHeader('User-Agent', self::BROWSER)->get($this->link($fresh, 'site'))->assertRedirect();

        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'admin')->value('id'));
        $this->actingAs($admin)->withHeader('User-Agent', self::BROWSER)->get($this->link($prospect, 'site'))->assertRedirect();

        $this->assertSame([0, 0], [$prospect->fresh()->click_count, $fresh->fresh()->click_count]);
    }

    public function test_links_must_be_signed_and_known(): void
    {
        $prospect = $this->prospect();

        $this->get("/o/{$prospect->id}/check")->assertForbidden();
        $this->get(URL::signedRoute('outreach.click', ['prospect' => $prospect->id, 'link' => 'evil']))->assertNotFound();
        $this->assertSame(0, $prospect->fresh()->click_count);
    }

    public function test_admin_list_shows_clicks_and_filters_them(): void
    {
        $this->prospect(['company' => 'Кликнал бизнес', 'clicked_at' => now(), 'last_clicked_at' => now(), 'click_count' => 2, 'last_click_link' => 'check']);
        $this->prospect(['email' => 'info@salon.bg', 'company' => 'Тих бизнес']);

        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'admin')->value('id'));

        $this->actingAs($admin)->get('/admin/outreach')->assertOk()
            ->assertSee('Кликнали (1)')->assertSee('Кликна: Безплатна проверка')->assertSee('(2×)');
        $this->actingAs($admin)->get('/admin/outreach?clicked=1')->assertOk()
            ->assertSee('Кликнал бизнес')->assertDontSee('Тих бизнес');
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('1 кликнали за 7 дни');
    }

    public function test_privacy_pages_mention_click_tracking(): void
    {
        $this->get('/poveritelnost')->assertOk()->assertSee('дали сте кликнали по линк в нашето писмо')->assertSee('Не следим дали отваряте писмото.');
        $this->get('/en/privacy')->assertOk()->assertSee('whether you clicked a link in our email');
    }
}
