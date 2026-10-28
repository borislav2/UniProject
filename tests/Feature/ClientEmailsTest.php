<?php

namespace Tests\Feature;

use App\Mail\ClientTemplateMail;
use App\Models\Category;
use App\Models\ClientEmail;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\ClientEmails;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ClientEmailsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['name' => 'Борислав Костадинов']);
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        return $user;
    }

    private function lead(array $overrides = []): Project
    {
        $this->seed(CategorySeeder::class);

        return Project::create(array_merge([
            'name' => 'Запитване от Мария Петрова', 'description' => 'Търся сайт.', 'start_date' => now(),
            'status' => 'Planning', 'manager' => 'Неразпределен', 'category_id' => Category::first()->id,
            'client_phone' => '0888 123 456', 'source' => 'website',
        ], $overrides));
    }

    private function offerData(array $overrides = []): array
    {
        return array_merge([
            'client_name' => 'Мария', 'business' => 'Студио Лавандула', 'sender' => 'Екипът на Creatium Lab',
            'price' => '850', 'monthly_price' => '400', 'deadline' => '2 до 3 седмици', 'valid_until' => now()->addWeek()->toDateString(),
            'payment' => '50% авансово при старт на проекта и 50% след одобрение на готовата демо версия.',
        ], $overrides);
    }

    public function test_guests_cannot_open_or_send(): void
    {
        $this->get('/admin/emails')->assertRedirect('/login');
        $this->post('/admin/emails/offer-start', ['to' => 'a@b.bg'])->assertRedirect('/login');
    }

    public function test_index_lists_every_template_and_is_in_the_menu(): void
    {
        $this->actingAs($this->admin())->get('/admin/emails')->assertOk()
            ->assertSee('Отговор на запитване')
            ->assertSee('Оферта: Стартов двигател')->assertSee('Оферта: Активен Маркетинг &amp; Растеж', false)
            ->assertDontSee('пакет Бизнес')
            ->assertSee('Имейли до клиенти')
            ->assertSee('Още няма изпратени имейли.');
    }

    public function test_create_prefills_the_client_from_the_inquiry(): void
    {
        $user = $this->admin();
        $lead = $this->lead(['client_email' => 'maria@salon.bg']);

        $this->actingAs($user)->get('/admin/emails/offer-active?project=' . $lead->id)->assertOk()
            ->assertSee('value="maria@salon.bg"', false)
            ->assertSee('value="Мария Петрова"', false)
            ->assertSee('value="Екипът на Creatium Lab"', false)
            ->assertSee('value="Проектно предложение: Дигитален растеж и маркетинг за {business} | Creatium Lab"', false)
            ->assertSee('value="850"', false)->assertSee('value="400"', false)
            ->assertSee('value="' . now()->addDays(7)->toDateString() . '"', false);

        $this->actingAs($user)->get('/admin/emails/nyama')->assertNotFound();
        $this->actingAs($user)->get('/admin/emails/offer-biznes')->assertNotFound();
        $this->actingAs($user)->get('/admin/inquiries')->assertSee('/admin/emails?project=' . $lead->id, false);
    }

    public function test_preview_renders_each_template_with_placeholders_and_escapes_input(): void
    {
        $user = $this->admin();

        foreach (array_keys(ClientEmails::all()) as $key) {
            $this->actingAs($user)->post("/admin/emails/$key/preview", ['data' => ['client_name' => '<script>alert(1)</script>']])
                ->assertOk()
                ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
                ->assertDontSee('<script>alert(1)</script>', false)
                ->assertSee('Creatium Lab');
        }

        $this->actingAs($user)->post('/admin/emails/offer-active/preview', ['data' => $this->offerData()])
            ->assertSee('Активен Маркетинг &amp; Растеж за Студио Лавандула', false)
            ->assertSee('€850 (еднократно)')->assertSee('€400 / месец')
            ->assertSee('Онлайн разплащания &amp; Резервации', false)
            ->assertSee('Ограничен прием на проекти')
            ->assertSee('валидни до ' . now()->addWeek()->format('d.m.Y') . ' г.')
            ->assertSee('Ако искате да уточним конкретен детайл');
        $this->actingAs($user)->post('/admin/emails/offer-start/preview', ['data' => ['business' => 'Мотор Про', 'price' => '450', 'monthly_price' => '150']])
            ->assertSee('Целенасочена архитектура')->assertSee('€450 (еднократно)')->assertSee('€150 / месец')
            ->assertSee('Мотор Про')->assertDontSee('валидни до')->assertDontSee('Ако искате да уточним');
        $this->actingAs($user)->post('/admin/emails/inquiry-reply/preview', ['data' => []])
            ->assertSee('С какво се отличава нашият подход')->assertSee('Стартов двигател')->assertSee('Индивидуално Партньорство')
            ->assertSee('ПРЕПОРЪЧАН')->assertSee('В какъв времеви рамков период планирате старта на проекта?')
            ->assertSee('С уважение,');
    }

    public function test_personal_note_shows_only_when_filled(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post('/admin/emails/inquiry-reply/preview', ['data' => ['client_name' => 'Мария']])->assertDontSee('Какво забелязахме');
        $this->actingAs($user)->post('/admin/emails/inquiry-reply/preview', ['data' => ['client_name' => 'Мария', 'personal_note' => 'Видяхме профила ви в Google.']])
            ->assertSee('Какво забелязахме')->assertSee('Видяхме профила ви в Google.');
    }

    public function test_sending_an_offer_mails_the_client_logs_it_and_saves_the_email_on_the_inquiry(): void
    {
        Mail::fake();
        $user = $this->admin();
        $lead = $this->lead();

        $this->actingAs($user)->post('/admin/emails/offer-active', [
            'to' => 'maria@salon.bg', 'subject' => ClientEmails::find('offer-active')['subject'], 'project_id' => $lead->id, 'data' => $this->offerData(),
        ])->assertRedirect('/admin/emails')->assertSessionHas('success');

        Mail::assertSent(ClientTemplateMail::class, function (ClientTemplateMail $mail) {
            $html = $mail->render();

            return $mail->hasTo('maria@salon.bg')
                && $mail->hasReplyTo(config('creatium.contact.email'))
                && $mail->hasSubject('Проектно предложение: Дигитален растеж и маркетинг за Студио Лавандула | Creatium Lab')
                && str_contains($html, 'Здравейте, Мария')
                && str_contains($html, '€850')
                && str_contains($html, 'валидни до ' . now()->addWeek()->format('d.m.Y'))
                && ! str_contains($html, '[');
        });

        $log = ClientEmail::sole();
        $this->assertSame('offer-active', $log->template);
        $this->assertSame('Проектно предложение: Дигитален растеж и маркетинг за Студио Лавандула | Creatium Lab', $log->subject);
        $this->assertNotNull($log->sent_at);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('maria@salon.bg', $lead->fresh()->client_email);

        $this->actingAs($user)->get('/admin/emails')->assertSee('maria@salon.bg')->assertSee('Изпратен')->assertSee('Мария Петрова');
    }

    public function test_an_existing_inquiry_email_is_not_overwritten(): void
    {
        Mail::fake();
        $lead = $this->lead(['client_email' => 'first@salon.bg']);

        $this->actingAs($this->admin())->post('/admin/emails/inquiry-reply', [
            'to' => 'other@salon.bg', 'subject' => 'Тема', 'project_id' => $lead->id,
            'data' => ['client_name' => 'Мария', 'business' => 'салона ви', 'sender' => 'Борислав'],
        ])->assertRedirect('/admin/emails');

        $this->assertSame('first@salon.bg', $lead->fresh()->client_email);
    }

    public function test_demo_site_email_links_the_demo_and_needs_a_real_link(): void
    {
        Mail::fake();
        $user = $this->admin();
        $lead = $this->lead(['name' => 'Запитване от Каракашев Ауто']);

        $this->actingAs($user)->get('/admin/emails/demo-site?project=' . $lead->id)->assertOk()
            ->assertSee('value="Каракашев Ауто"', false)
            ->assertSee('type="url"', false)
            ->assertSee(route('demo.show', 'avtoservis'));

        $data = ['client_name' => 'Г-н Каракашев', 'business' => 'Каракашев Ауто', 'sender' => 'Борислав', 'demo_url' => 'https://creatiumlab.com/demo/avtoservis',
            'call_note' => 'Искате клиентите да записват час за ремонт онлайн.', 'follow_up' => 'Ще ви се обадя в четвъртък.'];
        $this->actingAs($user)->post('/admin/emails/demo-site/preview', ['data' => $data])->assertOk()
            ->assertSee('Г-н Каракашев, ето демото за Каракашев Ауто')
            ->assertSee('href="https://creatiumlab.com/demo/avtoservis"', false)
            ->assertSee('От разговора ни')->assertSee('записват час за ремонт онлайн')
            ->assertSee('Ще ви се обадя в четвъртък.')->assertSee('Това е демо.');
        $this->actingAs($user)->post('/admin/emails/demo-site/preview', ['data' => ['call_note' => '', 'follow_up' => '']])->assertDontSee('От разговора ни')
            ->assertSee('Демото е в прикачения PDF файл')->assertDontSee('Вижте демото');

        $this->actingAs($user)->post('/admin/emails/demo-site', [
            'to' => 'office@karakashev.bg', 'subject' => 'Демо', 'data' => ['demo_url' => ''] + $data,
        ])->assertSessionHasErrors(['data.demo_url' => 'Сложете линк към демото или прикачете PDF с него.']);

        $this->actingAs($user)->post('/admin/emails/demo-site', [
            'to' => 'office@karakashev.bg', 'subject' => 'Демо', 'data' => ['demo_url' => 'javascript:alert(1)'] + $data,
        ])->assertSessionHasErrors('data.demo_url');
        Mail::assertNothingSent();

        $this->actingAs($user)->post('/admin/emails/demo-site', [
            'to' => 'office@karakashev.bg', 'subject' => ClientEmails::find('demo-site')['subject'], 'project_id' => $lead->id, 'data' => $data,
        ])->assertRedirect('/admin/emails');
        Mail::assertSent(ClientTemplateMail::class, fn (ClientTemplateMail $mail) => $mail->hasSubject('Демо на сайта за Каракашев Ауто | Creatium Lab'));
        $this->assertSame('demo-site', ClientEmail::sole()->template);
    }

    public function test_a_pdf_can_be_attached_and_other_files_are_refused(): void
    {
        Mail::fake();
        $user = $this->admin();
        $data = ['client_name' => 'Г-н Каракашев', 'business' => 'Каракашев Ауто', 'sender' => 'Борислав', 'demo_url' => ''];

        $this->actingAs($user)->get('/admin/emails/demo-site')->assertSee('enctype="multipart/form-data"', false)->assertSee('name="attachment"', false);

        $this->actingAs($user)->post('/admin/emails/demo-site', [
            'to' => 'office@karakashev.bg', 'subject' => 'Демо', 'data' => $data,
            'attachment' => \Illuminate\Http\UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('attachment');
        Mail::assertNothingSent();

        $pdf = \Illuminate\Http\UploadedFile::fake()->createWithContent('Оферта Каракашев<>.pdf', "%PDF-1.4\n%test\n");
        $this->actingAs($user)->post('/admin/emails/demo-site', [
            'to' => 'office@karakashev.bg', 'subject' => 'Демо', 'data' => $data, 'attachment' => $pdf,
        ])->assertRedirect('/admin/emails');

        Mail::assertSent(ClientTemplateMail::class, function (ClientTemplateMail $mail) {
            return $mail->hasAttachment(\Illuminate\Mail\Attachment::fromData(fn () => "%PDF-1.4\n%test\n", 'Оферта Каракашев.pdf')->withMime('application/pdf'))
                && str_contains($mail->render(), 'Демото е в прикачения PDF файл');
        });
        $this->assertSame('Оферта Каракашев.pdf', ClientEmail::sole()->attachment_name);
        $this->actingAs($user)->get('/admin/emails')->assertSee('+ Оферта Каракашев.pdf');
    }

    public function test_validation_blocks_bad_input(): void
    {
        Mail::fake();
        $user = $this->admin();

        $this->actingAs($user)->post('/admin/emails/offer-active', [
            'to' => 'not-an-email', 'subject' => 'Оферта',
            'data' => $this->offerData(['price' => '', 'valid_until' => now()->subDay()->toDateString()]),
        ])->assertSessionHasErrors(['to', 'data.price', 'data.valid_until']);

        Mail::assertNothingSent();
        $this->assertSame(0, ClientEmail::count());
    }

    public function test_a_mail_failure_is_shown_and_logged_without_crashing(): void
    {
        Mail::shouldReceive('to')->andThrow(new RuntimeException('535 Username and Password not accepted'));

        $this->actingAs($this->admin())->from('/admin/emails/offer-start')->post('/admin/emails/offer-start', [
            'to' => 'maria@salon.bg', 'subject' => 'Оферта', 'data' => $this->offerData(),
        ])->assertRedirect('/admin/emails/offer-start')->assertSessionHas('send_error');

        $log = ClientEmail::sole();
        $this->assertNull($log->sent_at);
        $this->assertStringContainsString('535', $log->error);
    }
}
