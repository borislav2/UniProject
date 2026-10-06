@extends('layouts.public')

@section('title', __('Проекти'))
@section('meta_description', __('Избрани проекти на Creatium Lab: уебсайтове, онлайн магазини и SEO за български бизнеси.'))

@section('content')
@include('partials/page-header', ['heading' => __('Проекти'), 'sub' => __('Разгледайте работата ни')])

<section class="py-16 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($projects->isEmpty())
            <div class="max-w-xl mx-auto text-center py-8">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-brand-50 flex items-center justify-center dark:bg-white/10">
                    <x-icon name="rocket" class="text-brand-600 text-xl dark:text-brand-300" />
                </div>
                <h2 class="text-2xl font-bold mb-3 dark:text-white">{{ __('Тук скоро ще има проекти') }}</h2>
                <p class="text-gray-600 mb-6 dark:text-gray-300">{{ __('Ако искате вашият да е един от тях, пишете ни.') }}</p>
                <a href="{{ lroute('contact') }}" class="inline-flex items-center gap-2 bg-brand-950 text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">{{ __('Пишете ни') }} <x-icon name="arrow-right" class="text-xs" /></a>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($projects as $project)
                    <article class="border border-gray-200 rounded-2xl p-7 card-hover bg-white reveal dark:border-white/10 dark:bg-white/[0.03]">
                        <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300">{{ $project->category->name }}</span>
                        <h2 class="text-xl font-bold text-brand-950 mt-2 mb-2 dark:text-white">{{ $project->name }}</h2>
                        <p class="text-gray-600 mb-4 dark:text-gray-300">{{ $project->description }}</p>
                        @if($project->website_url)
                            <a href="{{ $project->website_url }}" target="_blank" rel="noopener" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">
                                {{ __('Посетете сайта') }}: {{ preg_replace('/^www\./', '', (string) parse_url($project->website_url, PHP_URL_HOST)) }} <x-icon name="arrow-up-right-from-square" class="text-xs" />
                            </a>
                        @endif
                        @if($project->technologies->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($project->technologies as $technology)
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded dark:bg-white/10 dark:text-gray-200">{{ $technology->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

@endsection
