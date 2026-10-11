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
            'client_name' => 'Мария', 'business' => 'козметичния ви салон в София', 'sender' => 'Борислав Костадинов',
            'price' => '450', 'deadline' => '2 седмици', 'advance' => '225', 'valid_until' => now()->addWeek()->toDateString(),
            'payment' => '50% аванс, 50% при пускане', 'pages' => 'Начало, Услуги, Контакти', 'not_included' => 'хостинг и домейн',
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
            ->assertSee('Отговор на запитване за сайт')
            ->assertSee('Оферта: пакет Старт')->assertSee('Оферта: пакет Бизнес')->assertSee('Оферта: пакет Растеж')
            ->assertSee('Имейли до клиенти')
            ->assertSee('Още няма изпратени имейли.');
    }

    public function test_create_prefills_the_client_from_the_inquiry(): void
    {
        $user = $this->admin();
        $lead = $this->lead(['client_email' => 'maria@salon.bg']);

        $this->actingAs($user)->get('/admin/emails/offer-biznes?project=' . $lead->id)->assertOk()
            ->assertSee('value="maria@salon.bg"', false)
            ->assertSee('value="Мария Петрова"', false)
            ->assertSee('value="Борислав Костадинов"', false)
            ->assertSee('value="Вашата оферта е готова: пакет Бизнес"', false)
            ->assertSee('value="Начало, Услуги, Цени, За нас, Контакти"', false);

        $this->actingAs($user)->get('/admin/emails/nyama')->assertNotFound();
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

        $this->actingAs($user)->post('/admin/emails/offer-rastezh/preview', ['data' => $this->offerData(['monthly_price' => '150', 'min_term' => '6 месеца', 'texts_per_month' => '2'])])
            ->assertSee('Оферта · пакет Растеж')->assertSee('150 €/месец')->assertSee('Нови текстове за сайта: 2 на месец')
            ->assertSee('Не обещаваме конкретна позиция в Google');
        $this->actingAs($user)->post('/admin/emails/offer-start/preview', ['data' => []])->assertSee('[Цена за изработка (€)] €');
        $this->actingAs($user)->post('/admin/emails/inquiry-reply/preview', ['data' => []])->assertSee('Старт')->assertSee('Растеж')->assertSee('Няколко въпроса към вас')->assertSee('Директно с нас двамата');
    }

    public function test_personal_and_fomo_fields_show_only_when_filled(): void
    {
        $user = $this->admin();
        $empty = $this->actingAs($user)->post('/admin/emails/offer-start/preview', ['data' => $this->offerData()])->getContent();
        $this->assertStringNotContainsString('Какво чухме от вас', $empty);
        $this->assertStringNotContainsString('Бонус, ако потвърдите', $empty);
        $this->assertStringContainsString('Предлагаме ви пакет', $empty);

        $this->actingAs($user)->post('/admin/emails/offer-start/preview', ['data' => $this->offerData([
            'heard' => 'Искате повече записвания през седмицата.',
            'slots' => 'Имаме място за още 2 нови проекта през ноември.',
            'bonus' => 'първият месец поддръжка без допълнително заплащане',
        ])])
            ->assertSee('Какво чухме от вас')->assertSee('Искате повече записвания през седмицата.')
            ->assertSee('Затова ви предлагаме пакет')
            ->assertSee('Имаме място за още 2 нови проекта през ноември.')
            ->assertSee('Бонус, ако потвърдите до ' . now()->addWeek()->format('d.m.Y'))
            ->assertSee('бонусът важи до');

        $this->actingAs($user)->post('/admin/emails/inquiry-reply/preview', ['data' => ['client_name' => 'Мария', 'personal_note' => 'Видяхме профила ви в Google.']])
            ->assertSee('Какво забелязахме')->assertSee('Видяхме профила ви в Google.');

        // Без измислени факти и забранени фрази от DESIGN.md.
        foreach (['inquiry-reply', 'offer-start', 'offer-biznes', 'offer-rastezh'] as $key) {
            $html = $this->actingAs($user)->post("/admin/emails/$key/preview", ['data' => $this->offerData()])->getContent();
            foreach (['гаранц', 'Не просто сайт', 'най-добри', 'години опит'] as $banned) {
                $this->assertStringNotContainsStringIgnoringCase($banned, $html, "$key: $banned");
            }
        }
    }

    public function test_sending_an_offer_mails_the_client_logs_it_and_saves_the_email_on_the_inquiry(): void
    {
        Mail::fake();
        $user = $this->admin();
        $lead = $this->lead();

        $this->actingAs($user)->post('/admin/emails/offer-biznes', [
            'to' => 'maria@salon.bg', 'subject' => 'Вашата оферта е готова: пакет Бизнес', 'project_id' => $lead->id, 'data' => $this->offerData(),
        ])->assertRedirect('/admin/emails')->assertSessionHas('success');

        Mail::assertSent(ClientTemplateMail::class, function (ClientTemplateMail $mail) {
            $html = $mail->render();

            return $mail->hasTo('maria@salon.bg')
                && $mail->hasReplyTo(config('creatium.contact.email'))
                && $mail->hasSubject('Вашата оферта е готова: пакет Бизнес')
                && str_contains($html, 'Здравейте, Мария')
                && str_contains($html, '450 €')
                && str_contains($html, 'До 5 страници: Начало, Услуги, Контакти')
                && str_contains($html, 'Цената и срокът са запазени до ' . now()->addWeek()->format('d.m.Y'))
                && ! str_contains($html, '[');
        });

        $log = ClientEmail::sole();
        $this->assertSame('offer-biznes', $log->template);
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

    public function test_validation_blocks_bad_input(): void
    {
        Mail::fake();
        $user = $this->admin();

        $this->actingAs($user)->post('/admin/emails/offer-start', [
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
