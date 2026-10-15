{{-- Обща рамка за имейлите до клиенти (шаблоните в App\Support\ClientEmails). Само inline стилове и таблици: така изглеждат еднакво в Gmail, Outlook и на телефон. --}}
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $phones = $contact['phones'] ?? [];
    $tel = fn ($p) => preg_replace('/[^0-9+]/', '', $p);
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $subject ?? 'Creatium Lab' }}</title>
</head>
<body style="margin:0;padding:0;background:#eef1f5;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef1f5;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;">

<tr><td bgcolor="#0f1a2b" style="background:#0f1a2b;background-image:linear-gradient(135deg,#0f1a2b 0%,#22406a 55%,#2d63a3 100%);padding:32px 36px 36px;">
<img src="{{ asset('images/logo-white.png') }}" width="190" height="27" alt="Creatium Lab" style="display:block;border:0;width:190px;height:auto;">
<div style="height:28px;line-height:28px;font-size:0;">&nbsp;</div>
<div style="{{ $f }}font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#9cc0eb;">@yield('eyebrow')</div>
<div style="{{ $f }}font-size:26px;line-height:1.25;font-weight:bold;color:#ffffff;padding-top:8px;">@yield('headline')</div>
<div style="{{ $f }}font-size:15px;line-height:1.6;color:#d6e3f5;padding-top:10px;">@yield('sub')</div>
</td></tr>

@yield('body')

<tr><td style="padding:0 36px;"><div style="border-top:1px solid #e2e8f0;line-height:1px;font-size:0;">&nbsp;</div></td></tr>
<tr><td style="padding:22px 36px 30px;{{ $f }}font-size:14px;line-height:1.6;color:#1f2a3a;">
@php($signature = $data['sender'] ?? __('Екипът на Creatium Lab'))
{{ __('С уважение,') }}<br><strong style="color:#0f1a2b;">{{ $signature }}</strong>@unless(str_contains($signature, 'Creatium Lab'))<br><span style="color:#64748b;">Creatium Lab</span>@endunless
</td></tr>
@hasSection('ps')
<tr><td style="padding:0 36px 30px;{{ $f }}font-size:14px;line-height:1.6;color:#475569;"><strong style="color:#0f1a2b;">P.S.</strong> @yield('ps')</td></tr>
@endif

<tr><td bgcolor="#0f1a2b" style="background:#0f1a2b;padding:22px 36px;{{ $f }}font-size:13px;line-height:1.7;color:#9fb3cc;">
<strong style="color:#ffffff;">Creatium Lab</strong> · {{ __('Сайтове и дигитален маркетинг за малки бизнеси') }}<br>
@foreach($phones as $phone)<a href="tel:{{ $tel($phone) }}" style="color:#9cc0eb;text-decoration:none;">{{ $phone }}</a>@unless($loop->last) &nbsp;·&nbsp; @endunless @endforeach<br>
<a href="mailto:{{ $contact['email'] }}" style="color:#9cc0eb;text-decoration:none;">{{ $contact['email'] }}</a> &nbsp;·&nbsp; <a href="{{ lroute('home') }}" style="color:#9cc0eb;text-decoration:none;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
