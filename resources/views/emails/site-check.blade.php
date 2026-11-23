@extends('emails.client.layout')
@php
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $contact = config('creatium.contact');
    $phone = g_phones()[0] ?? null;
    $score = $report['score'];
    [$scoreColor, $verdict] = match (true) {
        ! $report['reachable'] => ['#B42318', __('Не успяхме да отворим сайта.')],
        $score >= 85 => ['#1F7A3D', __('Основата е добра. Има само дребни неща за оправяне.')],
        $score >= 60 => ['#8A5A00', __('Има няколко неща, които си струва да оправите.')],
        default => ['#B42318', __('Има важни проблеми, които пречат на сайта в Google и при клиентите.')],
    };
    $crawl = $report['crawl'] ?? null;
    $pagesChecked = (int) ($crawl['analyzed'] ?? 0);
    $issues = collect(\App\Support\SiteCheck\SiteAudit::items($report));
    $byArea = \App\Support\SiteCheck\SiteAudit::itemsByArea($report);
    $areas = \App\Support\SiteCheck\Areas::scores($report);
    $top = \App\Support\SiteCheck\Areas::top($issues->all(), 3);
    $signals = $crawl['signals'] ?? null;
    $tones = ['green' => ['#1F7A3D', '#E3F1E7'], 'amber' => ['#8A5A00', '#F6ECD6'], 'red' => ['#B42318', '#F9E3E0'], 'gray' => ['#5A6068', '#D9DBDE']];
    $sev = ['error' => ['#B42318', '#F9E3E0', '✕'], 'warning' => ['#8A5A00', '#F6ECD6', '!'], 'notice' => ['#C4300F', '#ECEEF0', 'i']];
    $speed = $report['pagespeed'] ?? null;
    // Данните за бизнеса не са „наред“, ако обхождането е намерило грешка в тях.
    $schemaProblem = $issues->contains(fn ($i) => str_starts_with((string) $i['key'], 'schema_'));
    $passed = collect($report['checks'])->where('status', 'ok')->reject(fn ($c) => $schemaProblem && ($c['key'] ?? null) === 'schema');
    $shopping = $report['shopping'] ?? null;
    $pad = 28;

    // Какво бихме направили за най-слабите области: до три, най-слабите първи. Без слаба област има един общ ред.
    $weak = collect($areas)->filter(fn ($r) => $r['score'] < 80 && count($byArea[$r['area']] ?? []) > 0)->sortBy('score')->take(3)->keys()->all();
    $shoppingGap = ! empty($shopping['checks']) && $shopping['ok'] < $shopping['total'];
    $helpKeys = $shoppingGap ? array_slice(array_merge(['shopping'], $weak), 0, 3) : $weak;
    $helpMap = [
        'technical' => ['website', __('Изграждаме или обновяваме сайта така, че да е бърз, ясен на телефон и да се отваря без грешки.')],
        'content' => ['seo', __('Пренаписваме заглавията и текстовете така, че да казват ясно какво предлагате и да се намират в Google.')],
        'ai' => ['seo', __('Подреждаме данните за фирмата така, че Google и AI асистентите като ChatGPT и Gemini да ви намират и препоръчват.')],
        'local' => ['google', __('Подреждаме профила в Google, контактите и отзивите, за да ви намират хората от вашия град и в Google Maps.')],
        'tracking' => ['support', __('Настройваме проследяването на запитванията и изпращаме месечен отчет, за да виждате откъде идва всеки клиент.')],
        'shopping' => ['shopping', __('Подготвяме продуктовите данни на магазина за Google Shopping, преди да е стартирал.')],
    ];
    $helpLabels = ['shopping' => __('Google Shopping')] + array_map(fn ($r) => $r['label'], $areas);
    $bookUrl = lroute('contact') . '#konsultatsiya';
    $team = collect(site('team'))->map(fn ($m) => explode(' ', trim($m['name']))[0])->filter()->values()->all();
    $teamNames = count($team) > 1 ? implode(', ', array_slice($team, 0, -1)) . ' ' . __('и') . ' ' . end($team) : ($team[0] ?? 'Creatium Lab');
    $lbl = fn ($t, $c = '#C4300F') => ['part' => 'label', 'text' => $t, 'color' => $c];
@endphp

