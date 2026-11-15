@extends('admin.layout')

@section('title', 'Статистика')

@php
    $pct = fn ($part, $whole) => $whole > 0 ? round($part / $whole * 100) . '%' : '–';
@endphp

@section('content')
<nav class="mb-6 flex flex-wrap items-center gap-2 text-sm">
    @foreach(\App\Http\Controllers\Admin\StatsController::PERIODS as $value => $label)
        <a href="{{ route('admin.stats', array_filter(['days' => $value, 'country' => $country])) }}" class="rounded-full px-4 py-1.5 font-semibold {{ $days === $value ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">Последните {{ $label }}</a>
    @endforeach
    <form method="GET" action="{{ route('admin.stats') }}" class="ml-auto inline-flex items-center gap-2 text-gray-700">
        <input type="hidden" name="days" value="{{ $days }}">
        <label for="stats-country" class="font-semibold">Държава</label>
        <select id="stats-country" name="country" class="rounded border border-gray-300 px-2 py-1.5" onchange="this.form.submit()" title="Филтрира писмата и обажданията">
            <option value="">Всички</option>
            @foreach(\App\Support\Outreach\Markets::codes() as $code)<option value="{{ $code }}" @selected($country === $code)>{{ \App\Support\Outreach\Markets::label($code) }}</option>@endforeach
        </select>
    </form>
</nav>

{{-- Две фунии една до друга: всяка стъпка с броя и процента от предишната. --}}
<div class="mb-6 grid gap-6 lg:grid-cols-2 2xl:grid-cols-3">
    @foreach([['Писма до бизнеси', $emails, 'bg-brand-500'], ['Обаждания', $calls, 'bg-brand-950'], ['LinkedIn', $linkedin, 'bg-gray-500']] as [$title, $steps, $bar])
        <section class="rounded-lg bg-white p-5 shadow">
            <h2 class="mb-4 font-semibold text-gray-800">{{ $title }}</h2>
            @php $top = max(1, $steps[0][1]); @endphp
            <div class="space-y-3">
                @foreach($steps as $i => [$label, $value])
                    <div>
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="text-gray-700">{{ $label }}</span>
                            <span><strong class="text-gray-900">{{ $value }}</strong>@if($i > 0) <span class="text-gray-500">· {{ $pct($value, $steps[$i - 1][1]) }}</span>@endif</span>
                        </div>
                        <div class="mt-1 h-2.5 rounded-full bg-gray-100"><div class="h-full rounded-full {{ $bar }}" style="width: {{ $value ? max(3, round($value / $top * 100)) : 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>
            @if($country && $title !== 'LinkedIn')
                <p class="mt-3 text-xs font-semibold text-brand-700">Само {{ \App\Support\Outreach\Markets::label($country) }}</p>
            @endif
            @if($title === 'Писма до бизнеси')
                <p class="mt-4 text-xs text-gray-500">Напомняния в периода: {{ $followups }}. Процентът е спрямо предишния ред.</p>
            @elseif($title === 'LinkedIn')
                <p class="mt-4 text-xs text-gray-500">От контактите, добавени в периода; всяка стъпка се брои, щом е стигната, дори статусът после да е друг.</p>
            @else
                <p class="mt-4 text-xs text-gray-500">По последния резултат на всеки обаден бизнес.</p>
            @endif
        </section>
    @endforeach
</div>

<section class="mb-6 rounded-lg bg-white shadow">
    <h2 class="border-b px-5 py-4 font-semibold text-gray-800">Запитвания по канал</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-5 py-2 font-medium">Канал</th>
                    <th class="px-5 py-2 font-medium text-right">Запитвания</th>
                    <th class="px-5 py-2 font-medium text-right">Стигнали до оферта</th>
                    <th class="px-5 py-2 font-medium text-right">Спечелени</th>
                    <th class="px-5 py-2 font-medium text-right">Загубени</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($channels as $row)
                    <tr>
                        <td class="px-5 py-2 font-medium text-gray-900">{{ $row['channel'] }}</td>
                        <td class="px-5 py-2 text-right">{{ $row['total'] }}</td>
                        <td class="px-5 py-2 text-right">{{ $row['offer'] }} <span class="text-gray-400">{{ $pct($row['offer'], $row['total']) }}</span></td>
                        <td class="px-5 py-2 text-right font-semibold text-green-700">{{ $row['won'] }} <span class="font-normal text-gray-400">{{ $pct($row['won'], $row['total']) }}</span></td>
                        <td class="px-5 py-2 text-right text-gray-600">{{ $row['lost'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-6 text-center text-gray-500">Няма запитвания в периода.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="mb-6 rounded-lg bg-white p-5 shadow">
    <h2 class="mb-3 font-semibold text-gray-800">Писма и обаждания по държава</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-gray-600"><tr><th class="py-1 pr-4 font-semibold">Държава</th><th class="py-1 pr-4 font-semibold">Писма</th><th class="py-1 pr-4 font-semibold">Кликнали</th><th class="py-1 pr-4 font-semibold">Отговорили</th><th class="py-1 pr-4 font-semibold">Обаждания</th><th class="py-1 font-semibold">Интерес</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($byCountry as $row)
                    <tr><td class="py-1.5 pr-4 text-gray-800">{{ \App\Support\Outreach\Markets::label($row['code']) }}</td><td class="pr-4">{{ $row['emails'] }}</td><td class="pr-4">{{ $row['clicked'] }}</td><td class="pr-4">{{ $row['replied'] }}</td><td class="pr-4">{{ $row['calls'] }}</td><td>{{ $row['interested'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-lg bg-white p-5 shadow">
        <h2 class="mb-3 font-semibold text-gray-800">Къде са запитванията сега</h2>
        <ul class="space-y-1 text-sm">
            @foreach($pipeline as [$label, $value])
                <li class="flex justify-between"><span class="text-gray-700">{{ $label }}</span><strong>{{ $value }}</strong></li>
            @endforeach
        </ul>
    </section>
    <section class="rounded-lg bg-white p-5 shadow">
        <h2 class="mb-3 font-semibold text-gray-800">Защо губим</h2>
        @forelse($lostReasons as $reason => $count)
            <p class="flex justify-between text-sm"><span class="text-gray-700">{{ $reason }}</span><strong>{{ $count }}</strong></p>
        @empty
            <p class="text-sm text-gray-500">Няма загубени в периода. Записвайте причината при „Загубен“, за да се вижда тук.</p>
        @endforelse
    </section>
</div>
@endsection
