@extends('demos._layout')
@section('body_class', 'bg-[#fff9ec] text-[#3a2413]')

@php
    $products = [
        ['Акациев мед', 'med', '450 г', 9.80, '#f1c453', 'Най-продаван', 'Светъл и нежен, не се захарва с месеци.'],
        ['Липов мед', 'med', '450 г', 8.90, '#e3a82b', '', 'Ароматен, за чай и при настинка.'],
        ['Манов мед', 'med', '450 г', 12.50, '#7a4a1e', 'Малка партида', 'Тъмен горски мед от Странджа.'],
        ['Мед с орехи', 'med', '250 г', 7.40, '#c98a2a', '', 'Орехови ядки от Тракия в полифлорен мед.'],
        ['Пчелен прашец', 'pchelni', '200 г', 8.20, '#e9b949', '', 'Сушен на ниска температура.'],
        ['Прополис тинктура', 'pchelni', '30 мл', 5.90, '#6b4a2b', '', 'Тридесет процента, в стъкло с капкомер.'],
        ['Свещи от пчелен восък', 'podaratsi', '6 броя', 11.00, '#f0d27a', 'Ново', 'Ръчно лети, горят по три часа.'],
        ['Подаръчна кутия „Кошер“', 'podaratsi', '3 бурканчета', 24.90, '#d9912a', 'Подарък', 'Три вида мед, дървена лъжичка и картичка.'],
    ];
    $eur = fn (float $n) => number_format($n, 2, ',', ' ');
@endphp

