<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndustryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_industry_page_renders_with_its_demo_seo_and_faq(): void
    {
        $demos = collect(config('creatium.demos'))->keyBy('slug');

        foreach (config('creatium.industry_pages') as $slug => $page) {
            $demo = $demos[$page['demo']];

            $this->get("/izrabotka-na-sait-za-$slug")->assertOk()
                ->assertSee('<title>' . e($page['seo_title']) . '</title>', false)
                ->assertSee('<meta name="description" content="' . e($page['seo_description']) . '">', false)
                ->assertSee($page['title'])
                ->assertSee($page['must_have'][0][0])
                ->assertSee($demo['problems'][0][0])
                ->assertSee(route('demo.show', $demo['slug']), false)
                ->assertSee('"@type":"FAQPage"', false)
                ->assertSee('"position":2,"name":"' . config('creatium.service_pages.website.menu') . '"', false)
                ->assertSee('<link rel="canonical" href="' . url("/izrabotka-na-sait-za-$slug") . '">', false);
        }

        $this->get('/izrabotka-na-sait-za-restorant')->assertSee('Стартов двигател')->assertSee('Изработка на сайт за автосервиз');
        $this->get('/izrabotka-na-sait-za-onlain-magazin')->assertSee('Индивидуална оферта');
        $this->get('/izrabotka-na-sait-za-nyama-takav')->assertNotFound();
    }

    public function test_pages_are_linked_and_listed_only_in_bulgarian(): void
    {
        $url = url('/izrabotka-na-sait-za-avtoservis');

        $this->get('/sitemap.xml')->assertSee('<loc>' . $url . '</loc>', false);
        $this->get('/llms.txt')->assertSee('[Изработка на сайт за автосервиз](' . $url . ')', false);
        $this->get('/')->assertSee('href="' . $url . '"', false);
        $this->get('/proekti')->assertSee('href="' . $url . '"', false);
        $this->get('/uslugi/' . config('creatium.service_pages.website.slug'))->assertSee('href="' . $url . '"', false);

        $this->get('/en')->assertDontSee('izrabotka-na-sait-za-', false);
        $this->get('/en/projects')->assertDontSee('izrabotka-na-sait-za-', false);
    }
}
