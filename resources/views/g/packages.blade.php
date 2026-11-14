@extends('layouts.public')

{{-- /paketi in design G (fourth round): three plans with groups of what is included, the five steps in a row and the quote form. --}}
@section('title', __('Пакети и цени за сайт и дигитален маркетинг от €500'))
@section('meta_description', __('Дигитален старт от €500, Активен маркетинг от €800 и Стратегически маркетинг от €1 200 на месец. Ясна структура за изграждане, поддръжка и устойчив растеж.'))
@section('g_lean', 'inner')

@php
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    $gSteps = collect((array) ($plans['g_steps'] ?? []))->filter(fn ($s) => filled($s['title'] ?? null))->values();
@endphp

@section('content')
<section class="g-ph g-ph-packs" id="paketi">
    <div class="g-wrap">
        <h1 class="g-h">{{ $plans['g_title'] ?? __('План за всеки етап от вашия бизнес') }}</h1>
        @if(filled($plans['g_subtitle'] ?? null))<p class="g-lead">{{ $plans['g_subtitle'] }}</p>@endif
        @include('partials.g.plan-cards')
    </div>
</section>

@if($gSteps->isNotEmpty())
<section class="g-s" id="stapki">
    <div class="g-wrap">
        <div class="g-sh">
            <h2 class="g-h">{{ $plans['g_steps_title'] ?? __('Нашите 5 стъпки') }}</h2>
        </div>
        <ol class="g-steps">
            @foreach($gSteps as $step)
                <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['text'] ?? '' }}</p></li>
            @endforeach
        </ol>
    </div>
</section>
@endif

@include('partials.g.quote-form', ['plans' => $plans, 'selectedPlan' => $selectedPlan])
@endsection
