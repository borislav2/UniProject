@extends('admin.layout')

@section('title', 'Projects')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">Projects</h2>
    <div class="flex space-x-4">
        <form action="{{ route('admin.projects.search') }}" method="GET" class="flex">
            <input type="text" name="search" placeholder="Search projects..." 
                   value="{{ $search ?? '' }}" 
                   class="px-4 py-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-r hover:bg-blue-600">
                <i class="fas fa-search"></i>
            </button>
        </form>
        <a href="{{ route('admin.projects.create') }}" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            <i class="fas fa-plus mr-2"></i>New Project
        </a>
    </div>
</div>

@if(isset($search))
    <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
        Search results for: <strong>{{ $search }}</strong>
        <a href="{{ route('admin.projects.index') }}" class="float-right text-blue-600 hover:underline">Clear</a>
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($projects as $project)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('admin.projects.show', $project) }}" class="text-blue-600 hover:underline">
                                        {{ $project->name }}
                                    </a>
                                </div>
                                <div class="text-sm text-gray-500">{{ Str::limit($project->description, 50) }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $project->manager }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $project->category->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($project->status == 'Completed') bg-green-100 text-green-800
                                @elseif($project->status == 'In Progress') bg-blue-100 text-blue-800
                                @elseif($project->status == 'Planning') bg-yellow-100 text-yellow-800
                                @elseif($project->status == 'On Hold') bg-orange-100 text-orange-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $project->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $project->start_date->format('M d, Y') }}
                            @if($project->end_date)
                                - {{ $project->end_date->format('M d, Y') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.projects.show', $project) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No projects found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t">
        {{ $projects->links() }}
    </div>
</div>
@endsection
