@extends('demos._layout')
@section('body_class', 'bg-white text-[#0f2b2c]')

@php
    $homes = [
        ['Тристаен апартамент с южна тераса', 'Кършияка', 'prodazhba novo', '148 000 €', 92, 3, '5 от 8', 'Ново', '#2f7f7b'],
        ['Двустаен до Гребната база', 'Западен', 'prodazhba', '96 500 €', 64, 2, '3 от 6', '', '#d98a5b'],
        ['Къща с двор в Марково', 'с. Марково', 'prodazhba kashta', '285 000 €', 210, 5, '2 етажа', 'Ексклузивно', '#5a7f4a'],
        ['Мезонет с гледка към тепетата', 'Център', 'prodazhba', '219 000 €', 128, 4, '6 от 6', '', '#7c6aa8'],
        ['Обзаведен двустаен под наем', 'Тракия', 'naem', '520 € на месец', 68, 2, '4 от 9', 'Свободен веднага', '#3f7fb5'],
        ['Студио в нова сграда', 'Христо Смирненски', 'naem novo', '380 € на месец', 41, 1, '2 от 7', '', '#c8795b'],
    ];
    $agents = [['Надежда Колева', 'Продажби, Център и Кършияка', '132 сделки', '#2f7f7b'], ['Стефан Груев', 'Нови сгради', '98 сделки', '#d98a5b'], ['Анелия Томова', 'Наеми и управление', '410 договора', '#7c6aa8']];
@endphp

