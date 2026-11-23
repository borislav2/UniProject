{{-- Обща рамка за имейлите до клиенти (шаблоните в App\Support\ClientEmails), в стила на дизайн Г: светъл фон, квадратни ъгли, тънки линии, един акцент.
     Винаги в новия вид, не зависи от creatium.design. Само inline стилове и таблици: така изглеждат еднакво в Gmail, Outlook и на телефон. Тъмният режим е в emails/_font.
     Логото е вградено в писмото (cid), за да се вижда и когато пощата блокира външни картинки; в прегледа в админа идва от сайта. Две лога: тъмно за светъл фон и светло за тъмен режим. --}}
@php
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $fh = 'font-family:Geologica,Segoe UI,Arial,sans-serif;';
    $fm = 'font-family:JetBrains Mono,Consolas,Menlo,monospace;';
    $contact = config('creatium.contact');
    $phones = $contact['phones'] ?? [];
    $tel = fn ($p) => preg_replace('/[^0-9+]/', '', $p);
    $pad = $data['pad'] ?? 36;
    $embedded = isset($message) && $message instanceof \Illuminate\Mail\Message;
    $logoLight = $embedded ? $message->embed(public_path('images/logo-g.png')) : asset('images/logo-g.png');
    $logoDark = $embedded ? $message->embed(public_path('images/logo-g-dark.png')) : asset('images/logo-g-dark.png');
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $subject ?? 'Creatium Lab' }}</title>
@include('emails._font')
</head>
<body style="margin:0;padding:0;background:#ECEEF0;{{ $f }}">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ECEEF0" style="background:#ECEEF0;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#F7F8F9" style="width:100%;max-width:600px;background:#F7F8F9;border:1px solid #D9DBDE;">

<tr><td class="g-pad" style="padding:28px {{ $pad }}px 26px;border-bottom:1px solid #D9DBDE;">
<img class="g-logo-l" src="{{ $logoLight }}" width="150" height="22" alt="Creatium Lab" style="display:block;border:0;{{ $fh }}color:#0C0E12;font-size:18px;width:150px;height:auto;">
<img class="g-logo-d" src="{{ $logoDark }}" width="150" height="22" alt="" style="display:none;border:0;width:150px;height:auto;max-height:0;overflow:hidden;mso-hide:all;">
</td></tr>

<tr><td class="g-pad" style="padding:30px {{ $pad }}px 4px;">
<div style="{{ $fm }}font-size:11px;line-height:1.4;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#C4300F;"><span style="display:inline-block;width:8px;height:8px;line-height:8px;font-size:0;background:#E8431B;">&nbsp;</span>&nbsp; @yield('eyebrow')</div>
<div class="g-h1" style="{{ $fh }}font-size:28px;line-height:1.2;font-weight:300;color:#0C0E12;padding-top:12px;">@yield('headline')</div>
<div style="{{ $f }}font-size:15px;line-height:1.6;color:#4A5058;padding-top:10px;">@yield('sub')</div>
</td></tr>

@yield('body')

<tr><td class="g-pad" style="padding:26px {{ $pad }}px 0;"><div style="border-top:1px solid #D9DBDE;line-height:1px;font-size:0;">&nbsp;</div></td></tr>
<tr><td class="g-pad" style="padding:22px {{ $pad }}px 30px;{{ $f }}font-size:14px;line-height:1.6;color:#0C0E12;">
@php($signature = $data['sender'] ?? (trim($__env->yieldContent('sender')) ?: __('Екипът на Creatium Lab')))
{{ __('С уважение,') }}<br><strong style="color:#0C0E12;">{{ $signature }}</strong>@unless(str_contains($signature, 'Creatium Lab'))<br><span style="color:#5A6068;">Creatium Lab</span>@endunless
@hasSection('after_signature')<br>@yield('after_signature')
@endif
</td></tr>
@hasSection('ps')
<tr><td class="g-pad" style="padding:0 {{ $pad }}px 30px;{{ $f }}font-size:14px;line-height:1.6;color:#4A5058;"><strong style="color:#0C0E12;">P.S.</strong> @yield('ps')</td></tr>
@endif

<tr><td class="g-pad" bgcolor="#0C0E12" style="background:#0C0E12;padding:24px {{ $pad }}px;{{ $f }}font-size:13px;line-height:1.7;color:#9AA0A9;">
<span style="{{ $fm }}font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#FFFFFF;">Creatium Lab</span> &nbsp;·&nbsp; {{ __('Дигитална агенция за бизнеси') }}<br>
@foreach($phones as $phone)<a href="tel:{{ $tel($phone) }}" style="color:#ECEEF0;text-decoration:none;">{{ $phone }}</a>@unless($loop->last) &nbsp;·&nbsp; @endunless @endforeach<br>
<a href="mailto:{{ $contact['email'] }}" style="color:#ECEEF0;text-decoration:none;">{{ $contact['email'] }}</a> &nbsp;·&nbsp; <a href="{{ lroute('home') }}" style="color:#ECEEF0;text-decoration:none;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
