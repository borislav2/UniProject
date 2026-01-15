<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Project Management System') - PM System</title>
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
                        <i class="fas fa-project-diagram text-2xl text-indigo-600 mr-3"></i>
                        <span class="font-bold text-xl text-gray-800">Project Manager</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }} px-3 py-2 text-sm font-medium transition-colors">
                        Home
                    </a>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }} px-3 py-2 text-sm font-medium transition-colors">
                        About
                    </a>
                    <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-700 hover:text-indigo-600' }} px-3 py-2 text-sm font-medium transition-colors">
                        Contact
                    </a>
                    
                    @if(auth()->check())
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">
                                <i class="fas fa-user-circle mr-1"></i>
                                {{ auth()->user()->name }}
                                @if(auth()->user()->primary_role)
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full" style="background-color: {{ auth()->user()->roles->first()->color }}20; color: white;">
                                        {{ auth()->user()->roles->first()->name }}
                                    </span>
                                @endif
                            </span>
                            <a href="{{ route('admin.dashboard') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-cog mr-2"></i>Admin Panel
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    @endif
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
                <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700 hover:text-indigo-600">Home</a>
                <a href="{{ route('about') }}" class="block px-3 py-2 text-gray-700 hover:text-indigo-600">About</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 text-gray-700 hover:text-indigo-600">Contact</a>
                
                @if(auth()->check())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 bg-indigo-600 text-white rounded mt-2">Admin Panel</a>
                    <form action="{{ route('logout') }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 bg-gray-600 text-white rounded">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 bg-indigo-600 text-white rounded mt-2">Login</a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 bg-green-600 text-white rounded mt-2">Register</a>
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
                        <i class="fas fa-project-diagram text-2xl text-indigo-400 mr-3"></i>
                        <span class="font-bold text-xl">Project Manager</span>
                    </div>
                    <p class="text-gray-300 mb-4">
                        A comprehensive project management system for tracking projects, technologies, and categories. 
                        Built with Laravel and modern web technologies.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-github text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition-colors">About</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-300 hover:text-white transition-colors">Contact</a></li>
                        <li><a href="{{ route('admin.dashboard') }}" class="text-gray-300 hover:text-white transition-colors">Admin Panel</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">System Info</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><i class="fas fa-check-circle text-green-400 mr-2"></i>Laravel Framework</li>
                        <li><i class="fas fa-check-circle text-green-400 mr-2"></i>Modern UI Design</li>
                        <li><i class="fas fa-check-circle text-green-400 mr-2"></i>Responsive Layout</li>
                        <li><i class="fas fa-check-circle text-green-400 mr-2"></i>Admin Panel</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Project Management System. All rights reserved.</p>
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
