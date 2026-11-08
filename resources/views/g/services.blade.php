@extends('layouts.public')

{{-- /uslugi in design G: numbered editorial rows for the components, the funnel and the calculator in the g palette, then the next step. --}}
@section('title', __('Изработка на сайт, SEO, Google профил и имейл маркетинг'))
@section('meta_description', __('Сайт, който продава, SEO и видимост в ChatGPT, Google профил и отзиви, имейл кампании и поддръжка. Всичко за онлайн клиенти, от един екип.'))
@section('g_lean', 'inner')

@php
    $cardPages = ['websites' => 'website', 'geo-seo' => 'seo', 'email' => 'email', 'monitoring' => 'support'];
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
@endphp

@section('content')
@include('partials.g.page-head', ['eyebrow' => __('Услуги'), 'heading' => $page['title'], 'sub' => $page['intro']])

<section class="g-s">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>01</b>{{ __('Компонентите') }}</p>
        </div>
        <div class="g-svc-list">
            @foreach($page['cards'] as $card)
                <article id="{{ $card['slug'] }}" class="g-svc">
                    <div class="g-svc-no"><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><x-icon :name="$card['icon']" class="g-ico" /></div>
                    <div class="g-svc-main">
                        <p class="g-mono g-mute">{{ $card['label'] }}</p>
                        <h2 class="g-h">{{ $card['title'] }}</h2>
                        <p class="g-svc-tag">{{ $card['tag'] }}</p>
                        <p class="g-svc-desc">{{ $card['description'] }}</p>
                    </div>
                    <div class="g-svc-inc">
                        <h3 class="g-mono g-mute">{{ __('Какво получавате') }}</h3>
                        <ul class="g-list">
                            @foreach($card['includes'] as $item)<li><span>{{ $item }}</span></li>@endforeach
                        </ul>
                        @if($pageKey = $cardPages[$card['slug']] ?? null)
                            <a class="g-link" href="{{ \App\Http\Controllers\PageController::serviceUrl($pageKey) }}">{{ __('Научете повече') }}</a>
                        @endif
                    </div>
                </article>
            @endforeach

            @foreach(['google', 'shopping'] as $featuredKey)
                @if($featured = site('service_pages')[$featuredKey] ?? null)
                    <article class="g-svc g-svc-new">
                        <div class="g-svc-no"><span class="g-chip g-mono">{{ __('Ново') }}</span><x-icon :name="$featured['icon']" class="g-ico" /></div>
                        <div class="g-svc-main">
                            <h2 class="g-h">{{ $featured['title'] }}</h2>
                            <p class="g-svc-desc">{{ $featured['lead'] }}</p>
                        </div>
                        <div class="g-svc-inc"><a class="g-btn" href="{{ \App\Http\Controllers\PageController::serviceUrl($featuredKey) }}">{{ __('Научете повече') }}<i></i></a></div>
                    </article>
                @endif
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
