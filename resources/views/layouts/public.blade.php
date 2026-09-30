<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>document.documentElement.classList.add('js');</script>
    @php
        $pageTitle = trim($__env->yieldContent('title', 'Сайтове и SEO за малки фирми'));
        $pageDescription = trim($__env->yieldContent('meta_description', 'Правим сайтове за малки фирми и се грижим хората да ги намират в Google. Първата консултация е безплатна.'));
    @endphp
    <title>{{ $pageTitle }} | Creatium Lab</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="bg_BG">
    <meta property="og:site_name" content="Creatium Lab">
    <meta property="og:title" content="{{ $pageTitle }} | Creatium Lab">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0f1a2b">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('favicon-64.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased">
    @php
        $links = [
            ['Услуги', route('services'), request()->routeIs('services')],
            ['Проекти', route('portfolio'), request()->routeIs('portfolio')],
            ['Пакети', route('home') . '#paketi', false],
            ['За нас', route('about'), request()->routeIs('about')],
            ['Контакти', route('contact'), request()->routeIs('contact')],
        ];
    @endphp

    <header class="sticky top-0 z-50 bg-white/85 backdrop-blur-md border-b border-gray-100">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="Основна навигация">
            <div class="flex justify-between items-center h-16 md:h-20">
                <a href="{{ route('home') }}" class="flex items-center shrink-0" aria-label="Creatium Lab - начало">
                    <img src="{{ asset('images/logo.webp') }}" srcset="{{ asset('images/logo-224.webp') }} 224w, {{ asset('images/logo-336.webp') }} 336w, {{ asset('images/logo.webp') }} 445w" sizes="(min-width: 768px) 223px, 195px" alt="Creatium Lab" width="445" height="64" fetchpriority="high" class="h-7 md:h-8 w-auto">
                </a>

                <div class="hidden md:flex items-center gap-1">
                    @foreach($links as [$label, $href, $active])
                        <a href="{{ $href }}" class="relative px-3 py-2 text-sm font-semibold rounded-lg transition-colors {{ $active ? 'text-brand-700' : 'text-gray-600 hover:text-brand-950 hover:bg-gray-50' }}">
                            {{ $label }}
                            @if($active)
                                <span class="absolute left-3 right-3 -bottom-0.5 h-0.5 rounded-full bg-brand-500"></span>
                            @endif
                        </a>
                    @endforeach

                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="ml-2 w-9 h-9 inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-brand-700 hover:bg-gray-50" title="Административен панел" aria-label="Административен панел">
                            <x-icon name="user-gear" />
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-gray-500 hover:text-brand-700 hover:bg-gray-50" title="Изход" aria-label="Изход">
                                <x-icon name="sign-out-alt" />
                            </button>
                        </form>
                    @endauth

                    <a href="{{ route('contact') }}" class="ml-3 inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-xl hover:bg-brand-800 transition-colors text-sm font-semibold shadow-sm">
                        Безплатна консултация <x-icon name="arrow-right" class="text-xs" />
                    </a>
                </div>

                <button type="button" class="mobile-menu-button md:hidden w-10 h-10 inline-flex items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100" aria-label="Меню" aria-expanded="false" aria-controls="mobile-menu">
                    <x-icon name="bars" class="text-lg" />
                </button>
            </div>
        </nav>

        <div id="mobile-menu" class="mobile-menu hidden md:hidden border-t border-gray-100 bg-white">
            <div class="px-4 py-3 space-y-1">
                @foreach($links as [$label, $href, $active])
                    <a href="{{ $href }}" class="block px-3 py-2.5 rounded-lg font-medium {{ $active ? 'text-brand-700 bg-brand-50' : 'text-gray-700 hover:bg-gray-50' }}">{{ $label }}</a>
                @endforeach
                <a href="{{ route('contact') }}" class="block px-3 py-3 bg-brand-950 text-white rounded-xl mt-2 text-center font-semibold">Безплатна консултация</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-gray-500">Административен панел</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 text-gray-500">Изход</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="relative bg-brand-950 text-white overflow-hidden">
        <div class="absolute inset-0 bg-grid-light opacity-60" aria-hidden="true"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                <div class="md:col-span-5">
                    <img src="{{ asset('images/logo-white.webp') }}" alt="Creatium Lab" width="445" height="64" class="h-8 w-auto mb-5" loading="lazy">
                    <p class="text-gray-300 max-w-sm leading-relaxed">Сайтове и SEO за малки фирми в България.</p>
                    <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center gap-2 bg-white text-brand-950 px-5 py-2.5 rounded-xl font-semibold hover:bg-brand-50 transition-colors">
                        Поискайте оферта <x-icon name="arrow-right" class="text-xs" />
                    </a>
                </div>

                <div class="md:col-span-3">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-brand-300 mb-4">Навигация</h3>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('services') }}" class="text-gray-300 hover:text-white transition-colors">Услуги</a></li>
                        <li><a href="{{ route('portfolio') }}" class="text-gray-300 hover:text-white transition-colors">Проекти</a></li>
                        <li><a href="{{ route('home') }}#paketi" class="text-gray-300 hover:text-white transition-colors">Пакети</a></li>
                        <li><a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition-colors">За нас</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-300 hover:text-white transition-colors">Контакти</a></li>
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-brand-300 mb-4">Контакти</h3>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-center gap-3"><x-icon name="envelope" class="w-4 text-brand-300" /><a href="mailto:{{ config('creatium.contact.email') }}" class="hover:text-white">{{ config('creatium.contact.email') }}</a></li>
                        <li class="flex items-center gap-3"><x-icon name="phone" class="w-4 text-brand-300" />{{ config('creatium.contact.phone') }}</li>
                        <li class="flex items-center gap-3"><x-icon name="location-dot" class="w-4 text-brand-300" />{{ config('creatium.contact.city') }}, България</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10 mt-12 pt-6 flex flex-col md:flex-row justify-between gap-4 text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} Creatium Lab. Всички права запазени.</p>
                <div class="flex flex-wrap gap-x-6 gap-y-2">
                    <a href="{{ route('privacy') }}" class="hover:text-white">Политика за поверителност</a>
                    <a href="{{ route('terms') }}" class="hover:text-white">Условия за ползване</a>
                    @if(config('creatium.meta_pixel_id'))
                        <a href="#" data-cookie-settings class="hover:text-white">Настройки за бисквитки</a>
                    @endif
                    @guest
                        <a href="{{ route('login') }}" class="hover:text-white">Вход за екипа</a>
                    @endguest
                </div>
            </div>
        </div>
    </footer>

    @if(config('creatium.meta_pixel_id'))
        @include('partials/cookie-consent')
    @endif

    <script>
        document.querySelector('.mobile-menu-button').addEventListener('click', function () {
            var menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
            this.setAttribute('aria-expanded', menu.classList.contains('hidden') ? 'false' : 'true');
        });
    </script>
</body>
</html>
