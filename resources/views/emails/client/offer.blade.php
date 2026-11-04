{{-- Оферта (Стартов двигател и Активен маркетинг и растеж). Всеки текст идва от полетата на формата (App\Support\ClientEmails::offerFields) и минава през fill(),
     така че {business} и {client_name} работят навсякъде. Празно по желание поле скрива целия си блок. Само таблици и inline стилове. --}}
@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $ce = \App\Support\ClientEmails::class;
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $d = $data;

    $t = fn ($key) => trim($ce::fill((string) ($d[$key] ?? ''), $d));
    $ls = fn ($key) => $ce::lines($t($key));
    $pr = fn ($key) => $ce::pairs($t($key));
    $paras = fn ($key) => array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/u', $t($key))), fn ($p) => $p !== ''));
    $h = fn ($text, $color = '#0f1a2b') => '<div style="' . $f . 'font-size:21px;line-height:1.3;font-weight:bold;color:' . $color . ';">' . e($text) . '</div>';
    $bar = '<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td width="36" height="3" bgcolor="#2d63a3" style="width:36px;height:3px;line-height:3px;font-size:0;background:#2d63a3;border-radius:2px;">&nbsp;</td></tr></table><div style="height:10px;line-height:10px;font-size:0;">&nbsp;</div>';

    // Фунията: четири стъпки, всяка по-тясна и по-тъмна от предишната.
    $stageStyles = [
        ['width' => '100%', 'bg' => '#9cc0eb', 'fg' => '#0f1a2b', 'badge' => '#0f1a2b', 'badgeFg' => '#ffffff'],
        ['width' => '92%', 'bg' => '#6d9bd1', 'fg' => '#0f1a2b', 'badge' => '#ffffff', 'badgeFg' => '#0f1a2b'],
        ['width' => '84%', 'bg' => '#2d63a3', 'fg' => '#ffffff', 'badge' => '#ffffff', 'badgeFg' => '#2d63a3'],
        ['width' => '76%', 'bg' => '#0f1a2b', 'fg' => '#ffffff', 'badge' => '#9cc0eb', 'badgeFg' => '#0f1a2b'],
    ];
    $stages = [];
    foreach ([1, 2, 3, 4] as $n) {
        $items = $pr("stage{$n}_items");
        if ($items) {
            $stages[] = ['title' => $t("stage{$n}_title"), 'items' => $items];
        }
    }

    $business = $t('business');
    $cta = $t('cta_text');
    $validUntil = $t('valid_until') !== '' && strtotime($t('valid_until')) !== false ? \Illuminate\Support\Carbon::parse($t('valid_until'))->format('d.m.Y') : null;
    $outcomes = $ls('outcomes');
    $trust = $pr('trust');
    $steps = $pr('steps');
    $extras = $pr('extras');
    $ps = $t('ps');
@endphp

@section('eyebrow', $t('eyebrow'))
@section('headline', $t('headline'))
@section('sub', $t('sub'))

@section('body')
{{-- 2. Поздрав и увод --}}
<tr><td style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте, {{ $t('client_name') }},</p>
@foreach($paras('intro') as $para)
<p style="margin:0 0 14px;">{!! nl2br(e($para)) !!}</p>
@endforeach
</td></tr>

{{-- 3. Какво чухме --}}
@if($t('heard') !== '')
<tr><td style="padding:10px 36px 0;">
@include('emails.client._parts', ['part' => 'quote', 'label' => $t('heading_heard'), 'text' => $t('heard')])
</td></tr>
@endif

{{-- 4. Защо сега --}}
@if($t('why_now') !== '')
<tr><td style="padding:20px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:12px;">
<tr><td style="padding:22px 24px;">
@if($t('heading_why_now') !== '')@include('emails.client._parts', ['part' => 'label', 'text' => $t('heading_why_now'), 'color' => '#9cc0eb'])@endif
<div style="{{ $f }}font-size:16px;line-height:1.6;font-weight:bold;color:#ffffff;padding-top:8px;">{!! nl2br(e($t('why_now'))) !!}</div>
</td></tr>
</table>
</td></tr>
@endif

