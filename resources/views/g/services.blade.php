@extends('layouts.public')

{{-- /uslugi in design G: numbered editorial rows for the components, the funnel and the calculator in the g palette, then the next step. --}}
@section('title', __('Изработка на сайт, SEO, Google профил и имейл маркетинг'))
@section('meta_description', __('Сайт, който продава, SEO и видимост в ChatGPT, Google профил и отзиви, имейл кампании и поддръжка. Всичко за онлайн клиенти, от един екип.'))
@section('g_lean', 'inner')

@php
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    // Fifth round (13.11.2026): the six services in plain words, each from service_pages (what it is, what we do, what you get, the package).
    $servicePages = (array) site('service_pages');
    $services = collect(['website', 'seo', 'google', 'email', 'support', 'shopping'])->filter(fn ($key) => isset($servicePages[$key]))->mapWithKeys(fn ($key) => [$key => $servicePages[$key]]);
    $gPlans = collect(\App\Support\GPlans::cards(app(\App\Support\Content::class)->withDefaults('plans')))->keyBy('index');
@endphp

@section('content')
@include('partials.g.page-head', ['eyebrow' => __('Услуги'), 'heading' => $page['title'], 'sub' => $page['intro']])

<section class="g-s">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>01</b>{{ __('Шестте ни услуги') }}</p>
        </div>
        <div class="g-svc-list">
            @foreach($services as $key => $service)
                @php($gPlan = $gPlans->get($service['package'] ?? -1))
                <article id="{{ $service['slug'] }}" class="g-svc" data-g-service="{{ $key }}">
                    <div class="g-svc-no"><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><x-icon :name="$service['icon']" class="g-ico" /></div>
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
        <p class="g-mono"><b>02</b>{{ $trust['engine_eyebrow'] }}</p>
        <h2 class="g-h">{{ $trust['principles_title'] }}</h2>
        <p class="g-band-text">{{ $trust['principles_text'] }}</p>
        @include('partials.g.funnel')
    </div>
</section>

@include('partials.g.calculator', ['number' => '03'])

<section class="g-s">
    <div class="g-wrap g-next-band">
        <div>
            <p class="g-mono"><b>04</b>{{ __('Следващата стъпка') }}</p>
            <h2 class="g-h">{{ $page['cta_title'] }}</h2>
        </div>
        <a class="g-btn g-big" href="{{ lroute('contact') }}#konsultatsiya">{{ $page['cta_button'] }}<i></i></a>
    </div>
</section>

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap g-checkbar">
        <div>
            <p class="g-mono g-mute">{{ __('Безплатно') }}</p>
            <h2 class="g-h">{{ __('Проверете сайта си за минута') }}</h2>
            <p>{{ __('Въведете адреса и имейл и ще получите отчет за HTTPS, скоростта на сървъра, заглавията за Google, телефона и още.') }}</p>
        </div>
        <a class="g-btn g-ghost" href="{{ lroute('site-check') }}">{{ __('Проверете сайта') }}<i></i></a>
    </div>
</section>
@endsection
