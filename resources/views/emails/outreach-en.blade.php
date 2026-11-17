{{-- First email to a business abroad (UK, Ireland, Netherlands), in British English. Plain business letter, no images, tables and inline styles only.
     Same variables and tracked links as emails.outreach. Lines starting with a dash in the observation become a numbered list (OutreachMail::observationParts).
     House rule for the English letter: no em or en dashes and no colons except in times and URLs. --}}
@php
    $contact = config('creatium.contact');
    $legal = config('creatium.legal');
    $phone = $contact['phones'][0] ?? null;
    $tel = $phone ? preg_replace('/[^0-9+]/', '', $phone) : null;
    $host = parse_url(url('/'), PHP_URL_HOST);
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $link = 'color:#C4300F;text-decoration:underline;';
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

<tr><td style="padding:0;"><div style="height:4px;line-height:4px;font-size:0;background:#C4300F;">&nbsp;</div></td></tr>

<tr><td style="padding:24px 32px 0;{{ $f }}">
<span style="font-family:Geologica,Segoe UI,Arial,sans-serif;font-size:18px;font-weight:bold;color:#0C0E12;">Creatium<span style="color:#C4300F;"> Lab</span></span>
<span style="font-size:12px;color:#5A6068;">&nbsp;·&nbsp;Websites and marketing for small businesses</span>
</td></tr>

<tr><td style="padding:22px 32px 0;{{ $f }}font-size:15px;line-height:1.65;color:#0C0E12;">
<p style="margin:0 0 14px;">Hello{{ filled($data['first_name'] ?? null) ? ' ' . $data['first_name'] : '' }},</p>
<p style="margin:0 0 14px;">{{ $teamSignature ? 'We are Creatium Lab, a small digital agency.' : 'My name is ' . $sender . ' and I am with Creatium Lab, a small digital agency.' }} We build and look after websites for small businesses, and we help them get found on Google, on Google Maps and in AI assistants.</p>
@foreach($parts['intro'] as $paragraph)
<p style="margin:0 0 14px;">{{ $paragraph }}</p>
@endforeach
</td></tr>

@if($parts['items'])
<tr><td style="padding:2px 32px 6px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFFFFF;border:1px solid #D9DBDE;">
@foreach($parts['items'] as $i => $item)
<tr><td style="padding:{{ $loop->first ? '16px' : '6px' }} 18px {{ $loop->last ? '16px' : '6px' }};">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td valign="top" style="padding-right:12px;"><div style="width:22px;height:22px;line-height:22px;background:#C4300F;color:#FFFFFF;text-align:center;{{ $f }}font-size:12px;font-weight:bold;">{{ $i + 1 }}</div></td>
<td valign="top" style="{{ $f }}font-size:14px;line-height:1.55;color:#0C0E12;"><strong style="color:#0C0E12;">{{ $item['title'] }}</strong>@if($item['detail'] !== '')<br><span style="color:#4A5058;">{{ $item['detail'] }}</span>@endif</td>
</tr></table>
</td></tr>
@endforeach
</table>
</td></tr>
@endif

<tr><td style="padding:14px 32px 0;{{ $f }}font-size:15px;line-height:1.65;color:#0C0E12;">
@foreach($parts['outro'] as $paragraph)
<p style="margin:0 0 14px;">{{ $paragraph }}</p>
@endforeach
@if($parts['items'])
<p style="margin:0 0 14px;">These are things a business owner rarely notices, because they do not show when you open your own site. They do affect how many enquiries and calls come in, and most can be fixed within a few days without a new website.</p>
<p style="margin:0 0 14px;">You can see the full result yourself. Our free website check emails a detailed report in about a minute. You can try it at <a href="{{ $links['check'] }}" style="{{ $link }}">{{ $links['check_label'] }}</a></p>
@else
<p style="margin:0 0 14px;">If you would like a technical report for your own site, our free website check emails one in about a minute. You can try it at <a href="{{ $links['check'] }}" style="{{ $link }}">{{ $links['check_label'] }}</a></p>
@endif
<p style="margin:0 0 14px;">You can read about our services and how we work at <a href="{{ $links['site'] }}" style="{{ $link }}">{{ $host }}/en</a></p>
<p style="margin:0 0 18px;">{{ $parts['items'] ? ($teamSignature ? 'If it would be useful, we can suggest a concrete plan for fixing them.' : 'If it would be useful, I can suggest a concrete plan for fixing them.') : ($teamSignature ? 'If you like, we can tell you what we would do for ' . $prospect->displayName() . '.' : 'If you like, I can tell you what I would do for ' . $prospect->displayName() . '.') }} Pick a time for a short call ({{ $links['minutes'] }} minutes) or simply reply to this email.</p>
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
We are writing because {{ $prospect->email }} is published as a contact for {{ $prospect->displayName() }}. We found the business on Google Maps and on its public website, and we think a short note about its website may be useful to you, which is our legitimate interest as a business writing to another business.
If you would rather not hear from us, <a href="{{ $unsubscribeUrl }}" style="color:#5A6068;">unsubscribe here</a> and we will not write again. You can read how we handle data in our <a href="{{ $privacy }}" style="color:#5A6068;">privacy policy</a>.
<br>{{ $legal['company'] }}@if($legal['eik']), company ID (EIK) {{ $legal['eik'] }}@endif @if($legal['address'])· {{ $legal['address'] }}@endif
</td></tr>
</table>

</td></tr>
</table>
</body>
</html>
