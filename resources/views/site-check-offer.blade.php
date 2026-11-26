@extends('layouts.public')

{{-- Поръчка с един клик от писмата след проверката на сайт, стар дизайн. Подписан адрес, noindex (SiteCheckOfferController). --}}
@php
    $fix = $offer === 'fix';
    $name = $text['name'] ?? '';
@endphp
@section('title', $done ? __('Благодарим ви') : $name)
@section('meta_description', $text['lead'] ?? '')

@section('content')
@include('partials/page-header', [
    'heading' => $done ? __('Благодарим ви') : $name,
    'sub' => $done
        ? __('Получихме заявката ви за „:offer“. Ще ви пишем в рамките на един работен ден.', ['offer' => $name])
        : \App\Support\SiteCheck\Offers::fill((string) ($text['lead'] ?? ''), $offer),
])
<section class="py-16 md:py-24 bg-white dark:bg-ink-950">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-700 leading-relaxed dark:text-gray-300">
        @if($done)
            <p>{{ __('Сайт') }} {{ $domain }}. {{ \App\Support\SiteCheck\Offers::priceLine($offer) }}.</p>
            <a href="{{ lroute('home') }}" class="mt-6 inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">{{ __('Към началото') }} <x-icon name="arrow-right" class="text-xs" /></a>
        @else
            @if($fix)
                <p class="font-semibold text-brand-950 dark:text-white">{{ \App\Support\SiteCheck\Offers::priceLine($offer) }}. {{ \App\Support\SiteCheck\Offers::fill((string) ($text['days_line'] ?? ''), $offer) }}</p>
                <p class="mt-3">{{ \App\Support\SiteCheck\Offers::fill((string) ($text['credit'] ?? ''), $offer) }}</p>
            @else
                <p class="font-semibold text-brand-950 dark:text-white">{{ \App\Support\SiteCheck\Offers::priceLine($offer) }}.</p>
                <ul class="mt-3 list-disc pl-5 space-y-1">@foreach((array) ($text['items'] ?? []) as $item)<li>{{ $item }}</li>@endforeach</ul>
            @endif
            <p class="mt-3">{{ __('Сайт') }} {{ $domain }}. {{ $text['note'] ?? '' }}</p>
            <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6">
                @csrf
                <button type="submit" class="btn-shine inline-flex items-center justify-center gap-2 bg-brand-950 text-white py-3.5 px-8 rounded-xl font-semibold hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">{{ __('Потвърдете') }}</button>
            </form>
        @endif
    </div>
</section>
@endsection
