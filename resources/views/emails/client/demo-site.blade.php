@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $url = $data['demo_url'] ?? '';
@endphp

@section('eyebrow', 'Демо на вашия сайт')
@section('headline'){{ $data['client_name'] }}, ето демото за {{ $data['business'] }}@endsection
@section('sub', 'Както се разбрахме по телефона: разгледайте го спокойно, от компютъра и от телефона.')

@section('body')
<tr><td style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте, {{ $data['client_name'] }},</p>
<p style="margin:0;">Благодарим ви за разговора. Както обещахме, изпращаме ви демо версия, за да видите как може да изглежда сайтът на {{ $data['business'] }} и как клиентите ви ще стигат до обаждане или запитване.</p>
</td></tr>

@if(! empty($data['call_note']))
<tr><td style="padding:22px 36px 0;">@include('emails.client._parts', ['part' => 'quote', 'label' => 'От разговора ни', 'text' => $data['call_note']])</td></tr>
@endif

<tr><td align="center" style="padding:28px 36px 0;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="{{ $url }}" style="display:inline-block;padding:15px 30px;{{ $f }}font-size:16px;font-weight:bold;color:#ffffff;text-decoration:none;">Вижте демото &rarr;</a></td>
</tr></table>
<div style="{{ $f }}font-size:12px;line-height:1.5;color:#64748b;padding-top:10px;word-break:break-all;">Ако бутонът не се отваря: <a href="{{ $url }}" style="color:#2d63a3;">{{ $url }}</a></div>
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'На какво да обърнете внимание'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => [
    'Отворете го и от телефона: оттам ще ви търсят повечето клиенти.',
    'Колко бързо се стига до бутона за обаждане и до формата за запитване.',
    'Дали услугите и информацията са подредени така, както клиентите ви питат за тях.',
]])
</td></tr>

<tr><td style="padding:22px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;">
<tr><td style="padding:18px 22px;{{ $f }}font-size:14px;line-height:1.6;color:#475569;">
<strong style="color:#0f1a2b;">Това е демо.</strong> Текстовете, снимките, цените и цветовете са примерни и ги заменяме с вашите. Формите в него не изпращат истински запитвания.
</td></tr>
</table>
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво следва'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => [
    ['Разгледайте демото,', 'когато ви е удобно.'],
    ['Кажете ни впечатленията си:', 'какво ви харесва и какво бихте сменили. Достатъчно е да отговорите на този имейл.'],
    ['Подготвяме сайта за вас:', 'с вашите услуги, снимки и контакти, и ви го показваме преди да тръгне.'],
]])
</td></tr>

<tr><td style="padding:24px 36px 28px;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
@if(! empty($data['follow_up']))<p style="margin:0 0 14px;">{{ $data['follow_up'] }}</p>@endif
<p style="margin:0;">Ако имате въпроси, просто отговорете на този имейл@if($phone) или ни се обадете на <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" style="color:#2d63a3;font-weight:bold;text-decoration:none;">{{ $phone }}</a>@endif.</p>
</td></tr>
@endsection
