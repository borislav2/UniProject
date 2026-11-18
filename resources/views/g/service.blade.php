@extends('layouts.public')

{{--
    One service page in design G, built from the owners' brief (18.11.2026): the dark first screen (the title, the text, the button and visual 1, or the short form for the website),
    the strip of platforms, the problems (numbered), visual 2, the process (numbered, visual 3), the questions, then the package box, the industries (website), the other services.
    The content is in service_pages.<key>.g_* (config/creatium.php and creatium_en.php); a missing part is simply left out.
--}}
@section('title', $page['title'])
@section('breadcrumb', $page['menu'])
@section('g_lean', 'inner')

@php
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    // The package in the names of design G (Core Изграждане ...), the same order as plans.items.
    $gPlan = collect(\App\Support\GPlans::cards(app(\App\Support\Content::class)->withDefaults('plans')))->keyBy('index')->get($page['package'] ?? -1);
    $vis = (array) ($page['g_vis'] ?? []);
    $problems = (array) ($page['g_problems'] ?? []);
    $steps = (array) ($page['g_process'] ?? []);
    $faq = ! empty($page['g_faq']) ? $page['g_faq'] : array_map(fn ($qa) => ['q' => $qa[0], 'a' => $qa[1]], $page['faq']);
    $isWebsite = $key === 'website';
    $direction = $page['g_direction'] ?? $key;
    // The button goes to the one contact form with the direction of the service ticked; on the website page the form is on the page itself.
    $buttonUrl = lroute('contact') . '?napravleniya=' . $direction . '#kontakt';
    $heroVisual = null;
    if ($isWebsite) {
        $heroVisual = view('partials.g.svc.form', ['page' => $page])->render();
    } elseif (! empty($vis['hero'])) {
        $heroVisual = view('partials.g.svc.' . $key, ['slot' => 'hero', 'v' => $vis['hero'], 'page' => $page])->render();
    }
    $actions = $isWebsite ? null : '<a class="g-btn" href="' . e($buttonUrl) . '">' . e($page['g_button'] ?? __('Запазете час за разговор')) . '<i></i></a>';
    $figure = fn ($slot) => empty($vis[$slot]) ? '' : view('partials.g.svc.' . $key, ['slot' => $slot, 'v' => $vis[$slot], 'page' => $page])->render();
@endphp

@section('content')
@include('partials.g.page-head', array_filter([
    'heading' => $page['g_hero_title'] ?? $page['title'],
    'sub' => $page['g_hero_text'] ?? (filled($page['g_what'] ?? null) ? $page['g_what'] : $page['lead']),
    'dark' => true,
    'actions' => $actions,
    'visualHtml' => $heroVisual,
    'visualKey' => $heroVisual ? null : $key,
], fn ($value) => $value !== null))

@include('partials.g.platforms')

@if($problems)
<section class="g-s g-tight g-svc-sec" id="problemi">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <h2 class="g-h">{{ $page['g_problems_title'] ?? __('Познато ли ви е?') }}</h2>
            @if(filled($page['g_problems_intro'] ?? null))<p class="g-sh-text">{{ $page['g_problems_intro'] }}</p>@endif
        </div>
        <ol class="g-rows g-numbered g-svc-rows">
            @foreach($problems as $problem)
                <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $problem['title'] }}</h3><p>{{ $problem['text'] }}</p></li>
            @endforeach
        </ol>
        @if($isWebsite && ! empty($vis['hero']))<div class="g-svc-fig">{!! view('partials.g.svc.website', ['slot' => 'hero', 'v' => $vis['hero'], 'page' => $page])->render() !!}</div>@endif
    </div>
</section>
@endif

@if($figure('v2') !== '')
<section class="g-s g-tight g-svc-sec">
    <div class="g-wrap">{!! $figure('v2') !!}</div>
</section>
@endif

