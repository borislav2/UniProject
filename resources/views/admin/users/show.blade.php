@extends('admin.layout')

@section('title', $user->name)

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
    <div class="flex space-x-4">
        <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">
            <x-icon name="edit" class="mr-2" />Edit User
        </a>
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                <x-icon name="trash" class="mr-2" />Delete
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">User Details</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Full Name</h4>
                        <p class="text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Email Address</h4>
                        <p class="text-gray-900">{{ $user->email }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">User ID</h4>
                        <p class="text-gray-900">#{{ $user->id }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Email Verified</h4>
                        <p class="text-gray-900">
                            @if($user->email_verified_at)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    {{ $user->email_verified_at->format('M d, Y') }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    Not Verified
                                </span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Account Created</h4>
                        <p class="text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Last Updated</h4>
                        <p class="text-gray-900">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Account Information</h3>
            <div class="space-y-3">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Account Status</h4>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                        Active
                    </span>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">User Type</h4>
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                        Administrator
                    </span>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Account Age</h4>
                    <p class="text-gray-900">{{ $user->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline">
        <x-icon name="arrow-left" class="mr-2" />Back to Users
    </a>
</div>
@endsection
