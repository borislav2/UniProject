<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\OutreachProspect;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\Badges;
use App\Support\Admin\Profiles;
use App\Support\Admin\SystemHealth;
use App\Support\Opportunities\Collector;
use App\Support\Opportunities\ManualEntry;
use App\Support\Opportunities\OpportunityLinks;
use App\Support\Opportunities\PartnerOpportunities;
use App\Support\Opportunities\PartnerPitch;
use App\Support\Outreach\BusinessSearch;
use App\Support\Outreach\Drafts;
use App\Support\Outreach\Markets;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Екранът „Възможности“: списък, действия, ръчно добавяне, партньори, профили, „Днес“, брояч, търсене, проект и командата. */
class OpportunitiesAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00', 'Europe/Sofia'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function person(string $profile = 'sales', string $name = 'Владимир'): User
    {
        if (! Role::exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CategorySeeder::class);
        }
        $user = User::factory()->create(['name' => $name, 'profile' => $profile]);
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    private function opp(array $values = []): Opportunity
    {
        static $n = 0;
        $n++;

        return Opportunity::create($values + [
            'kind' => 'tender', 'source' => 'ted', 'external_id' => "9000{$n}-2026", 'title' => "Нов уебсайт номер {$n}", 'buyer' => 'Община Тест', 'country' => 'BG', 'language' => 'bg',
            'url' => "https://ted.europa.eu/en/notice/-/detail/9000{$n}-2026", 'url_key' => Opportunity::urlKey("https://ted.europa.eu/en/notice/-/detail/9000{$n}-2026"), 'cpv' => ['72413000'],
            'value_max' => 40000, 'currency' => 'EUR', 'deadline_at' => now()->addDays(20), 'score' => 80,
            'score_reasons' => [['label' => 'CPV 72413000 (Услуги по уеб дизайн): точно наша услуга', 'points' => 40], ['label' => 'Пазар: България, основен пазар', 'points' => 20]],
            'status' => 'new', 'raw' => ['topic' => 'web'],
        ]);
    }

    // ---- Списък и филтри ----

    public function test_the_list_shows_title_buyer_country_deadline_value_score_source_link_and_a_bulgarian_summary(): void
    {
        $o = $this->opp(['title' => 'Нов уебсайт за общината', 'deadline_at' => now()->addDays(3), 'score' => 87]);

        $html = $this->actingAs($this->person())->get('/admin/opportunities')->assertOk()->getContent();
        $this->assertStringContainsString('Нов уебсайт за общината', $html);
        $this->assertStringContainsString('Община Тест', $html);
        $this->assertStringContainsString('България', $html);
        $this->assertStringContainsString('87', $html);
        $this->assertStringContainsString('€40 000', $html);
        $this->assertStringContainsString('21.11.2026', $html);
        $this->assertStringContainsString('след 3 дни', $html);
        // Под 7 дни е отбелязан като спешен (червено).
        $this->assertStringContainsString('op-row urgent', $html);
        $this->assertStringContainsString('Поръчка за уебсайт и уеб разработка от Община Тест, България, срок 21.11.2026, стойност €40 000.', $html);
        $this->assertStringContainsString('href="' . $o->url . '" target="_blank" rel="noopener noreferrer"', $html);
        $this->assertStringContainsString('Защо 87 точки', $html);
        $this->assertStringContainsString('Пазар: България, основен пазар', $html);
        $this->assertStringContainsString('Нищо не се подава и не се праща автоматично', $html);
    }

    public function test_filters_tabs_mine_and_sorting(): void
    {
        $me = $this->person();
        $other = $this->person('brand', 'Мария');
        $a = $this->opp(['title' => 'Алфа сайт', 'score' => 50, 'deadline_at' => now()->addDays(30), 'country' => 'GB']);
        $b = $this->opp(['title' => 'Бета сайт', 'score' => 90, 'deadline_at' => now()->addDays(5), 'assigned_to' => $me->id, 'status' => 'interesting']);
        $c = $this->opp(['title' => 'Гама партньор', 'kind' => 'partner', 'source' => 'search', 'score' => 70, 'deadline_at' => null, 'country' => 'IE', 'assigned_to' => $other->id]);
        $d = $this->opp(['title' => 'Делта сайт', 'status' => 'skipped', 'skip_reason' => 'твърде голяма']);

        $titles = fn (string $query) => collect(preg_match_all('/class="op-title">([^<]+)</', $this->actingAs($me)->get('/admin/opportunities' . $query)->getContent(), $m) ? $m[1] : [])->all();

        // По подразбиране „Отворени“ по оценка: пропуснатата я няма.
        $this->assertSame(['Бета сайт', 'Гама партньор', 'Алфа сайт'], $titles(''));
        $this->assertSame(['Делта сайт'], $titles('?status=skipped'));
        $this->assertSame(['Бета сайт'], $titles('?status=interesting'));
        $this->assertCount(4, $titles('?status=all'));
        $this->assertSame(['Гама партньор'], $titles('?kind=partner'));
        $this->assertSame(['Алфа сайт'], $titles('?country=GB'));
        $this->assertSame(['Гама партньор'], $titles('?source=search'));
        $this->assertSame(['Бета сайт'], $titles('?who=me'));
        $this->assertSame(['Алфа сайт'], $titles('?q=Алфа'));
        // По срок: най-близкият първи, без срок накрая.
        $this->assertSame(['Бета сайт', 'Алфа сайт', 'Гама партньор'], $titles('?sort=deadline'));
        $this->assertSame(['Гама партньор', 'Бета сайт', 'Алфа сайт'], $titles('?sort=new'));
    }

    public function test_quick_actions_interesting_take_skip_note_and_status_with_history(): void
    {
        $user = $this->person();
        $o = $this->opp();

        $this->actingAs($user)->post("/admin/opportunities/{$o->id}/interesting")->assertSessionHas('success');
        $this->assertSame('interesting', $o->fresh()->status);

        $this->actingAs($user)->post("/admin/opportunities/{$o->id}/take")->assertSessionHas('success');
        $this->assertSame($user->id, $o->fresh()->assigned_to);

        $this->actingAs($user)->patch("/admin/opportunities/{$o->id}", ['status' => 'preparing', 'note' => 'Питахме за референции', 'next_action_at' => '2026-11-20', 'assigned_to' => $user->id])->assertSessionHas('success');
        $fresh = $o->fresh();
        $this->assertSame('preparing', $fresh->status);
        $this->assertSame('Питахме за референции', $fresh->note);
        // Въвежда се по часа в София (09:00), пази се в UTC.
        $this->assertSame('2026-11-20 07:00:00', $fresh->next_action_at->utc()->format('Y-m-d H:i:s'));

        $this->actingAs($user)->post("/admin/opportunities/{$o->id}/skip", ['skip_reason' => 'твърде голяма за нас'])->assertSessionHas('success');
        $fresh = $o->fresh();
        $this->assertSame('skipped', $fresh->status);
        $this->assertSame('твърде голяма за нас', $fresh->skip_reason);
        $this->assertNull($fresh->next_action_at, 'a finished opportunity has no next step');

        $this->actingAs($user)->post("/admin/opportunities/{$o->id}/note", ['body' => 'Прегледахме документацията'])->assertSessionHas('success');

        $history = $o->notes()->pluck('body')->all();
        $this->assertContains('Прегледахме документацията', $history);
        $this->assertContains('Подготвяме → Пропусната: твърде голяма за нас', $history);
        $this->assertContains('Нова → Интересна', $history);
        $this->assertSame($user->id, $o->notes()->where('kind', 'status')->first()->user_id);

        $this->actingAs($user)->get("/admin/opportunities/{$o->id}")->assertOk()->assertSee('Прегледахме документацията')->assertSee('Какво трябва за участие')
            ->assertSee('оборот, референции')->assertSee('Админът не подава нищо');
    }

    public function test_detail_page_shows_cpv_labels_reasons_and_the_participation_checklist_only_for_tenders(): void
    {
        $user = $this->person();
        $tender = $this->opp(['cpv' => ['72413000', '79340000', '30192130'], 'summary' => 'Описание на български от обявата', 'language' => 'fi']);
        $html = $this->actingAs($user)->get("/admin/opportunities/{$tender->id}")->assertOk()->getContent();
        $this->assertStringContainsString('72413000', $html);
        $this->assertStringContainsString('Услуги по уеб дизайн', $html);
        $this->assertStringContainsString('Рекламни и маркетингови услуги', $html);
        $this->assertStringContainsString('30192130', $html);
        $this->assertStringContainsString('+40', $html);
        $this->assertStringContainsString('Описание на български от обявата', $html);
        $this->assertStringContainsString('Официалната обява', $html);
        $this->assertStringContainsString('Първо проверете условията', $html);
        $this->assertStringContainsString('малка агенция може да не покрива', $html);
        $this->assertStringContainsString('обявата е на фински', $html);

        $freelance = $this->opp(['kind' => 'freelance', 'source' => 'manual']);
        $this->actingAs($user)->get("/admin/opportunities/{$freelance->id}")->assertOk()->assertDontSee('Какво трябва за участие');
    }

    // ---- Ръчно добавяне ----

    public function test_manual_add_scores_it_and_refuses_the_same_url_twice(): void
    {
        $user = $this->person();
        $data = ['kind' => 'freelance', 'title' => 'Нов уебсайт за мебелна фирма', 'url' => 'https://www.upwork.com/jobs/~0123456789?utm_source=share', 'origin' => 'Upwork',
            'country' => 'GB', 'budget' => '1500', 'currency' => 'GBP', 'deadline' => '2026-12-10', 'note' => 'Търсят уебсайт и SEO'];

        $response = $this->actingAs($user)->post('/admin/opportunities', $data);
        $o = Opportunity::firstOrFail();
        $response->assertRedirect(route('admin.opportunities.show', $o));
        $this->assertSame('manual', $o->source);
        $this->assertSame('freelance', $o->kind);
        $this->assertSame('Upwork', $o->sourceLabel());
        $this->assertSame('GB', $o->country);
        $this->assertSame(1500.0, $o->value_max);
        $this->assertSame('GBP', $o->currency);
        $this->assertSame($user->id, $o->assigned_to);
        $this->assertSame('new', $o->status);
        $this->assertGreaterThan(0, $o->score);
        $this->assertSame('2026-12-10', $o->deadline_at->timezone('Europe/Sofia')->toDateString());

        // Същият адрес (с други параметри за проследяване, www и наклонена черта накрая) не се добавя пак.
        foreach (['https://upwork.com/jobs/~0123456789/', 'https://www.upwork.com/jobs/~0123456789?utm_campaign=x&ref=y'] as $same) {
            $this->actingAs($user)->post('/admin/opportunities', ['title' => 'Друго заглавие', 'url' => $same] + $data)
                ->assertSessionHasErrors('url')->assertSessionHas('duplicate_id', $o->id);
        }
        $this->assertSame(1, Opportunity::count());

        // Друг адрес е друга възможност; без адрес също се добавя.
        $this->actingAs($user)->post('/admin/opportunities', ['kind' => 'grant', 'title' => 'Програма за цифровизация', 'url' => 'https://www.eufunds.bg/bg/programa-1'])->assertSessionHasNoErrors();
        $this->actingAs($user)->post('/admin/opportunities', ['kind' => 'other', 'title' => 'Чух за нещо', 'note' => 'без адрес'])->assertSessionHasNoErrors();
        $this->assertSame(3, Opportunity::count());
        $this->assertSame(['freelance', 'grant', 'other'], Opportunity::orderBy('id')->pluck('kind')->all());
    }

    public function test_manual_add_validates_input(): void
    {
        $user = $this->person();
        $this->actingAs($user)->post('/admin/opportunities', ['kind' => 'tender'])->assertSessionHasErrors('title');
        $this->actingAs($user)->post('/admin/opportunities', ['kind' => 'nonsense', 'title' => 'x'])->assertSessionHasErrors('kind');
        $this->actingAs($user)->post('/admin/opportunities', ['kind' => 'other', 'title' => 'x', 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
        $this->actingAs($user)->post('/admin/opportunities', ['kind' => 'other', 'title' => 'x', 'deadline' => '2020-01-01'])->assertSessionHasErrors('deadline');
        $this->assertSame(0, Opportunity::count());

        // Връзката ключ е еднаква за един и същ адрес.
        $this->assertSame(Opportunity::urlKey('https://www.Example.com/a/b/?utm_source=x'), Opportunity::urlKey('example.com/a/b'));
        $this->assertNotSame(Opportunity::urlKey('https://example.com/a?id=1'), Opportunity::urlKey('https://example.com/a?id=2'));
        $this->assertNull(Opportunity::urlKey(''));
    }

    public function test_saved_searches_have_defaults_can_be_edited_and_only_keep_web_links(): void
    {
        $user = $this->person();
        $defaults = OpportunityLinks::get('searches');
        $labels = collect($defaults)->pluck('label')->implode(' ');
        foreach (['Upwork', 'Malt', 'freelancermap', 'PeoplePerHour'] as $platform) {
            $this->assertStringContainsString($platform, $labels);
        }
        $this->assertStringContainsString('eufunds.bg', json_encode(OpportunityLinks::get('grants')));
        $this->assertStringContainsString('Funding &amp; Tenders Portal', $this->actingAs($user)->get('/admin/opportunities')->getContent());

        $this->actingAs($user)->put('/admin/opportunities/links', [
            'searches' => "Upwork Laravel | https://www.upwork.com/nx/search/jobs/?q=laravel\nМръсотия | javascript:alert(1)\nhttps://www.malt.com/s?q=seo\nБез адрес",
            'grants' => "Моя програма | https://example.org/grants",
        ])->assertSessionHas('success');

        $this->assertSame([
            ['label' => 'Upwork Laravel', 'url' => 'https://www.upwork.com/nx/search/jobs/?q=laravel'],
            ['label' => 'www.malt.com', 'url' => 'https://www.malt.com/s?q=seo'],
        ], OpportunityLinks::get('searches'));
        $html = $this->actingAs($user)->get('/admin/opportunities')->getContent();
        $this->assertStringContainsString('href="https://www.upwork.com/nx/search/jobs/?q=laravel" target="_blank" rel="noopener noreferrer"', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringContainsString('Моя програма', $html);
    }

    // ---- Към проект ----

    public function test_to_project_creates_a_client_project_once_and_links_it_back(): void
    {
        $user = $this->person();
        $o = $this->opp(['title' => 'Нов уебсайт за общината', 'status' => 'interesting', 'note' => 'Говорих с възложителя']);

        $response = $this->actingAs($user)->post("/admin/opportunities/{$o->id}/project");
        $project = Project::where('source', 'opportunity')->firstOrFail();
        $response->assertRedirect(route('admin.projects.show', $project));
        $this->assertSame('Възможност: Нов уебсайт за общината', $project->name);
        $this->assertSame('Planning', $project->status);
        $this->assertSame('Възможности', $project->lead_channel);
        $this->assertNull($project->stage, 'it is work, not a lead');
        $this->assertSame('Възможности', $project->category->name);
        $this->assertStringContainsString($o->url, $project->description);
        $this->assertStringContainsString('Говорих с възложителя', $project->description);
        $this->assertSame($project->id, $o->fresh()->project_id);
        $this->assertSame('preparing', $o->fresh()->status);

        // Втори клик отваря същия проект.
        $this->actingAs($user)->post("/admin/opportunities/{$o->id}/project")->assertRedirect(route('admin.projects.show', $project));
        $this->assertSame(1, Project::where('source', 'opportunity')->count());

        // Проект за работа е в „Проекти“, не в „Запитвания“.
        $this->assertTrue(Project::clientProjects()->whereKey($project->id)->exists());
        $this->actingAs($user)->get('/admin/projects')->assertOk()->assertSee('Възможност: Нов уебсайт за общината');
        $this->actingAs($user)->get("/admin/opportunities/{$o->id}")->assertOk()->assertSee('Проектът →');
    }

    // ---- Профили, Днес, брояч, търсене, статистика, здраве ----

    public function test_profile_menus_put_opportunities_under_sales_and_all_and_under_all_sections_for_the_rest(): void
    {
        $menus = function (User $user) {
            $html = $this->actingAs($user)->get('/admin/dnes')->assertOk()->getContent();
            $nav = substr($html, strpos($html, 'class="adm-nav"'));
            $nav = substr($nav, 0, strpos($nav, 'adm-side-foot'));
            $parts = explode('class="adm-more"', $nav, 2);

            return [$parts[0], $parts[1] ?? ''];
        };

        [$main, $more] = $menus($this->person('sales'));
        $this->assertStringContainsString('Възможности', $main);
        $this->assertStringNotContainsString('Възможности', $more);
        [$main, $more] = $menus($this->person('all', 'Админ'));
        $this->assertStringContainsString('Възможности', $main);
        $this->assertSame('', $more);
        foreach (['brand', 'dev'] as $profile) {
            [$main, $more] = $menus($this->person($profile, "Човек $profile"));
            $this->assertStringNotContainsString('Възможности', $main, $profile);
            $this->assertStringContainsString('Възможности', $more, $profile);
        }
        $this->assertContains('sales-opportunities', Profiles::todaySections('sales')['']);
        $this->assertContains('sales-deadlines', Profiles::todaySections('sales')['']);
        $this->assertNotContains('sales-opportunities', Profiles::todaySections('brand')['']);
    }

    public function test_today_shows_new_opportunities_and_deadlines_this_week_for_sales_and_all_only(): void
    {
        $sales = $this->person('sales');
        $this->opp(['title' => 'Най-добрата поръчка', 'score' => 95]);
        $this->opp(['title' => 'Втората поръчка', 'score' => 70]);
        $this->opp(['title' => 'Слаба поръчка', 'score' => 40]);
        $this->opp(['title' => 'Работим по нея', 'status' => 'preparing', 'score' => 60, 'deadline_at' => now()->addDays(2)]);
        $this->opp(['title' => 'Интересна със срок след месец', 'status' => 'interesting', 'deadline_at' => now()->addDays(30)]);
        $this->opp(['title' => 'Нова със срок утре', 'status' => 'new', 'score' => 30, 'deadline_at' => now()->addDay()]);

        $html = $this->actingAs($sales)->get('/admin/dnes')->assertOk()->getContent();
        $this->assertStringContainsString('data-block="sales-opportunities"', $html);
        $this->assertStringContainsString('data-block="sales-deadlines"', $html);
        $newBlock = substr($html, strpos($html, 'data-block="sales-opportunities"'));
        $newBlock = substr($newBlock, 0, strpos($newBlock, '</section>'));
        $this->assertStringContainsString('Най-добрата поръчка', $newBlock);
        $this->assertStringContainsString('Втората поръчка', $newBlock);
        $this->assertStringNotContainsString('Слаба поръчка', $newBlock, 'below the badge score');
        $this->assertLessThan(strpos($newBlock, 'Втората поръчка'), strpos($newBlock, 'Най-добрата поръчка'), 'best first');
        $this->assertStringContainsString('Интересна', $newBlock);

        $deadlines = substr($html, strpos($html, 'data-block="sales-deadlines"'));
        $deadlines = substr($deadlines, 0, strpos($deadlines, '</section>'));
        $this->assertStringContainsString('Работим по нея', $deadlines);
        $this->assertStringNotContainsString('след месец', $deadlines);
        $this->assertStringNotContainsString('Нова със срок утре', $deadlines, 'only interesting or preparing');

        $this->actingAs($this->person('all', 'Админ'))->get('/admin/dnes')->assertOk()->assertSee('data-block="sales-opportunities"', false);
        $this->actingAs($this->person('brand', 'Мария'))->get('/admin/dnes')->assertOk()->assertDontSee('data-block="sales-opportunities"', false);
        $this->actingAs($this->person('dev', 'Борислав'))->get('/admin/dnes')->assertOk()->assertDontSee('data-block="sales-deadlines"', false);
    }

    public function test_menu_badge_counts_new_opportunities_above_the_threshold(): void
    {
        $this->opp(['score' => 80]);
        $this->opp(['score' => 60]);
        $this->opp(['score' => 59]);
        $this->opp(['score' => 95, 'status' => 'interesting']);
        $this->assertSame(2, Badges::get('opportunities'));

        config(['creatium.opportunities.badge_score' => 85]);
        request()->attributes->remove('admin.badges');
        $this->assertSame(0, Badges::get('opportunities'));

        config(['creatium.opportunities.badge_score' => 60]);
        $html = $this->actingAs($this->person('sales'))->get('/admin/dnes')->getContent();
        $this->assertMatchesRegularExpression('/Възможности\s*<\/span>\s*<span class="adm-badge">2</', $html);
    }

    public function test_search_finds_opportunities_by_title_and_buyer(): void
    {
        $user = $this->person();
        $this->opp(['title' => 'Рамково споразумение за маркетинг', 'buyer' => 'Столична община']);
        $this->opp(['title' => 'Нещо друго', 'buyer' => 'Министерство на туризма']);

        $this->actingAs($user)->get('/admin/tarsene?q=маркетинг')->assertOk()->assertSee('Възможности')->assertSee('Рамково споразумение за маркетинг')->assertDontSee('Нещо друго');
        $this->actingAs($user)->get('/admin/tarsene?q=Министерство')->assertOk()->assertSee('Нещо друго');
    }

    public function test_statistics_show_opportunities_by_kind_and_status(): void
    {
        $this->opp(['status' => 'won']);
        $this->opp(['kind' => 'partner', 'source' => 'search', 'status' => 'new']);

        $html = $this->actingAs($this->person())->get('/admin/statistika')->assertOk()->getContent();
        $this->assertStringContainsString('Възможности по вид и статус', $html);
        $this->assertStringContainsString('Обществена поръчка', $html);
        $this->assertStringContainsString('Партньор', $html);
    }

    public function test_system_health_shows_the_collector_state_and_last_error(): void
    {
        $rows = fn () => collect(SystemHealth::checks()['Продажби'])->firstWhere('name', 'Възможности');

        config(['creatium.opportunities.enabled' => false]);
        $this->assertSame('off', $rows()['state']);

        config(['creatium.opportunities.enabled' => true]);
        $this->assertSame('ok', $rows()['state']);
        $this->assertStringContainsString('още не е пускан', $rows()['text']);

        Http::fake([
            'api.ted.europa.eu/*' => Http::response('', 500),
            'www.contractsfinder.service.gov.uk/*' => Http::response(file_get_contents(base_path('tests/Fixtures/opportunities/contracts-finder.json')), 200),
            'www.find-tender.service.gov.uk/*' => Http::response(['releases' => []], 200),
        ]);
        foreach (Collector::feeds() as $feed) {
            app(Collector::class)->run($feed);
        }
        $row = $rows();
        $this->assertSame('warn', $row['state']);
        $this->assertStringContainsString('Contracts Finder (Великобритания): ', $row['text']);
        $this->assertStringContainsString('върнати 3', $row['text']);
        $this->assertStringContainsString('Грешка: TED (ЕС): ', $row['text']);
        $this->assertStringContainsString('creatium:opportunities', $row['fix']);

        $this->actingAs($this->person('all', 'Админ'))->get('/admin/zdrave')->assertOk()->assertSee('Възможности')->assertSee('Грешка: TED');
    }

    public function test_refresh_now_runs_all_sources_and_reports_without_breaking_on_a_failure(): void
    {
        Http::fake([
            'api.ted.europa.eu/*' => Http::response(file_get_contents(base_path('tests/Fixtures/opportunities/ted.json')), 200),
            'www.contractsfinder.service.gov.uk/*' => Http::response('', 500),
            'www.find-tender.service.gov.uk/*' => Http::response(['releases' => []], 200),
        ]);
        Carbon::setTestNow(Carbon::parse('2026-11-15 12:00', 'UTC'));

        $this->actingAs($this->person())->post('/admin/opportunities/refresh')->assertSessionHas('error', fn ($m) => str_contains($m, 'contracts-finder: грешка'));
        $this->assertGreaterThan(0, Opportunity::where('source', 'ted')->count());
        $this->assertNotNull(Collector::state()['ted']['last_ok_at']);
    }

    // ---- Командата ----

    public function test_the_command_fetches_one_or_all_sources_and_never_sends_anything(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 12:00', 'UTC'));
        Http::fake([
            'api.ted.europa.eu/*' => Http::response(file_get_contents(base_path('tests/Fixtures/opportunities/ted.json')), 200),
            'www.contractsfinder.service.gov.uk/*' => Http::response(file_get_contents(base_path('tests/Fixtures/opportunities/contracts-finder.json')), 200),
            'www.find-tender.service.gov.uk/*' => Http::response(file_get_contents(base_path('tests/Fixtures/opportunities/find-a-tender.json')), 200),
        ]);
        \Illuminate\Support\Facades\Mail::fake();

        $this->artisan('creatium:opportunities', ['--source' => 'ted', '--days' => 14])->assertSuccessful()->expectsOutputToContain('TED');
        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => str_contains($r['query'], 'publication-date>=20261101'));
        $this->assertSame(0, Opportunity::where('source', '!=', 'ted')->count());
        $before = Opportunity::count();
        $this->assertGreaterThan(0, $before);

        $this->artisan('creatium:opportunities')->assertSuccessful();
        Http::assertSentCount(4);
        $this->assertGreaterThanOrEqual($before, Opportunity::count());

        $this->artisan('creatium:opportunities', ['--source' => 'nope'])->assertFailed();
        \Illuminate\Support\Facades\Mail::assertNothingSent();
        \Illuminate\Support\Facades\Mail::assertNothingQueued();
    }

    // ---- Партньори ----

    private function agency(string $country, array $values = []): OutreachProspect
    {
        static $n = 0;
        $n++;

        return OutreachProspect::create($values + [
            'email' => "hello{$n}@agency{$n}.test", 'company' => "Pixel Agency {$n}", 'website' => "https://agency{$n}.test", 'email_status' => 'published', 'status' => 'new',
            'country' => $country, 'language' => Markets::language($country), 'city' => 'Manchester', 'industry' => 'web_agency', 'phone' => '+44 161 000 0000',
            'findings' => [], 'site_checked_at' => now(),
        ]);
    }

    public function test_agency_categories_exist_for_the_business_search_in_every_market(): void
    {
        $this->assertSame(['agency', 'web_agency'], BusinessSearch::partnerCategories());
        $this->assertSame('Дигитални и маркетинг агенции', BusinessSearch::CATEGORIES['agency']['label']);
        $this->assertSame('Уеб агенции', BusinessSearch::CATEGORIES['web_agency']['label']);
        foreach (['gb', 'ie', 'nl'] as $market) {
            $this->assertNotNull(Markets::query($market, 'agency'), $market);
            $this->assertNotNull(Markets::query($market, 'web_agency'), $market);
        }
        $this->assertSame('digital marketing agency', Markets::query('gb', 'agency'));
        $this->assertSame('webdesign bureau', Markets::query('nl', 'web_agency'));
        $this->assertNotNull(Markets::query('bg', 'agency'));

        $user = $this->person();
        $this->actingAs($user)->get('/admin/outreach-search')->assertOk()->assertSee('Дигитални и маркетинг агенции')->assertSee('Уеб агенции');
        // Агенциите не са клиенти: не са в плана на автопилота за България.
        $this->assertNotContains('agency', config('creatium.outreach.autopilot.plan.bg.categories'));
    }

    public function test_a_found_agency_becomes_a_partner_opportunity_linked_to_the_prospect_and_is_rescored_not_reset(): void
    {
        $agency = $this->agency('gb', ['company_evidence' => 'Pixel Agency Ltd']);
        $normal = $this->agency('gb', ['industry' => 'dentist']);

        $this->assertNull(PartnerOpportunities::sync($normal), 'a dentist is a client, not a partner');
        $o = PartnerOpportunities::sync($agency);
        $this->assertSame('partner', $o->kind);
        $this->assertSame('search', $o->source);
        $this->assertSame('GB', $o->country);
        $this->assertSame($agency->id, $o->outreach_prospect_id);
        $this->assertSame('new', $o->status);
        $this->assertSame($agency->website, $o->url);
        $this->assertGreaterThanOrEqual(75, $o->score);
        $this->assertStringContainsString('Агенция партньор: Pixel Agency', $o->summaryLine());

        $o->update(['status' => 'interesting', 'note' => 'Пишахме им']);
        $agency->update(['email_status' => 'risky']);
        $again = PartnerOpportunities::sync($agency->fresh());
        $this->assertSame($o->id, $again->id);
        $this->assertSame(1, Opportunity::where('kind', 'partner')->count());
        $this->assertSame('interesting', $again->status);
        $this->assertSame('Пишахме им', $again->note);

        // Връзката към бизнеса е на страницата с подробности.
        $this->actingAs($this->person())->get("/admin/opportunities/{$o->id}")->assertOk()->assertSee('Pixel Agency')->assertSee('Партньорско писмо')->assertSee($agency->email);
    }

    public function test_the_partner_pitch_is_the_draft_for_agencies_and_the_channel_rules_still_apply(): void
    {
        $company = $this->agency('gb', ['company_evidence' => 'Pixel Agency Ltd']);
        $this->assertTrue(Drafts::prepare($company));
        $company->refresh();
        $this->assertSame(PartnerPitch::subject($company), $company->draft_subject);
        $this->assertStringContainsString('three person team in Bulgaria', $company->draft_observation);
        $this->assertStringContainsString('under your brand', $company->draft_observation);
        $this->assertStringContainsString('fixed per project', $company->draft_observation);
        $this->assertStringContainsString('/en/projects', $company->draft_observation);
        $this->assertStringContainsString('European time', $company->draft_observation);
        $this->assertStringContainsString('Писмото на английски предлага', Drafts::summaryBg($company));

        // Британска агенция без регистрирана фирма и всяка нидерландска: без писмо, само обаждане или LinkedIn (както за всички).
        foreach ([$this->agency('gb'), $this->agency('nl', ['company_evidence' => 'Pixel BV'])] as $blocked) {
            $this->assertTrue(Drafts::prepare($blocked));
            $blocked->refresh();
            $this->assertNull($blocked->draft_observation);
            $this->assertFalse($blocked->canBeEmailed());
        }
        // Ирландия е позволена.
        $ie = $this->agency('ie');
        $this->assertTrue(Drafts::prepare($ie));
        $this->assertNotNull($ie->fresh()->draft_observation);
        // Български агенции: няма английско писмо.
        $this->assertFalse(Drafts::prepare($this->agency('bg')));
    }

    public function test_the_partner_pitch_follows_the_owners_copy_rule(): void
    {
        $prospect = $this->agency('ie');
        $text = PartnerPitch::subject($prospect) . "\n" . PartnerPitch::body($prospect, 'https://creatiumlab.com/en/projects') . "\n" . PartnerPitch::summaryBg();
        // Без тирета (кратко, дълго, с интервали) и без двоеточия, освен в адрес.
        $withoutUrls = preg_replace('#https?://\S+#', '', PartnerPitch::subject($prospect) . "\n" . PartnerPitch::body($prospect, 'https://creatiumlab.com/en/projects'));
        $this->assertDoesNotMatchRegularExpression('/[–—]| - |:/u', $withoutUrls);
        $this->assertStringNotContainsString('free', mb_strtolower(PartnerPitch::subject($prospect)));
        $this->assertStringContainsString('https://creatiumlab.com/en/projects', $text);
        // Не обещава нищо измислено: няма клиенти, оценки или резултати.
        $this->assertDoesNotMatchRegularExpression('/\b(clients? like|reviews?|rated|guarantee|results?)\b/i', PartnerPitch::body($prospect));
    }

    public function test_the_collector_never_touches_partner_or_manual_opportunities(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 12:00', 'UTC'));
        $partner = $this->opp(['kind' => 'partner', 'source' => 'search', 'external_id' => 'prospect:1', 'deadline_at' => null, 'status' => 'interesting']);
        $manual = ManualEntry::add(['kind' => 'freelance', 'title' => 'Сайт за ресторант', 'url' => 'https://www.malt.com/p/1', 'origin' => 'Malt'], null)['opportunity'];
        Http::fake(['*' => Http::response(['notices' => [], 'releases' => []], 200)]);

        foreach (Collector::feeds() as $feed) {
            app(Collector::class)->run($feed);
        }
        $this->assertSame('interesting', $partner->fresh()->status);
        $this->assertSame('new', $manual->fresh()->status);
        $this->assertSame(2, Opportunity::count());
    }
}
