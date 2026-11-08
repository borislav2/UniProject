@extends('layouts.public')

{{-- /paketi in design G: three price cards in the hero, then everything included, steps, terms, the price FAQ, a call block and the quote form. --}}
@section('title', __('Цена за изработка на сайт с пакети от €400'))
@section('meta_description', __('Цени от €400 старт и €200/месец или €800 старт и €650/месец. Индивидуален маркетинг от €1 200/месец. Фиксирана цена и 2 месеца безплатно при годишно плащане.'))
@section('g_lean', 'inner')

@php
    $planItems = array_values((array) ($plans['items'] ?? []));
    $contactInfo = config('creatium.contact');
    $recIndex = collect($planItems)->search(fn ($p) => ! empty($p['highlighted']));
    $billingOn = ! empty($billing['enabled']);
    $badgeText = collect($planItems)->pluck('badge')->filter()->first() ?: __('Препоръчваме');
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    $faqLd = [
        '@' . 'context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faq ?? [])->map(fn ($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ])->values()->all(),
    ];
@endphp

@if(! empty($faq))
    @push('head')
        <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endpush
@endif

@section('content')
<section class="g-ph g-ph-packs" id="paketi">
    <div class="g-wrap">
        <p class="g-mono g-eyebrow">{{ __('Пакети и цени') }}</p>
        <h1 class="g-h">{{ __('Изберете как да расте вашият бизнес') }}</h1>
        @if(filled($plans['intro'] ?? null))<p class="g-lead">{{ $plans['intro'] }}</p>@endif

        <div data-g-billing="m" data-g-billing-root class="g-packs-wrap">
            @if($billingOn)
                <div class="g-tog" role="group" aria-label="{{ __('Плащане') }}"><button type="button" data-b="m" aria-pressed="true">{{ __('Месечно') }}</button><button type="button" data-b="y" aria-pressed="false">{{ __('Годишно') }}</button></div>
                <p class="g-tognote">{{ $billing['yearly_label'] ?? '' }}. {{ __('Цените са в евро.') }}</p>
            @endif
            <div class="g-packs g-packs-sm">
                @foreach($planItems as $i => $plan)
                    @php
                        $isRec = $i === $recIndex;
                        $card = \App\Support\PlanBilling::cardPrice($billing, $plan, $i);
                        $monthly = \App\Support\PlanBilling::cell($billing, $plan, $i, 'monthly');
                        $yearly = \App\Support\PlanBilling::cell($billing, $plan, $i, 'yearly');
                        $button = filled($plan['button'] ?? null) ? $plan['button'] : ($plans['button'] ?? __('Получете оферта'));
                    @endphp
                    <article id="paket-{{ $i }}" class="g-pk {{ $isRec ? 'g-rec' : '' }}" data-plan="{{ $i }}">
                        <div class="g-top"><span class="g-mono {{ $isRec ? '' : 'g-mute' }}">{{ $pad($i + 1) }}</span>@if($isRec)<span class="g-mono">{{ $badgeText }}</span>@endif</div>
                        <div class="g-name">
                            <h2>{{ $plan['name'] }}</h2>
                            @if(filled($plan['audience'] ?? null))<p class="g-mute">{{ $plan['audience'] }}</p>@endif
                        </div>
                        @if($monthly['priced'])
                            <div class="g-price">{{ $card['from'] }}</div>
                            <p class="g-then g-mute">@if($card['caption'] !== ''){{ \Illuminate\Support\Str::ucfirst($card['caption']) }}, @endif
                                <b data-bv="m">{{ __('след това') }} {{ $monthly['price'] }} {{ $monthly['suffix'] }}</b><b data-bv="y">{{ __('след това') }} {{ $yearly['price'] }} {{ $yearly['suffix'] }}</b></p>
                        @else
                            <div class="g-price">{{ $card['from'] }}@if($card['caption'] !== '')<small>{{ $card['caption'] }}</small>@endif</div>
                            <p class="g-then g-mute">{{ $card['second'] }}</p>
                        @endif
                        <div class="g-body">
                            <ul>
                                @foreach(\App\Support\PlanBilling::cardItems($plan) as $line)<li>{{ $line }}</li>@endforeach
                            </ul>
                            <a class="g-link" href="#vsichko-{{ $i }}">{{ __('Вижте всичко включено') }}</a>
                        </div>
                        <a class="g-btn" href="{{ lroute('packages') }}?paket={{ $i }}#oferta">{{ $button }}<i></i></a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Everything included --}}
<section class="g-s">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>01</b>{{ __('Подробно') }}</p>
            <h2 class="g-h">{{ $plans['all_title'] ?? __('Всичко включено във всеки пакет') }}</h2>
        </div>
        <div class="g-cols3">
            @foreach($planItems as $i => $plan)
                <div id="vsichko-{{ $i }}" class="g-col">
                    <h3>{{ $plan['name'] }}</h3>
                    @foreach([['build_title', 'build_items'], ['monthly_title', 'monthly_items']] as [$titleKey, $itemsKey])
                        @if(! empty($plan[$itemsKey]))
                            @if(filled($plan[$titleKey] ?? null))<p class="g-mono g-mute">{{ $plan[$titleKey] }}</p>@endif
                            <ul class="g-list">
                                @foreach($plan[$itemsKey] as $line)
                                    @php([$head, $rest] = str_contains($line, ': ') ? explode(': ', $line, 2) : [null, $line])
                                    <li>@if($head)<b>{{ $head }}</b>@endif<span>{{ $rest }}</span></li>
                                @endforeach
                            </ul>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>

@if(! empty($plans['steps']))
<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono"><b>02</b>{{ __('Процес') }}</p>
            <h2 class="g-h">{{ $plans['steps_title'] ?? __('Как започваме') }}</h2>
        </div>
        <ol class="g-rows">
            @foreach($plans['steps'] as $step)
                <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['text'] }}</p></li>
            @endforeach
        </ol>
    </div>
</section>
@endif

@if(! empty($plans['terms']))
<section class="g-s">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>03</b>{{ __('Условия') }}</p>
            <h2 class="g-h">{{ $plans['terms_title'] ?? __('Условия') }}</h2>
        </div>
        <div class="g-cells">
            @foreach($plans['terms'] as $term)
                <div><span class="g-mono g-mute">{{ $pad($loop->iteration) }}</span><h3>{{ $term['title'] }}</h3><p>{{ $term['text'] }}</p></div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(! empty($faq))
<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono"><b>04</b>{{ __('Въпроси') }}</p>
            <h2 class="g-h">{{ $plans['faq_title'] ?? __('Въпроси') }}</h2>
            <a class="g-link" href="{{ lroute('faq') }}">{{ __('Всички въпроси') }}</a>
        </div>
        @include('partials.g.faq', ['items' => $faq])
    </div>
</section>
@endif

{{-- Prefer a call? --}}
<section class="g-band">
    <div class="g-wrap g-callgrid">
        <div>
            <p class="g-mono">{{ __('Разговор') }}</p>
            <h2 class="g-h">{{ $plans['call_title'] ?? __('Предпочитате разговор?') }}</h2>
            @if(filled($plans['call_text'] ?? null))<p class="g-band-text">{{ $plans['call_text'] }}</p>@endif
            <a class="g-btn g-onband" href="{{ lroute('contact') }}#konsultatsiya">{{ __('Запазете час за разговор') }}<i></i></a>
        </div>
        @include('partials.g.phones', ['phones' => $contactInfo['phones'] ?? []])
    </div>
</section>

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap">
        @include('partials.g.quote-form', ['plans' => $plans, 'selectedPlan' => $selectedPlan])
    </div>
</section>
@endsection
