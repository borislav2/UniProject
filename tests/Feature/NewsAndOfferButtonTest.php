<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\HeroOfferButton2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsAndOfferButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_hero_button_goes_to_the_quote_form(): void
    {
        $this->get('/')->assertOk()->assertSee('Получете оферта')->assertSee(lroute('packages') . '#oferta', false);
    }

    public function test_old_admin_button_text_is_replaced_but_a_custom_one_stays(): void
    {
        ContentBlock::create(['key' => 'home_hero', 'locale' => 'bg', 'value' => ['title' => 'Моето заглавие', 'button' => 'Изчислете ползата за бизнеса си']]);
        ContentBlock::create(['key' => 'home_hero', 'locale' => 'en', 'value' => ['title' => 'Mine', 'button' => 'Talk to us']]);

        $this->assertSame(['home_hero/bg'], HeroOfferButton2026::apply());
        $bg = ContentBlock::where(['key' => 'home_hero', 'locale' => 'bg'])->first()->value;
        $this->assertSame('Получете оферта', $bg['button']);
        $this->assertSame('Моето заглавие', $bg['title']);
        $this->assertSame('Talk to us', ContentBlock::where(['key' => 'home_hero', 'locale' => 'en'])->first()->value['button']);
    }

    public function test_blog_is_called_news(): void
    {
        $this->get('/blog')->assertOk()->assertSee('Новини')->assertDontSee('>Блог<', false);
    }
}
