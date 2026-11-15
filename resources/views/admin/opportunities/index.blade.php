@extends('admin.layout')

@section('title', 'Възможности')

@section('content')
@php
    $f = $filters;
    $keep = fn (array $extra = []) => array_filter(array_merge(['kind' => $f['kind'], 'source' => $f['source'], 'country' => $f['country'], 'who' => $f['mine'] ? 'me' : null, 'sort' => $f['sort'] === 'score' ? null : $f['sort'], 'q' => $f['q']], $extra), fn ($v) => $v !== null && $v !== '');
    $filtered = $f['kind'] || $f['source'] || $f['country'] || $f['mine'] || $f['q'];
@endphp

<div class="adm-page-head">
    <div>
        <h2>Възможности</h2>
        <p class="adm-eyebrow mt-1">Поръчки, партньори и заявки, които пасват на услугите ни</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form action="{{ route('admin.opportunities.refresh') }}" method="POST" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Взимам… (до минута)';">@csrf<button class="adm-btn ghost sm">Опресни сега</button></form>
        <a href="#dobavi" class="adm-btn sm" onclick="document.getElementById('dobavi').open = true"><x-icon name="plus" /> Добави възможност</a>
    </div>
</div>

<div class="op-note">
    <span><b>Нищо не се подава и не се праща автоматично.</b> Системата само събира, оценява и показва. Офертите се подават от човек, на сайта на възложителя.</span>
    @if(! $enabled)<span class="err">Автоматичното събиране е изключено (OPPORTUNITIES_ENABLED). Ръчното добавяне работи.</span>@endif
    <details class="w-full">
    <summary class="text-gray-500">Състояние на източниците</summary>
    @foreach($feeds as $key => $feed)
        @php $st = $state[$key] ?? []; @endphp
        <span class="block mt-1">
            {{ $feed->label() }}:
            @if(empty($st['last_run_at']))
                още не е пускан
            @else
                {{ \Illuminate\Support\Carbon::parse($st['last_run_at'])->timezone('Europe/Sofia')->format('d.m H:i') }}, върнати {{ $st['returned'] ?? 0 }}, съвпаднали {{ $st['matched'] ?? 0 }}
                @if(! empty($st['last_error']))<span class="err">грешка: {{ $st['last_error'] }}</span>@endif
            @endif
        </span>
    @endforeach
    </details>
</div>

<nav class="td-tabs" aria-label="Статус">
    <a href="{{ route('admin.opportunities.index', $keep(['status' => 'open'])) }}" class="td-tab" @if($f['status'] === 'open') aria-current="true" @endif>Отворени <i>{{ $openCount }}</i></a>
    @foreach(\App\Models\Opportunity::STATUSES as $key => [$label])
        <a href="{{ route('admin.opportunities.index', $keep(['status' => $key])) }}" class="td-tab" @if($f['status'] === $key) aria-current="true" @endif>{{ $label }} @if($counts[$key] ?? 0)<i>{{ $counts[$key] }}</i>@endif</a>
    @endforeach
    <a href="{{ route('admin.opportunities.index', $keep(['status' => 'all'])) }}" class="td-tab" @if($f['status'] === 'all') aria-current="true" @endif>Всички <i>{{ $counts->sum() }}</i></a>
</nav>

<form method="GET" action="{{ route('admin.opportunities.index') }}" class="op-filters">
    <input type="hidden" name="status" value="{{ $f['status'] }}">
    <select name="kind" aria-label="Вид" onchange="this.form.submit()">
        <option value="">Всички видове</option>
        @foreach(\App\Models\Opportunity::KINDS as $key => $label)<option value="{{ $key }}" @selected($f['kind'] === $key)>{{ $label }}</option>@endforeach
    </select>
    <select name="country" aria-label="Държава" onchange="this.form.submit()">
        <option value="">Всички държави</option>
        @foreach($countries as $code => $name)<option value="{{ $code }}" @selected($f['country'] === $code)>{{ $name }}</option>@endforeach
    </select>
    <select name="source" aria-label="Източник" onchange="this.form.submit()">
        <option value="">Всички източници</option>
        @foreach(\App\Models\Opportunity::SOURCES as $key => $label)<option value="{{ $key }}" @selected($f['source'] === $key)>{{ $label }}</option>@endforeach
    </select>
    <select name="sort" aria-label="Подредба" onchange="this.form.submit()">
        <option value="score" @selected($f['sort'] === 'score')>По оценка</option>
        <option value="deadline" @selected($f['sort'] === 'deadline')>По срок</option>
        <option value="new" @selected($f['sort'] === 'new')>Най-нови</option>
    </select>
    <label class="inline-flex items-center gap-1.5"><input type="checkbox" name="who" value="me" @checked($f['mine']) onchange="this.form.submit()"> Моите ({{ $mineCount }})</label>
    <input type="search" name="q" value="{{ $f['q'] }}" placeholder="Заглавие или възложител" aria-label="Търсене">
    <button class="td-act">Търси</button>
    @if($filtered)<a href="{{ route('admin.opportunities.index', ['status' => $f['status']]) }}" class="text-gray-500 hover:underline">Изчисти филтрите</a>@endif
