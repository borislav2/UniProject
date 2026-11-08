@extends('layouts.public')

{{-- A blog post in design G. The title keeps its own punctuation (the owners' articles), the date is the real publishing date. --}}
@section('title', $post->tr('title'))
@section('g_lean', 'inner')

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
    <header class="g-ph g-post-head">
        <div class="g-wrap g-narrow">
            <p class="g-mono g-eyebrow"><a href="{{ lroute('blog.index') }}">{{ __('Новини') }}</a></p>
            <h1 class="g-h">{{ $post->tr('title') }}</h1>
            <p class="g-post-meta g-mono">
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale(app()->getLocale())->translatedFormat('j F Y') }}</time>
                @if($post->category && $post->category->tr('slug'))
                    <a href="{{ lroute('blog.category', ['slug' => $post->category->tr('slug')]) }}">{{ $post->category->trOrBg('name') }}</a>
                @endif
            </p>
        </div>
    </header>

    <div class="g-s" style="border-bottom:0">
        <div class="g-wrap g-narrow">
            @if($post->cover_image)
                <div class="g-frame g-cover"><img src="{{ asset($post->cover_image) }}" alt="" width="1200" height="675" fetchpriority="high"></div>
            @endif
            @if($post->tr('excerpt'))<p class="g-post-lead">{{ $post->tr('excerpt') }}</p>@endif
            <div class="g-prose g-article">
                {!! $post->bodyHtml() !!}
            </div>

            <div class="g-share">
                <p class="g-mono">{{ __('Полезна ли ви беше статията? Споделете я.') }}</p>
                <div class="g-share-row">
                    <a class="g-btn g-ghost g-sm" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->url()) }}" target="_blank" rel="noopener">{{ __('Сподели във Facebook') }}</a>
                    <button type="button" class="g-btn g-ghost g-sm" data-copy-link="{{ $post->url() }}"><span data-copy-label>{{ __('Копирай връзката') }}</span></button>
                    @if(config('creatium.contact.facebook'))
                        <a class="g-link" href="{{ config('creatium.contact.facebook') }}" target="_blank" rel="noopener">{{ __('Последвайте ни във Facebook за новите статии') }}</a>
                    @endif
                </div>
            </div>
            <script>
                document.querySelectorAll('[data-copy-link]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var label = button.querySelector('[data-copy-label]');
                        navigator.clipboard && navigator.clipboard.writeText(button.dataset.copyLink).then(function () { label.textContent = @json(__('Връзката е копирана')); });
                    });
                });
            </script>
        </div>
    </div>
</article>

@if($related->isNotEmpty())
    <section class="g-s" style="border-top:1px solid var(--g-line)">
        <div class="g-wrap">
            <div class="g-sh"><p class="g-mono">{{ __('Още новини') }}</p></div>
            <div class="g-posts">
                @foreach($related as $post)
                    @include('g.blog._card')
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
