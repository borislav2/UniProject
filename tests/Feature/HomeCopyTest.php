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
            ->assertDontSee('Рекламните кодове са настроени')
            ->assertDontSee('Правим това от 4 години.')
            ->assertSee('Дигитални решения, изградени с мисъл за вашия')
            ->assertSee('Персонализирани сайтове. Брандиран маркетинг.')
            ->assertSee('Работим с бизнеси, които имат продукт или услуга с потенциал')
            ->assertSee('Цялостен подход към дигиталното ви присъствие')
            ->assertDontSee('Източник:')->assertDontSee('Технологична инфраструктура')
            ->assertDontSee('id="kalkulator"', false)->assertDontSee('Подобрението за вашия бизнес')
            ->assertSee('Изработка на сайтове и дигитален маркетинг от €400')
            ->assertDontSee('Готова система, не визитка')
            ->assertDontSee('Изработка на уебсайтове и дигитален маркетинг')
            ->assertDontSee('Без посредници')
            ->assertDontSee('без жаргон')
            ->assertDontSee('малки бизнеси');
    }

    public function test_home_page_hero_and_the_four_squares(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<span class="text-accent">успех</span>', false)
            ->assertSee('href="' . url('/paketi') . '#oferta"', false)
            ->assertSeeTextInOrder(['Партньор, който ви води стъпка по стъпка', 'Не ви оставяме сами в нито един момент.', 'Прецизен контрол над данните и бюджета', 'Лесно управление и пълна поддръжка', 'Изградено по доказан процес и световен стандарт']);
        $this->get('/en')->assertOk()->assertSeeText('A partner who guides you step by step')->assertSeeText('Built on a proven process and a global standard');
    }

    public function test_home_page_guide_items_and_system_steps(): void
    {
        $this->get('/')->assertOk()
            ->assertSeeTextInOrder(['Принципите, по които ви напътстваме', 'Стратегическа видимост', 'Репутация и доверие', 'Технологичен стандарт', 'Устойчивост във времето',
                'Обединяваме бранд идентичността', 'Брандиране', 'Визуален авторитет и доверие', 'Уеб изработка', 'Платформа за реални резултати', 'Видимост', 'Маркетинг', 'Клиенти, които се връщат', 'Грижа всеки месец', 'Успешен бизнес'])
            ->assertDontSee('Какво показват данните, когато всичко работи заедно');
        $this->get('/en')->assertOk()->assertSeeText('The principles we guide you by')->assertSeeText('Visual authority and trust');
    }

    public function test_home_page_shows_the_seven_funnel_levels(): void
    {
        $page = $this->get('/')->assertOk()
            ->assertSeeTextInOrder(['Анатомия на вашия дигитален двигател', 'Как изграждаме пътя от първия контакт до дългосрочната лоялност', 'Разгледайте всички услуги в детайли', 'Успехът не приключва с първата продажба.',
                '1 · Откриване', 'Awareness', 'Стратегическо присъствие пред готови купувачи', 'Урок за фунията', 'Първото ниво, което отваря вратата към вашия бизнес.', 'Ако спре', 'Пълна липса на входящ поток',
                '7 · Лоялност и препоръки', 'Loyalty', 'Липса на препоръки']);
        $this->assertSame(7, substr_count($page->getContent(), 'data-funnel-step="'));
        $this->get('/en')->assertOk()->assertSeeText('Strategic presence in front of ready buyers')->assertSeeText('Funnel lesson')->assertSeeText('No referrals');
        $this->get('/uslugi')->assertOk()->assertSee('data-funnel-step="6"', false);
    }

    public function test_calculator_lives_on_the_services_page_not_on_the_home_page(): void
    {
        $this->get('/')->assertOk()->assertDontSee('data-calc', false)->assertDontSee('Колко можем да подобрим бизнеса ви?');
        $this->get('/uslugi')->assertOk()->assertSee('id="kalkulator"', false)->assertSee('Колко можем да подобрим бизнеса ви?')->assertDontSee('03 / Калкулатор');
        $this->get('/en/services')->assertOk()->assertSee('id="kalkulator"', false);
        app()->setLocale('bg');
        $this->assertStringContainsString('/uslugi#kalkulator', \App\Support\Chat\SiteKnowledge::text());
    }

    public function test_home_page_values_industries_and_process_copy(): void
    {
        $this->get('/')->assertOk()
            ->assertSeeTextInOrder(['Нашите ценности', 'Принципите, които гарантират реалния растеж на вашия бизнес', 'Работим с бизнеси, които имат продукт или услуга с потенциал',
                'Резултатите пред украсата', 'Точни данни вместо предположения', 'Оперативна гъвкавост и бързина', 'Пълна собственост и контрол',
                'Специализирани дигитални архитектури', 'Готови концепции и работещи модели за конкретни бизнес ниши', 'Концепции за сектори', 'Бизнес бранша',
                'Как протича процесът', 'Шест етапа от анализа до постоянната оптимизация', 'Изграждаме вашата дигитална система чрез ясна методология.', 'Стъпка 1', 'Стратегически анализ', 'Стъпка 6', 'Поддръжка и развитие'])
            ->assertDontSee('Стъпка 1 — ');
        $this->get('/en')->assertOk()->assertSeeText('Results over decoration')->assertSeeText('Six stages from analysis to continuous optimisation')->assertSeeText('Step 1');
    }

    public function test_new_home_copy_survives_an_older_admin_save_of_the_blocks(): void
    {
        ContentBlock::create(['key' => 'home_trust', 'locale' => 'bg', 'value' => ['eyebrow' => '']]);
        ContentBlock::create(['key' => 'home_process', 'locale' => 'bg', 'value' => ['title' => 'Стар процес', 'steps' => [['title' => 'Едно', 'text' => 'Два']]]]);
        $this->get('/')->assertOk()->assertSee('Анатомия на вашия дигитален двигател')->assertSee('Партньор, който ви води стъпка по стъпка')->assertSee('Стар процес')->assertSee('Шест етапа от анализа до постоянната оптимизация');
    }

    public function test_about_page_tells_the_story_even_when_the_admin_saved_an_older_about_block(): void
    {
        ContentBlock::create(['key' => 'about', 'locale' => 'bg', 'value' => ['subtitle' => '', 'paragraphs' => ['Нашият текст от админа.']]]);

        $this->get('/za-nas')->assertOk()
            ->assertSeeInOrder(['Кои сме ние', 'Нашият текст от админа.', 'Защо го правим', 'Сайтът е готов, рекламата върви, а телефонът мълчи.', 'Екипът'])
            ->assertDontSee('Говорите с хората, които правят работата.');
        $this->get('/en/about')->assertOk()->assertSee('Why we do it')->assertSee('the phone stays silent');
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
