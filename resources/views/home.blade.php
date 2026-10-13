@extends('layouts.public')

@section('title', __('Уебсайтове и маркетинг за бизнеса в България'))
@section('meta_description', __('Сайтове, SEO и имейл кампании за малки и средни бизнеси в България. Правим нови сайтове, поддържаме съществуващи и се грижим да ви намират в Google.'))

@section('content')

{{-- Hero: dark in both themes --}}
<section class="relative overflow-hidden surface-night grain on-dark text-white">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_25%,transparent_72%)]" aria-hidden="true"></div>
    <div class="hidden md:block aurora-a absolute -top-48 right-[-10rem] w-[40rem] h-[40rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="hidden md:block aurora-b absolute top-1/3 -left-48 w-[30rem] h-[30rem] rounded-full bg-brand-700/30 blur-3xl" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-header pb-28 md:pb-36 grid lg:grid-cols-12 gap-14 lg:gap-10 items-center">
        <div class="lg:col-span-6">
            <span class="rise-in badge-glow inline-flex items-center gap-2.5 rounded-full px-4 py-1.5 text-xs font-semibold text-brand-100">
                <span class="relative flex w-2 h-2"><span class="absolute inset-0 rounded-full bg-brand-300 animate-ping motion-reduce:animate-none"></span><span class="relative w-2 h-2 rounded-full bg-brand-300"></span></span>
                {{ __('Уебсайтове и дигитален маркетинг') }}
            </span>
            <h1 class="display rise-text mt-7 text-5xl sm:text-6xl xl:text-7xl" style="--d: 80ms">
                {{ $hero['title'] }} <span class="text-shimmer">{{ $hero['highlight'] }}</span>
            </h1>
            <p class="rise-text mt-7 text-xl sm:text-2xl font-semibold text-white/90 leading-snug max-w-xl text-pretty" style="--d: 180ms">{{ $hero['subtitle'] }}</p>
            @if(!empty($hero['text']))
            <p class="rise-text mt-4 text-lg text-gray-400 leading-relaxed max-w-xl" style="--d: 240ms">{{ $hero['text'] }}</p>
            @endif
            <div class="rise-text mt-10 flex flex-col sm:flex-row gap-3" style="--d: 320ms">
                <a href="#kontakt" class="btn-shine btn-glow inline-flex items-center justify-center gap-2 bg-white text-brand-950 px-7 py-3.5 rounded-xl font-semibold">
                    {{ __('Свържете се с нас') }} <x-icon name="arrow-right" class="text-sm" />
                </a>
                <a href="{{ lroute('services') }}" class="relative edge glass inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl font-semibold text-white hover:bg-white/10 transition-colors">
                    {{ __('Вижте услугите') }}
                </a>
            </div>
            <ul class="rise-text mt-10 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm text-gray-300" style="--d: 400ms">
                <li class="flex items-start gap-2.5"><x-icon name="circle-check" class="text-brand-300 shrink-0 mt-0.5" />{{ __('Визия, съобразена с идентичността на бранда') }}</li>
                <li class="flex items-start gap-2.5"><x-icon name="circle-check" class="text-brand-300 shrink-0 mt-0.5" />{{ __('Естетика и визуално въздействие') }}</li>
                <li class="flex items-start gap-2.5"><x-icon name="circle-check" class="text-brand-300 shrink-0 mt-0.5" />{{ __('Поддръжка и партньорство') }}</li>
                <li class="flex items-start gap-2.5"><x-icon name="circle-check" class="text-brand-300 shrink-0 mt-0.5" />{{ __('Ясни показатели за успех (KPI) още от старта') }}</li>
            </ul>
        </div>

        <div class="lg:col-span-6 relative rise-in" style="--d: 260ms" @if(empty($hero['image'])) aria-hidden="true" @endif>
            <div class="absolute -inset-8 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>
            <div class="tilt" data-tilt>
                <div class="edge glass relative rounded-[1.75rem] p-2 shadow-[0_40px_120px_-30px_rgb(0_0_0/0.8)]">
                    @if(!empty($hero['image']))
                        <img src="{{ asset($hero['image']) }}" alt="" width="1200" height="900" fetchpriority="high" class="w-full aspect-[4/3] object-cover rounded-[1.35rem]">
                    @else
                        {{-- Illustration: a website with an incoming inquiry --}}
                        <div class="rounded-[1.35rem] bg-ink-900/90 overflow-hidden ring-1 ring-white/5">
                            <div class="flex items-center gap-3 px-4 py-3 border-b border-white/5">
                                <div class="flex gap-1.5">
                                    <span class="w-3 h-3 rounded-full bg-white/15"></span>
                                    <span class="w-3 h-3 rounded-full bg-white/15"></span>
                                    <span class="w-3 h-3 rounded-full bg-white/15"></span>
                                </div>
                                <div class="flex-1 flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 text-xs text-gray-400">
                                    <x-icon name="lock" class="text-[10px] text-emerald-400" /> {{ __('vashiat-biznes.bg') }}
                                </div>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="h-3 w-24 rounded-full bg-white/70"></div>
                                    <div class="flex gap-3">
                                        <div class="h-2 w-10 rounded-full bg-white/15"></div>
                                        <div class="h-2 w-10 rounded-full bg-white/15"></div>
                                        <div class="h-2 w-10 rounded-full bg-white/15"></div>
                                    </div>
                                </div>
                                <div class="sheen rounded-2xl bg-brand-gradient p-6 ring-1 ring-white/10">
                                    <div class="h-3.5 w-3/4 rounded-full bg-white/85 mb-3"></div>
                                    <div class="h-3.5 w-1/2 rounded-full bg-white/55 mb-6"></div>
                                    <div class="h-9 w-32 rounded-lg bg-white"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach(['fa-display', 'fa-magnifying-glass', 'fa-location-dot'] as $icon)
                                        <div class="rounded-xl bg-white/[0.04] ring-1 ring-white/5 p-3">
                                            <div class="w-7 h-7 rounded-lg bg-brand-500/20 flex items-center justify-center mb-2.5"><x-icon :name="$icon" class="text-xs text-brand-200" /></div>
                                            <div class="h-2 w-full rounded-full bg-white/15 mb-1.5"></div>
                                            <div class="h-2 w-2/3 rounded-full bg-white/10"></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if(empty($hero['image']))
                <div class="pop-in absolute -top-7 right-2 sm:-right-6" style="--d: 1100ms">
                    <div class="relative animate-float edge glass rounded-2xl bg-ink-900/60 px-4 py-3 flex items-center gap-3 shadow-[0_20px_50px_-20px_rgb(0_0_0/0.8)]">
                        <span class="relative flex w-9 h-9 rounded-full bg-emerald-400/15 items-center justify-center">
                            <x-icon name="bell" class="text-emerald-400 text-sm" />
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-400 animate-ping motion-reduce:animate-none"></span>
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-400 ring-2 ring-ink-900"></span>
                        </span>
                        <span class="text-sm leading-tight"><strong class="block text-white">{{ __('Ново запитване') }}</strong><span class="text-gray-400 text-xs">{{ __('от контактната форма') }}</span></span>
                    </div>
                </div>

                <div class="pop-in edge glass absolute -bottom-8 left-2 sm:-left-8 rounded-2xl bg-ink-900/60 px-5 py-3.5 flex items-center gap-3 shadow-[0_20px_50px_-20px_rgb(0_0_0/0.8)]" style="--d: 1300ms">
                    <span class="w-9 h-9 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon name="chart-line" class="text-brand-200" /></span>
                    <span class="text-sm font-semibold leading-tight">{!! __('Дизайн, код<br>и SEO') !!}</span>
                    <svg viewBox="0 0 64 28" class="w-16 h-7 ml-1" fill="none"><path class="draw-line" d="M2 24 L14 18 L24 21 L36 11 L46 14 L62 3" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-brand-300)"/></svg>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Promises (floats over the hero's edge) + audience --}}
