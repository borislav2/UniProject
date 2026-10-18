<?php

namespace Tests\Feature;

use App\Mail\OutreachMail;
use App\Models\ClientEmail;
use App\Models\OutreachProspect;
use App\Models\OutreachSuppression;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OutreachTest extends TestCase
{
    use RefreshDatabase;

    private const CSV = "\xEF\xBB\xBFemail,first_name,last_name,company_name,title,website,linkedin_url,industry,personalization,email_status,score,seniority,source,status,created_at\n"
        . "office@lozata.bg,Мария,,Ресторант Лозата,Собственик,https://lozata.bg,,Ресторант,No online booking; menu is a PDF,verified,82,,osm,new,2026-10-16\n"
        . "INFO@Salon.bg,,,Студио Лавандула,,https://salon.bg,,Салон,Blog stale since 2023,risky,64,,osm,new,2026-10-16\n"
        . "guess@roof.bg,,,Здрав покрив,,https://roof.bg,,Покриви,,guess,40,,osm,new,2026-10-16\n"
        . "not-an-email,,,Нищо,,,,,,verified,10,,osm,new,2026-10-16\n"
        . "office@lozata.bg,Мария,,Ресторант Лозата,,https://lozata.bg,,Ресторант,Duplicate row,verified,90,,osm,new,2026-10-16\n";

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['name' => 'Борислав Костадинов']);
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        return $user;
    }

    private function configure(): void
    {
        config([
            'creatium.outreach.from_address' => 'info@creativemoa.site',
            'creatium.outreach.daily_limit' => 2,
            'mail.mailers.outreach.host' => 'smtp.example.test',
        ]);
    }

    private function import(User $user): void
    {
        $file = UploadedFile::fake()->createWithContent('prospects.csv', self::CSV);
        $this->actingAs($user)->post('/admin/outreach/import', ['file' => $file])
            ->assertRedirect('/admin/outreach')
            ->assertSessionHas('success', 'Внесени: 3 нови, 1 обновени, 1 пропуснати (невалидни, повтарящи се или отписани).');
    }

    private function message(array $overrides = []): array
    {
        return $overrides + [
            'subject' => 'Въпрос за сайта на Ресторант Лозата',
            'first_name' => 'Мария',
            'observation' => 'Разгледахме сайта ви и видяхме, че менюто е само PDF и няма заявка за маса.',
            'sender' => 'Борислав',
        ];
    }

    public function test_guests_cannot_reach_it_and_it_is_in_the_menu(): void
    {
        $this->get('/admin/outreach')->assertRedirect('/login');
        $this->actingAs($this->admin())->get('/admin')->assertSee('Потенциални клиенти');
    }

    public function test_harvey_csv_is_imported_with_duplicates_and_bad_rows_handled(): void
    {
        $user = $this->admin();
        OutreachSuppression::add('gone@lozata.bg', 'unsubscribed');
        $this->import($user);

        $this->assertSame(3, OutreachProspect::count());
        $lozata = OutreachProspect::where('email', 'office@lozata.bg')->firstOrFail();
        $this->assertSame('Ресторант Лозата', $lozata->company);
        $this->assertSame(90, $lozata->score);
        $this->assertSame('info@salon.bg', OutreachProspect::where('company', 'Студио Лавандула')->value('email'));
        $this->assertFalse(OutreachProspect::where('email', 'guess@roof.bg')->first()->canBeEmailed());

        $this->actingAs($user)->get('/admin/outreach')->assertOk()
            ->assertSee('Ресторант Лозата')->assertSee('Пощата за първи писма не е настроена')
            ->assertSee('href="' . route('admin.outreach.compose', $lozata) . '"', false)
            ->assertDontSee('href="' . route('admin.outreach.compose', OutreachProspect::where('email', 'guess@roof.bg')->first()) . '"', false);
    }

    public function test_preview_shows_the_message_with_an_unsubscribe_link(): void
    {
        $user = $this->admin();
        $this->import($user);
        $prospect = OutreachProspect::where('email', 'office@lozata.bg')->firstOrFail();
        $this->configure();

        $this->actingAs($user)->get('/admin/outreach/' . $prospect->id)->assertOk()->assertSee('Duplicate row');
        $this->actingAs($user)->post('/admin/outreach/' . $prospect->id . '/preview', $this->message())->assertOk()
            ->assertSee('Здравейте, Мария,')->assertSee('менюто е само PDF')->assertSee('отпишете се тук')->assertSee('/otpisvane/' . $prospect->id, false);
    }

    public function test_sending_needs_the_separate_mailbox(): void
    {
        Mail::fake();
        $user = $this->admin();
        $this->import($user);
        $prospect = OutreachProspect::where('email', 'office@lozata.bg')->firstOrFail();

        $this->actingAs($user)->post('/admin/outreach/' . $prospect->id . '/send', $this->message())->assertSessionHas('send_error');
        Mail::assertNothingSent();
        $this->assertSame('new', $prospect->fresh()->status);
    }

    public function test_sending_goes_through_the_outreach_mailer_once_and_respects_the_daily_limit(): void
    {
        Mail::fake();
        $user = $this->admin();
        $this->import($user);
        $this->configure();
        $lozata = OutreachProspect::where('email', 'office@lozata.bg')->firstOrFail();
        $salon = OutreachProspect::where('email', 'info@salon.bg')->firstOrFail();

        $this->actingAs($user)->post('/admin/outreach/' . $lozata->id . '/send', ['observation' => 'кратко'] + $this->message())->assertSessionHasErrors('observation');

        $this->actingAs($user)->post('/admin/outreach/' . $lozata->id . '/send', $this->message())->assertRedirect('/admin/outreach')->assertSessionHas('success');
        Mail::assertSent(OutreachMail::class, function (OutreachMail $mail) {
            $this->assertSame('outreach', $mail->mailer);
            $this->assertTrue($mail->hasFrom('info@creativemoa.site'));
            $this->assertTrue($mail->hasReplyTo('info@creativemoa.site'));
            $this->assertStringContainsString('List-Unsubscribe=One-Click', $mail->headers()->text['List-Unsubscribe-Post']);
            $this->assertStringContainsString('/otpisvane/', $mail->headers()->text['List-Unsubscribe']);

            return $mail->hasTo('office@lozata.bg');
        });
        $this->assertSame('sent', $lozata->fresh()->status);
        $log = ClientEmail::sole();
        $this->assertSame($lozata->id, $log->outreach_prospect_id);
        $this->assertSame('Първо писмо (потенциален клиент)', $log->templateLabel());

        // Second send to the same business is blocked.
        $this->actingAs($user)->post('/admin/outreach/' . $lozata->id . '/send', $this->message())->assertSessionHas('send_error');

        // Daily limit is 2: one more is allowed, then nothing.
        $this->actingAs($user)->post('/admin/outreach/' . $salon->id . '/send', $this->message())->assertSessionHas('success');
        $extra = OutreachProspect::create(['email' => 'hello@third.bg', 'company' => 'Трети', 'email_status' => 'verified']);
        $this->actingAs($user)->post('/admin/outreach/' . $extra->id . '/send', $this->message())->assertSessionHas('send_error');
        Mail::assertSentCount(2);
    }

    public function test_unsubscribe_link_and_one_click_suppress_the_address_for_good(): void
    {
        $user = $this->admin();
        $this->import($user);
        $prospect = OutreachProspect::where('email', 'office@lozata.bg')->firstOrFail();
        $url = URL::signedRoute('outreach.unsubscribe', ['prospect' => $prospect->id]);

        $this->get('/otpisvane/' . $prospect->id)->assertForbidden();
        $this->get($url)->assertOk()->assertSee('Отписахте се')->assertSee('office@lozata.bg');
        $this->assertSame('unsubscribed', $prospect->fresh()->status);
        $this->assertTrue(OutreachSuppression::where('email', 'office@lozata.bg')->exists());

        $salon = OutreachProspect::where('email', 'info@salon.bg')->firstOrFail();
        $this->post(URL::signedRoute('outreach.unsubscribe', ['prospect' => $salon->id]), ['List-Unsubscribe' => 'One-Click'])->assertNoContent();
        $this->assertSame('unsubscribed', $salon->fresh()->status);

        // A new import never brings them back, and the admin cannot reset them.
        $this->import2($user);
        $this->assertSame('unsubscribed', $prospect->fresh()->status);
        $this->actingAs($user)->patch('/admin/outreach/' . $prospect->id, ['status' => 'new'])->assertSessionHas('send_error');
        $this->assertFalse($prospect->fresh()->canBeEmailed());
    }

    public function test_admin_imports_the_list_harvey_exported_on_the_server(): void
    {
        $user = $this->admin();
        $path = tempnam(sys_get_temp_dir(), 'harvey');
        config(['creatium.outreach.harvey_csv' => $path . '.missing', 'creatium.outreach.harvey_site_emails_csv' => $path . '.missing']);
        $this->actingAs($user)->get('/admin/outreach')->assertDontSee('Внеси от сървъра');
        $this->actingAs($user)->post('/admin/outreach/import-server')->assertSessionHas('send_error');

        file_put_contents($path, self::CSV);
        config(['creatium.outreach.harvey_csv' => $path]);
        $this->actingAs($user)->get('/admin/outreach')->assertSee('Внеси от сървъра');
        $this->actingAs($user)->post('/admin/outreach/import-server')->assertSessionHas('success', 'От сървъра: 3 нови, 1 обновени, 1 пропуснати.');
        $this->assertSame(3, OutreachProspect::count());

        // Имейлите от сайтовете (tools/harvey/find_site_emails.py) се внасят със същия бутон.
        $sitePath = $path . '.site';
        file_put_contents($sitePath, "email,first_name,last_name,company_name,title,website,industry,personalization,email_status,score\n"
            . "info@salon-iris.bg,,,Салон Ирис,,https://salon-iris.bg,beauty,Имейлът е публикуван на сайта: https://salon-iris.bg/kontakti,verified,60\n");
        config(['creatium.outreach.harvey_site_emails_csv' => $sitePath]);
        $this->actingAs($user)->post('/admin/outreach/import-server')->assertSessionHas('success', 'От сървъра: 1 нови, 4 обновени, 1 пропуснати.');
        $this->assertTrue(OutreachProspect::where('email', 'info@salon-iris.bg')->firstOrFail()->canBeEmailed());
        $this->actingAs($user)->get('/admin/outreach')->assertSee('Козметичен салон');
        unlink($sitePath);

        $this->artisan('creatium:outreach-import', ['path' => $path])->expectsOutputToContain('Нищо не е изпратено.')->assertSuccessful();
        $this->artisan('creatium:outreach-import', ['path' => $path . '.missing'])->assertFailed();
        unlink($path);
    }

    private function import2(User $user): void
    {
        $file = UploadedFile::fake()->createWithContent('prospects.csv', self::CSV);
        $this->actingAs($user)->post('/admin/outreach/import', ['file' => $file])->assertRedirect('/admin/outreach');
    }
}
