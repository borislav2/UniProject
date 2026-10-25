@extends('layouts.public')

@section('title', __('Уебсайтове и маркетинг за бизнеса в България'))
@section('meta_description', __('Сайтове, SEO и имейл кампании за малки и средни бизнеси в България. Правим нови сайтове, поддържаме съществуващи и се грижим да ви намират в Google.'))

@push('head')
@php
    $contact = config('creatium.contact');
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfessionalService',
        'name' => 'Creatium Lab',
        'url' => lroute('home'),
        'logo' => asset('images/logo.webp'),
        'image' => asset('images/og-image.png'),
        'description' => __('Правим сайтове за малки бизнеси и се грижим хората да ги намират в Google.'),
        'email' => $contact['email'],
        'telephone' => $contact['phones'][0] ?? null,
        'address' => ['@type' => 'PostalAddress', 'addressLocality' => __($contact['city']), 'addressCountry' => 'BG'],
        'areaServed' => ['@type' => 'Country', 'name' => __('България')],
        'sameAs' => array_values(array_filter([$contact['facebook'] ?? null])),
    ];
@endphp
<script type="application/ld+json">{!! json_encode(array_filter($ld), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@php
    // Заглавието с оцветени думи (home_hero.accent): текстът се екранира, после думите се обвиват в <span>.
    $title = e($hero['title']);
    foreach (array_filter((array) ($hero['accent'] ?? [])) as $word) {
        $title = preg_replace('/(?<![\p{L}])(' . preg_quote(e($word), '/') . ')(?![\p{L}])/u', '<span class="text-accent">$1</span>', $title, 1);
    }

    // Номерацията на секциите следва реда им („01 / Проблемът“), блогът може да липсва.
    $section = 0;
    $number = function () use (&$section) { return str_pad((string) ++$section, 2, '0', STR_PAD_LEFT) . ' /'; };

    $hasReviews = ($reviews['count'] ?? 0) > 0 && ($reviews['rating'] ?? 0) > 0;
@endphp

@section('content')

{{-- Hero: dark in both themes --}}
<section class="relative overflow-hidden surface-night grain on-dark text-white">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_25%,transparent_72%)]" aria-hidden="true"></div>
    <div class="hidden md:block aurora-a absolute -top-48 right-[-10rem] w-[40rem] h-[40rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="hidden md:block aurora-b absolute top-1/3 -left-48 w-[30rem] h-[30rem] rounded-full bg-brand-700/30 blur-3xl" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-header pb-14 md:pb-20 grid lg:grid-cols-12 gap-12 lg:gap-12 items-center">
        <div class="lg:col-span-7">
            <p class="rise-in kicker">{{ $trust['eyebrow'] }}</p>
            <h1 class="rise-text mt-6" style="--d: 80ms">
                <span class="display block text-[2.6rem] leading-[1.05] sm:text-6xl xl:text-7xl">{!! $title !!}</span>
                @if(filled($hero['highlight'] ?? null))
                    <span class="mt-5 block text-xl sm:text-2xl font-bold tracking-tight leading-snug text-brand-200">{{ $hero['highlight'] }}</span>
                @endif
            </h1>
            <div class="rise-text mt-10 flex flex-col sm:flex-row gap-3" style="--d: 260ms">
                <a href="{{ lroute('contact') }}#konsultatsiya" class="btn-shine btn-glow inline-flex items-center justify-center gap-2 bg-white text-brand-950 px-7 py-3.5 rounded-xl font-semibold">
                    <x-icon name="calendar" class="text-sm" /> {{ $hero['button'] }}
                </a>
                <a href="{{ lroute('services') }}" class="relative edge glass inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl font-semibold text-white hover:bg-white/10 transition-colors">
                    {{ __('Вижте услугите') }}
                </a>
            </div>
        </div>

        <div class="lg:col-span-5 2xl:-mr-16 rise-in" style="--d: 260ms">
            @include('partials/hero-visual')
        </div>
    </div>

    {{-- Proof: only facts we can stand behind; the Google rating appears once there are real reviews --}}
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 md:pb-20">
        {{-- Четири еднакви колони с една обща мисъл (proof_label): стойност и един ред текст; „link“ прави текста връзка. --}}
        @if(filled($trust['proof_label'] ?? null))
            <p class="rise-text mb-4 text-xs font-bold uppercase tracking-[0.18em] text-brand-300" style="--d: 300ms">{{ $trust['proof_label'] }}</p>
        @endif
        @if($hasReviews)
            <div class="rise-text mb-4 flex items-center gap-4" style="--d: 300ms">
                <span class="w-11 h-11 shrink-0 rounded-full bg-white flex items-center justify-center text-lg font-extrabold text-brand-950" aria-hidden="true">G</span>
                <p>
                    <span class="flex items-center gap-2 text-2xl font-extrabold">{{ number_format($reviews['rating'], 1, app()->getLocale() === 'bg' ? ',' : '.') }} <span class="text-sm text-amber-400 tracking-tight" aria-hidden="true">★★★★★</span></span>
                    @php($reviewsText = __($reviews['count'] === 1 ? ':count отзив в Google' : ':count отзива в Google', ['count' => $reviews['count']]))
                    <span class="block text-sm text-gray-400">@if($reviews['url'])<a href="{{ $reviews['url'] }}" target="_blank" rel="noopener" class="hover:text-white underline-offset-4 hover:underline">{{ $reviewsText }}</a>@else{{ $reviewsText }}@endif</span>
                </p>
            </div>
        @endif
        <dl class="rise-text grid grid-cols-2 lg:grid-cols-4 border-y border-white/10" style="--d: 320ms">
            @foreach($trust['proof'] as $fact)
                {{-- Отстъпи по позиция: първата колона на всеки ред е подравнена с надписа, другите имат линия вляво. --}}
                <div @class([
                    'py-6 border-white/10',
                    'pr-4 sm:pr-6' => $loop->index % 2 === 0,
                    'pl-4 sm:pl-6 pr-2 border-l' => $loop->index % 2 === 1,
                    'border-t lg:border-t-0' => $loop->index >= 2,
                    'lg:pl-6 lg:border-l' => $loop->index === 2,
                ])>
                    <dt class="sr-only">{{ $fact['text'] }}</dt>
                    <dd class="display text-2xl sm:text-3xl md:text-4xl">{{ $fact['value'] }}</dd>
                    <dd class="mt-2 text-sm text-gray-400 leading-snug">
                        @if(filled($fact['link'] ?? null))
                            <a href="{{ url($fact['link']) }}" class="group underline-offset-4 hover:text-white hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 rounded-sm">{{ $fact['text'] }}&nbsp;<x-icon name="arrow-right" class="inline text-[10px] text-brand-300 transition-transform group-hover:translate-x-0.5" /></a>
                        @else
                            {{ $fact['text'] }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- 01 Problem vs synergy --}}
