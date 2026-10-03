@extends('admin.layout')

@section('title', 'Моят профил')

@php($input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500')

@section('content')
<form action="{{ route('admin.profile.update') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-5 max-w-xl">
    @csrf
    @method('PUT')
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Име</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="{{ $input }}">
    </div>
    <div>
        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Имейл (за вход)</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="{{ $input }}">
    </div>

    <fieldset class="border-t border-gray-200 pt-5 space-y-4">
        <legend class="text-base font-bold text-gray-900">Смяна на паролата</legend>
        <p class="text-sm text-gray-500">Оставете празно, ако не я сменяте. Новата парола трябва да е поне 10 знака.</p>
        <div>
            <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">Текуща парола</label>
            <input id="current_password" type="password" name="current_password" autocomplete="current-password" class="{{ $input }}">
        </div>
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Нова парола</label>
            <input id="password" type="password" name="password" autocomplete="new-password" class="{{ $input }}">
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Повторете новата парола</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}">
        </div>
    </fieldset>

    <button type="submit" class="inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="floppy-disk" /> Запази</button>
</form>
@endsection
