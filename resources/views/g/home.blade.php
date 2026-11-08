@extends('layouts.public')

{{-- Home page of the redesign (concept G). The same content blocks and SEO as the current home page, a different layout. --}}
@section('title', __('Изработка на сайтове и дигитален маркетинг от €400'))
@section('meta_description', __('Сайт, който носи запитвания, плюс видимост в Google, Google Maps и ChatGPT. За бизнеси в България, с фиксирана цена и калкулатор на ползата.'))

@section('g_lean', '1')

@push('head')
@include('partials.home-ld')
@vite(['resources/js/g-home.js'])
@endpush

@php
    // Заглавието и думите в цвят (home_hero.accent, trust.agency_accent): текстът се екранира, после думите се обвиват в <em>.
    $accent = function (string $text, $words) {
        $text = e($text);
        foreach (array_filter((array) $words) as $word) {
            $text = preg_replace('/(?<![\p{L}])(' . preg_quote(e($word), '/') . ')(?![\p{L}])/u', '<em>$1</em>', $text, 1);
        }

        return $text;
    };
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);

    $nodes = array_values(array_filter((array) ($trust['network_nodes'] ?? [])));
    // The six parts: label, one short line and the service page each one links to (empty = all services). Older saves without `parts` fall back to the node names.
    $servicePages = (array) config('creatium.service_pages');
    $parts = collect((array) ($trust['parts'] ?? []))->filter(fn ($p) => filled($p['label'] ?? null))->map(fn ($p) => [
        'label' => $p['label'],
        'text' => $p['text'] ?? '',
        'href' => filled($p['service'] ?? null) && isset($servicePages[$p['service']]) ? \App\Http\Controllers\PageController::serviceUrl($p['service']) : lroute('services'),
    ])->values();
    if ($parts->isEmpty()) {
        $parts = collect($nodes)->map(fn ($label) => ['label' => $label, 'text' => '', 'href' => lroute('services')]);
    }
    $heroTrust = array_values(array_filter((array) ($trust['hero_trust'] ?? [])));

    // Proof: only what really exists. A Google rating needs a review count, testimonials a quote, results a value and a text.
    $rating = (float) ($reviews['rating'] ?? 0);
    $reviewCount = (int) ($reviews['count'] ?? 0);
    $testimonials = collect((array) ($trust['testimonials'] ?? []))->filter(fn ($t) => filled($t['quote'] ?? null))->values();
    $results = collect((array) ($trust['results'] ?? []))->filter(fn ($r) => filled($r['value'] ?? null) && filled($r['text'] ?? null))->values();
    $hasProof = $reviewCount > 0 || $testimonials->isNotEmpty() || $results->isNotEmpty();
    $steps = array_values((array) ($problem['system_steps'] ?? []));

    // Four concepts: the four industries of the design brief when they exist, otherwise the first four.
    $bySlug = collect($demos)->keyBy('slug');
    $concepts = collect(['restorant', 'salon', 'avtoservis', 'onlain-magazin'])->map(fn ($slug) => $bySlug->get($slug))->filter();
    if ($concepts->count() < 4) {
        $concepts = collect($demos)->take(4);
    }

    $planItems = array_values((array) ($plans['items'] ?? []));
    $recIndex = collect($planItems)->search(fn ($p) => ! empty($p['highlighted']));
    $billingOn = ! empty($billing['enabled']);
    $n = 0;
@endphp

@section('content')

{{-- Hero: a drifting topographic map behind the headline (the signature), then the six parts as a row --}}
<section class="g-hero" aria-label="{{ __('Начало') }}">
    <canvas id="g-topo" aria-hidden="true"></canvas>
    <div class="g-wrap">
        <p class="g-mono g-eyebrow" data-g-clear>{{ __('Дигитална агенция') }}</p>
        <h1 class="g-h" data-g-clear>{!! $accent($hero['title'], $hero['accent'] ?? []) !!}</h1>
        @if(filled($hero['highlight'] ?? null))
            <p class="g-lead" data-g-clear>{{ $hero['highlight'] }}</p>
        @endif
        <div class="g-cta">
            <a class="g-btn" data-g-clear href="{{ lroute('packages') }}#oferta">{{ $hero['button'] ?? __('Получете оферта') }}<i></i></a>
            <a class="g-btn g-ghost" data-g-clear href="{{ lroute('packages') }}">{{ __('Вижте пакетите') }}</a>
        </div>
        @if($heroTrust)
            <ul class="g-trust" data-g-clear>
                @foreach($heroTrust as $line)<li>{{ $line }}</li>@endforeach
            </ul>
        @endif
        <a class="g-link" data-g-clear href="{{ lroute('site-check') }}">{{ __('Проверете сайта си безплатно') }}</a>
        @if($parts->isNotEmpty())
            <nav class="g-parts" data-g-clear="full" aria-label="{{ __('Системата') }}">
                @foreach($parts as $part)
                    <a href="{{ $part['href'] }}"><span class="g-mono">{{ $pad($loop->iteration) }}</span><b>{{ $part['label'] }}</b>@if(filled($part['text']))<small>{{ $part['text'] }}</small>@endif</a>
                @endforeach
            </nav>
        @endif
    </div>
