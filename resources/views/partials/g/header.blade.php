{{-- Header of the redesign (concept G): a text wordmark, four links, the theme and language switches and one button. --}}
@php
    $gNav = [
        [__('Услуги'), lroute('services'), $is('services', 'services.show', 'site-check')],
        [__('Пакети'), lroute('packages'), $is('packages')],
        [__('Проекти'), lroute('portfolio'), $is('portfolio')],
        [__('За нас'), lroute('about'), $is('about', 'blog.*', 'faq')],
    ];
    $gOther = $locale === 'en' ? 'bg' : 'en';
@endphp
<header class="g-hd">
    <div class="g-wrap g-hd-in">
        <a class="g-logo" href="{{ lroute('home') }}" aria-label="{{ __('Creatium Lab, начало') }}">Creatium Lab<span class="g-dot" aria-hidden="true"></span></a>
        <nav class="g-nav" aria-label="{{ __('Основна навигация') }}">
            @foreach($gNav as [$label, $href, $active])
                <a href="{{ $href }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="g-tools">
            <a class="g-lang g-mono" href="{{ $alternates[$gOther] }}" hreflang="{{ $gOther }}" lang="{{ $gOther }}" title="{{ $languages[$gOther][1] }}">{{ $languages[$gOther][0] }}</a>
            <button type="button" class="g-tbtn" data-theme-toggle aria-label="{{ __('Тъмна тема') }}" aria-pressed="false">
                <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="6.2" fill="none" stroke="currentColor" stroke-width="1.3"/><path d="M8 1.8a6.2 6.2 0 0 0 0 12.4z" fill="currentColor"/></svg>
            </button>
            <a class="g-btn g-sm g-offer" href="{{ $offerUrl }}">{{ __('Получете оферта') }}</a>
            <button type="button" class="mobile-menu-button g-tbtn g-burger" aria-label="{{ __('Меню') }}" aria-expanded="false" aria-controls="mobile-menu">
                <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><path class="g-b1" d="M2 6h14" stroke="currentColor" stroke-width="1.4"/><path class="g-b2" d="M2 12h14" stroke="currentColor" stroke-width="1.4"/></svg>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="mobile-menu g-mm hidden">
        <div class="g-wrap">
            @foreach($gNav as [$label, $href, $active])
                <a href="{{ $href }}">{{ $label }}</a>
            @endforeach
            <a href="{{ lroute('site-check') }}" class="g-sub">{{ __('Безплатна проверка на сайт') }}</a>
            @if(config('creatium.blog_enabled'))<a href="{{ lroute('blog.index') }}" class="g-sub">{{ __('Новини') }}</a>@endif
            <a href="{{ lroute('faq') }}" class="g-sub">{{ __('Често задавани въпроси') }}</a>
            <a href="{{ lroute('contact') }}" class="g-sub">{{ __('Контакти') }}</a>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="g-sub">{{ __('Административен панел') }}</a>
            @endauth
            <a href="{{ $offerUrl }}" class="g-btn g-mm-cta">{{ __('Получете оферта') }}</a>
        </div>
    </div>
</header>
