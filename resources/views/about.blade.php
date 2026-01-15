@extends('layouts.public')

@section('title', 'About Us')

@section('content')
<!-- Hero Section -->
<section class="hero-gradient text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">About Our System</h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-100">
                A comprehensive project management solution built for modern teams
            </p>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Mission</h2>
                <p class="text-lg text-gray-600 mb-6">
                    We believe that effective project management is the cornerstone of successful software development. 
                    Our system is designed to provide teams with the tools they need to organize, track, and collaborate 
                    on projects efficiently.
                </p>
                <p class="text-lg text-gray-600 mb-6">
                    Built with modern web technologies and best practices, our platform offers a seamless experience 
                    for managing projects of all sizes, from small startups to enterprise-level applications.
                </p>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-gray-700">User-Friendly Interface</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-gray-700">Real-time Updates</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-gray-700">Secure & Reliable</span>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg p-8">
                <div class="text-center">
                    <i class="fas fa-project-diagram text-6xl text-indigo-600 mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Project Management Excellence</h3>
                    <p class="text-gray-600">
                        Empowering teams to deliver exceptional results through streamlined project workflows and intelligent resource management.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Key Features</h2>
            <p class="text-lg text-gray-600">Everything you need to manage your projects effectively</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-lg card-hover">
                <div class="text-3xl text-indigo-600 mb-4">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Project Management</h3>
                <p class="text-gray-600">
                    Create, edit, and manage projects with detailed information including timelines, status, and team assignments.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg card-hover">
                <div class="text-3xl text-green-600 mb-4">
                    <i class="fas fa-tags"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Category Organization</h3>
                <p class="text-gray-600">
                    Organize projects into categories for better management and quick access to related work.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg card-hover">
                <div class="text-3xl text-purple-600 mb-4">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Technology Tracking</h3>
                <p class="text-gray-600">
                    Track technologies used in projects and maintain a comprehensive inventory of your tech stack.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg card-hover">
                <div class="text-3xl text-orange-600 mb-4">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Advanced Search</h3>
                <p class="text-gray-600">
                    Find projects quickly with our powerful search functionality across multiple criteria.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg card-hover">
                <div class="text-3xl text-blue-600 mb-4">
                    <i class="fas fa-file-upload"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">File Management</h3>
                <p class="text-gray-600">
                    Upload and manage project files with support for various document and image formats.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg card-hover">
                <div class="text-3xl text-red-600 mb-4">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Analytics Dashboard</h3>
                <p class="text-gray-600">
                    Get insights into project progress, team performance, and resource utilization.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Technology Stack Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Built With Modern Technology</h2>
            <p class="text-lg text-gray-600">Powered by the latest web development technologies</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
            <div class="text-center">
                <div class="text-5xl text-red-600 mb-4">
                    <i class="fab fa-laravel"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Laravel</h4>
                <p class="text-sm text-gray-600">PHP Framework</p>
            </div>
            
            <div class="text-center">
                <div class="text-5xl text-blue-600 mb-4">
                    <i class="fab fa-css3-alt"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Tailwind CSS</h4>
                <p class="text-sm text-gray-600">Styling Framework</p>
            </div>
            
            <div class="text-center">
                <div class="text-5xl text-yellow-500 mb-4">
                    <i class="fab fa-js"></i>
                </div>
                <h4 class="font-semibold text-gray-900">JavaScript</h4>
                <p class="text-sm text-gray-600">Frontend Logic</p>
            </div>
            
            <div class="text-center">
                <div class="text-5xl text-blue-500 mb-4">
                    <i class="fas fa-database"></i>
                </div>
                <h4 class="font-semibold text-gray-900">MySQL</h4>
                <p class="text-sm text-gray-600">Database</p>
            </div>
            
            <div class="text-center">
                <div class="text-5xl text-green-600 mb-4">
                    <i class="fab fa-html5"></i>
                </div>
                <h4 class="font-semibold text-gray-900">HTML5</h4>
                <p class="text-sm text-gray-600">Markup Language</p>
            </div>
            
            <div class="text-center">
                <div class="text-5xl text-purple-600 mb-4">
                    <i class="fab fa-font-awesome"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Font Awesome</h4>
                <p class="text-sm text-gray-600">Icon Library</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Development Team</h2>
            <p class="text-lg text-gray-600">Dedicated professionals committed to excellence</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center bg-white p-6 rounded-lg shadow-lg">
                <div class="w-24 h-24 bg-indigo-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-user text-3xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Lead Developer</h3>
                <p class="text-gray-600 mb-4">Full-stack developer with expertise in Laravel and modern web technologies.</p>
                <div class="flex justify-center space-x-3">
                    <a href="#" class="text-gray-400 hover:text-indigo-600"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-indigo-600"><i class="fab fa-github"></i></a>
                    <a href="#" class="text-gray-400 hover:text-indigo-600"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="text-center bg-white p-6 rounded-lg shadow-lg">
                <div class="w-24 h-24 bg-green-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-palette text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">UI/UX Designer</h3>
                <p class="text-gray-600 mb-4">Creative designer focused on creating intuitive and beautiful user interfaces.</p>
                <div class="flex justify-center space-x-3">
                    <a href="#" class="text-gray-400 hover:text-green-600"><i class="fab fa-dribbble"></i></a>
                    <a href="#" class="text-gray-400 hover:text-green-600"><i class="fab fa-behance"></i></a>
                    <a href="#" class="text-gray-400 hover:text-green-600"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="text-center bg-white p-6 rounded-lg shadow-lg">
                <div class="w-24 h-24 bg-purple-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-database text-3xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Database Expert</h3>
                <p class="text-gray-600 mb-4">Database architect ensuring optimal performance and data integrity.</p>
                <div class="flex justify-center space-x-3">
                    <a href="#" class="text-gray-400 hover:text-purple-600"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-purple-600"><i class="fab fa-github"></i></a>
                    <a href="#" class="text-gray-400 hover:text-purple-600"><i class="fab fa-stack-overflow"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