</section>

{{-- The system: editorial rows from home_problem.system_steps, ending in the result --}}
@if($steps)
<section class="g-s" id="sistema">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Системата') }}</p>
            <h2 class="g-h">{{ __('Една система. Шест части.') }}</h2>
            @if(filled($problem['system_text'] ?? null))<p class="g-sh-text">{{ $problem['system_text'] }}</p>@endif
        </div>
        <ol class="g-rows">
            @foreach($steps as $step)
                <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['subtitle'] ?? '' }}</p></li>
            @endforeach
            @if(filled($problem['system_result'] ?? null))
                <li class="g-end"><span class="g-n g-mono g-eq">=</span><h3>{{ $problem['system_result'] }}</h3></li>
            @endif
        </ol>
    </div>
</section>
@endif

{{-- Four concepts of sites, clearly labelled --}}
@if($concepts->isNotEmpty())
<section class="g-s" id="proekti">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Концепции за сайтове') }}</p>
            <h2 class="g-h">{{ __('Така изглежда вашият сайт') }}</h2>
        </div>
        <div class="g-work">
            @foreach($concepts as $demo)
                <a href="{{ route('demo.show', $demo['slug']) }}">
                    <figure>
                        <div class="g-frame"><img src="{{ asset('images/demos/' . $demo['slug'] . '.webp') }}" srcset="{{ demo_srcset($demo['slug']) }}" sizes="(min-width: 760px) 50vw, 100vw" alt="{{ __('Концепция') }}, {{ $demo['industry'] }}" width="960" height="600" loading="lazy" decoding="async"></div>
                        <figcaption><span class="g-no g-mono">{{ $pad($loop->iteration) }}</span><span class="g-chip g-mono" data-concept-chip>{{ __('Концепция') }}</span><strong>{{ $demo['industry'] }}</strong></figcaption>
                    </figure>
                </a>
            @endforeach
        </div>
        <div class="g-more">
            <a class="g-btn g-ghost" href="{{ lroute('portfolio') }}">{{ __('Всички проекти') }}<i></i></a>
        </div>
        @if($posts->isNotEmpty())
            <div class="g-news">
                @foreach($posts as $post)
                    <a href="{{ $post->url() }}"><time class="g-mono" datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale(app()->getLocale())->translatedFormat('j.m.Y') }}</time><span>{{ $post->tr('title') }}</span></a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

