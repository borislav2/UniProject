@extends('layouts.public')

{{-- Home page of the redesign (concept G). The same content blocks and SEO as the current home page, a different layout. --}}
@section('title', __('Изработка на сайтове и дигитален маркетинг от €500'))
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

    // The six services of the first screen (owners' audit, November 2026): number, title, text and the service page each one links to (empty = all services).
    $servicePages = (array) config('creatium.service_pages');
    $parts = collect((array) ($trust['g_parts'] ?? []))->filter(fn ($p) => filled($p['title'] ?? null))->map(fn ($p) => [
        'label' => $p['title'],
        'text' => $p['text'] ?? '',
        'href' => filled($p['service'] ?? null) && isset($servicePages[$p['service']]) ? \App\Http\Controllers\PageController::serviceUrl($p['service']) : lroute('services'),
    ])->values();
    $heroTrust = array_values(array_filter((array) ($trust['g_hero_points'] ?? [])));

    // Proof: only what really exists. A Google rating needs a review count, testimonials a quote, results a value and a text.
    $rating = (float) ($reviews['rating'] ?? 0);
    $reviewCount = (int) ($reviews['count'] ?? 0);
    $testimonials = collect((array) ($trust['testimonials'] ?? []))->filter(fn ($t) => filled($t['quote'] ?? null))->values();
    $results = collect((array) ($trust['results'] ?? []))->filter(fn ($r) => filled($r['value'] ?? null) && filled($r['text'] ?? null))->values();
    $hasProof = $reviewCount > 0 || $testimonials->isNotEmpty() || $results->isNotEmpty();

@endphp

@section('content')

{{-- Hero: a drifting topographic map behind the headline (the signature), then the six services as one row; all of it fits the first screen on a desktop --}}
<section class="g-hero" aria-label="{{ __('Начало') }}">
    <canvas id="g-topo" aria-hidden="true"></canvas>
    <div class="g-wrap">
        <h1 class="g-h" data-g-clear>{!! $accent($hero['title'], $hero['accent'] ?? []) !!}</h1>
        @php($heroLead = filled($hero['g_highlight'] ?? null) ? $hero['g_highlight'] : ($hero['highlight'] ?? null))
        @if(filled($heroLead))
            <p class="g-lead" data-g-clear>{{ $heroLead }}</p>
        @endif
        <div class="g-cta">
            <a class="g-btn" data-g-clear href="{{ lroute('packages') }}#oferta">{{ __('Изпратете запитване') }}<i></i></a>
            <a class="g-btn g-ghost" data-g-clear href="{{ lroute('packages') }}">{{ __('Вижте пакетите') }}</a>
        </div>
        @if($heroTrust)
            <ul class="g-trust" data-g-clear>
                @foreach($heroTrust as $line)<li>{{ $line }}</li>@endforeach
            </ul>
        @endif
    </div>
</section>

{{-- The six services as a two column hairline list under the hero --}}
@if($parts->isNotEmpty())
<section class="g-s g-svc-home" aria-label="{{ __('Услуги') }}">
    <div class="g-wrap">
        <nav class="g-parts">
            @foreach($parts as $part)
                <a href="{{ $part['href'] }}"><b>{{ $part['label'] }}</b>@if(filled($part['text']))<small>{{ $part['text'] }}</small>@endif</a>
            @endforeach
        </nav>
    </div>
</section>
@endif

