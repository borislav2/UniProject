@extends('layouts.public')

{{-- „Изработка на сайт за …“ in design G (Bulgarian only): the concept, what the site must have, problems and solutions, the package, the FAQ. --}}
@section('title', $page['title'])
@section('breadcrumb', $page['title'])
@section('g_lean', 'inner')

@php($pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT))

@section('content')
@include('partials.g.page-head', ['eyebrow' => 'Изработка на сайт', 'heading' => $page['title'], 'sub' => $page['lead']])

<section class="g-s">
    <div class="g-wrap g-sys g-ind">
        <div class="g-sh">
            <p class="g-mono"><b>01</b>Концепция</p>
            <a href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener" class="g-frame-link">
                <div class="g-frame">
                    @if(is_file(public_path('images/demos/' . $demo['slug'] . '.webp')))
                        <img src="{{ asset('images/demos/' . $demo['slug'] . '.webp') }}" srcset="{{ demo_srcset($demo['slug']) }}" sizes="(min-width: 900px) 40vw, 100vw" alt="Концепция {{ $demo['industry'] }}" width="960" height="600" loading="eager" decoding="async">
                    @endif
                </div>
            </a>
            <p class="g-case-links"><span class="g-chip g-mono" data-concept-chip>Концепция</span><a class="g-link" href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener">Вижте сайта</a></p>
        </div>
        <div>
            <h2 class="g-h g-rows-h">{{ $page['must_have_title'] }}</h2>
            <ol class="g-rows">
                @foreach($page['must_have'] as [$title, $text])
                    <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p></li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

<section class="g-s">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>02</b>{{ $demo['industry'] }}</p>
            <h2 class="g-h">Къде губите клиенти и как го оправяме</h2>
        </div>
        <div class="g-two-cols">
            <div class="g-col">
                <h3 class="g-mono g-mute">Къде се губят клиенти</h3>
                <ol class="g-rows g-rows-tight">
                    @foreach($demo['problems'] as [$title, $text])<li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p></li>@endforeach
                </ol>
            </div>
            <div class="g-col">
                <h3 class="g-mono g-mute">Как го оправя сайтът</h3>
                <ol class="g-rows g-rows-tight">
                    @foreach($demo['solutions'] as [$title, $text])<li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p></li>@endforeach
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="g-band">
    <div class="g-wrap g-pkgcard">
        <div>
            <p class="g-mono">Подходящ пакет</p>
            <h2 class="g-h">{{ $plan['name'] ?? 'Индивидуална оферта' }}</h2>
            @if($plan)
                <ul class="g-band-list">@foreach($plan['prices'] as $price)<li>{{ $price }}</li>@endforeach</ul>
            @endif
            <p class="g-band-text">{{ $demo['package']['why'] }}</p>
        </div>
        <div class="g-cta">
            <a class="g-btn g-onband" href="{{ lroute('contact') }}#konsultatsiya">Запазете час за разговор<i></i></a>
            <a class="g-btn g-ghost g-onband-ghost" href="{{ lroute('packages') }}">Вижте пакетите</a>
        </div>
    </div>
</section>

<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono"><b>03</b>Въпроси</p>
            <h2 class="g-h">Често задавани въпроси</h2>
        </div>
        @include('partials.g.faq', ['items' => $demo['faq']])
    </div>
</section>

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap">
        <div class="g-sh"><p class="g-mono">Сайт за друг бранш</p></div>
        <div class="g-news g-links">
            @foreach($others as $otherSlug => $other)
                <a href="{{ route('industry', $otherSlug) }}"><span class="g-mono">{{ $pad($loop->iteration) }}</span><span>{{ $other['title'] }}</span></a>
            @endforeach
            <a href="{{ \App\Http\Controllers\PageController::serviceUrl('website') }}"><span class="g-mono">+</span><span>Изработка на сайт за всеки бизнес</span></a>
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
            'serviceType' => 'Изработка на сайт',
            'audience' => ['@type' => 'BusinessAudience', 'name' => $page['menu']],
            'areaServed' => ['@type' => 'Country', 'name' => 'Bulgaria'],
            'provider' => ['@type' => 'Organization', 'name' => 'Creatium Lab', 'url' => url('/')],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($qa) => ['@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]], $demo['faq']),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endsection
