@extends('layouts.public')

{{-- „Изработка на сайт за …“ (само на български): текстът е в creatium.industry_pages, проблемите, решенията, пакетът и въпросите идват от демото. --}}
@section('title', $page['title'])
@section('breadcrumb', $page['title'])

@section('content')
@include('partials/page-header', ['eyebrow' => 'Изработка на сайт', 'heading' => $page['title'], 'sub' => $page['lead']])

<section class="py-16 md:py-24 bg-white dark:bg-ink-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-10 lg:grid-cols-2 lg:items-center">
        <div class="reveal">
            <a href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-3xl border border-gray-200 bg-gray-100 shadow-xl shadow-brand-900/10 dark:border-white/10 dark:bg-white/5">
                @if(is_file(public_path('images/demos/' . $demo['slug'] . '.webp')))
                    <img src="{{ asset('images/demos/' . $demo['slug'] . '.webp') }}" srcset="{{ demo_srcset($demo['slug']) }}" sizes="(min-width: 1024px) 540px, 100vw" alt="Демо: {{ $demo['name'] }}" width="960" height="600" class="aspect-[8/5] w-full object-cover object-top transition-transform duration-500 group-hover:scale-[1.03]">
                @endif
            </a>
            <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2">
                <a href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">Отворете демото: {{ $demo['name'] }} <x-icon name="arrow-right" class="text-xs" /></a>
                <span class="text-sm text-gray-500 dark:text-gray-400">Бизнесът е измислен, сайтът е истински.</span>
            </div>
        </div>

        <div class="reveal" style="--reveal-delay: 100ms">
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $page['must_have_title'] }}</h2>
            <ol class="mt-6 space-y-5">
                @foreach($page['must_have'] as [$title, $text])
                    <li class="flex gap-4">
                        <span class="w-8 h-8 shrink-0 rounded-full border-2 border-brand-600 inline-flex items-center justify-center text-sm font-bold text-brand-600 dark:border-brand-300 dark:text-brand-300">{{ $loop->iteration }}</span>
                        <div>
                            <p class="font-semibold text-brand-950 dark:text-white">{{ $title }}</p>
                            <p class="mt-1 leading-relaxed text-gray-600 dark:text-gray-300">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

<section class="py-16 md:py-24 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider dark:text-brand-300">{{ $demo['industry'] }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950 dark:text-white">Къде губите клиенти и как го оправяме</h2>
        </div>
        <div class="mt-10 grid gap-6 lg:grid-cols-2">
            <div class="reveal rounded-3xl border border-gray-200 bg-white p-7 md:p-8 dark:border-white/10 dark:bg-white/[0.03]">
                <h3 class="flex items-center gap-3 text-lg font-bold text-brand-950 dark:text-white"><span class="w-9 h-9 rounded-xl bg-red-50 text-red-600 inline-flex items-center justify-center dark:bg-red-500/15 dark:text-red-300"><x-icon name="triangle-exclamation" class="text-sm" /></span>Познато ли ви е?</h3>
                <ul class="mt-6 space-y-5">
                    @foreach($demo['problems'] as [$title, $text])
                        <li><p class="font-semibold text-brand-950 dark:text-white">{{ $title }}</p><p class="mt-1 leading-relaxed text-gray-600 dark:text-gray-300">{{ $text }}</p></li>
                    @endforeach
                </ul>
            </div>
            <div class="reveal rounded-3xl border border-gray-200 bg-white p-7 md:p-8 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: 100ms">
                <h3 class="flex items-center gap-3 text-lg font-bold text-brand-950 dark:text-white"><span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 inline-flex items-center justify-center dark:bg-white/10 dark:text-brand-300"><x-icon name="check" class="text-sm" /></span>Какво прави сайтът</h3>
                <ul class="mt-6 space-y-5">
                    @foreach($demo['solutions'] as [$title, $text])
                        <li><p class="font-semibold text-brand-950 dark:text-white">{{ $title }}</p><p class="mt-1 leading-relaxed text-gray-600 dark:text-gray-300">{{ $text }}</p></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="reveal mt-6 relative overflow-hidden rounded-3xl bg-brand-950 text-white p-7 md:p-10 grid gap-8 md:grid-cols-12 md:items-center dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30">
            <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
            <div class="relative md:col-span-7">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-300">Подходящ пакет</p>
                <h2 class="mt-2 text-2xl md:text-3xl font-extrabold tracking-tight">{{ $plan['name'] ?? 'Индивидуална оферта' }}</h2>
                @if($plan)
                    <ul class="mt-3 space-y-1 font-semibold text-gray-200">
                        @foreach($plan['prices'] as $price)<li>{{ $price }}</li>@endforeach
                    </ul>
                @endif
                <p class="mt-4 leading-relaxed text-gray-300">{{ $demo['package']['why'] }}</p>
            </div>
            <div class="relative md:col-span-5 flex flex-col gap-3">
                <a href="{{ lroute('contact') }}#konsultatsiya" class="btn-shine btn-glow inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 font-semibold text-brand-950 hover:bg-brand-50">Безплатна консултация <x-icon name="arrow-right" class="text-sm" /></a>
                <a href="{{ lroute('packages') }}" class="inline-flex items-center justify-center rounded-xl border border-white/25 px-6 py-3.5 font-semibold text-white hover:bg-white/10">Вижте какво включват пакетите</a>
            </div>
        </div>
    </div>
</section>

<section class="py-16 md:py-20 bg-white dark:bg-ink-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center dark:text-white">Често задавани въпроси</h2>
        <div class="mt-8 space-y-3">
            @foreach($demo['faq'] as [$question, $answer])
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

<section class="py-16 md:py-20 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl font-extrabold tracking-tight text-brand-950 dark:text-white">Сайт за друг бранш</h2>
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($others as $otherSlug => $other)
                <a href="{{ route('industry', $otherSlug) }}" class="card-hover flex items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-4 font-semibold text-brand-950 hover:border-brand-400 dark:border-white/10 dark:bg-white/[0.03] dark:text-white">
                    {{ $other['title'] }} <x-icon name="arrow-right" class="text-xs text-brand-600 dark:text-brand-300" />
                </a>
            @endforeach
            <a href="{{ \App\Http\Controllers\PageController::serviceUrl('website') }}" class="card-hover flex items-center justify-between gap-3 rounded-2xl border border-brand-300 bg-brand-50/70 p-4 font-semibold text-brand-950 hover:border-brand-500 dark:border-brand-400/40 dark:bg-white/[0.05] dark:text-white">
                Изработка на сайт за всеки бизнес <x-icon name="arrow-right" class="text-xs text-brand-600 dark:text-brand-300" />
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
            'serviceType' => 'Изработка на сайт',
            'audience' => ['@type' => 'BusinessAudience', 'name' => $page['menu']],
            'areaServed' => ['@type' => 'Country', 'name' => 'Bulgaria'],
            'provider' => ['@type' => 'Organization', 'name' => 'Creatium Lab', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($qa) => ['@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]], $demo['faq']),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endsection