</form>

@if($opportunities->isEmpty())
    <div class="td-quiet">
        <b>Няма възможности тук.</b>
        <span>@if($counts->sum() === 0) Още нищо не е събрано. Натиснете „Опресни сега“ или добавете възможност на ръка. @else Опитайте с други филтри или друг статус. @endif</span>
    </div>
@else
    <ul class="op-list">
        @foreach($opportunities as $o)
            @include('admin.opportunities._row', ['o' => $o])
        @endforeach
    </ul>
    {{ $opportunities->links() }}
@endif

<div class="op-panels">
    <section class="adm-card op-panel">
        <h3>Търсения в платформи за свободна практика</h3>
        <p class="hint">Upwork, Malt, Fiverr и freelancermap забраняват автоматично събиране, затова тук са само готови търсения, които се отварят в нов раздел. Намерено интересно? Добавете го с формата по-долу.</p>
        <ul class="op-links">
            @foreach($searches as $link)<li><a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['label'] }} ↗</a></li>@endforeach
        </ul>
        @if(empty($searches))<p class="text-sm text-gray-500">Няма запазени търсения.</p>@endif
    </section>
    <section class="adm-card op-panel">
        <h3>Грантове и програми</h3>
        <p class="hint">Тук няма автоматично събиране. Следете страниците и добавяйте подходящите покани на ръка.</p>
        <ul class="op-links">
            @foreach($grants as $link)<li><a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['label'] }} ↗</a></li>@endforeach
        </ul>
    </section>
</div>

<details class="adm-card op-panel mb-6" id="dobavi" @if($errors->any()) open @endif>
    <summary class="font-semibold">Добави възможност на ръка</summary>
    <form action="{{ route('admin.opportunities.store') }}" method="POST" class="op-form mt-4">
        @csrf
        <label>Вид
            <select name="kind">
                @foreach(['freelance', 'grant', 'tender', 'partner', 'other'] as $key)<option value="{{ $key }}" @selected(old('kind', 'freelance') === $key)>{{ \App\Models\Opportunity::KINDS[$key] }}</option>@endforeach
            </select>
        </label>
        <label>Откъде е (платформа или сайт)
            <input type="text" name="origin" value="{{ old('origin') }}" maxlength="80" placeholder="Upwork, Malt, ИСУН…">
        </label>
        <label class="wide">Заглавие
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="300" placeholder="Например: Нов уебсайт за фирма за мебели">
            @error('title')<span class="adm-err">{{ $message }}</span>@enderror
        </label>
        <label class="wide">Адрес
            <input type="url" name="url" value="{{ old('url') }}" maxlength="1000" placeholder="https://…">
            @error('url')<span class="adm-err">{{ $message }} @if(session('duplicate_id'))<a href="{{ route('admin.opportunities.show', session('duplicate_id')) }}" class="underline">Отвори я</a>@endif</span>@enderror
        </label>
        <label>Държава
            <select name="country">
                <option value="">Не е ясна</option>
                @foreach(\App\Support\Opportunities\Countries::options() as $code => $name)<option value="{{ $code }}" @selected(old('country') === $code)>{{ $name }}</option>@endforeach
            </select>
        </label>
        <label>Срок
            <input type="date" name="deadline" value="{{ old('deadline') }}">
            @error('deadline')<span class="adm-err">{{ $message }}</span>@enderror
        </label>
        <label>Бюджет
            <input type="number" name="budget" value="{{ old('budget') }}" min="0" step="any" placeholder="1500">
        </label>
        <label>Валута
            <select name="currency">@foreach(['EUR', 'GBP', 'USD', 'BGN'] as $c)<option @selected(old('currency', 'EUR') === $c)>{{ $c }}</option>@endforeach</select>
        </label>
        <label class="wide">Бележка
            <textarea name="note" rows="3" maxlength="2000" placeholder="Какво търсят, как стигнахме до тях, какво да проверим">{{ old('note') }}</textarea>
        </label>
        <div class="wide" style="grid-column: 1 / -1"><button class="adm-btn">Добави</button></div>
    </form>
</details>

<details class="adm-card op-panel">
    <summary class="font-semibold">Редактирай връзките (търсения и програми)</summary>
    <form action="{{ route('admin.opportunities.links') }}" method="POST" class="op-form mt-4">
        @csrf @method('PUT')
        <label class="wide">Търсения в платформи, по едно на ред „Име | адрес“
            <textarea name="searches" rows="7" class="font-mono text-xs">{{ \App\Support\Opportunities\OpportunityLinks::toLines($searches) }}</textarea>
        </label>
        <label class="wide">Грантове и програми, по едно на ред „Име | адрес“
            <textarea name="grants" rows="4" class="font-mono text-xs">{{ \App\Support\Opportunities\OpportunityLinks::toLines($grants) }}</textarea>
        </label>
        <div class="wide" style="grid-column: 1 / -1"><button class="adm-btn ghost">Запази връзките</button></div>
    </form>
</details>
@endsection
