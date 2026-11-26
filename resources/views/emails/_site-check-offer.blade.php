{{-- Блокът с офертата след проверката в имейлите (отчета и второто писмо). $offer = fix|monitor, $url = подписаният линк, $pad = страничните полета (28). --}}
@php
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $o = \App\Support\SiteCheck\Offers::text($offer);
    $fixOffer = $offer === 'fix';
@endphp
<tr><td class="g-pad" style="padding:30px {{ $pad }}px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFFFFF;border:1px solid #D9DBDE;border-left:3px solid #E8431B;">
<tr><td style="padding:22px 22px 20px;{{ $f }}">
@include('emails.client._parts', ['part' => 'label', 'text' => __('Предложение')])
<div style="font-size:19px;line-height:1.35;font-weight:bold;color:#0C0E12;padding-top:8px;">{{ $o['name'] ?? '' }}</div>
<div style="font-size:15px;line-height:1.6;color:#0C0E12;padding-top:8px;"><strong>{{ \App\Support\SiteCheck\Offers::priceLine($offer) }}.</strong>@if($fixOffer) {{ \App\Support\SiteCheck\Offers::fill((string) ($o['days_line'] ?? ''), $offer) }}@endif</div>
@if($fixOffer)
<div style="font-size:14px;line-height:1.6;color:#4A5058;padding-top:8px;">{{ \App\Support\SiteCheck\Offers::fill((string) ($o['credit'] ?? ''), $offer) }}</div>
@else
<div style="padding-top:6px;">@include('emails.client._parts', ['part' => 'checklist', 'items' => (array) ($o['items'] ?? []), 'color' => '#C4300F'])</div>
@endif
<div style="height:14px;line-height:14px;font-size:0;">&nbsp;</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0C0E12" style="background:#0C0E12;"><a href="{{ $url }}" style="display:inline-block;padding:13px 24px;{{ $f }}font-size:15px;font-weight:bold;color:#FFFFFF;text-decoration:none;">{{ $o['button'] ?? '' }}</a></td>
</tr></table>
<div style="font-size:12px;line-height:1.6;color:#6B7178;padding-top:10px;">{{ $o['note'] ?? '' }}</div>
</td></tr>
</table>
</td></tr>
