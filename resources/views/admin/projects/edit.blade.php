@extends('admin.layout')

@section('title', 'Edit Project')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Edit Project: {{ $project->name }}</h2>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="manager" class="block text-sm font-medium text-gray-700 mb-2">Manager *</label>
                <input type="text" name="manager" id="manager" value="{{ old('manager', $project->manager) }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('manager')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select name="category_id" id="category_id" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $project->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select name="status" id="status" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select status</option>
                    <option value="Planning" {{ old('status', $project->status) == 'Planning' ? 'selected' : '' }}>Planning</option>
                    <option value="In Progress" {{ old('status', $project->status) == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Completed" {{ old('status', $project->status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="On Hold" {{ old('status', $project->status) == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="Cancelled" {{ old('status', $project->status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $project->start_date->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('start_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('end_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
            <textarea name="description" id="description" rows="4" required
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $project->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">Адрес на сайта</label>
            <input type="url" name="website_url" id="website_url" placeholder="https://example.bg" value="{{ old('website_url', $project->website_url) }}"
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-500 mt-1">Показва се като връзка в публичното портфолио (/proekti).</p>
            @error('website_url')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label for="technologies" class="block text-sm font-medium text-gray-700 mb-2">Technologies</label>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($technologies as $technology)
                    <label class="flex items-center">
                        <input type="checkbox" name="technologies[]" value="{{ $technology->id }}" 
                               {{ in_array($technology->id, old('technologies', $project->technologies->pluck('id')->toArray())) ? 'checked' : '' }}
                               class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm">{{ $technology->name }} {{ $technology->version ? "({$technology->version})" : '' }}</span>
                    </label>
                @endforeach
            </div>
            @error('technologies')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_public" value="1" class="mr-2" {{ old('is_public', $project->is_public) ? 'checked' : '' }}>
                <span class="text-sm font-medium text-gray-700">Покажи в публичното портфолио (/proekti)</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">Показват се само завършени проекти. Ползвайте само с разрешение на клиента.</p>
        </div>

        <div class="mt-6">
            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Project File</label>
            @if($project->file_path)
                <div class="mb-2 p-3 bg-gray-50 rounded">
                    <p class="text-sm text-gray-600">Current file:</p>
                    <a href="{{ asset($project->file_path) }}" target="_blank" class="text-blue-600 hover:underline">
                        {{ basename($project->file_path) }}
                    </a>
                </div>
            @endif
            <input type="file" name="file" id="file" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif"
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-sm text-gray-500 mt-1">Allowed formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, GIF (Max: 10MB)</p>
            @error('file')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('admin.projects.show', $project) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Update Project
            </button>
        </div>
    </form>
</div>
@endsection
