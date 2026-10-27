<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Админ') · Creatium Lab</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    {{-- Админът може да се добави към началния екран на телефона като приложение (отваря „Днес“). --}}
    <link rel="manifest" href="{{ asset('admin.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0f1a2b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Creatium">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <style>
        body.sidebar-open { overflow: hidden; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            body.sidebar-open .sidebar { transform: translateX(0); }
            .sidebar-overlay { display: none; }
            body.sidebar-open .sidebar-overlay { display: block; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar fixed inset-y-0 left-0 z-40 w-64 bg-gray-800 text-white overflow-y-auto md:relative md:transform-none">
            <div class="p-4">
                <a href="{{ route('admin.dashboard') }}" class="block"><img src="{{ asset('images/logo-white.webp') }}" alt="Creatium Lab" class="h-6 w-auto"></a>
                <p class="mt-2 text-xs uppercase tracking-wider text-gray-400">Административен панел</p>
            </div>
            @php
                $unreadInquiries = \App\Models\Project::where('source', 'website')->whereNull('read_at')->count();
                $urgentToday = \App\Support\Admin\Today::urgentCount();
                $latePayments = \App\Models\Project::subscribers()->with('payments')->get()->filter(fn ($client) => $client->paymentState()['state'] === 'late')->count();
                $callbacksToday = \App\Models\OutreachCall::whereNotNull('callback_on')->whereDate('callback_on', '<=', today())->count();
                // [група, [route, pattern, икона, текст, брояч]]
                $menu = [
                    null => [
                        ['admin.today', 'admin.today', 'calendar', 'Днес', $urgentToday],
                        ['admin.dashboard', 'admin.dashboard', 'tachometer-alt', 'Табло', 0],
                    ],
                    'Клиенти' => [
                        ['admin.inquiries.index', 'admin.inquiries.*', 'envelope', 'Запитвания', $unreadInquiries],
                        ['admin.projects.index', 'admin.projects.*', 'project-diagram', 'Проекти', 0],
                        ['admin.clients.index', 'admin.clients.*', 'users', 'Абонаменти', $latePayments],
                        ['admin.client-emails.index', 'admin.client-emails.*', 'paper-plane', 'Имейли до клиенти', 0],
                        ['admin.chats.index', 'admin.chats.*', 'quote-left', 'Чат разговори', 0],
                    ],
                    'Продажби' => [
                        ['admin.outreach.index', 'admin.outreach.*', 'magnifying-glass', 'Потенциални клиенти', $callbacksToday],
                        ['admin.stats', 'admin.stats', 'chart-line', 'Статистика', 0],
                        ['admin.reviews.index', 'admin.reviews.*', 'reply', 'Отговори на отзиви', 0],
                    ],
                    'Сайт' => [
                        ['admin.posts.index', 'admin.posts.*', 'newspaper', 'Блог', 0],
                        ['admin.post-categories.index', 'admin.post-categories.*', 'tags', 'Категории на блога', 0],
                        ['admin.content.index', 'admin.content.*', 'pen-to-square', 'Текстове по страниците', 0],
                        ['admin.seo.index', 'admin.seo.*', 'magnifying-glass', 'SEO', 0],
                    ],
                    'Настройки' => array_values(array_filter([
                        ['admin.categories.index', 'admin.categories.*', 'tags', 'Категории на проекти', 0],
                        ['admin.technologies.index', 'admin.technologies.*', 'cogs', 'Технологии', 0],
                        auth()->user()->isAdmin() ? ['admin.users.index', 'admin.users.*', 'users', 'Потребители', 0] : null,
                        ['admin.health', 'admin.health', 'cogs', 'Здраве на системата', 0],
                        ['admin.profile.edit', 'admin.profile.*', 'user-gear', 'Моят профил', 0],
                    ])),
                ];
            @endphp
            <nav class="mt-4 pb-6">
                @foreach($menu as $group => $items)
                    @if($group)<p class="mt-5 px-4 pb-1 text-xs uppercase tracking-wider text-gray-400">{{ $group }}</p>@endif
                    @foreach($items as [$route, $pattern, $icon, $label, $count])
                        <a href="{{ route($route) }}" class="flex items-center justify-between gap-2 px-4 py-2 hover:bg-gray-700 {{ request()->routeIs($pattern) ? 'bg-gray-700' : '' }}" @if(request()->routeIs($pattern)) aria-current="page" @endif>
                            <span><x-icon name="{{ $icon }}" class="mr-2" /> {{ $label }}</span>
                            @if($count > 0)<span class="rounded-full bg-blue-500 px-2 py-0.5 text-xs font-semibold text-white">{{ $count }}</span>@endif
                        </a>
                    @endforeach
                @endforeach
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-700"><x-icon name="sign-out-alt" class="mr-2" /> Изход</button>
                </form>
            </nav>
        </div>

        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay fixed inset-0 bg-black/50 z-30 md:hidden"></div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0 flex flex-col overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b sticky top-0 z-20">
                <div class="px-4 md:px-6 py-4 flex justify-between items-center gap-4">
                    <button id="menu-toggle" class="md:hidden p-2 hover:bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-lg md:text-2xl font-semibold text-gray-800 truncate">@yield('title', 'Админ')</h2>
                    <form action="{{ route('admin.search') }}" method="GET" class="ml-auto hidden md:flex" role="search">
                        <input type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" placeholder="Търсене (Ctrl+K)" class="w-56 lg:w-72 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" data-global-search>
                    </form>
                    <a href="{{ route('admin.search') }}" class="ml-auto md:hidden p-2 text-gray-600" aria-label="Търсене"><x-icon name="search" /></a>
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="hidden sm:flex items-center bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors whitespace-nowrap text-sm">
                        <x-icon name="home" class="mr-2" />Към сайта
                    </a>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 p-4 pb-24 md:p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any() && request()->routeIs('admin.posts.*', 'admin.post-categories.*', 'admin.content.*', 'admin.seo.*', 'admin.profile.*', 'admin.client-emails.*'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                        <p class="font-semibold">Има грешки във формата:</p>
                        <ul class="list-disc pl-5 mt-1 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    {{-- Долно меню на телефона: най-честите неща с палеца. --}}
    <nav class="md:hidden fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 border-t border-gray-200 bg-white pb-[env(safe-area-inset-bottom)] text-[11px] font-semibold text-gray-600" aria-label="Бързо меню">
        @foreach([
            ['admin.today', 'admin.today', 'calendar', 'Днес', $urgentToday],
            ['admin.inquiries.index', 'admin.inquiries.*', 'envelope', 'Запитвания', $unreadInquiries],
            ['admin.outreach.calls.next', 'admin.outreach.calls.*', 'phone', 'Обаждания', 0],
            ['admin.search', 'admin.search', 'search', 'Търсене', 0],
        ] as [$route, $pattern, $icon, $label, $count])
            <a href="{{ route($route) }}" class="relative flex flex-col items-center gap-1 py-2 {{ request()->routeIs($pattern) ? 'text-brand-700' : '' }}">
                <x-icon name="{{ $icon }}" class="text-lg" />{{ $label }}
                @if($count > 0)<span class="absolute right-3 top-1 rounded-full bg-blue-500 px-1.5 text-[10px] text-white">{{ $count }}</span>@endif
            </a>
        @endforeach
        <button type="button" class="flex flex-col items-center gap-1 py-2" data-bottom-menu><svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>Меню</button>
    </nav>

    <script>
        document.querySelector('[data-bottom-menu]')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        function closeSidebar() {
            document.body.classList.remove('sidebar-open');
        }

        menuToggle?.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-open');
        });

        overlay?.addEventListener('click', closeSidebar);

        // Ctrl+K (или Cmd+K) отваря търсенето отвсякъде.
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                const box = document.querySelector('[data-global-search]');
                if (box && box.offsetParent !== null) { box.focus(); box.select(); } else { location.href = @json(route('admin.search')); }
            }
        });
        sidebar?.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
    </script>
    @stack('scripts')
</body>
</html>
