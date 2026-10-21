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
    @if(config('creatium.bing_site_verification'))
        <meta name="msvalidate.01" content="{{ config('creatium.bing_site_verification') }}">
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
        $pageDescription = $seo['meta_description'] ?? trim($__env->yieldContent('meta_description', __('Правим сайтове за малки бизнеси и се грижим хората да ги намират в Google.')));
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
    @unless(\Illuminate\Support\Facades\Vite::isRunningHot())
        {{-- Start downloading the font with the HTML instead of after the CSS (removes the request chain PageSpeed flags) --}}
        @php
            $fonts = [];
            foreach (['cyrillic', 'latin'] as $subset) {
                $fonts[] = rescue(fn () => \Illuminate\Support\Facades\Vite::asset("node_modules/@fontsource-variable/manrope/files/manrope-{$subset}-wght-normal.woff2"), '', false);
            }
        @endphp
        @foreach(array_filter($fonts) as $font)
            <link rel="preload" href="{{ $font }}" as="font" type="font/woff2" crossorigin>
        @endforeach
    @endunless
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
        // Главно меню: Услуги ▾ · Пакети · Проекти · За нас ▾ и бутон „Безплатна консултация“.
        $is = fn (...$patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p, 'en.' . $p));
        $serviceLinks = [[__('Всички услуги'), lroute('services'), $is('services'), 'list-ul']];
        foreach (site('service_pages') as $key => $servicePage) {
            $serviceLinks[] = [$servicePage['menu'], \App\Http\Controllers\PageController::serviceUrl($key), request()->routeIs('services.show', 'en.services.show') && request()->route('slug') === $servicePage['slug'], $servicePage['icon']];
        }
        $serviceLinks[] = [__('Безплатна проверка на сайт'), lroute('site-check'), $is('site-check'), 'gauge-high'];
        $aboutLinks = [[__('Кои сме'), lroute('about'), $is('about'), 'users']];
        if (config('creatium.blog_enabled')) {
            $aboutLinks[] = [__('Блог'), lroute('blog.index'), $is('blog.*'), 'newspaper'];
        }
        $aboutLinks[] = [__('Често задавани въпроси'), lroute('about') . '#vaprosi', false, 'quote-left'];
        $aboutLinks[] = [__('Контакти'), lroute('contact'), $is('contact'), 'envelope'];
        $menu = [
            ['label' => __('Услуги'), 'children' => $serviceLinks, 'active' => $is('services', 'services.show', 'site-check')],
            ['label' => __('Пакети'), 'href' => lroute('packages'), 'active' => $is('packages')],
            ['label' => __('Проекти'), 'href' => lroute('portfolio'), 'active' => $is('portfolio')],
            ['label' => __('За нас'), 'children' => $aboutLinks, 'active' => $is('about', 'blog.*', 'contact')],
        ];
        $consultationUrl = lroute('contact') . '#konsultatsiya';
        $languages = ['bg' => ['BG', 'Български'], 'en' => ['EN', 'English']];
    @endphp

    <div class="scroll-progress" aria-hidden="true"></div>
    <header data-header class="sticky top-0 z-50 px-3 pt-3 md:px-4 md:pt-4 -mb-[4.25rem] md:-mb-20">
      <div class="mx-auto max-w-7xl rounded-2xl border border-white/60 bg-white shadow-[0_10px_30px_-14px_rgb(15_26_43/0.3)] backdrop-blur-xl backdrop-saturate-150">
        <nav class="px-4 sm:px-5" aria-label="{{ __('Основна навигация') }}">
            <div class="flex justify-between items-center h-14 md:h-16">
                <a href="{{ lroute('home') }}" class="flex items-center shrink-0" aria-label="{{ __('Creatium Lab - начало') }}">
                    <img src="{{ asset('images/logo.webp') }}" srcset="{{ asset('images/logo-224.webp') }} 224w, {{ asset('images/logo-352.webp') }} 352w, {{ asset('images/logo.webp') }} 445w" sizes="(min-width: 768px) 223px, 195px" alt="Creatium Lab" width="445" height="64" fetchpriority="high" class="h-7 md:h-8 w-auto">
                </a>

                <div class="hidden lg:flex items-center gap-0.5 xl:gap-1">
                    @foreach($menu as $item)
                        @php($itemClass = 'relative inline-flex items-center gap-1 whitespace-nowrap px-2.5 xl:px-3 py-2 text-sm font-semibold rounded-lg transition-colors ' . ($item['active'] ? 'text-brand-700' : 'text-gray-600 hover:text-brand-950 hover:bg-gray-50'))
                        @if(isset($item['children']))
                            <div class="relative group" data-dropdown>
                                <button type="button" class="{{ $itemClass }}" aria-expanded="false" aria-haspopup="true" data-dropdown-button>
                                    {{ $item['label'] }} <x-icon name="chevron-down" class="text-[10px] transition-transform group-hover:rotate-180" />
                                    @if($item['active'])<span class="absolute left-2.5 right-2.5 xl:left-3 xl:right-3 -bottom-0.5 h-0.5 rounded-full bg-brand-500"></span>@endif
                                </button>
                                <div class="absolute left-0 top-full z-50 hidden pt-2 group-hover:block group-focus-within:block" data-dropdown-panel>
                                    <div class="w-80 rounded-2xl border border-gray-200 bg-white p-2 shadow-xl shadow-brand-950/10">
                                        @foreach($item['children'] as [$label, $href, $active, $icon])
                                            <a href="{{ $href }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ $active ? 'bg-brand-50 text-brand-700' : 'text-gray-700 hover:bg-gray-50 hover:text-brand-950' }}" @if($active) aria-current="page" @endif>
                                                <span class="w-8 h-8 shrink-0 rounded-lg bg-brand-50 flex items-center justify-center"><x-icon :name="$icon" class="text-xs text-brand-600" /></span>{{ $label }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ $item['href'] }}" class="{{ $itemClass }}" @if($item['active']) aria-current="page" @endif>
                                {{ $item['label'] }}
                                @if($item['active'])<span class="absolute left-2.5 right-2.5 xl:left-3 xl:right-3 -bottom-0.5 h-0.5 rounded-full bg-brand-500"></span>@endif
                            </a>
                        @endif
                    @endforeach

                    <a href="{{ $consultationUrl }}" class="btn-shine ml-2 whitespace-nowrap rounded-xl bg-brand-950 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800">{{ __('Безплатна консултация') }}</a>

                    <div class="ml-3 flex items-center rounded-lg border border-gray-200 p-0.5 text-xs font-bold" role="group" aria-label="{{ __('Език') }}">
                        @foreach($languages as $code => [$short, $name])
                            <a href="{{ $alternates[$code] }}" hreflang="{{ $code }}" lang="{{ $code }}" title="{{ $name }}" class="px-2 py-1 rounded-md transition-colors {{ $locale === $code ? 'bg-brand-950 text-white' : 'text-gray-600 hover:text-brand-950' }}" @if($locale === $code) aria-current="true" @endif>{{ $short }}</a>
                        @endforeach
                    </div>

                    <button type="button" data-theme-toggle class="ml-1 w-9 h-9 inline-flex items-center justify-center rounded-lg text-gray-600 hover:text-brand-950 hover:bg-gray-50" aria-label="{{ __('Тъмна тема') }}" aria-pressed="false">
                        <span class="inline-flex dark:hidden"><x-icon name="moon" /></span><span class="hidden dark:inline-flex"><x-icon name="sun" /></span>
                    </button>

                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-brand-700 hover:bg-gray-50" title="{{ __('Административен панел') }}" aria-label="{{ __('Административен панел') }}">
                            <x-icon name="user-gear" />
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-brand-700 hover:bg-gray-50" title="{{ __('Изход') }}" aria-label="{{ __('Изход') }}">
                                <x-icon name="sign-out-alt" />
                            </button>
                        </form>
                    @endauth

                </div>

                <div class="flex items-center gap-1 lg:hidden">
                    @foreach($languages as $code => [$short, $name])
                        @if($code !== $locale)
                            <a href="{{ $alternates[$code] }}" hreflang="{{ $code }}" lang="{{ $code }}" class="h-10 px-2.5 inline-flex items-center rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100" title="{{ $name }}">{{ $short }}</a>
                        @endif
                    @endforeach
                    <button type="button" data-theme-toggle class="w-10 h-10 inline-flex items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100" aria-label="{{ __('Тъмна тема') }}" aria-pressed="false">
                        <span class="inline-flex dark:hidden"><x-icon name="moon" /></span><span class="hidden dark:inline-flex"><x-icon name="sun" /></span>
                    </button>
                    <button type="button" class="mobile-menu-button w-10 h-10 inline-flex items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100" aria-label="{{ __('Меню') }}" aria-expanded="false" aria-controls="mobile-menu">
                        <x-icon name="bars" class="text-lg" />
                    </button>
                </div>
            </div>
        </nav>

        <div id="mobile-menu" class="mobile-menu hidden lg:hidden border-t border-gray-200/70">
            <div class="px-4 py-3 space-y-1">
                @foreach($menu as $item)
                    @if(isset($item['children']))
                        <details class="group" @if($item['active']) open @endif>
                            <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-2.5 rounded-lg font-medium {{ $item['active'] ? 'text-brand-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                {{ $item['label'] }} <x-icon name="chevron-down" class="text-xs transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="ml-3 border-l border-gray-200 pl-2 pb-1">
                                @foreach($item['children'] as [$label, $href, $active, $icon])
                                    <a href="{{ $href }}" class="block px-3 py-2 rounded-lg text-sm {{ $active ? 'text-brand-700 bg-brand-50 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        </details>
                    @else
                        <a href="{{ $item['href'] }}" class="block px-3 py-2.5 rounded-lg font-medium {{ $item['active'] ? 'text-brand-700 bg-brand-50' : 'text-gray-700 hover:bg-gray-50' }}">{{ $item['label'] }}</a>
                    @endif
                @endforeach
                <a href="{{ $consultationUrl }}" class="block px-3 py-3 bg-brand-950 text-white rounded-xl mt-2 text-center font-semibold">{{ __('Безплатна консултация') }}</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-gray-500">{{ __('Административен панел') }}</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 text-gray-500">{{ __('Изход') }}</button>
                    </form>
                @endauth
            </div>
        </div>
      </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="relative surface-night grain on-dark text-white overflow-hidden">
        <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_20%,transparent_70%)]" aria-hidden="true"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 md:pt-20">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 py-14">
                <div class="md:col-span-4">
                    <img src="{{ asset('images/logo-white.webp') }}" srcset="{{ asset('images/logo-white-224.webp') }} 224w, {{ asset('images/logo-white-352.webp') }} 352w, {{ asset('images/logo-white.webp') }} 445w" sizes="223px" alt="Creatium Lab" width="445" height="64" class="h-8 w-auto" loading="lazy" decoding="async">
                    <p class="mt-5 max-w-sm text-gray-400 leading-relaxed">{{ __('Правим сайтове за малки бизнеси и се грижим хората да ги намират в Google.') }}</p>
                </div>

                <div class="md:col-span-3">
                    <h2 class="text-xs font-bold uppercase tracking-[0.18em] text-brand-300 mb-5">{{ __('Услуги') }}</h2>
                    <ul class="space-y-3">
                        @foreach(array_slice($serviceLinks, 1) as [$label, $href])
                            <li><a href="{{ $href }}" class="text-gray-300 hover:text-white transition-colors">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="md:col-span-2">
                    <h2 class="text-xs font-bold uppercase tracking-[0.18em] text-brand-300 mb-5">{{ __('Навигация') }}</h2>
                    <ul class="space-y-3">
                        @foreach(array_merge([[__('Пакети'), lroute('packages')], [__('Проекти'), lroute('portfolio')]], $aboutLinks) as [$label, $href])
                            <li><a href="{{ $href }}" class="text-gray-300 hover:text-white transition-colors">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="md:col-span-3">
                    <h2 class="text-xs font-bold uppercase tracking-[0.18em] text-brand-300 mb-5">{{ __('Контакти') }}</h2>
                    <ul class="space-y-3.5 text-gray-300">
                        <li class="flex items-center gap-3"><x-icon name="envelope" class="w-4 text-brand-300" /><a href="mailto:{{ config('creatium.contact.email') }}" class="hover:text-white">{{ config('creatium.contact.email') }}</a></li>
                        <li class="flex items-start gap-3"><x-icon name="phone" class="w-4 mt-1 text-brand-300" /><span class="flex flex-col">@foreach(config('creatium.contact.phones') as $phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:text-white">{{ $phone }}</a>@endforeach</span></li>
                        <li class="flex items-center gap-3"><x-icon name="location-dot" class="w-4 text-brand-300" />{{ __(config('creatium.contact.city')) }}, {{ __('България') }}</li>
                    </ul>
                    @php($presentationPdf = 'files/' . (app()->getLocale() === 'en' ? 'creatium-lab-presentation.pdf' : 'creatium-lab-prezentatsiya.pdf'))
                    @if(is_file(public_path($presentationPdf)))
                        <a href="{{ asset($presentationPdf) }}" download class="relative edge glass mt-6 flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-white hover:bg-white/10 transition-colors">
                            <x-icon name="file" class="text-brand-300" /> {{ __('Презентация (PDF)') }} <x-icon name="arrow-right" class="ml-auto text-[10px] text-brand-300 rotate-90" />
                        </a>
                    @endif
                </div>
            </div>

            <div class="border-t border-white/10 py-6 flex flex-col md:flex-row justify-between gap-4 text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} Creatium Lab. {{ __('Всички права запазени.') }}</p>
                <div class="flex flex-wrap gap-x-6 gap-y-2">
                    <a href="{{ lroute('privacy') }}" class="hover:text-white">{{ __('Политика за поверителност') }}</a>
                    <a href="{{ lroute('terms') }}" class="hover:text-white">{{ __('Общи условия') }}</a>
                    <a href="{{ lroute('cookies') }}" class="hover:text-white">{{ __('Политика за бисквитките') }}</a>
                    @if(config('creatium.gtm_id'))
                        <a href="#" data-cookie-settings class="hover:text-white">{{ __('Настройки за бисквитки') }}</a>
                    @endif
                    @guest
                        <a href="{{ route('login') }}" rel="nofollow" class="hover:text-white">{{ __('Вход за екипа') }}</a>
                    @endguest
                </div>
            </div>
        </div>
        <div class="wordmark relative pointer-events-none text-center whitespace-nowrap text-[15vw] -mb-[2.5vw] pt-2" aria-hidden="true">Creatium Lab</div>
    </footer>

    @if(config('creatium.chat.enabled'))
        @include('partials/chat-widget')
    @endif

    @if(config('creatium.gtm_id'))
        @include('partials/cookie-consent')
    @endif

    <script>
        // Подменютата: отварят се при посочване, а с клик/докосване или клавиатура остават отворени до клик навън или Esc.
        document.querySelectorAll('[data-dropdown]').forEach(function (dropdown) {
            var button = dropdown.querySelector('[data-dropdown-button]');
            var panel = dropdown.querySelector('[data-dropdown-panel]');
            function set(open) {
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
                panel.classList.toggle('hidden', !open);
            }
            button.addEventListener('click', function () { set(button.getAttribute('aria-expanded') !== 'true'); });
            document.addEventListener('click', function (e) { if (!dropdown.contains(e.target)) { set(false); } });
            dropdown.addEventListener('keydown', function (e) { if (e.key === 'Escape') { set(false); button.focus(); } });
        });

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
