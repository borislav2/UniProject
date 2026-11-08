@extends('layouts.public')

@section('title', __('Проекти и концепции за сайтове за ресторант, салон и автосервиз'))
@section('meta_description', __('Концепции за сайтове за ресторант, салон, зъболекар, автосервиз, строителна фирма и онлайн магазин. Вижте как изглежда сайт, който носи запитвания.'))

@section('content')
@include('partials/page-header', ['heading' => __('Проекти'), 'sub' => __('Разгледайте работата ни')])

@if($projects->isNotEmpty() || ! empty($demos))
<section class="py-16 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Един списък: първо истинските проекти, после концепциите; картите са еднакви, само концепциите носят етикет „Концепция“. --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" data-portfolio-grid>
            @foreach($projects as $project)
                <article class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white card-hover reveal dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 3) * 100 }}ms">
                    <div class="relative aspect-[8/5] overflow-hidden bg-gray-100 dark:bg-white/5">
                        @if($project->screenshot)
                            <img src="{{ asset($project->screenshot) }}" alt="{{ $project->name }}" width="960" height="600" loading="lazy" class="h-full w-full object-cover object-top transition-transform duration-500 group-hover:scale-[1.03]">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100 dark:from-white/5 dark:to-white/10"><x-icon name="rocket" class="text-3xl text-brand-300 dark:text-brand-300/60" /></div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300">{{ $project->category->name }}</span>
                        <h2 class="mt-2 text-xl font-bold text-brand-950 dark:text-white">{{ $project->name }}</h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">{{ $project->description }}</p>
                        @if($project->technologies->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-1">
                                @foreach($project->technologies as $technology)
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded dark:bg-white/10 dark:text-gray-200">{{ $technology->name }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if($project->website_url)
                            <a href="{{ $project->website_url }}" target="_blank" rel="noopener" class="mt-auto pt-5 inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">
                                {{ __('Посетете сайта') }}: {{ preg_replace('/^www\./', '', (string) parse_url($project->website_url, PHP_URL_HOST)) }} <x-icon name="arrow-up-right-from-square" class="text-xs" />
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach

            @foreach($demos as $demo)
                <article class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white card-hover reveal dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ (($projects->count() + $loop->index) % 3) * 100 }}ms">
                    <a href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener" class="relative block aspect-[8/5] overflow-hidden bg-gray-100 dark:bg-white/5" tabindex="-1" aria-hidden="true">
                        @if(is_file(public_path('images/demos/' . $demo['slug'] . '.webp')))
                            <img src="{{ asset('images/demos/' . $demo['slug'] . '.webp') }}" srcset="{{ demo_srcset($demo['slug']) }}" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" alt="" width="960" height="600" loading="lazy" class="h-full w-full object-cover object-top transition-transform duration-500 group-hover:scale-[1.03]">
                        @endif
                        <span class="absolute left-3 top-3 rounded-full bg-brand-950/90 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white" data-concept-chip>{{ __('Концепция') }}</span>
                    </a>
                    <div class="flex flex-1 flex-col p-6">
                        <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300">{{ $demo['industry'] }}</span>
                        <h2 class="mt-2 text-xl font-bold text-brand-950 dark:text-white">{{ $demo['name'] }}</h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">{{ $demo['description'] }}</p>
                        <a href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener" class="mt-auto pt-5 inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">
                            {{ __('Вижте сайта') }} <x-icon name="arrow-up-right-from-square" class="text-xs" />
                        </a>
                        @php($industrySlug = app()->getLocale() === 'bg' ? collect(config('creatium.industry_pages'))->search(fn ($page) => $page['demo'] === $demo['slug']) : false)
                        @if($industrySlug !== false)
                            <a href="{{ route('industry', $industrySlug) }}" class="mt-2 text-sm text-gray-600 underline decoration-gray-300 underline-offset-4 hover:text-brand-700 dark:text-gray-300 dark:hover:text-white">{{ config("creatium.industry_pages.$industrySlug.title") }}</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        @if(app()->getLocale() === 'en' && ! empty($demos))
            <p class="mt-8 text-sm text-gray-500 dark:text-gray-400">{{ __('Сайтовете-концепции са на български.') }}</p>
        @endif
        <p class="mt-6 text-gray-600 dark:text-gray-300">{{ __('Ако искате вашият да е един от тях, пишете ни.') }} <a href="{{ lroute('contact') }}" class="font-semibold text-brand-700 underline dark:text-brand-300">{{ __('Свържете се с нас') }}</a></p>
    </div>
</section>
@else
<section class="py-16 bg-white dark:bg-ink-950">
    <div class="max-w-xl mx-auto px-4 text-center py-8">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-brand-50 flex items-center justify-center dark:bg-white/10">
            <x-icon name="rocket" class="text-brand-600 text-xl dark:text-brand-300" />
        </div>
        <h2 class="text-2xl font-bold mb-3 dark:text-white">{{ __('Тук скоро ще има проекти') }}</h2>
        <p class="text-gray-600 mb-6 dark:text-gray-300">{{ __('Ако искате вашият да е един от тях, пишете ни.') }}</p>
        <a href="{{ lroute('contact') }}" class="inline-flex items-center gap-2 bg-brand-950 text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">{{ __('Пишете ни') }} <x-icon name="arrow-right" class="text-xs" /></a>
    </div>
</section>
@endif
@endsection
