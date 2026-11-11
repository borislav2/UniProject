@extends('demos._layout')
@section('body_class', 'bg-[#f5f0e8] text-[#1c2730]')

@php
    $materials = [['Керемиди', 62, 'Керамични или бетонови, живот над 50 години'], ['Метални керемиди', 38, 'Леки, бърз монтаж, гаранция 30 години'], ['Битумни керемиди', 34, 'За сложни форми и малък наклон'], ['Хидроизолация на плосък покрив', 29, 'Два слоя мембрана с посипка']];
    $projects = [
        ['Къща в с. Богомилово', 'Нов покрив с керамични керемиди, 186 кв. м', '9 работни дни', '#c8552d'],
        ['Блок в кв. Железник', 'Хидроизолация на плосък покрив, 420 кв. м', '6 работни дни', '#41586a'],
        ['Склад в индустриалната зона', 'Термопанели и улуци, 1 150 кв. м', '14 работни дни', '#7a8a5a'],
    ];
@endphp

@push('head')
<style>
    .pk-link { position: relative; }
    .pk-link::after { content: ""; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px; background: #e8703f; transform: scaleX(0); transform-origin: left; }
    .pk-link:hover::after { transform: scaleX(1); }
    details[data-menu] > summary::-webkit-details-marker { display: none; }
    details[data-menu][open] .pk-bars { display: none; }
    details[data-menu]:not([open]) .pk-close { display: none; }
    @media (prefers-reduced-motion: no-preference) {
        .pk-link::after { transition: transform .25s ease; }
        .pk-lift { transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
        .pk-lift:hover { transform: translateY(-5px); box-shadow: 0 18px 34px -16px rgba(28, 39, 48, .35); }
        [data-value] { transition: background-color .2s ease, border-color .2s ease; }
        @keyframes pk-smoke { 0% { transform: translateY(0) scale(.7); opacity: .0; } 20% { opacity: .5; } 100% { transform: translateY(-70px) scale(1.5); opacity: 0; } }
        @keyframes pk-cloud { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(22px); } }
        @keyframes pk-wave { 0%, 100% { transform: rotate(-4deg); } 50% { transform: rotate(8deg); } }
        .pk-smoke { animation: pk-smoke 5s ease-out infinite; }
        .pk-smoke-2 { animation-delay: 1.7s; }
        .pk-smoke-3 { animation-delay: 3.4s; }
        .pk-cloud { animation: pk-cloud 14s ease-in-out infinite; }
        .pk-wave { transform-origin: 1166px 322px; animation: pk-wave 2.4s ease-in-out infinite; }
    }
</style>
@endpush

@section('content')
<div class="demo-geo">
<header class="sticky top-0 z-50 bg-[#1c2730] text-white">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="flex items-center gap-2 text-xl font-extrabold"><svg viewBox="0 0 40 28" class="h-7 w-10" fill="none"><path d="M2 26 20 4l18 22" stroke="#e8703f" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/><path d="M11 26 20 15l9 11" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>Здрав покрив</a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#kalkulator" class="pk-link hover:text-[#e8703f]">Калкулатор</a><a href="#uslugi" class="pk-link hover:text-[#e8703f]">Услуги</a><a href="#obekti" class="pk-link hover:text-[#e8703f]">Обекти</a><a href="#garanciya" class="pk-link hover:text-[#e8703f]">Гаранция</a>
        </div>
        <div class="flex items-center gap-2">
            <a href="#ogled" class="rounded-md bg-[#e8703f] px-4 py-2 text-sm font-extrabold hover:bg-[#f08556] sm:px-5">Безплатен оглед</a>
            <details data-menu class="md:hidden">
                <summary aria-label="Меню" class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-md border border-white/25 text-lg hover:border-[#e8703f] hover:text-[#e8703f]"><span class="pk-bars"><x-icon name="bars" /></span><span class="pk-close text-2xl leading-none">&times;</span></summary>
                <div class="absolute inset-x-0 top-full border-b-4 border-[#e8703f] bg-[#1c2730] px-4 pb-5 pt-2 shadow-2xl">
                    <div class="grid text-lg font-extrabold">
                        @foreach([['#kalkulator', 'Калкулатор'], ['#uslugi', 'Услуги'], ['#obekti', 'Обекти'], ['#garanciya', 'Гаранция']] as [$href, $label])
                            <a href="{{ $href }}" class="border-b border-white/10 py-4 hover:text-[#e8703f]">{{ $label }}</a>
                        @endforeach
                    </div>
                    <a href="#ogled" class="mt-4 block rounded-md bg-[#e8703f] py-4 text-center font-extrabold">Заявете безплатен оглед</a>
                </div>
            </details>
        </div>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden bg-[#1c2730] text-white">
    <svg viewBox="0 0 1440 520" preserveAspectRatio="xMidYMax slice" class="absolute inset-0 h-full w-full" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="pk-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1c2730"/><stop offset=".7" stop-color="#32495a"/><stop offset="1" stop-color="#5d6a6e"/></linearGradient>
            <linearGradient id="pk-roof" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#e0683a"/><stop offset="1" stop-color="#a8431f"/></linearGradient>
            <linearGradient id="pk-roof2" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#000" stop-opacity=".25"/><stop offset=".5" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity=".3"/></linearGradient>
            <radialGradient id="pk-sun" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#ffe29a" stop-opacity=".95"/><stop offset=".45" stop-color="#f2c14e" stop-opacity=".55"/><stop offset="1" stop-color="#f2c14e" stop-opacity="0"/></radialGradient>
            <pattern id="pk-tiles" width="46" height="30" patternUnits="userSpaceOnUse"><path d="M0 30V15a23 15 0 0 0 46 0v15" stroke="#5a200c" stroke-opacity=".38" stroke-width="2.5"/><path d="M-23 15a23 15 0 0 0 46 0" stroke="#5a200c" stroke-opacity=".38" stroke-width="2.5"/></pattern>
        </defs>
        <rect width="1440" height="520" fill="url(#pk-sky)"/>
        <circle cx="1280" cy="110" r="150" fill="url(#pk-sun)"/><circle cx="1280" cy="110" r="42" fill="#f6d58a"/>
        <g fill="#fff" opacity=".1" class="pk-cloud"><ellipse cx="520" cy="96" rx="110" ry="20"/><ellipse cx="590" cy="82" rx="70" ry="18"/><ellipse cx="900" cy="64" rx="90" ry="14"/></g>
        <path d="M0 430c160-60 300-70 460-20s320 30 480-30 340-20 500 20v120H0Z" fill="#26343f"/>
        <path d="M600 520 1040 140l440 380Z" fill="url(#pk-roof)"/><path d="M600 520 1040 140l440 380Z" fill="url(#pk-tiles)"/><path d="M600 520 1040 140l440 380Z" fill="url(#pk-roof2)"/>
        <path d="M600 520 1040 140l440 380" stroke="#7d2e12" stroke-width="10" stroke-linejoin="round"/><path d="M1020 160l20-18 20 18" stroke="#f2a07a" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="880" y="190" width="56" height="96" fill="#8f3a1c"/><g stroke="#5a200c" stroke-opacity=".4" stroke-width="2"><path d="M880 214h56M880 238h56M880 262h56M906 190v24M920 214v24M900 238v24"/></g><rect x="872" y="178" width="72" height="16" fill="#26343f"/>
        <g fill="#dfe6ea"><circle cx="908" cy="160" r="14" class="pk-smoke"/><circle cx="912" cy="160" r="14" class="pk-smoke pk-smoke-2"/><circle cx="904" cy="160" r="14" class="pk-smoke pk-smoke-3"/></g>
        <rect x="700" y="488" width="680" height="32" fill="#e9dcc8" opacity=".9"/>
        <g stroke="#d9c7a6" stroke-width="7" stroke-linecap="round"><path d="M1336 520 1250 330M1366 520 1280 330"/></g><g stroke="#d9c7a6" stroke-width="5"><path d="M1327 500l36 0M1319 474l36 0M1311 448l36 0M1303 422l36 0M1295 396l36 0M1287 370l36 0M1279 344l36 0"/></g>
        <g><path d="M1150 336v-34" stroke="#26343f" stroke-width="12" stroke-linecap="round"/><path d="M1176 336v-34" stroke="#26343f" stroke-width="12" stroke-linecap="round"/><rect x="1142" y="256" width="42" height="56" rx="10" fill="#f2c14e"/><path d="M1142 276h42" stroke="#fff" stroke-opacity=".7" stroke-width="5"/><g class="pk-wave"><path d="M1184 266l30-20" stroke="#f2c14e" stroke-width="11" stroke-linecap="round"/></g><path d="M1142 268l-18 22" stroke="#f2c14e" stroke-width="11" stroke-linecap="round"/><circle cx="1163" cy="238" r="15" fill="#f0c9a4"/><path d="M1145 236a18 16 0 0 1 36 0Z" fill="#fff"/><rect x="1141" y="234" width="44" height="6" rx="3" fill="#fff"/></g>
    </svg>
    <div class="absolute inset-0 bg-gradient-to-r from-[#1c2730] via-[#1c2730]/60 to-transparent lg:via-[#1c2730]/20"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 lg:py-24">
        <div class="max-w-2xl">
            <p class="inline-flex items-center gap-2 rounded-md bg-white/10 px-3 py-1.5 text-sm font-semibold"><span class="text-[#f2c14e]">★ 4,9</span> от 214 отзива · Стара Загора и областта</p>
            <h1 class="mt-6 text-5xl font-extrabold leading-[1.03] tracking-tight sm:text-6xl lg:text-7xl">Покрив, за който <span class="text-[#e8703f]">не мислите</span> следващите 30 години</h1>
            <p class="mt-6 max-w-xl text-lg font-light leading-relaxed text-[#c9d3da]">Нови покриви, ремонт на течове и хидроизолация. Собствени бригади, договор с крайна цена и писмена гаранция до 15 години за труда.</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="#ogled" class="rounded-md bg-[#e8703f] px-8 py-4 font-extrabold hover:bg-[#f08556]">Заявете безплатен оглед</a>
                <a href="#kalkulator" class="rounded-md border border-white/30 px-8 py-4 font-semibold hover:bg-white/10">Сметнете цената</a>
            </div>
        </div>
        <dl class="mt-14 grid max-w-3xl grid-cols-2 gap-6 border-t border-white/15 pt-8 sm:grid-cols-4">
            @foreach([['1 380', 'покрива от 2006'], ['15 години', 'гаранция за труда'], ['48 часа', 'до оглед на място'], ['0 €', 'аванс преди договор']] as [$n, $t])
                <div><dd class="text-3xl font-extrabold">{{ $n }}</dd><dt class="mt-1 text-sm font-light text-[#9fb0bc]">{{ $t }}</dt></div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Urgent strip --}}
<div class="bg-[#e8703f] px-4 py-4 text-white">
    <p class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-4 gap-y-1 text-center font-semibold"><x-icon name="triangle-exclamation" /> Тече в момента? Аварийна бригада идва до 3 часа, и в почивните дни. <a href="tel:+35942000000" data-demo-action class="underline underline-offset-4">042 000 000</a></p>
</div>

{{-- Calculator --}}
<section id="kalkulator" class="dr scroll-mt-16 px-4 py-20">
    <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-12" data-roof>
        <div class="lg:col-span-5">
            <p class="text-sm font-extrabold uppercase tracking-widest text-[#c8552d]">Калкулатор</p>
            <h2 class="mt-3 text-4xl font-extrabold tracking-tight md:text-5xl">Колко струва вашият покрив</h2>
            <p class="mt-5 font-light leading-relaxed text-[#4c5b66]">Ориентировъчна цена с материалите, труда, скелето и извозването на стария покрив. Точната оферта получавате след оглед, който е безплатен.</p>
            <ul class="mt-8 space-y-3 text-sm">
                @foreach(['Цената в договора не се променя', 'Плащане на три етапа, без аванс преди договор', 'Материали с фабрична гаранция'] as $point)
                    <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#c8552d] text-xs text-white"><x-icon name="check" /></span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-black/5 md:p-9 lg:col-span-7">
            <p class="text-xs font-extrabold uppercase tracking-widest text-[#6b7a85]">Покритие</p>
            <div data-pick="material" class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach($materials as $i => [$name, $price, $note])
                    <button type="button" data-value="{{ $name }}" data-price="{{ $price }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-xl border-2 border-[#e6ded2] p-4 text-left hover:border-[#c8552d] aria-pressed:border-[#c8552d] aria-pressed:bg-[#fbeee8]">
                        <span class="block font-extrabold">{{ $name }}</span><span class="mt-1 block text-sm font-light text-[#4c5b66]">{{ $note }}</span>
                    </button>
                @endforeach
            </div>
            <label class="mt-8 flex items-baseline justify-between"><span class="text-xs font-extrabold uppercase tracking-widest text-[#6b7a85]">Площ на покрива</span><span class="text-2xl font-extrabold"><span data-area>140</span> кв. м</span></label>
            <input type="range" min="40" max="400" step="5" value="140" data-range class="mt-3 w-full accent-[#c8552d]" aria-label="Площ на покрива">
            <label class="mt-6 flex cursor-pointer items-center gap-3 text-sm"><input type="checkbox" data-old checked class="h-5 w-5 rounded border-[#cfc5b6] text-[#c8552d] focus:ring-[#c8552d]"> Сваляне и извозване на стария покрив</label>
            <div class="mt-8 flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-[#1c2730] p-6 text-white">
                <div><p class="text-sm font-light text-[#9fb0bc]">Ориентировъчна цена</p><p class="text-4xl font-extrabold sm:text-5xl"><span data-from>9 100</span> до <span data-to>10 900</span> €</p></div>
                <a href="#ogled" class="rounded-md bg-[#e8703f] px-6 py-3 font-extrabold hover:bg-[#f08556]">Искам точна оферта</a>
            </div>
        </div>
    </div>
</section>

{{-- Services --}}
<section id="uslugi" class="scroll-mt-16 bg-white px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="max-w-2xl text-4xl font-extrabold tracking-tight md:text-5xl">От един счупен улук до цял нов покрив</h2>
        <div class="dr mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['Нов покрив', 'Конструкция, изолация, покритие и обшивки, до ключ.', 'home'],
                ['Ремонт на течове', 'Намираме причината с термокамера и поправяме само нужното.', 'screwdriver-wrench'],
                ['Хидроизолация', 'Плоски покриви, тераси и гаражи с мембрана на два слоя.', 'shield-halved'],
                ['Улуци и обшивки', 'Олуци, водосточни тръби, комини и капандури.', 'hammer'],
            ] as [$name, $text, $icon])
                <article class="pk-lift rounded-2xl border border-[#e6ded2] p-7 hover:border-[#c8552d]">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#1c2730] text-xl text-[#e8703f]"><x-icon :name="$icon" /></span>
                    <h3 class="mt-5 text-xl font-extrabold">{{ $name }}</h3>
                    <p class="mt-2 font-light leading-relaxed text-[#4c5b66]">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Projects with before / after --}}
