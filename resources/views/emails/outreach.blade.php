{{-- Първо писмо до нов бизнес: умишлено почти обикновен текст (без картинки и бутони), защото такива писма стигат до входящата поща. --}}
@php
    $contact = config('creatium.contact');
    $legal = config('creatium.legal');
@endphp
<!doctype html>
<html lang="bg">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:24px 16px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#1f2a3a;">
<div style="max-width:560px;">
<p style="margin:0 0 14px;">Здравейте{{ filled($data['first_name'] ?? null) ? ', ' . $data['first_name'] : '' }},</p>
<p style="margin:0 0 14px;white-space:pre-line;">{{ $data['observation'] }}</p>
<p style="margin:0 0 14px;">Ние сме Creatium Lab: правим сайтове и маркетинг за малки бизнеси в България. Подобни неща обикновено се оправят бързо, а разликата се усеща в запитванията.</p>
<p style="margin:0 0 14px;">Ако ви е интересно, ще ви изпратим кратък безплатен преглед на сайта с няколко конкретни неща за подобряване. Да ви го пратим ли?</p>
<p style="margin:0 0 22px;">Поздрави,<br>{{ $data['sender'] }}<br>Creatium Lab · <a href="{{ url('/') }}" style="color:#2d63a3;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>@if(! empty($contact['phones'][0])) · {{ $contact['phones'][0] }}@endif</p>
<p style="margin:0;border-top:1px solid #e2e8f0;padding-top:12px;font-size:12px;line-height:1.5;color:#64748b;">
Пишем ви, защото адресът {{ $prospect->email }} е публикуван като контакт на {{ $prospect->displayName() }}.
Ако не искате да получавате писма от нас, <a href="{{ $unsubscribeUrl }}" style="color:#64748b;">отпишете се тук</a> и няма да ви пишем повече.
<br>{{ $legal['company'] }}@if($legal['eik']), ЕИК {{ $legal['eik'] }}@endif @if($legal['address'])· {{ $legal['address'] }}@endif
</p>
</div>
</body>
</html>
