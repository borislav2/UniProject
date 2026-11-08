@extends('layouts.public')

@section('title', __('Цена за изработка на сайт с пакети от €400'))
@section('meta_description', __('Цени от €400 старт и €200/месец или €800 старт и €650/месец. Индивидуален маркетинг от €1 200/месец. Фиксирана цена и 2 месеца безплатно при годишно плащане.'))

@php
    // FAQPage JSON-LD for the price and payment questions shown on this page.
    $faqLd = [
        '@' . 'context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faq ?? [])->map(fn ($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ])->values()->all(),
    ];
    $planItems = $plans['items'] ?? [];
    $contactInfo = config('creatium.contact');
@endphp

@if(! empty($faq))
    @push('head')
        <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endpush
@endif

@section('content')
{{-- 1. Тъмно заглавие с пакетите (собствениците, ноември 2026): трите карти са в самото начало, цените се виждат без превъртане.
     Картите са еднакви по височина: на голям екран всяка е подмрежа (subgrid) на общата мрежа, така че имена, цени, списъци и бутони са на една линия. --}}
<section data-packages-hero class="relative overflow-hidden surface-night grain on-dark text-white">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_25%,transparent_70%)]" aria-hidden="true"></div>
    <div class="hidden md:block aurora-a absolute -top-40 -right-24 w-[30rem] h-[30rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="hidden md:block aurora-b absolute -bottom-48 -left-24 w-[28rem] h-[28rem] rounded-full bg-brand-700/30 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-header pb-12 md:pb-16">
        <div class="text-center">
            <p class="kicker rise-in">{{ __('Пакети и цени') }}</p>
            <h1 class="display rise-text mt-4 text-3xl sm:text-4xl xl:text-5xl text-balance" style="--d: 80ms">{{ __('Изберете как да расте вашият бизнес') }}</h1>
            @if(filled($plans['intro'] ?? null))
                <p class="rise-text mt-4 text-base md:text-lg text-gray-300 max-w-3xl mx-auto leading-relaxed text-pretty" style="--d: 160ms">{{ $plans['intro'] }}</p>
            @endif
        </div>

        @php
            $recIndex = -1;
            foreach (array_values($planItems) as $i => $candidate) {
                if (! empty($candidate['highlighted'])) { $recIndex = $i; break; }
            }
            $badgeText = collect($planItems)->pluck('badge')->filter()->first() ?: __('Препоръчваме');
        @endphp
        <div id="paketi" class="mt-10 md:mt-12 grid gap-8 lg:gap-x-6 lg:gap-y-0 lg:grid-cols-3 lg:grid-rows-[auto_auto_1fr_auto_auto] scroll-mt-28">
            @foreach($planItems as $plan)
                @php
                    $isRec = $loop->index === $recIndex;
                    $buttonText = filled($plan['button'] ?? null) ? $plan['button'] : ($plans['button'] ?? __('Получете оферта'));
                    $card = \App\Support\PlanBilling::cardPrice($billing, $plan, $loop->index);
                    $short = \App\Support\PlanBilling::cardItems($plan);
                @endphp
                <div id="paket-{{ $loop->index }}" data-plan class="reveal scroll-mt-28 flex lg:row-span-5 lg:grid lg:grid-rows-subgrid {{ ! empty($plan['highlighted']) ? 'order-first lg:order-none' : '' }}" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <div class="plan-card flex flex-1 lg:row-span-5 lg:grid lg:grid-rows-subgrid" data-plan-card="{{ $loop->index }}" data-rec="{{ $isRec ? 'true' : 'false' }}">
                        <span class="pc-badge rounded-full bg-white px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-950 shadow-lg ring-1 ring-brand-200" aria-hidden="{{ $isRec ? 'false' : 'true' }}">{{ $badgeText }}</span>
                        <article class="plan-card-body relative flex flex-1 flex-col p-6 xl:p-8 lg:row-span-5 lg:grid lg:grid-rows-subgrid">
                            <div class="pc-deco absolute inset-0 bg-grid-light" aria-hidden="true"></div>
                            <div class="pc-deco absolute -top-24 -right-24 w-72 h-72 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>

                            <div class="relative">
                                <p class="pc-accent text-xs font-bold uppercase tracking-[0.18em]">{{ __('Пакет :n', ['n' => $loop->iteration]) }}</p>
                                <h2 class="pc-fg mt-2 text-2xl leading-tight font-extrabold tracking-tight">{{ $plan['name'] }}</h2>
                                <p class="pc-body mt-2 text-sm leading-relaxed">{{ $plan['audience'] }}</p>
                            </div>

                            {{-- Цена: голямо „от €400“, подпис и само годишният абонамент (или „Индивидуална оферта“). Без месечна цена и без плащане на старта. --}}
                            <div class="pc-box relative mt-5 rounded-3xl p-5">
                                <p class="pc-fg text-4xl xl:text-5xl font-extrabold tracking-tight leading-none tabular-nums">{{ $card['from'] }}</p>
                                @if($card['caption'] !== '')<p class="pc-body mt-2 text-sm leading-snug">{{ $card['caption'] }}</p>@endif
                                @if($card['second'] !== '')
                                    <div class="pc-line mt-4 pt-4 border-t">
                                        <p class="pc-fg text-xl xl:text-2xl font-extrabold leading-snug tracking-tight tabular-nums">{{ $card['second'] }}</p>
                                        @if($card['note'] !== '')<p class="pc-body mt-1 text-xs leading-snug">{{ $card['note'] }}</p>@endif
                                    </div>
                                @endif
                            </div>

                            <ul class="relative mt-5 space-y-2.5">
                                @foreach($short as $line)
                                    <li class="pc-body flex items-start gap-3 text-sm leading-snug">
                                        <span class="pc-chip mt-0.5 w-5 h-5 shrink-0 rounded-full flex items-center justify-center"><x-icon name="check" class="text-[10px]" /></span>
                                        <span>{{ $line }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <p class="relative mt-4">
                                <a href="#vsichko-{{ $loop->index }}" class="pc-strong inline-flex items-center gap-2 text-sm font-semibold hover:opacity-80">{{ __('Вижте всичко включено') }} <x-icon name="chevron-down" class="text-xs" /></a>
                            </p>

                            <div class="relative mt-auto pt-5">
                                <a href="{{ lroute('packages') }}?paket={{ $loop->index }}#oferta" data-p-link class="pc-btn btn-shine group/btn flex w-full items-center justify-center gap-2 text-center px-6 py-3.5 rounded-2xl font-semibold">
                                    <span>{{ $buttonText }}</span>
                                    <x-icon name="arrow-right" class="text-sm" />
                                </a>
                            </div>
                        </article>
                    </div>
                </div>
            @endforeach
        </div>

        @if(! empty($plans['trust']))
            <ul class="mt-10 md:mt-12 grid gap-3 sm:grid-cols-2 lg:grid-cols-5 text-left">
                @foreach($plans['trust'] as $item)
                    <li class="relative edge glass flex items-start gap-3 rounded-2xl p-4">
                        <span class="w-10 h-10 shrink-0 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon :name="$item['icon'] ?? 'check'" class="text-brand-200" /></span>
                        <span class="text-sm leading-snug text-gray-200">
                            @if(str_contains($item['text'], ': '))
                                <strong class="block text-white">{{ explode(': ', $item['text'], 2)[0] }}</strong>{{ explode(': ', $item['text'], 2)[1] }}
                            @else
                                {{ $item['text'] }}
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-brand-400/50 to-transparent" aria-hidden="true"></div>
</section>

{{-- 2. Всичко включено: пълните списъци от `build_items`, до които води „Вижте всичко включено“ на картите. --}}
<section class="py-16 md:py-20 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="kicker kicker-light">{{ __('Подробно') }}</p>
            <h2 class="display mt-3 text-3xl md:text-4xl text-brand-950 dark:text-white">{{ $plans['all_title'] ?? __('Всичко включено във всеки пакет') }}</h2>
        </div>
        <div class="mt-10 grid gap-6 lg:grid-cols-3 items-start">
            @foreach($planItems as $plan)
                <div id="vsichko-{{ $loop->index }}" class="scroll-mt-28 rounded-[2rem] border border-gray-200 bg-white p-6 md:p-7 dark:border-white/10 dark:bg-white/[0.03]">
                    <div class="plan-card" data-rec="false">
                        <h3 class="pc-fg text-xl font-extrabold tracking-tight">{{ $plan['name'] }}</h3>
                        @foreach([['build_title', 'build_items'], ['monthly_title', 'monthly_items']] as [$titleKey, $itemsKey])
                            @if(! empty($plan[$itemsKey]))
                                @if(filled($plan[$titleKey] ?? null))<h4 class="pc-body mt-4 text-sm font-semibold">{{ $plan[$titleKey] }}</h4>@endif
                                <ul class="mt-4 space-y-3">
                                    @foreach($plan[$itemsKey] as $line)
                                        @include('partials/plan-item', ['line' => $line])
                                    @endforeach
                                </ul>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Гаранция (собствениците, ноември 2026): веднага под пакетите. --}}
