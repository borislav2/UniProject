<section class="relative overflow-hidden bg-brand-gradient text-white dark:border-b dark:border-white/10">
    <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
    <div class="absolute -top-32 -right-24 w-[28rem] h-[28rem] rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 text-center">
        @isset($eyebrow)
            <span class="text-brand-300 text-sm font-bold uppercase tracking-wider">{{ $eyebrow }}</span>
        @endisset
        <h1 class="mt-2 text-4xl md:text-5xl font-extrabold tracking-tight">{{ $heading }}</h1>
        @isset($sub)
            <p class="mt-5 text-lg md:text-xl text-brand-100/90 max-w-2xl mx-auto">{{ $sub }}</p>
        @endisset
    </div>
</section>
