@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $score = $report['score'];
    [$scoreColor, $verdict] = match (true) {
        ! $report['reachable'] => ['#b91c1c', __('Не успяхме да отворим сайта.')],
        $score >= 85 => ['#15803d', __('Основата е добра. Има само дребни неща за оправяне.')],
        $score >= 60 => ['#b45309', __('Има няколко неща, които си струва да оправите.')],
        default => ['#b91c1c', __('Има важни проблеми, които пречат на сайта в Google и при клиентите.')],
    };
    $crawl = $report['crawl'] ?? null;
    $pagesChecked = (int) ($crawl['analyzed'] ?? 0);
    $issues = collect(\App\Support\SiteCheck\SiteAudit::items($report));
    $byArea = \App\Support\SiteCheck\SiteAudit::itemsByArea($report);
    $areas = \App\Support\SiteCheck\Areas::scores($report);
    $top = \App\Support\SiteCheck\Areas::top($issues->all(), 3);
    $signals = $crawl['signals'] ?? null;
    $tones = ['green' => ['#15803d', '#dcfce7'], 'amber' => ['#b45309', '#fef3c7'], 'red' => ['#b91c1c', '#fee2e2'], 'gray' => ['#64748b', '#e5e9f0']];
    $sev = ['error' => ['#b91c1c', '#fee2e2', '✕'], 'warning' => ['#b45309', '#fef3c7', '!'], 'notice' => ['#2d63a3', '#e0ecf8', 'i']];
    $speed = $report['pagespeed'] ?? null;
    // Данните за бизнеса не са „наред“, ако обхождането е намерило грешка в тях.
    $schemaProblem = $issues->contains(fn ($i) => str_starts_with((string) $i['key'], 'schema_'));
    $passed = collect($report['checks'])->where('status', 'ok')->reject(fn ($c) => $schemaProblem && ($c['key'] ?? null) === 'schema');
    $auditSubject = rawurlencode(__('Подробен одит за :domain', ['domain' => $report['domain']]));
    $priceNote = config('creatium.site_check.audit_price_note');
    $shopping = $report['shopping'] ?? null;
    $perArea = 5;
    $pad = 28;
@endphp

@section('eyebrow', __('Безплатна проверка на сайт'))
@section('headline'){{ $report['domain'] }}@endsection
@section('sub'){{ $pagesChecked > 1 ? __('Проверихме :n страници от сайта и основните настройки. Ето какво открихме.', ['n' => $pagesChecked]) : __('Проверихме началната страница и основните настройки. Ето какво открихме.') }}@endsection

@section('body')
<tr><td style="padding:28px {{ $pad }}px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;">
<tr>
<td width="110" align="center" valign="middle" style="padding:20px 0 20px 16px;{{ $f }}">
<div style="font-size:44px;line-height:1;font-weight:bold;color:{{ $scoreColor }};">{{ $score }}</div>
<div style="font-size:12px;color:#64748b;padding-top:4px;">{{ __('от 100') }}</div>
</td>
<td valign="middle" style="padding:20px 18px 20px 12px;{{ $f }}font-size:15px;line-height:1.55;color:#0f1a2b;"><strong>{{ $verdict }}</strong><br><span style="color:#64748b;font-size:13px;">{{ __(':issues за оправяне · :passed наред', ['issues' => $issues->where('severity', '!=', 'notice')->count(), 'passed' => $passed->count()]) }}</span></td>
</tr>
</table>
</td></tr>

@if($top)
<tr><td style="padding:24px {{ $pad }}px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => __('Какво да оправите първо'), 'color' => '#b91c1c'])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($top as $t)
<tr><td width="34" valign="top" style="padding:10px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;border-radius:11px;background:#0f1a2b;color:#ffffff;{{ $f }}font-size:12px;font-weight:bold;text-align:center;">{{ $loop->iteration }}</span></td>
<td style="padding:10px 0;{{ $f }}font-size:15px;line-height:1.5;color:#0f1a2b;border-bottom:1px solid #eef1f5;"><strong>{{ $t['title'] }}</strong><span style="display:block;font-size:12px;color:#64748b;padding-top:2px;">{{ $areas[$t['area']]['label'] }}</span></td></tr>
@endforeach
</table>
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#475569;padding-top:10px;">{{ __('Започнете с първото. Останалото е по-малко спешно и е разделено по области по-долу.') }}</div>
</td></tr>
@endif

@if($crawl && $pagesChecked > 0)
<tr><td style="padding:14px {{ $pad }}px 0;{{ $f }}font-size:13px;line-height:1.6;color:#64748b;">
{{ __('Обходихме :n страници (до :limit за една проверка).', ['n' => $pagesChecked, 'limit' => $crawl['limit']]) }}
@if(! empty($crawl['limited']))<strong style="color:#b45309;">{{ __('Обхождането беше ограничено по размер или време, затова не всички страници са проверени. Подробният одит обхваща целия сайт.') }}</strong>@endif
</td></tr>
@endif

