@extends('layouts.public')

{{-- /uslugi in design G: numbered editorial rows for the components, the funnel and the calculator in the g palette, then the next step. --}}
@section('title', __('Изработка на сайт, SEO, Google профил и имейл маркетинг'))
@section('meta_description', __('Сайт, който продава, SEO и видимост в ChatGPT, Google профил и отзиви, имейл кампании и поддръжка. Всичко за онлайн клиенти, от един екип.'))
@section('g_lean', 'inner')

@php
    // Fifth round (13.11.2026): the six services in plain words, each from service_pages (what it is, what we do, what you get, the package).
    $servicePages = (array) site('service_pages');
    $services = collect(['website', 'seo', 'google', 'email', 'support', 'shopping'])->filter(fn ($key) => isset($servicePages[$key]))->mapWithKeys(fn ($key) => [$key => $servicePages[$key]]);
    $gPlans = collect(\App\Support\GPlans::cards(app(\App\Support\Content::class)->withDefaults('plans')))->keyBy('index');
@endphp

@section('content')
@include('partials.g.page-head', ['heading' => $page['title'], 'sub' => $page['intro']])

<section class="g-s">
    <div class="g-wrap">
        <div class="g-svc-list">
            @foreach($services as $key => $service)
                @php($gPlan = $gPlans->get($service['package'] ?? -1))
                <article id="{{ $service['slug'] }}" class="g-svc" data-g-service="{{ $key }}">
                    <div class="g-svc-no"><x-icon :name="$service['icon']" class="g-ico" /></div>
                    <div class="g-svc-main">
                        <h2 class="g-h">{{ $service['menu'] }}</h2>
                        <p class="g-svc-tag">{{ filled($service['g_what'] ?? null) ? $service['g_what'] : $service['lead'] }}</p>
                        <div class="g-get">
                            <p class="g-mono g-mute">{{ __('Какво получавате') }}</p>
                            <p>{{ $service['result'] }}</p>
                        </div>
                        <p class="g-svc-pkg">
                            @if($gPlan)<span class="g-mono g-mute">{{ __('Включено в пакет') }}</span> <b>{{ $gPlan['name'] }}</b>@if(filled($gPlan['monthly'])), {{ trim($gPlan['from'] . ' ' . $gPlan['monthly']) }}{{ __('/месец') }}@endif
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

<section class="g-band g-engine">
    <div class="g-wrap">
        <p class="g-mono">{{ $trust['engine_eyebrow'] }}</p>
        <h2 class="g-h">{{ $trust['principles_title'] }}</h2>
        <p class="g-band-text">{{ $trust['principles_text'] }}</p>
        @include('partials.g.funnel')
    </div>
</section>

@include('partials.g.calculator')

<section class="g-closer">
    <div class="g-wrap">
        <div class="g-closer-in">
            <p class="g-closer-t g-h">{{ $page['cta_title'] }}</p>
            <div class="g-closer-a"><a class="g-btn" href="{{ lroute('contact') }}#konsultatsiya">{{ $page['cta_button'] }}<i></i></a></div>
        </div>
        <div class="g-closer-sub">
            <p><b>{{ __('Проверете сайта си за минута') }}</b> {{ __('Въведете адреса и имейл и ще получите отчет за HTTPS, скоростта на сървъра, заглавията за Google, телефона и още.') }}</p>
            <a class="g-link" href="{{ lroute('site-check') }}">{{ __('Безплатна проверка на сайт') }}</a>
        </div>
    </div>
</section>
@endsection