<section id="obekti" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <h2 class="text-4xl font-extrabold tracking-tight md:text-5xl">Завършени обекти</h2>
            <p class="font-light text-[#4c5b66]">Плъзнете, за да видите преди и след.</p>
        </div>
        <div class="dr mt-12 grid gap-6 md:grid-cols-3">
            @foreach($projects as $i => [$name, $text, $days, $color])
                <article class="pk-lift overflow-hidden rounded-3xl bg-white ring-1 ring-[#e6ded2]">
                    <div class="relative aspect-[4/3] select-none overflow-hidden" data-compare style="--pos: 50%">
                        <x-demo-photo :slug="$demo['slug']" name="after-{{ $i + 1 }}" alt="{{ $name }} след ремонта" class="absolute inset-0 h-full w-full bg-[#cfe3f0]">
                            <svg viewBox="0 0 400 300" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none">
                                <rect width="400" height="300" fill="#cfe3f0"/><circle cx="340" cy="50" r="22" fill="#fff4cf"/><path d="M0 250h400v50H0Z" fill="#a9b98f"/>
                                @if($i === 0)
                                    <path d="M92 168h216v100H92Z" fill="#f5f0e8"/><path d="M52 176 200 66l148 110Z" fill="{{ $color }}"/><path d="M52 176 200 66l148 110" stroke="#7d2e12" stroke-width="6" stroke-linejoin="round"/><g stroke="#fff" stroke-opacity=".4" stroke-width="3"><path d="M84 152h232M118 126h164M152 100h96"/></g><rect x="256" y="84" width="22" height="46" fill="#8f3a1c"/><rect x="252" y="78" width="30" height="8" fill="#41586a"/>
                                    <rect x="112" y="196" width="46" height="42" fill="#1c2730" opacity=".85"/><rect x="242" y="196" width="46" height="42" fill="#1c2730" opacity=".85"/><rect x="176" y="208" width="48" height="60" fill="#8a5a3a"/><path d="M52 178h296" stroke="#41586a" stroke-width="5"/>
                                @elseif($i === 1)
                                    <path d="M70 92h260v178H70Z" fill="#e8e2d6"/><path d="M62 84h276v12H62Z" fill="#f5f0e8"/><path d="M70 96h260v14H70Z" fill="{{ $color }}"/>
                                    <g fill="#1c2730" opacity=".78">@foreach([128, 172, 216] as $y)@foreach([92, 138, 184, 230, 276] as $x)<rect x="{{ $x }}" y="{{ $y }}" width="28" height="28"/>@endforeach @endforeach</g><rect x="188" y="250" width="26" height="20" fill="#41586a"/><rect x="262" y="62" width="40" height="22" fill="#9fb0bc"/>
                                @else
                                    <path d="M40 160h320v110H40Z" fill="#e9e4d9"/><path d="M24 166 200 104l176 62Z" fill="{{ $color }}"/><g stroke="#fff" stroke-opacity=".4" stroke-width="3"><path d="M96 146v20M146 130v36M200 112v54M254 130v36M304 146v20"/></g><path d="M24 168h352" stroke="#41586a" stroke-width="5"/>
                                    <rect x="70" y="196" width="72" height="74" fill="#41586a"/><rect x="164" y="196" width="72" height="74" fill="#41586a"/><path d="M70 214h72M70 232h72M70 250h72M164 214h72M164 232h72M164 250h72" stroke="#fff" stroke-opacity=".3" stroke-width="2"/><rect x="266" y="204" width="60" height="30" fill="#1c2730" opacity=".78"/>
                                @endif
                            </svg>
                        </x-demo-photo>
                        <div class="absolute inset-0 overflow-hidden" style="width: var(--pos)">
                            <x-demo-photo :slug="$demo['slug']" name="before-{{ $i + 1 }}" alt="{{ $name }} преди ремонта" class="absolute inset-y-0 left-0 h-full bg-[#b9bfc2]" data-compare-before>
                                <svg viewBox="0 0 400 300" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none">
                                    <rect width="400" height="300" fill="#b9bfc2"/><path d="M0 250h400v50H0Z" fill="#8a9279"/>
                                    @if($i === 0)
                                        <path d="M92 168h216v100H92Z" fill="#d8d2c8"/><path d="M52 176 200 66l148 110Z" fill="#6f6a63"/><path d="M52 176 200 66l148 110" stroke="#3a3632" stroke-width="6" stroke-linejoin="round"/><path d="M120 152l30 14 20-30 34 26 22-18M170 112l24 18" stroke="#3a3632" stroke-width="4"/><circle cx="240" cy="148" r="14" fill="#3a3632"/><g fill="#5b6f55" opacity=".7"><ellipse cx="130" cy="164" rx="22" ry="7"/><ellipse cx="290" cy="160" rx="18" ry="6"/></g>
                                        <rect x="256" y="84" width="22" height="46" fill="#7a6a60"/><path d="M262 84l8 22-6 24" stroke="#3a3632" stroke-width="3"/><rect x="252" y="78" width="30" height="8" fill="#5b5651"/>
                                        <rect x="112" y="196" width="46" height="42" fill="#1c2730" opacity=".6"/><rect x="242" y="196" width="46" height="42" fill="#1c2730" opacity=".6"/><rect x="176" y="208" width="48" height="60" fill="#6d5a48"/><path d="M52 178l120 0 20 14 156-14" stroke="#5b5651" stroke-width="5"/><path d="M92 190v60" stroke="#8a847c" stroke-width="5" opacity=".5"/>
                                    @elseif($i === 1)
                                        <path d="M70 92h260v178H70Z" fill="#cfc9be"/><path d="M62 84h276v12H62Z" fill="#a8a39a"/><path d="M70 96h260v14H70Z" fill="#4a4d50"/><path d="M96 98c30 8 20 24 40 30M200 98c-6 20 8 24 4 44M290 98c10 16-2 26 6 40" stroke="#6d675f" stroke-width="7" stroke-linecap="round" opacity=".45"/><ellipse cx="150" cy="90" rx="34" ry="5" fill="#7b8a96"/><ellipse cx="260" cy="89" rx="22" ry="4" fill="#7b8a96"/>
                                        <g fill="#1c2730" opacity=".6">@foreach([128, 172, 216] as $y)@foreach([92, 138, 184, 230, 276] as $x)<rect x="{{ $x }}" y="{{ $y }}" width="28" height="28"/>@endforeach @endforeach</g><rect x="188" y="250" width="26" height="20" fill="#6d675f"/><rect x="262" y="62" width="40" height="22" fill="#8a8f93"/>
                                    @else
                                        <path d="M40 160h320v110H40Z" fill="#cfc8bb"/><path d="M24 166 200 104l176 62Z" fill="#8a5a3a"/><g fill="#3a2a1e"><circle cx="110" cy="150" r="9"/><circle cx="262" cy="142" r="7"/><path d="M180 126l16 10-4 18-14-6Z"/></g><path d="M130 148l30 8M300 152l24 6" stroke="#5b3a22" stroke-width="6" stroke-linecap="round"/><path d="M24 168h352" stroke="#6b6560" stroke-width="5"/><path d="M300 168v34" stroke="#6b6560" stroke-width="5"/>
                                        <rect x="70" y="196" width="72" height="74" fill="#5f6a72"/><rect x="164" y="196" width="72" height="74" fill="#5f6a72"/><path d="M70 214h72M70 232h72M70 250h72M164 214h72M164 232h72M164 250h72" stroke="#fff" stroke-opacity=".2" stroke-width="2"/><rect x="266" y="204" width="60" height="30" fill="#1c2730" opacity=".6"/><path d="M100 160c-6 22 6 40 0 56" stroke="#8a7a60" stroke-width="6" opacity=".5"/>
                                    @endif
                                </svg>
                            </x-demo-photo>
                        </div>
                        <span class="pointer-events-none absolute inset-y-0 w-1 -translate-x-1/2 bg-white shadow" style="left: var(--pos)"><span class="absolute left-1/2 top-1/2 flex h-9 w-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white text-xs font-extrabold text-[#1c2730] shadow-lg">↔</span></span>
                        <span class="pointer-events-none absolute left-3 top-3 rounded bg-black/60 px-2 py-0.5 text-xs font-semibold text-white">Преди</span><span class="pointer-events-none absolute right-3 top-3 rounded bg-[#e8703f] px-2 py-0.5 text-xs font-semibold text-white">След</span>
                        <input type="range" min="0" max="100" value="50" aria-label="Преди и след, {{ $name }}" class="absolute inset-0 h-full w-full cursor-ew-resize opacity-0">
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-extrabold">{{ $name }}</h3>
                        <p class="mt-1 font-light text-[#4c5b66]">{{ $text }}</p>
                        <p class="mt-3 text-sm font-semibold text-[#c8552d]">Готов за {{ $days }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Guarantee and process --}}
