@extends('layouts.public')

@section('title', __('Уебсайтове и маркетинг за бизнеса в България'))
@section('meta_description', __('Сайтове, SEO и имейл кампании за малки и средни бизнеси в България. Правим нови сайтове, поддържаме съществуващи и се грижим да ви намират в Google.'))

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white dark:from-ink-900 dark:via-ink-950 dark:to-ink-950">
    <div class="absolute inset-0 bg-grid [mask-image:radial-gradient(ellipse_at_top,black_30%,transparent_75%)]" aria-hidden="true"></div>
    <div class="aurora-a absolute -top-40 -right-32 w-[34rem] h-[34rem] rounded-full bg-brand-300/30 blur-3xl dark:bg-brand-600/20" aria-hidden="true"></div>
    <div class="aurora-b absolute top-40 -left-40 w-[26rem] h-[26rem] rounded-full bg-brand-100/60 blur-3xl dark:bg-brand-800/20" aria-hidden="true"></div>
    <div class="aurora-b absolute -bottom-32 right-1/3 w-[22rem] h-[22rem] rounded-full bg-brand-200/30 blur-3xl dark:bg-brand-500/10" style="animation-delay: -9s" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-12 md:pt-12 md:pb-16 grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">
        <div>
            <span class="rise-in inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-4 py-1.5 text-xs font-semibold text-brand-700 shadow-sm dark:border-white/15 dark:bg-white/5 dark:text-brand-200">
                <span class="relative flex w-1.5 h-1.5"><span class="absolute inset-0 rounded-full bg-brand-400 animate-ping motion-reduce:animate-none"></span><span class="relative w-1.5 h-1.5 rounded-full bg-brand-500"></span></span>
                {{ __('Уебсайтове и дигитален маркетинг') }}
            </span>
            <h1 class="rise-in mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-brand-950 leading-[1.05] dark:text-white" style="--d: 80ms">
                {{ $hero['title'] }} <span class="text-shimmer">{{ $hero['highlight'] }}</span>
            </h1>
            <p class="rise-in mt-6 text-xl sm:text-2xl font-semibold text-brand-950 leading-snug max-w-xl dark:text-white" style="--d: 180ms">{{ $hero['subtitle'] }}</p>
            @if(!empty($hero['text']))
            <p class="rise-in mt-4 text-lg text-gray-600 leading-relaxed max-w-xl dark:text-gray-300" style="--d: 260ms">{{ $hero['text'] }}</p>
            @endif
            <div class="rise-in mt-8 flex flex-col sm:flex-row gap-3" style="--d: 340ms">
                <a href="#kontakt" class="btn-shine inline-flex items-center justify-center gap-2 bg-brand-950 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-brand-800 transition-colors shadow-lg shadow-brand-950/20 dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100 dark:shadow-none">
                    {{ __('Свържете се с нас') }} <x-icon name="arrow-right" class="text-sm" />
                </a>
                <a href="{{ lroute('services') }}" class="btn-shine inline-flex items-center justify-center gap-2 bg-white border border-gray-200 text-brand-950 px-7 py-3.5 rounded-xl font-semibold hover:border-brand-300 hover:text-brand-700 transition-colors dark:bg-white/5 dark:border-white/15 dark:text-white dark:hover:border-brand-400 dark:hover:text-white">
                    {{ __('Вижте услугите') }}
                </a>
            </div>
            <ul class="rise-in mt-8 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-600 dark:text-gray-300" style="--d: 420ms">
                <li class="flex items-start gap-2"><x-icon name="circle-check" class="text-brand-500 dark:text-brand-400 shrink-0 mt-0.5" />{{ __('Визия, съобразена с идентичността на бранда') }}</li>
                <li class="flex items-start gap-2"><x-icon name="circle-check" class="text-brand-500 dark:text-brand-400 shrink-0 mt-0.5" />{{ __('Естетика и визуално въздействие') }}</li>
                <li class="flex items-start gap-2"><x-icon name="circle-check" class="text-brand-500 dark:text-brand-400 shrink-0 mt-0.5" />{{ __('Поддръжка и партньорство') }}</li>
                <li class="flex items-start gap-2"><x-icon name="circle-check" class="text-brand-500 dark:text-brand-400 shrink-0 mt-0.5" />{{ __('Ясни показатели за успех (KPI) още от старта') }}</li>
            </ul>
        </div>

        @if(!empty($hero['image']))
        <div class="relative rise-in" style="--d: 250ms">
            <img src="{{ asset($hero['image']) }}" alt="" width="1200" height="900" fetchpriority="high" class="w-full aspect-[4/3] object-cover rounded-2xl shadow-2xl shadow-brand-950/15 ring-1 ring-gray-200/70 dark:ring-white/10 dark:shadow-black/40">
        </div>
        @else
        {{-- Illustration: a website with an incoming inquiry --}}
        <div class="relative rise-in" style="--d: 250ms" aria-hidden="true">
          <div class="tilt" data-tilt>
            <div class="relative rounded-2xl bg-white shadow-2xl shadow-brand-950/15 ring-1 ring-gray-200/70 overflow-hidden dark:bg-ink-900 dark:ring-white/10 dark:shadow-black/40">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 bg-gray-50/80 dark:border-white/10 dark:bg-white/5">
                    <div class="flex gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-300"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-300"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-300"></span>
                    </div>
                    <div class="flex-1 flex items-center gap-2 rounded-md bg-white border border-gray-200 px-3 py-1 text-xs text-gray-500 dark:bg-white/5 dark:border-white/10 dark:text-gray-400">
                        <x-icon name="lock" class="text-[10px] text-emerald-500" /> {{ __('vashiat-biznes.bg') }}
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="h-3 w-24 rounded bg-brand-950/80 dark:bg-white/70"></div>
                        <div class="flex gap-3">
                            <div class="h-2 w-10 rounded bg-gray-200 dark:bg-white/15"></div>
                            <div class="h-2 w-10 rounded bg-gray-200 dark:bg-white/15"></div>
                            <div class="h-2 w-10 rounded bg-gray-200 dark:bg-white/15"></div>
                        </div>
                    </div>
                    <div class="sheen rounded-xl bg-brand-gradient p-6">
                        <div class="h-3.5 w-3/4 rounded bg-white/85 mb-3"></div>
                        <div class="h-3.5 w-1/2 rounded bg-white/60 mb-5"></div>
                        <div class="h-8 w-32 rounded-lg bg-white"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['fa-display', 'fa-magnifying-glass', 'fa-location-dot'] as $icon)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="w-7 h-7 rounded-lg bg-brand-100 flex items-center justify-center mb-2 dark:bg-white/10"><x-icon :name="$icon" class="text-xs text-brand-600 dark:text-brand-300" /></div>
                                <div class="h-2 w-full rounded bg-gray-200 mb-1.5 dark:bg-white/15"></div>
                                <div class="h-2 w-2/3 rounded bg-gray-200 dark:bg-white/15"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pop-in absolute -top-6 right-2 sm:-right-6" style="--d: 1100ms">
            <div class="animate-float rounded-xl bg-white shadow-xl ring-1 ring-gray-200/70 px-4 py-3 flex items-center gap-3 dark:bg-ink-800 dark:ring-white/10">
                <span class="relative flex w-9 h-9 rounded-full bg-emerald-50 items-center justify-center dark:bg-emerald-500/15">
                    <x-icon name="bell" class="text-emerald-600 text-sm dark:text-emerald-400" />
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-400 animate-ping motion-reduce:animate-none"></span>
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-ink-800"></span>
                </span>
                <span class="text-sm leading-tight"><strong class="block text-brand-950 dark:text-white">{{ __('Ново запитване') }}</strong><span class="text-gray-500 text-xs dark:text-gray-400">{{ __('от контактната форма') }}</span></span>
            </div>
            </div>

            <div class="pop-in absolute -bottom-6 left-2 sm:-left-8 rounded-xl bg-brand-950 text-white shadow-xl px-5 py-3.5 flex items-center gap-3 dark:bg-brand-800 dark:ring-1 dark:ring-white/10" style="--d: 1300ms">
                <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center"><x-icon name="chart-line" class="text-brand-300" /></span>
                <span class="text-sm font-semibold leading-tight">{!! __('Дизайн, код<br>и SEO') !!}</span>
                <svg viewBox="0 0 64 28" class="w-16 h-7 ml-1" fill="none"><path class="draw-line" d="M2 24 L14 18 L24 21 L36 11 L46 14 L62 3" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-brand-300)"/></svg>
            </div>
          </div>
        </div>
        @endif
    </div>
