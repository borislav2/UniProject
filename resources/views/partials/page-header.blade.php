<section class="hero-gradient text-white py-16 md:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-5xl font-extrabold mb-4">{{ $heading }}</h1>
        @isset($sub)
            <p class="text-lg md:text-xl text-blue-100">{{ $sub }}</p>
        @endisset
    </div>
</section>