{{-- Projects right after the hero: real public projects first, then every concept site, each with one line on what is special in it --}}
@if($projects->isNotEmpty() || ! empty($demos))
<section class="g-s" id="proekti">
    <div class="g-wrap">
        <div class="g-sh g-sh-split">
            <h2 class="g-h">{{ $trust['g_projects_title'] ?? __('Проекти, създадени с мисъл за растеж') }}</h2>
            @if(filled($trust['g_projects_subtitle'] ?? null))<p class="g-sh-text g-wide">{{ $trust['g_projects_subtitle'] }}</p>@endif
        </div>
        <div class="g-work" data-g-projects>
            @foreach($projects as $project)
                @php($host = $project->website_url)
                <article class="g-card">
                    @if($host)<a href="{{ $project->website_url }}" target="_blank" rel="noopener">@endif
                        <figure>
                            <div class="g-frame">
                                @if($project->screenshot)<img src="{{ asset($project->screenshot) }}" alt="{{ $project->name }}" width="960" height="600" loading="lazy" decoding="async">@else<div class="g-noimg g-mono">{{ $project->name }}</div>@endif
                            </div>
                            <figcaption><strong>{{ $project->name }}</strong><span class="g-cat">{{ $project->category->name }}</span></figcaption>
                        </figure>
                        <p class="g-micro">{{ \Illuminate\Support\Str::limit((string) $project->description, 130) }}</p>
                    @if($host)</a>@endif
                </article>
            @endforeach
            @foreach($demos as $demo)
                <article class="g-card">
                    <a href="{{ route('demo.show', $demo['slug']) }}">
                        <figure>
                            <div class="g-frame"><img src="{{ asset('images/demos/' . $demo['slug'] . '.webp') }}" srcset="{{ demo_srcset($demo['slug']) }}" sizes="(min-width: 760px) 33vw, 100vw" alt="{{ $demo['name'] }}, {{ $demo['industry'] }}" width="960" height="600" loading="lazy" decoding="async"></div>
                            <figcaption><strong>{{ $demo['industry'] }}</strong></figcaption>
                        </figure>
                        <p class="g-micro">{{ filled($demo['g_micro'] ?? null) ? $demo['g_micro'] : ($demo['description'] ?? '') }}</p>
                        @include('partials.g.facts', ['facts' => $demo['g_facts'] ?? []])
                    </a>
                </article>
            @endforeach
        </div>
        <div class="g-more">
            <a class="g-btn g-ghost" href="{{ lroute('portfolio') }}">{{ __('Всички проекти') }}<i></i></a>
        </div>
    </div>
</section>
@endif

{{-- Packages: three equal cards, monthly or yearly, with the groups of what is included --}}
<section class="g-s" id="paketi">
    <div class="g-wrap">
        <div class="g-sh g-sh-split">
            <h2 class="g-h">{{ $plans['g_title'] ?? __('План за всеки етап от вашия бизнес') }}</h2>
            @if(filled($plans['g_subtitle'] ?? null))<p class="g-sh-text g-wide">{{ $plans['g_subtitle'] }}</p>@endif
        </div>
        @include('partials.g.plan-cards')
    </div>
</section>

{{-- Proof: rendered only when there is a Google rating, a testimonial or a result (nothing is made up) --}}
@if($hasProof)
<section class="g-s" id="dokazatelstva">
    <div class="g-wrap">
        <div class="g-sh">
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

{{-- Our goal (fifth round, 13.11.2026, instead of "The agency"): one sentence, a short text and the three promises --}}
<section class="g-band" id="tsel">
    <div class="g-wrap">
        <p class="g-mono g-goal-k">{{ __('Нашата цел') }}</p>
        <div class="g-goal">
            <h2 class="g-h">{!! $accent($trust['g_goal_title'] ?? $trust['agency_title'] ?? '', $trust['g_goal_accent'] ?? $trust['agency_accent'] ?? []) !!}</h2>
            @if(filled($trust['g_goal_text'] ?? null))<p class="g-goal-text">{{ $trust['g_goal_text'] }}</p>@endif
        </div>
        @if(! empty($trust['agency_facts']))
            <div class="g-pr">
                @foreach($trust['agency_facts'] as $fact)
                    <p><b>{{ $fact['title'] }}</b><span>{{ $fact['text'] ?? '' }}</span></p>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Contact: the form on the left, the text with the phone and the email on the right (text first on phones) --}}
<section class="g-s g-last" id="kontakt">
    <div class="g-wrap g-ct">
        <div class="g-ct-info">
            <h2 class="g-h g-ct-h">{{ $trust['g_contact_title'] ?? __('Запазете час за разговор') }}</h2>
            @if(filled($trust['g_contact_text'] ?? null))<p class="g-ct-text">{{ $trust['g_contact_text'] }}</p>@endif
            <div class="g-info">
                @foreach(g_phones() as $phone)
                    <div><span class="g-lbl">{{ __('Телефон') }}</span><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></div>
                @endforeach
                <div><span class="g-lbl">{{ __('Имейл') }}</span><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></div>
            </div>
        </div>
        <div class="g-ct-form">
            @include('partials.g.contact-form')
        </div>
    </div>
</section>
@endsection
