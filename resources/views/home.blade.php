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
    ];
@endphp
<script type="application/ld+json">{!! json_encode(array_filter($ld), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')

{{-- Hero: dark in both themes --}}
<section class="relative overflow-hidden surface-night grain on-dark text-white">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_25%,transparent_72%)]" aria-hidden="true"></div>
    <div class="hidden md:block aurora-a absolute -top-48 right-[-10rem] w-[40rem] h-[40rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="hidden md:block aurora-b absolute top-1/3 -left-48 w-[30rem] h-[30rem] rounded-full bg-brand-700/30 blur-3xl" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-header pb-20 md:pb-28 grid lg:grid-cols-12 gap-12 lg:gap-12 items-center">
        <div class="lg:col-span-7">
            <span class="rise-in badge-glow inline-flex items-center gap-2.5 rounded-full px-4 py-1.5 text-xs font-semibold text-brand-100">
                <span class="relative flex w-2 h-2"><span class="absolute inset-0 rounded-full bg-brand-300 animate-ping motion-reduce:animate-none"></span><span class="relative w-2 h-2 rounded-full bg-brand-300"></span></span>
                {{ __('Уебсайтове и дигитален маркетинг') }}
            </span>
            <h1 class="rise-text mt-7" style="--d: 80ms">
                <span class="display block text-4xl sm:text-5xl xl:text-6xl">{{ $hero['title'] }}</span>
                @if(filled($hero['highlight'] ?? null))
                    <span class="text-shimmer mt-4 block text-2xl sm:text-3xl font-extrabold tracking-tight leading-snug">{{ $hero['highlight'] }}</span>
                @endif
            </h1>
            <p class="rise-text mt-7 text-lg text-gray-300 leading-relaxed max-w-2xl text-pretty" style="--d: 180ms">{{ $hero['subtitle'] }}</p>
            <div class="rise-text mt-10 flex flex-col sm:flex-row gap-3" style="--d: 260ms">
                <a href="#konsultatsiya" class="btn-shine btn-glow inline-flex items-center justify-center gap-2 bg-white text-brand-950 px-7 py-3.5 rounded-xl font-semibold">
                    <x-icon name="calendar" class="text-sm" /> {{ $hero['button'] }}
                </a>
                <a href="{{ lroute('services') }}" class="relative edge glass inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl font-semibold text-white hover:bg-white/10 transition-colors">
                    {{ __('Вижте услугите') }}
                </a>
            </div>
        </div>

        <div class="lg:col-span-5 rise-in" style="--d: 260ms">
            <div class="relative edge glass rounded-[1.75rem] p-6 md:p-8 shadow-[0_40px_120px_-30px_rgb(0_0_0/0.8)]">
                <p class="eyebrow">{{ $hero['standards_title'] }}</p>
                <ul class="mt-6 space-y-6">
                    @foreach($hero['standards'] as $item)
                        <li class="flex items-start gap-4">
                            <span class="w-11 h-11 shrink-0 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon :name="['project-diagram', 'cogs', 'file-contract'][$loop->index % 3]" class="text-brand-200" /></span>
                            <span><span class="block font-bold text-white">{{ $item['title'] }}</span><span class="mt-1 block text-sm text-gray-300 leading-relaxed">{{ $item['text'] }}</span></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Problem vs synergy --}}
<section id="problemat" class="relative py-20 md:py-28 bg-white scroll-mt-24 dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="reveal display max-w-3xl text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $problem['title'] }}</h2>
        <div class="mt-12 grid lg:grid-cols-3 gap-6">
            @foreach($problem['scenarios'] as $scenario)
                @if($loop->last)
                    <article class="reveal relative overflow-hidden rounded-3xl bg-brand-950 text-white p-8 shadow-2xl shadow-brand-950/30 dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                        <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
                        <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
                        <div class="relative">
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-300"><x-icon name="circle-check" class="text-[11px]" /> {{ $scenario['label'] }}</span>
                            <h3 class="mt-5 text-2xl font-extrabold tracking-tight">{{ $scenario['title'] }}</h3>
                            <p class="mt-4 text-gray-300 leading-relaxed">{{ $scenario['text'] }}</p>
                        </div>
                    </article>
                @else
                    <article class="reveal card-hover rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-red-700 dark:bg-red-500/10 dark:text-red-300"><x-icon name="triangle-exclamation" class="text-[11px]" /> {{ $scenario['label'] }}</span>
                        <h3 class="mt-5 text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $scenario['title'] }}</h3>
                        <p class="mt-4 text-gray-600 leading-relaxed dark:text-gray-300">{{ $scenario['text'] }}</p>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- The engine: services as one system --}}