@section('content')
<div class="bg-[#3a2413] px-4 py-2 text-center text-sm text-[#fbe7b8]">Безплатна доставка за поръчки над 40 € · Доставка до 2 работни дни с Еконт и Спиди</div>
<header class="sticky top-0 z-40 border-b border-[#f0e2c4] bg-[#fff9ec]/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-2 px-4">
        <a href="#" class="demo-serif flex items-center gap-2 text-xl font-semibold sm:text-2xl"><svg viewBox="0 0 28 32" class="h-8 w-7 shrink-0"><path d="M14 1 27 8.500v15L14 31 1 23.500v-15Z" fill="#e8a317"/><path d="M14 9 20 12.500v7L14 23l-6-3.500v-7Z" fill="#fff9ec"/></svg>Медена пита</a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#magazin" class="nav-u hover:text-[#b9770a]">Магазин</a><a href="#pchelin" class="nav-u hover:text-[#b9770a]">Пчелинът</a><a href="#otzivi" class="nav-u hover:text-[#b9770a]">Отзиви</a><a href="#dostavka" class="nav-u hover:text-[#b9770a]">Доставка</a>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" data-cart-open aria-label="Количка" class="relative inline-flex items-center gap-2 rounded-full bg-[#3a2413] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#553720] sm:px-5"><x-icon name="bag-shopping" /> <span class="max-sm:hidden">Количка</span> <span data-cart-count class="flex h-5 min-w-5 items-center justify-center rounded-full bg-[#e8a317] px-1 text-xs font-bold text-[#3a2413]">0</span></button>
            <details data-menu class="md:hidden">
                <summary aria-label="Меню" class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full bg-[#fde9b5] text-[#3a2413] [&::-webkit-details-marker]:hidden"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></summary>
                <div class="absolute inset-x-0 top-full border-b border-[#f0e2c4] bg-[#fff9ec] px-4 pb-5 pt-2 shadow-xl">
                    <a href="#magazin" class="demo-serif block border-b border-[#f0e2c4] py-3.5 text-xl">Магазин</a><a href="#pchelin" class="demo-serif block border-b border-[#f0e2c4] py-3.5 text-xl">Пчелинът</a><a href="#otzivi" class="demo-serif block border-b border-[#f0e2c4] py-3.5 text-xl">Отзиви</a><a href="#dostavka" class="demo-serif block py-3.5 text-xl">Доставка</a>
                    <a href="#magazin" class="mt-2 block rounded-full bg-[#e8a317] py-3.5 text-center font-bold text-[#3a2413]">Към магазина</a>
                </div>
            </details>
        </div>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden">
    <svg class="absolute -right-10 top-0 h-full w-2/3 opacity-60" viewBox="0 0 600 500" preserveAspectRatio="xMaxYMid slice" aria-hidden="true">
        <defs><pattern id="hex" width="60" height="104" patternUnits="userSpaceOnUse"><path d="M30 0 60 17v35L30 69 0 52V17Zm0 104L0 87V69m30 35 30-17V69" fill="none" stroke="#f3d48a" stroke-width="2"/></pattern></defs>
        <rect width="600" height="500" fill="url(#hex)"/>
    </svg>
    <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 md:grid-cols-2 md:py-20">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full bg-[#fde9b5] px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-[#8a5a06]">Реколта 2026 · направо от пчелина</p>
            <h1 class="demo-serif mt-6 text-5xl font-semibold leading-[1.05] sm:text-6xl lg:text-7xl">Мед, който знаете <em class="text-[#b9770a]">откъде идва</em></h1>
            <p class="mt-6 max-w-md text-lg leading-relaxed text-[#6a4c33]">Сто и двадесет кошера в Странджа, далеч от пътища и пръскани ниви. Пълним бурканите на ръка и пращаме до два дни.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#magazin" class="rounded-full bg-[#e8a317] px-8 py-3.5 font-bold text-[#3a2413] hover:bg-[#f2b535]">Към магазина</a>
                <a href="#pchelin" class="rounded-full border border-[#3a2413]/30 px-8 py-3.5 font-semibold hover:border-[#3a2413]">Нашата история</a>
            </div>
            <ul class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm font-semibold text-[#6a4c33]">
                @foreach(['Лабораторен анализ на всяка партида', 'Без захар и подгряване', 'Връщане до 14 дни'] as $point)
                    <li class="flex items-center gap-2"><span class="text-[#b9770a]"><x-icon name="check-circle" /></span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>
        <x-demo-photo :slug="$demo['slug']" name="hero" alt="Буркани с мед" class="relative mx-auto aspect-square w-full max-w-md">
            <svg viewBox="0 0 400 400" class="h-full w-full" fill="none">
                <defs>
                    <radialGradient id="hj-bg" cx=".5" cy=".45" r=".55"><stop offset="0" stop-color="#fff3cf"/><stop offset="1" stop-color="#fde9b5"/></radialGradient>
                    <linearGradient id="hj1-g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffe08a"/><stop offset=".5" stop-color="#f1b73a"/><stop offset="1" stop-color="#c47f12"/></linearGradient>
                    <linearGradient id="hj2-g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffd05a"/><stop offset=".5" stop-color="#e8a317"/><stop offset="1" stop-color="#a86a0a"/></linearGradient>
                    <linearGradient id="hj3-g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#b8782f"/><stop offset=".5" stop-color="#7a4a1e"/><stop offset="1" stop-color="#3f230d"/></linearGradient>
                </defs>
                <circle cx="200" cy="210" r="172" fill="url(#hj-bg)"/>
                <g fill="none" stroke="#f3d48a" stroke-width="2"><path d="M60 90l18-10 18 10v20l-18 10-18-10zM96 110l18-10 18 10v20l-18 10-18-10zM330 280l18-10 18 10v20l-18 10-18-10z"/></g>
                <ellipse cx="200" cy="352" rx="150" ry="14" fill="#8a5a06" opacity=".16"/>
                <rect x="40" y="340" width="320" height="10" rx="5" fill="#d9a548" opacity=".5"/>
                <g transform="translate(40 138) scale(0.78)">
                    <ellipse cx="62" cy="214" rx="66" ry="9" fill="#3a2413" opacity=".18"/>
                    <path d="M0 48c0-12 10-22 22-22h86c12 0 22 10 22 22v150c0 14-12 26-26 26H26c-14 0-26-12-26-26Z" fill="url(#hj3-g)"/>
                    <path d="M0 104c26-14 52 10 130-4v98c0 14-12 26-26 26H26c-14 0-26-12-26-26Z" fill="#e8a317" opacity=".55"/>
                    <rect x="12" y="0" width="106" height="28" rx="8" fill="#3a2413"/><rect x="12" y="20" width="106" height="8" fill="#000" opacity=".18"/><path d="M22 6h86" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".35"/>
                    <rect x="16" y="84" width="98" height="76" rx="9" fill="#fff9ec"/><path d="M65 98 81 107.500v19L65 136l-16-9.500v-19Z" fill="#e8a317"/><path d="M32 148h66" stroke="#3a2413" stroke-width="3" stroke-linecap="round"/>
                    <path d="M10 54c-3 30-4 60-2 100" stroke="#fff" stroke-width="7" stroke-linecap="round" opacity=".5" fill="none"/><path d="M118 62c2 22 2 44 0 66" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".3" fill="none"/>
                </g>
                <g transform="translate(250 152) scale(0.7)">
                    <ellipse cx="62" cy="214" rx="66" ry="9" fill="#3a2413" opacity=".18"/>
                    <path d="M0 48c0-12 10-22 22-22h86c12 0 22 10 22 22v150c0 14-12 26-26 26H26c-14 0-26-12-26-26Z" fill="url(#hj3-g)"/>
                    <path d="M0 104c26-14 52 10 130-4v98c0 14-12 26-26 26H26c-14 0-26-12-26-26Z" fill="#c98a2a" opacity=".55"/>
                    <rect x="12" y="0" width="106" height="28" rx="8" fill="#3a2413"/><rect x="12" y="20" width="106" height="8" fill="#000" opacity=".18"/><path d="M22 6h86" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".35"/>
                    <rect x="16" y="84" width="98" height="76" rx="9" fill="#fff9ec"/><path d="M65 98 81 107.500v19L65 136l-16-9.500v-19Z" fill="#c98a2a"/><path d="M32 148h66" stroke="#3a2413" stroke-width="3" stroke-linecap="round"/>
                    <path d="M10 54c-3 30-4 60-2 100" stroke="#fff" stroke-width="7" stroke-linecap="round" opacity=".5" fill="none"/><path d="M118 62c2 22 2 44 0 66" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".3" fill="none"/>
                </g>
                <g transform="translate(128 66) scale(1)">
                    <ellipse cx="62" cy="214" rx="66" ry="9" fill="#3a2413" opacity=".18"/>
                    <path d="M0 48c0-12 10-22 22-22h86c12 0 22 10 22 22v150c0 14-12 26-26 26H26c-14 0-26-12-26-26Z" fill="url(#hj1-g)"/>
                    <path d="M0 104c26-14 52 10 130-4v98c0 14-12 26-26 26H26c-14 0-26-12-26-26Z" fill="#e8a317" opacity=".55"/>
                    <rect x="12" y="0" width="106" height="28" rx="8" fill="#3a2413"/><rect x="12" y="20" width="106" height="8" fill="#000" opacity=".18"/><path d="M22 6h86" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".35"/>
                    <rect x="16" y="84" width="98" height="76" rx="9" fill="#fff9ec"/><path d="M65 98 81 107.500v19L65 136l-16-9.500v-19Z" fill="#e8a317"/><path d="M32 148h66" stroke="#3a2413" stroke-width="3" stroke-linecap="round"/>
                    <path d="M10 54c-3 30-4 60-2 100" stroke="#fff" stroke-width="7" stroke-linecap="round" opacity=".5" fill="none"/><path d="M118 62c2 22 2 44 0 66" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".3" fill="none"/>
                </g>
                <path d="M193 58c0-22 -2-34 0-46" stroke="#e8a317" stroke-width="5" stroke-linecap="round" opacity=".95"/>
                <g class="honey-drip"><path d="M193 40c-5 8-5 13 0 13s5-5 0-13Z" fill="#f1b73a"/></g>
                <path d="M150 90c18 6 30 10 44 -2" stroke="#fff" stroke-width="3" opacity=".0"/>
                <g transform="translate(72 258)"><path d="M0 0c8 14 18 14 18 28s-6 22 -14 22" stroke="#f1b73a" stroke-width="0"/></g>
                <path d="M138 100c-4 6-4 12 0 18c4-6 4-12 0-18Z" fill="#ffe08a" opacity=".0"/>
                <g class="bee-float"><ellipse cx="330" cy="92" rx="14" ry="10" fill="#f1b73a"/><path d="M322 83v18M330 82v20M338 84v16" stroke="#3a2413" stroke-width="4"/><ellipse cx="322" cy="78" rx="10" ry="6" fill="#fff" opacity=".85" transform="rotate(-25 322 78)"/><ellipse cx="338" cy="77" rx="10" ry="6" fill="#fff" opacity=".85" transform="rotate(25 338 77)"/><circle cx="343" cy="92" r="2" fill="#3a2413"/><path d="M250 60c20 10 40 10 62 26" stroke="#3a2413" stroke-width="2" stroke-dasharray="3 7" stroke-linecap="round" fill="none"/></g>
                <g fill="#fff" opacity=".6"><circle cx="86" cy="204" r="3"/><circle cx="318" cy="238" r="2.500"/><circle cx="62" cy="170" r="2"/></g>
            </svg>
        </x-demo-photo>
    </div>
</section>

{{-- Shop --}}
<section id="magazin" class="scroll-mt-16 bg-white px-4 py-20">
    <div class="mx-auto max-w-7xl" data-group>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <h2 class="demo-serif text-4xl font-semibold md:text-5xl">Магазин</h2>
            <div class="demo-scroll flex gap-2">
                @foreach(['all' => 'Всички', 'med' => 'Мед', 'pchelni' => 'Пчелни продукти', 'podaratsi' => 'Подаръци'] as $key => $label)
                    <button type="button" data-show="{{ $key }}" aria-pressed="{{ $key === 'all' ? 'true' : 'false' }}" class="shrink-0 rounded-full border border-[#ead9b5] px-5 py-2 text-sm font-semibold hover:border-[#b9770a] aria-pressed:border-[#3a2413] aria-pressed:bg-[#3a2413] aria-pressed:text-white">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="mt-10 grid grid-cols-2 gap-4 md:gap-6 lg:grid-cols-4">
            @foreach($products as $i => [$name, $cat, $size, $price, $color, $badge, $text])
                <article data-key="{{ $cat }}" class="group flex flex-col rounded-3xl border border-[#f0e2c4] bg-[#fffdf7] p-3 transition hover:-translate-y-1 hover:shadow-xl md:p-4">
                    <div class="relative">
                        <x-demo-photo :slug="$demo['slug']" name="product-{{ $i + 1 }}" alt="{{ $name }}" class="aspect-square w-full rounded-2xl" style="background: {{ $color }}22">
                            <svg viewBox="0 0 200 200" class="h-full w-full transition duration-500 group-hover:scale-105" fill="none"><rect x="66" y="40" width="68" height="18" rx="5" fill="#3a2413"/><path d="M56 76c0-10 8-18 18-18h52c10 0 18 8 18 18v72c0 10-8 18-18 18H74c-10 0-18-8-18-18Z" fill="{{ $color }}"/><rect x="68" y="88" width="64" height="46" rx="5" fill="#fff9ec"/><path d="M100 98 110 104v12l-10 6-10-6v-12Z" fill="#e8a317"/></svg>
                        </x-demo-photo>
                        @if($badge !== '')<span class="absolute left-3 top-3 rounded-full bg-[#3a2413] px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-[#fbe7b8]">{{ $badge }}</span>@endif
                    </div>
                    <div class="flex flex-1 flex-col px-1 pt-4">
                        <h3 class="font-bold leading-snug md:text-lg">{{ $name }}</h3>
                        <p class="text-sm text-[#8a6f55]">{{ $size }}</p>
                        <p class="mt-2 hidden flex-1 text-sm text-[#6a4c33] sm:block">{{ $text }}</p>
                        <div class="mt-4 flex items-center justify-between gap-2">
                            <span class="demo-serif text-xl font-semibold md:text-2xl">{{ $eur($price) }} €</span>
                            <button type="button" data-add="{{ $i }}" data-name="{{ $name }}" data-price="{{ $price }}" data-size="{{ $size }}" data-color="{{ $color }}" aria-label="Добави {{ $name }} в количката" class="flex h-10 w-10 items-center justify-center rounded-full bg-[#e8a317] text-[#3a2413] hover:bg-[#3a2413] hover:text-white md:w-auto md:gap-2 md:px-4"><x-icon name="plus" class="text-sm" /><span class="hidden text-sm font-bold md:inline">Добави</span></button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Apiary --}}
