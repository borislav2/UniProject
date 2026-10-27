{{-- Напомняне (второ и последно писмо) до бизнес, който не е отговорил. Кратко, в същия стил като първото писмо, без картинки. --}}
@php
    $contact = config('creatium.contact');
    $legal = config('creatium.legal');
    $phone = $contact['phones'][0] ?? null;
    $tel = $phone ? preg_replace('/[^0-9+]/', '', $phone) : null;
    $host = parse_url(url('/'), PHP_URL_HOST);
    $f = 'font-family:Arial,Helvetica,sans-serif;';
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

<tr><td style="padding:24px 32px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте{{ filled($data['first_name'] ?? null) ? ', ' . $data['first_name'] : '' }},</p>
<p style="margin:0 0 14px;">{{ $teamSignature ? 'Пишем ви отново' : 'Пиша ви отново' }} накратко във връзка с писмото от {{ $data['first_date'] }} за сайта на {{ $prospect->displayName() }}.</p>
@if(filled($data['finding'] ?? null))
<p style="margin:0 0 14px;">Най-важното от проверката беше: <strong style="color:#0f1a2b;">{{ $data['finding'] }}</strong>. Обикновено се оправя за няколко дни, без нов сайт.</p>
@endif
<p style="margin:0 0 18px;">Ако темата е актуална за вас, изберете удобен час за кратък разговор ({{ $links['minutes'] }} минути) или просто отговорете на този имейл. Ако не е, няма да {{ $teamSignature ? 'ви пишем' : 'ви пиша' }} повече по нея.</p>
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
