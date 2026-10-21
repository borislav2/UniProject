@extends('admin.layout')

@section('title', 'Чат разговор')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.chats.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">← Всички разговори</a>
    <p class="text-sm text-gray-600">
        Започнат {{ $chat->created_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}
        @if($chat->page) от <span class="font-mono">{{ $chat->page }}</span>@endif
        · {{ strtoupper($chat->locale) }}
    </p>
</div>

@if($chat->lead)
    <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-900">
        Посетителят остави данни: <a href="{{ route('admin.projects.show', $chat->lead) }}" class="font-semibold underline">{{ $chat->lead->name }}</a>
        ({{ collect([$chat->lead->client_phone, $chat->lead->client_email])->filter()->implode(', ') }})
    </div>
@endif

<div class="space-y-3 rounded-lg bg-white p-5 shadow">
    @foreach($chat->messages as $message)
        <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-2xl rounded-2xl px-4 py-3 text-sm whitespace-pre-line {{ $message->role === 'user' ? 'bg-brand-950 text-white' : 'bg-gray-100 text-gray-900' }}">
                {{ $message->text }}
                <div class="mt-1 text-[11px] {{ $message->role === 'user' ? 'text-gray-300' : 'text-gray-500' }}">
                    {{ $message->created_at?->timezone('Europe/Sofia')->format('H:i') }}
                    @if($message->source === 'faq') · от честите въпроси (без AI)@endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