<section id="dvigatel" class="relative py-20 md:py-28 bg-brand-50/60 scroll-mt-24 dark:bg-ink-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 reveal">
            <h2 class="display max-w-3xl text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $engine['title'] }}</h2>
            <a href="{{ lroute('services') }}" class="group shrink-0 inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-950 dark:text-brand-300 dark:hover:text-white">
                {{ __('Вижте услугите') }} <x-icon name="arrow-right" class="text-xs transition-transform group-hover:translate-x-1" />
            </a>
        </div>

        <div class="mt-12 grid lg:grid-cols-2 gap-6">
            @foreach([['code', 'tech', 1], ['chart-line', 'growth', 2]] as [$icon, $group, $number])
                <article class="reveal card-hover overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 md:p-9 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($number - 1) * 100 }}ms">
                    <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-brand-400/10 blur-2xl dark:bg-brand-500/10" aria-hidden="true"></div>
                    <div class="flex items-start gap-5">
                        <div class="icon-pop w-14 h-14 shrink-0 rounded-2xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
                            <x-icon :name="$icon" class="text-white text-lg" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">{{ $number }}. {{ $engine[$group . '_tag'] }}</p>
                            <h3 class="mt-1 text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $engine[$group . '_title'] }}</h3>
                        </div>
                    </div>
                    <ul class="mt-8 space-y-5">
                        @foreach($engine[$group . '_items'] as $item)
                            <li class="flex items-start gap-3.5">
                                <span class="mt-1 w-5 h-5 shrink-0 rounded-full bg-brand-50 flex items-center justify-center dark:bg-white/10"><x-icon name="check" class="text-[10px] text-brand-600 dark:text-brand-300" /></span>
                                <span><span class="block font-bold text-brand-950 dark:text-white">{{ $item['title'] }}</span><span class="mt-1 block text-gray-600 leading-relaxed dark:text-gray-300">{{ $item['text'] }}</span></span>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Process: sticky title + timeline --}}
<section id="proces" class="relative py-20 md:py-28 bg-white scroll-mt-24 dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 lg:gap-16">
        <div class="lg:col-span-5 lg:sticky lg:top-32 self-start reveal">
            <span class="eyebrow">{{ __('Работен процес') }}</span>
            <h2 class="display mt-4 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $process['title'] }}</h2>
            <a href="#konsultatsiya" class="btn-shine mt-8 inline-flex items-center gap-2 bg-brand-950 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                <x-icon name="calendar" class="text-sm" /> {{ $hero['button'] }}
            </a>
        </div>

        <ol class="lg:col-span-7 relative pl-16 md:pl-20">
            <span class="absolute left-[1.375rem] md:left-[1.625rem] top-3 bottom-3 w-px bg-gray-200 dark:bg-white/10" aria-hidden="true"></span>
            <span class="timeline-fill absolute left-[1.375rem] md:left-[1.625rem] top-3 bottom-3 w-px bg-gradient-to-b from-brand-500 via-brand-400 to-brand-300" aria-hidden="true"></span>
            @foreach($process['steps'] as $step)
                <li class="reveal relative pb-6 last:pb-0" style="--reveal-delay: 60ms">
                    <span class="absolute -left-16 md:-left-20 top-4 w-11 h-11 md:w-[3.25rem] md:h-[3.25rem] rounded-full bg-white ring-1 ring-gray-200 flex items-center justify-center text-sm font-extrabold shadow-sm dark:bg-ink-900 dark:ring-white/15" aria-hidden="true"><span class="text-gradient">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></span>
                    <div class="card-hover rounded-2xl border border-gray-200 bg-white p-6 md:p-7 dark:border-white/10 dark:bg-white/[0.03]">
                        <h3 class="text-lg md:text-xl font-extrabold tracking-tight text-brand-950 dark:text-white"><span class="sr-only">{{ __('Стъпка :n', ['n' => $loop->iteration]) }}: </span>{{ $step['title'] }}</h3>
                        <p class="mt-2 text-gray-600 leading-relaxed dark:text-gray-300">{{ $step['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="relative py-20 md:py-28 surface-night grain on-dark text-white scroll-mt-24 overflow-hidden">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]" aria-hidden="true"></div>
    <div class="aurora-a absolute -bottom-40 -left-32 w-[32rem] h-[32rem] rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>
    <div class="aurora-b absolute -top-40 right-0 w-[26rem] h-[26rem] rounded-full bg-brand-400/10 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 items-start">
        <div class="lg:col-span-5 reveal lg:sticky lg:top-32">
            <span class="eyebrow">{{ __('Контакти') }}</span>
            <h2 class="display mt-4 text-3xl md:text-4xl">{{ $contactBlock['title'] }}</h2>
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
