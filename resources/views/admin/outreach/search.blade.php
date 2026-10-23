@extends('admin.layout')

@section('title', 'Потенциални клиенти')

@section('content')
@include('admin.outreach._tabs')

<div class="grid gap-6 lg:grid-cols-5 mb-8">
    <form action="{{ route('admin.outreach.search.store') }}" method="POST" class="rounded-lg bg-white p-5 shadow lg:col-span-3 space-y-4">
        @csrf
        <h3 class="font-bold text-gray-900">Намери бизнеси</h3>
        @if($usesGoogle)
            <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">Търси в <strong>Google Maps</strong> (Serper): до около 60 бизнеса на търсене, повечето със сайт.</p>
        @else
            <p class="rounded-lg bg-yellow-50 px-3 py-2 text-sm text-yellow-900">Търси в OpenStreetMap, където малко бизнеси имат записан сайт, затова имейлите са малко. За Google Maps добавете <code class="rounded bg-yellow-100 px-1">SERPER_API_KEY</code> в <code class="rounded bg-yellow-100 px-1">.env</code> (serper.dev, 2 500 безплатни търсения).</p>
        @endif
        <p class="text-sm text-gray-600">Търси бизнеси от избрания вид в града, само такива със сайт. За всеки намира публикувания имейл и проверява сайта; от проверката се пише личното писмо. Бизнесите без имейл се пропускат.@if(config('creatium.outreach.search_call_list')) Тези с телефон отиват в „Без сайт (за обаждане)“.@endif</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="font-semibold text-gray-800">Град</span>
                <select name="city" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    @foreach(array_keys(\App\Support\Outreach\BusinessSearch::CITIES) as $city)<option value="{{ $city }}" @selected(old('city') === $city)>{{ $city }}</option>@endforeach
                </select>
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-gray-800">Вид бизнес</span>
                <select name="category" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    @foreach(\App\Support\Outreach\BusinessSearch::CATEGORIES as $key => $category)<option value="{{ $key }}" @selected(old('category') === $key)>{{ $category['label'] }}</option>@endforeach
                </select>
            </label>
        </div>
        @error('city')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <button class="inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800"><x-icon name="magnifying-glass" /> Търси</button>
    </form>
    <div class="rounded-lg bg-white p-5 text-sm text-gray-700 shadow lg:col-span-2 space-y-2">
        <h3 class="font-bold text-gray-900">Как работи</h3>
        <p>Търсенето върви, докато тази страница е отворена (обикновено няколко минути). Може да го спрете и да продължите по-късно.</p>
        <p>Бизнес, намерен веднъж, не се проверява наново при следващо търсене.</p>
        <p class="text-gray-500">Данните са от OpenStreetMap: не всеки бизнес е там и не всеки има сайт. Опитайте няколко вида бизнес в един град.</p>
    </div>
</div>

@if($resettable > 0)
    <form action="{{ route('admin.outreach.search.reset') }}" method="POST" class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-red-200 bg-white p-4 text-sm shadow" onsubmit="return confirm('Да изтрием ли всички търсения, намерени бизнеси, списъка за обаждане и контактите, на които още не сме писали? Изпратените и отписаните остават.')">
        @csrf
        <span class="text-gray-700">Започнете на чисто: трие търсенията, намерените бизнеси, списъка за обаждане и контактите без изпратено писмо. Изпратените и отписаните остават.</span>
        <button class="rounded-lg border border-red-300 px-4 py-2 font-semibold text-red-700 hover:bg-red-50">Изчисти и започни отначало</button>
    </form>
@endif

<ul class="divide-y divide-gray-100 rounded-lg bg-white shadow">
    @forelse($searches as $search)
        <li class="flex flex-wrap items-center justify-between gap-3 p-4" @if($search->isActive()) data-search-work="{{ route('admin.outreach.search.work', $search) }}" @endif>
            <div>
                <span class="font-semibold text-gray-900">{{ $search->label() }}</span>
                <span class="block text-sm text-gray-500" data-progress>
                    {{ \App\Models\OutreachSearch::STATUSES[$search->status] ?? $search->status }} · намерени {{ $search->found }} · с имейл {{ $search->emails }}@if($search->calls) · за обаждане {{ $search->calls }}@endif
                    @if($search->error)<span class="block text-red-600">{{ $search->error }}</span>@endif
                </span>
            </div>
            <span class="text-xs text-gray-400">{{ $search->created_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</span>
        </li>
    @empty
        <li class="p-6 text-center text-gray-500">Още няма търсения. Изберете град и вид бизнес.</li>
    @endforelse
</ul>

<script>
(function () {
    var token = document.querySelector('input[name=_token]').value;
    document.querySelectorAll('[data-search-work]').forEach(function (row) {
        var out = row.querySelector('[data-progress]');
        function step(failures) {
            fetch(row.dataset.searchWork, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } })
                .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                .then(function (p) {
                    out.textContent = p.status_label + ' · проверени ' + p.processed + ' от ' + p.found + ' · с имейл ' + p.emails + (p.calls ? ' · за обаждане ' + p.calls : '') + (p.error ? ' · ' + p.error : '');
                    if (p.status === 'queued' || p.status === 'processing') { step(0); }
                })
                .catch(function () {
                    out.textContent += ' · връзката прекъсна, опитваме пак…';
                    if (failures < 5) { setTimeout(function () { step(failures + 1); }, 5000); }
                });
        }
        out.textContent = 'Търси в картата…';
        step(0);
    });
})();
</script>
@endsection
