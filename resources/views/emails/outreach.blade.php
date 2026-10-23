{{-- Първо писмо до нов бизнес (Потенциални клиенти). Делово писмо без картинки и тежък дизайн, за да стига до входящата поща.
     Само таблици и inline стилове (Gmail, Outlook, телефон). Редовете с „– “ в наблюдението стават номериран списък (OutreachMail::observationParts).
     Връзките водят към сайта, безплатната проверка и записването за консултация, с UTM, за да се виждат в атрибуцията на запитванията. --}}
@php
    $contact = config('creatium.contact');
    $legal = config('creatium.legal');
    $phone = $contact['phones'][0] ?? null;
    $tel = $phone ? preg_replace('/[^0-9+]/', '', $phone) : null;
    $host = parse_url(url('/'), PHP_URL_HOST);
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $link = 'color:#2d63a3;text-decoration:underline;';
    $sender = $data['sender'];
    $teamSignature = str_contains($sender, 'Creatium');
@endphp
<!doctype html>
<html lang="bg">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f6f9;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f6f9;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">

<tr><td style="padding:0;"><div style="height:4px;line-height:4px;font-size:0;background:#2d63a3;border-radius:12px 12px 0 0;">&nbsp;</div></td></tr>

<tr><td style="padding:24px 32px 0;{{ $f }}">
<span style="font-size:17px;font-weight:bold;color:#0f1a2b;">Creatium<span style="color:#2d63a3;"> Lab</span></span>
<span style="font-size:12px;color:#64748b;">&nbsp;·&nbsp;Сайтове и маркетинг за малки бизнеси</span>
</td></tr>

<tr><td style="padding:22px 32px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте{{ filled($data['first_name'] ?? null) ? ', ' . $data['first_name'] : '' }},</p>
<p style="margin:0 0 14px;">{{ $teamSignature ? 'Пишем ви от Creatium Lab.' : 'Казвам се ' . $sender . ' и съм от Creatium Lab.' }} Изработваме и поддържаме сайтове за малки и средни бизнеси в България и се грижим да бъдат намирани в Google, в Google Maps и в AI асистентите.</p>
@foreach($parts['intro'] as $paragraph)
<p style="margin:0 0 14px;">{{ $paragraph }}</p>
@endforeach
</td></tr>

@if($parts['items'])
<tr><td style="padding:2px 32px 6px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
@foreach($parts['items'] as $i => $item)
<tr><td style="padding:{{ $loop->first ? '16px' : '6px' }} 18px {{ $loop->last ? '16px' : '6px' }};">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td valign="top" style="padding-right:12px;"><div style="width:22px;height:22px;line-height:22px;border-radius:11px;background:#2d63a3;color:#ffffff;text-align:center;{{ $f }}font-size:12px;font-weight:bold;">{{ $i + 1 }}</div></td>
<td valign="top" style="{{ $f }}font-size:14px;line-height:1.55;color:#1f2a3a;"><strong style="color:#0f1a2b;">{{ $item['title'] }}</strong>@if($item['detail'] !== '')<br><span style="color:#475569;">{{ $item['detail'] }}</span>@endif</td>
</tr></table>
</td></tr>
@endforeach
</table>
</td></tr>
@endif

<tr><td style="padding:14px 32px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
@foreach($parts['outro'] as $paragraph)
<p style="margin:0 0 14px;">{{ $paragraph }}</p>
@endforeach
@if($parts['items'])
<p style="margin:0 0 14px;">Това са проблеми, които собственикът на сайта обикновено не забелязва, защото не се виждат при нормално отваряне. Те обаче влияят пряко на броя запитвания и обаждания. Повечето се отстраняват за няколко дни, без нов сайт.</p>
<p style="margin:0 0 14px;">Можете да видите пълния резултат и сами. Безплатната ни проверка изпраща подробен доклад на имейла ви за около минута: <a href="{{ $links['check'] }}" style="{{ $link }}">{{ $links['check_label'] }}</a></p>
@else
<p style="margin:0 0 14px;">Ако искате да видите техническия доклад за сайта си, безплатната ни проверка го изпраща на имейла ви за около минута: <a href="{{ $links['check'] }}" style="{{ $link }}">{{ $links['check_label'] }}</a></p>
@endif
<p style="margin:0 0 14px;">Повече за услугите ни, начина ни на работа и цените ще намерите на <a href="{{ $links['site'] }}" style="{{ $link }}">{{ $host }}</a>.</p>
<p style="margin:0 0 18px;">{{ $parts['items'] ? 'Ако желаете, ще ви предложа конкретен план за отстраняването им.' : 'Ако желаете, ще ви кажа какво конкретно бих направил за ' . $prospect->displayName() . '.' }} Изберете удобен час за безплатен разговор ({{ $links['minutes'] }} минути) или просто отговорете на този имейл.</p>
</td></tr>

<tr><td style="padding:0 32px 24px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="border-radius:8px;"><a href="{{ $links['booking'] }}" style="display:inline-block;padding:12px 22px;{{ $f }}font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:8px;">Запазете час за разговор</a></td>
</tr></table>
</td></tr>

<tr><td style="padding:0 32px;"><div style="border-top:1px solid #e2e8f0;line-height:1px;font-size:0;">&nbsp;</div></td></tr>
<tr><td style="padding:18px 32px 26px;{{ $f }}font-size:14px;line-height:1.6;color:#1f2a3a;">
С уважение,<br>
<strong style="color:#0f1a2b;">{{ $sender }}</strong>@unless($teamSignature)<br><span style="color:#64748b;">Creatium Lab</span>@endunless<br>
@if($phone)<a href="tel:{{ $tel }}" style="color:#2d63a3;text-decoration:none;">{{ $phone }}</a> &nbsp;·&nbsp; @endif<a href="{{ $links['site'] }}" style="color:#2d63a3;text-decoration:none;">{{ $host }}</a>
</td></tr>

</table>

<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">
<tr><td style="padding:14px 8px 0;{{ $f }}font-size:12px;line-height:1.55;color:#64748b;">
Пишем ви, защото адресът {{ $prospect->email }} е публикуван като контакт на {{ $prospect->displayName() }}.
Ако не искате да получавате писма от нас, <a href="{{ $unsubscribeUrl }}" style="color:#64748b;">отпишете се тук</a> и няма да ви пишем повече.
<br>{{ $legal['company'] }}@if($legal['eik']), ЕИК {{ $legal['eik'] }}@endif @if($legal['address'])· {{ $legal['address'] }}@endif
</td></tr>
</table>

</td></tr>
</table>
</body>
</html>
