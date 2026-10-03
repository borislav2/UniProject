<article class="reveal card-hover group rounded-2xl border border-gray-200 bg-white overflow-hidden flex flex-col dark:border-white/10 dark:bg-white/[0.03]">
    <a href="{{ $post->url() }}" class="block aspect-[16/9] bg-brand-50 overflow-hidden dark:bg-white/5" tabindex="-1" aria-hidden="true">
        @if($post->cover_image)
            <img src="{{ asset($post->cover_image) }}" alt="" width="640" height="360" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
        @else
            <span class="w-full h-full bg-brand-gradient flex items-center justify-center"><x-icon name="file" class="text-3xl text-white/40" /></span>
        @endif
    </a>
    <div class="p-6 flex flex-col flex-1">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold">
            @if($post->category)
                <span class="uppercase tracking-wider text-brand-600 dark:text-brand-300">{{ $post->category->trOrBg('name') }}</span>
            @endif
            <time datetime="{{ $post->published_at->toDateString() }}" class="text-gray-500 dark:text-gray-400">{{ $post->published_at->locale(app()->getLocale())->translatedFormat('j F Y') }}</time>
        </div>
        <h2 class="mt-3 text-xl font-bold text-brand-950 leading-snug dark:text-white"><a href="{{ $post->url() }}" class="hover:text-brand-700 dark:hover:text-brand-300">{{ $post->tr('title') }}</a></h2>
        <p class="mt-3 text-gray-600 leading-relaxed flex-1 dark:text-gray-300">{{ $post->excerptOrSummary() }}</p>
        <a href="{{ $post->url() }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-brand-700 group-hover:gap-3 transition-all dark:text-brand-300" aria-label="{{ __('Прочетете: :title', ['title' => $post->tr('title')]) }}">
            {{ __('Прочетете') }} <x-icon name="arrow-right" class="text-xs" />
        </a>
    </div>
</article>
