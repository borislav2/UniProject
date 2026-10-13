<section class="relative overflow-hidden surface-night grain on-dark text-white">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_top,black_25%,transparent_70%)]" aria-hidden="true"></div>
    <div class="aurora-a absolute -top-40 -right-24 w-[30rem] h-[30rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-header pb-16 md:pb-24 text-center">
        @isset($eyebrow)
            <span class="eyebrow rise-in">{{ $eyebrow }}</span>
        @endisset
        <h1 class="display rise-in mt-4 text-4xl sm:text-5xl md:text-6xl" style="--d: 80ms">{{ $heading }}</h1>
        @if(filled($sub ?? null))
            <p class="rise-in mt-6 text-lg md:text-xl text-gray-300 max-w-2xl mx-auto leading-relaxed text-pretty" style="--d: 160ms">{{ $sub }}</p>
        @endif
    </div>
    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-brand-400/50 to-transparent" aria-hidden="true"></div>
</section>
