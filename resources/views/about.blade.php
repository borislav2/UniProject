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
                @php
                    $initials = collect(preg_split('/\s+/u', trim((string) ($member['name'] ?? '')), -1, PREG_SPLIT_NO_EMPTY))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                    $linkedin = str_starts_with((string) ($member['linkedin'] ?? ''), 'https://') ? $member['linkedin'] : null;
                @endphp
                <div class="flex flex-col bg-white rounded-2xl border border-gray-200 p-7 card-hover reveal dark:bg-white/[0.03] dark:border-white/10">
                    @if(!empty($member['name']))
                        <div class="flex items-center gap-4 mb-4">
                            @if(!empty($member['image']))
                                <img src="{{ asset($member['image']) }}" alt="{{ $member['name'] }}" width="72" height="72" loading="lazy" class="w-[4.5rem] h-[4.5rem] shrink-0 rounded-full object-cover ring-2 ring-brand-100 dark:ring-white/15">
                            @else
                                <span class="w-[4.5rem] h-[4.5rem] shrink-0 rounded-full bg-brand-gradient flex items-center justify-center text-2xl font-extrabold text-white shadow-lg shadow-brand-900/20" aria-hidden="true">{{ $initials }}</span>
                            @endif
                            <div>
                                <h3 class="text-xl font-bold dark:text-white">{{ $member['name'] }}</h3>
                                <p class="text-brand-600 font-semibold dark:text-brand-300">{{ $member['role'] }}</p>
                            </div>
                        </div>
                    @else
                        <h3 class="text-xl font-bold text-brand-600 mb-3 dark:text-brand-300">{{ $member['role'] }}</h3>
                    @endif
                    <p class="font-medium text-brand-950 dark:text-white">{{ $member['description'] }}</p>
                    @if(!empty($member['bio']))
                        <div class="mt-4 space-y-3 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            @foreach((array) $member['bio'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($member['skills']))
                        <ul class="mt-5 flex flex-wrap gap-2">
                            @foreach((array) $member['skills'] as $skill)
                                <li class="rounded-full border border-gray-100 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">{{ $skill }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($linkedin)
                        <a href="{{ $linkedin }}" target="_blank" rel="noopener" class="mt-auto pt-6 inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">
                            {{ __('Профил в LinkedIn') }} <x-icon name="arrow-up-right-from-square" class="text-xs" />
                        </a>
                    @endif
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
