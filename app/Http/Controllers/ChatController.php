<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Support\Chat\ChatAssistant;
use App\Support\LeadAttribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Чатът на сайта: JSON за прозореца в долния десен ъгъл (partials/chat-widget). */
class ChatController extends Controller
{
    public function send(Request $request, ChatAssistant $assistant): JsonResponse
    {
        abort_unless(config('creatium.chat.enabled'), 404);

        $data = $request->validate([
            'conversation' => 'nullable|uuid',
            'message' => 'required|string|max:1000',
            'locale' => 'nullable|in:bg,en',
            'page' => 'nullable|string|max:255',
        ]);

        $chat = filled($data['conversation'] ?? null) ? ChatConversation::where('public_id', $data['conversation'])->first() : null;
        if (! $chat) {
            ChatConversation::prune();
            $chat = ChatConversation::create([
                'locale' => $data['locale'] ?? 'bg',
                'page' => $data['page'] ?? null,
                'ip_hash' => hash('sha256', $request->ip() . config('app.key')),
            ]);
        }
        app()->setLocale($chat->locale);

        $text = trim($data['message']);
        $hadLead = (bool) $chat->lead_id;
        ChatMessage::create(['chat_conversation_id' => $chat->id, 'role' => 'user', 'text' => $text]);
        $chat->increment('user_messages');

        if ($chat->user_messages > (int) config('creatium.chat.max_messages', 30)) {
            $result = ['reply' => __('Разговорът стана дълъг. За още въпроси ни пишете на :email или изберете час за безплатна консултация: :link', ['email' => config('creatium.contact.email'), 'link' => lroute('contact') . '#konsultatsiya']), 'source' => 'faq'];
        } else {
            $result = $assistant->reply($chat, $text, $request->session()->get(LeadAttribution::SESSION_KEY));
        }

        ChatMessage::create(['chat_conversation_id' => $chat->id, 'role' => 'assistant', 'text' => $result['reply'], 'source' => $result['source']]);
        $chat->touch();

        return response()->json([
            'conversation' => $chat->public_id,
            'reply' => $result['reply'],
            'lead' => ! $hadLead && $chat->fresh()->lead_id !== null,
        ]);
    }

    /** Съобщенията на разговора, за да продължи на следващата страница. */
    public function history(string $conversation): JsonResponse
    {
        abort_unless(config('creatium.chat.enabled'), 404);
        $chat = ChatConversation::where('public_id', $conversation)->first();

        return response()->json([
            'messages' => $chat ? $chat->messages()->get(['role', 'text'])->toArray() : [],
        ]);
    }
}
