@extends('admin.layout')

@section('title', 'Автопилот')

@section('content')
@include('admin.outreach._tabs')
@if(session('send_error'))
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('send_error') }}</div>
@endif

<div class="mb-6 grid gap-6 lg:grid-cols-3">
    <section class="rounded-lg bg-white p-5 shadow lg:col-span-2">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-lg font-bold text-gray-900">Автопилот</h2>
            @if(! $switchedOn)
                <span class="rounded-full bg-gray-100 px-3 py-0.5 text-xs font-semibold text-gray-700">Изключен</span>
            @elseif($settings['paused'])
                <span class="rounded-full bg-amber-100 px-3 py-0.5 text-xs font-semibold text-amber-800">На пауза</span>
            @else
                <span class="rounded-full bg-green-100 px-3 py-0.5 text-xs font-semibold text-green-800">Работи</span>
            @endif
        </div>
        <p class="mt-2 text-sm text-gray-700">Автопилотът сам търси бизнеси по плана долу, оценява ги, проверява сайтовете им и подготвя чернови в <a href="{{ route('admin.outreach.queue') }}" class="text-blue-600 hover:underline">За одобрение</a>. <strong>Той никога не праща писма:</strong> всяко писмо тръгва само когато човек натисне „Одобри и изпрати“.</p>
        <p class="mt-2 text-sm text-gray-700">Работи по една стъпка на 10 минути между 08:00 и 20:00 (София), докато някой отваря сайта или админа, и спира за деня при достигнат лимит.</p>
        @unless($switchedOn)
            <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-800">
                <p class="font-semibold">Как се включва</p>
                <p class="mt-1">В <code class="rounded bg-gray-100 px-1">.env</code> на сървъра сложете <code class="rounded bg-gray-100 px-1">OUTREACH_AUTOPILOT=true</code> (и <code class="rounded bg-gray-100 px-1">SERPER_API_KEY</code> за Google Maps), после <code class="rounded bg-gray-100 px-1">php artisan config:cache</code>. Планът и лимитите се менят оттук.</p>
            </div>
        @endunless
        @unless($google)
            <p class="mt-3 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-900">Няма <code>SERPER_API_KEY</code>: търсенето е в OpenStreetMap, където малко бизнеси имат сайт и имейл. За чужбина Google Maps е почти задължителен.</p>
        @endunless
        @if(! empty($state['last_error']))
            <p class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-800" role="alert">Последна грешка: {{ $state['last_error'] }}</p>
        @endif
    </section>

    <section class="rounded-lg bg-white p-5 shadow">
        <h3 class="font-bold text-gray-900">Днес</h3>
        <dl class="mt-2 space-y-1 text-sm text-gray-700">
            <div class="flex justify-between"><dt>Нови бизнеси</dt><dd><strong>{{ $usage['new'] }}</strong> от {{ $settings['daily_new'] }}</dd></div>
            <div class="flex justify-between"><dt>Търсения на автопилота</dt><dd><strong>{{ $usage['searches'] }}</strong> от {{ $settings['daily_searches'] }}</dd></div>
            <div class="flex justify-between"><dt>Всички търсения</dt><dd><strong>{{ $usage['all_searches'] }}</strong></dd></div>
            <div class="flex justify-between"><dt>Страници от Serper (кредити)</dt><dd><strong>{{ $usage['pages'] }}</strong></dd></div>
            <div class="flex justify-between"><dt>Чакат одобрение</dt><dd><strong>{{ $usage['waiting'] }}</strong></dd></div>
        </dl>
        @if(! empty($state['last_tick_at']))
            <p class="mt-3 text-xs text-gray-500">Последна стъпка {{ \Illuminate\Support\Carbon::parse($state['last_tick_at'])->timezone('Europe/Sofia')->format('d.m H:i') }}@if(! empty($state['last_search'])), {{ $state['last_search'] }}@endif.</p>
        @endif
        <p class="mt-1 text-xs text-gray-500">{{ $inHours ? 'Сега е работно време за стъпки.' : 'Сега е извън работното време (08:00 до 20:00).' }}</p>
        <form action="{{ route('admin.outreach.autopilot.run') }}" method="POST" class="mt-3">
            @csrf
            <button class="adm-btn ghost sm" title="Помощникът търси и подготвя чернови веднага, без да чака. Нищо не се изпраща." @disabled(! $switchedOn || $settings['paused'])>Направи една стъпка сега</button>
        </form>
    </section>