<section id="garanciya" class="dr scroll-mt-16 bg-[#1c2730] px-4 py-20 text-white">
    <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2">
        <div>
            <p class="text-sm font-extrabold uppercase tracking-widest text-[#e8703f]">Гаранция</p>
            <h2 class="mt-3 text-4xl font-extrabold tracking-tight md:text-5xl">Написано в договора, не казано на ръка</h2>
            <dl class="mt-10 grid grid-cols-2 gap-px overflow-hidden rounded-2xl bg-white/15">
                @foreach([['15 години', 'за труда при нов покрив'], ['30 години', 'фабрична гаранция за покритието'], ['5 години', 'за ремонт и хидроизолация'], ['Всяка пролет', 'безплатен преглед през първите 3 години']] as [$n, $t])
                    <div class="bg-[#1c2730] p-6"><dd class="text-2xl font-extrabold text-[#e8703f]">{{ $n }}</dd><dt class="mt-1 font-light text-[#c9d3da]">{{ $t }}</dt></div>
                @endforeach
            </dl>
        </div>
        <ol class="space-y-6">
            @foreach([
                ['Оглед и замерване', 'Идваме до 48 часа, качваме се на покрива и снимаме с дрон.'],
                ['Оферта с крайна цена', 'До два работни дни получавате оферта по пера, с материали и срок.'],
                ['Договор и график', 'Подписваме договор. Работим без прекъсване, докато не свършим.'],
                ['Приемане и гаранционна карта', 'Оглеждаме заедно, почистваме двора и получавате гаранцията.'],
            ] as $i => [$title, $text])
                <li class="flex gap-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-[#e8703f] text-lg font-extrabold text-[#e8703f]">{{ $i + 1 }}</span>
                    <div><h3 class="text-xl font-extrabold">{{ $title }}</h3><p class="mt-1 font-light leading-relaxed text-[#c9d3da]">{{ $text }}</p></div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Inspection form --}}
