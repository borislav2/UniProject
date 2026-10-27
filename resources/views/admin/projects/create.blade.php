@extends('admin.layout')

@section('title', 'Нов проект')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Нов проект</h2>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Име на проекта *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="manager" class="block text-sm font-medium text-gray-700 mb-2">Отговорник *</label>
                <input type="text" name="manager" id="manager" value="{{ old('manager') }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('manager')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Категория *</label>
                <select name="category_id" id="category_id" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Изберете категория</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Статус *</label>
                <select name="status" id="status" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(\App\Models\Project::STATUSES as $value => [$label])
                        <option value="{{ $value }}" @selected(old('status', $project->status ?? 'Planning') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Начало *</label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('start_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Край</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('end_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Описание *</label>
            <textarea name="description" id="description" rows="4" required
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="client_phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон на клиента</label>
                <input type="tel" name="client_phone" id="client_phone" value="{{ old('client_phone', $project->client_phone ?? '') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('client_phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="client_email" class="block text-sm font-medium text-gray-700 mb-2">Имейл на клиента</label>
                <input type="email" name="client_email" id="client_email" value="{{ old('client_email', $project->client_email ?? '') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('client_email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Абонамент: попълнете пакет и месечна такса, за да е клиентът в „Абонаменти“ (плащания, наблюдение на сайта, отчет). --}}
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <div>
                <label for="package" class="block text-sm font-medium text-gray-700 mb-2">Пакет (абонамент)</label>
                <input type="text" name="package" id="package" list="package-names" value="{{ old('package', $project->package ?? '') }}" maxlength="80" placeholder="напр. Стартов двигател"
                       class="w-full px-3 py-2 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <datalist id="package-names">
                    @foreach(collect(site('plans')['items'] ?? [])->pluck('name')->filter() as $planName)<option value="{{ $planName }}">@endforeach
                </datalist>
            </div>
            <div>
                <label for="monthly_fee" class="block text-sm font-medium text-gray-700 mb-2">Месечна такса (€)</label>
                <input type="number" name="monthly_fee" id="monthly_fee" min="0" step="0.01" value="{{ old('monthly_fee', $project->monthly_fee ?? '') }}"
                       class="w-full px-3 py-2 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('monthly_fee')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="billing_day" class="block text-sm font-medium text-gray-700 mb-2">Плаща на (ден от месеца)</label>
                <input type="number" name="billing_day" id="billing_day" min="1" max="31" value="{{ old('billing_day', $project->billing_day ?? '') }}" placeholder="1"
                       class="w-full px-3 py-2 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('billing_day')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <p class="md:col-span-3 -mt-3 text-xs text-gray-500">Празна такса = не е на абонамент. Със сайт в „Адрес на сайта“ сайтът се наблюдава автоматично.</p>
        </div>

        <div class="mt-6">
            <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">Адрес на сайта</label>
            <input type="url" name="website_url" id="website_url" placeholder="https://example.bg" value="{{ old('website_url') }}"
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-500 mt-1">Показва се като връзка в публичното портфолио (/proekti).</p>
            @error('website_url')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label for="technologies" class="block text-sm font-medium text-gray-700 mb-2">Технологии</label>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($technologies as $technology)
                    <label class="flex items-center">
                        <input type="checkbox" name="technologies[]" value="{{ $technology->id }}" 
                               {{ in_array($technology->id, old('technologies', [])) ? 'checked' : '' }}
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
                <input type="checkbox" name="is_public" value="1" class="mr-2" {{ old('is_public') ? 'checked' : '' }}>
                <span class="text-sm font-medium text-gray-700">Покажи в публичното портфолио (/proekti)</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">Показват се само завършени проекти. Ползвайте само с разрешение на клиента.</p>
        </div>

        <div class="mt-6">
            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Файл</label>
            <input type="file" name="file" id="file" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif"
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-sm text-gray-500 mt-1">PDF, DOC, DOCX, TXT, JPG, PNG, GIF до 10 MB</p>
            @error('file')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Отказ
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Създай проекта
            </button>
        </div>
    </form>
</div>
@endsection
