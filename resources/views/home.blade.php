@extends('layouts.public')

@section('title', __('Уебсайтове и маркетинг за бизнеса в България'))
@section('meta_description', __('Сайтове, SEO и имейл кампании за малки и средни бизнеси в България. Правим нови сайтове, поддържаме съществуващи и се грижим да ви намират в Google. Първата консултация е безплатна.'))

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white dark:from-ink-900 dark:via-ink-950 dark:to-ink-950">
    <div class="absolute inset-0 bg-grid [mask-image:radial-gradient(ellipse_at_top,black_30%,transparent_75%)]" aria-hidden="true"></div>
    <div class="absolute -top-40 -right-32 w-[34rem] h-[34rem] rounded-full bg-brand-300/30 blur-3xl dark:bg-brand-600/20" aria-hidden="true"></div>
    <div class="absolute top-40 -left-40 w-[26rem] h-[26rem] rounded-full bg-brand-100/60 blur-3xl dark:bg-brand-800/20" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20 md:pt-16 md:pb-28 grid lg:grid-cols-2 gap-14 items-center">
        <div class="reveal">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-4 py-1.5 text-xs font-semibold text-brand-700 shadow-sm dark:border-white/15 dark:bg-white/5 dark:text-brand-200">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                {{ __('Уебсайтове и дигитален маркетинг') }}
            </span>
            <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-brand-950 leading-[1.05] dark:text-white">
                {{ $hero['title'] }} <span class="text-gradient">{{ $hero['highlight'] }}</span>
            </h1>
            <p class="mt-6 text-xl sm:text-2xl font-semibold text-brand-950 leading-snug max-w-xl dark:text-white">{{ $hero['subtitle'] }}</p>
            @if(!empty($hero['text']))
            <p class="mt-4 text-lg text-gray-600 leading-relaxed max-w-xl dark:text-gray-300">{{ $hero['text'] }}</p>
            @endif
            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="#kontakt" class="inline-flex items-center justify-center gap-2 bg-brand-950 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-brand-800 transition-colors shadow-lg shadow-brand-950/20 dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100 dark:shadow-none">
                    {{ __('Свържете се с нас') }} <x-icon name="arrow-right" class="text-sm" />
                </a>
                <a href="{{ lroute('services') }}" class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 text-brand-950 px-7 py-3.5 rounded-xl font-semibold hover:border-brand-300 hover:text-brand-700 transition-colors dark:bg-white/5 dark:border-white/15 dark:text-white dark:hover:border-brand-400 dark:hover:text-white">
                    {{ __('Вижте услугите') }}
                </a>
            </div>
            <ul class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-600 dark:text-gray-300">
                <li class="flex items-start gap-2"><x-icon name="circle-check" class="text-brand-500 dark:text-brand-400 shrink-0 mt-0.5" />{{ __('Визия, съобразена с идентичността на бранда') }}</li>
                <li class="flex items-start gap-2"><x-icon name="circle-check" class="text-brand-500 dark:text-brand-400 shrink-0 mt-0.5" />{{ __('Естетика и визуално въздействие') }}</li>
                <li class="flex items-start gap-2"><x-icon name="circle-check" class="text-brand-500 dark:text-brand-400 shrink-0 mt-0.5" />{{ __('Поддръжка и партньорство') }}</li>
                <li class="flex items-start gap-2"><x-icon name="circle-check" class="text-brand-500 dark:text-brand-400 shrink-0 mt-0.5" />{{ __('Ясни показатели за успех (KPI) още от старта') }}</li>
            </ul>
        </div>

        @if(!empty($hero['image']))
        <div class="relative reveal" style="--reveal-delay: 150ms">
            <img src="{{ asset($hero['image']) }}" alt="" width="1200" height="900" fetchpriority="high" class="w-full aspect-[4/3] object-cover rounded-2xl shadow-2xl shadow-brand-950/15 ring-1 ring-gray-200/70 dark:ring-white/10 dark:shadow-black/40">
        </div>
        @else
        {{-- Illustration: a website with an incoming inquiry --}}
        <div class="relative reveal" style="--reveal-delay: 150ms" aria-hidden="true">
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
                    <div class="rounded-xl bg-brand-gradient p-6">
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

            <div class="absolute -top-6 right-2 sm:-right-6 animate-float rounded-xl bg-white shadow-xl ring-1 ring-gray-200/70 px-4 py-3 flex items-center gap-3 dark:bg-ink-800 dark:ring-white/10">
                <span class="relative flex w-9 h-9 rounded-full bg-emerald-50 items-center justify-center dark:bg-emerald-500/15">
                    <x-icon name="bell" class="text-emerald-600 text-sm dark:text-emerald-400" />
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-ink-800"></span>
                </span>
                <span class="text-sm leading-tight"><strong class="block text-brand-950 dark:text-white">{{ __('Ново запитване') }}</strong><span class="text-gray-500 text-xs dark:text-gray-400">{{ __('от контактната форма') }}</span></span>
            </div>

            <div class="absolute -bottom-6 left-2 sm:-left-8 rounded-xl bg-brand-950 text-white shadow-xl px-5 py-3.5 flex items-center gap-3 dark:bg-brand-800 dark:ring-1 dark:ring-white/10">
                <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center"><x-icon name="chart-line" class="text-brand-300" /></span>
                <span class="text-sm font-semibold leading-tight">{!! __('Дизайн, код<br>и SEO') !!}</span>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- Audience --}}
<section id="podhodyashto-za" class="py-20 md:py-28 bg-white scroll-mt-20 dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider dark:text-brand-300">{{ __('Подходящо за') }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $audience['title'] }}</h2>
            @if(!empty($audience['intro']))
            <p class="mt-4 text-gray-600 leading-relaxed dark:text-gray-300">{{ $audience['intro'] }}</p>
            @endif
        </div>

        <div class="mt-12 grid md:grid-cols-2 gap-6">
            @foreach($audience['cards'] as $card)
                <div class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-7 flex gap-5 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <div class="w-12 h-12 shrink-0 rounded-xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
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
<section id="uslugi" class="py-20 md:py-28 bg-brand-50/60 scroll-mt-20 dark:bg-ink-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider dark:text-brand-300">{{ $sections['services_eyebrow'] }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $sections['services_title'] }}</h2>
        </div>

        <div class="mt-14 grid md:grid-cols-2 gap-6 items-start">
            @foreach($services as $service)
                <article class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 2) * 100 }}ms">
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 shrink-0 rounded-xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
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
<section id="proces" class="relative py-20 md:py-28 bg-white scroll-mt-20 overflow-hidden dark:bg-ink-950">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider dark:text-brand-300">{{ $sections['process_eyebrow'] }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $sections['process_title'] }}</h2>
        </div>

        <ol class="mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($process as $step)
                <li class="reveal relative rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 3) * 100 }}ms">
                    <div class="flex items-center justify-between">
                        <span class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center dark:bg-white/10"><x-icon :name="$step['icon']" class="text-brand-600 dark:text-brand-300" /></span>
                        <span class="text-3xl font-extrabold text-brand-400" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-bold text-brand-950 dark:text-white"><span class="sr-only">{{ __('Стъпка :n', ['n' => $loop->iteration]) }}: </span>{{ $step['title'] }}</h3>
                    <p class="mt-2 text-gray-600 leading-relaxed dark:text-gray-300">{{ $step['description'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="relative py-20 md:py-28 bg-brand-gradient text-white scroll-mt-20 overflow-hidden">
    <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
    <div class="absolute -bottom-40 -left-32 w-[30rem] h-[30rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
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
