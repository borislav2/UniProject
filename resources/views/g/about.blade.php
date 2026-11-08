@extends('layouts.public')

{{-- /za-nas in design G, in the voice of an agency: who we are, method, standards, founders (names and roles), the company line. --}}
@section('title', __('За нас и екипа зад вашия сайт и маркетинг'))
@section('meta_description', __('Creatium Lab прави сайтове и маркетинг за бизнеси в България, от първата страница до клиентите, които я намират в Google.'))
@section('g_lean', 'inner')

@php
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    $intro = array_values(array_filter((array) ($about['g_intro'] ?? []), 'filled'));
    if (! $intro) {
        $intro = array_values(array_filter((array) ($about['paragraphs'] ?? []), 'filled'));
    }
    $method = array_values(array_filter((array) ($about['g_method'] ?? []), fn ($m) => filled($m['title'] ?? null)));
    $standards = array_values(array_filter((array) ($about['g_standards'] ?? []), fn ($m) => filled($m['title'] ?? null)));
    $founders = array_values(array_filter((array) $team, fn ($m) => filled($m['name'] ?? null)));
    $legal = config('creatium.legal');
    $legalLine = collect([
        $legal['company'] ?? null,
        ! empty($legal['eik']) ? __('ЕИК') . ' ' . $legal['eik'] : null,
        $legal['address'] ?? null,
        __(config('creatium.contact.city')) . ', ' . __('България'),
    ])->filter()->implode(', ');
    $n = 0;
@endphp

@section('content')
@include('partials.g.page-head', ['eyebrow' => __('За нас'), 'heading' => $about['g_intro_title'] ?? __('Дигитална агенция за бизнеси'), 'sub' => $intro[0] ?? null])

@if(count($intro) > 1)
<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh"><p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Кои сме ние') }}</p></div>
        <div class="g-prose g-big-prose">
            @foreach(array_slice($intro, 1) as $paragraph)<p>{{ $paragraph }}</p>@endforeach
        </div>
    </div>
</section>
@endif

@if($method)
<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Метод') }}</p>
            <h2 class="g-h">{{ $about['g_method_title'] ?? __('Как работим') }}</h2>
        </div>
        <ol class="g-rows">
            @foreach($method as $step)
                <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['text'] ?? '' }}</p></li>
            @endforeach
        </ol>
    </div>
</section>
@endif

@if($standards)
<section class="g-band">
    <div class="g-wrap">
        <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Стандарти') }}</p>
        <h2 class="g-h">{{ $about['g_standards_title'] ?? __('Нашите стандарти') }}</h2>
        <div class="g-pr g-pr4">
            @foreach($standards as $item)<div><b>{{ $item['title'] }}</b><span>{{ $item['text'] ?? '' }}</span></div>@endforeach
        </div>
    </div>
</section>
@endif

@if($founders)
<section class="g-s">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ $about['g_founders_title'] ?? __('Основатели') }}</p>
        </div>
        <ul class="g-founders">
            @foreach($founders as $member)
                <li>
                    @if(! empty($member['image']))<img src="{{ asset($member['image']) }}" alt="{{ $member['name'] }}" width="72" height="72" loading="lazy" decoding="async">@endif
                    <div><b>{{ $member['name'] }}</b><span class="g-mono g-mute">{{ $member['role'] }}</span></div>
                </li>
            @endforeach
        </ul>
        <p class="g-legal-line g-mono g-mute">{{ $legalLine }}</p>
    </div>
</section>
@endif

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap g-next-band">
        <div>
            <p class="g-mono g-mute">{{ __('Имате въпроси?') }}</p>
            <h2 class="g-h">{{ __('Запазете час за разговор') }}</h2>
        </div>
        <div class="g-cta">
            <a class="g-btn g-big" href="{{ lroute('contact') }}#konsultatsiya">{{ __('Запазете час') }}<i></i></a>
            <a class="g-btn g-ghost g-big" href="{{ lroute('faq') }}">{{ __('Към въпросите') }}</a>
        </div>
    </div>
</section>
@endsection
