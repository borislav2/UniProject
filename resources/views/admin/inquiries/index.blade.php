@extends('admin.layout')

@section('title', 'Запитвания')

@section('content')
@if($mailNotConfigured)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="alert">
        <p class="font-semibold">Имейл известията не са настроени.</p>
        <p class="mt-1">Сървърът е с <code class="rounded bg-yellow-100 px-1">MAIL_MAILER={{ config('mail.default') }}</code>: писмата само се записват в лога и не пристигат на {{ $notifyEmail }}. Запитванията се запазват и се виждат тук. За да почнат да идват и по имейл, пуснете на сървъра <code class="rounded bg-yellow-100 px-1">bash deploy/configure-mail.sh</code> (стъпките са в DEPLOY.md, раздел „Имейл“).</p>
    </div>
@endif

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.inquiries.index') }}" class="rounded-full px-4 py-1.5 font-semibold border {{ $filter === 'all' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">Всички ({{ $totalCount }})</a>
        <a href="{{ route('admin.inquiries.index', ['filter' => 'unread']) }}" class="rounded-full px-4 py-1.5 font-semibold border {{ $filter === 'unread' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">Непрочетени ({{ $unreadCount }})</a>
    </div>
    @if($unreadCount > 0)
        <form action="{{ route('admin.inquiries.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Маркирай всички като прочетени</button>
        </form>
    @endif
</div>

<div class="space-y-4">
    @forelse($inquiries as $inquiry)
        @php($customer = str_replace('Запитване от ', '', $inquiry->name))
        <article class="rounded-lg bg-white shadow p-5 {{ $inquiry->read_at ? '' : 'border-l-4 border-blue-500' }}">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="flex flex-wrap items-center gap-2 text-lg font-semibold text-gray-900">
                        {{ $customer }}
                        @unless($inquiry->read_at)
                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800">Ново</span>
                        @endunless
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $inquiry->created_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    @if($inquiry->service)<span class="rounded-full bg-gray-100 px-2 py-1 text-gray-700">{{ $inquiry->service }}</span>@endif
                    @if($inquiry->business_size)<span class="rounded-full bg-gray-100 px-2 py-1 text-gray-700">Бизнес: {{ $inquiry->business_size }}</span>@endif
                    @if($inquiry->lead_channel)<span class="rounded-full bg-gray-100 px-2 py-1 text-gray-700" title="Канал">{{ $inquiry->lead_channel }}</span>@endif
                    @if($inquiry->locale === 'en')<span class="rounded-full bg-purple-100 px-2 py-1 text-purple-800">EN: отговорете на английски</span>@endif
                </div>
            </div>

            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                @if($inquiry->client_phone)
                    <div><dt class="text-gray-500">Телефон</dt><dd class="font-semibold"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $inquiry->client_phone) }}" class="text-blue-600 hover:underline">{{ $inquiry->client_phone }}</a></dd></div>
                @endif
                @if($inquiry->client_email)
                    <div><dt class="text-gray-500">Имейл</dt><dd class="font-semibold"><a href="mailto:{{ $inquiry->client_email }}" class="text-blue-600 hover:underline">{{ $inquiry->client_email }}</a></dd></div>
                @endif
            </dl>

            <div class="mt-4">
                <p class="text-sm text-gray-500">Съобщение</p>
                <p class="mt-1 whitespace-pre-line rounded-md bg-gray-50 p-3 text-gray-800">{{ $inquiry->description }}</p>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
                <p class="{{ $inquiry->email_sent_at ? 'text-gray-500' : 'text-red-600 font-semibold' }}">
                    @if($inquiry->email_sent_at)
                        Имейл до {{ $notifyEmail }}: изпратен {{ $inquiry->email_sent_at->format('d.m.Y H:i') }}
                    @else
                        Имейлът до {{ $notifyEmail }} не е изпратен: прочетете запитването тук
                    @endif
                </p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.projects.show', $inquiry) }}" class="text-blue-600 hover:underline">Отвори като проект</a>
                    <form action="{{ route($inquiry->read_at ? 'admin.inquiries.unread' : 'admin.inquiries.read', $inquiry) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 font-semibold text-gray-700 hover:bg-gray-50">{{ $inquiry->read_at ? 'Маркирай като непрочетено' : 'Маркирай като прочетено' }}</button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-lg bg-white p-8 text-center text-gray-500 shadow">
            {{ $filter === 'unread' ? 'Няма непрочетени запитвания.' : 'Още няма запитвания от сайта.' }}
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $inquiries->links() }}</div>
@endsection
