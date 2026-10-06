@extends('layouts.public')

@section('title', $post->tr('title'))

@push('head')
    <meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at->toAtomString() }}">
    @php($ld = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->tr('title'),
        'description' => $post->excerptOrSummary(),
        'datePublished' => $post->published_at->toAtomString(),
        'dateModified' => $post->updated_at->toAtomString(),
        'inLanguage' => app()->getLocale(),
        'mainEntityOfPage' => $post->url(),
        'image' => $post->cover_image ? asset($post->cover_image) : asset('images/og-image.png'),
        'author' => ['@type' => 'Organization', 'name' => 'Creatium Lab', 'url' => lroute('home')],
        'publisher' => ['@type' => 'Organization', 'name' => 'Creatium Lab', 'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo.png')]],
    ])
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<article>
    <header class="relative overflow-hidden bg-brand-gradient text-white dark:border-b dark:border-white/10">
        <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
            <a href="{{ lroute('blog.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-200 hover:text-white"><x-icon name="arrow-left" class="text-xs" /> {{ __('Блог') }}</a>
            <h1 class="mt-5 text-3xl md:text-5xl font-extrabold tracking-tight leading-tight">{{ $post->tr('title') }}</h1>
            <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-brand-100/90">
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale(app()->getLocale())->translatedFormat('j F Y') }}</time>
                @if($post->category && $post->category->tr('slug'))
                    <a href="{{ lroute('blog.category', ['slug' => $post->category->tr('slug')]) }}" class="rounded-full bg-white/10 px-3 py-1 font-semibold hover:bg-white/20">{{ $post->category->trOrBg('name') }}</a>
                @endif
            </div>
        </div>
    </header>

    <div class="bg-white dark:bg-ink-950">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            @if($post->cover_image)
                <img src="{{ asset($post->cover_image) }}" alt="" width="1200" height="675" fetchpriority="high" class="w-full aspect-[16/9] object-cover rounded-2xl mb-10 ring-1 ring-gray-200 dark:ring-white/10">
            @endif
            @if($post->tr('excerpt'))
                <p class="text-xl text-gray-700 leading-relaxed mb-8 dark:text-gray-200">{{ $post->tr('excerpt') }}</p>
            @endif
            <div class="prose prose-lg max-w-none prose-headings:font-extrabold prose-headings:tracking-tight prose-headings:text-brand-950 prose-a:text-brand-700 prose-img:rounded-xl dark:prose-invert dark:prose-headings:text-white dark:prose-a:text-brand-300">
                {!! $post->bodyHtml() !!}
            </div>
        </div>
    </div>
</article>

@if($related->isNotEmpty())
    <section class="py-16 md:py-20 bg-brand-50/60 dark:bg-ink-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 mb-10 dark:text-white">{{ __('Още от блога') }}</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($related as $post)
                    @include('blog._card')
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
