@extends('layouts.public')

@section('title', 'Уебсайтове и маркетинг за бизнеса в България')
@section('meta_description', 'Сайтове, SEO и имейл кампании за малки и средни фирми в България. Правим нови сайтове, поддържаме съществуващи и се грижим да ви намират в Google. Първата консултация е безплатна.')

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white">
    <div class="absolute inset-0 bg-grid [mask-image:radial-gradient(ellipse_at_top,black_30%,transparent_75%)]" aria-hidden="true"></div>
    <div class="absolute -top-40 -right-32 w-[34rem] h-[34rem] rounded-full bg-brand-300/30 blur-3xl" aria-hidden="true"></div>
    <div class="absolute top-40 -left-40 w-[26rem] h-[26rem] rounded-full bg-brand-100/60 blur-3xl" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-20 md:pt-24 md:pb-28 grid lg:grid-cols-2 gap-14 items-center">
        <div class="reveal">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-4 py-1.5 text-xs font-semibold text-brand-700 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Уебсайтове и дигитален маркетинг
            </span>
            <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-brand-950 leading-[1.05]">
                {{ $hero['title'] }} <span class="text-gradient">{{ $hero['highlight'] }}</span>
            </h1>
            <p class="mt-6 text-xl sm:text-2xl font-semibold text-brand-950 leading-snug max-w-xl">{{ $hero['subtitle'] }}</p>
            <p class="mt-4 text-lg text-gray-600 leading-relaxed max-w-xl">{{ $hero['text'] }}</p>
            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="#kontakt" class="inline-flex items-center justify-center gap-2 bg-brand-950 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-brand-800 transition-colors shadow-lg shadow-brand-950/20">
                    Свържете се с нас <x-icon name="arrow-right" class="text-sm" />
                </a>
                <a href="{{ route('services') }}" class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 text-brand-950 px-7 py-3.5 rounded-xl font-semibold hover:border-brand-300 hover:text-brand-700 transition-colors">
                    Вижте услугите
                </a>
            </div>
            <ul class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm text-gray-600">
                <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-brand-500" />Първата консултация е безплатна</li>
                <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-brand-500" />Отговаряме до един работен ден</li>
                <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-brand-500" />Без посредници</li>
            </ul>
        </div>

        {{-- Illustration: a website with an incoming inquiry --}}
        <div class="relative reveal" style="--reveal-delay: 150ms" aria-hidden="true">
            <div class="relative rounded-2xl bg-white shadow-2xl shadow-brand-950/15 ring-1 ring-gray-200/70 overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 bg-gray-50/80">
                    <div class="flex gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-300"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-300"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-300"></span>
                    </div>
                    <div class="flex-1 flex items-center gap-2 rounded-md bg-white border border-gray-200 px-3 py-1 text-xs text-gray-500">
                        <x-icon name="lock" class="text-[10px] text-emerald-500" /> vashiat-biznes.bg
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="h-3 w-24 rounded bg-brand-950/80"></div>
                        <div class="flex gap-3">
                            <div class="h-2 w-10 rounded bg-gray-200"></div>
                            <div class="h-2 w-10 rounded bg-gray-200"></div>
                            <div class="h-2 w-10 rounded bg-gray-200"></div>
                        </div>
                    </div>
                    <div class="rounded-xl bg-brand-gradient p-6">
                        <div class="h-3.5 w-3/4 rounded bg-white/85 mb-3"></div>
                        <div class="h-3.5 w-1/2 rounded bg-white/60 mb-5"></div>
                        <div class="h-8 w-32 rounded-lg bg-white"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['fa-display', 'fa-magnifying-glass', 'fa-location-dot'] as $icon)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                                <div class="w-7 h-7 rounded-lg bg-brand-100 flex items-center justify-center mb-2"><x-icon :name="$icon" class="text-xs text-brand-600" /></div>
                                <div class="h-2 w-full rounded bg-gray-200 mb-1.5"></div>
                                <div class="h-2 w-2/3 rounded bg-gray-200"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="absolute -top-6 right-2 sm:-right-6 animate-float rounded-xl bg-white shadow-xl ring-1 ring-gray-200/70 px-4 py-3 flex items-center gap-3">
                <span class="relative flex w-9 h-9 rounded-full bg-emerald-50 items-center justify-center">
                    <x-icon name="bell" class="text-emerald-600 text-sm" />
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </span>
                <span class="text-sm leading-tight"><strong class="block text-brand-950">Ново запитване</strong><span class="text-gray-500 text-xs">от контактната форма</span></span>
            </div>

            <div class="absolute -bottom-6 left-2 sm:-left-8 rounded-xl bg-brand-950 text-white shadow-xl px-5 py-3.5 flex items-center gap-3">
                <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center"><x-icon name="chart-line" class="text-brand-300" /></span>
                <span class="text-sm font-semibold leading-tight">Дизайн, код<br>и SEO</span>
            </div>
        </div>
    </div>
</section>

