@extends('layouts.public')

@section('title', __('Услуги: сайт, SEO и AI видимост, имейл маркетинг, поддръжка'))
@section('meta_description', __('Уеб платформа, която продава, GEO и SEO позициониране, автоматизиран имейл маркетинг и техническа грижа 24/7. Четирите елемента на един работещ търговски канал.'))

@section('content')
@php($cardPages = ['websites' => 'website', 'geo-seo' => 'seo', 'email' => 'email', 'monitoring' => 'support'])
@include('partials/page-header', ['eyebrow' => __('Услуги'), 'heading' => $page['title'], 'sub' => $page['intro']])

<section class="py-16 md:py-24 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-6">
        @foreach($page['cards'] as $card)
            <article id="{{ $card['slug'] }}" class="reveal card-hover scroll-mt-28 overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 md:p-9 flex flex-col dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 2) * 100 }}ms">
                <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-brand-400/10 blur-2xl dark:bg-brand-500/10" aria-hidden="true"></div>
                <div class="flex items-center gap-4">
                    <div class="icon-pop w-14 h-14 shrink-0 rounded-2xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20"><x-icon :name="$card['icon']" class="text-white text-lg" /></div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-600 dark:text-brand-300">{{ $loop->iteration }}. {{ $card['label'] }}</p>
                </div>
                <h2 class="mt-6 text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $card['title'] }}</h2>
                <p class="mt-2 font-semibold text-brand-700 dark:text-brand-300">{{ $card['tag'] }}</p>
                <p class="mt-4 text-gray-600 leading-relaxed dark:text-gray-300">{{ $card['description'] }}</p>
                <h3 class="mt-7 text-sm font-bold uppercase tracking-wider text-brand-950 dark:text-white">{{ __('Какво получавате') }}</h3>
                <ul class="mt-4 space-y-3">
                    @foreach($card['includes'] as $item)
                        <li class="flex items-start gap-3 text-gray-700 dark:text-gray-200"><span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-brand-50 flex items-center justify-center dark:bg-white/10"><x-icon name="check" class="text-[10px] text-brand-600 dark:text-brand-300" /></span>{{ $item }}</li>
                    @endforeach
                </ul>
                @if($pageKey = $cardPages[$card['slug']] ?? null)
                    <div class="flex-1"></div>
                    <a href="{{ \App\Http\Controllers\PageController::serviceUrl($pageKey) }}" class="mt-7 inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">{{ __('Научете повече') }} <x-icon name="arrow-right" class="text-sm" /></a>
                @endif
            </article>
        @endforeach

        @if($google = site('service_pages')['google'] ?? null)
            <article class="reveal card-hover overflow-hidden rounded-3xl border border-brand-300 bg-brand-50/60 p-7 md:p-9 flex flex-col md:col-span-2 md:flex-row md:items-center md:justify-between gap-6 dark:border-brand-400/30 dark:bg-white/[0.04]">
                <div class="flex items-start gap-4">
                    <div class="icon-pop w-14 h-14 shrink-0 rounded-2xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20"><x-icon :name="$google['icon']" class="text-white text-lg" /></div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-600 dark:text-brand-300">{{ __('Ново') }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $google['title'] }}</h2>
                        <p class="mt-2 max-w-2xl text-gray-600 leading-relaxed dark:text-gray-300">{{ $google['lead'] }}</p>
                    </div>
                </div>
                <a href="{{ \App\Http\Controllers\PageController::serviceUrl('google') }}" class="btn-shine shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-brand-950 px-6 py-3.5 font-semibold text-white hover:bg-brand-800 dark:bg-white dark:text-brand-950">{{ __('Научете повече') }} <x-icon name="arrow-right" class="text-sm" /></a>
            </article>
        @endif
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        <div class="reveal relative overflow-hidden rounded-3xl bg-brand-950 text-white p-8 md:p-12 flex flex-col md:flex-row md:items-center md:justify-between gap-6 dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30">
            <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
            <h2 class="relative text-2xl md:text-3xl font-extrabold tracking-tight max-w-2xl">{{ $page['cta_title'] }}</h2>
            <a href="{{ lroute('contact') }}#contact-form" class="relative btn-shine btn-glow shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-white px-7 py-3.5 font-semibold text-brand-950 hover:bg-brand-50">{{ $page['cta_button'] }} <x-icon name="arrow-right" class="text-sm" /></a>
        </div>
    </div>
</section>

<section class="py-16 md:py-20 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal relative overflow-hidden rounded-2xl bg-brand-950 text-white p-8 md:p-10 grid md:grid-cols-12 gap-6 items-center dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30">
            <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
            <div class="relative md:col-span-8">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-300">{{ __('Безплатно') }}</p>
                <h2 class="mt-2 text-2xl md:text-3xl font-extrabold tracking-tight">{{ __('Проверете сайта си за минута') }}</h2>
                <p class="mt-3 text-gray-300 leading-relaxed">{{ __('Въведете адреса и имейл и ще получите отчет: HTTPS, скорост на сървъра, заглавия за Google, телефон и още.') }}</p>
            </div>
            <div class="relative md:col-span-4 md:text-right">
                <a href="{{ lroute('site-check') }}" class="btn-shine inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 font-semibold text-brand-950 hover:bg-brand-50">{{ __('Проверете сайта') }} <x-icon name="magnifying-glass" class="text-sm" /></a>
            </div>
        </div>
    </div>
</section>
@endsection
