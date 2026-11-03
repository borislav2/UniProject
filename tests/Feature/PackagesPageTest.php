<?php

namespace Tests\Feature;

use App\Support\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_names_trust_strip_year_totals_and_compare_table(): void
    {
        $this->get('/paketi')->assertOk()
            ->assertSee('Активен маркетинг и растеж')
            ->assertSee('Индивидуален маркетинг и мащабиране')
            ->assertSee('Препоръчваме')
            ->assertDontSee('Най-предпочитан')
            ->assertSee('Плащате на две части')->assertSee('50% при договор, 50% след одобрено демо')
            ->assertSee('Фиксирана цена в офертата')
            ->assertSee('Общо за първата година: €2 250 (€450 + 12 × €150)')
            ->assertSee('Общо за първата година: €5 250 (€850 + 11 × €400)')
            ->assertSee('Какво включва всеки пакет')
            ->assertSee('Онлайн плащания и резервации')
            ->assertSee('Как започваме')
            ->assertSee('Заявете разговор')
            ->assertSee('Предпочитате разговор?')
            ->assertSee('/kontakti#konsultatsiya', false);
        $this->get('/en/packages')->assertOk()
            ->assertSee('Active Marketing and Growth')->assertSee('Total for the first year: €5,250');
    }

    public function test_faq_json_ld_is_valid(): void
    {
        $html = $this->get('/paketi')->assertOk()->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $faq = collect($m[1])->map(fn ($j) => json_decode($j, true))->firstWhere('@type', 'FAQPage');
        $this->assertNotNull($faq);
        $this->assertSame('https://schema.org', $faq['@context']);
        $this->assertNotEmpty($faq['mainEntity']);
        $this->assertStringContainsString('Цената е фиксирана', json_encode($faq, JSON_UNESCAPED_UNICODE));
    }

    public function test_demo_package_lookup_accepts_old_and_new_names(): void
    {
        $items = config('creatium.plans.items');
        foreach (['Активен маркетинг и растеж', 'Активен Маркетинг & Растеж', ' активен  маркетинг и растеж '] as $name) {
            $this->assertSame($items[1]['name'], Plans::find($items, $name)['name'], $name);
        }
        $this->assertNull(Plans::find($items, null));
        $this->assertNull(Plans::find($items, 'Няма такъв'));
    }

    public function test_new_sections_hide_when_their_keys_are_empty(): void
    {
        $plans = config('creatium.plans');
        foreach (['trust', 'compare', 'steps', 'terms', 'faq_groups', 'faq_questions'] as $key) {
            $plans[$key] = [];
        }
        $plans['items'][0]['year_total'] = '';
        config(['creatium.plans' => $plans]);

        $this->get('/paketi')->assertOk()
            ->assertDontSee('Какво включва всеки пакет')
            ->assertDontSee('Как започваме')
            ->assertDontSee('Общо за първата година: €2 250');
    }
}
