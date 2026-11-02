@extends('admin.layout')

@section('title', $kind === 'site-check' ? 'Проверки на сайт' : 'Запитвания')

@php
    $pill = fn (bool $active) => 'inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 font-semibold border ' . ($active ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50');
    $query = fn (array $change) => route($route, array_filter(array_merge(['tab' => $tab, 'who' => $who, 'filter' => $unreadOnly ? 'unread' : null], $change), fn ($v) => $v !== null && $v !== 'all'));
@endphp

@section('content')
@if($mailNotConfigured)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="alert">
        <p class="font-semibold">Имейл известията не са настроени.</p>
        <p class="mt-1">Сървърът е с <code class="rounded bg-yellow-100 px-1">MAIL_MAILER={{ config('mail.default') }}</code>: писмата само се записват в лога и не пристигат на {{ $notifyEmail }}. Запитванията се запазват и се виждат тук. За да почнат да идват и по имейл, пуснете на сървъра <code class="rounded bg-yellow-100 px-1">bash deploy/configure-mail.sh</code> (стъпките са в DEPLOY.md, раздел „Имейл“).</p>
    </div>
@endif

<p class="mb-4 text-sm text-gray-600">
    @if($kind === 'site-check')
        Хората, които пуснаха безплатната проверка на сайта си. Отгоре на всяка карта е обобщението: какво не е наред на сайта им. Това е поводът да се свържете.
    @else
        Запитвания от формата за контакт и „Получете оферта“ от пакетите. Проверките на сайт са в <a href="{{ route('admin.site-checks.index') }}" class="font-semibold text-blue-600 hover:underline">„Проверки на сайт“</a>, а чатът в <a href="{{ route('admin.chats.index') }}" class="font-semibold text-blue-600 hover:underline">„Чат разговори“</a>.
    @endif
</p>

{{-- Етапи: всяко запитване минава Ново → Свързахме се → Оферта → Спечелен / Загубен. --}}
<nav class="mb-3 flex flex-wrap gap-2 text-sm" aria-label="Етап">
    @foreach(\App\Http\Controllers\Admin\InquiryController::TABS as $key => $label)
        <a href="{{ $query(['tab' => $key]) }}" class="{{ $pill($tab === $key) }}">{{ $label }} <span class="rounded-full px-1.5 text-xs {{ $tab === $key ? 'bg-white/20' : 'bg-gray-100 text-gray-600' }}">{{ $tabCounts[$key] ?? 0 }}</span></a>
    @endforeach
</nav>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3 text-sm">
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $query(['filter' => $unreadOnly ? null : 'unread']) }}" class="{{ $unreadOnly ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-blue-700 border-blue-300 hover:bg-blue-50' }} inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 font-semibold"><span class="h-2 w-2 rounded-full {{ $unreadOnly ? 'bg-white' : 'bg-blue-600' }}"></span> Непрочетени ({{ $unreadCount }})</a>
        <span class="mx-1 text-gray-300">|</span>
        <a href="{{ $query(['who' => 'all']) }}" class="{{ $pill($who === 'all') }}">На всички</a>
        <a href="{{ $query(['who' => 'me']) }}" class="{{ $pill($who === 'me') }}">Моите</a>
        <a href="{{ $query(['who' => 'none']) }}" class="{{ $pill($who === 'none') }}">Без човек</a>
    </div>
    @if($unreadCount > 0)
        <form action="{{ route('admin.inquiries.read-all') }}" method="POST">
            @csrf
            <input type="hidden" name="kind" value="{{ $kind }}">
            <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50">Маркирай всички като прочетени</button>
        </form>
    @endif
</div>

<div class="mb-4 flex flex-wrap gap-4 text-xs text-gray-500">
    <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm border-l-4 border-blue-600 bg-blue-50"></span> Ново, още непрочетено</span>
    <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm border-l-4 border-gray-300 bg-white"></span> Прочетено</span>
</div>

<div class="space-y-4">
    @forelse($inquiries as $inquiry)
        @include('admin.inquiries._card', ['inquiry' => $inquiry])
    @empty
        <div class="rounded-lg bg-white p-8 text-center text-gray-500 shadow">
            @if($unreadOnly)
                Няма непрочетени {{ $kind === 'site-check' ? 'проверки' : 'запитвания' }}.
            @elseif($kind === 'site-check')
                Още никой не е проверил сайта си{{ $tab !== 'all' ? ' в този етап' : '' }}.
            @else
                Няма запитвания{{ $tab !== 'all' ? ' в този етап' : '' }}.
            @endif
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $inquiries->links() }}</div>
@endsection