@section('eyebrow', __('Безплатна проверка на сайт'))
@section('headline'){{ $report['domain'] }}@endsection
@section('sub'){{ __('Здравейте, проверката на :domain е готова. Резултатът е по-долу, а след него ви разказваме кои сме и как бихме помогнали.', ['domain' => $report['domain']]) }}@endsection

@section('body')
<tr><td class="g-pad" style="padding:28px {{ $pad }}px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFFFFF;border:1px solid #D9DBDE;">
<tr>
<td width="110" align="center" valign="middle" style="padding:20px 0 20px 16px;{{ $f }}">
<div style="font-size:44px;line-height:1;font-weight:bold;color:{{ $scoreColor }};">{{ $score }}</div>
<div style="font-size:12px;color:#5A6068;padding-top:4px;">{{ __('от 100') }}</div>
</td>
<td valign="middle" style="padding:20px 18px 20px 12px;{{ $f }}font-size:15px;line-height:1.55;color:#0C0E12;"><strong>{{ $verdict }}</strong><br><span style="color:#5A6068;font-size:13px;">{{ __(':issues за оправяне · :passed наред', ['issues' => $issues->where('severity', '!=', 'notice')->count(), 'passed' => $passed->count()]) }}</span></td>
</tr>
</table>
@if($crawl && $pagesChecked > 0)
<div style="{{ $f }}font-size:13px;line-height:1.6;color:#5A6068;padding-top:8px;">{{ __('Обходихме :n страници от сайта.', ['n' => $pagesChecked]) }}</div>
@endif
</td></tr>

@if($top)
<tr><td class="g-pad" style="padding:26px {{ $pad }}px 0;">
@include('emails.client._parts', $lbl(__('Най-важното, което открихме'), '#B42318'))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($top as $t)
<tr><td width="34" valign="top" style="padding:12px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;background:#0C0E12;color:#FFFFFF;{{ $f }}font-size:12px;font-weight:bold;text-align:center;">{{ $loop->iteration }}</span></td>
<td style="padding:12px 0;{{ $f }}font-size:14px;line-height:1.55;color:#4A5058;border-bottom:1px solid #D9DBDE;"><strong style="display:block;color:#0C0E12;font-size:15px;">{{ $t['title'] }}</strong>{{ \Illuminate\Support\Str::limit((string) $t['detail'], 170) }}</td></tr>
@endforeach
</table>
</td></tr>
@endif

@if($speed)
<tr><td class="g-pad" style="padding:14px {{ $pad }}px 0;{{ $f }}font-size:14px;line-height:1.55;color:#4A5058;">
<strong style="color:#0C0E12;">{{ __('Скорост на телефон (Google PageSpeed)') }}</strong>
{{ $speed['score'] }}/100 @if($speed['lcp'] !== null) · LCP {{ number_format($speed['lcp'], 1, ',', '') }} s @endif
<span style="color:#6B7178;font-size:12px;">({{ ($speed['source'] ?? 'lab') === 'field' ? __('данни от реални посетители') : __('лабораторен тест') }})</span>
</td></tr>
@endif

<tr><td class="g-pad" style="padding:28px {{ $pad }}px 0;">
@include('emails.client._parts', $lbl(__('Оценка по области')))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($areas as $areaKey => $row)
@php
    [$c, $bg] = $tones[\App\Support\SiteCheck\Areas::tone($row['score'])];
    $areaItems = $byArea[$areaKey] ?? [];
    $n = count($areaItems);
    $count = $n === 0 ? __('Без находки') : ($n === 1 ? __('1 находка') : __(':n находки', ['n' => $n]));
