<?php

namespace Tests\Feature;

use App\Mail\NewLeadMail;
use App\Models\ChatConversation;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\Chat\ChatAssistant;
use App\Support\Chat\ChatLeads;
use App\Support\Chat\SiteKnowledge;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    /** Фалшив Claude: връща подадените отговори един по един и запомня какво е получил. */
    private function fakeClaude(array $responses): object
    {
        config(['services.anthropic.key' => 'test-key']);
        $fake = new class($responses) extends ChatAssistant {
            public array $calls = [];

            public function __construct(private array $responses) {}

            protected function askClaude(array $system, array $messages): array
            {
                $this->calls[] = ['system' => $system, 'messages' => $messages];

                return array_shift($this->responses);
            }
        };
        $this->app->instance(ChatAssistant::class, $fake);

        return $fake;
    }

    private function send(string $message, ?string $conversation = null, string $locale = 'bg')
    {
        return $this->postJson('/chat', ['message' => $message, 'conversation' => $conversation, 'locale' => $locale, 'page' => '/uslugi']);
    }

    public function test_widget_is_on_public_pages_and_can_be_turned_off(): void
    {
        $this->get('/')->assertOk()->assertSee('data-chat', false)->assertSee('Попитайте Крео');
        $this->get('/en')->assertSee('Ask Kreo');

        config(['creatium.chat.enabled' => false]);
        $this->get('/')->assertDontSee('data-chat-panel', false);
        $this->send('Здравейте')->assertNotFound();
    }

    public function test_without_a_key_it_answers_from_the_faq_and_saves_a_phone_as_an_inquiry(): void
    {
        Mail::fake();
        config(['services.anthropic.key' => null]);

        $first = $this->send('Колко време отнема изработката на сайта?')->assertOk()->json();
        $this->assertStringContainsString('1–2 седмици', $first['reply']);
        $this->assertFalse($first['lead']);

        $chat = ChatConversation::where('public_id', $first['conversation'])->firstOrFail();
        $this->assertSame('/uslugi', $chat->page);

        $second = $this->send('Обадете ми се на 0888 123 456, Иван', $first['conversation'])->assertOk()->json();
        $this->assertTrue($second['lead']);
        $lead = Project::where('service', ChatLeads::SERVICE)->firstOrFail();
        $this->assertSame('0888 123 456', $lead->client_phone);
        $this->assertSame('website', $lead->source);
        $this->assertSame($lead->id, $chat->fresh()->lead_id);
        Mail::assertSent(NewLeadMail::class);

        $this->getJson('/chat/' . $first['conversation'])->assertOk()->assertJsonCount(4, 'messages')
            ->assertJsonPath('messages.0.role', 'user')->assertJsonPath('messages.1.role', 'assistant');
    }

    public function test_claude_answers_saves_the_contact_with_the_tool_and_history_is_append_only(): void
    {
        Mail::fake();
        $thinking = ['type' => 'thinking', 'thinking' => '', 'signature' => 'sig-1'];
        $fake = $this->fakeClaude([
            ['stop' => 'end_turn', 'content' => [$thinking, ['type' => 'text', 'text' => 'Стартовият двигател е €450. Искате ли да ви се обадим?']]],
            ['stop' => 'tool_use', 'content' => [
                ['type' => 'thinking', 'thinking' => '', 'signature' => 'sig-2'],
                ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'save_contact', 'input' => ['name' => 'Мария', 'phone' => '+359 888 111 222', 'email' => '', 'business' => 'салон, Варна', 'need' => 'Иска сайт за салон.']],
            ]],
            ['stop' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'Благодарим, Мария! Ще ви се обадим до един работен ден.']]],
        ]);

        $first = $this->send('Колко струва сайт?')->assertOk()->json();
        $this->assertSame('Стартовият двигател е €450. Искате ли да ви се обадим?', $first['reply']);
        $this->assertStringContainsString('<site_info>', $fake->calls[0]['system'][0]['text']);
        $this->assertStringContainsString('Стартов двигател', $fake->calls[0]['system'][0]['text']);
        $this->assertSame(['type' => 'ephemeral'], $fake->calls[0]['system'][0]['cacheControl']);

        $second = $this->send('Да, Мария, +359 888 111 222, салон във Варна', $first['conversation'])->assertOk()->json();
        $this->assertTrue($second['lead']);
        $this->assertSame('Благодарим, Мария! Ще ви се обадим до един работен ден.', $second['reply']);

        // The second request starts with the first turn exactly as stored, thinking block included.
        $sent = $fake->calls[1]['messages'];
        $this->assertSame(['role' => 'user', 'content' => 'Колко струва сайт?'], $sent[0]);
        $this->assertSame($thinking, $sent[1]['content'][0]);
        // The tool result goes back with the id of the tool call.
        $this->assertSame('toolu_1', end($fake->calls[2]['messages'])['content'][0]['toolUseID']);

        $lead = Project::where('service', ChatLeads::SERVICE)->firstOrFail();
        $this->assertSame('Запитване от Мария', $lead->name);
        $this->assertSame('+359 888 111 222', $lead->client_phone);
        $this->assertStringContainsString('Иска сайт за салон.', $lead->description);
        $this->assertStringContainsString('Бизнес: салон, Варна', $lead->description);

        $history = ChatConversation::firstOrFail()->api_messages;
        $this->assertCount(6, $history);
        $this->assertSame('assistant', end($history)['role']);
    }

    public function test_a_refusal_or_the_daily_limit_falls_back_to_the_faq_without_touching_the_history(): void
    {
        $fake = $this->fakeClaude([['stop' => 'refusal', 'content' => []]]);
        $reply = $this->send('Колко време отнема изработката на сайта?')->assertOk()->json('reply');
        $this->assertStringContainsString('1–2 седмици', $reply);
        $this->assertNull(ChatConversation::firstOrFail()->api_messages);
        $this->assertCount(1, $fake->calls);

        config(['creatium.chat.daily_limit' => 0]);
        $this->send('Още един въпрос за цените на пакетите');
        $this->assertCount(1, $fake->calls, 'Over the daily limit Claude is not called');
    }

    public function test_long_conversations_are_capped(): void
    {
        config(['services.anthropic.key' => null, 'creatium.chat.max_messages' => 2]);
        $id = $this->send('Здравейте')->json('conversation');
        $this->send('Втори въпрос', $id);
        $this->assertStringContainsString('Разговорът стана дълъг', $this->send('Трети въпрос', $id)->json('reply'));
    }

    public function test_admin_sees_conversations_and_old_ones_are_deleted(): void
    {
        config(['services.anthropic.key' => null]);
        $id = $this->send('Колко струва сайт?')->json('conversation');
        $old = ChatConversation::create(['locale' => 'bg']);
        ChatConversation::whereKey($old->id)->update(['updated_at' => now()->subDays(31)]);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        $this->actingAs($user)->get('/admin/chats')->assertOk()->assertSee('Колко струва сайт?')->assertSee('Няма свързан AI');
        $this->assertModelMissing($old);
        $chat = ChatConversation::where('public_id', $id)->firstOrFail();
        $this->actingAs($user)->get('/admin/chats/' . $chat->id)->assertOk()->assertSee('от честите въпроси (без AI)');
    }

    public function test_site_knowledge_follows_the_language_and_the_privacy_policy_mentions_the_chat(): void
    {
        $this->assertStringContainsString('Стартов двигател', SiteKnowledge::text());
        app()->setLocale('en');
        $this->assertStringContainsString('Starter Engine', SiteKnowledge::text());

        $this->get('/poveritelnost')->assertSee('чата на сайта');
        $this->get('/en/privacy')->assertSee('chat on the site');
    }

    public function test_the_provider_follows_chat_provider_and_the_keys(): void
    {
        config(['services.anthropic.key' => 'a', 'services.mistral.key' => 'm', 'creatium.chat.provider' => 'auto']);
        $this->assertSame('anthropic', ChatAssistant::provider());
        config(['creatium.chat.provider' => 'mistral']);
        $this->assertSame('mistral', ChatAssistant::provider());
        config(['services.mistral.key' => null]);
        $this->assertNull(ChatAssistant::provider(), 'An explicit provider without its key means FAQ answers only');
        config(['services.anthropic.key' => null, 'services.mistral.key' => 'm', 'creatium.chat.provider' => 'auto']);
        $this->assertSame('mistral', ChatAssistant::provider());
    }

    public function test_mistral_answers_from_the_site_and_saves_the_contact_with_the_tool(): void
    {
        Mail::fake();
        config(['services.anthropic.key' => null, 'services.mistral.key' => 'm-key', 'services.mistral.model' => 'mistral-medium-latest', 'creatium.chat.provider' => 'auto']);
        Http::fake(['api.mistral.ai/*' => Http::sequence()
            ->push(['choices' => [['finish_reason' => 'stop', 'message' => ['role' => 'assistant', 'content' => 'Стартовият двигател е €450. Да ви се обадим ли?']]]])
            ->push(['choices' => [['finish_reason' => 'tool_calls', 'message' => ['role' => 'assistant', 'content' => '', 'tool_calls' => [
                ['id' => 'call_1', 'type' => 'function', 'function' => ['name' => 'save_contact', 'arguments' => json_encode(['name' => 'Мария', 'phone' => '0888 111 222', 'email' => '', 'business' => 'салон, Варна', 'need' => 'Иска сайт.'])]],
            ]]]]])
            ->push(['choices' => [['finish_reason' => 'stop', 'message' => ['role' => 'assistant', 'content' => [['type' => 'thinking', 'thinking' => [['type' => 'text', 'text' => 'вътрешно']]], ['type' => 'text', 'text' => 'Благодарим, Мария! Ще ви се обадим.']]]]]]),
        ]);

        $first = $this->send('Колко струва сайт?')->assertOk()->json();
        $this->assertSame('Стартовият двигател е €450. Да ви се обадим ли?', $first['reply']);
        $second = $this->send('Да, Мария, 0888 111 222, салон във Варна', $first['conversation'])->assertOk()->json();
        $this->assertSame('Благодарим, Мария! Ще ви се обадим.', $second['reply']);
        $this->assertTrue($second['lead']);

        $lead = Project::where('service', ChatLeads::SERVICE)->firstOrFail();
        $this->assertSame('0888 111 222', $lead->client_phone);
        $this->assertStringContainsString('Бизнес: салон, Варна', $lead->description);

        $requests = Http::recorded()->map(fn ($pair) => $pair[0]);
        $this->assertCount(3, $requests);
        $firstBody = $requests[0]->data();
        $this->assertSame('https://api.mistral.ai/v1/chat/completions', $requests[0]->url());
        $this->assertSame('Bearer m-key', $requests[0]->header('Authorization')[0]);
        $this->assertSame('mistral-medium-latest', $firstBody['model']);
        $this->assertSame('save_contact', $firstBody['tools'][0]['function']['name']);
        $this->assertSame('system', $firstBody['messages'][0]['role']);
        $this->assertStringContainsString('<site_info>', $firstBody['messages'][0]['content']);

        // The second turn carries the first exchange; the third the tool result for call_1.
        $secondBody = $requests[1]->data();
        $this->assertSame(['user', 'assistant', 'user'], array_column(array_slice($secondBody['messages'], 1), 'role'));
        $toolMessage = end($requests[2]->data()['messages']);
        $this->assertSame(['tool', 'call_1', 'save_contact'], [$toolMessage['role'], $toolMessage['tool_call_id'], $toolMessage['name']]);
        $this->assertNull(ChatConversation::firstOrFail()->api_messages, 'Mistral history comes from the visible messages');
    }

    public function test_a_mistral_error_falls_back_to_the_faq(): void
    {
        config(['services.anthropic.key' => null, 'services.mistral.key' => 'm-key', 'creatium.chat.provider' => 'mistral']);
        Http::fake(['api.mistral.ai/*' => Http::response(['message' => 'Unauthorized'], 401)]);

        $reply = $this->send('Колко време отнема изработката на сайта?')->assertOk()->json('reply');
        $this->assertStringContainsString('1–2 седмици', $reply);
    }
}
