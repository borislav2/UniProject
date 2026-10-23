<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Support\Chat\ChatAssistant;

/** „Чат разговори“: какво питат посетителите в чата на сайта (пазят се 30 дни). */
class ChatController extends Controller
{
    public function index()
    {
        ChatConversation::prune();

        return view('admin.chats.index', [
            'chats' => ChatConversation::with('lead')->withCount('messages')
                ->with(['messages' => fn ($q) => $q->where('role', 'user')->orderBy('id')])
                ->latest('updated_at')->paginate(30),
            'provider' => ChatAssistant::providerName(),
        ]);
    }

    public function show(ChatConversation $chat)
    {
        return view('admin.chats.show', ['chat' => $chat->load('messages', 'lead')]);
    }
}