<section id="podhodyashto-za" class="relative bg-white pb-20 md:pb-28 scroll-mt-24 dark:bg-ink-950">
    <div class="relative z-10 -mt-12 md:-mt-14 px-4 sm:px-6 lg:px-8">
        <ul class="reveal max-w-6xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-px overflow-hidden rounded-2xl bg-gray-200/70 ring-1 ring-gray-200/70 shadow-[0_30px_70px_-30px_rgb(15_26_43/0.45)] dark:bg-white/10 dark:ring-white/10 dark:shadow-[0_30px_70px_-30px_rgb(0_0_0/0.9)]">
            @foreach([['reply', __('Отговаряме до един работен ден')], ['file-contract', __('Фиксирана цена, преди да започнем')], ['eye', __('Виждате сайта, преди да е готов')], ['users', __('Работите директно с нас двамата')]] as [$icon, $text])
                <li class="flex items-center gap-3 bg-white px-4 py-5 md:px-6 md:py-6 dark:bg-ink-900">
                    <span class="w-10 h-10 shrink-0 rounded-xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20"><x-icon :name="$icon" class="text-white text-sm" /></span>
                    <span class="text-sm font-semibold leading-snug text-brand-950 dark:text-white">{{ $text }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 md:pt-28">
        <div class="max-w-2xl reveal">
            <span class="eyebrow">{{ __('Подходящо за') }}</span>
            <h2 class="display mt-4 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $audience['title'] }}</h2>
            @if(!empty($audience['intro']))
            <p class="mt-5 text-lg text-gray-600 leading-relaxed dark:text-gray-300">{{ $audience['intro'] }}</p>
            @endif
        </div>

        <div class="mt-12 grid md:grid-cols-2 gap-6">
            @foreach($audience['cards'] as $card)
                <div class="reveal card-hover overflow-hidden rounded-3xl border border-gray-200 bg-gradient-to-b from-white to-brand-50/50 p-8 md:p-10 dark:border-white/10 dark:from-white/[0.04] dark:to-white/[0.01]" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <x-icon :name="$card['icon']" class="pointer-events-none absolute -right-6 -bottom-8 text-[9rem] text-brand-600/[0.06] dark:text-white/[0.04]" />
                    <div class="icon-pop w-14 h-14 rounded-2xl bg-brand-gradient flex items-center justify-center shadow-xl shadow-brand-900/25">
                        <x-icon :name="$card['icon']" class="text-white text-lg" />
                    </div>
                    <h3 class="mt-7 text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $card['title'] }}</h3>
                    <p class="mt-3 text-gray-600 leading-relaxed dark:text-gray-300">{{ $card['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Services: bento grid --}}
<section id="uslugi" class="relative py-20 md:py-28 bg-brand-50/60 scroll-mt-24 dark:bg-ink-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 reveal">
            <div class="max-w-2xl">
                <span class="eyebrow">{{ $sections['services_eyebrow'] }}</span>
                <h2 class="display mt-4 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $sections['services_title'] }}</h2>
            </div>
            <a href="{{ lroute('services') }}" class="group inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-950 dark:text-brand-300 dark:hover:text-white">
                {{ __('Вижте услугите') }} <x-icon name="arrow-right" class="text-xs transition-transform group-hover:translate-x-1" />
            </a>
        </div>

        <div class="mt-12 grid md:grid-cols-2 lg:grid-cols-6 gap-5 items-start">
            @foreach($services as $service)
                @php($wide = in_array($loop->index % 4, [0, 3], true))
                <article class="reveal card-hover overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 md:p-8 dark:border-white/10 dark:bg-white/[0.03] {{ $wide ? 'lg:col-span-4' : 'lg:col-span-2' }}" style="--reveal-delay: {{ ($loop->index % 2) * 100 }}ms">
                    @if($wide)
                        <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-brand-400/10 blur-2xl dark:bg-brand-500/10" aria-hidden="true"></div>
                    @endif
                    <div class="flex items-start gap-5">
                        <div class="icon-pop w-12 h-12 shrink-0 rounded-2xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
                            <x-icon :name="$service['icon']" class="text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $service['title'] }}</h3>
                            <p class="mt-1 text-sm font-semibold text-brand-600 dark:text-brand-300">{{ $service['tag'] }}</p>
                        </div>
                    </div>
                    <p class="mt-5 text-gray-600 leading-relaxed dark:text-gray-300 {{ $wide ? 'lg:text-lg lg:max-w-2xl' : '' }}">{{ $service['description'] }}</p>
                    <details class="group mt-6 border-t border-gray-100 pt-4 dark:border-white/10">
                        <summary class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 cursor-pointer list-none [&::-webkit-details-marker]:hidden dark:text-brand-300">
                            <span class="group-open:hidden">{{ __('Вижте повече') }}</span><span class="hidden group-open:inline">{{ __('Скрийте') }}</span>
                            <x-icon name="chevron-down" class="text-xs transition-transform group-open:rotate-180" />
                        </summary>
                        <p class="mt-4 text-gray-600 leading-relaxed dark:text-gray-300">{{ $service['details'] }}</p>
                        <ul class="mt-4 grid gap-2 {{ $wide ? 'sm:grid-cols-2' : '' }}">
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

{{-- Process: sticky title + timeline --}}
<section id="proces" class="relative py-20 md:py-28 bg-white scroll-mt-24 dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 lg:gap-16">
        <div class="lg:col-span-5 lg:sticky lg:top-32 self-start reveal">
            <span class="eyebrow">{{ $sections['process_eyebrow'] }}</span>
            <h2 class="display mt-4 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ $sections['process_title'] }}</h2>
            <p class="mt-5 text-lg text-gray-600 leading-relaxed dark:text-gray-300">{{ __('Отговаряме до един работен ден. Ако е спешно, обадете се.') }}</p>
            <a href="#kontakt" class="btn-shine mt-8 inline-flex items-center gap-2 bg-brand-950 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                {{ __('Свържете се с нас') }} <x-icon name="arrow-right" class="text-sm" />
            </a>
        </div>

        <ol class="lg:col-span-7 relative pl-16 md:pl-20">
            <span class="absolute left-[1.375rem] md:left-[1.625rem] top-3 bottom-3 w-px bg-gray-200 dark:bg-white/10" aria-hidden="true"></span>
            <span class="timeline-fill absolute left-[1.375rem] md:left-[1.625rem] top-3 bottom-3 w-px bg-gradient-to-b from-brand-500 via-brand-400 to-brand-300" aria-hidden="true"></span>
            @foreach($process as $step)
                <li class="reveal relative pb-6 last:pb-0" style="--reveal-delay: 60ms">
                    <span class="absolute -left-16 md:-left-20 top-4 w-11 h-11 md:w-[3.25rem] md:h-[3.25rem] rounded-full bg-white ring-1 ring-gray-200 flex items-center justify-center text-sm font-extrabold shadow-sm dark:bg-ink-900 dark:ring-white/15" aria-hidden="true"><span class="text-gradient">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></span>
                    <div class="card-hover rounded-2xl border border-gray-200 bg-white p-6 md:p-7 dark:border-white/10 dark:bg-white/[0.03]">
                        <div class="flex items-center gap-4">
                            <span class="icon-pop w-11 h-11 shrink-0 rounded-xl bg-brand-50 flex items-center justify-center dark:bg-white/10"><x-icon :name="$step['icon']" class="text-brand-600 dark:text-brand-300" /></span>
                            <h3 class="text-lg md:text-xl font-extrabold tracking-tight text-brand-950 dark:text-white"><span class="sr-only">{{ __('Стъпка :n', ['n' => $loop->iteration]) }}: </span>{{ $step['title'] }}</h3>
                        </div>
                        <p class="mt-3 text-gray-600 leading-relaxed dark:text-gray-300">{{ $step['description'] }}</p>
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
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 items-center">
        <div class="lg:col-span-5 reveal">
            <span class="eyebrow">{{ __('Контакти') }}</span>
            <h2 class="display mt-4 text-4xl md:text-6xl">{{ $sections['contact_title'] }}</h2>
            <p class="mt-6 text-lg text-gray-300 max-w-md leading-relaxed">{{ $sections['contact_text'] }}</p>
            <ul class="mt-10 space-y-3">
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

        <div class="lg:col-span-7 reveal" style="--reveal-delay: 120ms">
            <div class="relative edge glass rounded-[1.75rem] p-2 shadow-[0_40px_120px_-30px_rgb(0_0_0/0.8)]">
                @include('partials/contact-form')
            </div>
        </div>
    </div>
</section>
@endsection
