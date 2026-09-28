<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Creatium Lab') - Creatium Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <span class="font-bold text-xl text-gray-900">Creatium</span><span class="font-bold text-xl text-blue-600">&nbsp;Lab</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}#uslugi" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                        Услуги
                    </a>
                    <a href="{{ route('home') }}#proces" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                        Процес
                    </a>
                    <a href="{{ route('home') }}#paketi" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                        Пакети
                    </a>
                    <a href="{{ route('home') }}#kontakt" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">
                        Контакт
                    </a>

                    @if(auth()->check())
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-blue-600 text-sm" title="Административен панел">
                            <i class="fas fa-user-gear"></i>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-blue-600 text-sm" title="Изход">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-400 hover:text-blue-600 text-sm" title="Вход за екипа">
                            <i class="fas fa-user"></i>
                        </a>
                    @endif

                    <a href="{{ route('home') }}#kontakt" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                        Безплатна консултация
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button class="mobile-menu-button text-gray-700 hover:text-indigo-600 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="mobile-menu hidden md:hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('home') }}#uslugi" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Услуги</a>
                <a href="{{ route('home') }}#proces" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Процес</a>
                <a href="{{ route('home') }}#paketi" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Пакети</a>
                <a href="{{ route('home') }}#kontakt" class="block px-3 py-2 bg-blue-600 text-white rounded mt-2">Контакт</a>

                @if(auth()->check())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-gray-500">Административен панел</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 text-gray-500">Изход</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-gray-500">Вход за екипа</a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <span class="font-bold text-xl">Creatium</span><span class="font-bold text-xl text-blue-400">&nbsp;Lab</span>
                    </div>
                    <p class="text-gray-300 mb-4">
                        Уебсайтове и маркетинг за бизнеса в България.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Навигация</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}#uslugi" class="text-gray-300 hover:text-white transition-colors">Услуги</a></li>
                        <li><a href="{{ route('home') }}#proces" class="text-gray-300 hover:text-white transition-colors">Процес</a></li>
                        <li><a href="{{ route('home') }}#paketi" class="text-gray-300 hover:text-white transition-colors">Пакети</a></li>
                        <li><a href="{{ route('home') }}#kontakt" class="text-gray-300 hover:text-white transition-colors">Контакт</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Контакти</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><i class="fas fa-envelope mr-2"></i>{{ config('creatium.contact.email') }}</li>
                        <li><i class="fas fa-phone mr-2"></i>{{ config('creatium.contact.phone') }}</li>
                        <li><i class="fas fa-location-dot mr-2"></i>{{ config('creatium.contact.city') }}, България</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Creatium Lab. Всички права запазени.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
        });
    </script>
</body>
</html>
