@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $questions = [
        'С какво се занимавате и кои са най-ценните ви клиенти?',
        'Имате ли вече сайт или домейн? Ако да, на какъв адрес?',
        'Какво искате да правят хората в сайта: да ви се обадят, да изпратят запитване, да видят цени?',
        'Колко страници си представяте? Например начало, услуги, цени, за нас, контакти.',
        'Имате ли лого, снимки и текстове, или да помогнем и с тях?',
        'Има ли сайт, който харесвате (на конкурент или на друг бизнес)?',
        'До кога бихте искали сайтът да е готов?',
    ];
@endphp

@section('eyebrow', 'Изработка на сайт')
@section('headline'){{ $data['client_name'] }}, благодарим ви за доверието@endsection
@section('sub', 'Прочетохме запитването ви лично. Ето какво следва и как можем да помогнем.')

@section('body')
<tr><td style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте, {{ $data['client_name'] }},</p>
<p style="margin:0;">Благодарим, че се обърнахте към нас за сайт за {{ $data['business'] }}. Няма да ви пратим шаблонна оферта „на сляпо“. Всеки бизнес е различен и искаме сайтът да носи клиенти точно на вас.</p>
</td></tr>

@if(! empty($data['personal_note']))
<tr><td style="padding:22px 36px 0;">@include('emails.client._parts', ['part' => 'quote', 'label' => 'Какво забелязахме', 'text' => $data['personal_note']])</td></tr>
@endif

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'С кого ще работите'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'why', 'items' => [
    ['Директно с нас двамата', 'Единият прави сайта, другият се грижи хората да ви намират в Google. Без посредници и без да ви прехвърлят от човек на човек.'],
    ['Ясна цена, преди да започнем', 'Получавате точна оферта: какво правим, за колко време и на каква цена. Започваме едва когато кажете „да“.'],
    ['Виждате сайта, преди да е готов', 'Показваме ви как върви работата и я съобразяваме с вас по пътя, не накрая.'],
]])
</td></tr>

<tr><td style="padding:24px 36px 8px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;">
<tr><td style="padding:22px 24px 4px;">@include('emails.client._parts', ['part' => 'label', 'text' => 'Няколко въпроса към вас'])</td></tr>
<tr><td style="padding:4px 24px 6px;{{ $f }}font-size:14px;line-height:1.6;color:#475569;">Отнемат около 5 минути и ни помагат да ви предложим точно това, което ви трябва. Отговорете направо на този имейл, може и накратко.</td></tr>
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
Не е нужно да избирате сега. След като се чуем, ще ви кажем честно кой пакет има смисъл за вас, дори ако е най-малкият.
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво следва'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => [
    ['Отговаряте', 'на въпросите или ни казвате удобен час за кратък разговор.'],
    ['Обаждаме ви се,', 'за да чуем повече за бизнеса ви и клиентите ви.'],
    ['Изпращаме оферта:', 'какво ще направим, за колко време и на каква цена, заедно с демо версия.'],
    ['Започваме', 'едва когато кажете „да“.'],
]])
</td></tr>

<tr><td style="padding:26px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:2px solid #0f1a2b;border-radius:12px;">
<tr><td align="center" style="padding:22px 24px;">
<div style="{{ $f }}font-size:17px;font-weight:bold;color:#0f1a2b;">Колкото по-рано се чуем, толкова по-скоро ще ви намират онлайн</div>
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#475569;padding:8px 0 4px;">Всеки ден без добър сайт е ден, в който хора, търсещи в Google услуги като вашите, намират някой друг.</div>
@if(! empty($data['slots']))
<div style="{{ $f }}font-size:14px;line-height:1.6;padding:10px 0 4px;"><span style="background:#fde68a;color:#0f1a2b;font-weight:bold;padding:3px 8px;border-radius:6px;">{{ $data['slots'] }}</span></div>
@endif
<div style="height:14px;line-height:14px;font-size:0;">&nbsp;</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Отговор на въпросите') }}" style="display:inline-block;padding:13px 22px;{{ $f }}font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">Отговарям на въпросите</a></td>
<td width="10" style="font-size:0;">&nbsp;</td>
<td style="border:2px solid #0f1a2b;border-radius:10px;"><a href="{{ route('portfolio') }}" style="display:inline-block;padding:11px 20px;{{ $f }}font-size:14px;font-weight:bold;color:#0f1a2b;text-decoration:none;">Вижте примери</a></td>
</tr></table>
</td></tr></table>
</td></tr>
<tr><td style="height:28px;line-height:28px;font-size:0;">&nbsp;</td></tr>
@endsection

@if($phone)
@section('ps')Ако ви е по-лесно да говорим, звъннете ни на <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" style="color:#2d63a3;font-weight:bold;text-decoration:none;">{{ $phone }}</a>. Вдигаме ние, не кол център.@endsection
@endif
