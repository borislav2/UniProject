@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $time = \App\Support\Booking::label($lead->consultation_at);
    $calendar = \App\Support\Booking::googleCalendarUrl($lead->consultation_at, __('Консултация с Creatium Lab'), __('Ще ви се обадим на :phone.', ['phone' => $lead->client_phone]));
@endphp

@section('eyebrow', __('Консултация'))
@section('headline'){{ ucfirst($time) }}@endsection
@section('sub'){{ __('Записахме ви. Ще ви се обадим в този час на :phone.', ['phone' => $lead->client_phone]) }}@endsection

@section('body')
<tr><td style="padding:30px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">{{ __('Благодарим за запитването!') }}</p>
<p style="margin:0;">{{ __('Разговорът е около :minutes минути. Ще поговорим за бизнеса ви, целите и какво е най-разумно да направим първо. Ако часът вече не ви е удобен, просто отговорете на този имейл.', ['minutes' => config('creatium.booking.minutes')]) }}</p>
</td></tr>
<tr><td style="padding:24px 36px 30px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="{{ $calendar }}" style="display:inline-block;padding:13px 22px;{{ $f }}font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">{{ __('Добавете в Google Календар') }}</a></td>
</tr></table>
</td></tr>
@endsection
