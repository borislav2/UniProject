@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $lines = fn ($text) => array_values(array_filter(array_map(fn ($line) => trim(ltrim(trim($line), '–-•')), preg_split('/\R/u', (string) $text))));
    $done = $lines($data['done'] ?? '');
    $results = $lines($data['results'] ?? '');
    $next = $lines($data['next'] ?? '');
@endphp

@section('eyebrow', 'Месечен отчет')
@section('headline'){{ $data['business'] }}: {{ $data['month'] }}@endsection
@section('sub', 'Какво направихме за вашия сайт и маркетинг този месец.')

@section('body')
<tr><td style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0;">Здравейте, {{ $data['client_name'] }},</p>
<p style="margin:14px 0 0;">Ето кратък отчет за {{ $data['month'] }}.</p>
</td></tr>

<tr><td style="padding:24px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво направихме'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => $done])
</td></tr>

@if($results)
<tr><td style="padding:24px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;">
<tr><td style="padding:20px 24px 6px;">@include('emails.client._parts', ['part' => 'label', 'text' => 'Резултати'])</td></tr>
<tr><td style="padding:0 24px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($results as $result)
<tr><td style="padding:6px 0;{{ $f }}font-size:15px;line-height:1.55;color:#0f1a2b;font-weight:bold;">{{ $result }}</td></tr>
@endforeach
</table>
</td></tr>
</table>
</td></tr>
@endif

@if(! empty($data['site_status']))
<tr><td style="padding:24px 36px 0;">@include('emails.client._parts', ['part' => 'quote', 'label' => 'Състояние на сайта', 'text' => $data['site_status']])</td></tr>
@endif

@if($next)
<tr><td style="padding:24px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'План за следващия месец'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => array_map(fn ($item) => [$item, ''], $next)])
</td></tr>
@endif

<tr><td style="padding:24px 36px 28px;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0;">Ако имате въпроси или идея, която искате да добавим, просто отговорете на този имейл.</p>
</td></tr>
@endsection
