@extends('emails.client.layout')
@php
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $url = $data['demo_url'] ?? '';
@endphp

@section('eyebrow', 'Демо на вашия сайт')
@section('headline'){{ $data['client_name'] }}, ето демото за {{ $data['business'] }}@endsection
@section('sub', 'Както се разбрахме по телефона, разгледайте го спокойно, когато ви е удобно.')

@section('body')
<tr><td class="g-pad" style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#0C0E12;">
<p style="margin:0 0 14px;">Здравейте, {{ $data['client_name'] }},</p>
<p style="margin:0;">Благодарим ви за разговора. Както обещахме, изпращаме ви демо версия, за да видите как може да изглежда сайтът на {{ $data['business'] }} и как клиентите ви ще стигат до обаждане или запитване.</p>
</td></tr>

@if(! empty($data['call_note']))
<tr><td class="g-pad" style="padding:22px 36px 0;">@include('emails.client._parts', ['part' => 'quote', 'label' => 'От разговора ни', 'text' => $data['call_note']])</td></tr>
@endif

@if($url)
<tr><td align="center" style="padding:28px 36px 0;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="g-btn" bgcolor="#0C0E12" style="background:#0C0E12;"><a href="{{ $url }}" style="display:inline-block;padding:15px 30px;{{ $f }}font-size:16px;font-weight:bold;color:#FFFFFF;text-decoration:none;">Вижте демото &rarr;</a></td>
</tr></table>
<div style="{{ $f }}font-size:12px;line-height:1.5;color:#5A6068;padding-top:10px;word-break:break-all;">Ако бутонът не се отваря, ползвайте този адрес <a href="{{ $url }}" style="color:#C4300F;">{{ $url }}</a></div>
</td></tr>
@else
<tr><td class="g-pad" style="padding:28px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFFFFF;border:1px solid #D9DBDE;">
<tr><td align="center" style="padding:20px 24px;{{ $f }}font-size:16px;line-height:1.5;color:#0C0E12;font-weight:bold;">Демото е в прикачения PDF файл</td></tr>
</table>
</td></tr>
@endif

<tr><td class="g-pad" style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'На какво да обърнете внимание'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => [
    $url ? 'Отворете го и от телефона. Оттам ще ви търсят повечето клиенти.' : 'Вижте как изглежда и на телефона. Оттам ще ви търсят повечето клиенти.',
    'Колко бързо се стига до бутона за обаждане и до формата за запитване.',
    'Дали услугите и информацията са подредени така, както клиентите ви питат за тях.',
]])
</td></tr>

<tr><td class="g-pad" style="padding:22px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFFFFF;border:1px solid #D9DBDE;">
<tr><td style="padding:18px 22px;{{ $f }}font-size:14px;line-height:1.6;color:#4A5058;">
<strong style="color:#0C0E12;">Това е демо.</strong> Текстовете, снимките, цените и цветовете са примерни и ги заменяме с вашите.@if($url) Формите в него не изпращат истински запитвания.@endif
</td></tr>
</table>
</td></tr>

<tr><td class="g-pad" style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво следва'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => [
    ['Разгледайте демото,', 'когато ви е удобно.'],
    ['Кажете ни впечатленията си.', 'Какво ви харесва и какво бихте сменили? Достатъчно е да отговорите на този имейл.'],
    ['Подготвяме сайта за вас.', 'Слагаме вашите услуги, снимки и контакти и ви го показваме преди да тръгне.'],
]])
</td></tr>

<tr><td class="g-pad" style="padding:24px 36px 28px;{{ $f }}font-size:15px;line-height:1.65;color:#0C0E12;">
@if(! empty($data['follow_up']))<p style="margin:0 0 14px;">{{ $data['follow_up'] }}</p>@endif
<p style="margin:0;">Ако имате въпроси, просто отговорете на този имейл@if($phone) или ни се обадете на <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" style="color:#C4300F;font-weight:bold;text-decoration:none;">{{ $phone }}</a>@endif.</p>
</td></tr>
@endsection
