<article class="g-post">
    <a href="{{ $post->url() }}" class="g-post-img" tabindex="-1" aria-hidden="true">
        <div class="g-frame">
            @if($post->cover_image)
                <img src="{{ asset($post->cover_image) }}" alt="" width="640" height="360" loading="lazy" decoding="async">
            @else
                <div class="g-noimg g-mono">Creatium Lab</div>
            @endif
        </div>
    </a>
    <div class="g-post-meta g-mono">
        @if($post->category)<span>{{ $post->category->trOrBg('name') }}</span>@endif
        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale(app()->getLocale())->translatedFormat('j F Y') }}</time>
    </div>
    <{{ $heading ?? 'h2' }} class="g-post-title"><a href="{{ $post->url() }}">{{ $post->tr('title') }}</a></{{ $heading ?? 'h2' }}>
    <p class="g-post-excerpt">{{ $post->excerptOrSummary() }}</p>
</article>
