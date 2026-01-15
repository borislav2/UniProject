@extends('admin.layout')

@section('title', $category->name)

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">{{ $category->name }}</h2>
    <div class="flex space-x-4">
        <a href="{{ route('admin.categories.edit', $category) }}" class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
            <i class="fas fa-edit mr-2"></i>Edit Category
        </a>
        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" {{ $category->projects->count() > 0 ? 'disabled' : '' }}>
                <i class="fas fa-trash mr-2"></i>Delete
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Details</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Description</h4>
                    <p class="text-gray-900">{{ $category->description ?? 'No description provided.' }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Total Projects</h4>
                        <p class="text-gray-900">{{ $category->projects->count() }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Created</h4>
                        <p class="text-gray-900">{{ $category->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Projects in this Category</h3>
            @if($category->projects->count() > 0)
                <div class="space-y-3">
                    @foreach($category->projects as $project)
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">
                                        <a href="{{ route('admin.projects.show', $project) }}" class="text-blue-600 hover:underline">
                                            {{ $project->name }}
                                        </a>
                                    </h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($project->description, 100) }}</p>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <span class="text-sm text-gray-500">Manager: {{ $project->manager }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($project->status == 'Completed') bg-green-100 text-green-800
                                            @elseif($project->status == 'In Progress') bg-blue-100 text-blue-800
                                            @elseif($project->status == 'Planning') bg-yellow-100 text-yellow-800
                                            @elseif($project->status == 'On Hold') bg-orange-100 text-orange-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $project->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No projects found in this category.</p>
            @endif
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Category Information</h3>
            <div class="space-y-3">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Category ID</h4>
                    <p class="text-gray-900">#{{ $category->id }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Last Updated</h4>
                    <p class="text-gray-900">{{ $category->updated_at->format('M d, Y H:i') }}</p>
                </div>
                @if($category->projects->count() > 0)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Project Status Breakdown</h4>
                        <div class="mt-2 space-y-1">
                            @foreach($category->projects->groupBy('status') as $status => $projects)
                                <div class="flex justify-between text-sm">
                                    <span>{{ $status }}</span>
                                    <span class="font-medium">{{ $projects->count() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('admin.categories.index') }}" class="text-blue-600 hover:underline">
        <i class="fas fa-arrow-left mr-2"></i>Back to Categories
    </a>
</div>
@endsection