<section id="ogled" class="dr scroll-mt-16 px-4 py-20">
    <div class="mx-auto grid max-w-6xl overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-black/5 lg:grid-cols-5">
        <div class="bg-[#c8552d] p-8 text-white md:p-10 lg:col-span-2">
            <h2 class="text-3xl font-extrabold tracking-tight md:text-4xl">Безплатен оглед до 48 часа</h2>
            <p class="mt-4 font-light text-[#fbe3d9]">Без задължение. Ако покривът може да се поправи, без да се сменя, ще ви го кажем.</p>
            <figure class="mt-10 border-t border-white/30 pt-6">
                <blockquote class="text-lg font-light leading-relaxed">„Три фирми ми казаха, че трябва нов покрив. Тук смениха 40 керемиди и обшивката на комина. Две зими няма капка.“</blockquote>
                <figcaption class="mt-3 text-sm font-semibold">Стоян Р., кв. Три чучура</figcaption>
            </figure>
            <a href="tel:+35942000000" data-demo-action class="mt-8 inline-flex items-center gap-2 text-xl font-extrabold"><x-icon name="phone" /> 042 000 000</a>
        </div>
        <form data-demo-form class="grid gap-4 p-8 md:p-10 lg:col-span-3">
            <div data-pick="need" class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach(['Нов покрив', 'Теч', 'Хидроизолация', 'Улуци'] as $i => $need)
                    <button type="button" data-value="{{ $need }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-lg border-2 border-[#e6ded2] py-2.5 text-sm font-semibold hover:border-[#c8552d] aria-pressed:border-[#c8552d] aria-pressed:bg-[#fbeee8]">{{ $need }}</button>
                @endforeach
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <input type="text" required placeholder="Име" class="rounded-lg border border-[#d9cfc0] px-4 py-3 focus:border-[#c8552d] focus:ring-[#c8552d]">
                <input type="tel" required placeholder="Телефон" class="rounded-lg border border-[#d9cfc0] px-4 py-3 focus:border-[#c8552d] focus:ring-[#c8552d]">
            </div>
            <input type="text" placeholder="Населено място и адрес" class="rounded-lg border border-[#d9cfc0] px-4 py-3 focus:border-[#c8552d] focus:ring-[#c8552d]">
            <label class="flex cursor-pointer items-center gap-4 rounded-lg border-2 border-dashed border-[#d9cfc0] p-4 hover:border-[#c8552d]">
                <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#f5f0e8] text-[#c8552d]"><x-icon name="image" /></span>
                <span><span class="block font-semibold">Добавете снимка на покрива</span><span class="text-sm font-light text-[#4c5b66]">По желание. Помага ни да дойдем подготвени.</span></span>
                <input type="file" accept="image/*" class="sr-only">
            </label>
            <button class="rounded-lg bg-[#1c2730] py-4 font-extrabold text-white hover:bg-[#c8552d]">Заявете оглед</button>
        </form>
    </div>
