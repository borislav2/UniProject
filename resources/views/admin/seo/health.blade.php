@extends('admin.layout')

@section('title', 'SEO здраве')

@section('content')
@php
    use App\Support\Seo\SiteHealth;
    use App\Support\Seo\Sparkline;
    $data = $report?->data ?? [];
    $groups = collect(SiteHealth::ISSUES)->map(fn ($def, $code) => ['code' => $code, 'sev' => $def[0], 'title' => $def[1], 'hint' => $def[2], 'items' => $data['issues'][$code] ?? [], 'count' => $data['counts'][$code] ?? 0])->filter(fn ($g) => $g['count'] > 0);
    $order = ['error' => 0, 'warning' => 1, 'notice' => 2];
    $groups = $groups->sortBy(fn ($g) => $order[$g['sev']])->values();
    $sevLabel = ['error' => 'грешка', 'warning' => 'предупреждение', 'notice' => 'бележка'];
    $delta = fn ($n, $goodWhenNegative = false) => $n === 0 ? '<span class="sx-nodata">без промяна</span>' : '<span class="' . (($n < 0) === $goodWhenNegative ? 'sx-up' : 'sx-down') . '">' . ($n > 0 ? '+' : '') . $n . '</span>';
@endphp
<div class="adm-page-head">
    <div>
        <h2>SEO</h2>
        <p class="adm-eyebrow mt-1">Проверка на нашия сайт отвътре, без заявки навън</p>
    </div>
    <form action="{{ route('admin.seo.health.run') }}" method="POST" onsubmit="var b=this.querySelector('button'); b.disabled = true; b.textContent = 'Пускам…';">
        @csrf
        <button class="adm-btn" @disabled($running)><x-icon name="check-circle" /> {{ $running ? 'Проверката върви' : 'Провери сега' }}</button>
    </form>
</div>
@include('admin.seo._tabs', ['active' => 'health'])

@if(! $report)
    <div class="op-note"><span><b>Още няма проверка.</b> Натиснете „Провери сега“ (отнема от няколко секунди до около минута) или изчакайте: пуска се сама най-много веднъж на {{ config('creatium.seo_health.interval_hours') }} часа, когато някой посети сайта.</span></div>
