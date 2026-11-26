@extends('emails.client.layout')
{{-- Трите писма след отчета от безплатната проверка (App\Mail\SiteCheckFollowUpMail). $step 1 съвет, 2 оферта, 3 проверка отново и следене. --}}
@php
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $pad = 28;
    $team = collect(site('team'))->map(fn ($m) => explode(' ', trim($m['name']))[0])->filter()->values()->all();
    $teamNames = count($team) > 1 ? implode(', ', array_slice($team, 0, -1)) . ' ' . __('и') . ' ' . end($team) : ($team[0] ?? 'Creatium Lab');
    $lbl = fn ($t, $c = '#C4300F') => ['part' => 'label', 'text' => $t, 'color' => $c];
    $p = $f . 'font-size:15px;line-height:1.65;color:#4A5058;';
@endphp

@section('eyebrow', __('Проверка на сайт'))
@section('headline'){{ $domain }}@endsection
@section('sub')@if($step === 1){{ __('Здравейте, ето едно конкретно нещо от отчета за :domain.', ['domain' => $domain]) }}@elseif($step === 2){{ __('Здравейте, ето трите най-важни неща от отчета за :domain и как можем да ги оправим вместо вас.', ['domain' => $domain]) }}@else{{ __('Здравейте, от проверката на :domain мина известно време.', ['domain' => $domain]) }}@endif @endsection

@section('body')
@if($step === 1 && $first)
<tr><td class="g-pad" style="padding:26px {{ $pad }}px 0;">
@include('emails.client._parts', $lbl(__('Най-важното от вашия отчет'), '#B42318'))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:8px;background:#FFFFFF;border:1px solid #D9DBDE;">
<tr><td style="padding:16px 18px;{{ $f }}font-size:14px;line-height:1.6;color:#4A5058;"><strong style="display:block;color:#0C0E12;font-size:16px;padding-bottom:4px;">{{ $first['title'] }}</strong>{{ \Illuminate\Support\Str::limit((string) $first['detail'], 320) }}</td></tr>
</table>
<div style="{{ $p }}padding-top:16px;">{{ __('Покажете този ред на човека, който поддържа сайта ви. Той ще знае откъде да започне. Цялата проверка е в първото ни писмо.') }}</div>
<div style="{{ $p }}padding-top:10px;">{{ __('Ако предпочитате да ви помогнем, просто отговорете на този имейл.') }}</div>
</td></tr>
@elseif($step === 2)
<tr><td class="g-pad" style="padding:26px {{ $pad }}px 0;">
@include('emails.client._parts', $lbl(__('Най-важното, което открихме'), '#B42318'))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($top as $t)
<tr><td width="34" valign="top" style="padding:12px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;background:#0C0E12;color:#FFFFFF;{{ $f }}font-size:12px;font-weight:bold;text-align:center;">{{ $loop->iteration }}</span></td>
<td style="padding:12px 0;{{ $f }}font-size:14px;line-height:1.55;color:#4A5058;border-bottom:1px solid #D9DBDE;"><strong style="display:block;color:#0C0E12;font-size:15px;">{{ $t['title'] }}</strong>{{ \Illuminate\Support\Str::limit((string) $t['detail'], 170) }}</td></tr>
@endforeach
</table>
</td></tr>
@include('emails._site-check-offer', ['offer' => 'fix', 'url' => $fixUrl, 'pad' => $pad])
@else
<tr><td class="g-pad" style="padding:26px {{ $pad }}px 0;">
<div style="{{ $p }}">{{ __('Ако сте променяли нещо по сайта, пуснете проверката отново и вижте как изглежда сега. Тя е безплатна.') }}</div>
<div style="height:16px;line-height:16px;font-size:0;">&nbsp;</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0C0E12" style="background:#0C0E12;"><a href="{{ $checkUrl }}" style="display:inline-block;padding:13px 24px;{{ $f }}font-size:15px;font-weight:bold;color:#FFFFFF;text-decoration:none;">{{ __('Проверете сайта отново') }}</a></td>
</tr></table>
</td></tr>
@include('emails._site-check-offer', ['offer' => 'monitor', 'url' => $monitorUrl, 'pad' => $pad])
@endif

<tr><td class="g-pad" style="padding:24px {{ $pad }}px 0;{{ $f }}font-size:12px;line-height:1.6;color:#6B7178;">
{{ __('Получавате това писмо, защото поискахте проверка на :domain и се съгласихте да ви пишем до три пъти за отчета.', ['domain' => $domain]) }}
<a href="{{ $unsubscribeUrl }}" style="color:#C4300F;text-decoration:underline;">{{ __('Отпишете се с едно кликване') }}</a>.
</td></tr>
<tr><td style="height:4px;line-height:4px;font-size:0;">&nbsp;</td></tr>
@endsection

@section('sender', $teamNames . ', Creatium Lab')
