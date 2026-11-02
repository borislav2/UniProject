<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Models\Post;
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
            ->assertSee('Дигитални решения, които движат бизнеса ви')
            ->assertSee('Персонализирани сайтове. Брандиран маркетинг.')
            ->assertSee('Поемаме проекти, в които можем да гарантираме директно отражение върху растежа на бизнеса.')
            ->assertSee('Сайт и маркетинг, които работят като едно.')
            ->assertSee('Източник')
            ->assertSee('thinkwithgoogle.com', false)->assertSee('deloitte.com', false)->assertDontSee('Технологична инфраструктура')
            ->assertSee('id="kalkulator"', false)
            ->assertSee('Подобрението за вашия бизнес')->assertSee('Видимост: посещения на месец')->assertDontSee('на година')
            ->assertSee('Изработка на сайтове и дигитален маркетинг от €450')
            ->assertDontSee('Готова система, не визитка')
            ->assertDontSee('Изработка на уебсайтове и дигитален маркетинг')
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

    public function test_long_post_titles_drop_the_brand_suffix_and_an_empty_english_blog_is_not_indexed(): void
    {
        Post::create([
            'title' => ['bg' => '4-те автоматични имейла, които всеки онлайн магазин трябва да има'],
            'slug' => ['bg' => '4-te-avtomatichni-imeyla'],
            'body' => ['bg' => 'Текст.'],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get('/blog/4-te-avtomatichni-imeyla')->assertSee('<title>4-те автоматични имейла, които всеки онлайн магазин трябва да има</title>', false);
        $this->get('/paketi')->assertSee('| Creatium Lab</title>', false);

        $this->get('/en/blog')->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get('/blog')->assertDontSee('noindex', false)->assertDontSee('hreflang="en" href="' . url('/en/blog') . '"', false);
        $this->get('/sitemap.xml')->assertSee('<loc>' . url('/blog') . '</loc>', false)->assertDontSee('<loc>' . url('/en/blog') . '</loc>', false);
    }
}
