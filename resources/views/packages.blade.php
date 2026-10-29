@extends('layouts.public')

@section('title', __('Цена за изработка на сайт: пакети от €450'))
@section('meta_description', __('Колко струва сайт с маркетинг: от €450 + €150/месец или €850 + €400/месец с активна реклама. Фиксирана цена и демо преди старта.'))

@section('content')
{{-- Пакетите излизат веднага: кратко заглавие отгоре, после картите по цялата ширина.
     На голям екран картите са subgrid с общи редове, така че цени, списъци и бутони стоят на една линия. --}}
<section class="relative overflow-hidden surface-night grain on-dark text-white">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_25%,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative max-w-[100rem] mx-auto px-4 sm:px-6 lg:px-10 pt-header pb-8 md:pb-10 text-center">
        <p class="kicker rise-in">{{ __('Пакети') }}</p>
        <h1 class="display rise-text mt-4 text-4xl sm:text-5xl" style="--d: 80ms">{{ __('Пакети и цени') }}</h1>
        @if(filled($plans['intro'] ?? null))
            <p class="rise-text mt-4 text-base md:text-lg text-gray-300 max-w-2xl mx-auto leading-relaxed text-pretty" style="--d: 160ms">{{ $plans['intro'] }}</p>
        @endif
    </div>
</section>

<section class="pt-10 pb-16 md:pt-12 md:pb-24 bg-white dark:bg-ink-950">
    <div class="max-w-[100rem] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="grid lg:grid-cols-3 gap-6 lg:gap-y-0 lg:gap-x-6">
            @foreach($plans['items'] as $plan)
                @php($dark = ! empty($plan['highlighted']))
                <article class="reveal relative overflow-hidden rounded-3xl p-7 md:p-8 flex flex-col lg:row-span-6 lg:grid lg:grid-rows-subgrid {{ $dark ? 'bg-brand-950 text-white shadow-2xl shadow-brand-950/30 dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30 dark:shadow-black/40' : 'card-hover border border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.03]' }}" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    @if($dark)
                        <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
                        <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
                    @endif
                    <div class="relative">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] {{ $dark ? 'text-brand-300' : 'text-brand-600 dark:text-brand-300' }}">{{ __('Пакет :n', ['n' => $loop->iteration]) }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <h2 class="text-2xl font-extrabold tracking-tight {{ $dark ? '' : 'text-brand-950 dark:text-white' }}">{{ $plan['name'] }}</h2>
                            @if(filled($plan['badge'] ?? null))
                                <span class="rounded-full bg-brand-500/20 text-brand-200 text-xs font-bold uppercase tracking-wide px-3 py-1 ring-1 ring-brand-400/30">{{ $plan['badge'] }}</span>
                            @endif
                        </div>
                    </div>
                    <p class="relative mt-3 leading-relaxed {{ $dark ? 'text-gray-300' : 'text-gray-600 dark:text-gray-300' }}">{{ $plan['audience'] }}</p>

                    <ul class="relative mt-6 space-y-2 rounded-2xl p-4 {{ $dark ? 'bg-white/[0.06] ring-1 ring-white/10' : 'bg-brand-50/70 dark:bg-white/[0.04]' }}">
                        @foreach($plan['prices'] as $price)
                            @php([$amount, $note] = preg_match('/^(€?[\d\s.,]+(?:\s*\/\s*месец|\s*\/\s*month)?)\s*(.*)$/u', $price, $m) ? [$m[1], $m[2]] : [null, $price])
                            <li class="{{ $dark ? 'text-gray-300' : 'text-gray-600 dark:text-gray-300' }} text-sm">@if($amount)<strong class="block text-2xl font-extrabold {{ $dark ? 'text-white' : 'text-brand-950 dark:text-white' }}">{{ $amount }}</strong>@endif{{ $note }}</li>
                        @endforeach
                    </ul>

                    {{-- Кратко: само заглавията на точките (частта преди „:“). Пълният текст е в „Вижте подробно“. --}}
                    <div class="relative">
                        @foreach([['build_items', __('При старта')], ['monthly_items', __('Всеки месец')]] as [$itemsKey, $shortTitle])
                            @if(! empty($plan[$itemsKey]))
                                <h3 class="mt-6 text-xs font-bold uppercase tracking-wider {{ $dark ? 'text-brand-300' : 'text-brand-600 dark:text-brand-300' }}">{{ empty($plan['monthly_items']) ? __('Включва') : $shortTitle }}</h3>
                                <ul class="mt-3 space-y-2">
                                    @foreach($plan[$itemsKey] as $line)
                                        @include('partials/plan-item', ['line' => str_contains($line, ': ') ? explode(': ', $line, 2)[0] : $line, 'dark' => $dark])
                                    @endforeach
                                </ul>
                            @endif
                        @endforeach
                    </div>

                    <details class="group relative mt-6">
                        <summary class="flex cursor-pointer list-none items-center gap-2 text-sm font-semibold {{ $dark ? 'text-brand-200 hover:text-white' : 'text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white' }}">
                            {{ __('Вижте подробно какво включва') }} <x-icon name="chevron-down" class="text-xs transition-transform group-open:rotate-180" />
                        </summary>
                        @foreach([['build_title', 'build_items'], ['monthly_title', 'monthly_items']] as [$titleKey, $itemsKey])
                            @if(! empty($plan[$itemsKey]))
                                <h4 class="mt-5 text-sm font-bold {{ $dark ? 'text-white' : 'text-brand-950 dark:text-white' }}">{{ $plan[$titleKey] }}</h4>
                                <ul class="mt-3 space-y-3">
                                    @foreach($plan[$itemsKey] as $line)
                                        @include('partials/plan-item', ['line' => $line, 'dark' => $dark])
                                    @endforeach
                                </ul>
                            @endif
                        @endforeach
                    </details>

                    <a href="{{ lroute('contact') }}#contact-form" class="btn-shine relative mt-8 self-end lg:self-auto lg:mt-8 w-full text-center px-6 py-3.5 rounded-xl font-semibold transition-colors {{ $dark ? 'bg-white text-brand-950 hover:bg-brand-50' : 'border border-gray-200 text-brand-950 hover:border-brand-400 hover:text-brand-700 dark:border-white/15 dark:text-white dark:hover:border-brand-400' }}">{{ $plans['button'] }}</a>
                </article>
            @endforeach
        </div>

        <div class="reveal mt-10 flex flex-col items-center gap-4 rounded-3xl border border-gray-200 p-7 text-center sm:flex-row sm:justify-between sm:text-left dark:border-white/10">
            <div>
                <h2 class="text-xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Не сте сигурни кой пакет ви трябва?') }}</h2>
                <p class="mt-1 text-gray-600 dark:text-gray-300">{{ __('На кратък разговор ще ви кажем кое има смисъл за вашия бизнес и кое не.') }}</p>
            </div>
            <a href="{{ lroute('contact') }}#konsultatsiya" class="btn-shine shrink-0 inline-flex items-center gap-2 rounded-xl bg-brand-950 px-6 py-3.5 font-semibold text-white hover:bg-brand-800 dark:bg-white dark:text-brand-950">{{ __('Безплатна консултация') }} <x-icon name="arrow-right" class="text-sm" /></a>
        </div>
    </div>
</section>
@endsection