{{-- 5. Пътят на клиента: фуния --}}
@if($stages)
<tr><td style="height:28px;line-height:28px;font-size:0;">&nbsp;</td></tr>
<tr><td bgcolor="#f5f8fc" style="background:#f5f8fc;padding:30px 36px 26px;">
{!! $bar !!}
@if($t('heading_path') !== ''){!! $h($t('heading_path')) !!}@endif
@if($t('path_intro') !== '')<div style="{{ $f }}font-size:14px;line-height:1.6;color:#475569;padding-top:6px;">{{ $t('path_intro') }}</div>@endif
<div style="height:16px;line-height:16px;font-size:0;">&nbsp;</div>
@foreach($stages as $i => $stage)
    @php($st = $stageStyles[$i])
    @if($i > 0)<div style="text-align:center;{{ $f }}font-size:12px;line-height:16px;color:#9cc0eb;">&#9660;</div>@endif
    <table role="presentation" width="{{ $st['width'] }}" align="center" cellpadding="0" cellspacing="0" border="0" style="width:{{ $st['width'] }};margin:0 auto;border:1px solid #cfdcec;border-radius:12px;overflow:hidden;background:#ffffff;">
    <tr><td bgcolor="{{ $st['bg'] }}" style="background:{{ $st['bg'] }};padding:12px 18px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
        <td width="34" valign="middle"><span style="display:inline-block;width:26px;height:26px;line-height:26px;border-radius:13px;background:{{ $st['badge'] }};color:{{ $st['badgeFg'] }};{{ $f }}font-size:13px;font-weight:bold;text-align:center;">{{ $i + 1 }}</span></td>
        <td valign="middle" style="{{ $f }}font-size:16px;line-height:1.3;font-weight:bold;color:{{ $st['fg'] }};">{{ $stage['title'] }}</td>
        </tr></table>
    </td></tr>
    <tr><td style="padding:10px 18px 12px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        @foreach($stage['items'] as [$title, $text])
        <tr><td width="24" valign="top" style="padding:5px 0;{{ $f }}font-size:14px;font-weight:bold;color:#2d63a3;">&#10003;</td><td style="padding:5px 0;{{ $f }}font-size:14px;line-height:1.5;color:#1f2a3a;">@if($text !== '')<strong style="color:#0f1a2b;">{{ $title }}:</strong> {{ $text }}@else{{ $title }}@endif</td></tr>
        @endforeach
        </table>
    </td></tr>
    </table>
@endforeach
</td></tr>
@endif

{{-- 6. Какво получавате --}}
@if($outcomes)
<tr><td style="padding:30px 36px 0;">
{!! $bar !!}
@if($t('heading_outcomes') !== ''){!! $h($t('heading_outcomes')) !!}@endif
<div style="height:12px;line-height:12px;font-size:0;">&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach(array_chunk($outcomes, 2) as $row)
<tr>
@foreach([0, 1] as $col)
<td width="50%" valign="top" style="padding:0 {{ $col === 0 ? '5px 10px 0' : '0 10px 5px' }};">
@if(isset($row[$col]))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f5f8fc" style="background:#f5f8fc;border-radius:10px;"><tr>
<td width="40" valign="top" style="padding:12px 0 12px 12px;"><span style="display:inline-block;width:24px;height:24px;line-height:24px;border-radius:12px;background:#2d63a3;color:#ffffff;{{ $f }}font-size:13px;font-weight:bold;text-align:center;">&#10003;</span></td>
<td valign="top" style="padding:12px 12px 12px 4px;{{ $f }}font-size:13px;line-height:1.5;color:#1f2a3a;">{{ $row[$col] }}</td>
</tr></table>
@endif
</td>
@endforeach
</tr>
@endforeach
</table>
</td></tr>
@endif

{{-- 7. Цена --}}
<tr><td style="padding:30px 36px 0;">
{!! $bar !!}
@if($t('heading_price') !== ''){!! $h($t('heading_price')) !!}@endif
<div style="height:14px;line-height:14px;font-size:0;">&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:14px;">
<tr>
<td width="50%" valign="top" align="center" style="padding:24px 12px 18px;border-right:1px solid #2a4468;">
@if($t('price_label') !== '')<div style="{{ $f }}font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#9cc0eb;">{{ $t('price_label') }}</div>@endif
<div style="{{ $f }}font-size:34px;line-height:1.2;font-weight:bold;color:#ffffff;padding-top:6px;">€{{ $t('price') }}</div>
<div style="{{ $f }}font-size:13px;color:#c5d6ec;">еднократно</div>
</td>
<td width="50%" valign="top" align="center" style="padding:24px 12px 18px;">
@if($t('monthly_label') !== '')<div style="{{ $f }}font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#9cc0eb;">{{ $t('monthly_label') }}</div>@endif
<div style="{{ $f }}font-size:34px;line-height:1.2;font-weight:bold;color:#ffffff;padding-top:6px;">€{{ $t('monthly_price') }}</div>
<div style="{{ $f }}font-size:13px;color:#c5d6ec;">/ месец</div>
</td>
</tr>
<tr><td colspan="2" style="padding:0 24px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach([['Срок', $t('deadline')], ['Плащане', $t('payment')], ['Абонамент', $t('subscription_terms')], ['Гаранция', $t('guarantee')], ['Реклама', $t('ad_budget')]] as [$label, $value])
@if($value !== '')
<tr><td valign="top" width="80" style="padding:10px 12px 10px 0;border-top:1px solid #2a4468;{{ $f }}font-size:13px;color:#c5d6ec;">{{ $label }}</td><td valign="top" style="padding:10px 0;border-top:1px solid #2a4468;{{ $f }}font-size:14px;line-height:1.5;font-weight:bold;color:#ffffff;">{{ $value }}</td></tr>
@endif
@endforeach
</table>
@if($validUntil)
<div style="padding-top:6px;{{ $f }}font-size:14px;line-height:1.6;"><span style="background:#fde68a;color:#0f1a2b;font-weight:bold;padding:4px 9px;border-radius:6px;">Предложението е валидно до {{ $validUntil }} г.</span></div>
@endif
</td></tr>
</table>
</td></tr>

