<?php

namespace Tests\Feature;

use App\Models\LinkedinProspect;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\TeamActivity;
use App\Support\Admin\Today;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** LinkedIn потенциални клиенти (ноември 2026): ръчно търсене, следене в админа, без връзка с LinkedIn. */
class LinkedinProspectsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $name = 'Борислав', string $role = 'developer'): User
    {
        if (! Role::exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CategorySeeder::class);
        }
        $user = User::factory()->create(['name' => $name]);
        $user->roles()->attach(Role::where('slug', $role)->value('id'));

        return $user;
    }

    private function prospect(array $values = []): LinkedinProspect
    {
        return LinkedinProspect::create($values + [
            'profile_url' => 'https://www.linkedin.com/in/ivan-petrov-' . uniqid(),
            'kind' => 'person', 'name' => 'Иван Петров', 'company' => 'Фирма ЕООД',
        ]);
    }

    public function test_urls_are_normalized_and_foreign_urls_rejected(): void
    {
        foreach ([
            'https://www.linkedin.com/in/Ivan-Petrov/' => ['https://www.linkedin.com/in/ivan-petrov', 'person'],
            'linkedin.com/in/ivan-petrov?utm_source=share&trk=abc' => ['https://www.linkedin.com/in/ivan-petrov', 'person'],
            'HTTP://BG.LinkedIn.com/in/ivan-petrov/details/experience/#top' => ['https://www.linkedin.com/in/ivan-petrov', 'person'],
            'https://www.linkedin.com/company/Firma-EOOD/about/' => ['https://www.linkedin.com/company/firma-eood', 'company'],
        ] as $raw => [$url, $kind]) {
            $result = LinkedinProspect::normalizeUrl($raw);
            $this->assertSame($url, $result['url'], $raw);
            $this->assertSame($kind, $result['kind'], $raw);
        }

        foreach (['', 'ivan', 'https://example.com/in/ivan', 'https://notlinkedin.com/in/ivan', 'https://linkedin.com.evil.com/in/ivan',
            'https://www.linkedin.com/feed/', 'https://www.linkedin.com/in/', 'https://www.linkedin.com/jobs/view/1', 'ftp://linkedin.com/in/x', 'javascript:alert(1)'] as $bad) {
            $this->assertNull(LinkedinProspect::normalizeUrl($bad), $bad);
        }
    }

    public function test_single_add_normalizes_derives_the_name_and_skips_duplicates(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->post('/admin/linkedin', ['profile_url' => 'linkedin.com/in/Maria-Georgieva-1a2b3c4d/?trk=x', 'company' => 'Салон Ирис', 'website' => 'https://iris.bg'])
            ->assertRedirect('/admin/linkedin')->assertSessionHasNoErrors();

        $p = LinkedinProspect::firstOrFail();
        $this->assertSame('https://www.linkedin.com/in/maria-georgieva-1a2b3c4d', $p->profile_url);
        $this->assertSame('person', $p->kind);
        $this->assertSame('Maria Georgieva', $p->name);
        $this->assertSame('found', $p->status);
        $this->assertSame($user->id, $p->added_by);

        $this->actingAs($user)->post('/admin/linkedin', ['profile_url' => 'https://www.linkedin.com/in/maria-georgieva-1a2b3c4d'])->assertSessionHasErrors('profile_url');
        $this->actingAs($user)->post('/admin/linkedin', ['profile_url' => 'https://example.com/in/ivan'])->assertSessionHasErrors('profile_url');
        $this->actingAs($user)->post('/admin/linkedin', ['profile_url' => 'https://www.linkedin.com/company/firma', 'website' => 'javascript:alert(1)'])->assertSessionHasErrors('website');
        $this->assertSame(1, LinkedinProspect::count());
    }

    public function test_bulk_add_takes_optional_name_and_company_and_reports_duplicates_and_bad_lines(): void
    {
        $user = $this->admin();
        $this->prospect(['profile_url' => 'https://www.linkedin.com/in/stoyan']);

        $lines = "https://www.linkedin.com/in/ivan-petrov | Иван Петров | Фирма ЕООД\n"
            . "\n"
            . "https://www.linkedin.com/company/firma-eood\n"
            . "https://www.linkedin.com/in/stoyan/\n"
            . "https://www.linkedin.com/in/ivan-petrov?x=1\n"
            . "https://facebook.com/ivan\n";

        $response = $this->actingAs($user)->post('/admin/linkedin/bulk', ['lines' => $lines]);
        $response->assertRedirect('/admin/linkedin');
        $this->assertStringContainsString('Добавени: 2.', session('success'));
        $this->assertStringContainsString('Вече в списъка (2)', session('success'));
        $this->assertStringContainsString('Не са адреси от LinkedIn (1)', session('success'));

        $this->assertSame(3, LinkedinProspect::count());
        $ivan = LinkedinProspect::where('profile_url', 'https://www.linkedin.com/in/ivan-petrov')->firstOrFail();
        $this->assertSame(['Иван Петров', 'Фирма ЕООД'], [$ivan->name, $ivan->company]);
        $company = LinkedinProspect::where('profile_url', 'https://www.linkedin.com/company/firma-eood')->firstOrFail();
        $this->assertSame(['company', 'Firma Eood'], [$company->kind, $company->name]);
    }

    public function test_status_timestamps_are_set_the_first_time_and_never_cleared(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');
        $user = $this->admin();
        $p = $this->prospect();

        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['status' => 'invited'])->assertSessionHasNoErrors();
        $p->refresh();
        $this->assertSame('2026-11-04 08:00:00', $p->invited_at->toDateTimeString());
        $this->assertSame($user->id, $p->invited_by);
        $this->assertNull($p->connected_at);

        // Пропусната стъпка: „Писано“ попълва и „Приета покана“; първата покана не се пипа.
        Carbon::setTestNow('2026-11-06 10:00:00');
        $other = $this->admin('Мария');
        $this->actingAs($other)->patch("/admin/linkedin/{$p->id}", ['status' => 'messaged']);
        $p->refresh();
        $this->assertSame('2026-11-04 08:00:00', $p->invited_at->toDateTimeString());
        $this->assertSame($user->id, $p->invited_by);
        $this->assertSame('2026-11-06 10:00:00', $p->connected_at->toDateTimeString());
        $this->assertSame('2026-11-06 10:00:00', $p->messaged_at->toDateTimeString());
        $this->assertSame($other->id, $p->messaged_by);
        $this->assertNull($p->replied_at);

        // Назад и отново напред: нищо не се изчиства или пренаписва; „Не се интересува“ също не пипа времената.
        Carbon::setTestNow('2026-11-09 10:00:00');
        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['status' => 'found']);
        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['status' => 'invited']);
        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['status' => 'not_interested']);
        $p->refresh();
        $this->assertSame('not_interested', $p->status);
        $this->assertSame('2026-11-04 08:00:00', $p->invited_at->toDateTimeString());
        $this->assertSame('2026-11-06 10:00:00', $p->messaged_at->toDateTimeString());
        $this->assertNull($p->replied_at);

        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['status' => 'replied']);
        $this->assertSame('2026-11-09 10:00:00', $p->fresh()->replied_at->toDateTimeString());

        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['status' => 'bogus'])->assertSessionHasErrors('status');
        Carbon::setTestNow();
    }

    public function test_update_keeps_other_fields_when_only_a_status_is_sent_and_saves_note_date_and_owner(): void
    {
        $user = $this->admin();
        $p = $this->prospect(['note' => 'Стара бележка', 'city' => 'Русе']);

        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['status' => 'invited']);
        $this->assertSame(['Стара бележка', 'Русе'], [$p->fresh()->note, $p->fresh()->city]);

        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['status' => 'invited', 'note' => 'Пиша във вторник', 'next_action_at' => '2026-11-10', 'assigned_to' => $user->id, 'title' => 'Управител']);
        $p->refresh();
        $this->assertSame('Пиша във вторник', $p->note);
        $this->assertSame('2026-11-10', $p->next_action_at->toDateString());
        $this->assertSame($user->id, $p->assigned_to);
        $this->assertSame('Управител', $p->title);

        $this->actingAs($user)->patch("/admin/linkedin/{$p->id}", ['note' => '', 'next_action_at' => '', 'assigned_to' => '']);
        $p->refresh();
        $this->assertNull($p->note);
        $this->assertNull($p->next_action_at);
        $this->assertNull($p->assigned_to);

        $this->actingAs($user)->delete("/admin/linkedin/{$p->id}")->assertRedirect();
        $this->assertSame(0, LinkedinProspect::count());
    }

    public function test_list_filters_by_status_mine_due_and_search(): void
    {
        Carbon::setTestNow('2026-11-04 12:00:00');
        $me = $this->admin('Борислав');
        $other = $this->admin('Мария');
        $this->prospect(['name' => 'Мой Контакт', 'assigned_to' => $me->id, 'next_action_at' => '2026-11-03']);
        $this->prospect(['name' => 'Днешен Контакт', 'next_action_at' => '2026-11-04', 'assigned_to' => $other->id]);
        $this->prospect(['name' => 'Утрешен Контакт', 'next_action_at' => '2026-11-05']);
        $this->prospect(['name' => 'Покана Контакт', 'status' => 'invited', 'city' => 'Варна']);
        $this->prospect(['name' => 'Отказан Контакт', 'status' => 'not_interested', 'next_action_at' => '2026-11-01']);

        $this->actingAs($me)->get('/admin/linkedin')->assertOk()->assertSee('Мой Контакт')->assertSee('Утрешен Контакт')->assertSee('LinkedIn');
        $this->actingAs($me)->get('/admin/linkedin?who=me')->assertSee('Мой Контакт')->assertDontSee('Днешен Контакт');
        $this->actingAs($me)->get('/admin/linkedin?due=1')->assertSee('Мой Контакт')->assertSee('Днешен Контакт')
            ->assertDontSee('Утрешен Контакт')->assertDontSee('Отказан Контакт');
        $this->actingAs($me)->get('/admin/linkedin?status=invited')->assertSee('Покана Контакт')->assertDontSee('Мой Контакт');
        $this->actingAs($me)->get('/admin/linkedin?q=' . urlencode('Варна'))->assertSee('Покана Контакт')->assertDontSee('Мой Контакт');
        $this->actingAs($me)->get('/admin/linkedin?q=' . urlencode('Фирма'))->assertSee('Мой Контакт');
        Carbon::setTestNow();
    }

    public function test_page_shows_todays_invites_and_messages_against_the_goal_and_the_tab(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');
        config(['creatium.outreach.linkedin_daily_invites' => 10]);
        $user = $this->admin();
        $a = $this->prospect();
        $b = $this->prospect();
        $this->actingAs($user)->patch("/admin/linkedin/{$a->id}", ['status' => 'invited']);
        $this->actingAs($user)->patch("/admin/linkedin/{$b->id}", ['status' => 'messaged']);
        // Вчерашна покана не се брои днес.
        $this->prospect(); // още „Намерен“: показва се бутонът за следващата стъпка
        $this->prospect(['status' => 'invited'])->forceFill(['invited_at' => now()->subDay()])->save();

        $this->assertSame(['invites' => 2, 'messages' => 1, 'goal' => 10], LinkedinProspect::todayStats());
        $this->actingAs($user)->get('/admin/linkedin')->assertSee('Днес: 2')->assertSee('от 10 покани')
            ->assertSee('Изпратих покана');
        $this->actingAs($user)->get('/admin/outreach')->assertSee(route('admin.linkedin.index'), false);
        Carbon::setTestNow();
    }

    public function test_to_lead_creates_one_linkedin_lead_and_a_second_click_opens_the_same(): void
    {
        $user = $this->admin();
        $p = $this->prospect([
            'profile_url' => 'https://www.linkedin.com/in/ivan-petrov', 'title' => 'Управител', 'city' => 'Русе',
            'email' => 'ivan@firma.bg', 'phone' => '0888123456', 'website' => 'https://firma.bg', 'note' => 'Иска сайт', 'assigned_to' => $user->id,
        ]);

        $response = $this->actingAs($user)->post("/admin/linkedin/{$p->id}/lead");
        $lead = Project::firstOrFail();
        $response->assertRedirect(route('admin.projects.show', $lead));

        $this->assertSame('outreach', $lead->source);
        $this->assertSame('contacted', $lead->stage);
        $this->assertSame('LinkedIn', $lead->lead_channel);
        $this->assertSame(['ivan@firma.bg', '0888123456', 'https://firma.bg', $user->id], [$lead->client_email, $lead->client_phone, $lead->website_url, $lead->assigned_to]);
        $this->assertStringContainsString('https://www.linkedin.com/in/ivan-petrov', $lead->description);
        $this->assertStringContainsString('Иска сайт', $lead->description);
        $this->assertSame($lead->id, $p->fresh()->project_id);

        $this->actingAs($user)->post("/admin/linkedin/{$p->id}/lead")->assertRedirect(route('admin.projects.show', $lead));
        $this->assertSame(1, Project::count());
        $this->actingAs($user)->get('/admin/linkedin')->assertSee(route('admin.projects.show', $lead), false);
    }

    public function test_today_lists_due_linkedin_contacts_for_the_selected_person_and_counts_them(): void
    {
        Carbon::setTestNow('2026-11-04 12:00:00');
        $me = $this->admin('Борислав');
        $other = $this->admin('Мария');
        $this->prospect(['name' => 'Моя Просрочена', 'assigned_to' => $me->id, 'next_action_at' => '2026-11-02']);
        $this->prospect(['name' => 'Без Човек Днес', 'next_action_at' => '2026-11-04']);
        $this->prospect(['name' => 'На Мария Днес', 'assigned_to' => $other->id, 'next_action_at' => '2026-11-04']);
        $this->prospect(['name' => 'За Утре', 'assigned_to' => $me->id, 'next_action_at' => '2026-11-05']);

        $this->actingAs($me)->get('/admin/dnes')->assertOk()
            ->assertSee('LinkedIn: време е да напишете')->assertSee('Моя Просрочена')->assertSee('Без Човек Днес')
            ->assertDontSee('На Мария Днес')->assertDontSee('За Утре')->assertSee('due=1', false);
        $this->actingAs($me)->get('/admin/dnes?user=all')->assertSee('На Мария Днес');

        $this->assertSame(2, Today::urgentCountFor($me->id));
        $this->assertSame(2, Today::urgentCountFor($other->id));
        $this->assertSame(3, Today::urgentCount());
        Carbon::setTestNow();
    }

    public function test_team_activity_counts_invites_and_messages_per_person(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');
        $me = $this->admin('Борислав');
        $other = $this->admin('Мария');
        $a = $this->prospect(['name' => 'Първи']);
        $b = $this->prospect(['name' => 'Втори']);
        $this->actingAs($me)->patch("/admin/linkedin/{$a->id}", ['status' => 'invited']);
        $this->actingAs($me)->patch("/admin/linkedin/{$b->id}", ['status' => 'invited']);
        $this->actingAs($other)->patch("/admin/linkedin/{$a->id}", ['status' => 'messaged']);

        $from = TeamActivity::startOfToday();
        $mine = TeamActivity::counts($me->id, $from);
        $theirs = TeamActivity::counts($other->id, $from);
        $this->assertSame([2, 0], [$mine['linkedin_invites'], $mine['linkedin_messages']]);
        $this->assertSame([0, 1], [$theirs['linkedin_invites'], $theirs['linkedin_messages']]);
        $all = TeamActivity::counts(null, $from);
        $this->assertSame([2, 1], [$all['linkedin_invites'], $all['linkedin_messages']]);

        $this->actingAs($me)->get('/admin/dnes')->assertSee('Покани в LinkedIn')->assertSee('Покана в LinkedIn');
        $this->actingAs($me)->get('/admin/dnes?user=all')->assertSee('Съобщение в LinkedIn');
        $this->actingAs($me)->get('/admin')->assertOk()->assertSee('LinkedIn');
        Carbon::setTestNow();
    }

    public function test_search_finds_linkedin_prospects_by_name_company_and_profile_url(): void
    {
        $user = $this->admin();
        $this->prospect(['profile_url' => 'https://www.linkedin.com/in/zlatina-dimitrova', 'name' => 'Златина Димитрова', 'company' => 'Студио Лотос']);

        foreach (['Златина', 'Лотос', 'zlatina-dimitrova'] as $q) {
            $this->actingAs($user)->get('/admin/tarsene?q=' . urlencode($q))->assertOk()->assertSee('LinkedIn')->assertSee('Златина Димитрова');
        }
        $this->actingAs($user)->get('/admin/tarsene?q=' . urlencode('Няма такъв'))->assertDontSee('Златина Димитрова');
    }

    public function test_stats_show_the_linkedin_funnel_for_the_period(): void
    {
        Carbon::setTestNow('2026-11-04 08:00:00');
        $user = $this->admin();
        $a = $this->prospect();
        $b = $this->prospect();
        $this->prospect();
        $old = $this->prospect();
        $old->forceFill(['created_at' => now()->subDays(60)])->save();
        $this->actingAs($user)->patch("/admin/linkedin/{$a->id}", ['status' => 'replied']);
        $this->actingAs($user)->patch("/admin/linkedin/{$b->id}", ['status' => 'invited']);
        $lead = \App\Support\Admin\LeadConverter::fromLinkedin($a->fresh(), $user);
        $lead->forceFill(['stage' => 'won'])->save();

        $response = $this->actingAs($user)->get('/admin/statistika?days=30');
        $response->assertOk()->assertSee('Добавени контакти')->assertSee('Спечелени');
        $funnel = collect($response->viewData('linkedin'))->pluck(1, 0)->all();
        $this->assertSame([
            'Добавени контакти' => 3, 'Покана изпратена' => 2, 'Приета покана' => 1, 'Писано' => 1, 'Отговорили' => 1, 'Запитване' => 1, 'Спечелени' => 1,
        ], $funnel);
        Carbon::setTestNow();
    }

    public function test_permissions_follow_the_outreach_pages(): void
    {
        $p = $this->prospect();

        $this->get('/admin/linkedin')->assertRedirect('/login');
        $this->post('/admin/linkedin', ['profile_url' => 'linkedin.com/in/x'])->assertRedirect('/login');
        $this->patch("/admin/linkedin/{$p->id}", ['status' => 'invited'])->assertRedirect('/login');
        $this->post("/admin/linkedin/{$p->id}/lead")->assertRedirect('/login');

        $nobody = User::factory()->create();
        $this->actingAs($nobody)->get('/admin/linkedin')->assertRedirect(route('home'));
        $this->actingAs($nobody)->delete("/admin/linkedin/{$p->id}")->assertRedirect(route('home'));
        $this->assertSame(1, LinkedinProspect::count());

        // Всяка от трите роли има достъп, както до останалите „Потенциални клиенти“.
        foreach (['admin', 'project-manager', 'developer'] as $role) {
            $this->actingAs($this->admin('Човек ' . $role, $role))->get('/admin/linkedin')->assertOk();
        }
    }

    public function test_privacy_pages_mention_linkedin_data(): void
    {
        $this->get('/poveritelnost')->assertOk()->assertSee('LinkedIn');
        $this->get('/en/privacy')->assertOk()->assertSee('LinkedIn');
    }
}
