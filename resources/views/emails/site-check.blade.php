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
    $groups = [
        'error' => [__('Какво да оправите първо'), '#b91c1c', '#fee2e2', '✕'],
        'warning' => [__('Какво да подобрите после'), '#b45309', '#fef3c7', '!'],
    ];
    $speed = $report['pagespeed'] ?? null;
    $passed = collect($report['checks'])->where('status', 'ok');
    $auditSubject = rawurlencode(__('Подробен одит за :domain', ['domain' => $report['domain']]));
    $priceNote = config('creatium.site_check.audit_price_note');
@endphp

@section('eyebrow', __('Безплатна проверка на сайт'))
@section('headline'){{ $report['domain'] }}@endsection
@section('sub'){{ $pagesChecked > 1 ? __('Проверихме :n страници от сайта и основните настройки. Ето какво открихме.', ['n' => $pagesChecked]) : __('Проверихме началната страница и основните настройки. Ето какво открихме.') }}@endsection

@section('body')
<tr><td style="padding:30px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;">
<tr>
<td width="120" align="center" valign="middle" style="padding:22px 0 22px 20px;{{ $f }}">
<div style="font-size:44px;line-height:1;font-weight:bold;color:{{ $scoreColor }};">{{ $score }}</div>
<div style="font-size:12px;color:#64748b;padding-top:4px;">{{ __('от 100') }}</div>
</td>
<td valign="middle" style="padding:22px 24px 22px 12px;{{ $f }}font-size:15px;line-height:1.55;color:#0f1a2b;"><strong>{{ $verdict }}</strong><br><span style="color:#64748b;font-size:13px;">{{ __(':issues за оправяне · :passed наред', ['issues' => $issues->count(), 'passed' => $passed->count()]) }}</span></td>
</tr>
</table>
</td></tr>

@if($crawl && $pagesChecked > 0)
<tr><td style="padding:14px 36px 0;{{ $f }}font-size:13px;line-height:1.6;color:#64748b;">
{{ __('Обходени страници: :n (до :limit за една проверка).', ['n' => $pagesChecked, 'limit' => $crawl['limit']]) }}
@if(! empty($crawl['limited']))<strong style="color:#b45309;">{{ __('Обхождането беше ограничено по размер или време, затова не всички страници са проверени. Подробният одит обхваща целия сайт.') }}</strong>@endif
</td></tr>
@endif

@if($speed)
<tr><td style="padding:14px 36px 0;{{ $f }}font-size:14px;line-height:1.55;color:#475569;">
<strong style="color:#0f1a2b;">{{ __('Скорост на телефон (Google PageSpeed)') }}:</strong>
{{ $speed['score'] }}/100 @if($speed['lcp'] !== null) · LCP {{ number_format($speed['lcp'], 1, ',', '') }} s @endif @if($speed['cls'] !== null) · CLS {{ number_format($speed['cls'], 2, ',', '') }} @endif @if($speed['inp'] !== null) · INP {{ $speed['inp'] }} ms @endif
<span style="color:#94a3b8;font-size:12px;">({{ ($speed['source'] ?? 'lab') === 'field' ? __('данни от реални посетители') : __('лабораторен тест') }})</span>
</td></tr>
@endif

@foreach($groups as $severity => [$groupTitle, $color, $bg, $mark])
@php $groupItems = $issues->where('severity', $severity); @endphp
@if($groupItems->isNotEmpty())
<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => $groupTitle, 'color' => $color])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($groupItems as $check)
<tr><td width="34" valign="top" style="padding:12px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;border-radius:11px;background:{{ $bg }};color:{{ $color }};{{ $f }}font-size:13px;font-weight:bold;text-align:center;">{{ $mark }}</span></td>
<td style="padding:12px 0;{{ $f }}font-size:14px;line-height:1.55;color:#475569;border-bottom:1px solid #eef1f5;"><strong style="display:block;color:#0f1a2b;font-size:15px;">{{ $check['title'] }}</strong>{{ $check['detail'] }}
@if(! empty($check['examples']))
<div style="padding-top:6px;font-size:12px;line-height:1.5;color:#64748b;">{{ __('Например') }}:
@foreach($check['examples'] as $example)
<div style="word-break:break-all;">{{ \Illuminate\Support\Str::limit($example['url'], 80) }}@if(! empty($example['from'])) ← {{ \Illuminate\Support\Str::limit($example['from'], 60) }}@endif @if(! empty($example['note'])) ({{ \Illuminate\Support\Str::limit($example['note'], 80) }})@endif</div>
@endforeach
</div>
@endif
</td></tr>
@endforeach
</table>
</td></tr>
@endif
@endforeach

@if($passed->isNotEmpty())
<tr><td style="padding:26px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => __('Какво е наред'), 'color' => '#15803d'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => $passed->pluck('title')->all(), 'color' => '#15803d'])
</td></tr>
@endif

<tr><td style="padding:26px 36px 0;{{ $f }}font-size:13px;line-height:1.6;color:#64748b;">
{{ __('Проверката е автоматична: обхожда до :limit страници и гледа техническите основи. Не преглежда текстовете, профила в Google Business и дали сайтът носи запитвания.', ['limit' => $crawl['limit'] ?? config('creatium.site_check.crawl_limit')]) }}
</td></tr>

<tr><td style="padding:26px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:12px;">
<tr><td style="padding:26px 26px 24px;{{ $f }}">
<div style="font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#9cc0eb;">{{ __('Следваща стъпка') }}</div>
<div style="font-size:19px;line-height:1.35;font-weight:bold;color:#ffffff;padding-top:8px;">{{ __('Искате ли подробен одит?') }}</div>
<div style="font-size:14px;line-height:1.6;color:#d6e3f5;padding-top:8px;">{{ __('Преглеждаме целия сайт ръчно: скорост на телефон, текстове, Google профил и какво правят конкурентите ви. Получавате план какво да оправите първо и какво после, а ако искате, го оправяме ние.') }}</div>
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
