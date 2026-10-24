@extends('layouts.public')

@section('title', __('За нас'))
@section('meta_description', __('Creatium Lab прави сайтове и маркетинг за малки бизнеси в България: от първата страница до клиентите, които я намират в Google.'))

@php
    // Номерацията на секциите следва реда им; текстът „За нас“ може да е празен в админа.
    $section = 0;
    $number = function () use (&$section) { return str_pad((string) ++$section, 2, '0', STR_PAD_LEFT) . ' /'; };
@endphp

@section('content')
@include('partials/page-header', ['eyebrow' => 'Creatium Lab', 'heading' => __('За нас'), 'sub' => filled($about['subtitle'] ?? null) ? $about['subtitle'] : __('Creatium Lab прави сайтове и маркетинг за малки бизнеси в България: от първата страница до клиентите, които я намират в Google.')])

@if(!empty($about['paragraphs']))
<section class="py-20 md:py-28 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-10 lg:gap-16">
        <div class="lg:col-span-4 reveal">
            <p class="kicker kicker-light">{{ $number() }} {{ __('Кои сме') }}</p>
        </div>
        <div class="lg:col-span-8 space-y-6 text-lg md:text-xl leading-relaxed text-gray-700 dark:text-gray-200 reveal">
            @foreach($about['paragraphs'] as $paragraph)
                <p class="{{ $loop->first ? 'text-2xl md:text-3xl font-bold tracking-tight text-brand-950 dark:text-white leading-snug' : '' }}">{{ $paragraph }}</p>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- The team --}}
<section class="relative py-20 md:py-28 bg-brand-50/60 dark:bg-ink-900">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-3xl">
            <p class="kicker kicker-light">{{ $number() }} {{ __('Екипът') }}</p>
            <h2 class="display mt-5 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ __('Говорите с хората, които правят работата.') }}</h2>
        </div>
        <div class="mt-12 grid md:grid-cols-2 gap-6">
            @foreach($team as $member)
                @php
                    $initials = collect(preg_split('/\s+/u', trim((string) ($member['name'] ?? '')), -1, PREG_SPLIT_NO_EMPTY))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                    $linkedin = str_starts_with((string) ($member['linkedin'] ?? ''), 'https://') ? $member['linkedin'] : null;
                @endphp
                <article class="reveal card-hover flex flex-col rounded-3xl border border-gray-200 bg-white p-7 md:p-9 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    @if(!empty($member['name']))
                        <div class="flex items-center gap-5">
                            @if(!empty($member['image']))
                                <img src="{{ asset($member['image']) }}" alt="{{ $member['name'] }}" width="112" height="112" loading="lazy" class="w-24 h-24 md:w-28 md:h-28 shrink-0 rounded-2xl object-cover ring-1 ring-gray-200 dark:ring-white/20">
                            @else
                                <span class="w-24 h-24 md:w-28 md:h-28 shrink-0 rounded-2xl bg-brand-gradient flex items-center justify-center text-2xl font-extrabold text-white" aria-hidden="true">{{ $initials }}</span>
                            @endif
                            <div>
                                <p class="text-xs font-bold tracking-[0.18em] text-brand-600 dark:text-brand-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="mt-1 text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $member['name'] }}</h3>
                                <p class="mt-1 font-semibold text-brand-600 dark:text-brand-300">{{ $member['role'] }}</p>
                            </div>
                        </div>
                    @else
                        <h3 class="text-2xl font-extrabold tracking-tight text-brand-600 dark:text-brand-300">{{ $member['role'] }}</h3>
                    @endif
                    <p class="mt-6 text-lg font-semibold text-brand-950 dark:text-white">{{ $member['description'] }}</p>
                    @if(!empty($member['bio']))
                        <div class="mt-4 space-y-3 leading-relaxed text-gray-600 dark:text-gray-300">
                            @foreach((array) $member['bio'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($member['skills']))
                        <ul class="mt-6 flex flex-wrap gap-2">
                            @foreach((array) $member['skills'] as $skill)
                                <li class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">{{ $skill }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($linkedin)
                        <a href="{{ $linkedin }}" target="_blank" rel="noopener" class="mt-auto pt-6 inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">
                            {{ __('Профил в LinkedIn') }} <x-icon name="arrow-up-right-from-square" class="text-xs" />
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- How we think (same principles as the home page) --}}
@if(!empty($trust['principle_items']))
<section class="py-20 md:py-28 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-3xl">
            <p class="kicker kicker-light">{{ $number() }} {{ $trust['principles_eyebrow'] }}</p>
            <h2 class="display mt-5 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $trust['principles_title'] }}</h2>
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
        <a href="{{ lroute('home') }}#s-kogo-rabotim" class="reveal mt-10 inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-950 dark:text-brand-300 dark:hover:text-white">
            {{ $trust['fit_eyebrow'] }} <x-icon name="arrow-right" class="text-xs" />
        </a>
    </div>
</section>
@endif

{{-- Въпросите са на отделна страница („Често задавани въпроси“, /vaprosi). --}}
<section class="py-16 md:py-20 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col items-center gap-4 rounded-3xl border border-gray-200 bg-white p-7 text-center sm:flex-row sm:justify-between sm:text-left dark:border-white/10 dark:bg-white/[0.03]">
            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Имате въпроси?') }}</h2>
                <p class="mt-1 text-gray-600 dark:text-gray-300">{{ __('Събрали сме отговорите на най-честите въпроси за цени, срокове и работа с нас.') }}</p>
            </div>
            <a href="{{ lroute('faq') }}" class="btn-shine shrink-0 inline-flex items-center gap-2 rounded-xl bg-brand-950 px-6 py-3.5 font-semibold text-white hover:bg-brand-800 dark:bg-white dark:text-brand-950">{{ __('Често задавани въпроси') }} <x-icon name="arrow-right" class="text-sm" /></a>
        </div>
    </div>
</section>
@endsection