@endphp
<tr><td style="padding:12px 0 4px;{{ $f }}font-size:15px;line-height:1.4;color:#0C0E12;"><strong>{{ $row['label'] }}</strong><span style="display:block;font-size:12px;color:#5A6068;">{{ $row['hint'] }}</span></td>
<td width="56" align="right" valign="top" style="padding:12px 0 4px;{{ $f }}font-size:20px;font-weight:bold;color:{{ $c }};">{{ $row['score'] }}</td></tr>
<tr><td colspan="2" style="padding:0 0 6px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td width="{{ max(2, $row['score']) }}%" height="6" bgcolor="{{ $c }}" style="background:{{ $c }};height:6px;line-height:6px;font-size:0;">&nbsp;</td><td height="6" bgcolor="#D9DBDE" style="background:#D9DBDE;height:6px;line-height:6px;font-size:0;">&nbsp;</td></tr></table></td></tr>
<tr><td colspan="2" style="padding:0 0 12px;border-bottom:1px solid #D9DBDE;{{ $f }}font-size:13px;line-height:1.55;color:#4A5058;"><strong style="color:{{ $c }};">{{ $count }}</strong>@foreach(array_slice($areaItems, 0, 2) as $item)<span style="display:block;">{{ $item['title'] }}</span>@endforeach
@if($areaKey === 'tracking' && $signals && (! empty($signals['tracking']) || ! empty($signals['consent'])))
<span style="display:block;color:#5A6068;">@if(! empty($signals['tracking'])){{ __('Открихме на сайта') }} {{ implode(', ', $signals['tracking']) }}. @endif @if(! empty($signals['consent'])){{ __('Изглежда има и инструмент за съгласие с бисквитки.') }}@endif</span>
@endif
</td></tr>
@endforeach
</table>
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#0C0E12;padding-top:12px;">{{ __('Пълният списък с точните страници и как се поправя всяко нещо е част от личния преглед и разговора с екипа.') }} {{ __('Специалист от нашия екип ще ви изпрати и личен преглед на сайта до два работни дни.') }}</div>
<div style="{{ $f }}font-size:12px;line-height:1.6;color:#6B7178;padding-top:8px;">{{ __('Оценките са от нашите автоматични проверки. Не са позиция в Google и не обещават резултат.') }}</div>
</td></tr>

@if($shoppingGap || ! empty($shopping['checks']))
<tr><td class="g-pad" style="padding:26px {{ $pad }}px 0;">
@include('emails.client._parts', $lbl(__('Готовност за Google Shopping')))
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#4A5058;padding-top:8px;">{{ __('Сайтът прилича на онлайн магазин. Google Shopping се очаква в България, но официална дата още няма. Първи печелят магазините с готови данни в деня на старта. Днес :ok от :total неща са наред.', ['ok' => $shopping['ok'], 'total' => $shopping['total']]) }} <a href="{{ \App\Http\Controllers\PageController::serviceUrl('shopping') }}" style="color:#C4300F;font-weight:bold;text-decoration:none;white-space:nowrap;">{{ __('Подготовка за Google Shopping') }} &rarr;</a></div>
</td></tr>
@endif

@if($passed->isNotEmpty())
<tr><td class="g-pad" style="padding:26px {{ $pad }}px 0;">
@include('emails.client._parts', $lbl(__('Вече е наред'), '#1F7A3D'))
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => $passed->pluck('title')->take(4)->all(), 'color' => '#1F7A3D'])
</td></tr>
@endif

<tr><td class="g-pad" style="padding:34px {{ $pad }}px 0;">
<div style="border-top:1px solid #D9DBDE;line-height:1px;font-size:0;">&nbsp;</div>
<div style="padding-top:26px;">@include('emails.client._parts', $lbl(__('Кои сме')))</div>
<div style="font-family:Geologica,Segoe UI,Arial,sans-serif;font-size:22px;line-height:1.3;font-weight:300;color:#0C0E12;padding-top:8px;">{{ __('Екип от трима души, който се грижи за сайта ви') }}</div>
<div style="{{ $f }}font-size:15px;line-height:1.65;color:#4A5058;padding-top:10px;">{{ __('Creatium Lab е екип от трима души, :names. Започнахме го, защото малките български фирми получават или евтин сайт, за който после никой не се грижи, или голяма агенция, която не могат да си позволят.', ['names' => $teamNames]) }}</div>
<div style="{{ $f }}font-size:15px;line-height:1.65;color:#4A5058;padding-top:10px;">{{ __('Искаме един екип, който изгражда сайта и го поддържа работещ и видим. Говорите директно с хората, които вършат работата.') }}</div>
<div style="{{ $f }}font-size:15px;line-height:1.65;color:#4A5058;padding-top:10px;">{{ __('Работим с малки и средни фирми и услуги, например клиники, счетоводители, къщи за гости, автосервизи и онлайн магазини.') }}</div>
</td></tr>