</div>

<form action="{{ route('admin.outreach.autopilot.update') }}" method="POST" class="space-y-6">
    @csrf @method('PUT')
    <section class="rounded-lg bg-white p-5 shadow">
        <h3 class="font-bold text-gray-900">Лимити</h3>
        <div class="mt-3 grid gap-4 sm:grid-cols-3">
            <label class="block text-sm"><span class="font-semibold text-gray-800">Нови бизнеси на ден</span>
                <input type="number" name="daily_new" min="0" max="200" value="{{ old('daily_new', $settings['daily_new']) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
            <label class="block text-sm"><span class="font-semibold text-gray-800">Търсения на ден</span>
                <input type="number" name="daily_searches" min="0" max="50" value="{{ old('daily_searches', $settings['daily_searches']) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></label>
            <label class="flex items-end gap-2 pb-2 text-sm"><input type="checkbox" name="paused" value="1" @checked(old('paused', $settings['paused']))> <span><span class="font-semibold text-gray-800">Пауза</span> <span class="text-gray-500">спира стъпките, без да пипате .env</span></span></label>
        </div>
        <p class="mt-3 text-xs text-gray-500">Всяко търсене в Google Maps е до 3 кредита в Serper. При {{ $settings['daily_searches'] }} търсения на ден това е най-много {{ $settings['daily_searches'] * 3 }} кредита на ден, около {{ $settings['daily_searches'] * 3 * 30 }} на месец.</p>
        @error('daily_new')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    <section class="rounded-lg bg-white p-5 shadow">
        <h3 class="font-bold text-gray-900">План: какво да търси</h3>
        <p class="mt-1 text-sm text-gray-600">Пазарите се редуват. Една комбинация държава, град и вид не се търси пак 30 дни. Без избрани градове се въртят всички.</p>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            @foreach(\App\Support\Outreach\Markets::codes() as $code)
                @php
                    $plan = $settings['plan'][$code] ?? ['enabled' => false, 'cities' => [], 'categories' => []];
                    $allCities = array_keys(\App\Support\Outreach\Markets::cities($code));
                    $chosenCities = $plan['cities'] ?: $allCities;
                @endphp
                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-1 text-sm font-bold text-gray-900">{{ \App\Support\Outreach\Markets::label($code) }}</legend>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-800"><input type="checkbox" name="plan[{{ $code }}][enabled]" value="1" @checked(! empty($plan['enabled']))> Търси тук</label>
                    <p class="mt-1 text-xs text-gray-500">{{ \App\Support\Outreach\Markets::rule($code) }}</p>
                    <details class="mt-2 text-sm"><summary class="cursor-pointer font-semibold text-gray-700">Градове ({{ count($plan['cities'] ?: $allCities) }} от {{ count($allCities) }})</summary>
                        <div class="mt-2 grid grid-cols-2 gap-1">
                            @foreach($allCities as $city)<label class="inline-flex items-center gap-1.5"><input type="checkbox" name="plan[{{ $code }}][cities][]" value="{{ $city }}" @checked(in_array($city, $chosenCities, true))> {{ $city }}</label>@endforeach
                        </div>
                    </details>
                    <details class="mt-2 text-sm"><summary class="cursor-pointer font-semibold text-gray-700">Видове бизнес ({{ count($plan['categories'] ?? []) }})</summary>
                        <div class="mt-2 grid grid-cols-1 gap-1 sm:grid-cols-2">
                            @foreach(\App\Support\Outreach\BusinessSearch::CATEGORIES as $key => $category)<label class="inline-flex items-center gap-1.5"><input type="checkbox" name="plan[{{ $code }}][categories][]" value="{{ $key }}" @checked(in_array($key, $plan['categories'] ?? [], true))> {{ $category['label'] }}</label>@endforeach
                        </div>
                    </details>
                </fieldset>
            @endforeach
        </div>
    </section>

    <button class="adm-btn">Запази настройките</button>
</form>
@endsection
