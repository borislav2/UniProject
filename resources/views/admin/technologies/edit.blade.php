@extends('admin.layout')

@section('title', 'Edit Technology')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Edit Technology: {{ $technology->name }}</h2>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.technologies.update', $technology) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Technology Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $technology->name) }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="version" class="block text-sm font-medium text-gray-700 mb-2">Version</label>
                <input type="text" name="version" id="version" value="{{ old('version', $technology->version) }}" placeholder="e.g., 11.x, 3.12"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('version')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('admin.technologies.show', $technology) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Update Technology
            </button>
        </div>
    </form>
</div>
@endsection