@if(filled($plans['guarantee']['text'] ?? null))
    <section class="pb-16 md:pb-20 bg-white dark:bg-ink-950">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal flex flex-col gap-5 rounded-[2rem] border border-emerald-200 bg-emerald-50/70 p-6 sm:flex-row sm:items-center md:p-8 dark:border-emerald-400/20 dark:bg-emerald-400/[0.06]">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-900/20"><x-icon name="shield-halved" class="text-xl" /></span>
                <div>
                    <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $plans['guarantee']['title'] ?? __('Гаранция') }}</h2>
                    <p class="mt-1 text-lg leading-relaxed text-gray-700 dark:text-gray-200">{{ $plans['guarantee']['text'] }}</p>
                    @if(filled($plans['guarantee']['note'] ?? null))
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $plans['guarantee']['note'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif

{{-- 3. Сравнение. Иконите са декорация, текстът „Да“ / „Не“ е за екранни четци. --}}
@if(! empty($plans['compare']))
    <section class="py-16 md:py-24 bg-brand-50/60 dark:bg-ink-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="kicker kicker-light">{{ __('Сравнение') }}</p>
                <h2 class="display mt-3 text-3xl md:text-5xl text-brand-950 dark:text-white">{{ $plans['compare_title'] ?? __('Какво включва всеки пакет') }}</h2>
            </div>

            <div class="relative mt-10 overflow-x-auto lg:overflow-visible rounded-[2rem] border border-gray-200 bg-white dark:border-white/10 dark:bg-ink-950">
                <table class="w-full min-w-[34rem] border-separate border-spacing-0 text-left">
                    <thead>
                        <tr>
                            <th scope="col" class="sticky left-0 z-20 lg:top-24 w-[34%] bg-white p-4 md:p-5 text-xs font-bold uppercase tracking-wider text-gray-500 rounded-tl-[2rem] dark:bg-ink-950 dark:text-gray-400"><span class="sr-only">{{ __('Какво включва') }}</span></th>
                            @foreach($planItems as $plan)
                                <th scope="col" class="lg:sticky lg:top-24 z-10 p-4 md:p-5 align-bottom text-sm md:text-base font-extrabold leading-tight {{ $loop->last ? 'rounded-tr-[2rem]' : '' }} {{ ! empty($plan['highlighted']) ? 'bg-brand-950 text-white' : 'bg-white text-brand-950 dark:bg-ink-950 dark:text-white' }}">
                                    {{ $plan['name'] }}
                                    @if(filled($plan['badge'] ?? null))<span class="mt-1 block text-[0.65rem] font-bold uppercase tracking-wider text-brand-300">{{ $plan['badge'] }}</span>@endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans['compare'] as $row)
                            <tr>
                                <th scope="row" class="sticky left-0 z-10 border-t border-gray-100 bg-white p-4 md:p-5 text-sm font-semibold leading-snug text-brand-950 dark:border-white/10 dark:bg-ink-950 dark:text-gray-100">{{ $row['label'] }}</th>
                                @foreach($planItems as $i => $plan)
                                    @php($value = trim((string) (($row['values'] ?? [])[$i] ?? '')))
                                    <td class="border-t border-gray-100 p-4 md:p-5 text-sm {{ ! empty($plan['highlighted']) ? 'bg-brand-50/70 dark:bg-white/[0.05]' : '' }} dark:border-white/10">
                                        @if(strtolower($value) === 'yes')
                                            <span class="inline-flex w-7 h-7 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-white/10 dark:text-brand-300"><x-icon name="check" class="text-xs" /></span><span class="sr-only">{{ __('Да') }}</span>
                                        @elseif(strtolower($value) === 'no' || $value === '')
                                            <x-icon name="minus" class="text-gray-500 text-xs" /><span class="sr-only">{{ __('Не') }}</span>
                                        @else
                                            <span class="font-semibold text-brand-700 dark:text-brand-300">{{ $value }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(filled($plans['compare_note'] ?? null))
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $plans['compare_note'] }}</p>
            @endif
        </div>
    </section>
