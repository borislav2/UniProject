@extends('layouts.public')

@section('title', __('Въпроси за изработка на сайт: цена, срокове, SEO'))
@section('meta_description', __('Отговори на най-честите въпроси за цени, плащане, срокове, Google и AI видимост и грижата за сайта след пускането.'))

@push('head')
    @php($ld = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $groups->flatten(1)->map(fn ($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ])->values()->all(),
    ])
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@include('partials/page-header', ['eyebrow' => __('Въпроси'), 'heading' => __('Често задавани въпроси'), 'sub' => __('Цени, срокове, Google и AI, грижата след пускането. Ако не намерите отговор, попитайте ни директно.')])

<section class="py-16 md:py-24 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-10 lg:gap-16">
        {{-- Темите вляво водят към групите; на телефон са хоризонтален ред. --}}
        <nav class="lg:col-span-4 lg:sticky lg:top-32 self-start" aria-label="{{ __('Теми') }}">
            @if($groups->keys()->filter()->isNotEmpty())
                <ul class="flex flex-wrap gap-2 lg:flex-col lg:gap-1">
                    @foreach($groups->keys()->filter() as $group)
                        <li><a href="#{{ Str::slug($group, '-', 'bg') }}" class="inline-flex rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-brand-950 hover:border-brand-400 hover:text-brand-700 lg:rounded-xl lg:border-0 lg:px-3 lg:text-base dark:border-white/15 dark:text-white dark:hover:text-brand-300">{{ $group }}</a></li>
                    @endforeach
                </ul>
            @endif
            <div class="mt-8 hidden lg:block rounded-2xl bg-brand-50/70 p-6 dark:bg-white/[0.04]">
                <p class="font-bold text-brand-950 dark:text-white">{{ __('Не намерихте отговор?') }}</p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('Запазете безплатна консултация и ще отговорим на всичко за вашия бизнес.') }}</p>
                <a href="{{ lroute('contact') }}#konsultatsiya" class="btn-shine mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-950 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800 dark:bg-white dark:text-brand-950">{{ __('Безплатна консултация') }} <x-icon name="arrow-right" class="text-xs" /></a>
            </div>
        </nav>

        <div class="lg:col-span-8 space-y-12">
            @foreach($groups as $group => $items)
                <div @if($group !== '') id="{{ Str::slug($group, '-', 'bg') }}" @endif class="scroll-mt-32">
                    @if($group !== '')
                        <h2 class="mb-4 text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $group }}</h2>
                    @endif
                    <div class="space-y-3">
                        @foreach($items as $item)
                            <details class="group rounded-2xl border border-gray-200 bg-white p-6 open:shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg font-bold text-brand-950 [&::-webkit-details-marker]:hidden dark:text-white">{{ $item['q'] }}<x-icon name="chevron-down" class="text-xs text-gray-500 transition-transform group-open:rotate-180" /></summary>
                                <p class="mt-3 leading-relaxed text-gray-600 dark:text-gray-300">{{ $item['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
