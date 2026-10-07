<?php

namespace Tests\Feature;

use App\Mail\MailTestMail;
use App\Models\Category;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InquiryInboxTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        return $user;
    }

    private function lead(array $overrides = []): Project
    {
        $this->seed(CategorySeeder::class);

        return Project::create(array_merge([
            'name' => 'Запитване от Мария Петрова', 'description' => "Търся сайт за салона.\nМоже ли оферта?", 'start_date' => now(),
            'status' => 'Planning', 'manager' => 'Неразпределен', 'category_id' => Category::first()->id,
            'client_phone' => '0888 123 456', 'service' => 'Изработка на сайт', 'business_size' => 'Малък', 'source' => 'website',
            'lead_channel' => 'Google (органично)',
        ], $overrides));
    }

    public function test_guests_cannot_see_the_inbox(): void
    {
        $this->get('/admin/inquiries')->assertRedirect('/login');
    }

    public function test_inbox_lists_website_messages_in_full_and_ignores_other_projects(): void
    {
        $this->lead();
        $this->lead(['name' => 'Клиентски проект', 'description' => 'Вътрешна работа.', 'source' => 'manual']);

        $this->actingAs($this->admin())->get('/admin/inquiries')
            ->assertOk()
            ->assertSee('Мария Петрова')
            ->assertSee('Търся сайт за салона.')
            ->assertSee('Може ли оферта?')
            ->assertSee('0888 123 456')
            ->assertSee('Изработка на сайт')
            ->assertSee('Бизнес: Малък')
            ->assertSee('Ново')
            ->assertDontSee('Клиентски проект');
    }

    public function test_unread_count_is_shown_in_the_menu_and_messages_can_be_marked_read(): void
    {
        $lead = $this->lead();
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Запитвания');

        $this->actingAs($admin)->post("/admin/inquiries/{$lead->id}/read")->assertRedirect();
        $this->assertNotNull($lead->fresh()->read_at);
        $this->get('/admin/inquiries?filter=unread')->assertSee('Няма непрочетени запитвания.');

        $this->post("/admin/inquiries/{$lead->id}/unread")->assertRedirect();
        $this->assertNull($lead->fresh()->read_at);
    }

    public function test_all_messages_can_be_marked_read_at_once(): void
    {
        $this->lead();
        $this->lead(['name' => 'Запитване от Иван']);

        $this->actingAs($this->admin())->post('/admin/inquiries/read-all')->assertRedirect();

        $this->assertSame(0, Project::whereNull('read_at')->count());
    }

    public function test_only_website_messages_can_be_marked_read(): void
    {
        $project = $this->lead(['source' => 'manual']);

        $this->actingAs($this->admin())->post("/admin/inquiries/{$project->id}/read")->assertNotFound();
    }

    public function test_messages_whose_email_was_not_sent_are_flagged(): void
    {
        $this->lead();
        $this->lead(['name' => 'Запитване от Иван', 'email_sent_at' => now()]);

        $this->actingAs($this->admin())->get('/admin/inquiries')
            ->assertSee('Имейлът до hello@creatiumlab.com не е изпратен')
            ->assertSee('изпратен ' . now()->format('d.m.Y'));
    }

    public function test_warning_is_shown_while_mail_is_only_logged(): void
    {
        $admin = $this->admin();

        config(['mail.default' => 'log']);
        $this->actingAs($admin)->get('/admin/inquiries')->assertSee('Имейл известията не са настроени');

        config(['mail.default' => 'smtp']);
        $this->get('/admin/inquiries')->assertDontSee('Имейл известията не са настроени');
    }

    public function test_test_mail_command_sends_to_the_notification_address(): void
    {
        Mail::fake();

        $this->artisan('creatium:test-mail')->assertSuccessful();

        Mail::assertSent(MailTestMail::class, fn ($mail) => $mail->hasTo('hello@creatiumlab.com'));
    }

    public function test_test_mail_command_rejects_a_bad_address_and_reports_failures(): void
    {
        $this->artisan('creatium:test-mail', ['to' => 'not-an-email'])->assertFailed();

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        $this->artisan('creatium:test-mail')->assertFailed();
    }

    public function test_test_mail_command_explains_a_rejected_google_login(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Expected response code "235" but got code "535", with message "535-5.7.8 Username and Password not accepted"'));

        $this->artisan('creatium:test-mail')
            ->expectsOutputToContain('ПАРОЛА НА ПРИЛОЖЕНИЕ')
            ->expectsOutputToContain('псевдоним')
            ->assertFailed();
    }
}