{{-- Packages: three equal cards, monthly or yearly --}}
@if($planItems)
<section class="g-s" id="paketi">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Пакети') }}</p>
            <h2 class="g-h">{{ __('Ясна цена, без изненади') }}</h2>
        </div>
        <div data-g-billing="m" data-g-billing-root>
            @if($billingOn)
                <div class="g-tog" role="group" aria-label="{{ __('Плащане') }}"><button type="button" data-b="m" aria-pressed="true">{{ __('Месечно') }}</button><button type="button" data-b="y" aria-pressed="false">{{ __('Годишно') }}</button></div>
                <p class="g-tognote">{{ $billing['yearly_label'] ?? '' }}. {{ __('Цените са в евро.') }}</p>
            @endif
            <div class="g-packs">
                @foreach($planItems as $i => $plan)
                    @php
                        $isRec = $i === $recIndex;
                        $card = \App\Support\PlanBilling::cardPrice($billing, $plan, $i);
                        $monthly = \App\Support\PlanBilling::cell($billing, $plan, $i, 'monthly');
                        $yearly = \App\Support\PlanBilling::cell($billing, $plan, $i, 'yearly');
                        $button = filled($plan['button'] ?? null) ? $plan['button'] : ($plans['button'] ?? __('Получете оферта'));
                    @endphp
                    <article class="g-pk {{ $isRec ? 'g-rec' : '' }}" data-plan="{{ $i }}">
                        <div class="g-top"><span class="g-mono {{ $isRec ? '' : 'g-mute' }}">{{ $pad($i + 1) }}</span>@if($isRec)<span class="g-mono">{{ filled($plan['badge'] ?? null) ? $plan['badge'] : __('Препоръчваме') }}</span>@endif</div>
                        <h3>{{ $plan['name'] }}</h3>
                        @if($monthly['priced'])
                            <div class="g-price">{{ $card['from'] }}</div>
                            <p class="g-then g-mute">@if($card['caption'] !== ''){{ \Illuminate\Support\Str::ucfirst($card['caption']) }}, @endif
                                <b data-bv="m">{{ __('след това') }} {{ $monthly['price'] }} {{ $monthly['suffix'] }}</b><b data-bv="y">{{ __('след това') }} {{ $yearly['price'] }} {{ $yearly['suffix'] }}</b></p>
                        @else
                            <div class="g-price">{{ $card['from'] }}@if($card['caption'] !== '')<small>{{ $card['caption'] }}</small>@endif</div>
                            <p class="g-then g-mute">{{ $card['second'] }}</p>
                        @endif
                        <ul>
                            @foreach(array_slice(\App\Support\PlanBilling::cardItems($plan), 0, 4) as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                        <a class="g-btn" href="{{ lroute('packages') }}?paket={{ $i }}#oferta">{{ $button }}<i></i></a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- Proof: rendered only when there is a Google rating, a testimonial or a result (nothing is made up) --}}
@if($hasProof)
<section class="g-s" id="dokazatelstva">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Доказателства') }}</p>
            <h2 class="g-h">{{ __('Доверие, което можете да проверите') }}</h2>
        </div>
        <div class="g-proof">
            @if($reviewCount > 0)
                <div>
                    @if($rating > 0)<span class="g-big">{{ number_format($rating, 1, app()->getLocale() === 'en' ? '.' : ',', '') }} / 5</span>@endif
                    @php($ratingText = __('Оценка в Google от :count отзива', ['count' => $reviewCount]))
                    <p>@if(filled($reviews['url'] ?? null))<a href="{{ $reviews['url'] }}" target="_blank" rel="noopener">{{ $ratingText }}</a>@else{{ $ratingText }}@endif</p>
                </div>
            @endif
            @foreach($results as $result)
                <div><span class="g-big">{{ $result['value'] }}</span><p>{{ $result['text'] }}</p></div>
            @endforeach
            @foreach($testimonials as $testimonial)
                <figure>
                    <blockquote>{{ $testimonial['quote'] }}</blockquote>
                    @if(filled($testimonial['name'] ?? null) || filled($testimonial['business'] ?? null))
                        <cite><b>{{ $testimonial['name'] ?? '' }}</b>@if(filled($testimonial['name'] ?? null) && filled($testimonial['business'] ?? null)), @endif{{ $testimonial['business'] ?? '' }}</cite>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- The agency: one idea and three promises (no team size, no portraits) --}}
<section class="g-band" id="agentsiyata">
    <div class="g-wrap">
        <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Агенцията') }}</p>
        <h2 class="g-h">{!! $accent($trust['agency_title'] ?? '', $trust['agency_accent'] ?? []) !!}</h2>
        @if(! empty($trust['agency_facts']))
            <div class="g-pr">
                @foreach($trust['agency_facts'] as $fact)
                    <div><b>{{ $fact['title'] }}</b><span>{{ $fact['text'] ?? '' }}</span></div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Contact: the simple form, phones and email as text --}}
<section class="g-s" id="kontakt" style="border-bottom:0">
    <div class="g-wrap g-ct">
        <div>
            <div class="g-sh" style="margin-bottom:34px">
                <p class="g-mono"><b>{{ $pad(++$n) }}</b>{{ __('Контакти') }}</p>
                <h2 class="g-h">{{ __('Запазете час за разговор') }}</h2>
            </div>
            <div class="g-info">
                @foreach($contact['phones'] as $phone)
                    <div><span class="g-mono">{{ __('Телефон') }}</span><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></div>
                @endforeach
                <div><span class="g-mono">{{ __('Имейл') }}</span><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></div>
            </div>
        </div>
        <div class="g-form">
            @include('partials/contact-form')
        </div>
    </div>
</section>
@endsection
