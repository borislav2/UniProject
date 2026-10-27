<?php

namespace Tests\Feature;

use App\Mail\OutreachFollowUpMail;
use App\Models\Category;
use App\Models\ClientEmail;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\OutreachSuppression;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FollowUpAndStatsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CategorySeeder::class);
        $user = User::factory()->create(['name' => 'Борислав Костадинов']);
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        return $user;
    }

    private function configure(): void
    {
        config(['creatium.outreach.from_address' => 'info@creativemoa.site', 'mail.mailers.outreach.host' => 'smtp.example.test', 'creatium.outreach.daily_limit' => 5, 'creatium.outreach.followup_after_days' => 4]);
    }

    private function sentProspect(string $email, int $daysAgo, array $values = []): OutreachProspect
    {
        $prospect = OutreachProspect::create($values + [
            'email' => $email, 'company' => 'Бизнес ' . $email, 'website' => 'https://' . explode('@', $email)[1],
            'email_status' => 'published', 'status' => 'sent', 'sent_at' => now()->subDays($daysAgo),
            'findings' => [['title' => 'Липсва описание в резултатите на Google', 'detail' => '']],
        ]);
        ClientEmail::create(['outreach_prospect_id' => $prospect->id, 'template' => 'outreach', 'to_email' => $email, 'subject' => 'Резултати от проверка на сайта на ' . $prospect->company, 'data' => [], 'sent_at' => $prospect->sent_at]);

        return $prospect;
    }

    public function test_only_silent_prospects_after_the_wait_are_due_for_a_follow_up(): void
    {
        $this->configure();
        $due = $this->sentProspect('info@due.bg', 5);
        $this->sentProspect('info@recent.bg', 2);
        $this->sentProspect('info@replied.bg', 6, ['status' => 'replied']);
        $this->sentProspect('info@done.bg', 6, ['followup_sent_at' => now()->subDay()]);
        $this->sentProspect('info@gone.bg', 6);
        OutreachSuppression::add('info@gone.bg', 'unsubscribed');

        $this->assertSame([$due->id], OutreachProspect::dueForFollowUp()->pluck('id')->all());
    }

    public function test_follow_up_is_sent_once_as_a_reply_with_tracking_and_unsubscribe(): void
    {
        Mail::fake();
        $this->configure();
        $admin = $this->admin();
        $prospect = $this->sentProspect('info@lozata.bg', 5);

        $this->actingAs($admin)->get('/admin/outreach')->assertSee('За напомняне (1)')->assertSee('Напомни на всички (1)');
        $this->actingAs($admin)->post("/admin/outreach/{$prospect->id}/follow-up")->assertSessionHas('success');

        Mail::assertSent(OutreachFollowUpMail::class, function (OutreachFollowUpMail $mail) use ($prospect) {
            $html = $mail->render();
            $this->assertStringContainsString('Пиша ви отново накратко', $html);
            $this->assertStringContainsString('Липсва описание в резултатите на Google', $html);
            $this->assertStringContainsString("/o/{$prospect->id}/booking?signature=", $html);
            $this->assertStringContainsString('отпишете се тук', $html);

            return $mail->hasTo('info@lozata.bg') && $mail->mailSubject === 'Re: Резултати от проверка на сайта на Бизнес info@lozata.bg'
                && $mail->mailer === 'outreach';
        });
        $this->assertNotNull($prospect->fresh()->followup_sent_at);
        $this->assertSame(1, ClientEmail::where('template', 'outreach-followup')->whereNotNull('sent_at')->count());

        // Второ напомняне не се праща.
        $this->actingAs($admin)->post("/admin/outreach/{$prospect->id}/follow-up")->assertSessionHas('send_error');
        Mail::assertSentCount(1);
    }

    public function test_follow_up_all_respects_the_daily_limit(): void
    {
        Mail::fake();
        $this->configure();
        config(['creatium.outreach.daily_limit' => 2]);
        $admin = $this->admin();
        foreach (['a', 'b', 'c'] as $name) {
            $this->sentProspect("info@{$name}.bg", 5);
        }

        $this->actingAs($admin)->post('/admin/outreach/follow-up-all')->assertRedirect('/admin/outreach');

        Mail::assertSentCount(2);
        $this->assertSame(1, OutreachProspect::dueForFollowUp()->count());
    }

    public function test_follow_up_is_blocked_without_the_outreach_mailbox(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $prospect = $this->sentProspect('info@lozata.bg', 5);

        $this->actingAs($admin)->post("/admin/outreach/{$prospect->id}/follow-up")->assertSessionHas('send_error');
        Mail::assertNothingSent();
    }

    public function test_stats_show_the_funnels_and_lost_reasons(): void
    {
        $admin = $this->admin();
        $category = Category::first()->id;
        $won = Project::create(['name' => 'Запитване от А', 'description' => 'd', 'start_date' => now()->toDateString(), 'status' => 'In Progress', 'manager' => 'm', 'category_id' => $category, 'source' => 'outreach', 'stage' => 'won', 'lead_channel' => 'Писма до бизнеси']);
        Project::create(['name' => 'Запитване от Б', 'description' => 'd', 'start_date' => now()->toDateString(), 'status' => 'Cancelled', 'manager' => 'm', 'category_id' => $category, 'source' => 'website', 'stage' => 'lost', 'lost_reason' => 'Цена', 'lead_channel' => 'Google']);
        Project::create(['name' => 'Запитване от В', 'description' => 'd', 'start_date' => now()->toDateString(), 'status' => 'Planning', 'manager' => 'm', 'category_id' => $category, 'source' => 'website', 'lead_channel' => 'Google']);
        $this->sentProspect('info@a.bg', 3, ['clicked_at' => now(), 'last_clicked_at' => now(), 'click_count' => 1, 'last_click_link' => 'check', 'project_id' => $won->id]);
        $this->sentProspect('info@b.bg', 3);
        OutreachCall::create(['external_id' => 'c1', 'name' => 'Фирма', 'phone' => '0888111222', 'status' => 'interested', 'called_at' => now()]);
        OutreachCall::create(['external_id' => 'c2', 'name' => 'Фирма 2', 'phone' => '0888111223', 'status' => 'no_answer', 'called_at' => now()]);

        $this->actingAs($admin)->get('/admin/statistika')->assertOk()
            ->assertSeeInOrder(['Писма до бизнеси', 'Изпратени първи писма', '2', 'Кликнали', '1', '50%', 'Спечелени', '1'])
            ->assertSeeInOrder(['Обаждания', 'Обадени бизнеси', '2', 'Вдигнаха', '1', '50%', 'Интерес', '1'])
            ->assertSee('Писма до бизнеси')->assertSee('Google')
            ->assertSeeInOrder(['Защо губим', 'Цена', '1']);
        $this->actingAs($admin)->get('/admin/statistika?days=7')->assertOk()->assertSee('Последните 7 дни');
    }
}
