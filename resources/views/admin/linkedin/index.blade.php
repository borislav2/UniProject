@extends('admin.layout')

@section('title', 'Потенциални клиенти')

@section('content')
@include('admin.outreach._tabs')

@if(session('send_error'))
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('send_error') }}</div>
@endif

<div class="mb-6 rounded-lg bg-white p-5 shadow">
    <h3 class="font-bold text-gray-900">LinkedIn</h3>
    <p class="mt-1 text-sm text-gray-600">LinkedIn забранява автоматичното събиране и пращане, затова търсите и пишете на ръка в самия LinkedIn, а тук отбелязвате кого сте намерили, докъде е стигнало и кога да напомните. Нищо не се взима от LinkedIn и нищо не се праща натам.</p>
</div>

@php
    $invitePct = min(100, (int) round($today['invites'] / $today['goal'] * 100));
    $base = array_filter(['q' => $filters['q'], 'who' => $filters['mine'] ? 'me' : null]);
@endphp
<div class="mb-6 rounded-lg bg-white p-4 shadow">
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <p class="text-sm text-gray-700"><strong class="text-lg text-gray-900">Днес: {{ $today['invites'] }}</strong> от {{ $today['goal'] }} покани</p>
        <p class="text-xs text-gray-600">Писани съобщения днес: <strong>{{ $today['messages'] }}</strong></p>
    </div>
    <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-valuenow="{{ $today['invites'] }}" aria-valuemin="0" aria-valuemax="{{ $today['goal'] }}" aria-label="Покани днес">
        <div class="h-full rounded-full {{ $invitePct >= 100 ? 'bg-green-600' : 'bg-brand-600' }}" style="width: {{ $invitePct }}%"></div>
    </div>
    <p class="mt-2 text-xs text-gray-500">LinkedIn пуска около 100 покани на седмица; по-често рискувате акаунта.</p>
</div>

<div class="mb-8 grid gap-4 lg:grid-cols-2">
    <form action="{{ route('admin.linkedin.store') }}" method="POST" class="rounded-lg bg-white p-5 shadow">
        @csrf
        <h3 class="font-bold text-gray-900">Добавяне на един</h3>
        <label class="mt-3 block text-sm font-semibold text-gray-700" for="li-url">Адрес на профила или фирмената страница</label>
        <input id="li-url" type="text" name="profile_url" value="{{ old('profile_url') }}" required maxlength="500" placeholder="https://www.linkedin.com/in/ime-prezime" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm">
        @error('profile_url')<p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach(['name' => 'Име', 'title' => 'Длъжност', 'company' => 'Фирма', 'city' => 'Град', 'industry' => 'Сфера', 'website' => 'Сайт', 'email' => 'Имейл', 'phone' => 'Телефон'] as $field => $label)
                <label class="text-xs font-semibold text-gray-600">{{ $label }}
                    <input type="{{ $field === 'email' ? 'email' : 'text' }}" name="{{ $field }}" value="{{ old($field) }}" maxlength="255" class="mt-0.5 w-full rounded border border-gray-300 px-2 py-1.5 text-sm font-normal text-gray-900">
                </label>
                @error($field)<p class="text-xs text-red-700 sm:col-span-2" role="alert">{{ $message }}</p>@enderror
            @endforeach
        </div>
        <button class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800"><x-icon name="plus" /> Добави</button>
    </form>

    <form action="{{ route('admin.linkedin.bulk') }}" method="POST" class="rounded-lg bg-white p-5 shadow">
        @csrf
        <h3 class="font-bold text-gray-900">Добавяне на много наведнъж</h3>
        <label class="mt-3 block text-sm font-semibold text-gray-700" for="li-bulk">По един адрес на ред, по желание с име и фирма</label>
        <textarea id="li-bulk" name="lines" rows="6" required placeholder="https://www.linkedin.com/in/ivan-petrov | Иван Петров | Фирма ЕООД&#10;https://www.linkedin.com/company/firma" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 font-mono text-xs">{{ old('lines') }}</textarea>
        @error('lines')<p class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-gray-500">Повторенията и адресите, които не са от LinkedIn, се пропускат и се показват след добавянето.</p>
        <button class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800"><x-icon name="plus" /> Добави списъка</button>
    </form>