@endif

{{-- 4. Как започваме: хоризонтална линия на голям екран. --}}
@if(! empty($plans['steps']))
    <section class="relative overflow-hidden surface-night grain on-dark text-white py-16 md:py-24">
        <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_20%,transparent_70%)]" aria-hidden="true"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="kicker">{{ __('Процес') }}</p>
                <h2 class="display mt-3 text-3xl md:text-5xl">{{ $plans['steps_title'] ?? __('Как започваме') }}</h2>
            </div>
            <ol class="mt-12 grid gap-8 lg:gap-6" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 15rem), 1fr))">
                @foreach($plans['steps'] as $step)
                    <li class="reveal relative pl-0" style="--reveal-delay: {{ $loop->index * 90 }}ms">
                        <div class="flex items-center gap-4">
                            <span class="relative edge glass w-12 h-12 shrink-0 rounded-2xl flex items-center justify-center text-lg font-extrabold">{{ sprintf('%02d', $loop->iteration) }}</span>
                            @unless($loop->last)
                                <span class="hidden lg:block h-px flex-1 bg-gradient-to-r from-brand-400/60 to-transparent" aria-hidden="true"></span>
                            @endunless
                        </div>
                        <h3 class="mt-5 text-xl font-bold">{{ $step['title'] }}</h3>
                        <p class="mt-2 leading-relaxed text-gray-300">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif

