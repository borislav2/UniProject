<?php

namespace Tests\Feature;

use App\Mail\ConsultationBookedMail;
use App\Mail\NewLeadMail;
use App\Models\Project;
use App\Support\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Friday afternoon in Sofia: the next working day is Monday.
        $this->travelTo(Carbon::parse('2026-10-16 12:00', 'UTC'));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Иван Иванов', 'phone' => '0888123456', 'email' => 'ivan@example.bg',
            'service' => 'new-system', 'consent' => '1',
        ], $overrides);
    }

    public function test_days_start_tomorrow_and_skip_weekends(): void
    {
        $days = Booking::days();

        $this->assertCount(config('creatium.booking.days'), $days);
        $this->assertSame('2026-10-19', $days[0]['date']->format('Y-m-d'));
        foreach ($days as $day) {
            $this->assertFalse($day['date']->isWeekend());
            $this->assertSame(config('creatium.booking.times'), array_column($day['slots'], 'time'));
        }
    }

    public function test_booking_a_slot_saves_it_and_emails_the_client_and_the_team(): void
    {
        Mail::fake();

        $this->post('/kontakti', $this->payload(['consultation' => '2026-10-19 11:00']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Получихме запитването ви. Ще ви се обадим на понеделник, 19 октомври, 11:00 за консултацията.');

        $lead = Project::firstOrFail();
        $this->assertSame('2026-10-19 08:00:00', $lead->consultation_at->utc()->format('Y-m-d H:i:s'));

        Mail::assertSent(ConsultationBookedMail::class, function ($mail) {
            $html = $mail->render();
            $this->assertStringContainsString('calendar.google.com/calendar/render', $html);
            $this->assertStringContainsString('20261019T080000Z%2F20261019T083000Z', $html);

            return $mail->hasTo('ivan@example.bg');
        });
        Mail::assertSent(NewLeadMail::class, fn ($mail) => str_contains($mail->render(), 'понеделник, 19 октомври, 11:00'));

        // The slot is now taken for everyone else.
        $this->flushSession();
        $this->assertMatchesRegularExpression('/value="2026-10-19 11:00"[^>]*\sdisabled/', $this->get('/kontakti')->getContent());
        $this->assertDoesNotMatchRegularExpression('/value="2026-10-19 12:00"[^>]*\sdisabled/', $this->get('/kontakti')->getContent());
        $this->post('/kontakti', $this->payload(['consultation' => '2026-10-19 11:00']))->assertSessionHasErrors('consultation');
    }

    public function test_slots_outside_the_offer_are_rejected_and_the_consultation_is_optional(): void
    {
        Mail::fake();

        foreach (['2026-10-17 11:00', '2026-10-19 13:00', '2026-10-16 16:00', 'утре'] as $slot) {
            $this->post('/kontakti', $this->payload(['consultation' => $slot]))->assertSessionHasErrors('consultation');
        }

        $this->post('/kontakti', $this->payload())->assertSessionHasNoErrors();
        $this->assertNull(Project::firstOrFail()->consultation_at);
        Mail::assertNotSent(ConsultationBookedMail::class);
    }

    public function test_admin_inquiries_show_the_consultation(): void
    {
        Mail::fake();
        $this->post('/kontakti', $this->payload(['consultation' => '2026-10-20 14:00']));
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $admin = \App\Models\User::factory()->create();
        $admin->roles()->attach(\App\Models\Role::where('slug', 'admin')->value('id'));

        $this->actingAs($admin)->get('/admin/inquiries')->assertSee('Консултация')->assertSee('вторник, 20 октомври, 14:00')->assertSee('calendar.google.com', false);
    }
}
