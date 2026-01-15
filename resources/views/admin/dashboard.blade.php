@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-500 rounded-full text-white">
                <i class="fas fa-project-diagram text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Total Projects</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_projects'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-500 rounded-full text-white">
                <i class="fas fa-tags text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Categories</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_categories'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-purple-500 rounded-full text-white">
                <i class="fas fa-cogs text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Technologies</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_technologies'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-500 rounded-full text-white">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Completed</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_projects'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-orange-500 rounded-full text-white">
                <i class="fas fa-spinner text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">In Progress</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['in_progress_projects'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 bg-indigo-500 rounded-full text-white">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Users</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Recent Projects</h3>
    </div>
    <div class="p-6">
        @if($recentProjects->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-4">Name</th>
                            <th class="text-left py-2 px-4">Manager</th>
                            <th class="text-left py-2 px-4">Category</th>
                            <th class="text-left py-2 px-4">Status</th>
                            <th class="text-left py-2 px-4">Technologies</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentProjects as $project)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-4">
                                    <a href="{{ route('admin.projects.show', $project) }}" class="text-blue-600 hover:underline">
                                        {{ $project->name }}
                                    </a>
                                </td>
                                <td class="py-2 px-4">{{ $project->manager }}</td>
                                <td class="py-2 px-4">{{ $project->category->name }}</td>
                                <td class="py-2 px-4">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($project->status == 'Completed') bg-green-100 text-green-800
                                        @elseif($project->status == 'In Progress') bg-blue-100 text-blue-800
                                        @elseif($project->status == 'Planning') bg-yellow-100 text-yellow-800
                                        @elseif($project->status == 'On Hold') bg-orange-100 text-orange-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ $project->status }}
                                    </span>
                                </td>
                                <td class="py-2 px-4">
                                    {{ $project->technologies->pluck('name')->implode(', ') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500">No projects found.</p>
        @endif
    </div>
</div>
@endsection
