@extends('layouts.public')

{{-- /uslugi in design G: numbered editorial rows for the seven services (software development included), then the interactive panel and the next step. The funnel is gone (third round, 16.11.2026). --}}
@section('title', __('Изработка на сайт, SEO, Google профил и имейл маркетинг'))
@section('meta_description', __('Сайт, който продава, SEO и видимост в ChatGPT, Google профил и отзиви, имейл кампании и поддръжка. Всичко за онлайн клиенти, от един екип.'))
@section('g_lean', 'inner')

@php
    // The services in plain words, each from service_pages (what it is, what we do, what you get, the package). Every service of the list, in its order.
    $services = collect((array) site('service_pages'));
    $gPage = app(\App\Support\Content::class)->withDefaults('services_page');
    $gPlans = collect(\App\Support\GPlans::cards(app(\App\Support\Content::class)->withDefaults('plans')))->keyBy('index');
@endphp

@section('content')
@include('partials.g.page-head', ['heading' => filled($gPage['g_title'] ?? null) ? $gPage['g_title'] : $page['title'], 'sub' => filled($gPage['g_text'] ?? null) ? $gPage['g_text'] : $page['intro']])

<section class="g-s">
    <div class="g-wrap">
        <div class="g-svc-list">
            @foreach($services as $key => $service)
                @php($gPlan = $gPlans->get($service['package'] ?? -1))
                <article id="{{ $service['slug'] }}" class="g-svc" data-g-service="{{ $key }}">
                    <div class="g-svc-main">
                        <h2 class="g-h">{{ $service['menu'] }}</h2>
                        <p class="g-svc-tag">{{ filled($service['g_what'] ?? null) ? $service['g_what'] : $service['lead'] }}</p>
                        <div class="g-get">
                            <p class="g-mono g-mute">{{ __('Какво получавате') }}</p>
                            <p>{{ $service['result'] }}</p>
                        </div>
                        <p class="g-svc-pkg">
                            @if($gPlan)<span class="g-mono g-mute">{{ __('Включено в пакет') }}</span> <b>{{ \App\Support\GPlans::label($gPlan) }}</b>@if(filled($gPlan['monthly'])), {{ trim($gPlan['from'] . ' ' . $gPlan['monthly']) }}{{ __('/месец') }}@endif
                            @else<span class="g-mono g-mute">{{ __('Цена') }}</span> <b>{{ __('Цена по оферта') }}</b>@endif
                        </p>
                        <a class="g-link" href="{{ \App\Http\Controllers\PageController::serviceUrl($key) }}">{{ __('Научете повече') }}</a>
                    </div>
                    <div class="g-svc-inc">
                        <h3 class="g-mono g-mute">{{ __('Какво правим') }}</h3>
                        <ul class="g-list">
                            @foreach($service['includes'] as $item)<li><span>{{ $item }}</span></li>@endforeach
                        </ul>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

@include('partials.g.panel')

<section class="g-closer">
    <div class="g-wrap">
        <div class="g-closer-in">
            <p class="g-closer-t g-h">{{ $page['cta_title'] }}</p>
            <div class="g-closer-a"><a class="g-btn" href="{{ lroute('contact') }}#kontakt">{{ $page['cta_button'] }}<i></i></a></div>
        </div>
        <div class="g-closer-sub">
            <p><b>{{ __('Проверете сайта си за минута') }}</b> {{ __('Въведете адреса и имейл и ще получите отчет за HTTPS, скоростта на сървъра, заглавията за Google, телефона и още.') }}</p>
            <a class="g-link" href="{{ lroute('site-check') }}">{{ __('Безплатна проверка на сайт') }}</a>
        </div>
    </div>
</section>
@endsection
