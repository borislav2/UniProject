<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Models\Role;
use App\Models\User;
use App\Support\Content;
use App\Support\TeamMaria2026;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Мария Флорова е третият член на екипа: страница „За нас“ в двата дизайна и пренасянето в запазените редакции. */
class TeamMariaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    public function test_about_page_shows_all_three_people_in_the_current_design(): void
    {
        $this->get('/za-nas')->assertOk()
            ->assertSee('Борислав Костадинов')->assertSee('Владимир Цончев')->assertSee('Мария Флорова')
            ->assertSee('Брандиране, имейл кампании и социални мрежи')
            ->assertSee('images/team/maria-florova.webp', false);
        $this->get('/en/about')->assertOk()
            ->assertSee('Borislav Kostadinov')->assertSee('Vladimir Tsonchev')->assertSee('Maria Florova')
            ->assertSee('Branding, email campaigns and social media');
    }

    public function test_about_page_shows_all_three_people_in_design_g(): void
    {
        $this->actingAs($this->admin());
        $this->get('/za-nas?dizain=g')->assertOk()
            ->assertSee('class="g-member"', false)
            ->assertSee('Борислав Костадинов')->assertSee('Владимир Цончев')->assertSee('Мария Флорова');
        $this->get('/en/about')->assertOk()
            ->assertSee('Borislav Kostadinov')->assertSee('Vladimir Tsonchev')->assertSee('Maria Florova');
    }

    public function test_an_empty_linkedin_link_is_hidden(): void
    {
        $html = $this->get('/za-nas')->getContent();
        $this->assertSame(2, substr_count($html, 'Профил в LinkedIn'));
    }

    public function test_apply_appends_maria_once_backs_up_and_keeps_the_others(): void
    {
        $old = [['name' => 'Борислав Костадинов', 'role' => 'Редактиран'], ['name' => 'Владимир Цончев', 'role' => 'Маркетинг']];
        ContentBlock::create(['key' => 'team', 'locale' => 'bg', 'value' => $old]);
        ContentBlock::create(['key' => 'team', 'locale' => 'en', 'value' => [['name' => 'Borislav Kostadinov', 'role' => 'Edited']]]);

        $this->assertSame(['team/bg', 'team/en'], TeamMaria2026::apply());

        $bg = ContentBlock::where(['key' => 'team', 'locale' => 'bg'])->first()->value;
        $this->assertCount(3, $bg);
        $this->assertSame($old, array_slice($bg, 0, 2));
        $this->assertSame('Мария Флорова', $bg[2]['name']);
        $en = ContentBlock::where(['key' => 'team', 'locale' => 'en'])->first()->value;
        $this->assertSame('Maria Florova', $en[1]['name']);
        $this->assertSame($old, ContentBlock::where(['key' => 'team:backup-2026-11-13', 'locale' => 'bg'])->first()->value);

        // Second run changes nothing and never overwrites the backup.
        $this->assertSame([], TeamMaria2026::apply());
        $this->assertCount(3, ContentBlock::where(['key' => 'team', 'locale' => 'bg'])->first()->value);
        $this->assertSame(2, ContentBlock::where('key', 'team:backup-2026-11-13')->count());

        $this->assertSame('Мария Флорова', app(Content::class)->get('team')[2]['name']);
    }

    public function test_apply_skips_a_team_that_already_has_maria(): void
    {
        ContentBlock::create(['key' => 'team', 'locale' => 'bg', 'value' => [['name' => 'мария иванова']]]);

        $this->assertSame([], TeamMaria2026::apply());
        $this->assertNull(ContentBlock::where('key', 'team:backup-2026-11-13')->first());
    }

    public function test_kreo_knowledge_keeps_the_owners_and_lists_maria_as_a_colleague(): void
    {
        $text = \App\Support\Chat\SiteKnowledge::text();
        $this->assertStringContainsString('Мария Флорова', $text);
        $this->assertStringContainsString('colleagues, not owners or founders', $text);
    }
}
