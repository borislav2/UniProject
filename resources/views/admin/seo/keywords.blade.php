@extends('admin.layout')

@section('title', 'Ключови думи')

@section('content')
@php
    use App\Models\SeoKeyword;
    use App\Support\Seo\KeywordTargets;
    use App\Support\Seo\Sparkline;
    $today = now('Europe/Sofia')->toDateString();
@endphp
<div class="adm-page-head">
    <div>
        <h2>SEO</h2>
        <p class="adm-eyebrow mt-1">Ключови думи и позиции, въведени на ръка</p>
    </div>
</div>
@include('admin.seo._tabs', ['active' => 'keywords'])

<div class="op-note"><span><b>Няма автоматично следене.</b> Позициите се вписват на ръка или се внасят от Search Console („Ефективност“ → „Заявки“ → експорт или копиране). Нищо не се пита от Google.</span></div>

<div class="sx-forms two">
    <details class="adm-card" @if($errors->any() || old('keyword')) open @endif>
        <summary class="sx-card-h">Нова ключова дума</summary>
        <form action="{{ route('admin.seo.keywords.store') }}" method="POST" class="op-form mt-3">
            @csrf
            <label class="wide">Ключова дума<input name="keyword" value="{{ old('keyword') }}" required maxlength="200" placeholder="например изработка на сайт за ресторант"></label>
            <label>Език<select name="locale">@foreach(SeoKeyword::LOCALES as $k => $v)<option value="{{ $k }}" @selected(old('locale') === $k)>{{ $v }}</option>@endforeach</select></label>
            <label>Държава<select name="country">@foreach(SeoKeyword::COUNTRIES as $k => $v)<option value="{{ $k }}" @selected(old('country', 'bg') === $k)>{{ $v }}</option>@endforeach</select></label>
            <label>Целева страница<select name="target"><option value="">Още не е избрана</option>@foreach($targets as $group => $options)<optgroup label="{{ $group }}">@foreach($options as $value => $label)<option value="{{ $value }}" @selected(old('target') === $value)>{{ $label }}</option>@endforeach</optgroup>@endforeach</select></label>
            <label>Приоритет<select name="priority">@foreach(SeoKeyword::PRIORITIES as $k => $v)<option value="{{ $k }}" @selected(old('priority', 'medium') === $k)>{{ $v }}</option>@endforeach</select></label>
            <label class="wide">Бележка<input name="note" value="{{ old('note') }}" maxlength="500"></label>
            <div class="wide"><button class="adm-btn sm">Добави</button></div>
        </form>
    </details>
    <details class="adm-card" @if(old('csv')) open @endif>
        <summary class="sx-card-h">Внеси позиции от Search Console</summary>
        <form action="{{ route('admin.seo.keywords.import') }}" method="POST" class="op-form mt-3">
            @csrf
            <label class="wide">Поставете експорта „Заявки“ (заявка, кликвания, показвания, CTR, позиция)<textarea name="csv" rows="6" required placeholder="Топ заявки;Кликвания;Показвания;CTR;Позиция&#10;изработка на сайт;12;840;1,4 %;8,3">{{ old('csv') }}</textarea></label>
            <label>За дата<input type="date" name="date" value="{{ old('date', $today) }}" max="{{ $today }}"></label>
            <label>Език<select name="locale">@foreach(SeoKeyword::LOCALES as $k => $v)<option value="{{ $k }}" @selected(old('locale') === $k)>{{ $v }}</option>@endforeach</select></label>
            <label>Държава<select name="country">@foreach(SeoKeyword::COUNTRIES as $k => $v)<option value="{{ $k }}" @selected(old('country', 'bg') === $k)>{{ $v }}</option>@endforeach</select></label>
            <label style="display:flex;gap:8px;align-items:center;text-transform:none;letter-spacing:0;font:400 13.5px var(--body)"><input type="checkbox" name="add_unknown" value="1" style="width:auto;margin:0"> Добави и непознатите думи (до 50, нисък приоритет)</label>
            <div class="wide"><button class="adm-btn sm">Внеси</button> <span class="hint" style="font-size:13px;color:var(--mute)">Разделител запетая, точка и запетая или табулация. Български и английски заглавия. Позиция и с десетична запетая.</span></div>
        </form>
    </details>
</div>

<div class="op-filters">
    <span class="adm-eyebrow">Език</span>
    <a href="{{ route('admin.seo.keywords') }}" class="td-tab" @if(! $locale) aria-current="true" @endif>Всички</a>
    @foreach(SeoKeyword::LOCALES as $k => $v)<a href="{{ route('admin.seo.keywords', ['locale' => $k]) }}" class="td-tab" @if($locale === $k) aria-current="true" @endif>{{ $v }}</a>@endforeach
</div>

