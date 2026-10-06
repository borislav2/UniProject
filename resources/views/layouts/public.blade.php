<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(config('creatium.gtm_id'))
        @include('partials/gtm-head')
    @endif
    @if(config('creatium.google_site_verification'))
        <meta name="google-site-verification" content="{{ config('creatium.google_site_verification') }}">
    @endif
    {{-- Runs before the CSS so there is no flash: saved choice first, otherwise the device setting. --}}
    <script>
        document.documentElement.classList.add('js');
        (function () {
            var theme = null;
            try { theme = localStorage.getItem('creatium-theme'); } catch (e) {}
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @php
        $locale = app()->getLocale();
        $routeName = \Illuminate\Support\Str::after((string) \Illuminate\Support\Facades\Route::currentRouteName(), 'en.');

        // SEO: values from the controller (blog posts) or the admin's per-page settings override the view's defaults.
        $seo = array_filter($seo ?? [], 'filled') ?: app(\App\Support\Content::class)->seo($routeName);
        $pageTitle = $seo['meta_title'] ?? trim($__env->yieldContent('title', __('Сайтове и дигитален маркетинг за малки бизнеси'))) . ' | Creatium Lab';
        $pageDescription = $seo['meta_description'] ?? trim($__env->yieldContent('meta_description', __('Правим сайтове за малки бизнеси и се грижим хората да ги намират в Google. Първата консултация е безплатна.')));
        $canonicalUrl = $seo['canonical_url'] ?? ($canonical ?? url()->current());
        $ogImage = isset($seo['og_image']) ? asset($seo['og_image']) : asset('images/og-image.png');

        // The same page in the other language: for the switcher (always) and hreflang (only real translations).
        if (isset($pageAlternates)) {
            $alternates = $pageAlternates;
            $hreflang = $hreflangAlternates ?? [];
        } else {
            $alternates = ['bg' => lroute('home', [], 'bg'), 'en' => lroute('home', [], 'en')];
            $hreflang = [];
            if (request()->isMethod('GET') && ! isset($exception) && \Illuminate\Support\Facades\Route::has('en.' . $routeName) && \Illuminate\Support\Facades\Route::has($routeName)) {
                try {
                    $alternates = $hreflang = ['bg' => lroute($routeName, [], 'bg'), 'en' => lroute($routeName, [], 'en')];
                } catch (\Illuminate\Routing\Exceptions\UrlGenerationException) {
                    // A route with parameters that did not pass $pageAlternates: keep the home page links.
                }
            }
        }
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if(count($hreflang) > 1)
        @foreach($hreflang as $lang => $href)
            <link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $hreflang['bg'] ?? reset($hreflang) }}">
    @endif
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:locale" content="{{ $locale === 'en' ? 'en_US' : 'bg_BG' }}">
    <meta property="og:locale:alternate" content="{{ $locale === 'en' ? 'bg_BG' : 'en_US' }}">
    <meta property="og:site_name" content="Creatium Lab">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $pageTitle }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    @unless(isset($seo['og_image']))
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endunless
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0f1a2b">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('favicon-64.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @if(config('creatium.inline_css') && ! \Illuminate\Support\Facades\Vite::isRunningHot())
        {{-- Inline CSS (~10 KB gzip) saves a render-blocking request on first load --}}
        <style>{!! \Illuminate\Support\Facades\Vite::content('resources/css/app.css') !!}</style>
        @vite(['resources/js/app.js'])
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('head')
</head>
<body class="bg-white text-gray-900 antialiased dark:bg-ink-950 dark:text-gray-100">
    @if(config('creatium.gtm_id'))
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('creatium.gtm_id') }}" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif
    @php
        $links = [];
        foreach (['services' => 'Услуги', 'packages' => 'Пакети', 'portfolio' => 'Проекти', 'blog.index' => 'Блог', 'about' => 'За нас', 'contact' => 'Контакти'] as $name => $label) {
            $pattern = $name === 'blog.index' ? 'blog.*' : $name;
            $links[] = [__($label), lroute($name), request()->routeIs($pattern, 'en.' . $pattern)];
        }
        $languages = ['bg' => ['BG', 'Български'], 'en' => ['EN', 'English']];
    @endphp

    <header class="sticky top-0 z-50 bg-white/85 backdrop-blur-md border-b border-gray-100 dark:bg-ink-950/85 dark:border-white/10">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="{{ __('Основна навигация') }}">
            <div class="flex justify-between items-center h-16 md:h-20">
                <a href="{{ lroute('home') }}" class="flex items-center shrink-0" aria-label="{{ __('Creatium Lab - начало') }}">
                    <img src="{{ asset('images/logo.webp') }}" srcset="{{ asset('images/logo-224.webp') }} 224w, {{ asset('images/logo-352.webp') }} 352w, {{ asset('images/logo.webp') }} 445w" sizes="(min-width: 768px) 223px, 195px" alt="Creatium Lab" width="445" height="64" fetchpriority="high" class="h-7 md:h-8 w-auto dark:hidden">
                    <img src="{{ asset('images/logo-white.webp') }}" srcset="{{ asset('images/logo-white-224.webp') }} 224w, {{ asset('images/logo-white-352.webp') }} 352w, {{ asset('images/logo-white.webp') }} 445w" sizes="(min-width: 768px) 223px, 195px" alt="Creatium Lab" width="445" height="64" loading="lazy" class="hidden h-7 md:h-8 w-auto dark:block">
                </a>

                <div class="hidden lg:flex items-center gap-1">
                    @foreach($links as [$label, $href, $active])
                        <a href="{{ $href }}" class="relative px-3 py-2 text-sm font-semibold rounded-lg transition-colors {{ $active ? 'text-brand-700 dark:text-brand-300' : 'text-gray-600 hover:text-brand-950 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5' }}" @if($active) aria-current="page" @endif>
                            {{ $label }}
                            @if($active)
                                <span class="absolute left-3 right-3 -bottom-0.5 h-0.5 rounded-full bg-brand-500"></span>
                            @endif
                        </a>
                    @endforeach

                    <div class="ml-3 flex items-center rounded-lg border border-gray-200 p-0.5 text-xs font-bold dark:border-white/15" role="group" aria-label="{{ __('Език') }}">
                        @foreach($languages as $code => [$short, $name])
                            <a href="{{ $alternates[$code] }}" hreflang="{{ $code }}" lang="{{ $code }}" title="{{ $name }}" class="px-2 py-1 rounded-md transition-colors {{ $locale === $code ? 'bg-brand-950 text-white dark:bg-white dark:text-brand-950' : 'text-gray-600 hover:text-brand-950 dark:text-gray-300 dark:hover:text-white' }}" @if($locale === $code) aria-current="true" @endif>{{ $short }}</a>
                        @endforeach
                    </div>

                    <button type="button" data-theme-toggle class="ml-1 w-9 h-9 inline-flex items-center justify-center rounded-lg text-gray-600 hover:text-brand-950 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/5" aria-label="{{ __('Тъмна тема') }}" aria-pressed="false">
                        <span class="inline-flex dark:hidden"><x-icon name="moon" /></span><span class="hidden dark:inline-flex"><x-icon name="sun" /></span>
                    </button>

                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-brand-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/5" title="{{ __('Административен панел') }}" aria-label="{{ __('Административен панел') }}">
                            <x-icon name="user-gear" />
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-brand-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/5" title="{{ __('Изход') }}" aria-label="{{ __('Изход') }}">
                                <x-icon name="sign-out-alt" />
                            </button>
                        </form>
                    @endauth

                    <a href="{{ lroute('contact') }}" class="ml-2 hidden xl:inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-xl hover:bg-brand-800 transition-colors text-sm font-semibold shadow-sm dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                        {{ __('Безплатна консултация') }} <x-icon name="arrow-right" class="text-xs" />
                    </a>
                </div>

                <div class="flex items-center gap-1 lg:hidden">
                    @foreach($languages as $code => [$short, $name])
                        @if($code !== $locale)
                            <a href="{{ $alternates[$code] }}" hreflang="{{ $code }}" lang="{{ $code }}" class="h-10 px-2.5 inline-flex items-center rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/10" title="{{ $name }}">{{ $short }}</a>
                        @endif
                    @endforeach
                    <button type="button" data-theme-toggle class="w-10 h-10 inline-flex items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/10" aria-label="{{ __('Тъмна тема') }}" aria-pressed="false">
                        <span class="inline-flex dark:hidden"><x-icon name="moon" /></span><span class="hidden dark:inline-flex"><x-icon name="sun" /></span>
                    </button>
                    <button type="button" class="mobile-menu-button w-10 h-10 inline-flex items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/10" aria-label="{{ __('Меню') }}" aria-expanded="false" aria-controls="mobile-menu">
                        <x-icon name="bars" class="text-lg" />
                    </button>
                </div>
            </div>
        </nav>

        <div id="mobile-menu" class="mobile-menu hidden lg:hidden border-t border-gray-100 bg-white dark:bg-ink-950 dark:border-white/10">
            <div class="px-4 py-3 space-y-1">
                @foreach($links as [$label, $href, $active])
                    <a href="{{ $href }}" class="block px-3 py-2.5 rounded-lg font-medium {{ $active ? 'text-brand-700 bg-brand-50 dark:text-brand-300 dark:bg-white/5' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5' }}">{{ $label }}</a>
                @endforeach
                <a href="{{ lroute('contact') }}" class="block px-3 py-3 bg-brand-950 text-white rounded-xl mt-2 text-center font-semibold dark:bg-white dark:text-brand-950">{{ __('Безплатна консултация') }}</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-gray-500 dark:text-gray-400">{{ __('Административен панел') }}</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 text-gray-500 dark:text-gray-400">{{ __('Изход') }}</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="relative bg-brand-950 text-white overflow-hidden dark:bg-ink-900 dark:border-t dark:border-white/10">
        <div class="absolute inset-0 bg-grid-light opacity-60" aria-hidden="true"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                <div class="md:col-span-5">
                    <img src="{{ asset('images/logo-white.webp') }}" srcset="{{ asset('images/logo-white-224.webp') }} 224w, {{ asset('images/logo-white-352.webp') }} 352w, {{ asset('images/logo-white.webp') }} 445w" sizes="223px" alt="Creatium Lab" width="445" height="64" class="h-8 w-auto mb-5" loading="lazy" decoding="async">
                    <p class="max-w-sm text-2xl font-extrabold leading-tight tracking-tight">{{ site('hero')['title'] }} <span class="text-brand-300">{{ site('hero')['highlight'] }}</span></p>
                    <a href="{{ lroute('contact') }}" class="mt-6 inline-flex items-center gap-2 bg-white text-brand-950 px-5 py-2.5 rounded-xl font-semibold hover:bg-brand-50 transition-colors">
                        {{ __('Свържете се с нас') }} <x-icon name="arrow-right" class="text-xs" />
                    </a>
                </div>

                <div class="md:col-span-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brand-300 mb-4">{{ __('Навигация') }}</h2>
                    <ul class="space-y-2.5">
                        @foreach($links as [$label, $href])
                            <li><a href="{{ $href }}" class="text-gray-300 hover:text-white transition-colors">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brand-300 mb-4">{{ __('Контакти') }}</h2>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-center gap-3"><x-icon name="envelope" class="w-4 text-brand-300" /><a href="mailto:{{ config('creatium.contact.email') }}" class="hover:text-white">{{ config('creatium.contact.email') }}</a></li>
                        <li class="flex items-start gap-3"><x-icon name="phone" class="w-4 mt-1 text-brand-300" /><span class="flex flex-col">@foreach(config('creatium.contact.phones') as $phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:text-white">{{ $phone }}</a>@endforeach</span></li>
                        <li class="flex items-center gap-3"><x-icon name="location-dot" class="w-4 text-brand-300" />{{ __(config('creatium.contact.city')) }}, {{ __('България') }}</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10 mt-12 pt-6 flex flex-col md:flex-row justify-between gap-4 text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} Creatium Lab. {{ __('Всички права запазени.') }}</p>
                <div class="flex flex-wrap gap-x-6 gap-y-2">
                    <a href="{{ lroute('privacy') }}" class="hover:text-white">{{ __('Политика за поверителност') }}</a>
                    <a href="{{ lroute('terms') }}" class="hover:text-white">{{ __('Общи условия') }}</a>
                    <a href="{{ lroute('cookies') }}" class="hover:text-white">{{ __('Политика за бисквитките') }}</a>
                    @if(config('creatium.gtm_id'))
                        <a href="#" data-cookie-settings class="hover:text-white">{{ __('Настройки за бисквитки') }}</a>
                    @endif
                    @guest
                        <a href="{{ route('login') }}" class="hover:text-white">{{ __('Вход за екипа') }}</a>
                    @endguest
                </div>
            </div>
        </div>
    </footer>

    @if(config('creatium.gtm_id'))
        @include('partials/cookie-consent')
    @endif

    <script>
        document.querySelector('.mobile-menu-button').addEventListener('click', function () {
            var menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
            this.setAttribute('aria-expanded', menu.classList.contains('hidden') ? 'false' : 'true');
        });

        (function () {
            var root = document.documentElement;
            var buttons = document.querySelectorAll('[data-theme-toggle]');
            var labels = {dark: @json(__('Светла тема')), light: @json(__('Тъмна тема'))};
            function sync() {
                var dark = root.classList.contains('dark');
                buttons.forEach(function (b) {
                    b.setAttribute('aria-pressed', dark ? 'true' : 'false');
                    b.setAttribute('aria-label', dark ? labels.dark : labels.light);
                });
            }
            buttons.forEach(function (b) {
                b.addEventListener('click', function () {
                    var dark = root.classList.toggle('dark');
                    try { localStorage.setItem('creatium-theme', dark ? 'dark' : 'light'); } catch (e) {}
                    sync();
                });
            });
            // Follow the device setting until the visitor picks a theme here.
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                var saved = null;
                try { saved = localStorage.getItem('creatium-theme'); } catch (err) {}
                if (!saved) { root.classList.toggle('dark', e.matches); sync(); }
            });
            sync();
        })();
    </script>
</body>
</html>