<section id="pchelin" class="scroll-mt-16 px-4 py-20">
    <div class="dr mx-auto grid max-w-6xl items-center gap-12 md:grid-cols-2">
        <x-demo-photo :slug="$demo['slug']" name="apiary" alt="Кошерите в Странджа" class="relative aspect-[4/3] w-full overflow-hidden rounded-[2rem] bg-[linear-gradient(180deg,#cfe6d2_0%,#a8c98f_60%,#7ea65c_100%)]">
            <svg viewBox="0 0 400 300" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none"><circle cx="320" cy="60" r="34" fill="#f6d77a"/><path d="M0 200c80-50 160-40 240-10s110 10 160-20v130H0Z" fill="#5f8a45"/>
                @foreach([[60, '#e8a317'], [150, '#fff9ec'], [240, '#7fb3d5']] as [$x, $c])<g transform="translate({{ $x }} 150)"><path d="M-6 0h72l-6-14H0Z" fill="#3a2413"/><rect width="60" height="70" fill="{{ $c }}"/><path d="M0 24h60M0 46h60" stroke="#3a2413" stroke-opacity=".25" stroke-width="2"/><rect x="20" y="58" width="20" height="5" fill="#3a2413"/><path d="M6 70v16m48-16v16" stroke="#3a2413" stroke-width="4"/></g>@endforeach
            </svg>
        </x-demo-photo>
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#b9770a]">Пчелинът</p>
            <h2 class="demo-serif mt-3 text-4xl font-semibold leading-tight md:text-5xl">Семеен пчелин от 1987 година</h2>
            <p class="mt-5 leading-relaxed text-[#6a4c33]">Баща ми започна с пет кошера край Малко Търново. Днес сме трима и гледаме сто и двадесет. Не подхранваме със захарен сироп по време на паша и не подгряваме меда, за да остане жив.</p>
            <dl class="mt-8 grid grid-cols-3 gap-4 border-t border-[#ead9b5] pt-6">
                @foreach([['120', 'кошера'], ['4', 'паши в годината'], ['100%', 'проследима партида']] as [$n, $t])
                    <div><dd class="demo-serif text-4xl font-semibold text-[#b9770a]">{{ $n }}</dd><dt class="text-sm text-[#6a4c33]">{{ $t }}</dt></div>
                @endforeach
            </dl>
        </div>
    </div>