{{-- 5. Условия. --}}
@if(! empty($plans['terms']))
    <section class="py-16 md:py-24 bg-white dark:bg-ink-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="kicker kicker-light">{{ __('Условия') }}</p>
                <h2 class="display mt-3 text-3xl md:text-5xl text-brand-950 dark:text-white">{{ $plans['terms_title'] ?? __('Условия') }}</h2>
            </div>
            <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach($plans['terms'] as $term)
                    <div class="reveal card-hover rounded-[2rem] border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ ($loop->index % 3) * 80 }}ms">
                        <span class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 inline-flex items-center justify-center dark:bg-white/10 dark:text-brand-300"><x-icon name="check" class="text-sm" /></span>
                        <h3 class="mt-5 text-lg font-bold text-brand-950 dark:text-white">{{ $term['title'] }}</h3>
                        <p class="mt-2 leading-relaxed text-gray-600 dark:text-gray-300">{{ $term['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- 6. Въпроси за цената и плащането (същите като на /vaprosi, с FAQPage JSON-LD в head). --}}
@if(! empty($faq))
    <section class="py-16 md:py-24 bg-brand-50/60 dark:bg-ink-900">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="kicker kicker-light">{{ __('Въпроси') }}</p>
            <h2 class="display mt-3 text-3xl md:text-5xl text-brand-950 dark:text-white">{{ $plans['faq_title'] ?? __('Въпроси') }}</h2>
            <div class="mt-10 space-y-3">
                @foreach($faq as $item)
                    <details class="group rounded-2xl border border-gray-200 bg-white p-6 open:shadow-sm dark:border-white/10 dark:bg-white/[0.03]">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg font-bold text-brand-950 [&::-webkit-details-marker]:hidden dark:text-white">{{ $item['q'] }}<x-icon name="chevron-down" class="text-xs text-gray-500 shrink-0 transition-transform group-open:rotate-180" /></summary>
                        <p class="mt-3 leading-relaxed text-gray-600 dark:text-gray-300">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
            <a href="{{ lroute('faq') }}" class="mt-8 inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">{{ __('Всички въпроси') }} <x-icon name="arrow-right" class="text-xs" /></a>
        </div>
    </section>
@endif

{{-- 7. Предпочитате разговор? Телефон, Viber, WhatsApp и календарът за консултация. --}}
<section class="pt-16 md:pt-24 bg-white dark:bg-ink-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-[2rem] surface-night grain on-dark text-white p-7 md:p-12">
            <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_20%,transparent_70%)]" aria-hidden="true"></div>
            <div class="relative grid md:grid-cols-2 gap-8 md:gap-12 items-center">
                <div>
                    <h2 class="display text-3xl md:text-4xl">{{ $plans['call_title'] ?? __('Предпочитате разговор?') }}</h2>
                    @if(filled($plans['call_text'] ?? null))
                        <p class="mt-4 text-lg leading-relaxed text-gray-300">{{ $plans['call_text'] }}</p>
                    @endif
                    <a href="{{ lroute('contact') }}#konsultatsiya" class="btn-shine btn-glow mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 font-semibold text-brand-950 hover:bg-brand-50">{{ __('Запазете час за разговор') }} <x-icon name="arrow-right" class="text-sm" /></a>
                </div>
                <ul class="space-y-3">
                    @foreach($contactInfo['phones'] ?? [] as $phone)
                        <li class="relative edge glass rounded-2xl p-4 flex flex-wrap items-center gap-x-4 gap-y-3">
                            <span class="w-11 h-11 shrink-0 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon name="phone" class="text-brand-200" /></span>
                            <a href="{{ \App\Support\Phone::tel($phone) }}" class="text-lg font-bold hover:text-brand-200">{{ $phone }}</a>
                            <span class="flex gap-2 text-xs font-semibold">
                                @if($viber = \App\Support\Phone::viber($phone))<a href="{{ $viber }}" class="rounded-lg bg-white/10 px-3 py-1.5 hover:bg-white/20">Viber</a>@endif
                                @if($whatsapp = \App\Support\Phone::whatsapp($phone))<a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="rounded-lg bg-white/10 px-3 py-1.5 hover:bg-white/20">WhatsApp</a>@endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- 8. Формата за оферта. --}}
