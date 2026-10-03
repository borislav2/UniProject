    @extends('layouts.public')

    @section('title', 'Login')

    @section('content')
    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">Вход в системата</h1>
                <p class="text-xl md:text-2xl mb-8 text-gray-100">
                    Влезте в административния панел за управление на проекти
                </p>
            </div>
        </div>
    </section>

    <!-- Login Form -->
    <section class="py-16 bg-white dark:bg-ink-950">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-8 dark:bg-ink-900 dark:ring-1 dark:ring-white/10">
                <div class="text-center mb-8">
                    <x-icon name="sign-in-alt" class="text-4xl text-brand-600 mb-4" />
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Вход</h2>
                    <p class="text-gray-600 mt-2 dark:text-gray-300">Въведете вашите данни за достъп</p>
                </div>
                
                <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Имейл адрес</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-icon name="envelope" class="text-gray-400" />
                            </div>
                            <input type="email" id="email" name="email" required autocomplete="username"
                                placeholder="name@creatiumlab.com" value="{{ old('email') }}"
                                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-white/5 dark:border-white/15 dark:text-white">
                        </div>
                        @error('email')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">Парола</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-icon name="lock" class="text-gray-400" />
                            </div>
                            <input type="password" id="password" name="password" required
                                placeholder="••••••••"
                                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-white/5 dark:border-white/15 dark:text-white">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="mr-2">
                            <label for="remember" class="text-sm text-gray-600 dark:text-gray-300">Запомни ме</label>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-brand-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-brand-700 transition-colors">
                        <x-icon name="sign-in-alt" class="mr-2" />Вход в системата
                    </button>
                </form>
            </div>
        </div>
    </section>
    @endsection
