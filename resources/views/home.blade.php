@extends('layouts.public')

@section('title', 'Home')

@section('content')
<!-- Hero Section -->
<section class="hero-gradient text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Project Management System
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-100">
                Organize, track, and manage your projects with our comprehensive management solution
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <div class="text-center">
                        <p class="text-white mb-4">Добре дошли, {{ auth()->user()->name }}!</p>
                        <a href="{{ route('admin.dashboard') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                            <i class="fas fa-cog mr-2"></i>Admin Panel
                        </a>
                    </div>
                @endauth
                @guest
                    <a href="{{ route('login') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="{{ route('register') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                @endguest
                <a href="#features" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition-colors">
                    Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">System Overview</h2>
            <p class="text-lg text-gray-600">Real-time statistics from our project management system</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <div class="text-center p-6 bg-blue-50 rounded-lg card-hover">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $stats['total_projects'] }}</div>
                <div class="text-gray-600">Total Projects</div>
            </div>
            <div class="text-center p-6 bg-green-50 rounded-lg card-hover">
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $stats['completed_projects'] }}</div>
                <div class="text-gray-600">Completed</div>
            </div>
            <div class="text-center p-6 bg-orange-50 rounded-lg card-hover">
                <div class="text-3xl font-bold text-orange-600 mb-2">{{ $stats['in_progress_projects'] }}</div>
                <div class="text-gray-600">In Progress</div>
            </div>
            <div class="text-center p-6 bg-purple-50 rounded-lg card-hover">
                <div class="text-3xl font-bold text-purple-600 mb-2">{{ $stats['total_categories'] }}</div>
                <div class="text-gray-600">Categories</div>
            </div>
            <div class="text-center p-6 bg-indigo-50 rounded-lg card-hover">
                <div class="text-3xl font-bold text-indigo-600 mb-2">{{ $stats['total_technologies'] }}</div>
                <div class="text-gray-600">Technologies</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Projects Section -->
<section id="features" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Projects</h2>
            <p class="text-lg text-gray-600">Explore some of our recent and notable projects</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($featuredProjects as $project)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden card-hover">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 text-xs rounded-full 
                                @if($project->status == 'Completed') bg-green-100 text-green-800
                                @elseif($project->status == 'In Progress') bg-blue-100 text-blue-800
                                @elseif($project->status == 'Planning') bg-yellow-100 text-yellow-800
                                @elseif($project->status == 'On Hold') bg-orange-100 text-orange-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $project->status }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $project->category->name }}</span>
                        </div>
                        
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $project->name }}</h3>
                        <p class="text-gray-600 mb-4">{{ Str::limit($project->description, 100) }}</p>
                        
                        <div class="mb-4">
                            <div class="text-sm text-gray-500 mb-2">Technologies:</div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($project->technologies->take(3) as $technology)
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                        {{ $technology->name }}
                                    </span>
                                @endforeach
                                @if($project->technologies->count() > 3)
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                        +{{ $project->technologies->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span><i class="fas fa-user mr-1"></i>{{ $project->manager }}</span>
                            <span><i class="fas fa-calendar mr-1"></i>{{ $project->start_date->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">No projects available at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Project Categories</h2>
            <p class="text-lg text-gray-600">Browse projects by category</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            @foreach($categories as $category)
                <div class="text-center p-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg card-hover">
                    <div class="text-3xl mb-4">
                        @if($category->name == 'Web Development')
                            <i class="fas fa-globe text-blue-600"></i>
                        @elseif($category->name == 'Mobile Development')
                            <i class="fas fa-mobile-alt text-green-600"></i>
                        @elseif($category->name == 'Desktop Applications')
                            <i class="fas fa-desktop text-purple-600"></i>
                        @elseif($category->name == 'Data Science')
                            <i class="fas fa-chart-bar text-orange-600"></i>
                        @else
                            <i class="fas fa-cogs text-indigo-600"></i>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $category->projects_count }} projects</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Technologies Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Popular Technologies</h2>
            <p class="text-lg text-gray-600">Technologies used in our projects</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            @foreach($technologies as $technology)
                <div class="text-center p-4 bg-white rounded-lg shadow card-hover">
                    <div class="text-2xl mb-2">
                        @if(strtolower($technology->name) == 'laravel')
                            <i class="fab fa-laravel text-red-600"></i>
                        @elseif(strtolower($technology->name) == 'react')
                            <i class="fab fa-react text-blue-400"></i>
                        @elseif(strtolower($technology->name) == 'vue.js')
                            <i class="fab fa-vuejs text-green-600"></i>
                        @elseif(strtolower($technology->name) == 'angular')
                            <i class="fab fa-angular text-red-600"></i>
                        @elseif(strtolower($technology->name) == 'node.js')
                            <i class="fab fa-node text-green-600"></i>
                        @elseif(strtolower($technology->name) == 'python')
                            <i class="fab fa-python text-blue-600"></i>
                        @elseif(strtolower($technology->name) == 'docker')
                            <i class="fab fa-docker text-blue-500"></i>
                        @else
                            <i class="fas fa-cube text-indigo-600"></i>
                        @endif
                    </div>
                    <h4 class="text-sm font-medium text-gray-900">{{ $technology->name }}</h4>
                    <p class="text-xs text-gray-500">{{ $technology->projects_count }} projects</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-indigo-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        @if(auth()->check())
            <h2 class="text-3xl font-bold mb-4">Готови да управлявате проектите?</h2>
            <p class="text-xl mb-8 text-indigo-100">
                Достъпвайте административния панел за създаване и управление на проекти
            </p>
            <a href="{{ route('admin.dashboard') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                <i class="fas fa-cog mr-2"></i>Admin Panel
            </a>
        @else
            <h2 class="text-3xl font-bold mb-4">Готови да управлявате проектите?</h2>
            <p class="text-xl mb-8 text-indigo-100">
                Регистрирайте се за достъп до нашия административен панел
            </p>
            <a href="{{ route('register') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                <i class="fas fa-user-plus mr-2"></i>Регистрирайте се
            </a>
        @endif
    </div>
</section>
@endsection