</section>

{{-- Reviews --}}
<section id="otzivi" class="scroll-mt-16 bg-[#3a2413] px-4 py-20 text-[#fff9ec]">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h2 class="demo-serif text-4xl font-semibold md:text-5xl">2 300 доволни клиенти</h2>
            <p class="text-[#e8cf9b]"><span class="text-[#e8a317]">★★★★★</span> 4,9 от 5 от 640 отзива</p>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach([
                ['Поръчах в неделя вечер, във вторник беше в офиса на Еконт. Мановият мед е като от детството.', 'Радостина Б.', 'Манов мед'],
                ['Взех кутията „Кошер“ за подарък на колеги. Опакована е толкова красиво, че поръчах още пет.', 'Мартин Ц.', 'Подаръчна кутия'],
                ['Харесва ми, че на буркана има номер на партидата и анализът е на сайта. Купувам всеки месец.', 'Галина Ж.', 'Акациев мед'],
            ] as [$quote, $who, $what])
                <figure class="rounded-3xl bg-white/[0.06] p-7 ring-1 ring-white/10">
                    <blockquote class="demo-serif text-xl leading-snug">„{{ $quote }}“</blockquote>
                    <figcaption class="mt-5 text-sm"><strong>{{ $who }}</strong><span class="block text-[#e8cf9b]">Потвърдена покупка · {{ $what }}</span></figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Delivery --}}
