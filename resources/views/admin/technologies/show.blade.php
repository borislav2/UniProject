@extends('admin.layout')

@section('title', $technology->name)

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">{{ $technology->name }}</h2>
    <div class="flex space-x-4">
        <a href="{{ route('admin.technologies.edit', $technology) }}" class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
            <x-icon name="edit" class="mr-2" />Edit Technology
        </a>
        <form action="{{ route('admin.technologies.destroy', $technology) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this technology?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" {{ $technology->projects->count() > 0 ? 'disabled' : '' }}>
                <x-icon name="trash" class="mr-2" />Delete
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Technology Details</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Technology Name</h4>
                        <p class="text-gray-900">{{ $technology->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Version</h4>
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                            {{ $technology->version ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Total Projects</h4>
                        <p class="text-gray-900">{{ $technology->projects->count() }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Created</h4>
                        <p class="text-gray-900">{{ $technology->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Projects using this Technology</h3>
            @if($technology->projects->count() > 0)
                <div class="space-y-3">
                    @foreach($technology->projects as $project)
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
                                        <span class="text-sm text-gray-500">Category: {{ $project->category->name }}</span>
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
                <p class="text-gray-500">No projects found using this technology.</p>
            @endif
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Technology Information</h3>
            <div class="space-y-3">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Technology ID</h4>
                    <p class="text-gray-900">#{{ $technology->id }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Last Updated</h4>
                    <p class="text-gray-900">{{ $technology->updated_at->format('M d, Y H:i') }}</p>
                </div>
                @if($technology->projects->count() > 0)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Project Status Breakdown</h4>
                        <div class="mt-2 space-y-1">
                            @foreach($technology->projects->groupBy('status') as $status => $projects)
                                <div class="flex justify-between text-sm">
                                    <span>{{ $status }}</span>
                                    <span class="font-medium">{{ $projects->count() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Categories Used</h4>
                        <div class="mt-2 space-y-1">
                            @foreach($technology->projects->groupBy('category.name') as $category => $projects)
                                <div class="flex justify-between text-sm">
                                    <span>{{ $category }}</span>
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
    <a href="{{ route('admin.technologies.index') }}" class="text-blue-600 hover:underline">
        <x-icon name="arrow-left" class="mr-2" />Back to Technologies
    </a>
</div>
@endsection
