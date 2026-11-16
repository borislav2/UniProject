<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Design G, fourth round (17.11.2026): team first on /za-nas, an industry link on every project card, the contact heading, no decorative rules. */
class GAudit4Test extends TestCase
{
    use RefreshDatabase;

    public function test_about_shows_the_team_before_method_and_standards(): void
    {
        config(['creatium.design' => 'g']);
        $html = $this->get('/za-nas')->assertOk()->getContent();
        $team = strpos($html, 'id="ekip"');
        $this->assertNotFalse($team);
        $this->assertLessThan(strpos($html, 'Как работим'), $team);
        $this->assertLessThan(strpos($html, 'Защо го правим по този начин'), strpos($html, 'Как работим'));
    }

    public function test_every_project_card_has_an_industry_link_on_home_and_portfolio(): void
    {
        config(['creatium.design' => 'g']);
        foreach (['/', '/proekti'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('Изработка на сайт за ресторант', $html);
            $this->assertStringContainsString('/izrabotka-na-sait-za-restorant', $html);
        }
        $html = $this->get('/proekti')->getContent();
        $this->assertStringContainsString('Изработка на сайт за къща за гости', $html);
        $this->assertStringContainsString('Изработка на сайт за фитнес', $html);
        $this->assertStringContainsString('Изработка на сайт за адвокатска кантора', $html);
        $this->assertStringContainsString('/uslugi/izrabotka-na-sait', $html);
    }

    public function test_contact_heading_has_an_exclamation_mark_in_both_languages(): void
    {
        config(['creatium.design' => 'g']);
        $this->get('/kontakti')->assertOk()->assertSee('Обсъдете проекта си с нас!');
        $this->get('/en/contact')->assertOk()->assertSee('Discuss your project with us!');
    }

    public function test_decorative_rules_are_gone_from_the_g_css(): void
    {
        $css = file_get_contents(resource_path('css/g.css')) . file_get_contents(resource_path('css/g-pages.css'));
        foreach (['.g-hero { position: relative; border-bottom', 'section.g-s { padding-block: var(--g-pad); border-bottom', '.g-pr { display: flex; flex-wrap: wrap; gap: 8px 40px; margin-top: var(--g-gap); padding-top: 24px; border-top', '.g-fbox { border:'] as $old) {
            $this->assertStringNotContainsString($old, $css);
        }
    }
}
