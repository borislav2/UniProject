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

    public function test_contact_form_preselects_the_service_from_the_link(): void
    {
        $this->assertMatchesRegularExpression('/<option value="support"[^>]*\sselected/', $this->get('/kontakti?service=support')->getContent());
        $this->assertDoesNotMatchRegularExpression('/<option value="[a-z-]+"[^>]*\sselected/', $this->get('/kontakti?service=nonsense')->getContent());
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
            ->assertSeeInOrder([
                'Сайт, който продава. Маркетинг, който докарва клиенти.',
                'Целият ви дигитален двигател – в една работеща система.',
                'Планирайте консултация', 'Ново запитване',
                'Защо фрагментираните дигитални услуги пилеят ресурси?', 'Сценарий А', 'Сценарий Б', 'Решението',
                'Компонентите на вашия дигитален двигател', 'Технологична инфраструктура', 'Маркетинг, аналитика и позициониране',
                'Управляем и структуриран път от концепцията до пазарния старт', 'Стратегически анализ', 'Регулярна поддръжка и надграждане',
                'Готови ли сте за дигитална система, която работи за вашите продажби?', 'За директна връзка',
            ])
            ->assertSee('href="#konsultatsiya"', false)
            ->assertSee('id="konsultatsiya"', false)
            ->assertDontSee('Стандарти на работа')
            ->assertDontSee('Бъди разпознаваем.')
            ->assertDontSee('Подходящо за');
    }

    public function test_packages_page_shows_the_three_packages_with_prices(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertSee('Вие не купувате просто „уебсайт“.')
            ->assertSeeInOrder(['Стартов двигател', '€450', '€150 / месец', 'Активен Маркетинг &amp; Растеж', 'Най-предпочитан', '€850', '€400 / месец', 'Индивидуална Маркетинг Мащабируемост'], false)
            ->assertSee('Поискайте оферта за този пакет');
        $this->get('/en/packages')->assertOk()->assertSee('Starter Engine')->assertSee('Request a quote for this package');
    }

    public function test_contact_form_asks_for_project_status_and_email(): void
    {
        $this->get('/kontakti')
            ->assertSee('Име и фамилия')
            ->assertSee('Телефон за връзка')
            ->assertSee('name="email"', false)
            ->assertSee('Настоящ статус на проекта')
            ->assertSee('Изграждане на нова дигитална система (Уебсайт + Маркетинг)')
            ->assertSee('Модернизация на съществуващ сайт и маркетингова оптимизация')
            ->assertSee('Техническа поддръжка, аналитика и дигитално развитие')
            ->assertSee('Описание на бизнес целите')
            ->assertSee('Стартирайте вашия проект')
            ->assertDontSee('Размер на бизнеса');
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
            ->assertSee('Performance Marketing Expert')
            ->assertSee('Laravel')
            ->assertSee('href="https://www.linkedin.com/in/vladimirtsonchev/"', false);

        $this->get('/en/about')->assertSee('Borislav Kostadinov')->assertSee('LinkedIn profile')->assertSee('Performance Marketing Expert');
    }

    public function test_faq_lives_on_the_about_page(): void
    {
        $this->get('/za-nas')->assertSee('Често задавани въпроси')
            ->assertSeeInOrder(['Как се определя инвестицията за проекта?', 'Каква е ролята на месечния абонамент?', 'Как протича процесът на плащане?']);
        $this->get('/en/about')->assertSee('Frequently asked questions')->assertSee('How is the project budget set?');
        $this->get('/uslugi')->assertDontSee('Как се определя инвестицията');
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

        $this->get('/poveritelnost')->assertSee('ЕИК 123456789')->assertSee('адрес: гр. София, ул. Примерна 1');
        $this->get('/en/privacy')->assertSee('company ID (EIK) 123456789');
    }

    public function test_packages_page_links_to_contact(): void
    {
        $this->get('/paketi')->assertOk()->assertSee('Поискайте оферта за този пакет')->assertSee(route('contact') . '#contact-form', false);
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
        $this->post('/kontakti', [])->assertSessionHasErrors(['name', 'phone', 'email', 'service', 'consent']);
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
}
