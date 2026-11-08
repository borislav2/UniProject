<?php

namespace Tests\Feature;

use App\Mail\NewLeadMail;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Иван Иванов',
            'phone' => '+359 888 123 456',
            'email' => 'ivan@example.bg',
            'service' => 'new-system',
            'message' => 'Искам сайт за моя ресторант.',
            'consent' => '1',
        ], $overrides);
    }

    public function test_public_pages_load(): void
    {
        foreach (['/', '/uslugi', '/paketi', '/proekti', '/za-nas', '/kontakti', '/poveritelnost', '/usloviya', '/biskvitki', '/sitemap.xml', '/robots.txt'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_old_urls_redirect_permanently(): void
    {
        $this->get('/about')->assertStatus(301)->assertRedirect('/za-nas');
        $this->get('/contact')->assertStatus(301)->assertRedirect('/kontakti');
    }

    public function test_unknown_page_shows_404(): void
    {
        $this->get('/nyama-takava-stranica')->assertNotFound()->assertSee('Страницата не е намерена');
    }

    public function test_public_registration_is_gone(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'x', 'email' => 'x@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertNotFound();
    }

    public function test_robots_blocks_admin_and_lists_sitemap(): void
    {
        $this->get('/robots.txt')->assertSee('Disallow: /admin')->assertSee('/sitemap.xml');
    }

    public function test_portfolio_only_shows_public_completed_projects(): void
    {
        $category = Category::create(['name' => 'Ресторанти']);
        $base = ['description' => 'd', 'start_date' => '2026-01-01', 'manager' => 'm', 'category_id' => $category->id];

        Project::create($base + ['name' => 'Видим проект', 'status' => 'Completed', 'is_public' => true]);
        Project::create($base + ['name' => 'Скрит проект', 'status' => 'Completed', 'is_public' => false]);
        Project::create($base + ['name' => 'Незавършен проект', 'status' => 'In Progress', 'is_public' => true]);

        $this->get('/proekti')->assertSee('Видим проект')->assertDontSee('Скрит проект')->assertDontSee('Незавършен проект');
    }

    public function test_empty_portfolio_shows_coming_soon_when_there_are_no_demos(): void
    {
        config(['creatium.demos' => []]);

        $this->get('/proekti')->assertSee('Тук скоро ще има проекти');
    }

    public function test_services_page_shows_the_four_components_and_one_call_to_action(): void
    {
        $this->get('/uslugi')
            ->assertSee('Архитектурата на вашия дигитален успех')
            ->assertSeeInOrder(['Уеб платформа, проектирана да продава', 'GEO, SEO и позициониране в AI', 'Автоматизация на продажбите', 'Техническа грижа и сигурност 24/7'])
            ->assertSee('Какво получавате')
            ->assertSee('Имате нужда от цялостната система или от отделен компонент?')
            ->assertSee('Обсъдете проекта си с нас')
            ->assertDontSee('?service=', false);

        $this->get('/en/services')->assertSee('A web platform designed to sell')->assertSee('Discuss your project with us');
    }

    public function test_contact_form_preselects_the_interest_from_the_link(): void
    {
        $this->assertMatchesRegularExpression('/name="interests\[\]" value="ads"[^>]*\schecked/', $this->get('/kontakti?interest=ads')->getContent());
        $this->assertDoesNotMatchRegularExpression('/name="interests\[\]" value="[a-z-]+"[^>]*\schecked/', $this->get('/kontakti?interest=nonsense')->getContent());
    }

    public function test_public_project_links_to_the_client_site(): void
    {
        $category = Category::create(['name' => 'Уебсайт']);
        Project::create([
            'name' => 'Сайт с адрес', 'description' => 'Описание.', 'start_date' => now(), 'end_date' => now(), 'status' => 'Completed',
            'manager' => 'Екип', 'category_id' => $category->id, 'is_public' => true, 'website_url' => 'https://www.example.bg/',
        ]);

        $this->get('/proekti')->assertSee('href="https://www.example.bg/"', false)->assertSee('Посетете сайта: example.bg');
        $this->get('/en/projects')->assertSee('Visit the site: example.bg');
    }

    public function test_home_page_follows_the_spec(): void
    {
        $this->get('/')
            ->assertSeeTextInOrder([
                'Дигитални решения, изградени с мисъл за вашия успех.',
                'Персонализирани сайтове. Брандиран маркетинг. Стратегически решения за повече клиенти.',
                'Получете оферта', 'Нова резервация',
                'Цялостен подход към дигиталното ви присъствие', 'Принципите, по които ви напътстваме', 'Стратегическа видимост', 'Всичко, което клиентът вижда за вас, е изградено като едно.', 'Брандиране', 'Уеб изработка', 'Маркетинг', 'Успешен бизнес',
                'Анатомия на вашия дигитален двигател', 'Двигателят работи', '1 · Откриване', 'Ако спре', 'Подава ги на',
                'Нашите ценности', 'Принципите, които гарантират реалния растеж на вашия бизнес',
                'Как протича процесът', 'Шест етапа от анализа до постоянната оптимизация', 'Стратегически анализ', 'Поддръжка и развитие',
                'Готови ли сте за дигитална система, която работи за вашите продажби?', 'За директна връзка',
            ])
            ->assertSee('<span class="text-accent">успех</span>', false)
            ->assertSeeTextInOrder(['Партньор, който ви води стъпка по стъпка', 'Лесно управление и пълна поддръжка', '01 / Решението', '02 / Дигиталният двигател', '03 / Нашите ценности', '04 / Браншове', '05 / Работен процес'])
            ->assertDontSee('Готова система, не визитка')
            ->assertDontSee('отзива в Google')
            ->assertSee('href="' . url('/paketi') . '#oferta"', false)
            ->assertSee('id="konsultatsiya"', false)
            ->assertDontSee('Стандарти на работа')
            ->assertDontSee('Бъди разпознаваем.')
            ->assertDontSee('Подходящо за');
    }

    public function test_packages_page_shows_the_three_packages_with_prices(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertSee('Три начина да работим заедно')
            ->assertSeeInOrder(['Стартов двигател', 'от €400', 'еднократно за изграждането и първия месец', 'на година', 'Активен маркетинг и растеж', 'от €800', 'на година', 'Индивидуален маркетинг', 'от €1', 'Индивидуална оферта'], false)
            ->assertSee('Получете оферта');
        $this->get('/en/packages')->assertOk()->assertSee('Starter Engine')->assertSee('Get a quote');
    }

    public function test_contact_form_is_one_simple_form(): void
    {
        $this->get('/kontakti')
            ->assertSee('Свържете се с нас')
            ->assertSee('Име и фамилия')
            ->assertSee('вашсайт.bg, ако имате')
            ->assertSee('От какво се интересувате')
            ->assertSee('Изработка на сайт')
            ->assertSee('Реклами в Google и Meta')
            ->assertSee('Разкажете ни накратко за бизнеса си и какво търсите')
            ->assertSee('Искате ли да запазите час за разговор? (по желание)')
            ->assertSeeInOrder(['name="website"', 'name="name"', 'name="site_url"', 'name="email"', 'name="phone"', 'name="interests[]"', 'name="message"', '<details', 'id="konsultatsiya"', '</details>', 'name="consent"', 'Изпратете'], false)
            ->assertDontSee('data-step', false)
            ->assertDontSee('Напред')
            ->assertDontSee('name="service"', false)
            ->assertDontSee('name="goals[]"', false)
            ->assertDontSee('name="timeline"', false);

        $this->get('/en/contact')->assertSee('What are you interested in')->assertSee('Website design')->assertSee('Send');
    }

    public function test_booking_details_open_when_a_slot_was_chosen(): void
    {
        $this->get('/kontakti')->assertDontSee('data-booking-details open', false);
        $this->withSession(['_old_input' => ['consultation' => '2026-12-01 10:00']])
            ->get('/kontakti')->assertSee('data-booking-details open', false);
    }

    public function test_interests_are_saved_as_the_service_and_in_the_description(): void
    {
        Mail::fake();

        $payload = $this->payload(['interests' => ['website', 'ads'], 'site_url' => 'motorpro.bg']);
        unset($payload['service']);
        $this->post('/en/contact', $payload)->assertSessionHasNoErrors()->assertSessionHas('lead_service', 'Изработка на сайт, Реклами в Google и Meta');

        $lead = Project::where('source', 'website')->firstOrFail();
        $this->assertSame('Изработка на сайт, Реклами в Google и Meta', $lead->service);
        $this->assertSame("Искам сайт за моя ресторант.\n\nИнтересува се от Изработка на сайт, Реклами в Google и Meta.\nСегашен сайт motorpro.bg", $lead->description);
        $this->assertSame('https://motorpro.bg', $lead->website_url);
        $this->assertStringNotContainsString(':', $lead->service);
        Mail::assertSent(NewLeadMail::class);

        $this->post('/kontakti', $this->payload(['interests' => ['nonsense']]))->assertSessionHasErrors('interests.0');
        $this->post('/kontakti', $this->payload(['message' => '', 'site_url' => 'не е адрес']))->assertSessionHasNoErrors();
        $this->assertNull(Project::where('source', 'website')->latest('id')->first()->website_url);
    }

    public function test_website_interests_and_message_are_optional(): void
    {
        Mail::fake();

        $this->post('/kontakti', ['name' => 'Мария', 'phone' => '0888123456', 'email' => 'm@example.bg', 'consent' => '1'])
            ->assertSessionHasNoErrors()->assertSessionHas('lead_service', 'Запитване от сайта');

        $lead = Project::where('source', 'website')->firstOrFail();
        $this->assertSame('Запитване от сайта', $lead->service);
        $this->assertSame('Без съобщение. Запитване от сайта.', $lead->description);
        $this->assertNull($lead->website_url);
    }

    public function test_email_is_saved_on_the_lead(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload(['email' => 'Ivan@Example.bg']))->assertSessionHasNoErrors();
        $this->assertSame('ivan@example.bg', Project::where('source', 'website')->firstOrFail()->client_email);

        $this->post('/kontakti', $this->payload(['email' => 'не е имейл']))->assertSessionHasErrors('email');
    }

    public function test_about_page_introduces_the_team_with_linkedin_links(): void
    {
        $this->get('/za-nas')->assertDontSee('Двама души, които правят сайтове');
        $this->get('/za-nas')->assertDontSee('Какво можете да очаквате');
        $this->get('/en/about')->assertDontSee('What you can expect');
        $this->get('/en/about')->assertDontSee('Two people who build websites');

        $this->get('/za-nas')
            ->assertSee('Борислав Костадинов')
            ->assertSee('Владимир Цончев')
            ->assertSee('Да ви намират в Google')
            ->assertSee('Сайт, който се зарежда бързо')
            ->assertSee('href="https://www.linkedin.com/in/vladimirtsonchev/"', false);

        $this->get('/en/about')->assertSee('Borislav Kostadinov')->assertSee('LinkedIn profile')->assertSee('Being found on Google');
    }

    public function test_faq_has_its_own_page_grouped_by_topic(): void
    {
        $this->get('/vaprosi')->assertOk()
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSeeInOrder(['Цени и плащане', 'Как се определя цената на проекта?', 'Включени ли са хостингът и домейнът?',
                'Изработка и срокове', 'Ще видя ли сайта, преди да бъде пуснат?', 'Google, реклама и AI', 'Гарантирате ли първо място в Google?',
                'След пускането', 'Колко бързо отговаряте?'])
            ->assertSee('href="#ceni-i-plashtane"', false);
        $this->get('/en/faq')->assertOk()->assertSee('Frequently asked questions')->assertSee('How is the project budget set?')->assertSee('Prices and payment');
        $this->get('/za-nas')->assertSee('href="' . url('/vaprosi') . '"', false)->assertDontSee('Как се определя цената на проекта');
        $this->get('/sitemap.xml')->assertSee(url('/vaprosi'))->assertSee(url('/en/faq'));
    }

    public function test_privacy_page_shows_no_placeholder_company_details(): void
    {
        foreach (['/poveritelnost', '/en/privacy'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('[ЕИК]')->assertDontSee('[адрес на управление]')->assertDontSee('ЕИК ,')->assertDontSee('адрес:');
        }
        $this->get('/poveritelnost')->assertSee('Администратор на лични данни е')->assertSee('Creatium Lab</strong>. За въпроси', false);
    }

    public function test_privacy_page_shows_eik_and_address_once_they_are_set(): void
    {
        config(['creatium.legal.eik' => '123456789', 'creatium.legal.address' => 'гр. София, ул. Примерна 1']);

        $this->get('/poveritelnost')->assertSee('ЕИК 123456789')->assertSee('адрес гр. София, ул. Примерна 1');
        $this->get('/en/privacy')->assertSee('company ID (EIK) 123456789');
    }

    public function test_packages_page_links_to_the_quote_form(): void
    {
        $this->get('/paketi')->assertOk()->assertSee('Получете оферта')->assertSee(route('packages') . '?paket=0#oferta', false);
    }

    public function test_sitemap_lists_new_pages(): void
    {
        $this->get('/sitemap.xml')->assertSee(route('packages'))->assertSee(route('cookies'));
    }

    public function test_contact_form_creates_lead_and_notifies_team(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload())->assertRedirect()->assertSessionHas('success');

        $lead = Project::where('source', 'website')->firstOrFail();
        $this->assertSame('Запитване от Иван Иванов', $lead->name);
        $this->assertSame('+359 888 123 456', $lead->client_phone);
        $this->assertSame('Изграждане на нова дигитална система (Уебсайт + Маркетинг)', $lead->service);
        $this->assertSame('ivan@example.bg', $lead->client_email);
        $this->assertNull($lead->consultation_at);
        $this->assertSame('Искам сайт за моя ресторант.', $lead->description);
        $this->assertSame('Planning', $lead->status);
        $this->assertFalse($lead->is_public);

        Mail::assertSent(NewLeadMail::class);
    }

    public function test_message_is_optional(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload(['message' => '']))->assertSessionHasNoErrors();

        $lead = Project::where('source', 'website')->firstOrFail();
        $this->assertStringContainsString('Изграждане на нова дигитална система', $lead->description);
    }

    public function test_contact_form_validation(): void
    {
        $this->post('/kontakti', [])->assertSessionHasErrors(['name', 'phone', 'email', 'consent'])->assertSessionDoesntHaveErrors(['service', 'interests', 'site_url', 'message']);
        $this->post('/kontakti', $this->payload(['phone' => 'abc']))->assertSessionHasErrors('phone');
        $this->post('/kontakti', $this->payload(['service' => 'Нещо друго']))->assertSessionHasErrors('service');
        $this->post('/kontakti', $this->payload(['consent' => null]))->assertSessionHasErrors('consent');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_honeypot_drops_spam_silently(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload(['website' => 'http://spam.example']))->assertSessionHas('success');

        $this->assertDatabaseCount('projects', 0);
        Mail::assertNothingSent();
    }

    public function test_mail_failure_does_not_lose_the_lead(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->post('/kontakti', $this->payload())->assertSessionHas('success');

        $this->assertDatabaseCount('projects', 1);
        $this->assertNull(Project::first()->email_sent_at);
    }

    public function test_contact_form_notifies_hello_and_records_that_the_email_was_sent(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload())->assertSessionHas('success');

        Mail::assertSent(NewLeadMail::class, fn ($mail) => $mail->hasTo('info@creatiumlab.com'));
        $this->assertNotNull(Project::first()->email_sent_at);
    }

    public function test_contact_form_is_rate_limited(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/kontakti', $this->payload())->assertSessionHasNoErrors();
        }

        $this->post('/kontakti', $this->payload())->assertStatus(429);
    }

    public function test_home_page_shows_the_google_rating_only_when_there_are_reviews_and_the_latest_articles(): void
    {
        config(['creatium.google_reviews' => ['rating' => 4.9, 'count' => 12, 'url' => 'https://g.page/r/example'], 'creatium.blog_enabled' => true]);
        \App\Models\Post::create(['title' => ['bg' => 'Първа статия'], 'slug' => ['bg' => 'parva-statiya'], 'body' => ['bg' => 'Текст.'], 'status' => 'published', 'published_at' => now()->subDay()]);

        $this->get('/')->assertOk()
            ->assertSeeText('4,9')->assertSeeText('12 отзива в Google')->assertSee('https://g.page/r/example', false)
            ->assertSeeTextInOrder(['05 / Работен процес', '06 / Анализи и съвети', 'Първа статия', '07 / Следващата стъпка']);
        $this->get('/en')->assertOk()->assertSeeText('12 Google reviews')->assertDontSeeText('Първа статия');
    }

    public function test_presentation_page_and_pdf_download(): void
    {
        $this->get('/prezentatsiya')->assertOk()->assertSee('noindex', false)
            ->assertSeeText('Работим за растежа на вашия бизнес.')->assertSeeText('Стартов двигател')->assertSeeText('creatiumlab.com');
        $this->get('/en/presentation')->assertOk()->assertSeeText('Starter Engine');

        $this->assertFileExists(public_path('files/creatium-lab-prezentatsiya.pdf'));
        $this->assertFileExists(public_path('files/creatium-lab-presentation.pdf'));
        $this->get('/')->assertSee('files/creatium-lab-prezentatsiya.pdf', false);
        $this->get('/en')->assertSee('files/creatium-lab-presentation.pdf', false);
    }
}
