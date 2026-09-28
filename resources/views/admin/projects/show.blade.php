@extends('admin.layout')

@section('title', $project->name)

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
        {{ $project->name }}
        @if($project->source === 'website')
            <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">Запитване от сайта</span>
        @endif
    </h2>
    <div class="flex space-x-4">
        <a href="{{ route('admin.projects.edit', $project) }}" class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
            <i class="fas fa-edit mr-2"></i>Edit Project
        </a>
        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this project?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                <i class="fas fa-trash mr-2"></i>Delete
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Project Details</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Description</h4>
                    <p class="text-gray-900">{{ $project->description }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Manager</h4>
                        <p class="text-gray-900">{{ $project->manager }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Category</h4>
                        <p class="text-gray-900">{{ $project->category->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Status</h4>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($project->status == 'Completed') bg-green-100 text-green-800
                            @elseif($project->status == 'In Progress') bg-blue-100 text-blue-800
                            @elseif($project->status == 'Planning') bg-yellow-100 text-yellow-800
                            @elseif($project->status == 'On Hold') bg-orange-100 text-orange-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $project->status }}
                        </span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Duration</h4>
                        <p class="text-gray-900">
                            {{ $project->start_date->format('M d, Y') }}
                            @if($project->end_date)
                                - {{ $project->end_date->format('M d, Y') }}
                                ({{ $project->start_date->diffInDays($project->end_date) }} days)
                            @else
                                - Present ({{ $project->start_date->diffInDays(now()) }} days)
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Technologies Used</h3>
            @if($project->technologies->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($project->technologies as $technology)
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                            {{ $technology->name }} {{ $technology->version ? "({$technology->version})" : '' }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No technologies assigned to this project.</p>
            @endif
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Project Information</h3>
            <div class="space-y-3">
                @if($project->client_email || $project->client_phone)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Контакт с клиента</h4>
                        @if($project->client_email)
                            <p class="text-gray-900"><a href="mailto:{{ $project->client_email }}" class="text-blue-600 hover:underline">{{ $project->client_email }}</a></p>
                        @endif
                        @if($project->client_phone)
                            <p class="text-gray-900"><a href="tel:{{ $project->client_phone }}" class="text-blue-600 hover:underline">{{ $project->client_phone }}</a></p>
                        @endif
                    </div>
                @endif
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Created</h4>
                    <p class="text-gray-900">{{ $project->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Last Updated</h4>
                    <p class="text-gray-900">{{ $project->updated_at->format('M d, Y H:i') }}</p>
                </div>
                @if($project->file_path)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Project File</h4>
                        <a href="{{ asset($project->file_path) }}" target="_blank" 
                           class="text-blue-600 hover:underline flex items-center">
                            <i class="fas fa-file mr-2"></i>
                            {{ basename($project->file_path) }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('admin.projects.index') }}" class="text-blue-600 hover:underline">
        <i class="fas fa-arrow-left mr-2"></i>Back to Projects
    </a>
</div>
@endsection
