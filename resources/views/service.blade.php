@extends('layouts.public')

@section('title', $page['title'])
@section('breadcrumb', $page['menu'])

@section('content')
@include('partials/page-header', ['eyebrow' => __('Услуги'), 'heading' => $page['title'], 'sub' => $page['lead']])

<section class="py-16 md:py-24 bg-white dark:bg-ink-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-6 lg:grid-cols-5">
        <div class="reveal rounded-3xl border border-gray-200 bg-gray-50/70 p-7 md:p-8 lg:col-span-2 dark:border-white/10 dark:bg-white/[0.03]">
            <h2 class="text-xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Познато ли ви е?') }}</h2>
            <ul class="mt-5 space-y-3">
                @foreach($page['problems'] as $problem)
                    <li class="flex items-start gap-3 text-gray-700 dark:text-gray-300">
                        <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-red-50 flex items-center justify-center dark:bg-red-500/15"><x-icon name="minus" class="text-[10px] text-red-600 dark:text-red-300" /></span>
                        {{ $problem }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="reveal rounded-3xl border border-gray-200 bg-white p-7 md:p-8 lg:col-span-3 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: 100ms">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 shrink-0 rounded-2xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20"><x-icon :name="$page['icon']" class="text-white" /></div>
                <h2 class="text-xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Какво правим') }}</h2>
            </div>
            <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                @foreach($page['includes'] as $item)
                    <li class="flex items-start gap-3 text-gray-700 dark:text-gray-200">
                        <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-brand-50 flex items-center justify-center dark:bg-white/10"><x-icon name="check" class="text-[10px] text-brand-600 dark:text-brand-300" /></span>
                        {{ $item }}
                    </li>
                @endforeach
            </ul>
            <p class="mt-7 rounded-2xl bg-brand-50/70 p-4 font-semibold text-brand-950 dark:bg-white/[0.05] dark:text-white">{{ $page['result'] }}</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="reveal relative overflow-hidden rounded-3xl bg-brand-950 text-white p-7 md:p-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6 dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30">
            <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
            <div class="relative max-w-2xl">
                @if($plan)
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-300">{{ __('Включено в пакет') }}</p>
                    <h2 class="mt-2 text-2xl md:text-3xl font-extrabold tracking-tight">{{ $plan['name'] }}</h2>
                    <p class="mt-2 text-gray-300">{{ $plan['prices'][0] ?? '' }}</p>
                @endif
                @if(filled($page['note'] ?? null))
                    <p class="mt-3 text-gray-300">{{ $page['note'] }}</p>
                @endif
            </div>
            <div class="relative flex flex-col sm:flex-row gap-3 shrink-0">
                <a href="{{ lroute('contact') }}#konsultatsiya" class="btn-shine btn-glow inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 font-semibold text-brand-950 hover:bg-brand-50">{{ __('Безплатна консултация') }} <x-icon name="arrow-right" class="text-sm" /></a>
                <a href="{{ lroute('packages') }}" class="inline-flex items-center justify-center rounded-xl border border-white/25 px-6 py-3.5 font-semibold text-white hover:bg-white/10">{{ __('Вижте пакетите') }}</a>
            </div>
        </div>
    </div>
</section>

<section class="py-16 md:py-20 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center dark:text-white">{{ __('Често задавани въпроси') }}</h2>
        <div class="mt-8 space-y-3">
            @foreach($page['faq'] as [$question, $answer])
                <details class="group rounded-2xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-white/[0.04]">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-brand-950 dark:text-white">
                        {{ $question }}
                        <x-icon name="chevron-down" class="text-sm text-brand-600 transition-transform group-open:rotate-180 dark:text-brand-300" />
                    </summary>
                    <p class="mt-3 leading-relaxed text-gray-600 dark:text-gray-300">{{ $answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 md:py-20 bg-white dark:bg-ink-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Другите ни услуги') }}</h2>
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach($others as $otherKey => $other)
                <a href="{{ \App\Http\Controllers\PageController::serviceUrl($otherKey) }}" class="card-hover flex items-center gap-3 rounded-2xl border border-gray-200 p-4 font-semibold text-brand-950 hover:border-brand-400 dark:border-white/10 dark:text-white">
                    <x-icon :name="$other['icon']" class="text-brand-600 dark:text-brand-300" /> {{ $other['menu'] }}
                </a>
            @endforeach
            <a href="{{ lroute('site-check') }}" class="card-hover flex items-center gap-3 rounded-2xl border border-brand-300 bg-brand-50/70 p-4 font-semibold text-brand-950 hover:border-brand-500 dark:border-brand-400/40 dark:bg-white/[0.05] dark:text-white">
                <x-icon name="gauge-high" class="text-brand-600 dark:text-brand-300" /> {{ __('Безплатна проверка на сайт') }}
            </a>
        </div>
    </div>
</section>

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => $page['title'],
            'description' => $page['seo_description'],
            'serviceType' => $page['menu'],
            'areaServed' => ['@type' => 'Country', 'name' => 'Bulgaria'],
            'provider' => ['@type' => 'Organization', 'name' => 'Creatium Lab', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($qa) => ['@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]], $page['faq']),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endsection
