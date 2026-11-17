{{-- Follow up (second and last email) to a business abroad that has not replied. Short, same style as the first email, no images. --}}
@php
    $contact = config('creatium.contact');
    $legal = config('creatium.legal');
    $phone = $contact['phones'][0] ?? null;
    $tel = $phone ? preg_replace('/[^0-9+]/', '', $phone) : null;
    $host = parse_url(url('/'), PHP_URL_HOST);
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $sender = $data['sender'];
    $teamSignature = str_contains($sender, 'Creatium');
    $privacy = route('en.privacy');
@endphp
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">@include('emails._font')</head>
<body style="margin:0;padding:0;background:#ECEEF0;font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ECEEF0;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#FFFFFF;border:1px solid #D9DBDE;">

<tr><td style="padding:24px 32px 0;{{ $f }}font-size:15px;line-height:1.65;color:#0C0E12;">
<p style="margin:0 0 14px;">Hello{{ filled($data['first_name'] ?? null) ? ' ' . $data['first_name'] : '' }},</p>
<p style="margin:0 0 14px;">{{ $teamSignature ? 'This is a short follow up to our email' : 'This is a short follow up to my email' }} of {{ $data['first_date'] }} about the website of {{ $prospect->displayName() }}.</p>
@if(filled($data['finding'] ?? null))
<p style="margin:0 0 14px;">The most important point from the check was "<strong style="color:#0C0E12;">{{ $data['finding'] }}</strong>". It can usually be fixed within a few days, without a new website.</p>
@endif
<p style="margin:0 0 18px;">If this is relevant for you, pick a time for a short call ({{ $links['minutes'] }} minutes) or simply reply to this email. If it is not, {{ $teamSignature ? 'we will not write to you again about it' : 'I will not write to you again about it' }}.</p>
</td></tr>

<tr><td style="padding:0 32px 24px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="g-btn" bgcolor="#0C0E12" style="background:#0C0E12;"><a href="{{ $links['booking'] }}" style="display:inline-block;padding:12px 22px;{{ $f }}font-size:14px;font-weight:bold;color:#FFFFFF;text-decoration:none;">Book a call</a></td>
</tr></table>
</td></tr>

<tr><td style="padding:0 32px;"><div style="border-top:1px solid #D9DBDE;line-height:1px;font-size:0;">&nbsp;</div></td></tr>
<tr><td style="padding:18px 32px 26px;{{ $f }}font-size:14px;line-height:1.6;color:#0C0E12;">
Kind regards,<br>
<strong style="color:#0C0E12;">{{ $sender }}</strong>@unless($teamSignature)<br><span style="color:#5A6068;">Creatium Lab</span>@endunless<br>
@if($phone)<a href="tel:{{ $tel }}" style="color:#C4300F;text-decoration:none;">{{ $phone }}</a> &nbsp;·&nbsp; @endif<a href="{{ $links['site'] }}" style="color:#C4300F;text-decoration:none;">{{ $host }}</a>
</td></tr>

</table>

<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">
<tr><td style="padding:14px 8px 0;{{ $f }}font-size:12px;line-height:1.55;color:#5A6068;">
We are writing because {{ $prospect->email }} is published as a contact for {{ $prospect->displayName() }}.
If you would rather not hear from us, <a href="{{ $unsubscribeUrl }}" style="color:#5A6068;">unsubscribe here</a> and we will not write again. You can read how we handle data in our <a href="{{ $privacy }}" style="color:#5A6068;">privacy policy</a>.
<br>{{ $legal['company'] }}@if($legal['eik']), company ID (EIK) {{ $legal['eik'] }}@endif @if($legal['address'])· {{ $legal['address'] }}@endif
</td></tr>
</table>

</td></tr>
</table>
</body>
</html>
