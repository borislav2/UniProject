@extends('layouts.public')

@section('title', __('Страницата не е намерена'))

@section('content')
<section class="py-24 text-center bg-white dark:bg-ink-950">
    <div class="max-w-xl mx-auto px-4">
        <p class="text-6xl font-extrabold text-brand-600 mb-4 dark:text-brand-300">404</p>
        <h1 class="text-2xl font-bold mb-3 dark:text-white">{{ __('Страницата не е намерена') }}</h1>
        <p class="text-gray-600 mb-8 dark:text-gray-300">{{ __('Възможно е адресът да е променен или страницата да не съществува.') }}</p>
        <a href="{{ lroute('home') }}" class="inline-flex items-center gap-2 bg-brand-950 text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand-800 dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">{{ __('Към началото') }}</a>
    </div>
</section>
@endsection
