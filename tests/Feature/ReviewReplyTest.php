<?php

namespace Tests\Feature;

use App\Models\ReviewReply;
use App\Models\Role;
use App\Models\User;
use App\Support\Reviews\ReviewReplyWriter;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewReplyTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        return $user;
    }

    private function review(array $overrides = []): array
    {
        return $overrides + [
            'business_name' => 'Ресторант Лозата',
            'business_type' => 'ресторант',
            'reviewer_name' => 'Мария Петрова',
            'rating' => 2,
            'review_text' => 'Храната беше студена и чакахме 40 минути.',
            'tone' => 'friendly',
            'notes' => 'Наехме още един готвач.',
            'signature' => 'Иван, управител',
        ];
    }

    /** Фалшив Claude: запомня подканата и връща готов текст (или null като отказ). */
    private function fakeClaude(?string $answer): object
    {
        config(['services.anthropic.key' => 'test-key']);
        $fake = new class($answer) extends ReviewReplyWriter {
            public array $prompts = [];

            public function __construct(private ?string $answer) {}

            protected function askClaude(string $system, string $prompt): ?string
            {
                $this->prompts[] = $prompt;

                return $this->answer;
            }
        };
        $this->app->instance(ReviewReplyWriter::class, $fake);

        return $fake;
    }

    public function test_without_a_key_the_draft_comes_from_a_template(): void
    {
        config(['services.anthropic.key' => null]);
        $user = $this->admin();

        $this->actingAs($user)->get('/admin/reviews')->assertOk()->assertSee('Claude не е свързан')->assertSee('Отговори на отзиви');

        $response = $this->actingAs($user)->post('/admin/reviews', $this->review());
        $reply = ReviewReply::firstOrFail();
        $response->assertRedirect("/admin/reviews/{$reply->id}")->assertSessionHas('success');

        $this->assertSame('template', $reply->source);
        $this->assertStringStartsWith('Здравейте, Мария,', $reply->reply_text);
        $this->assertStringContainsString('съжаляваме', $reply->reply_text);
        $this->assertStringEndsWith('Иван, управител', $reply->reply_text);

        $this->actingAs($user)->get("/admin/reviews/{$reply->id}")->assertOk()->assertSee('Храната беше студена')->assertSee('Копирай');
    }

    public function test_with_a_key_claude_writes_the_draft_from_the_review_and_notes(): void
    {
        $fake = $this->fakeClaude("Здравейте, Мария, съжаляваме за чакането. Наехме още един готвач.\n\nИван, управител");
        $user = $this->admin();

        $this->actingAs($user)->post('/admin/reviews', $this->review())->assertSessionHas('success');

        $reply = ReviewReply::firstOrFail();
        $this->assertSame('ai', $reply->source);
        $this->assertStringContainsString('Наехме още един готвач', $reply->reply_text);
        $this->assertStringContainsString("<review>\nХраната беше студена и чакахме 40 минути.\n</review>", $fake->prompts[0]);
        $this->assertStringContainsString('Rating: 2 out of 5', $fake->prompts[0]);
        $this->assertStringContainsString("Owner's notes: Наехме още един готвач.", $fake->prompts[0]);
    }

    public function test_a_declined_request_falls_back_to_the_template_with_a_warning(): void
    {
        $this->fakeClaude(null);

        $this->actingAs($this->admin())->post('/admin/reviews', $this->review(['rating' => 5]))->assertSessionHas('send_error');

        $reply = ReviewReply::firstOrFail();
        $this->assertSame('template', $reply->source);
        $this->assertStringContainsString('благодарим', $reply->reply_text);
    }

    public function test_the_draft_can_be_edited_and_the_form_validates(): void
    {
        config(['services.anthropic.key' => null]);
        $user = $this->admin();

        $this->actingAs($user)->post('/admin/reviews', ['business_name' => 'X', 'tone' => 'friendly'])
            ->assertSessionHasErrors(['review_text', 'rating']);

        $this->actingAs($user)->post('/admin/reviews', $this->review());
        $reply = ReviewReply::firstOrFail();

        $this->actingAs($user)->patch("/admin/reviews/{$reply->id}", ['reply_text' => 'Поправен текст'])->assertSessionHas('success');
        $this->assertSame('Поправен текст', $reply->fresh()->reply_text);

        $this->actingAs($user)->get("/admin/reviews?from={$reply->id}")->assertSee('value="Ресторант Лозата"', false);
    }

    public function test_guests_cannot_use_it(): void
    {
        $this->get('/admin/reviews')->assertRedirect('/login');
        $this->post('/admin/reviews', $this->review())->assertRedirect('/login');
    }
}
