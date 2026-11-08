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

    /** Всички полета на шаблона с подразбиращите се текстове, с поправки отгоре. */
    private function offerData(array $overrides = [], string $key = 'offer-active'): array
    {
        return array_merge(
            collect(ClientEmails::find($key)['fields'])->map(fn ($f) => $f['default'] ?? '')->all(),
            ['client_name' => 'Мария', 'business' => 'Студио Лавандула', 'valid_until' => now()->addWeek()->toDateString()],
            $overrides
        );
    }

    private function render(string $key, array $overrides = []): string
    {
        return $this->actingAs($this->admin())->post("/admin/emails/$key/preview", ['data' => $this->offerData($overrides, $key)])->assertOk()->getContent();
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
            ->assertSee('Оферта: Стартов двигател')->assertSee('Оферта: Активен маркетинг и растеж')->assertSee('Оферта: Индивидуален маркетинг')
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
            ->assertSee('value="{business}: предложение за постоянен растеж | Creatium Lab"', false)
            ->assertSee('Какво включва')->assertSee('<details', false)
            ->assertSee('value="800"', false)->assertSee('value="650"', false)
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
            ->assertSee('Как Студио Лавандула ще расте с постоянен поток от запитвания')
            ->assertSee('Благодарим Ви за доверието и интереса към Creatium Lab!')
            ->assertSee('пакет „Активен маркетинг и растеж“', false)
            ->assertSee('€800', false)->assertSee('€650', false)->assertSee('/ месец')
            ->assertSee('Управление и проследяване на един рекламен канал')->assertSee('До 2 имейл кампании месечно')
            ->assertSee('€200 преди да подготвим офертата и демото и останалите €600 при съгласие')
            ->assertSee('€6 500 вместо €7 800')
            ->assertSee('Предложението е валидно до ' . now()->addWeek()->format('d.m.Y') . ' г.')
            ->assertSee('Поемаме ограничен брой нови проекти')->assertDontSee('безплатно демо')->assertDontSee('Демо преди старта');
        $this->actingAs($user)->post('/admin/emails/offer-start/preview', ['data' => ['business' => 'Мотор Про', 'price' => '400', 'monthly_price' => '200']])
            ->assertSee('Мотор Про')->assertDontSee('Предложението е валидно до')->assertDontSee('Поемаме ограничен брой');
        $this->actingAs($user)->post('/admin/emails/inquiry-reply/preview', ['data' => []])
            ->assertSee('С какво се отличава нашият подход')->assertSee('Стартов двигател')->assertSee('Индивидуален маркетинг')->assertDontSee('мащабиране')
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
                && $mail->hasSubject('Студио Лавандула: предложение за постоянен растеж | Creatium Lab')
                && str_contains($html, 'Здравейте, Мария')
                && str_contains($html, '€800')
                && str_contains($html, 'валидно до ' . now()->addWeek()->format('d.m.Y'))
                && ! str_contains($html, '{business}') && ! str_contains($html, '{client_name}')
                && ! str_contains(preg_replace('/<!--\[if mso\].*?<!\[endif\]-->/s', '', $html), '['); // no unfilled [поле], ignoring the Outlook-only font comment
        });

        $log = ClientEmail::sole();
        $this->assertSame('offer-active', $log->template);
        $this->assertSame('Студио Лавандула: предложение за постоянен растеж | Creatium Lab', $log->subject);
        $this->assertSame('До 8 съдържателни единици месечно', ClientEmails::lines($log->data['includes'])[3]);
        $this->assertSame(array_keys(ClientEmails::find('offer-active')['fields']), array_keys($log->data));
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

    public function test_helpers_fill_split_lines_and_pairs(): void
    {
        $this->assertSame('Мария, Лавандула', ClientEmails::fill('{client_name}, {business}', ['client_name' => 'Мария', 'business' => 'Лавандула']));
        $this->assertSame(['а', 'б: в'], ClientEmails::lines(" а \n\n  б: в \r\n"));
        $this->assertSame([['Заглавие', 'текст: още'], ['без двоеточие', '']], ClientEmails::pairs("Заглавие: текст: още\nбез двоеточие"));
        $this->assertSame([], ClientEmails::lines(null));
        $groups = ClientEmails::groups(ClientEmails::find('offer-start')['fields']);
        $this->assertSame(['Клиент', 'Начало', 'Какво включва', 'Защо сега', 'Пътят на клиента', 'Какво получавате', 'Цена и условия', 'Доверие и стъпки', 'Край'], array_keys($groups));
        $this->assertSame([''], array_keys(ClientEmails::groups(ClientEmails::find('demo-site')['fields'])));
    }

    public function test_business_placeholder_is_filled_everywhere_in_the_offer(): void
    {
        $html = $this->render('offer-start', ['intro' => 'Здравейте от {business}.', 'closing' => 'Пишете ни, {client_name}: {business}.', 'ps' => 'P.S. за {business}', 'trust' => 'Цена: за {business} е фиксирана.']);

        $this->assertStringContainsString('Как Студио Лавандула ще печели клиенти онлайн', $html);
        $this->assertStringContainsString('Здравейте от Студио Лавандула.', $html);
        $this->assertStringContainsString('Пишете ни, Мария: Студио Лавандула.', $html);
        $this->assertStringContainsString('P.S. за Студио Лавандула', $html);
        $this->assertStringContainsString('за Студио Лавандула е фиксирана.', $html);
        $this->assertStringNotContainsString('{business}', $html);
        $this->assertStringNotContainsString('{client_name}', $html);
        $this->assertStringContainsString('subject=' . rawurlencode('Одобрявам предложението: Студио Лавандула'), $html);
        $this->assertStringContainsString('Пакетът включва', $html);
    }

    public function test_empty_optional_fields_hide_their_blocks(): void
    {
        $full = $this->render('offer-start', ['heard' => 'Искат повече обаждания.', 'why_now' => 'Конкурентите вече са там.', 'outcomes' => 'Повече обаждания', 'ps' => 'Пишете ни.', 'extras' => "Допълнителна страница: €50\nЛого: €120"]);
        foreach (['Какво чухме от Вас', 'Искат повече обаждания.', 'Защо сега', 'Конкурентите вече са там.', 'Допълнително по желание', 'Допълнителна страница', 'Какво получавате', 'Повече обаждания', 'P.S.', 'Пакетът включва'] as $text) {
            $this->assertStringContainsString($text, $full);
        }

        // По подразбиране (писмата на собствениците) няма „Какво чухме“, „Защо сега“, „Какво получавате“ и P.S. при Стартов двигател.
        $default = $this->render('offer-start');
        foreach (['Какво чухме от Вас', 'Защо сега', 'Какво получавате', 'P.S.', 'Вашият път към клиента'] as $text) {
            $this->assertStringNotContainsString($text, $default);
        }

        $empty = $this->render('offer-start', ['heard' => '', 'why_now' => '', 'extras' => '', 'outcomes' => '', 'ps' => '', 'closing' => '', 'valid_until' => '', 'includes' => '', 'yearly_terms' => '']);
        foreach (['Какво чухме от Вас', 'Защо сега', 'Допълнително по желание', 'Какво получавате', 'P.S.', 'Ако имате въпроси', 'Предложението е валидно', 'Пакетът включва', 'Годишно'] as $text) {
            $this->assertStringNotContainsString($text, $empty);
        }
    }

    public function test_a_stage_without_items_is_hidden(): void
    {
        $html = $this->render('offer-start', ['stage1_items' => 'Профил в Google', 'stage3_items' => 'Бутон за обаждане', 'stage4_items' => 'Хостинг: Защита и архиви']);

        $this->assertStringContainsString('Вашият път към клиента', $html);
        $this->assertStringContainsString('Намират Ви', $html);
        $this->assertStringNotContainsString('Доверяват Ви се', $html);
        $this->assertStringContainsString('Пишат и купуват', $html);
        $this->assertStringContainsString('Всичко работи всеки ден', $html);

        $html = $this->render('offer-start', ['stage1_items' => '', 'stage2_items' => '', 'stage3_items' => '', 'stage4_items' => '']);
        $this->assertStringNotContainsString('Вашият път към клиента', $html);
    }

    public function test_the_subject_typed_in_the_form_is_used(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())->post('/admin/emails/offer-start', [
            'to' => 'maria@salon.bg', 'subject' => 'Нашата оферта за {business}', 'data' => $this->offerData(['valid_until' => ''], 'offer-start'),
        ])->assertRedirect('/admin/emails')->assertSessionHasNoErrors();

        Mail::assertSent(ClientTemplateMail::class, fn (ClientTemplateMail $mail) => $mail->hasSubject('Нашата оферта за Студио Лавандула'));
        $this->assertSame('Нашата оферта за Студио Лавандула', ClientEmail::sole()->subject);
    }

    public function test_use_again_prefills_the_saved_data_and_the_link_is_in_the_history(): void
    {
        Mail::fake();
        $user = $this->admin();
        $lead = $this->lead();
        $data = $this->offerData(['why_now' => 'Моят собствен текст за сега.', 'price' => '999', 'valid_until' => ''], 'offer-start');

        $this->actingAs($user)->post('/admin/emails/offer-start', [
            'to' => 'maria@salon.bg', 'subject' => 'Моя тема за {business}', 'project_id' => $lead->id, 'data' => $data,
        ])->assertRedirect('/admin/emails');
        $sent = ClientEmail::sole();

        $this->actingAs($user)->get('/admin/emails')
            ->assertSee('Използвай отново')
            ->assertSee('from=' . $sent->id, false)->assertSee('project=' . $lead->id, false);

        $this->actingAs($user)->get('/admin/emails/offer-start?from=' . $sent->id)->assertOk()
            ->assertSee('Моят собствен текст за сега.')->assertSee('value="999"', false)
            ->assertSee('value="Моя тема за Студио Лавандула"', false)
            ->assertSee('value="Студио Лавандула"', false);

        // Друг шаблон не взима чужди данни.
        $this->actingAs($user)->get('/admin/emails/offer-active?from=' . $sent->id)->assertOk()
            ->assertDontSee('Моят собствен текст за сега.')->assertSee('value="800"', false);
    }

    public function test_the_three_offers_carry_the_final_prices_deposit_and_the_owners_letters(): void
    {
        $start = $this->render('offer-start');
        foreach (['Благодарим Ви за отделеното време и възможността да работим върху развитието на Вашия бизнес!', 'пакет „Стартов двигател“', '€400', '€200', 'Изработка на уебсайт', 'Google Business Profile', 'Настройка на Google Analytics и Meta Pixel', 'До 4 съдържателни единици месечно', 'Базово управление на социалните канали', 'Месечен отчет',
            '€100 преди да подготвим офертата и демото и останалите €300 при съгласие', '€2 000 вместо €2 400', 'Ще се радваме да станем Ваш партньор в развитието на онлайн присъствието Ви!'] as $text) {
            $this->assertStringContainsString($text, $start);
        }
        $this->assertStringNotContainsString('безплатно демо', $start);
        $this->assertStringNotContainsString('Демо преди старта', $start);

        $individual = $this->render('offer-individual');
        foreach (['Благодарим Ви за проведената среща', 'индивидуално предложение за маркетингово партньорство', 'Съгласно индивидуално изготвената оферта', 'от €1 200', 'Приоритетна комуникация', 'CRM интеграции', 'Партньорството може да включва', 'Надяваме се това да бъде началото на успешно и дългосрочно партньорство!', 'Екипът на Creatium Lab', config('creatium.contact.email')] as $text) {
            $this->assertStringContainsString($text, $individual);
        }
        // Без €-префикс пред текстовата цена, без гаранция за трафик и без срок на валидност от подразбиране.
        $this->assertStringNotContainsString('€Съгласно', $individual);
        $this->assertStringNotContainsString('60 дни', $individual);

        $template = ClientEmails::find('offer-individual');
        $this->assertSame('individual', $template['package']);
        $this->assertSame('Индивидуален маркетинг', ClientEmails::PACKAGES['individual']);
        $this->actingAs($this->admin())->get('/admin/emails/offer-individual')->assertOk()
            ->assertDontSee('value="' . now()->addDays(7)->toDateString() . '"', false);
    }

    public function test_the_start_offer_has_no_valid_until_prefill_and_active_does(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->get('/admin/emails/offer-start')->assertOk()->assertDontSee('value="' . now()->addDays(7)->toDateString() . '"', false);
        $this->actingAs($user)->get('/admin/emails/offer-active')->assertOk()->assertSee('value="' . now()->addDays(7)->toDateString() . '"', false);
    }

    public function test_every_email_uses_one_font(): void
    {
        $html = view('emails.client.offer', ['data' => ['client_name' => 'Ивон', 'business' => 'Pet Bowls', 'price' => '800'], 'package' => 'active', 'subject' => 'x'])->render();
        $this->assertStringContainsString('font-family: Arial, Helvetica, sans-serif !important;', $html);
        $this->assertStringContainsString('<!--[if mso]>', $html);
        preg_match_all('/font-family:\s*([^;"]+)/', $html, $m);
        foreach ($m[1] as $stack) {
            $this->assertMatchesRegularExpression('/^Arial,\s*Helvetica,\s*sans-serif/', trim(str_replace(' !important', '', $stack)));
        }
        // The plain-text part does not get the CSS.
        $this->assertStringNotContainsString('font-family', \App\Support\PlainText::fromHtml($html));
    }
}
