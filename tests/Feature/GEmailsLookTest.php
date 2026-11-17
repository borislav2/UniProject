<?php

namespace Tests\Feature;

use App\Mail\ClientTemplateMail;
use App\Mail\ConsultationBookedMail;
use App\Mail\NewLeadMail;
use App\Mail\OutreachFollowUpMail;
use App\Mail\OutreachMail;
use App\Mail\SiteCheckReportMail;
use App\Models\OutreachProspect;
use App\Models\Project;
use App\Support\ClientEmails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Имейлите, офертите и презентацията са в стила на дизайн Г (17.11.2026): светъл фон, квадратни ъгли, мастилен бутон, един акцент, без стария тъмносин и син.
 * Новият вид не зависи от creatium.design. Писмата до потенциални клиенти остават прости, личен текст, само със същите шрифтове, цвят на връзките и подпис.
 * Задай MAIL_G_DUMP=/път/до/папка, за да запишеш всеки рендиран имейл като HTML (за снимки).
 */
class GEmailsLookTest extends TestCase
{
    use RefreshDatabase;

    /** Цветовете на стария имейл дизайн (тъмносиньо, синьо, светлосиньо, сиво-синкави). */
    private const OLD_COLOURS = ['#0f1a2b', '#2d63a3', '#9cc0eb', '#22406a', '#f5f8fc', '#eef1f5', '#e2e8f0', '#d6e3f5', '#c5d6ec', '#9fb3cc', '#2a4468', '#4b6a92', '#6d9bd1', '#e0ecf8', '#1f2a3a', '#475569', '#64748b', '#f4f6f9', '#fde68a'];

    private ?array $rendered = null;

    private function lead(): Project
    {
        $this->seed(\Database\Seeders\CategorySeeder::class);

        return Project::create([
            'category_id' => \App\Models\Category::first()->id, 'name' => 'Запитване от Мария Иванова', 'description' => "Услуги: сайт\nСъобщение: Здравейте", 'status' => 'Planning', 'start_date' => now()->toDateString(), 'manager' => 'Борислав', 'client_email' => 'maria@example.bg', 'client_phone' => '+359 888 123 456',
            'website_url' => 'https://lavandula.bg', 'service' => 'Оферта за Pro Ускорение', 'source' => 'website', 'consultation_at' => now()->addDays(2)->setTime(11, 0),
        ]);
    }

    /** A slice of the base64 of a logo file (the encoder may wrap lines, so not the whole string). */
    private function logoSlice(string $name): string
    {
        return substr(base64_encode(file_get_contents(public_path("images/{$name}.png"))), 2000, 120);
    }

    private function offerData(string $key, array $overrides = []): array
    {
        return array_merge(
            collect(ClientEmails::find($key)['fields'])->map(fn ($f) => $f['default'] ?? '')->all(),
            ['client_name' => 'Мария', 'business' => 'Студио Лавандула', 'valid_until' => now()->addWeek()->toDateString(), 'heard' => 'Искаме повече запитвания от Google.', 'why_now' => 'Сезонът започва след два месеца.'],
            $overrides
        );
    }

    private function client(string $key, array $data): string
    {
        return (new ClientTemplateMail(ClientEmails::find($key), $data, 'Тема'))->render();
    }

