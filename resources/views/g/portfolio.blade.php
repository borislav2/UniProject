@extends('layouts.public')

{{-- /proekti in design G: one grid of equal 16:10 previews, real projects first, then the concepts with the „Концепция“ chip. --}}
@section('title', __('Проекти и концепции за сайтове за ресторант, салон и автосервиз'))
@section('meta_description', __('Концепции за сайтове за ресторант, салон, зъболекар, автосервиз, строителна фирма и онлайн магазин. Вижте как изглежда сайт, който носи запитвания.'))
@section('g_lean', 'inner')

@php
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    $n = 0;
@endphp

@section('content')
@include('partials.g.page-head', ['eyebrow' => __('Проекти'), 'heading' => __('Разгледайте работата ни')])

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap">
        @if($projects->isNotEmpty() || ! empty($demos))
            <div class="g-work" data-portfolio-grid>
                @foreach($projects as $project)
                    @php($host = preg_replace('/^www\./', '', (string) parse_url($project->website_url, PHP_URL_HOST)))
                    <article class="g-case">
                        <figure>
                            @if($project->website_url)<a href="{{ $project->website_url }}" target="_blank" rel="noopener" tabindex="-1" aria-hidden="true">@endif
                            <div class="g-frame">
                                @if($project->screenshot)
                                    <img src="{{ asset($project->screenshot) }}" alt="{{ $project->name }}" width="960" height="600" loading="lazy" decoding="async">
                                @else
                                    <div class="g-noimg g-mono">{{ $project->name }}</div>
                                @endif
                            </div>
                            @if($project->website_url)</a>@endif
                            <figcaption>
                                <span class="g-no g-mono">{{ $pad(++$n) }}</span>
                                <span class="g-mute g-mono">{{ $project->category->name }}</span>
                                <strong>{{ $project->name }}</strong>
                            </figcaption>
                        </figure>
                        <p class="g-case-text">{{ $project->description }}</p>
                        @if($project->website_url)<a class="g-link" href="{{ $project->website_url }}" target="_blank" rel="noopener">{{ __('Посетете сайта') }} {{ $host }}</a>@endif
                    </article>
                @endforeach

                @foreach($demos as $demo)
                    @php($industrySlug = app()->getLocale() === 'bg' ? collect(config('creatium.industry_pages'))->search(fn ($page) => $page['demo'] === $demo['slug']) : false)
                    <article class="g-case">
                        <figure>
                            <a href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener" tabindex="-1" aria-hidden="true">
                                <div class="g-frame">
                                    @if(is_file(public_path('images/demos/' . $demo['slug'] . '.webp')))
                                        <img src="{{ asset('images/demos/' . $demo['slug'] . '.webp') }}" srcset="{{ demo_srcset($demo['slug']) }}" sizes="(min-width: 760px) 50vw, 100vw" alt="" width="960" height="600" loading="lazy" decoding="async">
                                    @endif
                                </div>
                            </a>
                            <figcaption>
                                <span class="g-no g-mono">{{ $pad(++$n) }}</span>
                                <span class="g-chip g-mono" data-concept-chip>{{ __('Концепция') }}</span>
                                <strong>{{ $demo['industry'] }}</strong>
                            </figcaption>
                        </figure>
                        <h2 class="g-case-name">{{ $demo['name'] }}</h2>
                        <p class="g-case-text">{{ $demo['description'] }}</p>
                        <div class="g-case-links">
                            <a class="g-link" href="{{ route('demo.show', $demo['slug']) }}" target="_blank" rel="noopener">{{ __('Вижте сайта') }}</a>
                            @if($industrySlug !== false)<a class="g-link g-sub" href="{{ route('industry', $industrySlug) }}">{{ config("creatium.industry_pages.$industrySlug.title") }}</a>@endif
                        </div>
                    </article>
                @endforeach
            </div>
            @if(app()->getLocale() === 'en' && ! empty($demos))
                <p class="g-hint g-after">{{ __('Сайтовете-концепции са на български.') }}</p>
            @endif
            <p class="g-after">{{ __('Ако искате вашият да е един от тях, пишете ни.') }} <a class="g-link" href="{{ lroute('contact') }}">{{ __('Свържете се с нас') }}</a></p>
        @else
            <div class="g-empty">
                <h2 class="g-h">{{ __('Тук скоро ще има проекти') }}</h2>
                <p>{{ __('Ако искате вашият да е един от тях, пишете ни.') }}</p>
                <a class="g-btn" href="{{ lroute('contact') }}">{{ __('Свържете се с нас') }}<i></i></a>
            </div>
        @endif
    </div>
</section>
@endsection
