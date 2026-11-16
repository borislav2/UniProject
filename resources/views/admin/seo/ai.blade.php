@extends('admin.layout')

@section('title', 'AI споменавания')

@section('content')
@php $today = now('Europe/Sofia')->toDateString(); $stale = ! $last || $last->lt(now('Europe/Sofia')->subDays(35)); @endphp
<div class="adm-page-head">
    <div>
        <h2>SEO</h2>
        <p class="adm-eyebrow mt-1">Дали AI асистентите ни препоръчват: месечна проверка на ръка</p>
    </div>
</div>
@include('admin.seo._tabs', ['active' => 'ai'])

<div class="op-note">
    <span><b>Нищо не се пуска автоматично.</b> Веднъж месечно задайте въпросите по-долу във всеки асистент (в нов разговор, без вход в профил, ако може) и запишете дали сме споменати.</span>
    @if($stale)<span class="err">@if($last) Последната проверка е от {{ $last->format('d.m.Y') }}: време е за нова. @else Още няма записана проверка. @endif</span>@endif
</div>

<div class="sx-forms two">
    <div class="adm-card">
        <h3>Записване на проверка</h3>
        <p class="hint">Една проверка е един въпрос в един асистент.</p>
        <form action="{{ route('admin.seo.ai.store') }}" method="POST" class="op-form" id="ai-form">
            @csrf
            <label>Дата<input type="date" name="checked_on" value="{{ old('checked_on', $today) }}" max="{{ $today }}" required></label>
            <label>Асистент<select name="assistant">@foreach($assistants as $a)<option @selected(old('assistant') === $a)>{{ $a }}</option>@endforeach</select></label>
            <label class="wide">Въпрос<input name="prompt" id="ai-prompt" value="{{ old('prompt') }}" maxlength="500" required placeholder="кликнете на въпрос от списъка вдясно или напишете свой"></label>
            <label>Споменати ли сме?<select name="mentioned"><option value="1" @selected(old('mentioned') === '1')>Да</option><option value="0" @selected(old('mentioned', '0') === '0')>Не</option></select></label>
            <label>На коя позиция или бележка<input name="position_note" value="{{ old('position_note') }}" maxlength="300" placeholder="например трети в списъка"></label>
            <label class="wide">Връзка към отговора или снимката (по желание)<input type="url" name="link" value="{{ old('link') }}" maxlength="1000" placeholder="https://"></label>
            <div class="wide"><button class="adm-btn sm">Запиши</button></div>
        </form>
    </div>
    <div class="adm-card" style="padding:14px 16px">
        <h3 class="sx-card-h">10 въпроса за проверката</h3>
        <p class="hint" style="font-size:13px;color:var(--mute);margin:0">Кликнете „Попълни“, за да отиде въпросът във формата.</p>
        <ol class="sx-prompts">
            @foreach($prompts as $p)<li><span>{{ $p }}</span><button type="button" data-fill="{{ $p }}">Попълни</button></li>@endforeach
        </ol>
    </div>
</div>

<div class="td-section"><h3>Колко често ни споменават</h3></div>
@if($total === 0)
    <div class="td-quiet"><b>Още няма проверки.</b><span>След първия запис тук се появява обобщение по асистент и по месец.</span></div>
@else
    <div class="sx-wrap">
        <table class="sx-table">
            <thead><tr><th>Месец</th>@foreach($assistants as $a)<th class="sx-cell">{{ $a }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach($months as $m => $cells)
                    <tr>
                        <td><b>{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::createFromFormat('Y-m', $m)->locale('bg')->translatedFormat('F Y')) }}</b></td>
                        @foreach($assistants as $a)
                            @php $c = $cells[$a] ?? null; $rate = $c ? $c[1] / $c[0] : null; @endphp
                            <td class="sx-cell {{ $c === null ? 'none' : ($rate >= .5 ? 'high' : ($rate > 0 ? 'mid' : 'low')) }}">@if($c){{ round($rate * 100) }}% <small>({{ $c[1] }} от {{ $c[0] }})</small>@else·@endif</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr style="background:#EFF1F3">
                    <td><b>Общо</b></td>
                    @foreach($assistants as $a)
                        @php [$n, $hit] = $totals[$a]; @endphp
                        <td class="sx-cell {{ $n ? '' : 'none' }}">@if($n)<b>{{ round($hit / $n * 100) }}%</b> <small>({{ $hit }} от {{ $n }})</small>@else·@endif</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <div class="td-section"><h3>Записи ({{ $total }})</h3></div>
    <div class="sx-wrap">
        <table class="sx-table">
            <thead><tr><th>Дата</th><th>Асистент</th><th>Въпрос</th><th>Споменати</th><th>Позиция или бележка</th><th>Връзка</th><th></th></tr></thead>
            <tbody>
                @foreach($checks as $c)
                    <tr>
                        <td class="num" style="text-align:left">{{ $c->checked_on->format('d.m.Y') }}</td>
                        <td>{{ $c->assistant }}</td>
                        <td style="max-width:44ch">{{ $c->prompt }}</td>
                        <td><span class="sx-len {{ $c->mentioned ? 'ok' : 'bad' }}">{{ $c->mentioned ? 'Да' : 'Не' }}</span></td>
                        <td>{{ $c->position_note ?: '·' }}</td>
                        <td>@if($c->link)<a href="{{ $c->link }}" target="_blank" rel="noopener noreferrer" class="underline">отвори</a>@else·@endif</td>
                        <td class="num"><form action="{{ route('admin.seo.ai.destroy', $c) }}" method="POST" onsubmit="return confirm('Да изтрия ли записа?')">@csrf @method('DELETE')<button style="border:0;background:none;color:#B92E14;cursor:pointer;font-size:13px">изтрий</button></form></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-fill]').forEach(function (b) {
        b.addEventListener('click', function () { var f = document.getElementById('ai-prompt'); f.value = b.dataset.fill; f.focus(); f.scrollIntoView({ block: 'center' }); });
    });
</script>
@endpush
