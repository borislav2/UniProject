@extends('layouts.public')

@section('title', $category ? __('Блог: :category', ['category' => $category->trOrBg('name')]) : __('Блог'))
@section('meta_description', __('Статии за сайтове, SEO, Google Business и имейл кампании за малки и средни бизнеси. Пишем просто и с примери.'))

@section('content')
@include('partials/page-header', ['eyebrow' => $category ? __('Блог') : null, 'heading' => $category ? $category->trOrBg('name') : __('Блог'), 'sub' => __('Тук ще има полезни неща за сайтове и онлайн маркетинг.')])

<section class="py-16 md:py-20 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($categories->count() > 1)
            <nav class="mb-10 flex flex-wrap gap-2" aria-label="{{ __('Категории') }}">
                <a href="{{ lroute('blog.index') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold border transition-colors {{ $category ? 'border-gray-200 text-gray-700 hover:border-brand-300 dark:border-white/15 dark:text-gray-200' : 'border-brand-950 bg-brand-950 text-white dark:bg-white dark:text-brand-950 dark:border-white' }}" @unless($category) aria-current="page" @endunless>{{ __('Всички') }}</a>
                @foreach($categories as $item)
                    @php($active = $category && $category->is($item))
                    <a href="{{ lroute('blog.category', ['slug' => $item->tr('slug')]) }}" class="rounded-full px-4 py-1.5 text-sm font-semibold border transition-colors {{ $active ? 'border-brand-950 bg-brand-950 text-white dark:bg-white dark:text-brand-950 dark:border-white' : 'border-gray-200 text-gray-700 hover:border-brand-300 dark:border-white/15 dark:text-gray-200' }}" @if($active) aria-current="page" @endif>{{ $item->trOrBg('name') }}</a>
                @endforeach
            </nav>
        @endif

        @if($posts->isEmpty())
            <div class="max-w-xl mx-auto text-center py-8">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-brand-50 flex items-center justify-center dark:bg-white/10">
                    <x-icon name="file" class="text-brand-600 text-xl dark:text-brand-300" />
                </div>
                <h2 class="text-2xl font-bold mb-3 dark:text-white">{{ __('Скоро ще публикуваме първите статии') }}</h2>
                <p class="text-gray-600 dark:text-gray-300">{{ __('Междувременно, ако имате въпрос за сайта или за Google, пишете ни.') }}</p>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    @include('blog._card')
                @endforeach
            </div>

            @if($posts->hasPages())
                <nav class="mt-12 flex items-center justify-between gap-4" aria-label="{{ __('Страници') }}">
                    @if($posts->onFirstPage())
                        <span></span>
                    @else
                        <a href="{{ $posts->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 font-semibold text-brand-950 hover:border-brand-300 dark:border-white/15 dark:text-white"><x-icon name="arrow-left" class="text-xs" /> {{ __('По-нови') }}</a>
                    @endif
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Страница :current от :last', ['current' => $posts->currentPage(), 'last' => $posts->lastPage()]) }}</span>
                    @if($posts->hasMorePages())
                        <a href="{{ $posts->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 font-semibold text-brand-950 hover:border-brand-300 dark:border-white/15 dark:text-white">{{ __('По-стари') }} <x-icon name="arrow-right" class="text-xs" /></a>
                    @else
                        <span></span>
                    @endif
                </nav>
            @endif
        @endif
    </div>
</section>

@include('partials/cta')
@endsection
