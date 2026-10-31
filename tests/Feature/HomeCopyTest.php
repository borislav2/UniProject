<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_copy_from_the_owners(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Рекламните кодове са настроени')
            ->assertSee('Ние сме с вас по пътя на реализирането')
            ->assertSee('Правим това от 4 години.')
            ->assertSee('Направихме сайтове за бизнеси от различни браншове.')
            ->assertDontSee('Без посредници')
            ->assertDontSee('без жаргон')
            ->assertDontSee('малки бизнеси');
    }

    public function test_about_page_tells_the_story_even_when_the_admin_saved_an_older_about_block(): void
    {
        ContentBlock::create(['key' => 'about', 'locale' => 'bg', 'value' => ['subtitle' => '', 'paragraphs' => ['Нашият текст от админа.']]]);

        $this->get('/za-nas')->assertOk()
            ->assertSeeInOrder(['Кои сме ние', 'Нашият текст от админа.', 'Защо го правим', 'От 4 години правим сайтове и дигитален маркетинг.', 'Екипът'])
            ->assertDontSee('Говорите с хората, които правят работата.');
        $this->get('/en/about')->assertOk()->assertSee('Why we do it')->assertSee('for 4 years');
    }
}
