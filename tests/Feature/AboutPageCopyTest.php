<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\AboutCopy2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_has_no_years_no_section_numbers_and_plain_team_copy(): void
    {
        $this->get('/za-nas')->assertOk()
            ->assertDontSee('4 години')
            ->assertDontSee('02 /')
            ->assertSee('Сайтът е готов, рекламата върви, а телефонът мълчи.')
            ->assertSee('Екипът')
            ->assertSee('Сайт, който се зарежда бързо')
            ->assertDontSee('Laravel &amp; Filament', false);

        $this->get('/en/about')->assertOk()->assertDontSee('4 years')->assertSee('the phone stays silent');
    }

    public function test_admin_copies_get_the_new_story_and_bios_but_keep_names_roles_and_photos(): void
    {
        ContentBlock::create(['key' => 'about', 'locale' => 'bg', 'value' => ['subtitle' => 'Мой подзаглавие', 'story' => ['От 4 години правим сайтове.']]]);
        ContentBlock::create(['key' => 'team', 'locale' => 'bg', 'value' => [
            ['name' => 'Борислав Костадинов', 'role' => 'Full-stack Developer', 'description' => 'стар', 'bio' => ['стар'], 'skills' => ['Laravel'], 'image' => 'x.webp'],
            ['name' => 'Владимир Цончев', 'role' => 'Head of Growth', 'description' => 'стар', 'bio' => ['стар'], 'skills' => ['SEO'], 'image' => 'y.webp'],
        ]]);

        $this->assertSame(['about/bg', 'team/bg'], AboutCopy2026::apply());

        $about = ContentBlock::where('key', 'about')->first()->value;
        $this->assertSame('Мой подзаглавие', $about['subtitle']);
        $this->assertSame(config('creatium.about.story'), $about['story']);

        $team = ContentBlock::where('key', 'team')->first()->value;
        $this->assertSame('Full-stack Developer', $team[0]['role']);
        $this->assertSame('x.webp', $team[0]['image']);
        $this->assertSame(config('creatium.team.0.bio'), $team[0]['bio']);
        $this->assertSame('Head of Growth', $team[1]['role']);
        $this->assertSame(config('creatium.team.1.skills'), $team[1]['skills']);
        $this->assertNotNull(ContentBlock::where('key', 'team:backup-2026-11-08-e')->first());
        $this->assertSame([], AboutCopy2026::apply());
    }
}
