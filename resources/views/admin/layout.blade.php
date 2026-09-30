<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Project Management</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 shrink-0 bg-gray-800 text-white overflow-y-auto">
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
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0 flex flex-col overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-800">@yield('title', 'Admin Panel')</h2>
                    <a href="{{ route('home') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        <x-icon name="home" class="mr-2" />Back to Home
                    </a>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
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
</body>
</html>