</div>

<nav class="mb-4 flex flex-wrap gap-2 text-sm">
    <a href="{{ route('admin.linkedin.index', $base + ['due' => 1]) }}" class="rounded-full px-3 py-1 {{ $filters['due'] ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 shadow' }}">За днес ({{ $dueCount }})</a>
    <a href="{{ route('admin.linkedin.index', $filters['mine'] ? ['who' => 'me'] : []) }}" class="rounded-full px-3 py-1 {{ $filters['status'] || $filters['due'] ? 'bg-white text-gray-700 shadow' : 'bg-brand-950 text-white' }}">Всички ({{ $counts->sum() }})</a>
    @foreach(\App\Models\LinkedinProspect::STATUSES as $key => [$label])
        <a href="{{ route('admin.linkedin.index', $base + ['status' => $key]) }}" class="rounded-full px-3 py-1 {{ $filters['status'] === $key ? 'bg-brand-950 text-white' : 'bg-white text-gray-700 shadow' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
    @endforeach
    <a href="{{ route('admin.linkedin.index', array_filter(['status' => $filters['status'], 'due' => $filters['due'] ? 1 : null, 'q' => $filters['q'], 'who' => $filters['mine'] ? null : 'me'])) }}" class="rounded-full px-3 py-1 {{ $filters['mine'] ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800 shadow' }}">Моите ({{ $mineCount }})</a>
</nav>

<form method="GET" action="{{ route('admin.linkedin.index') }}" class="mb-4 flex flex-wrap items-center gap-3 text-sm">
    @if($filters['status'])<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
    @if($filters['due'])<input type="hidden" name="due" value="1">@endif
    @if($filters['mine'])<input type="hidden" name="who" value="me">@endif
    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Име, фирма, град или адрес" class="w-64 rounded border border-gray-300 px-3 py-1.5" aria-label="Търсене">
    <button class="rounded-lg border border-gray-300 px-3 py-1.5 font-semibold text-gray-800 hover:bg-gray-50">Търси</button>
    @if($filters['q'] || $filters['status'] || $filters['due'] || $filters['mine'])<a href="{{ route('admin.linkedin.index') }}" class="text-gray-500 hover:text-gray-800 hover:underline">Изчисти филтрите</a>@endif
</form>