<div class="sx-wrap">
    <table class="sx-table">
        <thead><tr><th>Ключова дума</th><th>Целева страница</th><th class="num">Позиция</th><th class="num">Промяна</th><th>Графика</th><th class="num">Показвания</th><th class="num">Клика</th><th>Нов запис</th><th></th></tr></thead>
        <tbody>
        @forelse($keywords as $k)
            @php
                $last = $k->last(); $change = $k->change();
                $latestAny = $k->positions->last();
                $url = KeywordTargets::url($k->target, $k->locale);
            @endphp
            <tr>
                <td class="sx-page"><b>{{ $k->keyword }}</b>
                    <small><span class="sx-tag quiet">{{ strtoupper($k->locale) }} · {{ strtoupper($k->country) }}</span> <span class="sx-tag {{ $k->priority === 'high' ? 'own' : 'quiet' }}">{{ SeoKeyword::PRIORITIES[$k->priority] }}</span></small>
                    @if($k->note)<small>{{ $k->note }}</small>@endif</td>
                <td>@if($k->target)@if($url)<a href="{{ $url }}" target="_blank" rel="noopener" class="underline">{{ KeywordTargets::label($k->target) }}</a>@else{{ KeywordTargets::label($k->target) }}@endif @else<span class="sx-nodata">не е избрана</span>@endif</td>
                <td class="num">@if($last)<b>{{ rtrim(rtrim(number_format($last->position, 1, ',', ''), '0'), ',') }}</b><br><small style="color:var(--mute-2)">{{ $last->date->format('d.m.Y') }}</small>@else<span class="sx-nodata">няма</span>@endif</td>
                <td class="num">@if($change === null)<span class="sx-nodata">·</span>@elseif($change > 0)<span class="sx-up">▲ {{ number_format($change, 1, ',', '') }}</span>@elseif($change < 0)<span class="sx-down">▼ {{ number_format(abs($change), 1, ',', '') }}</span>@else<span class="sx-nodata">0</span>@endif</td>
                <td>{!! Sparkline::svg($k->positions->whereNotNull('position')->take(-12)->pluck('position')->all(), true, 110, 26, 'Позиция по време') !!}</td>
                <td class="num">{{ $latestAny?->impressions !== null ? number_format($latestAny->impressions, 0, ',', ' ') : '·' }}</td>
                <td class="num">{{ $latestAny?->clicks !== null ? number_format($latestAny->clicks, 0, ',', ' ') : '·' }}</td>
                <td>
                    <form action="{{ route('admin.seo.keywords.position', $k) }}" method="POST" class="sx-inline">
                        @csrf
                        <input type="date" name="date" value="{{ $today }}" max="{{ $today }}" aria-label="Дата">
                        <input type="number" name="position" step="0.1" min="0.1" max="1000" placeholder="поз." aria-label="Позиция">
                        <input type="number" name="impressions" min="0" placeholder="показв." aria-label="Показвания">
                        <input type="number" name="clicks" min="0" placeholder="клика" aria-label="Кликвания">
                        <button class="adm-btn ghost sm">Запиши</button>
                    </form>
                </td>
                <td class="num">
                    <details class="adm-menu" style="display:inline-block">
                        <summary class="adm-btn ghost sm" style="list-style:none">Още</summary>
                        <div class="adm-pop" style="min-width:300px; padding:12px; text-align:left">
                            <form action="{{ route('admin.seo.keywords.update', $k) }}" method="POST" class="op-form">
                                @csrf @method('PUT')
                                <label class="wide">Дума<input name="keyword" value="{{ $k->keyword }}" maxlength="200" required></label>
                                <label>Език<select name="locale">@foreach(SeoKeyword::LOCALES as $v => $name)<option value="{{ $v }}" @selected($k->locale === $v)>{{ $name }}</option>@endforeach</select></label>
                                <label>Държава<select name="country">@foreach(SeoKeyword::COUNTRIES as $v => $name)<option value="{{ $v }}" @selected($k->country === $v)>{{ $name }}</option>@endforeach</select></label>
                                <label class="wide">Целева страница<select name="target"><option value="">Не е избрана</option>@foreach($targets as $group => $options)<optgroup label="{{ $group }}">@foreach($options as $value => $label)<option value="{{ $value }}" @selected($k->target === $value)>{{ $label }}</option>@endforeach</optgroup>@endforeach</select></label>
                                <label>Приоритет<select name="priority">@foreach(SeoKeyword::PRIORITIES as $v => $name)<option value="{{ $v }}" @selected($k->priority === $v)>{{ $name }}</option>@endforeach</select></label>
                                <label class="wide">Бележка<input name="note" value="{{ $k->note }}" maxlength="500"></label>
                                <div class="wide"><button class="adm-btn sm">Запази</button></div>
                            </form>
                            @if($k->positions->isNotEmpty())
                                <hr>
                                <small class="adm-eyebrow">Записи</small>
                                @foreach($k->positions->reverse()->take(8) as $p)
                                    <form action="{{ route('admin.seo.keywords.position.destroy', [$k, $p]) }}" method="POST" style="display:flex;gap:8px;align-items:center;font-size:13px;padding:3px 0">
                                        @csrf @method('DELETE')
                                        <span>{{ $p->date->format('d.m.Y') }}</span><b>{{ $p->position !== null ? rtrim(rtrim(number_format($p->position, 1, ',', ''), '0'), ',') : '·' }}</b>
                                        <button style="margin-left:auto;border:0;background:none;color:#B92E14;cursor:pointer" onclick="return confirm('Да изтрия ли този запис?')">изтрий</button>
                                    </form>
                                @endforeach
                            @endif
                            <hr>
                            <form action="{{ route('admin.seo.keywords.destroy', $k) }}" method="POST" onsubmit="return confirm('Да премахна ли думата и записите ѝ?')">@csrf @method('DELETE')<button style="border:0;background:none;color:#B92E14;cursor:pointer;font-size:13px">Премахни думата</button></form>
                        </div>
                    </details>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" style="padding:24px"><span class="sx-note">Още няма ключови думи. Добавете първата горе или внесете експорт от Search Console с „Добави и непознатите думи“.</span></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