<section id="dostavka" class="scroll-mt-16 px-4 py-16">
    <div class="mx-auto grid max-w-7xl gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['Доставка до 2 дни', 'До офис на Еконт или Спиди 3,90 €, до адрес 5,50 €. Над 40 € е безплатна.', 'rocket'],
            ['Плащане', 'С карта, по банков път или с наложен платеж при получаване.', 'lock'],
            ['Връщане до 14 дни', 'Ако не сте отворили буркана, връщаме сумата без въпроси.', 'rotate-left'],
            ['Въпроси', 'Пишете ни или се обадете на 0700 00 000 всеки делник до 18 часа.', 'phone'],
        ] as [$title, $text, $icon])
            <div class="rounded-3xl border border-[#f0e2c4] bg-white p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#fde9b5] text-[#8a5a06]"><x-icon :name="$icon" /></span>
                <h3 class="mt-4 font-bold">{{ $title }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-[#6a4c33]">{{ $text }}</p>
            </div>
        @endforeach
    </div>
    <form data-demo-form class="mx-auto mt-10 flex max-w-3xl flex-col items-center gap-4 rounded-3xl bg-[#fde9b5] p-8 text-center md:flex-row md:text-left">
        <p class="flex-1"><strong class="demo-serif block text-2xl">10% за първата поръчка</strong><span class="text-sm text-[#6a4c33]">Пишем само при нова реколта, най-много веднъж в месеца.</span></p>
        <input type="email" required placeholder="Вашият имейл" class="w-full rounded-full border border-[#e3c98a] bg-white px-5 py-3 focus:border-[#3a2413] focus:ring-[#3a2413] md:w-60">
        <button class="w-full rounded-full bg-[#3a2413] px-7 py-3 font-bold text-white hover:bg-[#553720] md:w-auto">Искам отстъпката</button>
    </form>
</section>

<footer class="border-t border-[#f0e2c4] px-4 py-8 text-center text-sm text-[#8a6f55]">Медена пита · с. Звездец, община Малко Търново · Регистриран пчелин в БАБХ</footer>

{{-- Cart drawer --}}
<div data-cart hidden class="fixed inset-0 z-[60]">
    <button type="button" data-cart-close aria-label="Затвори количката" class="absolute inset-0 h-full w-full bg-[#3a2413]/50"></button>
    <aside class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-[#fff9ec] shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#f0e2c4] px-6 py-5">
            <h2 class="demo-serif text-2xl font-semibold">Количка</h2>
            <button type="button" data-cart-close class="flex h-9 w-9 items-center justify-center rounded-full hover:bg-[#fde9b5]" aria-label="Затвори">✕</button>
        </div>
        <div class="border-b border-[#f0e2c4] px-6 py-4">
            <p class="text-sm font-semibold" data-ship-text>Добавете още 40,00 € за безплатна доставка</p>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#f0e2c4]"><div data-ship-bar class="h-full w-0 rounded-full bg-[#e8a317] transition-all duration-300"></div></div>
        </div>
        <ul data-cart-items class="flex-1 divide-y divide-[#f0e2c4] overflow-y-auto px-6"></ul>
        <p data-cart-empty class="flex-1 px-6 py-16 text-center text-[#8a6f55]">Количката е празна. Започнете с акациевия мед, той е любимият на клиентите ни.</p>
        <div class="border-t border-[#f0e2c4] px-6 py-5">
            <p class="flex items-baseline justify-between"><span class="font-semibold">Общо</span><span class="demo-serif text-3xl font-semibold"><span data-cart-total>0,00</span> €</span></p>
            <button type="button" data-demo-action class="mt-4 w-full rounded-full bg-[#3a2413] py-4 font-bold text-white hover:bg-[#553720]">Към поръчката</button>
            <p class="mt-3 text-center text-xs text-[#8a6f55]">Плащане с карта или наложен платеж</p>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var cart = {}, drawer = document.querySelector('[data-cart]'), list = drawer.querySelector('[data-cart-items]'), empty = drawer.querySelector('[data-cart-empty]');
        function money(n) { return n.toFixed(2).replace('.', ','); }
        function draw() {
            var total = 0, count = 0;
            list.innerHTML = '';
            Object.keys(cart).forEach(function (id) {
                var item = cart[id]; total += item.price * item.qty; count += item.qty;
                var li = document.createElement('li');
                li.className = 'flex items-center gap-4 py-4';
                li.innerHTML = '<span class="h-14 w-14 shrink-0 rounded-xl" style="background:' + item.color + '"></span><span class="min-w-0 flex-1"><strong class="block truncate"></strong><span class="text-sm text-[#8a6f55]"></span></span>'
                    + '<span class="flex items-center gap-2"><button type="button" data-qty="-1" class="h-7 w-7 rounded-full border border-[#ead9b5] font-bold" aria-label="По-малко">−</button><span class="w-5 text-center font-bold tabular-nums">' + item.qty + '</span><button type="button" data-qty="1" class="h-7 w-7 rounded-full border border-[#ead9b5] font-bold" aria-label="Повече">+</button></span>';
                li.querySelector('strong').textContent = item.name;
                li.querySelector('span span').textContent = item.size + ' · ' + money(item.price * item.qty) + ' €';
                li.querySelectorAll('[data-qty]').forEach(function (b) { b.addEventListener('click', function () { item.qty += Number(b.dataset.qty); if (item.qty < 1) delete cart[id]; draw(); }); });
                list.appendChild(li);
            });
            list.hidden = count === 0; empty.hidden = count > 0;
            drawer.querySelector('[data-cart-total]').textContent = money(total);
            document.querySelector('[data-cart-count]').textContent = count;
            drawer.querySelector('[data-ship-bar]').style.width = Math.min(100, total / 40 * 100) + '%';
            drawer.querySelector('[data-ship-text]').textContent = total >= 40 ? 'Доставката за тази поръчка е безплатна' : 'Добавете още ' + money(40 - total) + ' € за безплатна доставка';
        }
        function open(show) { drawer.hidden = ! show; document.body.style.overflow = show ? 'hidden' : ''; }
        document.querySelectorAll('[data-add]').forEach(function (b) {
            b.addEventListener('click', function () {
                var id = b.dataset.add;
                cart[id] = cart[id] || { name: b.dataset.name, price: Number(b.dataset.price), size: b.dataset.size, color: b.dataset.color, qty: 0 };
                cart[id].qty++; draw(); open(true);
            });
        });
        document.querySelector('[data-cart-open]').addEventListener('click', function () { open(true); });
        drawer.querySelectorAll('[data-cart-close]').forEach(function (b) { b.addEventListener('click', function () { open(false); }); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') open(false); });
        draw();
    })();
</script>
@endpush