<ul class="divide-y divide-gray-100 rounded-lg bg-white shadow">
    @forelse($prospects as $p)
        @php $next = \App\Models\LinkedinProspect::NEXT_STEP[$p->status] ?? null; @endphp
        <li class="grid gap-3 p-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,2fr)] lg:gap-6">
            <div class="min-w-0">
                <a href="{{ $p->profile_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-blue-700 hover:underline">{{ $p->name }}</a>
                <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $p->statusClass() }}">{{ $p->statusLabel() }}</span>
                <span class="block text-xs text-gray-500">{{ $p->kind === 'company' ? 'Фирмена страница' : 'Профил' }}@if($p->title) · {{ $p->title }}@endif</span>
                <span class="block text-xs text-gray-500">{{ collect([$p->company, $p->city, $p->industry])->filter()->join(' · ') }}</span>
                <span class="mt-1 block text-xs">
                    @if($p->phone)<x-phone :number="$p->phone" />@endif
                    @if($p->email)<a href="mailto:{{ $p->email }}" class="ml-2 text-blue-600 hover:underline">{{ $p->email }}</a>@endif
                    @if($p->website)<a href="{{ $p->website }}" target="_blank" rel="noopener noreferrer" class="ml-2 text-blue-600 hover:underline">сайт</a>@endif
                </span>
                <span class="mt-1 block text-xs text-gray-400">
                    @foreach(['invited_at' => 'покана', 'connected_at' => 'прие', 'messaged_at' => 'писано', 'replied_at' => 'отговор'] as $col => $label)
                        @if($p->{$col}){{ $label }} {{ $p->{$col}->timezone('Europe/Sofia')->format('d.m') }}@unless($loop->last) · @endunless @endif
                    @endforeach
                </span>
            </div>
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    @if($next)
                        <form action="{{ route('admin.linkedin.update', $p) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $next[0] }}"><button class="rounded-lg bg-brand-950 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-800">{{ $next[1] }}</button></form>
                    @endif
                    @if($p->status !== 'not_interested')
                        <form action="{{ route('admin.linkedin.update', $p) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="not_interested"><button class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Не се интересува</button></form>
                    @endif
                    @if($p->project_id)<a href="{{ route('admin.projects.show', $p->project_id) }}" class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-800 hover:bg-blue-100">Запитване →</a>@else<form action="{{ route('admin.linkedin.lead', $p) }}" method="POST">@csrf<button class="rounded-lg border border-blue-300 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50" title="Прояви интерес: направи запитване с етапи и следваща стъпка">→ Запитване</button></form>@endif
                </div>
                <form action="{{ route('admin.linkedin.update', $p) }}" method="POST" class="flex flex-wrap items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="rounded border border-gray-300 px-2 py-1.5 text-sm" aria-label="Статус">
                        @foreach(\App\Models\LinkedinProspect::STATUSES as $key => [$label])<option value="{{ $key }}" @selected($p->status === $key)>{{ $label }}</option>@endforeach
                    </select>
                    <input type="text" name="note" value="{{ $p->note }}" maxlength="2000" placeholder="Бележка (какво сме писали, за какво сме се разбрали…)" class="min-w-0 flex-1 basis-48 rounded border border-gray-300 px-2 py-1.5 text-sm" aria-label="Бележка">
                    <label class="inline-flex items-center gap-1 text-xs text-gray-600">Напомни на <input type="date" name="next_action_at" value="{{ $p->next_action_at?->toDateString() }}" class="rounded border border-gray-300 px-1.5 py-1 text-sm {{ $p->isDue() ? 'border-amber-500 bg-amber-50' : '' }}"></label>
                    <label class="inline-flex items-center gap-1 text-xs text-gray-600">Води го
                        <select name="assigned_to" class="rounded border border-gray-300 px-1.5 py-1 text-sm">
                            <option value="">Никой</option>
                            @foreach($team as $person)<option value="{{ $person->id }}" @selected($p->assigned_to === $person->id)>{{ $person->name }}</option>@endforeach
                        </select>
                    </label>
                    <button class="rounded bg-brand-950 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-800">Запази</button>
                </form>
                <details class="mt-2 text-xs">
                    <summary class="cursor-pointer font-semibold text-gray-500">Данни и изтриване</summary>
                    <form action="{{ route('admin.linkedin.update', $p) }}" method="POST" class="mt-2 grid gap-2 sm:grid-cols-2">
                        @csrf @method('PATCH')
                        @foreach(['name' => 'Име', 'title' => 'Длъжност', 'company' => 'Фирма', 'city' => 'Град', 'industry' => 'Сфера', 'website' => 'Сайт', 'email' => 'Имейл', 'phone' => 'Телефон'] as $field => $label)
                            <label class="font-semibold text-gray-600">{{ $label }}<input type="text" name="{{ $field }}" value="{{ $p->{$field} }}" maxlength="255" class="mt-0.5 w-full rounded border border-gray-300 px-2 py-1 text-sm font-normal text-gray-900"></label>
                        @endforeach
                        <div class="sm:col-span-2"><button class="rounded border border-gray-300 px-3 py-1 font-semibold text-gray-800 hover:bg-gray-50">Запази данните</button></div>
                    </form>
                    <form action="{{ route('admin.linkedin.destroy', $p) }}" method="POST" class="mt-2" onsubmit="return confirm('Да изтрия ли този запис?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Изтрий от списъка</button></form>
                </details>
                @if($p->assignee)<span class="mt-1 block text-xs text-gray-500">Води: {{ $p->assignee->name }}</span>@endif
            </div>
        </li>
    @empty
        <li class="px-4 py-8 text-center text-gray-500">Още няма никого. Намерете хора във LinkedIn и добавете адресите на профилите им по-горе.</li>
    @endforelse
</ul>
<div class="mt-4">{{ $prospects->links() }}</div>
@endsection
