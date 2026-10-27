<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Админ') · Creatium Lab</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
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
                        ['admin.client-emails.index', 'admin.client-emails.*', 'paper-plane', 'Имейли до клиенти', 0],
                        ['admin.chats.index', 'admin.chats.*', 'quote-left', 'Чат разговори', 0],
                    ],
                    'Продажби' => [
                        ['admin.outreach.index', 'admin.outreach.*', 'magnifying-glass', 'Потенциални клиенти', $callbacksToday],
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
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="hidden sm:flex items-center bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors whitespace-nowrap text-sm">
                        <x-icon name="home" class="mr-2" />Към сайта
                    </a>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 p-4 md:p-6">
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
    <script>
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
        sidebar?.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
    </script>
    @stack('scripts')
</body>
</html>
