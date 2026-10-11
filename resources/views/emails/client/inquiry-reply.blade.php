@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $questions = [
        'С какво се занимавате и кои са основните ви клиенти?',
        'Имате ли вече сайт или домейн? Ако да, на какъв адрес?',
        'Какво искате да правят хората в сайта: да ви се обадят, да изпратят запитване, да видят цени?',
        'Колко страници си представяте? Например начало, услуги, цени, за нас, контакти.',
        'Имате ли лого, снимки и текстове, или да помогнем и с тях?',
        'Има ли сайт, който харесвате (ваш конкурент или друг бизнес)?',
        'До кога бихте искали сайтът да е готов?',
    ];
@endphp

@section('eyebrow', 'Изработка на сайт')
@section('headline')Благодарим за запитването, {{ $data['client_name'] }}@endsection
@section('sub', 'Ето как продължаваме, за да направим сайт, който работи за вашия бизнес.')

@section('body')
<tr><td style="padding:32px 36px 8px;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте, {{ $data['client_name'] }},</p>
<p style="margin:0;">Радваме се, че мислите за сайт за {{ $data['business'] }}. За да ви предложим точно това, което ви трябва, ще ни помогнат отговорите на няколко въпроса. Може и накратко.</p>
</td></tr>

<tr><td style="padding:20px 36px 8px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;">
<tr><td style="padding:22px 24px 10px;">@include('emails.client._parts', ['part' => 'label', 'text' => count($questions) . ' въпроса към вас'])</td></tr>
<tr><td style="padding:0 24px 18px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $f }}font-size:14px;line-height:1.55;color:#1f2a3a;">
@foreach($questions as $question)
<tr><td width="30" valign="top" style="padding:6px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;border-radius:11px;background:#0f1a2b;color:#ffffff;font-size:12px;font-weight:bold;text-align:center;">{{ $loop->iteration }}</span></td><td style="padding:6px 0;">{{ $question }}</td></tr>
@endforeach
</table>
</td></tr>
</table>
</td></tr>

<tr><td style="padding:28px 36px 6px;{{ $f }}">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Пакети'])
<div style="font-size:20px;font-weight:bold;color:#0f1a2b;padding-top:6px;">Три пакета, за да имате ориентир</div>
</td></tr>
<tr><td style="padding:12px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $f }}">
@foreach(site('packages') as $package)
    @php($dark = ! empty($package['highlighted']))
    <tr><td @if($dark) bgcolor="#0f1a2b" @endif style="{{ $dark ? 'background:#0f1a2b;' : 'border:1px solid #dde3ec;' }}border-radius:12px;padding:18px 20px;">
    <div style="font-size:16px;font-weight:bold;color:{{ $dark ? '#ffffff' : '#0f1a2b' }};">{{ $package['name'] }}@if($dark) <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:10px;background:#2d63a3;color:#ffffff;font-size:11px;font-weight:bold;letter-spacing:0.5px;vertical-align:middle;">ПРЕПОРЪЧАН</span>@endif</div>
    <div style="font-size:14px;line-height:1.55;color:{{ $dark ? '#d6e3f5' : '#475569' }};padding-top:4px;">{{ $package['description'] }}<br>{{ implode(' · ', $package['features'] ?? []) }}</div>
    </td></tr>
    @unless($loop->last)<tr><td style="height:10px;line-height:10px;font-size:0;">&nbsp;</td></tr>@endunless
@endforeach
</table>
</td></tr>
<tr><td style="padding:14px 36px 0;{{ $f }}font-size:14px;line-height:1.6;color:#475569;">
Цената зависи от това колко страници и какво съдържание ви трябва. Затова изпращаме точна оферта, след като се разберем какво искате.
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Как продължаваме'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => [
    ['Отговаряте', 'на въпросите или ни казвате удобен час за разговор.'],
    ['Обаждаме ви се,', 'за да изясним детайлите.'],
    ['Изпращаме оферта:', 'какво ще направим, за колко време и на каква цена, заедно с демо версия.'],
    ['Започваме', 'едва когато кажете „да“, и ви показваме как върви работата, преди сайтът да е готов.'],
]])
</td></tr>

<tr><td align="center" style="padding:26px 36px 8px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Отговор на въпросите') }}" style="display:inline-block;padding:13px 22px;{{ $f }}font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">Отговорете на въпросите</a></td>
<td width="10" style="font-size:0;">&nbsp;</td>
<td style="border:2px solid #0f1a2b;border-radius:10px;"><a href="{{ route('portfolio') }}" style="display:inline-block;padding:11px 20px;{{ $f }}font-size:14px;font-weight:bold;color:#0f1a2b;text-decoration:none;">Вижте примери</a></td>
</tr></table>
</td></tr>
@if($phone)
<tr><td align="center" style="padding:6px 36px 30px;{{ $f }}font-size:13px;color:#64748b;">
Предпочитате да говорим? <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" style="color:#2d63a3;text-decoration:none;font-weight:bold;">{{ $phone }}</a>
</td></tr>
@endif
@endsection
