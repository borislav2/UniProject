<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $pageTitle = trim($__env->yieldContent('title', 'Уебсайтове и маркетинг за бизнеса'));
        $pageDescription = trim($__env->yieldContent('meta_description', 'Creatium Lab изгражда бързи и красиви сайтове за български бизнеси и ги подкрепя с маркетинг и SEO. Безплатна консултация.'));
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
    <meta name="theme-color" content="#2563eb">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center" aria-label="Creatium Lab - начало">
                        <span class="font-extrabold text-xl text-gray-900">Creatium</span><span class="font-extrabold text-xl text-blue-600">&nbsp;Lab</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    @php
                        $links = [
                            ['Услуги', route('services'), request()->routeIs('services')],
                            ['Проекти', route('portfolio'), request()->routeIs('portfolio')],
                            ['Пакети', route('home') . '#paketi', false],
                            ['За нас', route('about'), request()->routeIs('about')],
                            ['Контакти', route('contact'), request()->routeIs('contact')],
                        ];
                    @endphp
                    @foreach($links as [$label, $href, $active])
                        <a href="{{ $href }}" class="{{ $active ? 'text-blue-600' : 'text-gray-700 hover:text-blue-600' }} py-2 text-sm font-medium transition-colors">{{ $label }}</a>
                    @endforeach

                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 text-sm" title="Административен панел" aria-label="Административен панел">
                            <i class="fas fa-user-gear"></i>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-blue-600 text-sm" title="Изход" aria-label="Изход">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    @endauth

                    <a href="{{ route('contact') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                        Безплатна консултация
                    </a>
                </div>

                <div class="md:hidden flex items-center">
                    <button type="button" class="mobile-menu-button text-gray-700 hover:text-blue-600 focus:outline-none" aria-label="Меню" aria-expanded="false">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="mobile-menu hidden md:hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                @foreach($links as [$label, $href, $active])
                    <a href="{{ $href }}" class="block px-3 py-2 {{ $active ? 'text-blue-600' : 'text-gray-700 hover:text-blue-600' }}">{{ $label }}</a>
                @endforeach
                <a href="{{ route('contact') }}" class="block px-3 py-2 bg-blue-600 text-white rounded mt-2 text-center font-semibold">Безплатна консултация</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-gray-500">Административен панел</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 text-gray-500">Изход</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="mb-4">
                        <span class="font-extrabold text-xl">Creatium</span><span class="font-extrabold text-xl text-blue-400">&nbsp;Lab</span>
                    </div>
                    <p class="text-gray-300">Уебсайтове и маркетинг за бизнеса в България.</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Навигация</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('services') }}" class="text-gray-300 hover:text-white transition-colors">Услуги</a></li>
                        <li><a href="{{ route('portfolio') }}" class="text-gray-300 hover:text-white transition-colors">Проекти</a></li>
                        <li><a href="{{ route('home') }}#paketi" class="text-gray-300 hover:text-white transition-colors">Пакети</a></li>
                        <li><a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition-colors">За нас</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-300 hover:text-white transition-colors">Контакти</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Контакти</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><i class="fas fa-envelope mr-2"></i><a href="mailto:{{ config('creatium.contact.email') }}" class="hover:text-white">{{ config('creatium.contact.email') }}</a></li>
                        <li><i class="fas fa-phone mr-2"></i>{{ config('creatium.contact.phone') }}</li>
                        <li><i class="fas fa-location-dot mr-2"></i>{{ config('creatium.contact.city') }}, България</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between gap-4 text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} Creatium Lab. Всички права запазени.</p>
                <div class="flex gap-6">
                    <a href="{{ route('privacy') }}" class="hover:text-white">Политика за поверителност</a>
                    <a href="{{ route('terms') }}" class="hover:text-white">Условия за ползване</a>
                    @guest
                        <a href="{{ route('login') }}" class="hover:text-white">Вход за екипа</a>
                    @endguest
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.querySelector('.mobile-menu-button').addEventListener('click', function () {
            var menu = document.querySelector('.mobile-menu');
            menu.classList.toggle('hidden');
            this.setAttribute('aria-expanded', menu.classList.contains('hidden') ? 'false' : 'true');
        });
    </script>
</body>
</html>
