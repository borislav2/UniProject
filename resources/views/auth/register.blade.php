@extends('layouts.public')

@section('title', 'Register')

@section('content')
<!-- Hero Section -->
<section class="hero-gradient text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">Регистрация</h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-100">
                Създайте нов акаунт за достъп до административния панел
            </p>
        </div>
    </div>
</section>

<!-- Register Form -->
<section class="py-16 bg-white">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <i class="fas fa-user-plus text-4xl text-green-600 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-900">Регистрация</h2>
                <p class="text-gray-600 mt-2">Създайте нов потребителски акаунт</p>
            </div>
            
            <form action="{{ route('register.post') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Име и фамилия</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" id="name" name="name" required
                               placeholder="Иван Иванов"
                               value="{{ old('name') }}"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Имейл адрес</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" required
                               placeholder="ivan@example.com"
                               value="{{ old('email') }}"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Парола</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                               placeholder="Минимум 8 символа"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Потвърдете паролата</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               placeholder="Повторете паролата"
                               class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('password_confirmation')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="terms" name="terms" required class="mr-2">
                    <label for="terms" class="text-sm text-gray-600">
                        Съгласявам с <a href="#" class="text-green-600 hover:text-green-500">условията за ползване</a>
                    </label>
                </div>
                
                <button type="submit" 
                        class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Създай акаунт
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Вече имате акаунт? 
                    <a href="{{ route('login') }}" class="text-green-600 hover:text-green-500 font-medium">Влезте тук</a>
                </p>
            </div>
            
            <div class="mt-8 p-4 bg-green-50 rounded-lg">
                <h4 class="text-sm font-semibold text-green-900 mb-2">Предимства на регистрацията:</h4>
                <ul class="text-sm text-green-700 space-y-1">
                    <li><i class="fas fa-check text-green-600 mr-2"></i>Пълен достъп до административния панел</li>
                    <li><i class="fas fa-check text-green-600 mr-2"></i>Управление на проекти и ресурси</li>
                    <li><i class="fas fa-check text-green-600 mr-2"></i>Качване на файлове и документи</li>
                    <li><i class="fas fa-check text-green-600 mr-2"></i>Разширени функции за търсене</li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endsection