<tr><td class="g-pad" style="padding:30px {{ $pad }}px 0;">
@include('emails.client._parts', $lbl(__('Как бихме помогнали на вашия сайт')))
@if($helpKeys)
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($helpKeys as $hk)
@php [$svc, $sentence] = $helpMap[$hk]; @endphp
<tr><td style="padding:14px 0;border-bottom:1px solid #D9DBDE;{{ $f }}font-size:14px;line-height:1.6;color:#4A5058;"><strong style="display:block;color:#0C0E12;font-size:15px;">{{ $helpLabels[$hk] }}</strong>{{ $sentence }} <a href="{{ \App\Http\Controllers\PageController::serviceUrl($svc) }}" style="color:#C4300F;font-weight:bold;text-decoration:none;white-space:nowrap;">{{ __('Вижте как работим') }} &rarr;</a></td></tr>
@endforeach
</table>
@else
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#4A5058;padding-top:10px;">{{ __('Основата на сайта ви е добра. Можем да се грижим тя да остане такава, със следене на сайта всеки месец и отчет.') }} <a href="{{ \App\Http\Controllers\PageController::serviceUrl('support') }}" style="color:#C4300F;font-weight:bold;text-decoration:none;white-space:nowrap;">{{ __('Вижте как работим') }} &rarr;</a></div>
@endif
</td></tr>

<tr><td class="g-pad" style="padding:30px {{ $pad }}px 0;">
@include('emails.client._parts', $lbl(__('Какво се променя')))
<div style="{{ $f }}font-size:15px;line-height:1.65;color:#4A5058;padding-top:8px;">{{ __('Когато работата е свършена, сайтът е бърз и ясен на телефон, вижда се в Google, в Google Maps и в AI асистентите и води до запитвания. Някой го следи всеки месец и ви изпраща отчет какво е направено. Това описва как работим, а не обещава конкретен резултат.') }}</div>
</td></tr>

<tr><td class="g-pad" style="padding:30px {{ $pad }}px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0C0E12" style="background:#0C0E12;">
<tr><td style="padding:26px 24px 24px;{{ $f }}">
<div style="font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#E8431B;">{{ __('Следваща стъпка') }}</div>
<div style="font-size:20px;line-height:1.35;font-weight:bold;color:#FFFFFF;padding-top:8px;">{{ __('Нека го обсъдим заедно') }}</div>
<div style="font-size:14px;line-height:1.6;color:#C9CDD3;padding-top:8px;">{{ __('Запазете час за разговор. Ще разгледаме резултата, целите ви и какво има смисъл да се направи първо.') }}</div>
<div style="height:16px;line-height:16px;font-size:0;">&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td align="center" bgcolor="#FFFFFF" style="background:#FFFFFF;"><a href="{{ $bookUrl }}" style="display:block;padding:14px 20px;{{ $f }}font-size:15px;font-weight:bold;color:#0C0E12;text-decoration:none;text-align:center;">{{ __('Запазете разговор') }}</a></td>
</tr></table>
<div style="{{ $f }}font-size:14px;line-height:1.6;padding-top:14px;"><a href="{{ lroute('packages') }}" style="color:#FFFFFF;font-weight:bold;text-decoration:underline;">{{ __('Вижте пакетите ни') }}</a></div>
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#C9CDD3;padding-top:12px;">
@if($phone){{ __('Можете и просто да отговорите на този имейл или да ни звъннете на') }} <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" style="color:#FFFFFF;font-weight:bold;text-decoration:none;white-space:nowrap;">{{ $phone }}</a>.
@else{{ __('Можете и просто да отговорите на този имейл.') }}@endif
</div>
</td></tr>
</table>
</td></tr>

<tr><td class="g-pad" style="padding:22px {{ $pad }}px 0;{{ $f }}font-size:12px;line-height:1.6;color:#6B7178;">
{{ __('Проверката е автоматична и гледа техническите основи на сайта. Не оценява качеството на текстовете и профила в Google Business, не знае как сайтът се подрежда в Google и не показва дали носи запитвания.') }}
</td></tr>
<tr><td style="height:4px;line-height:4px;font-size:0;">&nbsp;</td></tr>
@endsection

@section('sender', $teamNames . ', Creatium Lab')

@section('after_signature')
<a href="{{ lroute('home') }}" style="color:#C4300F;font-weight:bold;text-decoration:none;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>
@endsection