@if($steps)
<section class="g-s g-tight g-svc-sec" id="protses">
    <div class="g-wrap">
        <div class="g-sys">
            <div class="g-sh">
                <h2 class="g-h">{{ $page['g_process_title'] ?? __('Какво правим') }}</h2>
            </div>
            <ol class="g-rows g-numbered g-svc-rows">
                @foreach($steps as $step)
                    <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['text'] }}</p></li>
                @endforeach
            </ol>
        </div>
        @if($figure('v3') !== '')<div class="g-svc-fig g-svc-fig-wide">{!! $figure('v3') !!}</div>@endif
    </div>
</section>
@endif

<section class="g-s g-tight g-svc-sec">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <h2 class="g-h">{{ __('Често задавани въпроси') }}</h2>
        </div>
        @include('partials.g.faq', ['items' => $faq])
    </div>
</section>

<section class="g-s g-tight">
    <div class="g-wrap">
        <div class="g-pkgcard">
            <div>
                @if($gPlan)
                    <p class="g-mono g-mute">{{ __('Включено в пакет') }}</p>
                    <h2 class="g-h">{{ \App\Support\GPlans::label($gPlan) }}</h2>
                    @if(filled($gPlan['monthly']))<p class="g-pkg-price">{{ trim($gPlan['from'] . ' ' . $gPlan['monthly']) }}{{ __('/месец') }}</p>@endif
                @else
                    <p class="g-mono g-mute">{{ __('Цена') }}</p>
                    <h2 class="g-h">{{ __('Цена по оферта') }}</h2>
                @endif
                @if(filled($page['note'] ?? null))<p class="g-pkg-note">{{ $page['note'] }}</p>@endif
                <div class="g-get g-get-line">
                    <p class="g-mono g-mute">{{ __('Какво получавате') }}</p>
                    <p>{{ $page['result'] }}</p>
                </div>
            </div>
            <div class="g-cta">
                <a class="g-btn" href="{{ lroute('contact') }}#konsultatsiya">{{ __('Запазете час за разговор') }}</a>
                <a class="g-btn g-ghost" href="{{ lroute('packages') }}">{{ __('Вижте пакетите') }}</a>
            </div>
        </div>
    </div>
</section>

@if($key === 'website' && app()->getLocale() === 'bg')
<section class="g-s g-tight">
    <div class="g-wrap">
        <div class="g-sh">
            <h2 class="g-h">Сайт за вашия бранш</h2>
            <p class="g-sh-text">Какво трябва да има сайтът, с пример и подходящ пакет за всеки бранш.</p>
        </div>
        <div class="g-links">
            @foreach(config('creatium.industry_pages') as $industrySlug => $industry)
                <a href="{{ route('industry', $industrySlug) }}"><span>{{ $industry['title'] }}</span><x-icon name="arrow-right" class="g-ar" /></a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="g-s g-tight g-last">
    <div class="g-wrap">
        <div class="g-sh">
            <h2 class="g-h g-sub-h">{{ __('Другите ни услуги') }}</h2>
        </div>
        <div class="g-links">
            @foreach($others as $otherKey => $other)
                <a href="{{ \App\Http\Controllers\PageController::serviceUrl($otherKey) }}"><span>{{ $other['menu'] }}</span><x-icon name="arrow-right" class="g-ar" /></a>
            @endforeach
            <a href="{{ lroute('site-check') }}"><span>{{ __('Безплатна проверка на сайт') }}</span><x-icon name="arrow-right" class="g-ar" /></a>
        </div>
    </div>
</section>

{{-- '@' . 'context': Blade иначе чете @context като своя директива и поврежда ключа. --}}
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => $page['title'],
            'description' => $page['seo_description'],
            'serviceType' => $page['menu'],
            'areaServed' => ['@type' => 'Country', 'name' => 'Bulgaria'],
            'url' => url()->current(),
            'provider' => \App\Support\Schema::provider(),
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($qa) => ['@type' => 'Question', 'name' => $qa['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa['a']]], array_values($faq)),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endsection