@if($speed)
<tr><td style="padding:14px {{ $pad }}px 0;{{ $f }}font-size:14px;line-height:1.55;color:#475569;">
<strong style="color:#0f1a2b;">{{ __('Скорост на телефон (Google PageSpeed)') }}</strong>
{{ $speed['score'] }}/100 @if($speed['lcp'] !== null) · LCP {{ number_format($speed['lcp'], 1, ',', '') }} s @endif @if($speed['cls'] !== null) · CLS {{ number_format($speed['cls'], 2, ',', '') }} @endif @if($speed['inp'] !== null) · INP {{ $speed['inp'] }} ms @endif
<span style="color:#94a3b8;font-size:12px;">({{ ($speed['source'] ?? 'lab') === 'field' ? __('данни от реални посетители') : __('лабораторен тест') }})</span>
</td></tr>
@endif

<tr><td style="padding:26px {{ $pad }}px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => __('Оценка по области'), 'color' => '#2d63a3'])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($areas as $row)
@php [$c, $bg] = $tones[\App\Support\SiteCheck\Areas::tone($row['score'])]; @endphp
<tr><td style="padding:10px 0 4px;{{ $f }}font-size:15px;line-height:1.4;color:#0f1a2b;"><strong>{{ $row['label'] }}</strong><span style="display:block;font-size:12px;color:#64748b;">{{ $row['hint'] }}</span></td>
<td width="56" align="right" valign="top" style="padding:10px 0 4px;{{ $f }}font-size:20px;font-weight:bold;color:{{ $c }};">{{ $row['score'] }}</td></tr>
<tr><td colspan="2" style="padding:0 0 8px;border-bottom:1px solid #eef1f5;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td width="{{ max(2, $row['score']) }}%" height="6" bgcolor="{{ $c }}" style="background:{{ $c }};height:6px;line-height:6px;font-size:0;border-radius:3px;">&nbsp;</td><td height="6" bgcolor="#e5e9f0" style="background:#e5e9f0;height:6px;line-height:6px;font-size:0;border-radius:3px;">&nbsp;</td></tr></table></td></tr>
@endforeach
</table>
<div style="{{ $f }}font-size:12px;line-height:1.6;color:#94a3b8;padding-top:8px;">{{ __('Оценките по области са от нашите автоматични проверки. Не са позиция в Google и не обещават резултат.') }}</div>
</td></tr>

@foreach($byArea as $areaKey => $areaItems)
@php
    $row = $areas[$areaKey];
    [$c, $bg] = $tones[\App\Support\SiteCheck\Areas::tone($row['score'])];
    $shown = array_slice($areaItems, 0, $perArea);
    $more = count($areaItems) - count($shown);
@endphp
<tr><td style="padding:30px {{ $pad }}px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td style="{{ $f }}font-size:18px;line-height:1.3;font-weight:bold;color:#0f1a2b;">{{ $row['label'] }}</td>
<td width="60" align="right" style="{{ $f }}"><span style="display:inline-block;padding:3px 10px;border-radius:10px;background:{{ $bg }};color:{{ $c }};font-size:13px;font-weight:bold;">{{ $row['score'] }}</span></td>
</tr></table>
@if($areaKey === 'tracking' && $signals && (! empty($signals['tracking']) || ! empty($signals['consent'])))
<div style="{{ $f }}font-size:13px;line-height:1.6;color:#64748b;padding-top:6px;">
@if(! empty($signals['tracking'])){{ __('Открихме на сайта') }} {{ implode(', ', $signals['tracking']) }}. @endif
@if(! empty($signals['consent'])){{ __('Изглежда има и инструмент за съгласие с бисквитки.') }}@endif
</div>
@endif
@if(! $shown)
<div style="{{ $f }}font-size:14px;line-height:1.55;color:#15803d;padding-top:8px;">&#10003; {{ __('Не открихме проблеми при проверените неща.') }}</div>
@else
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:4px;">
@foreach($shown as $check)
@php [$color, $mbg, $mark] = $sev[$check['severity']] ?? $sev['warning']; @endphp
<tr><td width="34" valign="top" style="padding:12px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;border-radius:11px;background:{{ $mbg }};color:{{ $color }};{{ $f }}font-size:13px;font-weight:bold;text-align:center;">{{ $mark }}</span></td>
<td style="padding:12px 0;{{ $f }}font-size:14px;line-height:1.55;color:#475569;border-bottom:1px solid #eef1f5;"><strong style="display:block;color:#0f1a2b;font-size:15px;">{{ $check['title'] }}</strong>{{ $check['detail'] }}
@if(! empty($check['examples']))
<div style="padding-top:6px;font-size:12px;line-height:1.5;color:#64748b;"><div style="font-weight:bold;">{{ __('Например') }}</div>
@foreach(array_slice($check['examples'], 0, 3) as $example)
<div style="word-break:break-all;">{{ \Illuminate\Support\Str::limit($example['url'], 80) }}@if(! empty($example['from'])) ← {{ \Illuminate\Support\Str::limit($example['from'], 60) }}@endif @if(! empty($example['note'])) ({{ \Illuminate\Support\Str::limit(\App\Support\SiteCheck\IssueCatalog::note($example['note']), 80) }})@endif</div>
@endforeach
</div>
@endif
</td></tr>
@endforeach
</table>
@if($more > 0)
<div style="{{ $f }}font-size:13px;line-height:1.6;color:#64748b;padding-top:8px;">{{ __('И още :n по-дребни неща в тази област. Подробният одит ги разглежда един по един.', ['n' => $more]) }}</div>
@endif
@endif
</td></tr>
@endforeach