<section id="problemat" class="relative py-20 md:py-28 bg-white scroll-mt-24 dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="reveal kicker kicker-light">{{ $number() }} {{ __('Проблемът') }}</p>
        <h2 class="reveal display mt-5 max-w-3xl text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $problem['title'] }}</h2>
        <div class="mt-12 grid lg:grid-cols-3 gap-6">
            @foreach($problem['scenarios'] as $scenario)
                @if($loop->last)
                    <article class="reveal relative overflow-hidden rounded-3xl bg-brand-950 text-white p-8 shadow-2xl shadow-brand-950/30 dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                        <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
                        <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
                        <div class="relative">
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-300"><x-icon name="circle-check" class="text-[11px]" /> {{ $scenario['label'] }}</span>
                            <h3 class="mt-5 text-2xl font-extrabold tracking-tight">{{ $scenario['title'] }}</h3>
                            @foreach(preg_split('/\R\s*\R/u', trim($scenario['text'])) as $paragraph)<p class="mt-3 first-of-type:mt-4 text-gray-300 leading-relaxed">{{ $paragraph }}</p>@endforeach
                        </div>
                    </article>
                @else
                    <article class="reveal card-hover rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-red-700 dark:bg-red-500/10 dark:text-red-300"><x-icon name="triangle-exclamation" class="text-[11px]" /> {{ $scenario['label'] }}</span>
                        <h3 class="mt-5 text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $scenario['title'] }}</h3>
                        @foreach(preg_split('/\R\s*\R/u', trim($scenario['text'])) as $paragraph)<p class="mt-3 first-of-type:mt-4 text-gray-600 leading-relaxed dark:text-gray-300">{{ $paragraph }}</p>@endforeach
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- 02 The engine: one system, drawn as a hub with the four components --}}
<section id="dvigatel" class="relative py-20 md:py-28 surface-night grain on-dark text-white scroll-mt-24 overflow-hidden">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 reveal">
            <div>
                <p class="kicker">{{ $number() }} {{ $trust['engine_eyebrow'] }}</p>
                <h2 class="display mt-5 max-w-3xl text-4xl md:text-5xl">{{ $engine['title'] }}</h2>
            </div>
            <a href="{{ lroute('services') }}" class="group shrink-0 inline-flex items-center gap-2 font-semibold text-brand-200 hover:text-white">
                {{ __('Вижте услугите') }} <x-icon name="arrow-right" class="text-xs transition-transform group-hover:translate-x-1" />
            </a>
        </div>

        @include('partials/engine-diagram', ['cards' => $engineCards, 'center' => $trust['engine_center']])

        <div class="mt-14 grid lg:grid-cols-2 gap-6">
            @foreach([['code', 'tech', 1], ['chart-line', 'growth', 2]] as [$icon, $group, $n])
                <article class="reveal relative edge glass overflow-hidden rounded-3xl p-7 md:p-9" style="--reveal-delay: {{ ($n - 1) * 100 }}ms">
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 shrink-0 rounded-2xl bg-brand-gradient ring-1 ring-white/20 flex items-center justify-center">
                            <x-icon :name="$icon" class="text-white text-lg" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-300">{{ $engine[$group . '_tag'] }}</p>
                            <h3 class="mt-1 text-2xl font-extrabold tracking-tight">{{ $engine[$group . '_title'] }}</h3>
                        </div>
                    </div>
                    <ul class="mt-8 space-y-5">
                        @foreach($engine[$group . '_items'] as $item)
                            <li class="flex items-start gap-3.5">
                                <span class="mt-1 w-5 h-5 shrink-0 rounded-full bg-white/10 flex items-center justify-center"><x-icon name="check" class="text-[10px] text-brand-200" /></span>
                                <span><span class="block font-bold">{{ $item['title'] }}</span><span class="mt-1 block text-gray-400 leading-relaxed">{{ $item['text'] }}</span></span>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 03 Who we work with (and who we don't) --}}
<section id="s-kogo-rabotim" class="relative py-20 md:py-28 bg-brand-50/60 scroll-mt-24 dark:bg-ink-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 lg:gap-16">
        <div class="lg:col-span-5 reveal lg:sticky lg:top-32 self-start">
            <p class="kicker kicker-light">{{ $number() }} {{ $trust['fit_eyebrow'] }}</p>
            <h2 class="display mt-5 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $trust['fit_title'] }}</h2>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed dark:text-gray-300">{{ $trust['fit_text'] }}</p>
        </div>
        <div class="lg:col-span-7">
            @if(filled($trust['fit_list_label'] ?? null))
                <p class="reveal mb-4 text-sm font-bold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ $trust['fit_list_label'] }}</p>
            @endif
            <ol class="reveal divide-y divide-gray-200 border-y border-gray-200 dark:divide-white/10 dark:border-white/10">
                @foreach($trust['fit_items'] as $item)
                    <li class="group relative flex gap-6 py-6 md:py-7 pl-5 md:pl-6">
                        <span class="absolute left-0 top-6 bottom-6 w-[3px] rounded-full bg-brand-200 transition-colors group-hover:bg-brand-600 dark:bg-white/10 dark:group-hover:bg-brand-300" aria-hidden="true"></span>
                        <span class="pt-1 text-sm font-bold tracking-[0.18em] text-brand-600 dark:text-brand-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span>
                            <span class="block text-xl md:text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $item['title'] }}</span>
                            <span class="mt-2 block text-gray-600 leading-relaxed dark:text-gray-300">{{ $item['text'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

{{-- 04 Principles: large numerals --}}
<section id="printsipi" class="relative py-20 md:py-28 bg-white scroll-mt-24 dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-3xl">
            <p class="kicker kicker-light">{{ $number() }} {{ $trust['principles_eyebrow'] }}</p>
            <h2 class="display mt-5 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $trust['principles_title'] }}</h2>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed dark:text-gray-300">{{ $trust['principles_text'] }}</p>
        </div>
        <div class="mt-14 grid md:grid-cols-2 border-t border-l border-gray-200 dark:border-white/10">
            @foreach($trust['principle_items'] as $item)
                <article class="reveal p-8 md:p-10 border-r border-b border-gray-200 dark:border-white/10" style="--reveal-delay: {{ ($loop->index % 2) * 100 }}ms">
                    <div class="flex items-center gap-6">
                        <span class="numeral">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="h-px flex-1 bg-gray-200 dark:bg-white/10" aria-hidden="true"></span>
                    </div>
                    <h3 class="mt-8 text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $item['title'] }}</h3>
                    <p class="mt-3 text-gray-600 leading-relaxed dark:text-gray-300">{{ $item['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 05 Industries: the demo websites --}}
@if(! empty($demos))
<section id="branshove" class="relative py-20 md:py-28 surface-night grain on-dark text-white scroll-mt-24 overflow-hidden">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal grid lg:grid-cols-12 gap-8 items-end">
            <div class="lg:col-span-7">
                <p class="kicker">{{ $number() }} {{ $trust['demos_eyebrow'] }}</p>
                <h2 class="display mt-5 text-4xl md:text-5xl">{{ $trust['demos_title'] }}</h2>
                <p class="mt-6 text-lg text-gray-400 leading-relaxed">{{ $trust['demos_text'] }}</p>
            </div>
            <dl class="lg:col-span-5 flex gap-10 lg:justify-end">
                <div><dd class="display text-5xl">{{ count($demos) }}</dd><dt class="mt-1 text-xs font-bold uppercase tracking-[0.18em] text-gray-400">{{ __('демо сайта') }}</dt></div>
                <div><dd class="display text-5xl">{{ collect($demos)->pluck('industry')->unique()->count() }}</dd><dt class="mt-1 text-xs font-bold uppercase tracking-[0.18em] text-gray-400">{{ __('бранша') }}</dt></div>
            </dl>
        </div>

        <div class="reveal mt-12 -mx-4 px-4 sm:mx-0 sm:px-0 flex gap-4 overflow-x-auto snap-x snap-mandatory pb-4 sm:grid sm:grid-cols-2 lg:grid-cols-3 sm:overflow-visible sm:pb-0 [scrollbar-width:none]">
            @foreach($demos as $demo)
                <a href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener" class="group relative edge glass snap-start shrink-0 w-[82%] sm:w-auto rounded-2xl overflow-hidden hover:bg-white/10 transition-colors">
                    <span class="relative block aspect-[8/5] overflow-hidden bg-white/5">
                        @if(is_file(public_path('images/demos/' . $demo['slug'] . '.webp')))
                            <img src="{{ asset('images/demos/' . $demo['slug'] . '.webp') }}" srcset="{{ demo_srcset($demo['slug']) }}" sizes="(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw" alt="" width="960" height="600" loading="lazy" decoding="async" class="h-full w-full object-cover object-top opacity-90 transition duration-500 group-hover:scale-[1.03] group-hover:opacity-100">
                        @endif
                        <span class="absolute top-3 left-3 rounded-full bg-brand-950/80 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-white ring-1 ring-white/20">{{ __('Демо') }}</span>
                    </span>
                    <span class="block p-5">
                        <span class="block text-xs font-bold uppercase tracking-[0.18em] text-brand-300">{{ $demo['industry'] }}</span>
                        <span class="mt-1.5 flex items-center justify-between gap-3 font-extrabold text-lg">{{ $demo['name'] }} <x-icon name="arrow-up-right-from-square" class="text-xs text-brand-300" /></span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 06 Process: large numerals in a grid --}}
<section id="proces" class="relative py-20 md:py-28 bg-brand-50/60 scroll-mt-24 dark:bg-ink-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8">
            <div class="max-w-3xl">
                <p class="kicker kicker-light">{{ $number() }} {{ __('Работен процес') }}</p>
                <h2 class="display mt-5 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $process['title'] }}</h2>
            </div>
        </div>
        <ol class="mt-14 grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($process['steps'] as $step)
                <li class="reveal card-hover rounded-3xl border border-gray-200 bg-white p-7 md:p-8 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 3) * 80 }}ms">
                    <div class="flex items-center gap-5">
                        <span class="numeral text-5xl">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="h-px flex-1 bg-gray-200 dark:bg-white/10" aria-hidden="true"></span>
                    </div>
                    <h3 class="mt-6 text-xl font-extrabold tracking-tight text-brand-950 dark:text-white"><span class="sr-only">{{ __('Стъпка :n', ['n' => $loop->iteration]) }}: </span>{{ $step['title'] }}</h3>
                    <p class="mt-2 text-gray-600 leading-relaxed dark:text-gray-300">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- 07 Blog --}}
@if($posts->isNotEmpty())
<section id="statii" class="relative py-20 md:py-28 bg-white scroll-mt-24 dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div class="max-w-3xl">
                <p class="kicker kicker-light">{{ $number() }} {{ $trust['blog_eyebrow'] }}</p>
                <h2 class="display mt-5 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $trust['blog_title'] }}</h2>
                <p class="mt-6 text-lg text-gray-600 leading-relaxed dark:text-gray-300">{{ $trust['blog_text'] }}</p>
            </div>
            <a href="{{ lroute('blog.index') }}" class="group shrink-0 inline-flex items-center gap-2 rounded-full border border-gray-300 px-6 py-3 font-semibold text-brand-950 hover:border-brand-500 dark:border-white/20 dark:text-white dark:hover:border-brand-300">
                {{ __('Всички статии') }} <x-icon name="arrow-right" class="text-xs transition-transform group-hover:translate-x-1" />
            </a>
        </div>
        <div class="mt-12 grid md:grid-cols-3 gap-6">
            @foreach($posts as $post)
                @include('blog._card', ['post' => $post, 'heading' => 'h3'])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Contact --}}
<section id="kontakt" class="relative py-20 md:py-28 surface-night grain on-dark text-white scroll-mt-24 overflow-hidden">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]" aria-hidden="true"></div>
    <div class="aurora-a absolute -bottom-40 -left-32 w-[32rem] h-[32rem] rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>
    <div class="aurora-b absolute -top-40 right-0 w-[26rem] h-[26rem] rounded-full bg-brand-400/10 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 items-start">
        <div class="lg:col-span-5 reveal lg:sticky lg:top-32">
            <p class="kicker">{{ $number() }} {{ $trust['contact_eyebrow'] }}</p>
            <h2 class="display mt-5 text-3xl md:text-4xl">{{ $contactBlock['title'] }}</h2>
            <p class="mt-6 text-lg text-gray-300 max-w-md leading-relaxed">{{ $contactBlock['text'] }}</p>
            <p class="mt-10 text-xs font-bold uppercase tracking-[0.18em] text-brand-300">{{ __('За директна връзка') }}</p>
            <ul class="mt-4 space-y-3">
                <li>
                    <a href="mailto:{{ $contact['email'] }}" class="relative edge glass flex items-center gap-4 rounded-2xl px-4 py-3.5 hover:bg-white/10 transition-colors">
                        <span class="w-11 h-11 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon name="envelope" class="text-brand-200" /></span>
                        <span class="font-semibold">{{ $contact['email'] }}</span>
                    </a>
                </li>
                <li class="relative edge glass flex items-center gap-4 rounded-2xl px-4 py-3.5">
                    <span class="w-11 h-11 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon name="phone" class="text-brand-200" /></span>
                    <span class="flex flex-col font-semibold">
                        @foreach($contact['phones'] as $phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:text-brand-200">{{ $phone }}</a>
                        @endforeach
                    </span>
                </li>
                <li class="relative edge glass flex items-center gap-4 rounded-2xl px-4 py-3.5">
                    <span class="w-11 h-11 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon name="location-dot" class="text-brand-200" /></span>
                    <span class="font-semibold">{{ __($contact['city']) }}, {{ __('България') }}</span>
                </li>
                @php($presentationPdf = 'files/' . (app()->getLocale() === 'en' ? 'creatium-lab-presentation.pdf' : 'creatium-lab-prezentatsiya.pdf'))
                @if(is_file(public_path($presentationPdf)))
                    <li>
                        <a href="{{ asset($presentationPdf) }}" download class="relative edge glass flex items-center gap-4 rounded-2xl px-4 py-3.5 hover:bg-white/10 transition-colors">
                            <span class="w-11 h-11 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon name="file" class="text-brand-200" /></span>
                            <span class="font-semibold">{{ __('Изтеглете презентацията ни') }} <span class="text-gray-400 font-normal">PDF</span></span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="lg:col-span-7 min-w-0 reveal" style="--reveal-delay: 120ms">
            <div class="relative edge glass rounded-[1.75rem] p-2 shadow-[0_40px_120px_-30px_rgb(0_0_0/0.8)]">
                @include('partials/contact-form')
            </div>
        </div>
    </div>
</section>
@endsection
