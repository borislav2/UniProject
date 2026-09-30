<section class="py-16 md:py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-brand-gradient px-6 py-12 md:px-14 md:py-14 text-white reveal">
            <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
            <div class="absolute -bottom-24 -right-16 w-72 h-72 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">Имате нужда от сайт?</h2>
                    <p class="mt-2 text-brand-100/90">Пишете ни. Първият разговор е безплатен.</p>
                </div>
                <a href="{{ route('contact') }}" class="shrink-0 inline-flex items-center justify-center gap-2 bg-white text-brand-950 px-7 py-3.5 rounded-xl font-semibold hover:bg-brand-50 transition-colors">
                    Поискайте оферта <x-icon name="arrow-right" class="text-sm" />
                </a>
            </div>
        </div>
    </div>
</section>
