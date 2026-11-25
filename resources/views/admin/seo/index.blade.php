@extends('admin.layout')

@section('title', 'SEO на страниците')

@section('content')
@php
    use App\Support\Seo\Snippet;
    $over = 0; $under = 0;
    foreach ($info as $locales) { foreach ($locales as $r) {
        if (in_array(Snippet::titleState($r['title']), ['long'], true)) $over++;
        if (in_array(Snippet::descriptionState($r['description']), ['long'], true)) $over++;
        if (in_array(Snippet::titleState($r['title']), ['missing'], true) || in_array(Snippet::descriptionState($r['description']), ['missing'], true)) $under++;
    } }
    $dupCount = count($duplicates['title']) + count($duplicates['description']);
    $state = fn ($s) => $s === 'long' ? 'bad' : ($s === 'short' ? 'warn' : ($s === 'missing' ? 'missing' : 'ok'));
@endphp

<div class="adm-page-head">
    <div>
        <h2>SEO</h2>
        <p class="adm-eyebrow mt-1">Страници, статии, здраве на сайта, ключови думи и AI споменавания</p>
    </div>
</div>
@include('admin.seo._tabs', ['active' => 'pages'])

<p class="sx-note">Така излиза всяка страница в Google: пълното заглавие и описание, прочетени от самата страница. „Ваш текст“ значи, че сте го писали тук, „по подразбиране“ е текстът от шаблона. Заглавието е добре между 30 и 60 знака, описанието между 70 и 155. SEO на статиите е в раздел „Статии“.</p>

<div class="sx-mini-kpis">
    <div><small>Страници</small><b>{{ count($info) * 2 }}</b></div>
    <div><small>Над лимита</small><b style="{{ $over ? 'color:#B92E14' : '' }}">{{ $over }}</b></div>
    <div><small>Без текст</small><b style="{{ $under ? 'color:#B92E14' : '' }}">{{ $under }}</b></div>
    <div><small>Повтарящи се</small><b style="{{ $dupCount ? 'color:#B92E14' : '' }}">{{ $dupCount }}</b></div>
</div>
@if(! $report)
    <p class="sx-note">Колоната „Връзки към нея“ се попълва след първата проверка в <a href="{{ route('admin.seo.health') }}" class="underline">SEO здраве</a>.</p>
@endif

<div class="sx-wrap">
    <table class="sx-table">
        <thead>
            <tr><th>Страница</th><th>Заглавие в Google</th><th>Описание в Google</th><th>Състояние</th><th>Заглавие h1</th><th class="num">Връзки към нея</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($pages as $page => $label)
                @foreach(['bg', 'en'] as $l)
                    @php $r = $info[$page][$l] ?? null; @endphp
                    @continue(! $r)
                    @php
                        $dupT = in_array("$page|$l", $duplicates['title'], true);
                        $dupD = in_array("$page|$l", $duplicates['description'], true);
                    @endphp
                    <tr>
                        <td class="sx-page"><b><span class="sx-lang">{{ strtoupper($l) }}</span>{{ $label }}</b><small><a href="{{ $r['url'] }}" target="_blank" rel="noopener">{{ $r['path'] }}</a></small></td>
                        <td>
                            <div class="sx-text">{{ $r['title'] ?: '—' }}</div>
                            <div class="sx-meta">
                                <span class="sx-len {{ $state(Snippet::titleState($r['title'])) }}">{{ Snippet::length($r['title']) }} / 60 · {{ round(Snippet::width((string) $r['title'])) }} / {{ Snippet::TITLE_PX }} px</span>
                                <span class="sx-tag {{ $r['title_own'] ? 'own' : 'quiet' }}">{{ $r['title_own'] ? 'Ваш текст' : 'по подразбиране' }}</span>
                                @if($dupT)<span class="sx-tag dup" title="Същото заглавие има друга страница">повтаря се</span>@endif
                            </div>
                        </td>
                        <td>
                            <div class="sx-text">{{ $r['description'] ?: '—' }}</div>
                            <div class="sx-meta">
                                <span class="sx-len {{ $state(Snippet::descriptionState($r['description'])) }}">{{ Snippet::length($r['description']) }} / 155 · {{ round(Snippet::descriptionWidth($r['description'])) }} / {{ Snippet::DESC_PX }} px</span>
                                <span class="sx-tag {{ $r['description_own'] ? 'own' : 'quiet' }}">{{ $r['description_own'] ? 'Ваш текст' : 'по подразбиране' }}</span>
                                @if($dupD)<span class="sx-tag dup" title="Същото описание има друга страница">повтаря се</span>@endif
                            </div>
                        </td>
                        <td>
                            <div class="sx-facts">
                                <span class="sx-tag {{ $r['noindex'] ? 'hot' : 'quiet' }}" title="{{ $r['noindex'] ? 'noindex значи, че страницата е скрита от Google и няма да се показва в търсенето' : 'Страницата може да се показва в Google' }}">{{ $r['noindex'] ? 'noindex' : 'индексира се' }}</span>
                                <span class="sx-tag {{ $r['in_sitemap'] ? 'quiet' : ($r['noindex'] ? 'quiet' : 'hot') }}" title="Sitemap е списъкът със страниците на сайта, който даваме на Google, за да ги намира по-лесно">{{ $r['in_sitemap'] ? 'в sitemap' : 'не е в sitemap' }}</span>
                                <span class="sx-tag {{ $r['canonical_state'] === 'self' ? 'quiet' : 'hot' }}" title="Canonical е главният адрес на страницата, който Google трябва да брои. Добре е да сочи към самата нея.@if($r['canonical'] && $r['canonical_state'] === 'other') Сега сочи към {{ $r['canonical'] }}@endif">canonical {{ ['self' => 'към себе си', 'other' => 'към друга', 'missing' => 'липсва'][$r['canonical_state']] }}</span>
                            </div>
                        </td>
                        <td><div class="sx-text">{{ $r['h1'] ?: '—' }}</div>@if($r['h1_count'] > 1)<span class="sx-tag hot">{{ $r['h1_count'] }} × h1</span>@endif</td>
                        <td class="num">{{ $r['inlinks'] ?? '·' }}</td>
                        <td class="num"><a href="{{ route('admin.seo.edit', $page) }}" class="adm-btn ghost sm">Редактирай</a></td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endsection
