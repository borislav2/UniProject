<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Project Management</title>
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
            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    <x-icon name="tachometer-alt" class="mr-2" /> Dashboard
                </a>
                <a href="{{ route('admin.projects.index') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.projects.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="project-diagram" class="mr-2" /> Projects
                </a>
                <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="tags" class="mr-2" /> Categories
                </a>
                <a href="{{ route('admin.technologies.index') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.technologies.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="cogs" class="mr-2" /> Technologies
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="users" class="mr-2" /> Users
                </a>
                @endif

                <p class="mt-6 px-4 pb-1 text-xs uppercase tracking-wider text-gray-400">Сайт</p>
                <a href="{{ route('admin.posts.index') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.posts.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="newspaper" class="mr-2" /> Блог
                </a>
                <a href="{{ route('admin.post-categories.index') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.post-categories.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="tags" class="mr-2" /> Категории на блога
                </a>
                <a href="{{ route('admin.content.index') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.content.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="pen-to-square" class="mr-2" /> Текстове по страниците
                </a>
                <a href="{{ route('admin.seo.index') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.seo.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="magnifying-glass" class="mr-2" /> SEO
                </a>

                <p class="mt-6 px-4 pb-1 text-xs uppercase tracking-wider text-gray-400">Акаунт</p>
                <a href="{{ route('admin.profile.edit') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.profile.*') ? 'bg-gray-700' : '' }}">
                    <x-icon name="user-gear" class="mr-2" /> Моят профил
                </a>
                <form action="{{ route('logout') }}" method="POST">
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
                    <h2 class="text-lg md:text-2xl font-semibold text-gray-800 truncate">@yield('title', 'Admin Panel')</h2>
                    <a href="{{ route('home') }}" class="hidden sm:flex items-center bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors whitespace-nowrap text-sm">
                        <x-icon name="home" class="mr-2" />Back to Home
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

                @if($errors->any() && request()->routeIs('admin.posts.*', 'admin.post-categories.*', 'admin.content.*', 'admin.seo.*', 'admin.profile.*'))
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