@else
    @if(! empty($state['last_error']))
        <div class="op-warn">Последният опит не успя: {{ $state['last_error'] }}</div>
    @endif
    <p class="sx-note">Проверено на {{ $report->ran_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }} за {{ $report->seconds }} сек.: {{ $report->pages }} адреса от двата езика (sitemap има {{ $data['sitemap'] ?? 0 }}), публичният дизайн „{{ ($data['design'] ?? 'current') === 'g' ? 'Г' : 'сегашният' }}“. Автоматично се подновява най-много веднъж на {{ config('creatium.seo_health.interval_hours') }} часа@if(! \App\Support\Seo\SiteHealth::enabled()) (сега е изключено)@endif.@if(! empty($data['skipped'])) Обходът е спрян по лимита: още {{ $data['skipped'] }} адреса чакат.@endif</p>

    <div class="td-kpis" style="--cols: 5">
        <div class="td-kpi {{ $report->score < 70 ? 'hot' : '' }}"><small>Оценка</small><b>{{ $report->score }}<span> / 100</span></b>@if($trend)<em style="color:var(--mute)">{!! $delta($trend['score'], false) !!} спрямо предишната</em>@endif</div>
        <div class="td-kpi {{ $report->errors ? 'hot' : '' }}"><small>Грешки</small><b>{{ $report->errors }}</b>@if($trend)<em style="color:var(--mute)">{!! $delta($trend['errors'], true) !!}</em>@endif</div>
        <div class="td-kpi"><small>Предупреждения</small><b>{{ $report->warnings }}</b>@if($trend)<em style="color:var(--mute)">{!! $delta($trend['warnings'], true) !!}</em>@endif</div>
        <div class="td-kpi"><small>Обходени адреси</small><b>{{ $report->pages }}</b></div>
        <div class="td-kpi"><small>Най-далеч от началната</small><b>{{ $data['max_depth'] ?? 0 }}<span> клика</span></b></div>
    </div>

    @if($trend)
        <p class="sx-trend"><span>Спрямо предишната проверка: <b>{{ $trend['new'] }}</b> нови проблема, <b>{{ $trend['fixed'] }}</b> поправени.</span>
            @if($history->count() > 1)<span style="display:inline-flex;align-items:center;gap:8px">Оценка по време {!! Sparkline::svg($history->pluck('score')->all(), false, 140, 26, 'Оценка по време') !!}</span>@endif</p>
    @endif

    @if($groups->isEmpty())
        <div class="td-quiet"><b>Няма нито един проблем.</b><span>Всички проверки са чисти.</span></div>
    @endif

    <div class="sx-issues">
        @foreach($groups as $g)
            <section class="adm-card sx-issue" id="{{ $g['code'] }}" @if($g['sev'] === 'notice') data-notice @endif>
                <details @if($g['sev'] !== 'notice') open @endif>
                    <summary style="padding: 13px 16px; list-style: none;">
                        <span style="display:inline-flex; flex-wrap:wrap; align-items:center; gap:4px 12px">
                            <span class="sx-sev {{ $g['sev'] }}">{{ $sevLabel[$g['sev']] }}</span>
                            <b style="font: 600 15.5px/1.25 var(--head)">{{ $g['title'] }}</b>
                            <span class="td-count {{ $g['sev'] === 'error' ? 'hot' : '' }}">{{ $g['count'] }}</span>
                        </span>
                    </summary>
                    <p class="hint">{{ $g['hint'] }}</p>
                    <ul class="sx-items">
                        @foreach(array_slice($g['items'], 0, 6) as $item)
                            @php $edit = SiteHealth::editUrl($item['url']); @endphp
                            <li><a class="p" href="{{ rtrim($data['root'] ?? '', '/') . $item['url'] }}" target="_blank" rel="noopener">{{ $item['url'] }}</a><span class="detail">{{ $item['detail'] }}</span>@if($edit)<a class="edit" href="{{ $edit }}">Редактирай →</a>@endif</li>
                        @endforeach
                    </ul>
                    @if(count($g['items']) > 6)
                        <details class="sx-more">
                            <summary>Още {{ count($g['items']) - 6 }}@if($g['count'] > count($g['items'])) (показани са първите {{ count($g['items']) }} от {{ $g['count'] }})@endif</summary>
                            <ul class="sx-items">
                                @foreach(array_slice($g['items'], 6) as $item)
                                    @php $edit = SiteHealth::editUrl($item['url']); @endphp
                                    <li><a class="p" href="{{ rtrim($data['root'] ?? '', '/') . $item['url'] }}" target="_blank" rel="noopener">{{ $item['url'] }}</a><span class="detail">{{ $item['detail'] }}</span>@if($edit)<a class="edit" href="{{ $edit }}">Редактирай →</a>@endif</li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </details>
            </section>
        @endforeach
    </div>

    @if(! empty($data['rows']))
        <details class="adm-card mt-6">
            <summary style="padding: 13px 16px; font: 600 15px var(--head)">Всички обходени адреси ({{ count($data['rows']) }})</summary>
            <div class="sx-wrap" style="border:0; border-top:1px solid var(--line)">
                <table class="sx-table">
                    <thead><tr><th>Адрес</th><th class="num">Отговор</th><th class="num">Дълбочина</th><th class="num">Заглавие</th><th class="num">Описание</th><th class="num">h1</th><th class="num">Думи</th><th class="num">Връзки към нея</th><th class="num">Връзки от нея</th><th>Бележки</th></tr></thead>
                    <tbody>
                        @foreach($data['rows'] as $row)
                            <tr>
                                <td><a href="{{ rtrim($data['root'] ?? '', '/') . $row['p'] }}" target="_blank" rel="noopener" class="font-mono" style="font-size:12.5px">{{ $row['p'] }}</a></td>
                                <td class="num"><span class="sx-len {{ $row['s'] === 200 ? 'ok' : ($row['s'] >= 400 || $row['s'] === 0 ? 'bad' : 'warn') }}">{{ $row['s'] }}</span></td>
                                <td class="num">{{ $row['d'] ?? '·' }}</td>
                                <td class="num">{{ $row['t'] }}</td><td class="num">{{ $row['m'] }}</td><td class="num">{{ $row['h'] }}</td><td class="num">{{ $row['w'] }}</td><td class="num">{{ $row['i'] }}</td><td class="num">{{ $row['o'] }}</td>
                                <td>@if($row['n'])<span class="sx-tag hot" title="Скрита от Google">noindex</span> @endif @if($row['sm'])<span class="sx-tag quiet" title="Е в списъка със страници, който даваме на Google">sitemap</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @endif
@endif
@endsection