</section>

{{-- Industries: endless strip (static and wrapped when the visitor prefers less motion) --}}
@php($industries = site('industries'))
@if(!empty($industries))
<section aria-label="{{ __('Браншове, за които правим сайтове') }}" class="border-y border-gray-100 bg-white py-5 dark:border-white/10 dark:bg-ink-950">
    <div class="marquee overflow-hidden">
        <div class="marquee-track">
            @foreach([false, true] as $dup)
                <ul class="flex shrink-0 items-center gap-10 pr-10 {{ $dup ? 'marquee-dup' : '' }}" @if($dup) aria-hidden="true" @endif>
                    @foreach(array_merge($industries, $industries) as $industry)
                        <li class="flex items-center gap-3 whitespace-nowrap text-sm font-semibold text-gray-600 dark:text-gray-300 @if(! $dup && $loop->index >= count($industries)) marquee-dup @endif" @if(! $dup && $loop->index >= count($industries)) aria-hidden="true" @endif>
                            <span class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center dark:bg-white/10"><x-icon :name="$industry['icon']" class="text-brand-600 text-sm dark:text-brand-300" /></span>
                            {{ $industry['name'] }}
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Audience --}}
<section id="podhodyashto-za" class="py-12 md:py-16 bg-white scroll-mt-20 dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider dark:text-brand-300">{{ __('Подходящо за') }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $audience['title'] }}</h2>
            @if(!empty($audience['intro']))
            <p class="mt-4 text-gray-600 leading-relaxed dark:text-gray-300">{{ $audience['intro'] }}</p>
            @endif
        </div>

        <div class="mt-8 grid md:grid-cols-2 gap-6">
            @foreach($audience['cards'] as $card)
                <div class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-7 flex gap-5 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <div class="icon-pop w-12 h-12 shrink-0 rounded-xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
                        <x-icon :name="$card['icon']" class="text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-brand-950 dark:text-white">{{ $card['title'] }}</h3>
                        <p class="mt-2 text-gray-600 leading-relaxed dark:text-gray-300">{{ $card['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Services --}}
<section id="uslugi" class="py-12 md:py-16 bg-brand-50/60 scroll-mt-20 dark:bg-ink-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider dark:text-brand-300">{{ $sections['services_eyebrow'] }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $sections['services_title'] }}</h2>
        </div>

        <div class="mt-8 grid md:grid-cols-2 gap-6 items-start">
            @foreach($services as $service)
                <article class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 2) * 100 }}ms">
                    <div class="flex items-start gap-5">
                        <div class="icon-pop w-12 h-12 shrink-0 rounded-xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
                            <x-icon :name="$service['icon']" class="text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-brand-950 dark:text-white">{{ $service['title'] }}</h3>
                            <p class="mt-1 text-sm font-semibold text-brand-600 dark:text-brand-300">{{ $service['tag'] }}</p>
                        </div>
                    </div>
                    <p class="mt-5 text-gray-600 leading-relaxed dark:text-gray-300">{{ $service['description'] }}</p>
                    <details class="group mt-5 border-t border-gray-100 pt-4 dark:border-white/10">
                        <summary class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 cursor-pointer list-none [&::-webkit-details-marker]:hidden dark:text-brand-300">
                            <span class="group-open:hidden">{{ __('Вижте повече') }}</span><span class="hidden group-open:inline">{{ __('Скрийте') }}</span>
                            <x-icon name="chevron-down" class="text-xs transition-transform group-open:rotate-180" />
                        </summary>
                        <p class="mt-4 text-gray-600 leading-relaxed dark:text-gray-300">{{ $service['details'] }}</p>
                        <ul class="mt-4 space-y-2">
                            @foreach($service['includes'] as $item)
                                <li class="text-sm text-gray-700 flex items-start gap-2.5 dark:text-gray-200"><x-icon name="check" class="text-brand-500 text-xs mt-1 dark:text-brand-400" />{{ $item }}</li>
                            @endforeach
                        </ul>
                    </details>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Process --}}
