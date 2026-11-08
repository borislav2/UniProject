@extends('layouts.public')

{{-- One service page in design G: the problems, what we do (numbered rows), the package card, the industries (website), the FAQ and the other services. --}}
@section('title', $page['title'])
@section('breadcrumb', $page['menu'])
@section('g_lean', 'inner')

@php($pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT))

@section('content')
@include('partials.g.page-head', ['eyebrow' => __('Услуги'), 'heading' => $page['title'], 'sub' => $page['lead']])

<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono"><b>01</b>{{ __('Познато ли ви е?') }}</p>
            <ul class="g-list g-problems">
                @foreach($page['problems'] as $problem)<li><span>{{ $problem }}</span></li>@endforeach
            </ul>
        </div>
        <div>
            <p class="g-mono g-mute g-rows-label"><x-icon :name="$page['icon']" class="g-ico" />{{ __('Какво правим') }}</p>
            <ol class="g-rows g-rows-tight">
                @foreach($page['includes'] as $item)
                    <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $item }}</h3></li>
                @endforeach
                <li class="g-end"><span class="g-n g-mono g-eq">=</span><h3>{{ $page['result'] }}</h3></li>
            </ol>
        </div>
    </div>
</section>

<section class="g-band">
    <div class="g-wrap g-pkgcard">
        <div>
            @if($plan)
                <p class="g-mono">{{ __('Включено в пакет') }}</p>
                <h2 class="g-h">{{ $plan['name'] }}</h2>
                <p class="g-band-text">{{ $plan['prices'][0] ?? '' }}</p>
            @else
                <p class="g-mono">{{ __('Цена') }}</p>
                <h2 class="g-h">{{ __('Цена по оферта') }}</h2>
            @endif
            @if(filled($page['note'] ?? null))<p class="g-band-text">{{ $page['note'] }}</p>@endif
        </div>
        <div class="g-cta">
            <a class="g-btn g-onband" href="{{ lroute('contact') }}#konsultatsiya">{{ __('Запазете час за разговор') }}<i></i></a>
            <a class="g-btn g-ghost g-onband-ghost" href="{{ lroute('packages') }}">{{ __('Вижте пакетите') }}</a>
        </div>
    </div>
</section>

@if($key === 'website' && app()->getLocale() === 'bg')
<section class="g-s">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>02</b>Бранш</p>
            <h2 class="g-h">Сайт за вашия бранш</h2>
            <p class="g-sh-text">Какво трябва да има сайтът, с концепция и подходящ пакет за всеки бранш.</p>
        </div>
        <div class="g-news g-links">
            @foreach(config('creatium.industry_pages') as $industrySlug => $industry)
                <a href="{{ route('industry', $industrySlug) }}"><span class="g-mono">{{ $pad($loop->iteration) }}</span><span>{{ $industry['title'] }}</span></a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono"><b>{{ $key === 'website' && app()->getLocale() === 'bg' ? '03' : '02' }}</b>{{ __('Въпроси') }}</p>
            <h2 class="g-h">{{ __('Често задавани въпроси') }}</h2>
        </div>
        @include('partials.g.faq', ['items' => $page['faq']])
    </div>
</section>

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono">{{ __('Другите ни услуги') }}</p>
        </div>
        <div class="g-news g-links">
            @foreach($others as $otherKey => $other)
                <a href="{{ \App\Http\Controllers\PageController::serviceUrl($otherKey) }}"><x-icon :name="$other['icon']" class="g-ico" /><span>{{ $other['menu'] }}</span></a>
            @endforeach
            <a href="{{ lroute('site-check') }}"><x-icon name="gauge-high" class="g-ico" /><span>{{ __('Безплатна проверка на сайт') }}</span></a>
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
            'provider' => ['@type' => 'Organization', 'name' => 'Creatium Lab', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($qa) => ['@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]], $page['faq']),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endsection
