@extends('layouts.public')

{{-- Поръчка с един клик от писмата след проверката на сайт, дизайн Г. Подписан адрес, noindex. GET показва потвърждение, POST записва интереса
     (SiteCheckOfferController). Текстовете на офертата са от site_check_offers (BG и EN). --}}
@php
    $fix = $offer === 'fix';
    $name = $text['name'] ?? '';
@endphp
@section('title', $done ? __('Благодарим ви') : $name)
@section('meta_description', $text['lead'] ?? '')
@section('g_lean', 'inner')

@section('content')
@include('partials.g.page-head', [
    'heading' => $done ? __('Благодарим ви') : $name,
    'sub' => $done
        ? __('Получихме заявката ви за „:offer“. Ще ви пишем в рамките на един работен ден.', ['offer' => $name])
        : \App\Support\SiteCheck\Offers::fill((string) ($text['lead'] ?? ''), $offer),
])
<section class="g-s g-last">
    <div class="g-wrap g-narrow">
        <div class="g-prose">
            @if($done)
                <p>{{ __('Сайт') }} {{ $domain }}. {{ \App\Support\SiteCheck\Offers::priceLine($offer) }}.</p>
                <p><a class="g-btn g-ghost" href="{{ lroute('home') }}">{{ __('Към началото') }}</a></p>
            @else
                @if($fix)
                    <p><strong>{{ \App\Support\SiteCheck\Offers::priceLine($offer) }}.</strong> {{ \App\Support\SiteCheck\Offers::fill((string) ($text['days_line'] ?? ''), $offer) }}</p>
                    <p>{{ \App\Support\SiteCheck\Offers::fill((string) ($text['credit'] ?? ''), $offer) }}</p>
                @else
                    <p><strong>{{ \App\Support\SiteCheck\Offers::priceLine($offer) }}.</strong></p>
                    <ul>@foreach((array) ($text['items'] ?? []) as $item)<li>{{ $item }}</li>@endforeach</ul>
                @endif
                <p>{{ __('Сайт') }} {{ $domain }}. {{ $text['note'] ?? '' }}</p>
                <form method="POST" action="{{ request()->fullUrl() }}">
                    @csrf
                    <button type="submit" class="g-btn g-big">{{ __('Потвърдете') }}</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
