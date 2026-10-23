@extends('admin.layout')

@section('title', 'Чат разговори')

@section('content')

@if($provider)
    <p class="mb-4 text-sm text-gray-600">Отговаря: <span class="font-semibold text-gray-900">{{ $provider }}</span>. Дневен таван: {{ config('creatium.chat.daily_limit') }} съобщения през AI (<code class="rounded bg-gray-100 px-1">CHAT_DAILY_LIMIT</code>), над него отговаря от честите въпроси.</p>
@else
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="status">
        <p class="font-semibold">Няма свързан AI, затова чатът отговаря само от честите въпроси на сайта.</p>
        <p class="mt-1">Добавете в <code class="rounded bg-yellow-100 px-1">.env</code> на сървъра <code class="rounded bg-yellow-100 px-1">MISTRAL_API_KEY</code> (безплатен план от console.mistral.ai; първо изключете използването на данните за обучение) или <code class="rounded bg-yellow-100 px-1">ANTHROPIC_API_KEY</code> (Claude, плаща се на употреба).</p>
    </div>
@endif

<p class="mb-4 text-sm text-gray-600">Разговорите от чата на сайта. Пазят се {{ config('creatium.chat.retention_days') }} дни след последното съобщение и после се изтриват сами. Когато посетител остави телефон или имейл, запитването се появява и в „Запитвания“.</p>

<div class="overflow-hidden rounded-lg bg-white shadow">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
            <tr><th class="px-4 py-3">Първо съобщение</th><th class="px-4 py-3">Съобщения</th><th class="px-4 py-3">Запитване</th><th class="px-4 py-3">Последно</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($chats as $chat)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.chats.show', $chat) }}" class="font-semibold text-brand-700 hover:underline">{{ \Illuminate\Support\Str::limit($chat->messages->first()?->text ?? '…', 90) }}</a>
                        <span class="ml-2 rounded bg-gray-100 px-1.5 py-0.5 text-xs uppercase text-gray-600">{{ $chat->locale }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $chat->messages_count }}</td>
                    <td class="px-4 py-3">
                        @if($chat->lead)
                            <a href="{{ route('admin.projects.show', $chat->lead) }}" class="rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">{{ $chat->lead->client_phone ?? $chat->lead->client_email }}</a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $chat->updated_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Още няма разговори.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $chats->links() }}</div>
@endsection