<section id="proces" class="relative py-12 md:py-16 bg-white scroll-mt-20 overflow-hidden dark:bg-ink-950">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider dark:text-brand-300">{{ $sections['process_eyebrow'] }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $sections['process_title'] }}</h2>
        </div>

        <ol class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($process as $step)
                <li class="reveal card-hover relative rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 3) * 100 }}ms">
                    <span class="step-bar absolute inset-x-0 top-0 h-1 rounded-t-2xl bg-gradient-to-r from-brand-600 via-brand-400 to-brand-300" aria-hidden="true"></span>
                    <div class="flex items-center justify-between">
                        <span class="icon-pop w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center dark:bg-white/10"><x-icon :name="$step['icon']" class="text-brand-600 dark:text-brand-300" /></span>
                        <span class="text-4xl font-extrabold text-gradient" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-bold text-brand-950 dark:text-white"><span class="sr-only">{{ __('Стъпка :n', ['n' => $loop->iteration]) }}: </span>{{ $step['title'] }}</h3>
                    <p class="mt-2 text-gray-600 leading-relaxed dark:text-gray-300">{{ $step['description'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="relative py-12 md:py-16 bg-brand-gradient bg-animated text-white scroll-mt-20 overflow-hidden">
    <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
    <div class="aurora-a absolute -bottom-40 -left-32 w-[30rem] h-[30rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="aurora-b absolute -top-40 right-0 w-[24rem] h-[24rem] rounded-full bg-brand-400/15 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
        <div class="reveal">
            <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight">{{ $sections['contact_title'] }}</h2>
            <p class="mt-5 text-lg text-brand-100/90 max-w-md">{{ $sections['contact_text'] }}</p>
            <ul class="mt-8 space-y-4">
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="envelope" class="text-brand-200" /></span>
                    <a href="mailto:{{ $contact['email'] }}" class="hover:underline">{{ $contact['email'] }}</a>
                </li>
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="phone" class="text-brand-200" /></span>
                    <span class="flex flex-col">
                        @foreach($contact['phones'] as $phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:underline">{{ $phone }}</a>
                        @endforeach
                    </span>
                </li>
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="location-dot" class="text-brand-200" /></span>
                    {{ __($contact['city']) }}, {{ __('България') }}
                </li>
            </ul>
        </div>

        <div class="reveal shadow-2xl shadow-black/30 rounded-2xl" style="--reveal-delay: 120ms">
            @include('partials/contact-form')
        </div>
    </div>
</section>
@endsection
