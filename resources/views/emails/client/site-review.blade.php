@extends('emails.client.layout')
{{-- Личният преглед към безплатната проверка на сайт (App\Support\ClientEmails 'site-review'): резюме, приоритетни стъпки, следваща стъпка.
     Полетата идват предварително попълнени от автоматичния отчет и се редактират. „Заглавие: текст“ в точките прави заглавието удебелено.
     Подписът и данните за връзка са в общия layout. --}}
@php
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $fill = fn ($text) => \App\Support\ClientEmails::fill((string) $text, $data);
    $items = array_map(fn ($pair) => [$fill($pair[0]), $fill($pair[1])], \App\Support\ClientEmails::pairs($data['items'] ?? ''));
@endphp

@section('eyebrow', 'Личен преглед')
@section('headline'){{ $data['business'] }}@endsection
@section('sub', 'Специалист от нашия екип прегледа сайта ви лично.')

@section('body')
<tr><td class="g-pad" style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#0C0E12;">
<p style="margin:0;">Здравейте{{ filled($data['client_name'] ?? '') ? ', ' . $data['client_name'] : '' }},</p>
@if(! empty($data['summary']))<p style="margin:14px 0 0;white-space:pre-line;">{{ $fill($data['summary']) }}</p>@endif
</td></tr>

@if($items)
<tr><td class="g-pad" style="padding:26px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво да оправите първо'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => $items])
</td></tr>
@endif

@if(! empty($data['next']))
<tr><td class="g-pad" style="padding:26px 36px 0;">@include('emails.client._parts', ['part' => 'quote', 'label' => 'Следваща стъпка', 'text' => $fill($data['next'])])</td></tr>
@endif
@endsection
