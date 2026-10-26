<?php

namespace Tests\Feature;

use App\Models\OutreachProspect;
use App\Models\Role;
use App\Models\User;
use App\Support\Outreach\EmailVerifier;
use App\Support\Outreach\Outreach;
use App\Support\SiteCheck\HostResolver;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OutreachCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(HostResolver::class, new class extends HostResolver
        {
            public function publicIp(string $host): ?string
            {
                return '93.184.216.34';
            }
        });
        $this->app->instance(EmailVerifier::class, new class extends EmailVerifier
        {
            protected function acceptsMail(string $domain): bool
            {
                return $domain !== 'nomail.bg';
            }
        });
    }

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['name' => 'Борислав']);
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        return $user;
    }

    private function unchecked(string $email, string $website, string $company, ?string $page = null): OutreachProspect
    {
        return OutreachProspect::create([
            'email' => $email, 'website' => $website, 'company' => $company, 'email_status' => 'guess', 'status' => 'new', 'score' => 40,
            'personalization' => 'Имейлът е публикуван на сайта: ' . ($page ?? $website),
        ]);
    }

    public function test_unchecked_businesses_get_a_site_check_and_only_real_published_emails_are_confirmed(): void
    {
        Http::fake([
            'https://podmetlite.eu/' => Http::response('<html><head><title>Под метлите</title></head><body><a href="/kontakti.html">Контакти</a></body></html>'),
            'https://podmetlite.eu/kontakti.html' => Http::response('<p>Пишете ни: <a href="mailto:office@podmetlite.eu">office@podmetlite.eu</a></p>'),
            'https://pizza-amore.bg/contacts/' => Http::response('<p>amorekn@gmail.com</p>'),
            'https://pizza-amore.bg/' => Http::response('<html><body>Пица</body></html>'),
            'https://stale.bg/' => Http::response('<html><body>Пишете на hello@stale.bg</body></html>'),
            'https://nomail.bg/' => Http::response('<html><body>info@nomail.bg</body></html>'),
            '*' => Http::response('', 404),
        ]);
        $user = $this->admin();

        $own = $this->unchecked('office@podmetlite.eu', 'https://podmetlite.eu', 'Под метлите', 'https://podmetlite.eu/kontakti.html');
        $free = $this->unchecked('amorekn@gmail.com', 'https://pizza-amore.bg', 'Пицария Аморе', 'https://pizza-amore.bg/contacts/');
        $gone = $this->unchecked('info@stale.bg', 'https://stale.bg', 'Старо');
        $platform = $this->unchecked('info@notino.bg', 'https://www.notino.bg', 'При коконите');
        $foreign = $this->unchecked('emk@other.bg', 'http://mak06.net', 'Мак - 06');
        $noMail = $this->unchecked('info@nomail.bg', 'https://nomail.bg', 'Без поща');

        $this->actingAs($user)->get('/admin/outreach')->assertOk()
            ->assertSee('Провери сайта и имейла на непроверените (6)')->assertSee('data-check-work', false);

        $progress = $this->actingAs($user)->postJson('/admin/outreach/check')->assertOk()->json();
        $this->assertSame([6, 0], [$progress['checked'], $progress['remaining']]);

        $this->assertSame('published', $own->fresh()->email_status);
        $this->assertTrue($own->fresh()->canBeEmailed());
        $this->assertNotEmpty($own->fresh()->findings);
        $this->assertSame('published', $free->fresh()->email_status);

        $this->assertSame(['guess', 'Имейлът не се намира на сайта им.'], [$gone->fresh()->email_status, $gone->fresh()->check_note]);
        $this->assertStringContainsString('голяма платформа', $platform->fresh()->check_note);
        $this->assertSame('guess', $platform->fresh()->email_status);
        $this->assertStringContainsString('чужд домейн (other.bg)', $foreign->fresh()->check_note);
        $this->assertSame(['guess', 'Домейнът nomail.bg не приема поща.'], [$noMail->fresh()->email_status, $noMail->fresh()->check_note]);
        foreach ([$gone, $platform, $foreign, $noMail] as $prospect) {
            $this->assertFalse($prospect->fresh()->canBeEmailed());
        }

        // Проверените не се проверяват пак, а бележката се вижда в списъка.
        $this->actingAs($user)->get('/admin/outreach')->assertDontSee('data-check-work', false)->assertSee('Имейлът не се намира на сайта им.');
        $this->assertSame(0, $this->actingAs($user)->postJson('/admin/outreach/check')->json('checked'));
    }

    public function test_a_new_harvey_import_does_not_undo_the_check(): void
    {
        $prospect = $this->unchecked('office@podmetlite.eu', 'https://podmetlite.eu', 'Под метлите');
        $prospect->forceFill(['email_status' => 'published', 'site_checked_at' => now(), 'findings' => []])->save();

        $csv = tempnam(sys_get_temp_dir(), 'harvey');
        file_put_contents($csv, "email,company_name,website,email_status,score\noffice@podmetlite.eu,Под метлите,https://podmetlite.eu,guess,40\n");
        Outreach::import($csv);

        $this->assertSame('published', $prospect->fresh()->email_status);
    }

    public function test_the_command_checks_and_sends_nothing(): void
    {
        Http::fake(['*' => Http::response('', 404)]);
        $this->unchecked('info@stale.bg', 'https://stale.bg', 'Старо');

        $this->artisan('creatium:outreach-check')->expectsOutputToContain('Проверени: 1. Остават: 0')->assertSuccessful();
    }

    public function test_guests_cannot_run_the_check(): void
    {
        $this->postJson('/admin/outreach/check')->assertUnauthorized();
    }
}
