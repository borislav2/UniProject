@extends('admin.layout')

@section('title', 'Категории на блога')

@php($input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500')

@section('content')
<div class="grid lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr><th class="px-6 py-3">Български</th><th class="px-6 py-3">English</th><th class="px-6 py-3">Статии</th><th class="px-6 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $category->tr('name', 'bg') }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $category->tr('name', 'en') ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $category->posts_count }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('admin.post-categories.edit', $category) }}" class="text-brand-700 font-semibold mr-3">Редактирай</a>
                            <form action="{{ route('admin.post-categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Да изтрия ли категорията? Статиите остават, но без категория.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" aria-label="Изтрий"><x-icon name="trash" /></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">Още няма категории.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form action="{{ route('admin.post-categories.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        <h3 class="text-lg font-bold text-gray-900">Нова категория</h3>
        <div>
            <label for="name_bg" class="block text-sm font-semibold text-gray-700 mb-1">Име на български</label>
            <input id="name_bg" name="name[bg]" value="{{ old('name.bg') }}" required maxlength="100" class="{{ $input }}">
        </div>
        <div>
            <label for="name_en" class="block text-sm font-semibold text-gray-700 mb-1">Име на английски <span class="font-normal text-gray-500">(по желание)</span></label>
            <input id="name_en" name="name[en]" value="{{ old('name.en') }}" maxlength="100" class="{{ $input }}">
        </div>
        <button type="submit" class="inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="plus" /> Добави</button>
    </form>
</div>
@endsection
