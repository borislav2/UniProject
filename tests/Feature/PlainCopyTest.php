<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\ClientEmails;
use App\Support\PlainCopy2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Собствениците (ноември 2026): „навсякъде където има тирета и двуеточия искам да се махнат“.
 * Видимият текст на сайта и на писмата няма тире (–, —, „ - “) и двоеточие, освен в часове (09:00) и адреси (https://).
 */
class PlainCopyTest extends TestCase
{
    use RefreshDatabase;

    /** Visible text: no scripts, styles, comments, tags or attributes; the title and meta description are added. */
    public static function visibleText(string $html): string
    {
        $meta = [];
        if (preg_match('#<meta name="description" content="([^"]*)"#', $html, $m)) {
            $meta[] = $m[1];
        }
        $html = preg_replace(['#<(script|style|noscript|template)\b[^>]*>.*?</\1>#si', '#<!--.*?-->#s'], ' ', $html);
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', $html)) . ' ' . implode(' ', $meta), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_replace('/\s+/u', ' ', $text);
    }

    /** @return list<string> the offending snippets with a little context */
    public static function offences(string $text): array
    {
        // Allowed: web addresses, clock times and ranges of times written with a word.
        $text = preg_replace(['#\b[a-z][a-z0-9+.-]*://\S+#i', '/\b\d{1,2}:\d{2}\b/u'], ' ', $text);
        preg_match_all('/.{0,40}(?:[–—:]| - ).{0,40}/u', $text, $m);

        return array_map('trim', $m[0]);
    }

    private function urls(): array
    {
        preg_match_all('#<loc>([^<]+)</loc>#', $this->get('/sitemap.xml')->getContent(), $m);
        $paths = array_map(fn ($url) => parse_url($url, PHP_URL_PATH) ?: '/', $m[1]);

        return array_values(array_unique(array_merge($paths, [
            '/llms.txt', '/proverka-na-sait', '/en/website-check', '/kontakti', '/en/contact', '/paketi', '/en/packages',
            '/paketi?plashtane=godishno', '/prezentatsiya', '/en/presentation', '/blog',
        ], array_map(fn ($demo) => '/demo/' . $demo['slug'], config('creatium.demos')))));
    }

    public function test_public_pages_have_no_dashes_or_colons_in_visible_text(): void
    {
        $failures = [];
        foreach ($this->urls() as $path) {
            $response = $this->get($path);
            $response->assertOk();
            $body = $response->getContent();
            $text = str_ends_with($path, '.txt') ? $body : self::visibleText($body);
            foreach (self::offences($text) as $snippet) {
                $failures[] = "$path  …{$snippet}…";
            }
        }

        $this->assertSame([], $failures, implode("\n", array_slice($failures, 0, 120)));
    }

    public function test_offer_previews_have_no_dashes_or_colons(): void
    {
        $failures = [];
        foreach (ClientEmails::all() as $key => $tpl) {
            $data = ['client_name' => 'Иван', 'business' => 'Автосервиз Иванов'];
            foreach ($tpl['fields'] as $name => $field) {
                $data[$name] ??= $field['default'] ?? match ($field['type'] ?? 'text') {
                    'date' => now()->addDays(7)->toDateString(),
                    'url' => 'https://example.com/demo',
                    default => 'Примерен текст',
                };
            }
            $html = view($tpl['view'], ['data' => $data, 'package' => $tpl['package'] ?? null, 'subject' => ClientEmails::subject($tpl['subject'], $data)])->render();
            $text = self::visibleText($html) . ' ' . ClientEmails::subject($tpl['subject'], $data);
            foreach (self::offences($text) as $snippet) {
                $failures[] = "$key  …{$snippet}…";
            }
        }

        $this->assertSame([], $failures, implode("\n", array_slice($failures, 0, 120)));
    }

    public function test_lead_and_booking_emails_have_no_dashes_or_colons(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $slot = \App\Support\Booking::days()[0]['slots'][0]['value'];
        $this->post('/kontakti', [
            'name' => 'Иван Иванов', 'phone' => '0888123456', 'email' => 'ivan@example.bg', 'site_url' => 'motorpro.bg',
            'interests' => ['website', 'ads'], 'message' => 'Искам сайт.', 'consultation' => $slot, 'consent' => '1',
        ])->assertSessionHasNoErrors();

        foreach ([\App\Mail\NewLeadMail::class, \App\Mail\ConsultationBookedMail::class] as $class) {
            \Illuminate\Support\Facades\Mail::assertSent($class, function ($mail) {
                $text = self::visibleText($mail->render()) . ' ' . $mail->envelope()->subject;
                $this->assertSame([], self::offences($text), get_class($mail));

                return true;
            });
        }
    }

    public function test_outreach_and_site_check_emails_have_no_dashes_or_colons(): void
    {
        $prospect = \App\Models\OutreachProspect::create([
            'email' => 'info@lozata.bg', 'company' => 'Лозата', 'website' => 'https://lozata.bg', 'email_status' => 'published', 'status' => 'new',
        ]);
        $data = ['first_name' => 'Иван', 'sender' => 'Борислав', 'first_date' => '01.11.2026', 'finding' => 'Липсва описание в резултатите на Google',
            'observation' => "Здравейте.\n– Сайтът се зарежда бавно. Хората си тръгват.\n– Няма описание. Google показва случаен текст.\nКрай."];
        $failures = [];
        foreach ([new \App\Mail\OutreachMail($prospect, $data, 'Тема'), new \App\Mail\OutreachFollowUpMail($prospect, $data, 'Re: Тема')] as $mail) {
            $html = $mail->render();
            $text = view($mail instanceof \App\Mail\OutreachFollowUpMail ? 'emails.outreach-followup-text' : 'emails.outreach-text', [
                'data' => $data, 'parts' => \App\Mail\OutreachMail::observationParts($data['observation']), 'links' => $mail->links(), 'plain' => \App\Mail\OutreachMail::targets(), 'prospect' => $prospect,
                'unsubscribeUrl' => $mail->unsubscribeUrl(), 'teamSignature' => false, 'sender' => 'Борислав',
            ])->render();
            foreach ([self::visibleText($html), preg_replace('/\s+/u', ' ', $text)] as $t) {
                foreach (self::offences($t) as $snippet) {
                    $failures[] = get_class($mail) . "  …{$snippet}…";
                }
            }
        }
        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_site_check_report_email_has_no_dashes_or_colons(): void
    {
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
        foreach (['bg', 'en'] as $locale) {
            app()->setLocale($locale);
            $mail = new \App\Mail\SiteCheckReportMail($report + ['locale' => $locale]);
            $text = self::visibleText($mail->render()) . ' ' . $mail->envelope()->subject;
            $this->assertSame([], self::offences($text), $locale);
        }
    }

    public function test_the_migration_replaces_only_exact_old_defaults_and_keeps_a_backup(): void
    {
        [$old, $new] = [array_key_first(PlainCopy2026::REPLACE), PlainCopy2026::REPLACE[array_key_first(PlainCopy2026::REPLACE)]];
        $key = PlainCopy2026::KEYS[0];
        ContentBlock::create(['key' => $key, 'locale' => 'bg', 'value' => ['a' => $old, 'b' => [$old, 'Моят текст: свой'], 'c' => $old . ' и още']]);
        ContentBlock::create(['key' => PlainCopy2026::KEYS[1], 'locale' => 'bg', 'value' => ['x' => 'Нищо за смяна: собствен текст']]);
        ContentBlock::create(['key' => $key . PlainCopy2026::BACKUP, 'locale' => 'en', 'value' => ['old' => 'backup']]);
        ContentBlock::create(['key' => $key, 'locale' => 'en', 'value' => ['a' => 'Partnerships: our own']]);

        $this->assertSame([$key . '/bg'], PlainCopy2026::apply());

        $value = ContentBlock::where('key', $key)->where('locale', 'bg')->first()->value;
        $this->assertSame($new, $value['a']);
        $this->assertSame([$new, 'Моят текст: свой'], $value['b']);
        $this->assertSame($old . ' и още', $value['c']);
        $this->assertSame($old, ContentBlock::where('key', $key . ':backup-2026-11-08-g')->first()->value['a']);

        // Running it again changes nothing and never overwrites the backup.
        $this->assertSame([], PlainCopy2026::apply());
        $this->assertSame($old, ContentBlock::where('key', $key . ':backup-2026-11-08-g')->first()->value['a']);
    }

    public function test_every_replacement_is_free_of_dashes_and_colons_and_is_the_new_default(): void
    {
        $defaults = [];
        foreach (['creatium', 'creatium_en'] as $file) {
            foreach (PlainCopy2026::KEYS as $key) {
                $config = (array) config("$file.$key");
                array_walk_recursive($config, function ($item) use (&$defaults) {
                    if (is_string($item)) {
                        $defaults[$item] = true;
                    }
                });
            }
        }

        foreach (PlainCopy2026::REPLACE as $old => $new) {
            $this->assertNotSame($old, $new);
            $this->assertSame([], self::offences($new), $new);
            $this->assertArrayHasKey($new, $defaults, "Not a config default: $new");
            $this->assertArrayNotHasKey($old, $defaults, "Still a config default: $old");
        }
    }
}
