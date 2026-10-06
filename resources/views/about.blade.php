@extends('layouts.public')

@section('title', __('За нас'))
@section('meta_description', __('Creatium Lab сме двама: единият прави сайтовете, другият се грижи хората да ги намират в Google. Работим с малки бизнеси в България.'))

@section('content')
@include('partials/page-header', ['heading' => __('За нас'), 'sub' => $about['subtitle']])

<section class="py-16 bg-white dark:bg-ink-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 text-lg text-gray-700 dark:text-gray-200">
        @foreach($about['paragraphs'] as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach
    </div>
</section>

<section class="py-16 md:py-20 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-10 dark:text-white">{{ __('Екипът') }}</h2>
        <div class="grid md:grid-cols-2 gap-8">
            @foreach($team as $member)
                <div class="bg-white rounded-2xl border border-gray-200 p-7 card-hover reveal dark:bg-white/[0.03] dark:border-white/10">
                    @if(!empty($member['name']))
                        <h3 class="text-xl font-bold dark:text-white">{{ $member['name'] }}</h3>
                        <p class="text-brand-600 font-semibold mb-3 dark:text-brand-300">{{ $member['role'] }}</p>
                    @else
                        <h3 class="text-xl font-bold text-brand-600 mb-3 dark:text-brand-300">{{ $member['role'] }}</h3>
                    @endif
                    <p class="text-gray-600 dark:text-gray-300">{{ $member['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-white dark:bg-ink-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-10 dark:text-white">{{ __('Какво можете да очаквате') }}</h2>
        <div class="grid sm:grid-cols-3 gap-8 text-center">
            <div><x-icon name="reply" class="text-brand-600 text-2xl mb-3 dark:text-brand-300" /><h3 class="font-semibold mb-1 dark:text-white">{{ __('Отговаряме бързо') }}</h3><p class="text-sm text-gray-600 dark:text-gray-300">{{ __('До един работен ден, по телефона или по имейл.') }}</p></div>
            <div><x-icon name="tag" class="text-brand-600 text-2xl mb-3 dark:text-brand-300" /><h3 class="font-semibold mb-1 dark:text-white">{{ __('Цената е ясна предварително') }}</h3><p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Знаете колко ще струва, преди да започнем работа.') }}</p></div>
            <div><x-icon name="screwdriver-wrench" class="text-brand-600 text-2xl mb-3 dark:text-brand-300" /><h3 class="font-semibold mb-1 dark:text-white">{{ __('Оставаме и след това') }}</h3><p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Ако трябва да смените нещо или нещо спре да работи, пишете ни.') }}</p></div>
        </div>
    </div>
</section>

@endsection
