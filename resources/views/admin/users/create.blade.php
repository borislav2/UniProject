@extends('admin.layout')

@section('title', 'Нов потребител')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Нов потребител</h2>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Име *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Имейл *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Парола *</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Паролата отново *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('password_confirmation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            @php $current = 'all'; @endphp
            <div>
                <label for="profile" class="block text-sm font-medium text-gray-700 mb-2">Профил в админа</label>
                <select name="profile" id="profile" class="w-full px-3 py-2 border rounded-lg" data-profile-select>
                    @foreach(\App\Support\Admin\Profiles::PROFILES as $key => $p)
                        <option value="{{ $key }}" @selected(old('profile', $current) === $key)>{{ $p['label'] }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500" data-profile-hint>{{ \App\Support\Admin\Profiles::description(old('profile', $current)) }}</p>
                @error('profile')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                <script>
                    (function () {
                        var d = @json(collect(\App\Support\Admin\Profiles::PROFILES)->map(fn ($p) => $p['description']));
                        var s = document.querySelector('[data-profile-select]'), h = document.querySelector('[data-profile-hint]');
                        s.addEventListener('change', function () { h.textContent = d[s.value] || ''; });
                    })();
                </script>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Отказ
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Създай
            </button>
        </div>
    </form>
</div>
@endsection
