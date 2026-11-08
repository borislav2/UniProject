<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\Content;
use App\Support\FinalPrices2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Миграцията 2026_11_08_000001_apply_final_prices: архив първо, после само ценовите ключове на записите от админа. */
class FinalPricesMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function oldPlans(): array
    {
        $item = fn (string $name, array $prices, string $badge = '') => [
            'name' => $name, 'badge' => $badge, 'highlighted' => $badge !== '', 'audience' => 'Стар текст', 'prices' => $prices,
            'year_total' => '€2 250 за първата година', 'upgrade_note' => 'Бележка на собствениците', 'button' => '',
            'build_title' => 'Стар', 'build_items' => ['Стара точка'], 'monthly_title' => '', 'monthly_items' => [],
        ];

        return [
            'intro' => 'Всеки пакет включва цялата система: марка, сайт, видимост, маркетинг, връщащи се клиенти и грижа всеки месец. Разликата е в мащаба.',
            'pricing' => ['title' => 'Плъзгач', 'steps' => [['value' => '4', 'monthly' => [200, 450], 'recommended' => 1]]],
            'trust' => [
                ['icon' => 'eye', 'text' => 'Демо преди пускане: виждате сайта и го одобрявате, преди да тръгне'],
                ['icon' => 'lock', 'text' => 'Моят собствен текст за доверие'],
                ['icon' => 'file-contract', 'text' => 'Плащате на две части: 50% при договор, 50% след одобрено демо'],
            ],
            'items' => [
                $item('Стартов двигател', ['€600 старт: стар', 'след това от €150 / месец']),
                $item('Активен маркетинг и растеж', ['€900 старт: стар', 'след това от €400 / месец'], 'Хит'),
                $item('Индивидуален маркетинг и мащабиране', ['Индивидуална оферта и абонамент според мащаба']),
            ],
            'compare' => [['label' => 'Стар ред', 'values' => ['yes', 'yes', 'yes']]],
            'steps' => [
                ['title' => 'Запитване', 'text' => 'Моето собствено описание на първата стъпка.'],
                ['title' => 'Оферта и демо', 'text' => 'Получавате оферта с фиксирана цена. Преди пускането виждате демо на сайта.'],
                ['title' => 'Старт след одобрение', 'text' => 'Стандартният срок е 1–2 седмици. Сайтът тръгва едва след вашето одобрение.'],
            ],
            'terms' => [
                ['title' => 'Фиксирана цена', 'text' => 'Моят текст за фиксираната цена.'],
                ['title' => 'Плащане на две части', 'text' => 'Половината при подписване на договора, другата половина след като одобрите демото.'],
                ['title' => 'Какво покрива месечната такса', 'text' => 'Стар текст за таксата.'],
                ['title' => 'Моето собствено условие', 'text' => 'Остава накрая.'],
            ],
            'guarantee' => ['title' => 'Моята гаранция', 'text' => 'Текст на собствениците, който не се пипа.', 'note' => ''],
            'owners_extra_key' => 'не се пипа',
        ];
    }

    private function block(string $key, string $locale, array $value): ContentBlock
    {
        return ContentBlock::create(['key' => $key, 'locale' => $locale, 'value' => $value]);
    }

    public function test_a_backup_is_made_first_and_only_price_keys_change(): void
    {
        $old = $this->oldPlans();
        $this->block('plans', 'bg', $old);
        $new = config('creatium.plans');

        $report = FinalPrices2026::apply();

        $this->assertSame(['plans:bg'], $report['backed_up']);
        $backup = ContentBlock::where(['key' => 'plans' . FinalPrices2026::BACKUP_SUFFIX, 'locale' => 'bg'])->sole();
        $this->assertSame($old, $backup->value);

        $row = ContentBlock::where(['key' => 'plans', 'locale' => 'bg'])->sole()->value;

        // Цените, имената, списъците и годишните суми са новите; бележката, бадж-ът и бутонът на собствениците остават.
        $this->assertSame($new['items'][0]['prices'], $row['items'][0]['prices']);
        $this->assertSame([config('creatium.plans.items.1.prices.0'), 'след това €650 / месец'], $row['items'][1]['prices']);
        $this->assertSame('Индивидуален маркетинг', $row['items'][2]['name']);
        $this->assertSame($new['items'][1]['build_items'], $row['items'][1]['build_items']);
        $this->assertSame($new['items'][0]['deposit'], $row['items'][0]['deposit']);
        $this->assertSame('€7 950 за първата година (€800 + 11 × €650)', $row['items'][1]['year_total']);
        $this->assertSame('Хит', $row['items'][1]['badge']);
        $this->assertTrue($row['items'][1]['highlighted']);
        $this->assertSame('Бележка на собствениците', $row['items'][0]['upgrade_note']);

        $this->assertSame($new['compare'], $row['compare']);
        $this->assertSame($new['compare_note'], $row['compare_note']);
        $this->assertSame($new['intro'], $row['intro']);
        $this->assertArrayNotHasKey('pricing', $row);
        $this->assertSame($new['billing'], $row['billing']);

        // Каквото не е свързано с цените, остава както го е написал администраторът.
        $this->assertSame($old['guarantee'], $row['guarantee']);
        $this->assertSame('не се пипа', $row['owners_extra_key']);
        $this->assertSame('Моят собствен текст за доверие', $row['trust'][1]['text']);
        $this->assertSame($new['trust'][0]['text'], $row['trust'][0]['text']);
        $this->assertSame($new['trust'][1]['text'], $row['trust'][2]['text']);
        $this->assertSame('lock', $row['trust'][1]['icon']);
        $this->assertSame('Моето собствено описание на първата стъпка.', $row['steps'][0]['text']);
        $this->assertSame($new['steps'][2], $row['steps'][1]);
        $this->assertSame($new['steps'][3], $row['steps'][2]);

        // Условията: плащането и месечната такса са новите, „Фиксирана цена“ е на собствениците, собственото условие остава накрая.
        $titles = array_column($row['terms'], 'title');
        $this->assertNotContains('Плащане на две части', $titles);
        $this->assertContains('Плащане на старта на две части', $titles);
        $this->assertContains('Годишно плащане с 2 месеца безплатно', $titles);
        $this->assertSame('Моят текст за фиксираната цена.', $row['terms'][array_search('Фиксирана цена', $titles, true)]['text']);
        $this->assertSame('Моето собствено условие', end($row['terms'])['title']);
        $monthly = $row['terms'][array_search('Какво покрива месечната такса', $titles, true)];
        $this->assertStringContainsString('месечен отчет', $monthly['text']);
    }

    public function test_an_existing_backup_is_never_overwritten_and_a_second_run_changes_nothing(): void
    {
        $this->block('plans', 'bg', $this->oldPlans());
        FinalPrices2026::apply();
        $first = ContentBlock::where(['key' => 'plans' . FinalPrices2026::BACKUP_SUFFIX, 'locale' => 'bg'])->sole()->value;

        // Собствениците пак слагат стара цена и пускаме отново: архивът остава първият.
        $edited = ContentBlock::where(['key' => 'plans', 'locale' => 'bg'])->sole();
        $value = $edited->value;
        $value['items'][0]['prices'][0] = '€600 старт: пак стара';
        $edited->update(['value' => $value]);

        $report = FinalPrices2026::apply();
        $this->assertSame([], $report['backed_up']);
        $this->assertSame(['plans:bg'], $report['changed']);
        $this->assertSame($first, ContentBlock::where(['key' => 'plans' . FinalPrices2026::BACKUP_SUFFIX, 'locale' => 'bg'])->sole()->value);
        $this->assertSame(config('creatium.plans.items.0.prices.0'), ContentBlock::where(['key' => 'plans', 'locale' => 'bg'])->sole()->value['items'][0]['prices'][0]);

        // Трети път: нищо за променяне, няма нови записи.
        $count = ContentBlock::count();
        $this->assertSame([], FinalPrices2026::apply()['changed']);
        $this->assertSame($count, ContentBlock::count());
    }

    public function test_a_different_number_of_plans_is_replaced_whole_and_english_follows_the_english_config(): void
    {
        $old = $this->oldPlans();
        $old['items'] = array_slice($old['items'], 0, 2);
        $this->block('plans', 'bg', $old);
        $this->block('plans', 'en', ['intro' => 'Every package includes the whole system: brand, website, visibility, marketing, returning customers and care every month. The difference is the scale.', 'items' => [['name' => 'Starter Engine', 'prices' => ['€600 start']]]]);

        FinalPrices2026::apply();

        $bg = ContentBlock::where(['key' => 'plans', 'locale' => 'bg'])->sole()->value;
        $this->assertSame(config('creatium.plans.items'), $bg['items']);
        $en = ContentBlock::where(['key' => 'plans', 'locale' => 'en'])->sole()->value;
        $this->assertSame(config('creatium_en.plans.items'), $en['items']);
        $this->assertSame(config('creatium_en.plans.intro'), $en['intro']);
        $this->assertSame('Custom Marketing', $en['items'][2]['name']);
    }

    public function test_questions_and_the_home_chip_change_only_when_they_are_the_old_default_text(): void
    {
        $oldAnswers = (new \ReflectionClassConstant(FinalPrices2026::class, 'OLD'))->getValue()['bg']['questions'];
        $this->block('questions', 'bg', [
            ['q' => 'Как се определя цената на проекта?', 'a' => $oldAnswers['Как се определя цената на проекта?'], 'group' => 'Цени и плащане'],
            ['q' => 'Как протича процесът на плащане?', 'a' => 'Моят собствен отговор със стара цена €600.', 'group' => 'Цени и плащане'],
            ['q' => 'Моят въпрос', 'a' => 'Моят отговор.'],
        ]);
        $this->block('home_problem', 'bg', ['system_trust' => [
            ['icon' => 'handshake', 'text' => 'Мой текст'],
            ['icon' => 'tag', 'text' => 'Фиксирана цена и демо преди старта'],
        ], 'title' => 'Моето заглавие']);

        $report = FinalPrices2026::apply();

        $questions = ContentBlock::where(['key' => 'questions', 'locale' => 'bg'])->sole()->value;
        $this->assertStringContainsString('Стартов двигател е €400 еднократно', $questions[0]['a']);
        $this->assertSame('Моят собствен отговор със стара цена €600.', $questions[1]['a']);
        $this->assertSame('Моят отговор.', $questions[2]['a']);
        $this->assertContains('questions:bg', $report['untouched_with_old_prices']); // €600 в отговор, който не е стандартният

        $chips = ContentBlock::where(['key' => 'home_problem', 'locale' => 'bg'])->sole()->value;
        $this->assertSame('Мой текст', $chips['system_trust'][0]['text']);
        $this->assertSame('Фиксирана цена, записана в офертата', $chips['system_trust'][1]['text']);
        $this->assertSame('Моето заглавие', $chips['title']);

        $this->assertTrue(ContentBlock::where(['key' => 'questions' . FinalPrices2026::BACKUP_SUFFIX, 'locale' => 'bg'])->exists());
        $this->assertTrue(ContentBlock::where(['key' => 'home_problem' . FinalPrices2026::BACKUP_SUFFIX, 'locale' => 'bg'])->exists());
    }

    public function test_untouched_blocks_get_no_backup_and_the_content_cache_is_flushed(): void
    {
        $this->block('home_hero', 'bg', ['title' => 'Моето заглавие']);
        $this->block('plans', 'bg', $this->oldPlans());

        // Кешът държи старите цени, докато не се изчисти.
        $this->assertSame('€600 старт: стар', app(Content::class)->get('plans.items.0.prices.0'));

        FinalPrices2026::apply();

        $this->assertFalse(ContentBlock::where('key', 'home_hero' . FinalPrices2026::BACKUP_SUFFIX)->exists());
        $this->assertSame('€400 еднократно за изграждането и първия месец', app(Content::class)->get('plans.items.0.prices.0'));
        $this->get('/paketi')->assertOk()->assertSee('€400')->assertDontSee('€600 старт');
    }

    public function test_nothing_happens_without_overrides(): void
    {
        $report = FinalPrices2026::apply();
        $this->assertSame(['backed_up' => [], 'changed' => [], 'untouched_with_old_prices' => []], $report);
        $this->assertSame(0, ContentBlock::count());
    }
}