    /** @return array<string, string> име => HTML */
    private function everyEmail(): array
    {
        if ($this->rendered !== null) {
            return $this->rendered;
        }
        $emails = [];
        foreach (['offer-start', 'offer-active', 'offer-individual'] as $key) {
            $emails[$key] = $this->client($key, $this->offerData($key));
        }
        $emails['inquiry-reply'] = $this->client('inquiry-reply', ['client_name' => 'Мария', 'business' => 'Студио Лавандула', 'sender' => 'Борислав', 'personal_note' => 'Сайтът ви се зарежда бавно на телефон.']);
        $emails['demo-site'] = $this->client('demo-site', ['client_name' => 'Мария', 'business' => 'Студио Лавандула', 'sender' => 'Борислав', 'demo_url' => 'https://creatiumlab.com/demo/salon', 'call_note' => 'Искате повече обаждания.', 'follow_up' => '']);
        $emails['monthly-report'] = $this->client('monthly-report', ['client_name' => 'Мария', 'business' => 'Студио Лавандула', 'sender' => 'Борислав', 'month' => 'октомври 2026',
            'done' => "Обновихме страницата с услугите\nНастроихме Google профила", 'results' => "Посещения 420\nОбаждания 12", 'site_status' => 'Сайтът работи без прекъсвания.', 'next' => "Нови снимки\nСтатия за блога"]);
        $emails['consultation'] = (new ConsultationBookedMail($this->lead()))->render();
        $emails['new-lead'] = (new NewLeadMail($this->lead()))->render();

        $report = [
            'domain' => 'firma.bg', 'url' => 'https://firma.bg', 'reachable' => true, 'score' => 62, 'home_score' => 70,
            'checks' => [
                ['key' => 'title', 'status' => 'fail', 'title' => 'Няма заглавие за Google', 'detail' => 'Добавете заглавие.'],
                ['key' => 'https', 'status' => 'ok', 'title' => 'Сайтът е с HTTPS', 'detail' => 'Всичко е наред.'],
            ],
            'crawl' => ['pages' => 12, 'analyzed' => 12, 'limit' => 50, 'limited' => false, 'limited_by' => null, 'seconds' => 8],
            'issues' => [['key' => 'title_missing', 'severity' => 'error', 'count' => 3, 'examples' => [['url' => 'https://firma.bg/a', 'from' => 'https://firma.bg/']]]],
            'pagespeed' => null, 'shopping' => null,
        ];
        $emails['site-check'] = (new SiteCheckReportMail($report + ['locale' => 'bg']))->render();

        $prospect = OutreachProspect::create(['email' => 'info@lozata.bg', 'company' => 'Лозата', 'website' => 'https://lozata.bg', 'email_status' => 'published', 'status' => 'new']);
        $data = ['first_name' => 'Иван', 'sender' => 'Борислав', 'first_date' => '01.11.2026', 'finding' => 'Липсва описание в резултатите на Google',
            'observation' => "Здравейте.\n– Сайтът се зарежда бавно. Хората си тръгват.\n– Няма описание. Google показва случаен текст.\nКрай."];
        $emails['outreach'] = (new OutreachMail($prospect, $data, 'Тема'))->render();
        $emails['outreach-followup'] = (new OutreachFollowUpMail($prospect, $data, 'Re: Тема'))->render();

        if ($dir = getenv('MAIL_G_DUMP')) {
            @mkdir($dir, 0777, true);
            foreach ($emails as $name => $html) {
                file_put_contents($dir . '/' . $name . '.html', $html);
            }
        }

        return $this->rendered = $emails;
    }

    public function test_every_email_uses_the_g_tokens_and_none_of_the_old_look(): void
    {
        foreach ($this->everyEmail() as $name => $html) {
            $lower = strtolower($html);
            foreach (self::OLD_COLOURS as $old) {
                $this->assertStringNotContainsString($old, $lower, "{$name} still has {$old}");
            }
            $this->assertStringNotContainsString('gradient', $lower, $name);
            $this->assertDoesNotMatchRegularExpression('/border-radius\s*:\s*[1-9]/', $html, "{$name} has a rounded corner");
            $this->assertDoesNotMatchRegularExpression('/box-shadow|text-shadow/', $html, $name);
            $this->assertStringContainsString('#0C0E12', $html, $name);
            $this->assertStringContainsString('Onest', $html, $name);
            $this->assertStringContainsString('color-scheme', $html, $name);
        }
    }