@section('content')
<div class="demo-geo">
<header class="sticky top-0 z-50 border-b border-[#e3ebe9] bg-white/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="flex items-center gap-2 text-xl font-extrabold"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0f3d3e] text-white"><x-icon name="home" class="text-sm" /></span>Нов праг</a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#imoti" class="nav-u hover:text-[#ff6b4a]">Имоти</a><a href="#kredit" class="nav-u hover:text-[#ff6b4a]">Кредитен калкулатор</a><a href="#ocenka" class="nav-u hover:text-[#ff6b4a]">Продавам имот</a><a href="#ekip" class="nav-u hover:text-[#ff6b4a]">Брокери</a>
        </div>
        <div class="flex items-center gap-2">
            <a href="tel:+35932000000" data-demo-action aria-label="Обадете се на 032 000 000" class="inline-flex items-center gap-2 rounded-full bg-[#ff6b4a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#e85a3a] sm:px-5"><x-icon name="phone" /> <span class="max-sm:hidden">032 000 000</span></a>
            <details data-menu class="md:hidden">
                <summary aria-label="Меню" class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full bg-[#eef3f1] text-[#0f3d3e] [&::-webkit-details-marker]:hidden"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></summary>
                <div class="absolute inset-x-0 top-full border-b border-[#e3ebe9] bg-white px-4 pb-5 pt-2 shadow-xl">
                    <a href="#imoti" class="block border-b border-[#eef3f1] py-3.5 text-lg font-semibold">Имоти</a><a href="#kredit" class="block border-b border-[#eef3f1] py-3.5 text-lg font-semibold">Кредитен калкулатор</a><a href="#ocenka" class="block border-b border-[#eef3f1] py-3.5 text-lg font-semibold">Продавам имот</a><a href="#ekip" class="block py-3.5 text-lg font-semibold">Брокери</a>
                    <a href="tel:+35932000000" data-demo-action class="mt-2 flex items-center justify-center gap-2 rounded-xl bg-[#ff6b4a] py-3.5 font-semibold text-white"><x-icon name="phone" /> 032 000 000</a>
                </div>
            </details>
        </div>
    </nav>
</header>

{{-- Hero with search --}}
<section class="relative overflow-hidden bg-[#0f3d3e] text-white">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_80%_20%,#1d6b68_0%,transparent_55%)]" aria-hidden="true"></div>
    <svg viewBox="0 0 1440 360" preserveAspectRatio="xMidYMax slice" class="absolute inset-x-0 bottom-0 h-3/5 w-full opacity-20 lg:hidden" fill="#1c5a5b" aria-hidden="true">
        <path d="M0 360V230h70v-60h90v60h50V120h110v110h60v-70h80v70h70V60h130v170h50v-90h100v90h80V150h90v80h60V90h120v140h70v-50h80v50h130v130Z"/>
    </svg>
    <div class="pointer-events-none absolute inset-y-0 right-0 bottom-24 hidden w-[50%] lg:block" aria-hidden="true">
        <svg viewBox="0 0 700 560" preserveAspectRatio="xMaxYMax meet" class="absolute bottom-0 right-0 h-full w-full">
            <defs>
                <linearGradient id="hs-glass" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7fd6cf"/><stop offset=".55" stop-color="#2f8f8c"/><stop offset="1" stop-color="#165a5c"/></linearGradient>
                <linearGradient id="hs-side" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#0c3031"/><stop offset="1" stop-color="#15595a"/></linearGradient>
                <linearGradient id="hs-hill1" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2a7a77"/><stop offset="1" stop-color="#0f3d3e"/></linearGradient>
                <linearGradient id="hs-hill2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1c5f5e"/><stop offset="1" stop-color="#0f3d3e"/></linearGradient>
                <radialGradient id="hs-sun" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#ffb199"/><stop offset=".55" stop-color="#ff6b4a" stop-opacity=".55"/><stop offset="1" stop-color="#ff6b4a" stop-opacity="0"/></radialGradient>
                <linearGradient id="hs-fade" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0f3d3e" stop-opacity="0"/><stop offset="1" stop-color="#0f3d3e"/></linearGradient>
            </defs>
            <circle cx="430" cy="170" r="170" fill="url(#hs-sun)"/><circle cx="430" cy="170" r="52" fill="#ffd9c9" opacity=".92"/>
            <path d="M0 400c70-70 130-96 210-60 60-60 150-70 210-10 60-40 160-40 280 30V560H0Z" fill="url(#hs-hill1)" opacity=".75"/>
            <g transform="translate(120 290)"><path d="M0 70h8V30h-8Zm5-60v22" stroke="#e8f4f2" stroke-width="3" opacity=".7"/><path d="M-4 30h20l-10-22Z" fill="#e8f4f2" opacity=".7"/></g>
            <path d="M0 450c90-60 170-70 260-20 80-60 200-70 280-10 60-30 110-20 160 20v120H0Z" fill="url(#hs-hill2)"/>
            <g fill="#cfe9e6" opacity=".35"><path d="M60 470h30v40H60zM200 450h46l-23-24zM330 460h36v50h-36zM470 440h44v60h-44z"/></g>
            <g transform="translate(300 110)">
                <path d="M170 0 230 26V400H170Z" fill="url(#hs-side)"/>
                <path d="M0 26 170 0V400H0Z" fill="url(#hs-glass)"/>
                <g fill="#fff" opacity=".16">@foreach(range(0, 15) as $r)<path d="M10 {{ 40 + $r * 22 }}h150v8H10z"/>@endforeach</g>
                <g fill="#0f3d3e" opacity=".22">@foreach(range(0, 5) as $c)<rect x="{{ 10 + $c * 26 }}" y="40" width="3" height="350"/>@endforeach</g>
                <path d="M0 26 170 0 230 26 60 52Z" fill="#a6e3dd" opacity=".55"/>
                <path d="M30 120l60-50M30 200l110-95M30 280l140-120" stroke="#fff" stroke-width="10" opacity=".13" stroke-linecap="round"/>
                <g fill="#ffd27a">@foreach([[24, 150], [76, 194], [128, 150], [50, 282], [102, 238], [128, 326]] as [$x, $y])<rect x="{{ $x }}" y="{{ $y }}" width="22" height="14" rx="2" opacity=".85"/>@endforeach</g>
                <g fill="#0f2b2c" opacity=".38">@foreach(range(0, 15) as $r)<rect x="184" y="{{ 40 + $r * 22 }}" width="8" height="10"/><rect x="204" y="{{ 40 + $r * 22 }}" width="8" height="10"/>@endforeach</g>
            </g>
            <g transform="translate(150 250)">
                <path d="M130 0 180 20V260H130Z" fill="#0c3031"/><path d="M0 20 130 0V260H0Z" fill="#e9f3f1"/>
                <g fill="#2f8f8c" opacity=".55">@foreach(range(0, 6) as $r)@foreach(range(0, 3) as $c)<rect x="{{ 12 + $c * 30 }}" y="{{ 34 + $r * 30 }}" width="20" height="18" rx="2"/>@endforeach @endforeach</g>
                <g stroke="#0f3d3e" stroke-width="3" opacity=".35">@foreach(range(0, 6) as $r)<path d="M0 {{ 56 + $r * 30 }}h130"/>@endforeach</g>
                <rect x="104" y="-14" width="26" height="14" fill="#ff6b4a"/>
            </g>
            <g transform="translate(510 300)"><path d="M0 40 60 26V250H0Z" fill="#12494a"/><path d="M60 26 100 40V250H60Z" fill="#0c3031"/><g fill="#7fd6cf" opacity=".5">@foreach(range(0, 5) as $r)<rect x="10" y="{{ 56 + $r * 30 }}" width="40" height="14" rx="2"/>@endforeach</g></g>
            <rect x="0" y="508" width="700" height="52" fill="url(#hs-fade)"/>
            <g class="hero-float"><rect x="68" y="120" width="170" height="64" rx="16" fill="#fff"/><circle cx="102" cy="152" r="18" fill="#ff6b4a"/><path d="M93 152l7 7 12-14" stroke="#fff" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/><rect x="132" y="138" width="86" height="9" rx="4.500" fill="#0f3d3e"/><rect x="132" y="156" width="56" height="8" rx="4" fill="#9fb3b1"/></g>
        </svg>
    </div>
    <div class="relative mx-auto max-w-7xl px-4 pb-32 pt-16 lg:pt-24">
        <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-semibold"><span class="text-[#ffd27a]">★ 4,9</span> от 380 отзива · Пловдив и областта</p>
        <h1 class="mt-6 max-w-4xl text-5xl font-extrabold leading-[1.03] tracking-tight sm:text-6xl lg:max-w-[46%] lg:text-6xl xl:text-7xl">Намерете дома, в който <span class="text-[#ff8a6e]">ще останете</span></h1>
        <p class="mt-6 max-w-xl text-lg font-light leading-relaxed text-[#c4d9d7]">412 проверени имота в Пловдив. Всяка обява е с реални снимки, точен адрес и юридическа проверка преди оглед.</p>
    </div>
</section>

<section class="relative z-10 -mt-24 px-4">
    <form data-demo-form class="mx-auto max-w-6xl rounded-3xl bg-white p-5 shadow-2xl ring-1 ring-black/5 md:p-7">
        <div data-pick="deal" class="inline-flex rounded-full bg-[#eef3f1] p-1 text-sm font-semibold">
            <button type="button" data-value="Купувам" aria-pressed="true" class="rounded-full px-6 py-2 aria-pressed:bg-[#0f3d3e] aria-pressed:text-white">Купувам</button>
            <button type="button" data-value="Наемам" aria-pressed="false" class="rounded-full px-6 py-2 aria-pressed:bg-[#0f3d3e] aria-pressed:text-white">Наемам</button>
        </div>
        <div class="mt-5 grid gap-3 md:grid-cols-12">
            <label class="md:col-span-3"><span class="text-xs font-semibold uppercase tracking-wider text-[#5c7473]">Вид имот</span><select class="mt-1 w-full rounded-xl border border-[#d5e0de] px-4 py-3 font-semibold focus:border-[#0f3d3e] focus:ring-[#0f3d3e]"><option>Апартамент</option><option>Къща</option><option>Офис</option><option>Парцел</option></select></label>
            <label class="md:col-span-3"><span class="text-xs font-semibold uppercase tracking-wider text-[#5c7473]">Квартал</span><select class="mt-1 w-full rounded-xl border border-[#d5e0de] px-4 py-3 font-semibold focus:border-[#0f3d3e] focus:ring-[#0f3d3e]"><option>Всички квартали</option><option>Център</option><option>Кършияка</option><option>Тракия</option><option>Западен</option></select></label>
            <div class="md:col-span-2"><span class="text-xs font-semibold uppercase tracking-wider text-[#5c7473]">Стаи</span>
                <div data-pick="rooms" class="mt-1 grid grid-cols-4 gap-1">@foreach(['1', '2', '3', '4+'] as $r)<button type="button" data-value="{{ $r }}" aria-pressed="{{ $r === '3' ? 'true' : 'false' }}" class="rounded-xl border border-[#d5e0de] py-3 text-sm font-semibold aria-pressed:border-[#0f3d3e] aria-pressed:bg-[#0f3d3e] aria-pressed:text-white">{{ $r }}</button>@endforeach</div>
            </div>
            <label class="md:col-span-2"><span class="text-xs font-semibold uppercase tracking-wider text-[#5c7473]">Цена до</span><select class="mt-1 w-full rounded-xl border border-[#d5e0de] px-4 py-3 font-semibold focus:border-[#0f3d3e] focus:ring-[#0f3d3e]"><option>150 000 €</option><option>100 000 €</option><option>200 000 €</option><option>300 000 €</option></select></label>
            <button class="rounded-xl bg-[#ff6b4a] font-semibold text-white hover:bg-[#e85a3a] max-md:py-3.5 md:col-span-2 md:mt-5"><x-icon name="magnifying-glass" class="mr-1" /> Търси</button>
        </div>
        <p class="mt-4 text-sm font-light text-[#5c7473]">Често търсени са <a href="#imoti" class="font-semibold text-[#0f3d3e] underline underline-offset-4">двустайни в Кършияка</a>, <a href="#imoti" class="font-semibold text-[#0f3d3e] underline underline-offset-4">нови сгради с Акт 16</a>, <a href="#imoti" class="font-semibold text-[#0f3d3e] underline underline-offset-4">къщи до 15 км от Пловдив</a></p>
    </form>
</section>

{{-- Listings --}}
<section id="imoti" class="dr scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl" data-group>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div><p class="text-sm font-semibold text-[#ff6b4a]">Нови тази седмица</p><h2 class="mt-2 text-4xl font-extrabold tracking-tight md:text-5xl">Избрани имоти</h2></div>
            <div class="demo-scroll flex gap-2">
                @foreach(['all' => 'Всички', 'prodazhba' => 'Продажба', 'naem' => 'Наем', 'novo' => 'Нови сгради', 'kashta' => 'Къщи'] as $key => $label)
                    <button type="button" data-show="{{ $key }}" aria-pressed="{{ $key === 'all' ? 'true' : 'false' }}" class="shrink-0 rounded-full border border-[#d5e0de] px-5 py-2 text-sm font-semibold hover:border-[#0f3d3e] aria-pressed:border-[#0f3d3e] aria-pressed:bg-[#0f3d3e] aria-pressed:text-white">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($homes as $i => [$name, $area, $keys, $price, $sqm, $rooms, $floor, $badge, $color])
                <article data-key="{{ $keys }}" class="group overflow-hidden rounded-3xl bg-white ring-1 ring-[#e3ebe9] transition hover:-translate-y-1 hover:shadow-2xl">
                    <div class="relative">
                        <x-demo-photo :slug="$demo['slug']" name="home-{{ $i + 1 }}" alt="{{ $name }}" class="block aspect-[4/3] w-full" style="background: linear-gradient(180deg, {{ $color }}33, {{ $color }}99)">
                            <svg viewBox="0 0 400 300" class="h-full w-full transition duration-500 group-hover:scale-105" preserveAspectRatio="xMidYMid slice" fill="none">
                                <defs>
                                    <linearGradient id="sky-{{ $i }}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="{{ $color }}" stop-opacity=".25"/><stop offset="1" stop-color="#fff" stop-opacity=".9"/></linearGradient>
                                    <linearGradient id="gl-{{ $i }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".9"/><stop offset="1" stop-color="{{ $color }}" stop-opacity=".85"/></linearGradient>
                                    <linearGradient id="sh-{{ $i }}" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#0f2b2c" stop-opacity=".0"/><stop offset="1" stop-color="#0f2b2c" stop-opacity=".28"/></linearGradient>
                                </defs>
                                @if($i === 0)
                                    <rect width="400" height="300" fill="url(#sky-0)"/><circle cx="330" cy="62" r="26" fill="#ffd27a"/>
                                    <path d="M0 240c60-30 120-34 200-10s140 10 200-18v88H0Z" fill="#cfe5de"/>
                                    <rect x="96" y="40" width="208" height="244" fill="#f6faf9"/><rect x="96" y="40" width="208" height="14" fill="#0f2b2c"/><rect x="288" y="54" width="16" height="230" fill="url(#sh-0)"/>
                                    @foreach(range(0, 3) as $r)@foreach(range(0, 2) as $c)<rect x="{{ 112 + $c * 62 }}" y="{{ 70 + $r * 52 }}" width="46" height="34" fill="url(#gl-0)"/><rect x="{{ 108 + $c * 62 }}" y="{{ 106 + $r * 52 }}" width="54" height="7" fill="#0f2b2c" opacity=".75"/><path d="M{{ 108 + $c * 62 }} {{ 113 + $r * 52 }}v-7M{{ 162 + $c * 62 }} {{ 113 + $r * 52 }}v-7" stroke="#0f2b2c" stroke-width="2"/>@endforeach @endforeach
                                    <rect x="170" y="248" width="60" height="36" fill="{{ $color }}"/><circle cx="130" cy="106" r="7" fill="#5a9a62"/><circle cx="254" cy="158" r="8" fill="#5a9a62"/><circle cx="192" cy="210" r="7" fill="#5a9a62"/>
                                    <path d="M0 284h400v16H0Z" fill="#5a7f4a"/><circle cx="52" cy="262" r="22" fill="#4c8a58"/><rect x="49" y="270" width="6" height="16" fill="#5a3b24"/><circle cx="352" cy="266" r="18" fill="#6aa66f"/>
                                @elseif($i === 1)
                                    <rect width="400" height="300" fill="url(#sky-1)"/><circle cx="70" cy="66" r="24" fill="#ffe3b3"/>
                                    <rect x="150" y="70" width="170" height="150" rx="3" fill="#fbf1e6"/><path d="M150 70h170v16H150Z" fill="#b5653f"/><path d="M320 70l40 22v128h-40Z" fill="#e9d2bd"/>
                                    @foreach(range(0, 2) as $r)@foreach(range(0, 3) as $c)<rect x="{{ 164 + $c * 38 }}" y="{{ 100 + $r * 38 }}" width="26" height="26" fill="url(#gl-1)"/><path d="M{{ 164 + $c * 38 }} {{ 113 + $r * 38 }}h26" stroke="#fff" stroke-width="2" opacity=".7"/>@endforeach @endforeach
                                    <rect x="20" y="120" width="104" height="100" fill="#e9d2bd"/><path d="M14 120 72 84l58 36Z" fill="#b5653f"/><rect x="58" y="160" width="26" height="60" fill="{{ $color }}"/>
                                    <rect y="220" width="400" height="80" fill="#4f9bb8"/><path d="M0 232h400M0 252h400M0 274h400" stroke="#fff" stroke-width="3" opacity=".35" stroke-dasharray="40 18"/><path d="M150 220h170v40H150Z" fill="#fbf1e6" opacity=".25"/>
                                    <path d="M0 220h400" stroke="#2c6f8a" stroke-width="4"/><path d="M-20 232l60-12 60 12" fill="none"/>
                                    <path d="M40 300 95 214l55 86Z" fill="#fff" opacity=".18"/><g transform="translate(300 232)"><path d="M0 22c18 10 54 10 80 0l-10 14c-24 8-46 8-60 0Z" fill="#fff"/><rect x="38" y="10" width="3" height="14" fill="#0f2b2c"/></g>
                                    <circle cx="336" cy="172" r="26" fill="#5a9a62"/><rect x="333" y="190" width="6" height="30" fill="#5a3b24"/><circle cx="388" cy="190" r="22" fill="#6aa66f"/>
                                @elseif($i === 2)
                                    <rect width="400" height="300" fill="url(#sky-2)"/><circle cx="64" cy="58" r="28" fill="#ffe3b3"/>
                                    <path d="M0 230c70-26 150-30 230-8s120 6 170-10v88H0Z" fill="#a9d08a"/>
                                    <path d="M214 120h112v140H214Z" fill="#fbf3ea"/><path d="M200 124 270 70l70 54Z" fill="#7d3b2a"/><rect x="296" y="76" width="16" height="34" fill="#9a4a36"/>
                                    <rect x="80" y="130" width="150" height="132" fill="#fff"/><path d="M62 134 155 62l93 72Z" fill="#0f2b2c"/><path d="M62 134 155 62" stroke="#fff" stroke-width="2" opacity=".18"/>
                                    <rect x="138" y="196" width="34" height="66" rx="3" fill="{{ $color }}"/><circle cx="165" cy="230" r="2.500" fill="#ffd27a"/>
                                    @foreach([96, 184] as $x)<rect x="{{ $x }}" y="150" width="34" height="34" fill="url(#gl-2)"/><path d="M{{ $x + 17 }} 150v34M{{ $x }} 167h34" stroke="#fff" stroke-width="2"/>@endforeach
                                    <rect x="236" y="170" width="30" height="38" fill="url(#gl-2)"/><rect x="278" y="170" width="30" height="38" fill="url(#gl-2)"/>
                                    <path d="M150 262h30l30 38h-90Z" fill="#e3d9c8"/><path d="M0 262h400v6H0Z" fill="#78a55c"/>
                                    <g stroke="#fff" stroke-width="4">@foreach(range(0, 7) as $k)<path d="M{{ 12 + $k * 18 }} 262v-26"/>@endforeach</g><path d="M10 244h130" stroke="#fff" stroke-width="3"/>
                                    <circle cx="360" cy="196" r="38" fill="#5a9a62"/><circle cx="342" cy="212" r="26" fill="#6aa66f"/><rect x="355" y="226" width="10" height="42" fill="#5a3b24"/>
                                    <circle cx="36" cy="270" r="9" fill="#ff8a6e"/><circle cx="52" cy="276" r="8" fill="#ffd27a"/>
                                @elseif($i === 3)
                                    <rect width="400" height="300" fill="#e7def5"/><rect width="400" height="150" fill="url(#sky-3)"/><circle cx="300" cy="100" r="34" fill="#ffc9a8"/>
                                    <path d="M0 190c40-50 90-60 140-24 40-50 100-60 150-10 40-28 80-14 110 10V300H0Z" fill="#8b7cb5"/><path d="M0 220c70-30 150-30 220-4s130 0 180-14v98H0Z" fill="#5f9a6a"/>
                                    <path d="M44 84h312v176H44Z" fill="#fff"/><path d="M44 84h312v18H44Z" fill="#0f2b2c"/><path d="M44 260h312v14H44Z" fill="#cdbfe2"/>
                                    <rect x="60" y="116" width="170" height="122" fill="url(#gl-3)"/><path d="M60 238 140 116M110 238l84-122M170 238l60-72" stroke="#fff" stroke-width="12" opacity=".2"/><path d="M60 116v122M117 116v122M174 116v122" stroke="#0f2b2c" stroke-width="3"/>
                                    <rect x="246" y="116" width="94" height="122" fill="#f3ecfa"/><rect x="258" y="130" width="70" height="90" fill="url(#gl-3)"/>
                                    <path d="M36 236h328v12H36Z" fill="#0f2b2c"/><g stroke="#0f2b2c" stroke-width="3">@foreach(range(0, 15) as $k)<path d="M{{ 44 + $k * 20 }} 236v-22"/>@endforeach</g><path d="M36 214h328" stroke="#0f2b2c" stroke-width="4"/>
                                    <g transform="translate(260 196)"><rect width="38" height="20" rx="4" fill="#ff8a6e"/><rect x="6" y="20" width="4" height="10" fill="#0f2b2c"/><rect x="28" y="20" width="4" height="10" fill="#0f2b2c"/></g><circle cx="90" cy="206" r="10" fill="#4c8a58"/><rect x="86" y="214" width="8" height="22" rx="2" fill="#b5653f"/>
                                @elseif($i === 4)
                                    <rect width="400" height="300" fill="#f4ede3"/><rect y="236" width="400" height="64" fill="#d9c3a5"/><path d="M0 236h400" stroke="#c4aa86" stroke-width="3"/>
                                    <rect x="34" y="40" width="150" height="150" rx="4" fill="#fff"/><rect x="42" y="48" width="134" height="134" fill="url(#gl-4)"/><path d="M109 48v134M42 115h134" stroke="#fff" stroke-width="5"/><path d="M42 182 110 100l50 82Z" fill="#fff" opacity=".22"/><path d="M42 182h134v8H42Z" fill="#e8dccb"/>
                                    <rect x="236" y="46" width="108" height="76" rx="3" fill="#fff" stroke="#c4aa86" stroke-width="5"/><path d="M246 110l26-34 24 24 16-14 20 24Z" fill="{{ $color }}" opacity=".8"/><circle cx="318" cy="66" r="8" fill="#ffd27a"/>
                                    <rect x="150" y="170" width="200" height="76" rx="14" fill="#7aa3c9"/><rect x="150" y="150" width="200" height="40" rx="14" fill="#8fb6dc"/><rect x="136" y="176" width="26" height="70" rx="12" fill="#6b94bb"/><rect x="338" y="176" width="26" height="70" rx="12" fill="#6b94bb"/>
                                    <rect x="170" y="164" width="46" height="34" rx="9" fill="#fff" opacity=".7"/><rect x="226" y="164" width="46" height="34" rx="9" fill="#ff8a6e"/><rect x="146" y="246" width="12" height="14" fill="#5a3b24"/><rect x="342" y="246" width="12" height="14" fill="#5a3b24"/>
                                    <ellipse cx="250" cy="274" rx="150" ry="14" fill="#b86a4b" opacity=".35"/><g transform="translate(24 150)"><rect x="40" y="44" width="5" height="64" fill="#0f2b2c"/><path d="M20 44h46l-8-34H28Z" fill="#ffd27a"/><ellipse cx="42" cy="110" rx="18" ry="5" fill="#0f2b2c"/></g>
                                    <g transform="translate(364 190)"><path d="M0 56h28l-4 -34H4Z" fill="#c8795b"/><path d="M14 24c-18-10-14-34 0-44 14 10 18 34 0 44Z" fill="#5a9a62"/></g>
                                @else
                                    <rect width="400" height="300" fill="#f1ece6"/><rect y="240" width="400" height="60" fill="#caa985"/>
                                    <rect x="200" y="30" width="170" height="170" rx="4" fill="#fff"/><rect x="208" y="38" width="154" height="154" fill="url(#gl-5)"/><path d="M285 38v154" stroke="#fff" stroke-width="5"/>
                                    <g fill="#0f2b2c" opacity=".35"><rect x="220" y="124" width="22" height="68"/><rect x="248" y="100" width="26" height="92"/><rect x="298" y="116" width="24" height="76"/><rect x="328" y="90" width="26" height="102"/></g><path d="M208 192h154" stroke="#e1d6c8" stroke-width="8"/>
                                    <rect x="28" y="168" width="190" height="78" rx="8" fill="#fff"/><rect x="28" y="150" width="190" height="34" rx="8" fill="{{ $color }}"/><rect x="40" y="154" width="78" height="26" rx="12" fill="#fff"/><rect x="128" y="158" width="78" height="22" rx="10" fill="#fff" opacity=".7"/><rect x="28" y="246" width="190" height="8" fill="#0f2b2c" opacity=".2"/>
                                    <rect x="28" y="110" width="14" height="70" rx="3" fill="#0f2b2c"/><rect x="28" y="110" width="30" height="36" rx="3" fill="#0f2b2c"/>
                                    <g transform="translate(240 206)"><rect width="80" height="34" rx="4" fill="#fff"/><path d="M0 12h80" stroke="#e1d6c8" stroke-width="3"/><circle cx="20" cy="26" r="5" fill="#0f2b2c" opacity=".4"/><circle cx="44" cy="26" r="5" fill="#0f2b2c" opacity=".4"/></g>
                                    <g transform="translate(40 40)"><rect width="100" height="10" rx="3" fill="#b5653f"/><rect x="8" y="-30" width="20" height="30" rx="3" fill="#ff8a6e"/><rect x="34" y="-40" width="16" height="40" rx="3" fill="{{ $color }}"/><circle cx="76" cy="-12" r="12" fill="#5a9a62"/><rect x="72" y="-2" width="8" height="2" fill="#5a3b24"/></g>
                                    <ellipse cx="124" cy="270" rx="100" ry="10" fill="#8f6d49" opacity=".3"/>
                                @endif
                            </svg>
                        </x-demo-photo>
                        @if($badge !== '')<span class="absolute left-4 top-4 rounded-full bg-[#ff6b4a] px-3 py-1 text-xs font-semibold text-white">{{ $badge }}</span>@endif
                        <button type="button" data-like aria-pressed="false" aria-label="Запази в любими" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-lg text-[#9fb3b1] shadow aria-pressed:text-[#ff6b4a]">♥</button>
                        <span class="absolute bottom-4 left-4 rounded-full bg-black/55 px-3 py-1 text-xs font-semibold text-white">{{ 12 + $i * 3 }} снимки · 3D разходка</span>
                    </div>
                    <div class="p-6">
                        <p class="text-2xl font-extrabold tracking-tight">{{ $price }}</p>
                        <h3 class="mt-1 font-semibold">{{ $name }}</h3>
                        <p class="mt-1 flex items-center gap-1.5 text-sm font-light text-[#5c7473]"><x-icon name="location-dot" class="text-xs" /> {{ $area }}, Пловдив</p>
                        <dl class="mt-5 grid grid-cols-3 gap-2 border-t border-[#e9efee] pt-4 text-sm">
                            <div><dt class="font-light text-[#5c7473]">Площ</dt><dd class="font-semibold">{{ $sqm }} кв. м</dd></div>
                            <div><dt class="font-light text-[#5c7473]">Стаи</dt><dd class="font-semibold">{{ $rooms }}</dd></div>
                            <div><dt class="font-light text-[#5c7473]">Етаж</dt><dd class="font-semibold">{{ $floor }}</dd></div>
                        </dl>
                        <button type="button" data-demo-action class="mt-5 w-full rounded-xl border border-[#0f3d3e] py-3 text-sm font-semibold hover:bg-[#0f3d3e] hover:text-white">Заяви оглед</button>
                    </div>
                </article>
            @endforeach
        </div>
        <p class="mt-10 text-center"><a href="#" data-demo-action class="inline-flex items-center gap-2 rounded-full bg-[#0f3d3e] px-8 py-4 font-semibold text-white hover:bg-[#165253]">Вижте всички 412 имота <x-icon name="arrow-right" class="text-xs" /></a></p>
    </div>
</section>

{{-- Why --}}
<section class="dr bg-[#eef3f1] px-4 py-20">
    <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2">
        <div>
            <h2 class="text-4xl font-extrabold tracking-tight md:text-5xl">Покупка без неприятни изненади</h2>
            <p class="mt-5 max-w-lg font-light leading-relaxed text-[#48605f]">Проверяваме имота, преди да го покажем. Знаете кой е собственикът, има ли тежести и колко реално са квадратите.</p>
            <dl class="mt-10 grid grid-cols-2 gap-6">
                @foreach([['1 240', 'сделки от 2011'], ['38 дни', 'средно до продажба'], ['97%', 'от исканата цена'], ['0 €', 'комисиона за наемател']] as [$n, $t])
                    <div class="rounded-2xl bg-white p-6"><dd class="text-3xl font-extrabold text-[#0f3d3e]">{{ $n }}</dd><dt class="mt-1 text-sm font-light text-[#48605f]">{{ $t }}</dt></div>
                @endforeach
            </dl>
        </div>
        <ol class="space-y-4">
            @foreach([['Разговор за нуждите', 'Бюджет, квартал, срок. Казваме честно какво може да се намери за тези пари.'], ['Подбор и огледи', 'Получавате списък до 24 часа. Огледите са в удобно за вас време, и вечер.'], ['Юридическа проверка', 'Наш юрист проверява собствеността, тежестите и документите на сградата.'], ['Сделка и ключове', 'Съдействаме за кредит, подготвяме документите и сме с вас при нотариуса.']] as $i => [$title, $text])
                <li class="flex gap-5 rounded-2xl bg-white p-6"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#ff6b4a] font-extrabold text-white">{{ $i + 1 }}</span><div><h3 class="text-lg font-semibold">{{ $title }}</h3><p class="mt-1 font-light leading-relaxed text-[#48605f]">{{ $text }}</p></div></li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Mortgage calculator --}}
<section id="kredit" class="dr scroll-mt-16 px-4 py-20">
    <div class="mx-auto grid max-w-7xl overflow-hidden rounded-[2rem] bg-[#0f3d3e] text-white lg:grid-cols-2" data-loan>
        <div class="p-8 md:p-12">
            <p class="text-sm font-semibold text-[#ff8a6e]">Кредитен калкулатор</p>
            <h2 class="mt-2 text-4xl font-extrabold tracking-tight">Колко ще плащате на месец</h2>
            @foreach([['price', 'Цена на имота', 40000, 400000, 1000, 148000, '€'], ['down', 'Собствени средства', 10, 60, 5, 20, '%'], ['years', 'Срок', 5, 35, 1, 25, 'години']] as [$key, $label, $min, $max, $step, $value, $unit])
                <label class="mt-8 flex items-baseline justify-between"><span class="font-light text-[#c4d9d7]">{{ $label }}</span><span class="text-xl font-extrabold"><span data-out="{{ $key }}">{{ $value }}</span> {{ $unit }}</span></label>
                <input type="range" data-in="{{ $key }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" value="{{ $value }}" aria-label="{{ $label }}" class="mt-3 w-full accent-[#ff6b4a]">
            @endforeach
        </div>
        <div class="flex flex-col justify-center bg-white/[0.06] p-8 md:p-12">
            <p class="font-light text-[#c4d9d7]">Месечна вноска при лихва 2,9%</p>
            <p class="mt-2 text-6xl font-extrabold tracking-tight md:text-7xl"><span data-out="monthly">555</span> €</p>
            <dl class="mt-8 grid grid-cols-2 gap-6 border-t border-white/15 pt-6">
                <div><dt class="text-sm font-light text-[#c4d9d7]">Размер на кредита</dt><dd class="text-2xl font-extrabold"><span data-out="loan">118 400</span> €</dd></div>
                <div><dt class="text-sm font-light text-[#c4d9d7]">Самоучастие</dt><dd class="text-2xl font-extrabold"><span data-out="own">29 600</span> €</dd></div>
            </dl>
            <button type="button" data-demo-action class="mt-8 rounded-xl bg-[#ff6b4a] py-4 font-semibold hover:bg-[#e85a3a]">Безплатна консултация с кредитен консултант</button>
            <p class="mt-3 text-xs font-light text-[#9fbcba]">Сметката е ориентировъчна. Сравняваме офертите на седем банки вместо вас.</p>
        </div>
    </div>
</section>

{{-- Valuation --}}
<section id="ocenka" class="dr scroll-mt-16 px-4 pb-20">
    <div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2">
        <div>
            <p class="text-sm font-semibold text-[#ff6b4a]">Продавате имот</p>
            <h2 class="mt-2 text-4xl font-extrabold tracking-tight md:text-5xl">Разберете колко струва имотът ви</h2>
            <p class="mt-5 max-w-lg font-light leading-relaxed text-[#48605f]">Безплатна оценка до 24 часа, по реални сделки в квартала от последните шест месеца, не по цените в обявите.</p>
            <ul class="mt-8 space-y-3">
                @foreach(['Професионални снимки, видео и 3D разходка от нас', 'Обявата ви в 9 портала и пред 6 400 купувачи в нашата база', 'Комисиона само при сключена сделка'] as $point)
                    <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#d9ece9] text-xs text-[#0f3d3e]"><x-icon name="check" /></span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>
        <form data-demo-form class="grid gap-4 rounded-3xl bg-[#eef3f1] p-8 md:p-10">
            <div data-pick="kind" class="grid grid-cols-3 gap-2">
                @foreach(['Апартамент', 'Къща', 'Друго'] as $i => $kind)<button type="button" data-value="{{ $kind }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-xl border border-[#d5e0de] bg-white py-3 text-sm font-semibold aria-pressed:border-[#0f3d3e] aria-pressed:bg-[#0f3d3e] aria-pressed:text-white">{{ $kind }}</button>@endforeach
            </div>
            <input type="text" required placeholder="Адрес или квартал" class="rounded-xl border border-[#d5e0de] px-4 py-3.5 focus:border-[#0f3d3e] focus:ring-[#0f3d3e]">
            <div class="grid grid-cols-2 gap-4">
                <input type="number" placeholder="Площ в кв. м" class="rounded-xl border border-[#d5e0de] px-4 py-3.5 focus:border-[#0f3d3e] focus:ring-[#0f3d3e]">
                <input type="tel" required placeholder="Телефон" class="rounded-xl border border-[#d5e0de] px-4 py-3.5 focus:border-[#0f3d3e] focus:ring-[#0f3d3e]">
            </div>
            <button class="rounded-xl bg-[#0f3d3e] py-4 font-semibold text-white hover:bg-[#165253]">Искам безплатна оценка</button>
        </form>
    </div>
</section>

{{-- Trust strip --}}
<section class="dr border-y border-[#e3ebe9] bg-white px-4 py-12">
    <ul class="mx-auto grid max-w-7xl gap-x-6 gap-y-7 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([['Лицензирани посредници', 'Всеки брокер е вписан в публичния регистър.'], ['Застраховка на отговорността', 'Покрива грешка при сделката, не само при огледа.'], ['Юрист във всяка сделка', 'Проверка на собствеността преди първия оглед.'], ['Договор без скрити такси', 'Комисионата е написана в цифри още на първата среща.']] as $k => [$t, $d])
            <li class="flex items-start gap-4">
                <svg viewBox="0 0 48 48" class="h-12 w-12 shrink-0" fill="none" aria-hidden="true"><path d="M24 3 42 10v13c0 11-8 19-18 22C14 42 6 34 6 23V10Z" fill="#0f3d3e"/><path d="M24 9 36 14v9c0 8-5 14-12 17-7-3-12-9-12-17v-9Z" fill="#1c5a5b"/>@if($k === 0)<path d="M17 24l5 5 9-11" stroke="#ffd27a" stroke-width="3.500" stroke-linecap="round" stroke-linejoin="round"/>@elseif($k === 1)<path d="M24 16v16M17 22h14" stroke="#ffd27a" stroke-width="3.500" stroke-linecap="round"/>@elseif($k === 2)<path d="M24 15v18M16 19h16M16 19l-3 8h6ZM32 19l-3 8h6Z" stroke="#ffd27a" stroke-width="2.500" stroke-linecap="round" stroke-linejoin="round"/>@else<circle cx="24" cy="24" r="8" stroke="#ffd27a" stroke-width="3"/><path d="M20 24h8" stroke="#ffd27a" stroke-width="3" stroke-linecap="round"/>@endif</svg>
                <div><h3 class="font-semibold leading-snug">{{ $t }}</h3><p class="mt-1 text-sm font-light leading-relaxed text-[#48605f]">{{ $d }}</p></div>
            </li>
        @endforeach
    </ul>
</section>

{{-- Agents --}}
<section id="ekip" class="scroll-mt-16 bg-[#eef3f1] px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="text-4xl font-extrabold tracking-tight md:text-5xl">Брокерите</h2>
        <div class="dr mt-12 grid gap-6 md:grid-cols-3">
            @foreach($agents as $i => [$name, $role, $deals, $color])
                <article class="flex items-center gap-5 rounded-3xl bg-white p-6">
                    <x-demo-photo :slug="$demo['slug']" name="agent-{{ $i + 1 }}" alt="{{ $name }}" class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl" style="background: {{ $color }}33">
                        <svg viewBox="0 0 100 100" class="h-full w-full" fill="none">
                            <defs><linearGradient id="ag-bg-{{ $i }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".7"/><stop offset="1" stop-color="{{ $color }}" stop-opacity=".45"/></linearGradient></defs>
                            <rect width="100" height="100" fill="url(#ag-bg-{{ $i }})"/>
                            @if($i === 0)
                                <path d="M24 100c0-24 12-38 26-38s26 14 26 38Z" fill="#0f3d3e"/><path d="M40 62l10 18 10-18Z" fill="#fff"/><path d="M33 52c-6-4-8-14-4-26 4-12 14-16 22-16s18 4 22 16c4 12 2 22-4 26l4 22c-6 0-10-4-12-10H45c-2 6-6 10-12 10Z" fill="#4a2f22"/><rect x="44" y="50" width="12" height="14" fill="#f0c7a4"/><ellipse cx="50" cy="40" rx="14" ry="16" fill="#f6d3b3"/><path d="M36 34c4-12 24-14 30 2-8-2-18-4-30-2Z" fill="#4a2f22"/><circle cx="44" cy="41" r="1.500" fill="#2a1a12"/><circle cx="56" cy="41" r="1.500" fill="#2a1a12"/><path d="M45 48c3 3 7 3 10 0" stroke="#c2553a" stroke-width="2" stroke-linecap="round"/>
                            @elseif($i === 1)
                                <path d="M20 100c0-24 14-38 30-38s30 14 30 38Z" fill="#2c3e50"/><path d="M42 62l8 20 8-20Z" fill="#fff"/><path d="M48 70l2 6 2-6-2-6Z" fill="#ff6b4a"/><rect x="43" y="50" width="14" height="14" fill="#d9a67f"/><ellipse cx="50" cy="40" rx="14" ry="16" fill="#e3b58f"/><path d="M36 38c0-16 28-18 28 0-4-6-10-9-14-9s-10 3-14 9Z" fill="#2a1a12"/><path d="M36 44c2 14 8 20 14 20s12-6 14-20c-4 8-8 10-14 10s-10-2-14-10Z" fill="#2a1a12"/><circle cx="44" cy="40" r="1.500" fill="#2a1a12"/><circle cx="56" cy="40" r="1.500" fill="#2a1a12"/><path d="M45 52c3 2 7 2 10 0" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                            @else
                                <path d="M22 100c0-24 12-38 28-38s28 14 28 38Z" fill="#6a4f9a"/><path d="M38 62c4 10 8 14 12 14s8-4 12-14Z" fill="#f4e9ff"/><rect x="44" y="50" width="12" height="14" fill="#f2c9a9"/><path d="M32 54c-8-18-2-38 18-38s26 20 18 38c-2-8-4-12-4-12H36s-2 4-4 12Z" fill="#b5653f"/><ellipse cx="50" cy="40" rx="14" ry="16" fill="#f8d6b8"/><path d="M35 38c2-14 12-18 22-14 6 2 8 8 8 14-6-6-16-10-30 0Z" fill="#b5653f"/><circle cx="44" cy="41" r="1.500" fill="#2a1a12"/><circle cx="56" cy="41" r="1.500" fill="#2a1a12"/><path d="M44 48c4 4 8 4 12 0" stroke="#c2553a" stroke-width="2" stroke-linecap="round"/><circle cx="35" cy="46" r="2" fill="#ffd27a"/><circle cx="65" cy="46" r="2" fill="#ffd27a"/>
                            @endif
                        </svg>
                    </x-demo-photo>
                    <div class="min-w-0"><h3 class="text-lg font-semibold">{{ $name }}</h3><p class="text-sm font-light text-[#48605f]">{{ $role }}</p><p class="mt-2 text-sm font-semibold text-[#ff6b4a]">{{ $deals }}</p><a href="tel:+35932000000" data-demo-action class="mt-1 inline-block text-sm font-semibold underline underline-offset-4">Обади се</a></div>
                </article>
            @endforeach
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-2">
            @foreach([['Продадохме апартамента на майка ми за 31 дни и на 4 000 евро над цената, която друга агенция ни каза.', 'Веселин Д., продавач'], ['Гледахме пет имота за една събота. Юристът им намери проблем с документите на първия ни избор и ни спести много нерви.', 'Яна и Тодор, купувачи']] as [$quote, $who])
                <figure class="rounded-3xl bg-[#0f3d3e] p-8 text-white"><p class="text-[#ffd27a]" aria-label="5 от 5 звезди">★★★★★</p><blockquote class="mt-3 text-lg font-light leading-relaxed">„{{ $quote }}“</blockquote><figcaption class="mt-4 text-sm font-semibold text-[#ff8a6e]">{{ $who }}</figcaption></figure>
            @endforeach
        </div>
    </div>
</section>

<footer class="px-4 py-10 text-sm font-light text-[#5c7473]">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4"><p class="font-extrabold text-[#0f2b2c]">Нов праг · Агенция за недвижими имоти</p><p>бул. „Шести септември“ 152, Пловдив · Понеделник до събота, 9:00 до 19:00</p></div>
</footer>
</div>
@endsection

@push('head')
<style>
    .nav-u { background: linear-gradient(#ff6b4a, #ff6b4a) 0 100% / 0 2px no-repeat; padding-bottom: 4px; }
    @media (prefers-reduced-motion: no-preference) {
        .nav-u { transition: background-size .3s ease, color .2s ease; }
        .nav-u:hover { background-size: 100% 2px; }
        @keyframes hero-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .hero-float { animation: hero-float 5s ease-in-out infinite; }
        #ekip article, .dr .rounded-2xl.bg-white { transition: transform .25s ease, box-shadow .25s ease; }
        #ekip article:hover, .dr dl .rounded-2xl:hover { transform: translateY(-4px); box-shadow: 0 18px 30px -18px rgba(15, 61, 62, .45); }
        [data-show], [data-pick] button { transition: background-color .2s ease, color .2s ease, border-color .2s ease; }
        summary { transition: background-color .2s ease; }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var menu = document.querySelector('[data-menu]');
        if (menu) menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { menu.removeAttribute('open'); }); });
    })();
</script>
@endpush

@push('scripts')
<script>
    (function () {
        var box = document.querySelector('[data-loan]');
        function fmt(n) { return Math.round(n).toLocaleString('bg-BG').replace(/ /g, ' '); }
        function val(key) { return Number(box.querySelector('[data-in="' + key + '"]').value); }
        function out(key, text) { box.querySelector('[data-out="' + key + '"]').textContent = text; }
        function draw() {
            var price = val('price'), own = price * val('down') / 100, loan = price - own, r = 0.029 / 12, n = val('years') * 12;
            out('price', fmt(price)); out('down', val('down')); out('years', val('years'));
            out('loan', fmt(loan)); out('own', fmt(own)); out('monthly', fmt(loan * r / (1 - Math.pow(1 + r, -n))));
        }
        box.querySelectorAll('[data-in]').forEach(function (input) { input.addEventListener('input', draw); });
        draw();
        document.querySelectorAll('[data-like]').forEach(function (b) { b.addEventListener('click', function () { b.setAttribute('aria-pressed', b.getAttribute('aria-pressed') === 'true' ? 'false' : 'true'); }); });
    })();
</script>
@endpush
