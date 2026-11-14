@extends('layouts.public')

@section('title', __('Въпроси за изработка на сайт, цена, срокове и SEO'))
@section('meta_description', __('Отговори на най-честите въпроси за цени, плащане, срокове, Google и AI видимост и грижата за сайта след пускането.'))
@section('g_lean', 'inner')

@push('head')
    @php($ld = [
        '@' . 'context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $groups->flatten(1)->map(fn ($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ])->values()->all(),
    ])
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@include('partials.g.page-head', ['heading' => __('Често задавани въпроси'), 'sub' => __('Цени, срокове, Google и AI, грижата след пускането. Ако не намерите отговор, попитайте ни директно.')])

<section class="g-s g-last">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            @if($groups->keys()->filter()->isNotEmpty())
                <nav class="g-topics" aria-label="{{ __('Теми') }}">
                    @foreach($groups->keys()->filter() as $group)
                        <a href="#{{ Str::slug($group, '-', 'bg') }}">{{ $group }}</a>
                    @endforeach
                </nav>
            @endif
            <div class="g-askbox">
                <p class="g-askbox-h">{{ __('Не намерихте отговор?') }}</p>
                <p class="g-askbox-t">{{ __('Запазете час за разговор и ще отговорим на всичко за вашия бизнес.') }}</p>
                <a class="g-btn" href="{{ lroute('contact') }}#konsultatsiya">{{ __('Запазете час') }}</a>
            </div>
        </div>
        <div class="g-faq-groups">
            @foreach($groups as $group => $items)
                <div @if($group !== '') id="{{ Str::slug($group, '-', 'bg') }}" @endif class="g-faq-group">
                    @if($group !== '')<h2 class="g-h">{{ $group }}</h2>@endif
                    @include('partials.g.faq', ['items' => $items])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