<section class="pb-16 pt-4 md:pb-24 bg-white dark:bg-ink-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @include('partials/quote-form', ['plans' => $plans, 'selectedPlan' => $selectedPlan])
    </div>
</section>

{{-- 9. Лента „Получете оферта“ само на телефон; скрива се, докато формата се вижда. Вдясно е оставено място за бутона на чата. --}}
<div data-sticky-cta class="md:hidden fixed inset-x-0 bottom-0 z-50 px-4 pb-4 pointer-events-none transition duration-300">
    <a href="#oferta" class="btn-shine pointer-events-auto flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-950 px-5 py-4 font-semibold text-white shadow-2xl shadow-brand-950/40 ring-1 ring-white/20 dark:bg-white dark:text-brand-950">{{ $plans['button'] ?? __('Получете оферта') }} <x-icon name="arrow-right" class="text-sm" /></a>
</div>
<script>
    (function () {
        var bar = document.querySelector('[data-sticky-cta]');
        var form = document.getElementById('oferta');
        if (!bar || !form || !('IntersectionObserver' in window)) { return; }
        new IntersectionObserver(function (entries) {
            var hide = entries[0].isIntersecting;
            bar.classList.toggle('opacity-0', hide);
            bar.classList.toggle('translate-y-full', hide);
            bar.setAttribute('aria-hidden', hide ? 'true' : 'false');
            bar.querySelector('a').tabIndex = hide ? -1 : 0;
            // На телефон бутонът на Крео е в същия ъгъл: вдигаме го над лентата, докато тя се вижда.
            var chat = document.getElementById('chat');
            if (chat) {
                chat.style.transition = 'bottom .3s';
                chat.style.bottom = (!hide && window.matchMedia('(max-width: 767px)').matches) ? '5.5rem' : '';
            }
        }).observe(form);
    })();
</script>
@endsection
