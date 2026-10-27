@extends('admin.layout')

@section('title', 'Абонаменти')

@php
    $money = fn ($value) => number_format((float) $value, 0, ',', ' ') . ' €';
@endphp

@section('content')
<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 text-sm">
    <div class="rounded-lg bg-white p-4 shadow"><p class="text-gray-500">Месечни приходи</p><p class="text-2xl font-bold text-gray-900">{{ $money($mrr) }}</p><p class="text-xs text-gray-500">{{ $clients->count() }} {{ $clients->count() === 1 ? 'клиент' : 'клиента' }}</p></div>
    <div class="rounded-lg bg-white p-4 shadow"><p class="text-gray-500">Платено този месец</p><p class="text-2xl font-bold text-gray-900">{{ $money($paidThisMonth) }}</p></div>
    <div class="rounded-lg bg-white p-4 shadow"><p class="text-gray-500">Закъсняват</p><p class="text-2xl font-bold {{ $late ? 'text-red-600' : 'text-gray-900' }}">{{ $late }}</p></div>
    <div class="rounded-lg bg-white p-4 shadow"><p class="text-gray-500">Сайтове с проблем</p><p class="text-2xl font-bold {{ $sitesWithProblems ? 'text-red-600' : 'text-gray-900' }}">{{ $sitesWithProblems }}</p></div>
</div>

<div class="space-y-3">
    @forelse($clients as $client)
        @php
            $pay = $client->paymentState();
            $month = $pay['due']->copy()->locale('bg')->isoFormat('MMMM');
            $problems = \App\Support\Admin\SiteMonitor::problems($client);
        @endphp
        <article class="rounded-lg bg-white p-5 shadow {{ $pay['state'] === 'late' ? 'border-l-4 border-red-500' : '' }}">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <a href="{{ route('admin.projects.show', $client) }}" class="text-lg font-semibold text-gray-900 hover:text-blue-600">{{ $client->name }}</a>
                    <p class="text-sm text-gray-500">{{ collect([$client->package, $money($client->monthly_fee) . ' на месец', 'плаща на ' . ($client->billing_day ?: 1) . '-о число'])->filter()->join(' · ') }}</p>
                    @if($client->client_phone || $client->client_email)
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-sm">
                            <x-phone :number="$client->client_phone" />
                            @if($client->client_email)<a href="mailto:{{ $client->client_email }}" class="text-blue-600 hover:underline">{{ $client->client_email }}</a>@endif
                        </div>
                    @endif
                </div>
                <div class="text-right text-sm">
                    @if($pay['state'] === 'paid')
                        <span class="rounded-full bg-green-100 px-3 py-1 font-semibold text-green-800">Платено за {{ $month }}</span>
                    @elseif($pay['state'] === 'upcoming')
                        <span class="rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">{{ $pay['days'] === 0 ? 'Плаща днес' : 'Плаща след ' . $pay['days'] . ' дни' }} ({{ $pay['due']->format('d.m') }})</span>
                    @else
                        <span class="rounded-full bg-red-100 px-3 py-1 font-semibold text-red-800">Закъснява {{ $pay['days'] }} {{ $pay['days'] === 1 ? 'ден' : 'дни' }} за {{ $month }}</span>
                    @endif
                    @unless($pay['state'] === 'paid')
                        <form action="{{ route('admin.clients.paid', $client) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="period" value="{{ $pay['period'] }}">
                            <button class="rounded-lg bg-green-600 px-3 py-1.5 font-semibold text-white hover:bg-green-700">Платено за {{ $month }}</button>
                        </form>
                    @endunless
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-3 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    @if($client->website_url)
                        <a href="{{ $client->website_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ parse_url($client->website_url, PHP_URL_HOST) ?: $client->website_url }}</a>
                        @if(! $client->monitor_checked_at)
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">още не е проверен</span>
                        @elseif($problems)
                            @foreach($problems as $problem)<span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">{{ $problem }}</span>@endforeach
                        @else
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Работи</span>
                        @endif
                        @if($client->ssl_expires_at && ! $problems)<span class="text-xs text-gray-500">SSL до {{ $client->ssl_expires_at->format('d.m.Y') }}</span>@endif
                        @if($client->domain_expires_at && ! $problems)<span class="text-xs text-gray-500">· домейн до {{ $client->domain_expires_at->format('d.m.Y') }}</span>@endif
                        @if($client->monitor_checked_at)<span class="text-xs text-gray-400">· проверен {{ $client->monitor_checked_at->timezone('Europe/Sofia')->format('d.m H:i') }}</span>@endif
                    @else
                        <span class="text-xs text-gray-500">Няма адрес на сайт. Добавете го в „Редактирай“, за да го наблюдаваме.</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($client->website_url)
                        <form action="{{ route('admin.clients.check', $client) }}" method="POST">@csrf<button class="rounded-lg border border-gray-300 px-3 py-1.5 font-semibold text-gray-700 hover:bg-gray-50">Провери сега</button></form>
                    @endif
                    <a href="{{ route('admin.client-emails.create', ['template' => 'monthly-report', 'project' => $client->id]) }}" class="rounded-lg bg-brand-950 px-3 py-1.5 font-semibold text-white hover:bg-brand-800">Месечен отчет</a>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-lg bg-white p-8 text-center text-gray-500 shadow">
            Още няма клиенти на абонамент. В проекта натиснете „Редактирай“ и попълнете пакет и месечна такса.
        </div>
    @endforelse
</div>
@endsection
