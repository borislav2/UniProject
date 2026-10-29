@extends('layouts.public')

@section('title', __('Изработка на сайт, SEO, Google профил и имейл маркетинг'))
@section('meta_description', __('Сайт, който продава, SEO и видимост в ChatGPT, Google профил и отзиви, имейл кампании и поддръжка. Всичко за онлайн клиенти, от един екип.'))

@section('content')
@php($cardPages = ['websites' => 'website', 'geo-seo' => 'seo', 'email' => 'email', 'monitoring' => 'support'])
@include('partials/page-header', ['eyebrow' => __('Услуги'), 'heading' => $page['title'], 'sub' => $page['intro']])

<section class="py-20 md:py-28 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 reveal">
        <p class="kicker kicker-light">01 / {{ __('Компонентите') }}</p>
    </div>
    <div class="mt-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-6">
        @foreach($page['cards'] as $card)
            <article id="{{ $card['slug'] }}" class="reveal card-hover scroll-mt-28 overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 md:p-9 flex flex-col dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 2) * 100 }}ms">
                <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-brand-400/10 blur-2xl dark:bg-brand-500/10" aria-hidden="true"></div>
                <div class="flex items-center gap-5">
                    <span class="numeral">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="h-px flex-1 bg-gray-200 dark:bg-white/10" aria-hidden="true"></span>
                    <div class="icon-pop w-12 h-12 shrink-0 rounded-2xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20"><x-icon :name="$card['icon']" class="text-white" /></div>
                </div>
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">{{ $card['label'] }}</p>
                <h2 class="mt-2 text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $card['title'] }}</h2>
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

</section>

{{-- 02 How the parts work together --}}
<section class="relative py-20 md:py-28 surface-night grain on-dark text-white overflow-hidden">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-3xl">
            <p class="kicker">02 / {{ $trust['engine_eyebrow'] }}</p>
            <h2 class="display mt-5 text-4xl md:text-5xl">{{ $trust['principles_title'] }}</h2>
            <p class="mt-6 text-lg text-gray-400 leading-relaxed">{{ $trust['principles_text'] }}</p>
        </div>
        @include('partials/engine-diagram', ['cards' => $page['cards'], 'center' => $trust['engine_center']])
    </div>
</section>

<section class="py-16 md:py-24 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="reveal kicker kicker-light">03 / {{ __('Следващата стъпка') }}</p>
        <div class="mt-8 reveal relative overflow-hidden rounded-3xl bg-brand-950 text-white p-8 md:p-12 flex flex-col md:flex-row md:items-center md:justify-between gap-6 dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30">
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
