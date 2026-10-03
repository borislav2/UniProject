@extends('layouts.public')

@section('title', __('Сесията е изтекла'))

@section('content')
<section class="py-24 text-center bg-white dark:bg-ink-950">
    <div class="max-w-xl mx-auto px-4">
        <h1 class="text-2xl font-bold mb-3 dark:text-white">{{ __('Сесията е изтекла') }}</h1>
        <p class="text-gray-600 mb-8 dark:text-gray-300">{{ __('Формата е стояла отворена твърде дълго. Върнете се назад, презаредете страницата и опитайте отново.') }}</p>
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 bg-brand-950 text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand-800 dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">{{ __('Назад') }}</a>
    </div>
</section>
@endsection
