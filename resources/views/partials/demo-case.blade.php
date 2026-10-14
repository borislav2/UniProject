{{-- Под всяко демо: трудностите на бранша, какво прави сайтът за тях, подходящ пакет и въпроси (данните са в creatium.demos). --}}
@php
    $package = $demo['package']['name'] ? collect(site('packages'))->firstWhere('name', $demo['package']['name']) : null;
@endphp
<section id="za-bransha" class="scroll-mt-20 bg-[#f5f8fc] text-gray-900 border-t border-gray-200 font-sans">
    <div class="max-w-6xl mx-auto px-4 py-16 md:py-20">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-600">Creatium Lab · {{ $demo['industry'] }}</p>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950">Какво решава този сайт за бранша</h2>
            <p class="mt-4 text-gray-600 leading-relaxed">Демото е измислено, но проблемите са истински. Ето с какво се сблъскват бизнесите от бранша онлайн и какво правим за тях.</p>
        </div>

        <div class="mt-12 grid lg:grid-cols-2 gap-6">
            <div class="rounded-2xl bg-white p-6 md:p-8 ring-1 ring-gray-200">
                <h3 class="flex items-center gap-3 text-lg font-bold text-brand-950"><span class="w-9 h-9 rounded-xl bg-red-50 text-red-600 inline-flex items-center justify-center"><x-icon name="triangle-exclamation" class="text-sm" /></span>Проблемите на бранша</h3>
                <ul class="mt-6 space-y-5">
                    @foreach($demo['problems'] as [$title, $text])
                        <li><p class="font-semibold text-brand-950">{{ $title }}</p><p class="mt-1 text-gray-600 leading-relaxed">{{ $text }}</p></li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-2xl bg-white p-6 md:p-8 ring-1 ring-gray-200">
                <h3 class="flex items-center gap-3 text-lg font-bold text-brand-950"><span class="w-9 h-9 rounded-xl bg-green-50 text-green-700 inline-flex items-center justify-center"><x-icon name="check" class="text-sm" /></span>Как ги решаваме</h3>
                <ul class="mt-6 space-y-5">
                    @foreach($demo['solutions'] as [$title, $text])
                        <li><p class="font-semibold text-brand-950">{{ $title }}</p><p class="mt-1 text-gray-600 leading-relaxed">{{ $text }}</p></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-6 rounded-2xl bg-[#0f1a2b] text-white p-6 md:p-10 grid md:grid-cols-12 gap-8 items-center">
            <div class="md:col-span-7">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#9cc0eb]">Подходящ пакет</p>
                <h3 class="mt-3 text-2xl md:text-3xl font-extrabold">{{ $package ? 'Пакет ' . $package['name'] : 'Индивидуална оферта' }}</h3>
                <p class="mt-3 text-[#d6e3f5] leading-relaxed">{{ $demo['package']['why'] }}</p>
                @if($package && ! empty($package['price_note']))
                    <p class="mt-3 font-semibold">{{ $package['price_note'] }}</p>
                @endif
            </div>
            <div class="md:col-span-5">
                @if($package)
                    <ul class="space-y-2.5">
                        @foreach($package['features'] as $feature)
                            <li class="flex items-start gap-3 text-[#d6e3f5]"><x-icon name="check" class="mt-1 text-xs text-[#9cc0eb]" />{{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}#service-website" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 font-semibold text-[#0f1a2b] hover:bg-[#e6eefc]">Свържете се с нас <x-icon name="arrow-right" class="text-xs" /></a>
                    <a href="{{ route('packages') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/30 px-5 py-3 font-semibold text-white hover:bg-white/10">Всички пакети</a>
                </div>
            </div>
        </div>

        <div class="mt-16 max-w-3xl">
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950">Въпроси и отговори</h2>
            <div class="mt-6 divide-y divide-gray-200 border-y border-gray-200">
                @foreach($demo['faq'] as [$question, $answer])
                    <details class="group py-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-brand-950 [&::-webkit-details-marker]:hidden">
                            {{ $question }}
                            <x-icon name="chevron-down" class="shrink-0 text-xs text-gray-500 transition-transform group-open:rotate-180" />
                        </summary>
                        <p class="mt-3 text-gray-600 leading-relaxed">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</section>
