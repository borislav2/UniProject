<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroNetworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_hero_has_the_six_node_network_and_the_wave(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('data-hero-network', false)
            ->assertSee('data-hero-visual', false)
            ->assertSee(e(json_encode(['Бранд', 'Сайт', 'Google', 'Реклами', 'Имейли', 'Грижа'], JSON_UNESCAPED_UNICODE)), false)
            ->assertSee('fill-white dark:fill-ink-950', false);

        $this->get('/en')->assertOk()->assertSee(e(json_encode(['Brand', 'Website', 'Google', 'Ads', 'Emails', 'Care'])), false);
    }

    public function test_empty_node_list_hides_the_network(): void
    {
        config(['creatium.home_trust.network_nodes' => []]);

        $this->get('/')->assertOk()->assertDontSee('data-hero-network', false);
    }
}
