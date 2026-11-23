<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\DirectContactWording2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectContactWordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_config_no_longer_has_the_direct_contact_lines(): void
    {
        $all = json_encode([config('creatium.about'), config('creatium.home_problem'), config('creatium.home_trust'),
            config('creatium_en.about'), config('creatium_en.home_problem'), config('creatium_en.home_trust')], JSON_UNESCAPED_UNICODE);
        foreach (['Говорите директно с хората', 'Работите директно с хората', 'You talk directly to the people', 'You work directly with the people'] as $old) {
            $this->assertStringNotContainsString($old, $all, $old);
        }
    }

    public function test_saved_overrides_change_only_exact_old_defaults_with_a_backup(): void
    {
        $old = DirectContactWording2026::OLD['bg'];
        ContentBlock::create(['key' => 'about', 'locale' => 'bg', 'value' => ['story' => ['Наш ред.', $old['story']]]]);
        ContentBlock::create(['key' => 'home_problem', 'locale' => 'bg', 'value' => ['system_trust' => [['icon' => 'handshake', 'text' => $old['chip']], ['icon' => 'tag', 'text' => 'Друго']]]]);
        ContentBlock::create(['key' => 'home_trust', 'locale' => 'bg', 'value' => ['principle_items' => [['title' => 'А', 'text' => $old['principle']], ['title' => 'Б', 'text' => 'Редактиран от нас']]]]);

        $this->assertCount(3, DirectContactWording2026::apply());

        $about = ContentBlock::where('key', 'about')->where('locale', 'bg')->first()->value;
        $this->assertSame(['Наш ред.', config('creatium.about.story.3')], $about['story']);
        $problem = ContentBlock::where('key', 'home_problem')->where('locale', 'bg')->first()->value;
        $this->assertSame([['icon' => 'tag', 'text' => 'Друго']], $problem['system_trust']);
        $trust = ContentBlock::where('key', 'home_trust')->where('locale', 'bg')->first()->value;
        $this->assertSame(config('creatium.home_trust.principle_items.2.text'), $trust['principle_items'][0]['text']);
        $this->assertSame('Редактиран от нас', $trust['principle_items'][1]['text']);
        $this->assertTrue(ContentBlock::where('key', 'about:backup-2026-11-23')->exists());

        $this->assertSame([], DirectContactWording2026::apply(), 'Second run changes nothing');
    }
}
