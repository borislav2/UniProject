<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_service_has_a_page_in_both_languages_with_faq_schema(): void
    {
        foreach (config('creatium.service_pages') as $key => $page) {
            $en = config("creatium_en.service_pages.$key");

            $this->get('/uslugi/' . $page['slug'])->assertOk()
                ->assertSee($page['title'])
                ->assertSee('<title>' . e($page['seo_title']) . '</title>', false)
                ->assertSee('hreflang="en" href="' . url('/en/services/' . $en['slug']) . '"', false)
                ->assertSee('"@type":"FAQPage"', false)
                ->assertSee($page['faq'][0][0])
                ->assertSee('Безплатна консултация');

            $this->get('/en/services/' . $en['slug'])->assertOk()->assertSee($en['title'])->assertSee('Free consultation');
        }

        $this->get('/uslugi/nyama-takava')->assertNotFound();
        $this->get('/en/services/izrabotka-na-sait')->assertNotFound();
    }

    public function test_the_google_profile_page_names_its_package_and_the_overview_links_every_page(): void
    {
        $this->get('/uslugi/google-profil-i-otzivi')->assertOk()
            ->assertSee('Google профил и отзиви')
            ->assertSee('Включено в пакет')
            ->assertSee('Активен Маркетинг &amp; Растеж', false);

        $overview = $this->get('/uslugi')->assertOk();
        foreach (config('creatium.service_pages') as $page) {
            $overview->assertSee('href="' . url('/uslugi/' . $page['slug']) . '"', false);
        }
    }

    public function test_menu_groups_services_and_about_and_the_sitemap_lists_service_pages(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('data-dropdown', false)
            ->assertSeeInOrder(['Услуги', 'Изработка на сайт', 'Google профил и отзиви', 'Безплатна проверка на сайт', 'Пакети', 'Проекти', 'За нас', 'Кои сме', 'Често задавани въпроси', 'Контакти', 'Безплатна консултация'])
            ->assertSee('href="' . url('/kontakti') . '#konsultatsiya"', false)
            ->assertSee('href="' . url('/vaprosi') . '"', false);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('<loc>' . url('/uslugi/izrabotka-na-sait') . '</loc>', false)
            ->assertSee('<loc>' . url('/en/services/google-business-profile') . '</loc>', false);
    }

    public function test_packages_show_the_short_list_and_keep_every_detail_behind_a_toggle(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertSee('Вижте подробно какво включва')
            ->assertSee('Google профил и отзиви')
            // Пълният текст на всяка точка е запазен.
            ->assertSee('Модерен UI/UX дизайн, оптимизиран за мобилни устройства и незабавно зареждане.')
            ->assertSee('Не сте сигурни кой пакет ви трябва?');
    }
}
