{{--
    Схемата на „Дигиталния двигател“: център и четирите компонента от services_page.cards, всеки води към картата си на /uslugi.
    $cards: services_page.cards, $center: текстът в центъра. Слага се върху тъмна секция (on-dark).
--}}
@php
    $engineNodes = array_values(array_map(function ($card) {
        preg_match('/^(.+?)\s*\((.+)\)\s*$/u', $card['label'], $m);
        return ['slug' => $card['slug'], 'icon' => $card['icon'], 'name' => $m[1] ?? $card['label'], 'tag' => $m[2] ?? null];
    }, $cards));
@endphp
@if(count($engineNodes) === 4)
    <div class="reveal relative mt-14 grid gap-4 md:grid-cols-2 md:gap-x-48 md:gap-y-16 lg:gap-x-72" style="--reveal-delay: 80ms">
        <svg class="pointer-events-none absolute inset-0 hidden md:block w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <g class="engine-lines" stroke="rgb(143 181 224 / 0.45)" stroke-width="0.25" fill="none" vector-effect="non-scaling-stroke">
                <path d="M50 50 L22 22" vector-effect="non-scaling-stroke"/><path d="M50 50 L78 22" vector-effect="non-scaling-stroke"/>
                <path d="M50 50 L22 78" vector-effect="non-scaling-stroke"/><path d="M50 50 L78 78" vector-effect="non-scaling-stroke"/>
            </g>
        </svg>
        <div class="order-first md:order-none md:absolute md:left-1/2 md:top-1/2 md:-translate-x-1/2 md:-translate-y-1/2 z-10 flex justify-center">
            <div class="engine-hub relative flex flex-col items-center justify-center text-center w-36 h-36 md:w-44 md:h-44 rounded-full bg-brand-gradient ring-1 ring-white/25 shadow-[0_0_80px_-10px_rgb(59_119_189/0.8)]">
                <span class="absolute inset-0 rounded-full ring-1 ring-brand-300/40 engine-pulse" aria-hidden="true"></span>
                <x-icon name="cogs" class="text-2xl text-brand-100" />
                <span class="mt-2 px-4 text-sm md:text-base font-extrabold leading-tight">{{ $center }}</span>
            </div>
        </div>
        @foreach($engineNodes as $node)
            <a href="{{ lroute('services') }}#{{ $node['slug'] }}" class="relative edge glass group rounded-2xl p-5 md:p-6 flex items-center gap-4 hover:bg-white/10 transition-colors">
                <span class="w-12 h-12 shrink-0 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon :name="$node['icon']" class="text-brand-200" /></span>
                <span class="min-w-0">
                    <span class="block text-xs font-bold tracking-[0.18em] text-brand-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="block font-extrabold text-lg leading-snug">{{ $node['name'] }}</span>
                    @if($node['tag'])<span class="block text-sm text-gray-400">{{ $node['tag'] }}</span>@endif
                </span>
                <x-icon name="arrow-right" class="ml-auto text-xs text-brand-300 transition-transform group-hover:translate-x-1" />
            </a>
        @endforeach
    </div>
@endif