{{-- Audience --}}
<section id="podhodyashto-za" class="py-20 md:py-28 bg-white scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider">Подходящо за</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950">{{ $audience['title'] }}</h2>
            <p class="mt-4 text-gray-600 leading-relaxed">{{ $audience['intro'] }}</p>
        </div>

        <div class="mt-12 grid md:grid-cols-2 gap-6">
            @foreach($audience['cards'] as $card)
                <div class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-7 flex gap-5" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <div class="w-12 h-12 shrink-0 rounded-xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
                        <x-icon :name="$card['icon']" class="text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-brand-950">{{ $card['title'] }}</h3>
                        <p class="mt-2 text-gray-600 leading-relaxed">{{ $card['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10 flex flex-wrap gap-2.5 reveal">
            @foreach($industries as $industry)
                <span class="inline-flex items-center gap-2 rounded-full bg-gray-50 border border-gray-100 px-3.5 py-1.5 text-sm text-gray-700">
                    <x-icon :name="$industry['icon']" class="text-brand-500 text-xs" />{{ $industry['name'] }}
                </span>
            @endforeach
        </div>
    </div>
</section>

{{-- Services --}}
<section id="uslugi" class="py-20 md:py-28 bg-brand-50/60 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider">Услуги</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950">С какво можем да ви помогнем да се отличите</h2>
        </div>

        <div class="mt-14 grid md:grid-cols-2 gap-6 items-start">
            @foreach($services as $service)
                <article class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-7" style="--reveal-delay: {{ ($loop->index % 2) * 100 }}ms">
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 shrink-0 rounded-xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
                            <x-icon :name="$service['icon']" class="text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-brand-950">{{ $service['title'] }}</h3>
                            <p class="mt-1 text-sm font-semibold text-brand-600">{{ $service['tag'] }}</p>
                        </div>
                    </div>
                    <p class="mt-5 text-gray-600 leading-relaxed">{{ $service['description'] }}</p>
                    <details class="group mt-5 border-t border-gray-100 pt-4">
                        <summary class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                            <span class="group-open:hidden">Вижте повече</span><span class="hidden group-open:inline">Скрийте</span>
                            <x-icon name="chevron-down" class="text-xs transition-transform group-open:rotate-180" />
                        </summary>
                        <p class="mt-4 text-gray-600 leading-relaxed">{{ $service['details'] }}</p>
                        <ul class="mt-4 space-y-2">
                            @foreach($service['includes'] as $item)
                                <li class="text-sm text-gray-700 flex items-start gap-2.5"><x-icon name="check" class="text-brand-500 text-xs mt-1" />{{ $item }}</li>
                            @endforeach
                        </ul>
                    </details>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Process --}}
<section id="proces" class="relative py-20 md:py-28 bg-white scroll-mt-20 overflow-hidden">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider">From Concept to Implementation</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950">Работен процес</h2>
        </div>

        <ol class="mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($process as $step)
                <li class="reveal relative rounded-2xl border border-gray-200 bg-white p-6" style="--reveal-delay: {{ ($loop->index % 3) * 100 }}ms">
                    <div class="flex items-center justify-between">
                        <span class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center"><x-icon :name="$step['icon']" class="text-brand-600" /></span>
                        <span class="text-3xl font-extrabold text-brand-100" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-bold text-brand-950"><span class="sr-only">Стъпка {{ $loop->iteration }}: </span>{{ $step['title'] }}</h3>
                    <p class="mt-2 text-gray-600 leading-relaxed">{{ $step['description'] }}</p>
                </li>
            @endforeach
        </ol>

        <div class="mt-10 reveal relative overflow-hidden rounded-2xl bg-brand-950 text-white p-7 md:p-9 flex flex-col md:flex-row md:items-center gap-5">
            <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
            <span class="relative w-14 h-14 shrink-0 rounded-2xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="crown" class="text-2xl text-brand-200" /></span>
            <div class="relative">
                <h3 class="text-2xl font-extrabold">{{ $promise['title'] }}</h3>
                <p class="mt-2 text-gray-300 leading-relaxed max-w-3xl">{{ $promise['text'] }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="relative py-20 md:py-28 bg-brand-gradient text-white scroll-mt-20 overflow-hidden">
    <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
    <div class="absolute -bottom-40 -left-32 w-[30rem] h-[30rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
        <div class="reveal">
            <span class="text-brand-300 text-sm font-bold uppercase tracking-wider">Контакт</span>
            <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight">Да поговорим.</h2>
            <p class="mt-5 text-lg text-brand-100/90 max-w-md">
                Оставете име и телефон и изберете с какво да помогнем. Ще ви се обадим до един работен ден, а първият разговор е безплатен.
            </p>
            <ul class="mt-8 space-y-4">
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="envelope" class="text-brand-200" /></span>
                    <a href="mailto:{{ $contact['email'] }}" class="hover:underline">{{ $contact['email'] }}</a>
                </li>
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="phone" class="text-brand-200" /></span>
                    {{ $contact['phone'] }}
                </li>
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="location-dot" class="text-brand-200" /></span>
                    {{ $contact['city'] }}, България
                </li>
            </ul>
        </div>

        <div class="reveal shadow-2xl shadow-black/30 rounded-2xl" style="--reveal-delay: 120ms">
            @include('partials/contact-form')
        </div>
    </div>
</section>
@endsection