@if($passed->isNotEmpty())
<tr><td style="padding:26px {{ $pad }}px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => __('Какво е наред'), 'color' => '#15803d'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => $passed->pluck('title')->all(), 'color' => '#15803d'])
</td></tr>
@endif

@if(! empty($shopping['checks']))
<tr><td style="padding:28px {{ $pad }}px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => __('Готовност за Google Shopping'), 'color' => '#2d63a3'])
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#475569;padding-top:8px;">{{ __('Сайтът прилича на онлайн магазин. Google Shopping се очаква в България, но официална дата още няма. Първи печелят магазините с готови данни в деня на старта. Днес :ok от :total неща са наред.', ['ok' => $shopping['ok'], 'total' => $shopping['total']]) }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($shopping['checks'] as $shopCheck)
@php
    $isOk = $shopCheck['status'] === 'ok';
    $shopText = \App\Support\SiteCheck\ShoppingReadiness::describe($shopCheck);
@endphp
<tr><td width="34" valign="top" style="padding:10px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;border-radius:11px;background:{{ $isOk ? '#dcfce7' : '#fee2e2' }};color:{{ $isOk ? '#15803d' : '#b91c1c' }};{{ $f }}font-size:13px;font-weight:bold;text-align:center;">{{ $isOk ? '✓' : '✕' }}</span></td>
<td style="padding:10px 0;{{ $f }}font-size:14px;line-height:1.55;color:#475569;border-bottom:1px solid #eef1f5;"><strong style="display:block;color:#0f1a2b;font-size:15px;">{{ $shopText['title'] }}</strong>@if($shopText['detail']){{ $shopText['detail'] }}@endif</td></tr>
@endforeach
</table>
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#475569;padding-top:12px;">{{ __('Какво включва подготовката на магазина') }} <a href="{{ \App\Http\Controllers\PageController::serviceUrl('shopping') }}" style="color:#2d63a3;font-weight:bold;text-decoration:none;">{{ __('Подготовка за Google Shopping') }}</a>.</div>
</td></tr>
@endif

<tr><td style="padding:26px {{ $pad }}px 0;{{ $f }}font-size:13px;line-height:1.6;color:#64748b;">
{{ __('Проверката е автоматична. Обхожда до :limit страници и гледа техническите основи. Не оценява качеството на текстовете, профила в Google Business и дали сайтът носи запитвания, и не знае как се подрежда в Google.', ['limit' => $crawl['limit'] ?? config('creatium.site_check.crawl_limit')]) }}
</td></tr>

<tr><td style="padding:26px {{ $pad }}px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:12px;">
<tr><td style="padding:26px 26px 24px;{{ $f }}">
<div style="font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#9cc0eb;">{{ __('Следваща стъпка') }}</div>
<div style="font-size:19px;line-height:1.35;font-weight:bold;color:#ffffff;padding-top:8px;">{{ __('Искате ли подробен одит?') }}</div>
<div style="font-size:14px;line-height:1.6;color:#d6e3f5;padding-top:8px;">{{ __('Преглеждаме целия сайт ръчно. Гледаме скоростта на телефон, текстовете, Google профила и какво правят конкурентите ви. Получавате план какво да оправите първо и какво после, а ако искате, го оправяме ние.') }}</div>
<div style="font-size:14px;line-height:1.6;color:#ffffff;font-weight:bold;padding-top:8px;">{{ $priceNote ?: __('Цената зависи от големината на сайта и я казваме предварително.') }}</div>
<div style="height:16px;line-height:16px;font-size:0;">&nbsp;</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#ffffff" style="background:#ffffff;border-radius:10px;"><a href="mailto:{{ $contact['email'] }}?subject={{ $auditSubject }}" style="display:inline-block;padding:12px 20px;{{ $f }}font-size:14px;font-weight:bold;color:#0f1a2b;text-decoration:none;">{{ __('Искам подробен одит') }}</a></td>
<td width="10" style="font-size:0;">&nbsp;</td>
<td style="border:1px solid #4b6a92;border-radius:10px;"><a href="{{ lroute('contact') }}" style="display:inline-block;padding:11px 18px;{{ $f }}font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">{{ __('Свържете се с нас') }}</a></td>
</tr></table>
</td></tr>
</table>
</td></tr>
<tr><td style="height:28px;line-height:28px;font-size:0;">&nbsp;</td></tr>
@endsection

@section('ps')
@if($phone)
{{ __('Ако имате въпрос за някой от резултатите, отговорете на този имейл или ни звъннете на') }} <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" style="color:#2d63a3;font-weight:bold;text-decoration:none;">{{ $phone }}</a>.
@else
{{ __('Ако имате въпрос за някой от резултатите, просто отговорете на този имейл.') }}
@endif
@endsection