</section>

<footer class="bg-[#141c22] px-4 py-10 text-sm font-light text-[#9fb0bc]">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4">
        <p class="font-extrabold text-white">Здрав покрив</p>
        <p>бул. „Патриарх Евтимий“ 90, Стара Загора · Понеделник до събота, 8:00 до 18:00</p>
    </div>
</footer>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var menu = document.querySelector('details[data-menu]');
        if (menu) menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { menu.open = false; }); });
        var box = document.querySelector('[data-roof]'), range = box.querySelector('[data-range]'), old = box.querySelector('[data-old]'), price = 62;
        function money(n) { return (Math.round(n / 100) * 100).toLocaleString('bg-BG').replace(/ /g, ' '); }
        function draw() {
            var base = range.value * (price + (old.checked ? 6 : 0));
            box.querySelector('[data-area]').textContent = range.value;
            box.querySelector('[data-from]').textContent = money(base * 0.95);
            box.querySelector('[data-to]').textContent = money(base * 1.15);
        }
        range.addEventListener('input', draw); old.addEventListener('change', draw);
        box.querySelector('[data-pick]').addEventListener('pick', function (e) { price = Number(e.target.querySelector('[aria-pressed="true"]').dataset.price); draw(); });
        draw();

        document.querySelectorAll('[data-compare]').forEach(function (compare) {
            var before = compare.querySelector('[data-compare-before]');
            function size() { before.style.width = compare.offsetWidth + 'px'; }
            size(); window.addEventListener('resize', size);
            compare.querySelector('input').addEventListener('input', function (e) { compare.style.setProperty('--pos', e.target.value + '%'); });
        });
    })();
</script>
@endpush
