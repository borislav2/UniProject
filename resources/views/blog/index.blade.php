@extends('layouts.public')

@section('title', $category ? __('Новини за :category', ['category' => $category->trOrBg('name')]) : __('Новини за клиенти от Google и ChatGPT'))
@section('breadcrumb', $category ? $category->trOrBg('name') : '')
@section('meta_description', $category ? __('Статии от категория „:category“. Практични съвети за бизнеси, просто и с примери.', ['category' => $category->trOrBg('name')]) : __('Практични статии за собственици на бизнес. Как да ви намират в Google и ChatGPT, какво трябва да има сайтът и кои реклами и имейли се изплащат.'))

@section('content')
@include('partials/page-header', ['eyebrow' => $category ? __('Новини') : null, 'heading' => $category ? $category->trOrBg('name') : __('Новини'), 'sub' => __('Практични съвети как клиентите да ви намират в Google и ChatGPT и да ви пишат.')])

<section class="py-16 md:py-20 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- По-дълго описание под заглавието (собствениците, ноември 2026, текстът е с бизнес и маркетинг експерти). --}}
        @unless($category)
            <div class="mb-12 max-w-3xl space-y-4 text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                <p>{{ __('Пишем какво носи клиенти на един бизнес. Как да ви намират в Google и в ChatGPT, какво трябва да има сайтът, кога рекламите в Google и Meta се изплащат и как имейлите връщат клиенти. Има и бележки от eCommerce Summit и Digital Community Day и какво ще промени Google Shopping, когато стигне до България.') }}</p>
                <p>{{ __('Текстовете са за собственици на салони, ресторанти, сервизи, строителни фирми и магазини, без жаргон. Повечето статии завършват с нещо, което можете да направите още тази седмица.') }}</p>
            </div>
        @endunless
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
                @if(app()->getLocale() === 'en' && ! $category)
                    <h2 class="text-2xl font-bold mb-3 dark:text-white">Our articles are in Bulgarian for now</h2>
                    <p class="text-gray-600 dark:text-gray-300">Read them on the <a href="{{ lroute('blog.index', [], 'bg') }}" hreflang="bg" class="font-semibold text-brand-700 underline dark:text-brand-300">Bulgarian blog</a>, or write to us if you have a question about your website or Google.</p>
                @else
                    <h2 class="text-2xl font-bold mb-3 dark:text-white">{{ __('Скоро ще публикуваме първите статии') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Междувременно, ако имате въпрос за сайта или за Google, пишете ни.') }}</p>
                @endif
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

@endsection