{{-- 8. Допълнително --}}
@if($extras)
<tr><td style="padding:26px 36px 0;">
@if($t('heading_extras') !== '')@include('emails.client._parts', ['part' => 'label', 'text' => $t('heading_extras')])@endif
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
@foreach($extras as [$name, $price])
<tr><td valign="top" style="padding:9px 12px 9px 0;border-bottom:1px solid #e2e8f0;{{ $f }}font-size:14px;line-height:1.5;color:#1f2a3a;">{{ $name }}</td>@if($price !== '')<td valign="top" align="right" style="padding:9px 0;border-bottom:1px solid #e2e8f0;{{ $f }}font-size:14px;line-height:1.5;font-weight:bold;color:#0f1a2b;white-space:nowrap;">{{ $price }}</td>@endif</tr>
@endforeach
</table>
</td></tr>
@endif

{{-- 9. Доверие --}}
@if($trust)
<tr><td style="height:30px;line-height:30px;font-size:0;">&nbsp;</td></tr>
<tr><td bgcolor="#f5f8fc" style="background:#f5f8fc;padding:30px 36px 24px;">
{!! $bar !!}
@if($t('heading_trust') !== ''){!! $h($t('heading_trust')) !!}@endif
<div style="height:12px;line-height:12px;font-size:0;">&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach(array_chunk($trust, 2) as $row)
<tr>
@foreach([0, 1] as $col)
<td width="50%" valign="top" style="padding:0 {{ $col === 0 ? '5px 10px 0' : '0 10px 5px' }};">
@if(isset($row[$col]))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="background:#ffffff;border-radius:10px;border:1px solid #dbe6f3;"><tr><td style="padding:14px 14px 14px;">
<span style="display:inline-block;width:24px;height:24px;line-height:24px;border-radius:7px;background:#0f1a2b;color:#9cc0eb;{{ $f }}font-size:13px;font-weight:bold;text-align:center;">&#10003;</span>
<div style="{{ $f }}font-size:14px;line-height:1.35;font-weight:bold;color:#0f1a2b;padding-top:8px;">{{ $row[$col][0] }}</div>
@if($row[$col][1] !== '')<div style="{{ $f }}font-size:13px;line-height:1.5;color:#475569;padding-top:4px;">{{ $row[$col][1] }}</div>@endif
</td></tr></table>
@endif
</td>
@endforeach
</tr>
@endforeach
</table>
</td></tr>
@endif

{{-- 10. Как започваме: времева линия --}}
@if($steps)
<tr><td style="padding:30px 36px 0;">
{!! $bar !!}
@if($t('heading_steps') !== ''){!! $h($t('heading_steps')) !!}@endif
<div style="height:12px;line-height:12px;font-size:0;">&nbsp;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($steps as [$title, $text])
<tr>
<td width="44" valign="top" align="center" style="@unless($loop->last)background-image:linear-gradient(#c9d9ee,#c9d9ee);background-repeat:no-repeat;background-position:14px 0;background-size:2px 100%;@endunless"><span style="display:inline-block;width:28px;height:28px;line-height:28px;border-radius:14px;background:#0f1a2b;color:#ffffff;{{ $f }}font-size:13px;font-weight:bold;text-align:center;">{{ $loop->iteration }}</span></td>
<td valign="top" style="padding:3px 0 {{ $loop->last ? '0' : '18px' }} 6px;{{ $f }}font-size:14px;line-height:1.55;color:#475569;">@if($text !== '')<strong style="display:block;color:#0f1a2b;font-size:15px;">{{ $title }}</strong>{{ $text }}@else<strong style="color:#0f1a2b;font-size:15px;">{{ $title }}</strong>@endif</td>
</tr>
@endforeach
</table>
</td></tr>
@endif

{{-- 11. Бутон --}}
@if($cta !== '')
<tr><td style="padding:30px 36px 0;" align="center">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode($cta . ': ' . $business) }}" style="display:inline-block;padding:16px 34px;{{ $f }}font-size:16px;font-weight:bold;color:#ffffff;text-decoration:none;">{{ $cta }}</a></td>
</tr></table>
@if($phone)<div style="{{ $f }}font-size:13px;line-height:1.6;color:#64748b;padding-top:12px;">или се обадете: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" style="color:#2d63a3;font-weight:bold;text-decoration:none;">{{ $phone }}</a></div>@endif
</td></tr>
@endif

{{-- 12. Край --}}
@if($t('closing') !== '')
<tr><td style="padding:24px 36px 6px;{{ $f }}font-size:14px;line-height:1.65;color:#475569;">{!! nl2br(e($t('closing'))) !!}</td></tr>
@endif
@endsection

@if($ps !== '')
@section('ps', $ps)
@endif
