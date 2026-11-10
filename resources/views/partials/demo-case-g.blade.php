{{--
    The end of a concept page in design G (fourth round): the analysis of the industry, our solution, what happens after the launch, the matching package and a
    button to it. No questions and answers. Data in creatium.demos: problems, solutions, after_launch, package. Drawn with utilities because the concept pages load only app.css.
--}}
@php
    $gPlans = app(\App\Support\Content::class)->withDefaults('plans');
    $gCards = \App\Support\GPlans::cards($gPlans);
    $gIndex = \App\Support\GPlans::indexFor($gPlans, $demo['package']['name'] ?? null);
    $gCard = $gIndex !== null ? collect($gCards)->firstWhere('index', $gIndex) : null;
    $gAfter = array_values(array_filter((array) ($demo['after_launch'] ?? []), fn ($item) => filled($item[0] ?? null)));
    $gPad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    $gMono = 'font-mono text-xs font-medium uppercase tracking-[0.1em]';
@endphp
<section id="za-bransha" class="scroll-mt-20 border-t border-[#0C0E12]/15 bg-[#ECEEF0] font-sans text-[#0C0E12]">
    <div class="mx-auto max-w-6xl px-4 py-16 md:py-24">
        <p class="{{ $gMono }} text-[#4A5058]">Creatium Lab <span class="mx-1">/</span> {{ $demo['industry'] }}</p>

        <div class="mt-10 grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="{{ $gMono }} text-[#C4300F]">01</p>
                <h2 class="mt-3 text-3xl font-light tracking-tight md:text-4xl">Анализ на бранша</h2>
                <ol class="mt-8 border-t border-[#0C0E12]/50">
                    @foreach($demo['problems'] as [$title, $text])
                        <li class="grid grid-cols-[2.5rem_1fr] gap-x-3 border-b border-[#0C0E12]/15 py-5">
                            <span class="{{ $gMono }} pt-1 text-[#4A5058]">{{ $gPad($loop->iteration) }}</span>
                            <div><p class="font-medium">{{ $title }}</p><p class="mt-1 leading-relaxed text-[#4A5058]">{{ $text }}</p></div>
                        </li>
                    @endforeach
                </ol>
            </div>
            <div>
                <p class="{{ $gMono }} text-[#C4300F]">02</p>
                <h2 class="mt-3 text-3xl font-light tracking-tight md:text-4xl">Нашето решение</h2>
                <ol class="mt-8 border-t border-[#0C0E12]/50">
                    @foreach($demo['solutions'] as [$title, $text])
                        <li class="grid grid-cols-[2.5rem_1fr] gap-x-3 border-b border-[#0C0E12]/15 py-5">
                            <span class="{{ $gMono }} pt-1 text-[#4A5058]">{{ $gPad($loop->iteration) }}</span>
                            <div><p class="font-medium">{{ $title }}</p><p class="mt-1 leading-relaxed text-[#4A5058]">{{ $text }}</p></div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        @if($gAfter)
            <div class="mt-16 md:mt-20">
                <p class="{{ $gMono }} text-[#C4300F]">03</p>
                <h2 class="mt-3 max-w-2xl text-3xl font-light tracking-tight md:text-4xl">Какво се случва след пускането на сайта?</h2>
                <ul class="mt-8 grid border-t border-[#0C0E12]/50 md:grid-cols-3">
                    @foreach($gAfter as [$title, $text])
                        <li class="border-b border-[#0C0E12]/15 py-6 md:border-b-0 md:pr-8">
                            <span class="mb-3 block h-0.5 w-9 bg-[#E8431B]"></span>
                            <p class="font-medium">{{ $title }}</p>
                            <p class="mt-2 leading-relaxed text-[#4A5058]">{{ $text }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-16 bg-[#0C0E12] p-6 text-[#ECEEF0] md:mt-20 md:p-10">
            <div class="grid items-start gap-10 md:grid-cols-12">
                <div class="md:col-span-7">
                    <p class="{{ $gMono }} text-[#9AA0A9]">04 <span class="mx-1">/</span> Пакет</p>
                    <h2 class="mt-3 text-3xl font-light tracking-tight md:text-4xl">{{ $gCard ? $gCard['name'] : 'Индивидуална оферта' }}</h2>
                    @if($gCard && $gCard['monthly'])
                        <p class="mt-3 text-2xl font-light">{{ trim($gCard['from'] . ' ' . $gCard['monthly']) }}<span class="ml-1 text-base font-normal italic text-[#9AA0A9]">/месец</span></p>
                    @endif
                    <p class="mt-4 max-w-xl leading-relaxed text-[#C9CED6]">{{ $demo['package']['why'] }}</p>
                </div>
                <div class="md:col-span-5">
                    @if($gCard && $gCard['groups'])
                        <p class="{{ $gMono }} mb-3 text-[#9AA0A9]">Какво включва</p>
                        <ul class="space-y-2.5">
                            @foreach($gCard['groups'] as $group)
                                <li class="flex items-start gap-3 text-[#C9CED6]"><span class="mt-2 h-1.5 w-1.5 shrink-0 bg-[#FF5A33]"></span>{{ $group['title'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ $gIndex !== null ? route('packages') . '?paket=' . $gIndex . '#oferta' : route('contact') }}" class="inline-flex h-12 items-center gap-3 bg-[#FF5A33] px-6 font-medium text-[#0C0E12] hover:bg-[#ECEEF0]">Изпратете запитване <span aria-hidden="true">&rarr;</span></a>
                        @if($gIndex !== null)<a href="{{ route('packages') }}#paket-{{ $gIndex }}" class="inline-flex h-12 items-center border border-[#ECEEF0]/40 px-6 font-medium hover:bg-[#ECEEF0] hover:text-[#0C0E12]">Вижте пакета</a>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