    public function test_client_emails_carry_the_g_logo_the_ink_button_and_the_g_fonts(): void
    {
        foreach (['offer-start', 'offer-active', 'offer-individual', 'inquiry-reply', 'demo-site', 'monthly-report', 'consultation', 'site-check'] as $name) {
            $html = $this->everyEmail()[$name];
            // Mailable::render() inlines the embedded logos as data URIs.
            $flat = preg_replace('/\s+/', '', $html); // the encoder wraps the base64 lines
            $this->assertStringContainsString($this->logoSlice('logo-g'), $flat, $name);
            $this->assertStringContainsString($this->logoSlice('logo-g-dark'), $flat, $name);
            $this->assertStringNotContainsString($this->logoSlice('logo-white'), $flat, $name);
            $this->assertStringContainsString('Geologica', $html, $name);
            $this->assertStringContainsString('JetBrains Mono', $html, $name);
            $this->assertStringContainsString('#E8431B', $html, $name);
        }
        $this->assertMatchesRegularExpression('/class="g-btn"[^>]*background:#0C0E12;/', $this->everyEmail()['consultation']);
        $this->assertFileExists(public_path('images/logo-g.png'));
        $this->assertFileExists(public_path('images/logo-g-dark.png'));
    }

    public function test_the_look_does_not_depend_on_the_public_design(): void
    {
        $data = $this->offerData('offer-active');
        config(['creatium.design' => 'current']);
        $current = $this->client('offer-active', $data);
        config(['creatium.design' => 'g']);

        $this->assertSame($current, $this->client('offer-active', $data));
    }

    public function test_outreach_letters_stay_plain_but_share_the_g_fonts_link_colour_and_signature(): void
    {
        $emails = $this->everyEmail();
        foreach (['outreach', 'outreach-followup'] as $name) {
            $html = $emails[$name];
            $this->assertStringNotContainsString('<img', $html, $name . ' is a plain letter, no pictures');
            $this->assertStringContainsString('Onest', $html, $name);
            $this->assertStringContainsString('color:#C4300F', $html, $name);
            $this->assertStringContainsString('Борислав', $html, $name);
            $this->assertStringContainsString('отпишете се тук', $html, $name);
        }
    }

    public function test_offers_still_hide_empty_optional_blocks(): void
    {
        $full = $this->client('offer-active', $this->offerData('offer-active'));
        $this->assertStringContainsString('Сезонът започва след два месеца.', $full);
        $this->assertStringContainsString('Искаме повече запитвания от Google.', $full);

        $bare = $this->client('offer-active', $this->offerData('offer-active', ['why_now' => '', 'heard' => '', 'demo_url' => '', 'extras' => '', 'ps' => '', 'closing' => '']));
        $this->assertStringNotContainsString('Сезонът започва', $bare);
        $this->assertStringNotContainsString('Какво чухме от Вас', $bare);
        $this->assertStringNotContainsString('Вижте демото', $bare);
        $this->assertStringNotContainsString('P.S.', $bare);
        // The prices and the rest of the offer are untouched.
        $this->assertStringContainsString('€800', $bare);
        $this->assertStringContainsString('Pro Ускорение', $bare);
    }

    public function test_the_admin_preview_renders_the_same_new_look(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $admin = \App\Models\User::factory()->create();
        $admin->roles()->attach(\App\Models\Role::where('slug', 'developer')->value('id'));
        $html = $this->actingAs($admin)->post('/admin/emails/offer-start/preview', ['data' => $this->offerData('offer-start')])->assertOk()->getContent();

        $this->assertStringContainsString(asset('images/logo-g.png'), $html);
        $this->assertStringContainsString('Core Изграждане', $html);
        $this->assertStringNotContainsString('#0f1a2b', strtolower($html));
    }

    public function test_the_presentation_has_ten_slides_in_the_g_look_in_both_languages(): void
    {
        foreach (['/prezentatsiya', '/en/presentation'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(10, preg_match_all('/<section[^>]*class="[^"]*\bgp-slide\b/', $html), $url);
            $this->assertStringContainsString('gp-deck', $html, $url);
            $this->assertStringNotContainsString('border-radius', strtolower(preg_replace('/border-radius\s*:\s*0\b[^;]*;?/', '', $html)), $url);
            foreach (['#0f1a2b', '#2d63a3', '#9cc0eb', 'linear-gradient', 'radial-gradient'] as $old) {
                $this->assertStringNotContainsString($old, strtolower($html), "{$url} {$old}");
            }
        }
    }
}
