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
    $issues = collect($report['checks'])->where('status', '!=', 'ok')->sortBy(fn ($c) => $c['status'] === 'fail' ? 0 : 1);
    $passed = collect($report['checks'])->where('status', 'ok');
    $auditSubject = rawurlencode(__('Подробен одит за :domain', ['domain' => $report['domain']]));
    $priceNote = config('creatium.site_check.audit_price_note');
@endphp

@section('eyebrow', __('Безплатна проверка на сайт'))
@section('headline'){{ $report['domain'] }}@endsection
@section('sub'){{ __('Проверихме началната страница и основните настройки. Ето какво открихме.') }}@endsection

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

@if($issues->isNotEmpty())
<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => __('Какво да оправите')])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($issues as $check)
<tr><td width="34" valign="top" style="padding:12px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;border-radius:11px;background:{{ $check['status'] === 'fail' ? '#fee2e2' : '#fef3c7' }};color:{{ $check['status'] === 'fail' ? '#b91c1c' : '#b45309' }};{{ $f }}font-size:13px;font-weight:bold;text-align:center;">{{ $check['status'] === 'fail' ? '✕' : '!' }}</span></td>
<td style="padding:12px 0;{{ $f }}font-size:14px;line-height:1.55;color:#475569;border-bottom:1px solid #eef1f5;"><strong style="display:block;color:#0f1a2b;font-size:15px;">{{ $check['title'] }}</strong>{{ $check['detail'] }}</td></tr>
@endforeach
</table>
</td></tr>
@endif

@if($passed->isNotEmpty())
<tr><td style="padding:26px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => __('Какво е наред'), 'color' => '#15803d'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => $passed->pluck('title')->all(), 'color' => '#15803d'])
</td></tr>
@endif

<tr><td style="padding:26px 36px 0;{{ $f }}font-size:13px;line-height:1.6;color:#64748b;">
{{ __('Проверката е автоматична и гледа само началната страница. Не преглежда текстовете, скоростта на телефон, профила в Google Business и дали сайтът носи запитвания.') }}
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
