{{-- Header of the redesign (concept G): the logo image (white one in the dark theme), four links, the theme and language switches and one button. --}}
@php
    // Third round (16.11.2026): „Услуги“ opens a dropdown with all the services (the seven of service_pages, software development included).
    $gServices = collect(site('service_pages'))->map(fn ($service, $key) => [
        $service['menu'],
        \App\Http\Controllers\PageController::serviceUrl($key),
        request()->routeIs('services.show', 'en.services.show') && request()->route('slug') === $service['slug'],
    ])->values()->all();
    $gNav = [
        [__('Услуги'), lroute('services'), $is('services', 'services.show', 'site-check'), $gServices],
        [__('Пакети'), lroute('packages'), $is('packages'), []],
        [__('Проекти'), lroute('portfolio'), $is('portfolio'), []],
        [__('За нас'), lroute('about'), $is('about', 'blog.*', 'faq'), []],
    ];
    $gOffer = g_offer_url();
    $gOther = $locale === 'en' ? 'bg' : 'en';
@endphp
<header class="g-hd">
    <div class="g-wrap g-hd-in">
        <a class="g-logo" href="{{ lroute('home') }}" aria-label="{{ __('Creatium Lab, начало') }}">
            <img class="g-logo-d" src="{{ asset('images/logo-g.webp') }}" srcset="{{ asset('images/logo-g-224.webp') }} 224w, {{ asset('images/logo-g-352.webp') }} 352w, {{ asset('images/logo-g.webp') }} 445w" sizes="(min-width: 768px) 223px, 195px" alt="Creatium Lab" width="445" height="64" fetchpriority="high">
            <img class="g-logo-w" src="{{ asset('images/logo-g-dark.webp') }}" srcset="{{ asset('images/logo-g-dark-224.webp') }} 224w, {{ asset('images/logo-g-dark-352.webp') }} 352w, {{ asset('images/logo-g-dark.webp') }} 445w" sizes="(min-width: 768px) 223px, 195px" alt="" aria-hidden="true" width="445" height="64">
        </a>
        <nav class="g-nav" aria-label="{{ __('Основна навигация') }}">
            @foreach($gNav as [$label, $href, $active, $children])
                @if($children)
                    <div class="g-dd" data-g-dd>
                        <a href="{{ $href }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
                        <button type="button" class="g-dd-btn" aria-expanded="false" aria-controls="g-dd-services" aria-label="{{ __('Покажи услугите') }}"><i aria-hidden="true"></i></button>
                        <div class="g-dd-panel" id="g-dd-services">
                            @foreach($children as [$childLabel, $childHref, $childActive])
                                <a href="{{ $childHref }}" @if($childActive) aria-current="page" @endif>{{ $childLabel }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $href }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
                @endif
            @endforeach
        </nav>
        <div class="g-tools">
            <a class="g-lang g-mono" href="{{ $alternates[$gOther] }}" hreflang="{{ $gOther }}" lang="{{ $gOther }}" title="{{ $languages[$gOther][1] }}">{{ $languages[$gOther][0] }}</a>
            <button type="button" class="g-tbtn" data-theme-toggle aria-label="{{ __('Тъмна тема') }}" aria-pressed="false">
                <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="6.2" fill="none" stroke="currentColor" stroke-width="1.3"/><path d="M8 1.8a6.2 6.2 0 0 0 0 12.4z" fill="currentColor"/></svg>
            </button>
            <a class="g-btn g-sm g-offer" href="{{ $gOffer }}">{{ __('Изпратете запитване') }}</a>
            <button type="button" class="mobile-menu-button g-tbtn g-burger" aria-label="{{ __('Меню') }}" aria-expanded="false" aria-controls="mobile-menu">
                <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><path class="g-b1" d="M2 6h14" stroke="currentColor" stroke-width="1.4"/><path class="g-b2" d="M2 12h14" stroke="currentColor" stroke-width="1.4"/></svg>
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="mobile-menu g-mm hidden">
        <div class="g-wrap">
            @foreach($gNav as [$label, $href, $active, $children])
                <a href="{{ $href }}">{{ $label }}</a>
                @foreach($children as [$childLabel, $childHref])
                    <a href="{{ $childHref }}" class="g-sub g-mm-child">{{ $childLabel }}</a>
                @endforeach
            @endforeach
            <a href="{{ lroute('site-check') }}" class="g-sub">{{ __('Безплатна проверка на сайт') }}</a>
            @if(config('creatium.blog_enabled'))<a href="{{ lroute('blog.index') }}" class="g-sub">{{ __('Новини') }}</a>@endif
            <a href="{{ lroute('faq') }}" class="g-sub">{{ __('Често задавани въпроси') }}</a>
            <a href="{{ lroute('contact') }}" class="g-sub">{{ __('Контакти') }}</a>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="g-sub">{{ __('Административен панел') }}</a>
            @endauth
            <a href="{{ $gOffer }}" class="g-btn g-mm-cta">{{ __('Изпратете запитване') }}</a>
        </div>
    </div>
</header>
