@extends('admin.layout')

@section('title', 'Категория: ' . $category->tr('name', 'bg'))

@php($input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500')

@section('content')
<form action="{{ route('admin.post-categories.update', $category) }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-4 max-w-xl">
    @csrf
    @method('PUT')
    <div>
        <label for="name_bg" class="block text-sm font-semibold text-gray-700 mb-1">Име на български</label>
        <input id="name_bg" name="name[bg]" value="{{ old('name.bg', $category->tr('name', 'bg')) }}" required maxlength="100" class="{{ $input }}">
    </div>
    <div>
        <label for="name_en" class="block text-sm font-semibold text-gray-700 mb-1">Име на английски</label>
        <input id="name_en" name="name[en]" value="{{ old('name.en', $category->tr('name', 'en')) }}" maxlength="100" class="{{ $input }}">
    </div>
    <p class="text-xs text-gray-500">Адресът на категорията се създава от името.</p>
    <div class="flex gap-3">
        <button type="submit" class="inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="floppy-disk" /> Запази</button>
        <a href="{{ route('admin.post-categories.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 font-semibold text-gray-700">Отказ</a>
    </div>
</form>
@endsection
