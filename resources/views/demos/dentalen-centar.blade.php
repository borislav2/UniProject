@extends('demos._layout')
@section('body_class', 'bg-white text-[#0f2a33]')

@php
    $services = [
        ['Профилактика', 'Преглед, почистване на зъбен камък и полиране. Препоръчваме два пъти в годината.', 'от 35 €', 'shield-halved'],
        ['Лечение на кариес', 'Фотополимерни пломби в цвета на зъба, с упойка, която не се усеща.', 'от 45 €', 'heart-pulse'],
        ['Импланти', 'Планиране с 3D скенер и водач. Нов зъб без да се пилят съседните.', 'от 690 €', 'cogs'],
        ['Брекети и алайнери', 'Метални, керамични и прозрачни шини. Първата консултация е безплатна.', 'от 950 €', 'check-circle'],
        ['Избелване', 'Кабинетно избелване за един час, до осем тона по-светло.', '190 €', 'sun'],
        ['Детска стоматология', 'Спокоен първи преглед с игра. Силанти и лечение без страх.', 'от 25 €', 'users'],
    ];
    $team = [
        ['Д-р Елена Маринова', 'Имплантолог, управител', '18 години опит', '#0e8f8f'],
        ['Д-р Ивайло Христов', 'Ортодонт', '12 години опит', '#2f6fb0'],
        ['Д-р Невена Колева', 'Детски зъболекар', '9 години опит', '#e08a5b'],
    ];
    $prices = [
        ['Първичен преглед с план за лечение', '20 €'], ['Панорамна снимка', '18 €'], ['Почистване на зъбен камък и полиране', '45 €'],
        ['Фотополимерна пломба', '45 до 70 €'], ['Лечение на канал', '80 до 140 €'], ['Циркониева корона', '260 €'], ['Имплант с корона', 'от 690 €'],
    ];
@endphp

@push('head')
<style>
    .nav-u { position: relative; }
    .nav-u::after { content: ''; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px; background: #0e8f8f; transform: scaleX(0); transform-origin: left; transition: transform .3s ease; }
    .nav-u:hover::after { transform: scaleX(1); }
    @media (prefers-reduced-motion: no-preference) {
        @keyframes dc-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @keyframes dc-twinkle { 0%, 100% { opacity: .35; transform: scale(.85); } 50% { opacity: 1; transform: scale(1.1); } }
        @keyframes dc-drift { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(10px, -14px); } }
        .dc-float { animation: dc-float 5s ease-in-out infinite; }
        .dc-twinkle { transform-box: fill-box; transform-origin: center; animation: dc-twinkle 3.6s ease-in-out infinite; }
        .dc-twinkle.d2 { animation-delay: 1.2s; } .dc-twinkle.d3 { animation-delay: 2.1s; }
        .dc-drift { animation: dc-drift 9s ease-in-out infinite; }
        .dc-lift { transition: transform .3s ease, box-shadow .3s ease; }
        .dc-lift:hover { transform: translateY(-4px); box-shadow: 0 18px 40px -22px rgba(11, 59, 68, .5); }
        .dc-tab { transition: background-color .2s ease, border-color .2s ease, color .2s ease, transform .2s ease; }
        .dc-tab:active { transform: scale(.97); }
    }
</style>
@endpush

@section('content')
<div class="demo-geo">
<div class="bg-[#0b3b44] px-4 py-2 text-center text-sm text-white">
    <span class="mr-2 inline-flex h-2 w-2 rounded-full bg-[#ff7a59]"></span>Спешна болка? Приемаме в същия ден. <a href="tel:+35956000000" data-demo-action class="font-semibold underline underline-offset-2">Обадете се на 056 000 000</a>
</div>
<header class="sticky top-0 z-50 border-b border-[#e3eef0] bg-white/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="flex items-center gap-2 text-xl font-semibold"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0e8f8f] text-white"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M7.5 2C4.6 2 3 4.2 3 7c0 2.300.900 3.600 1.500 5.500.500 1.600.600 4.500 1.200 6.800.300 1.200.900 2.700 2 2.700 1.500 0 1.500-2.300 1.800-3.800.300-1.400.900-2.700 2.500-2.700s2.200 1.300 2.500 2.700c.300 1.500.300 3.800 1.800 3.800 1.100 0 1.700-1.500 2-2.700.600-2.300.700-5.200 1.200-6.800.600-1.900 1.500-3.200 1.500-5.500 0-2.800-1.600-5-4.500-5-1.800 0-2.900 1-4.500 1S9.300 2 7.500 2Z"/></svg></span>Бяла усмивка</a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#uslugi" class="nav-u hover:text-[#0e8f8f]">Услуги</a><a href="#ekip" class="nav-u hover:text-[#0e8f8f]">Лекари</a><a href="#ceni" class="nav-u hover:text-[#0e8f8f]">Цени</a><a href="#vaprosi" class="nav-u hover:text-[#0e8f8f]">Въпроси</a>
        </div>
        <span class="flex items-center gap-3">
            <a href="#chas" class="rounded-full bg-[#0e8f8f] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#0b7676]">Запази час</a>
            <button type="button" data-menu-button aria-expanded="false" aria-controls="dc-menu" aria-label="Меню" class="flex h-10 w-10 items-center justify-center rounded-full border border-[#dbe9eb] text-[#0e8f8f] md:hidden"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path data-menu-lines d="M4 7h16M4 12h16M4 17h16"/></svg></button>
        </span>
    </nav>
    <div id="dc-menu" hidden class="absolute inset-x-0 top-full border-b border-[#dbe9eb] bg-white shadow-xl md:hidden">
        <div class="mx-auto flex max-w-7xl flex-col px-4 py-3 text-base font-semibold">
            @foreach([['#uslugi', 'Услуги'], ['#ekip', 'Лекари'], ['#ceni', 'Цени'], ['#vaprosi', 'Въпроси']] as [$href, $label])
                <a href="{{ $href }}" class="border-b border-[#eef4f5] py-3.5">{{ $label }}</a>
            @endforeach
            <a href="#chas" class="my-4 rounded-full bg-[#0e8f8f] py-3 text-center text-white">Запази час за преглед</a>
        </div>
    </div>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden bg-[linear-gradient(180deg,#eef8f8_0%,#ffffff_100%)]">
    <svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 1440 720" preserveAspectRatio="xMidYMax slice" fill="none" aria-hidden="true">
        <defs>
            <radialGradient id="dc-glow" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#9fe0da" stop-opacity=".75"/><stop offset="1" stop-color="#9fe0da" stop-opacity="0"/></radialGradient>
            <radialGradient id="dc-warm" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#ffc9b5" stop-opacity=".7"/><stop offset="1" stop-color="#ffc9b5" stop-opacity="0"/></radialGradient>
            <linearGradient id="dc-tooth" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#cfeeeb"/></linearGradient>
            <symbol id="dc-t" viewBox="0 0 100 120"><path d="M30 6C14 6 4 18 4 36c0 14 6 22 9 38 2 12 4 42 15 42 9 0 8-26 14-34h16c6 8 5 34 14 34 11 0 13-30 15-42 3-16 9-24 9-38C96 18 86 6 70 6c-9 0-13 5-20 5S39 6 30 6Z"/></symbol>
        </defs>
        <circle cx="1130" cy="330" r="430" fill="url(#dc-glow)"/>
        <circle cx="120" cy="640" r="260" fill="url(#dc-warm)"/>
        <g stroke="#0e8f8f" stroke-opacity=".14"><circle cx="1130" cy="330" r="300"/><circle cx="1130" cy="330" r="390" stroke-dasharray="3 9"/><circle cx="1130" cy="330" r="480" stroke-opacity=".08"/></g>
        <g class="dc-drift" transform="translate(570 560) rotate(-12)"><use href="#dc-t" width="104" height="125" fill="url(#dc-tooth)" stroke="#0e8f8f" stroke-opacity=".32" stroke-width="1.5"/><path d="M22 30c-5 3-8 8-8 15" stroke="#fff" stroke-width="3.5" stroke-linecap="round" opacity=".95"/></g>
        <g class="dc-drift" transform="translate(668 610) rotate(14)" opacity=".9"><use href="#dc-t" width="58" height="70" fill="url(#dc-tooth)" stroke="#0e8f8f" stroke-opacity=".3" stroke-width="1.5"/></g>
        <g class="dc-drift" transform="translate(1362 40) rotate(16)" opacity=".9"><use href="#dc-t" width="60" height="72" fill="url(#dc-tooth)" stroke="#0e8f8f" stroke-opacity=".3" stroke-width="1.5"/></g>
        <g fill="#0e8f8f"><path class="dc-twinkle" d="M440 40l6 18 18 6-18 6-6 18-6-18-18-6 18-6Z" opacity=".5"/><path class="dc-twinkle d2" d="M1010 70l4 12 12 4-12 4-4 12-4-12-12-4 12-4Z" opacity=".45"/><path class="dc-twinkle d3" d="M1370 520l5 14 14 5-14 5-5 14-5-14-14-5 14-5Z" opacity=".4"/></g>
        <g fill="#ff7a59"><path class="dc-twinkle d2" d="M700 90l4 11 11 4-11 4-4 11-4-11-11-4 11-4Z" opacity=".6"/><path class="dc-twinkle" d="M40 190l3 9 9 3-9 3-3 9-3-9-9-3 9-3Z" opacity=".55"/></g>
        <g fill="#0e8f8f" opacity=".16"><circle cx="610" cy="620" r="7"/><circle cx="650" cy="650" r="4"/><circle cx="1220" cy="640" r="9"/><circle cx="250" cy="130" r="5"/></g>
        <path d="M-20 700Q720 600 1460 700" stroke="#0e8f8f" stroke-opacity=".16" stroke-width="2"/>
        <g fill="#fff" stroke="#0e8f8f" stroke-opacity=".2">@foreach(range(0, 11) as $i)<rect x="{{ 20 + $i * 122 }}" y="{{ 676 + (int) round(22 * sin(($i / 11) * 3.14159)) * -1 + 14 }}" width="64" height="48" rx="22"/>@endforeach</g>
    </svg>
    <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-6">
            <p class="text-sm font-semibold text-[#0e8f8f]">Дентален център в центъра на Бургас</p>
            <h1 class="mt-4 text-5xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl">Зъболекар, при когото <span class="text-[#0e8f8f]">не ви е страх</span> да отидете</h1>
            <p class="mt-6 max-w-lg text-lg font-light leading-relaxed text-[#48636b]">Обясняваме какво ще правим, казваме цената преди да започнем и работим с упойка, която не се усеща. Лечението е с гаранция до 5 години.</p>
            <ul class="mt-7 grid max-w-lg gap-3 text-sm sm:grid-cols-2">
                @foreach(['Работим с НЗОК', 'План и цена преди лечението', 'Разсрочено плащане без лихва', 'Паркинг за пациенти'] as $point)
                    <li class="flex items-center gap-2"><span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#d6f0ee] text-[10px] text-[#0e8f8f]"><x-icon name="check" /></span>{{ $point }}</li>
                @endforeach
            </ul>
            <div class="mt-9 flex flex-wrap gap-6 border-t border-[#dbe9eb] pt-6">
                @foreach([['4,9', 'от 5 в Google'], ['12 400', 'пациенти от 2008'], ['5 години', 'гаранция на импланти']] as [$n, $t])
                    <p><span class="block text-3xl font-extrabold">{{ $n }}</span><span class="text-sm text-[#48636b]">{{ $t }}</span></p>
                @endforeach
            </div>
        </div>

        {{-- Appointment card --}}
        <div id="chas" class="scroll-mt-24 lg:col-span-6">
            <form data-demo-form class="relative rounded-[2rem] bg-white p-6 shadow-[0_30px_80px_-30px_rgba(11,59,68,.45)] ring-1 ring-[#dbe9eb] md:p-8">
                <span class="absolute -top-4 right-6 rounded-full bg-[#e8603d] px-4 py-1.5 text-xs font-semibold text-white">Свободни часове тази седмица</span>
                <span class="dc-float absolute -top-5 left-8 hidden items-center gap-2 rounded-full bg-white py-1.5 pl-1.5 pr-4 text-xs font-semibold text-[#0b3b44] shadow-lg ring-1 ring-[#dbe9eb] sm:flex"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#0e8f8f] text-white"><x-icon name="shield-halved" class="text-[11px]" /></span>Гаранция до 5 години</span>
                <h2 class="text-2xl font-semibold">Запазете час за преглед</h2>
                <p class="mt-1 text-sm text-[#48636b]">Връщаме обаждане до един час в работно време.</p>
                <p class="mt-6 text-xs font-semibold uppercase tracking-wider text-[#0e8f8f]">Какво ви води при нас</p>
                <div data-pick="reason" class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach(['Преглед', 'Болка', 'Почистване', 'Имплант', 'Брекети', 'Дете'] as $i => $reason)
                        <button type="button" data-value="{{ $reason }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="dc-tab rounded-xl border border-[#dbe9eb] py-2.5 text-sm font-semibold hover:border-[#0e8f8f] aria-pressed:border-[#0e8f8f] aria-pressed:bg-[#e6f6f5] aria-pressed:text-[#0b6f6f]">{{ $reason }}</button>
                    @endforeach
                </div>
                <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-[#0e8f8f]">Кога ви е удобно</p>
                <div data-pick="when" class="mt-2 grid grid-cols-3 gap-2">
                    @foreach(['Сутрин', 'Следобед', 'След 17 часа'] as $i => $when)
                        <button type="button" data-value="{{ $when }}" aria-pressed="{{ $i === 1 ? 'true' : 'false' }}" class="dc-tab rounded-xl border border-[#dbe9eb] py-2.5 text-sm font-semibold hover:border-[#0e8f8f] aria-pressed:border-[#0e8f8f] aria-pressed:bg-[#e6f6f5] aria-pressed:text-[#0b6f6f]">{{ $when }}</button>
                    @endforeach
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <input type="text" required placeholder="Име" class="rounded-xl border border-[#dbe9eb] px-4 py-3 focus:border-[#0e8f8f] focus:ring-[#0e8f8f]">
                    <input type="tel" required placeholder="Телефон" class="rounded-xl border border-[#dbe9eb] px-4 py-3 focus:border-[#0e8f8f] focus:ring-[#0e8f8f]">
                </div>
                <button class="mt-4 w-full rounded-xl bg-[#0e8f8f] py-3.5 font-semibold text-white hover:bg-[#0b7676]">Запази час за <span data-pick-out="reason" class="lowercase">преглед</span></button>
                <p class="mt-3 text-center text-xs text-[#4f6a72]">Първичният преглед е 20 € и се приспада от лечението.</p>
            </form>
        </div>
    </div>
</section>

{{-- Services --}}
<section id="uslugi" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold text-[#0e8f8f]">Услуги</p>
            <h2 class="mt-3 text-4xl font-extrabold tracking-tight md:text-5xl">Всичко за усмивката на едно място</h2>
        </div>
        <div class="dr mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($services as [$name, $text, $price, $icon])
                <article class="group flex flex-col rounded-3xl border border-[#dbe9eb] p-7 transition hover:-translate-y-1 hover:border-[#0e8f8f] hover:shadow-xl">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#e6f6f5] text-xl text-[#0e8f8f] transition group-hover:bg-[#0e8f8f] group-hover:text-white"><x-icon :name="$icon" /></span>
                    <h3 class="mt-5 text-xl font-semibold">{{ $name }}</h3>
                    <p class="mt-2 flex-1 font-light leading-relaxed text-[#48636b]">{{ $text }}</p>
                    <p class="mt-5 flex items-center justify-between border-t border-[#e8f1f2] pt-4"><span class="font-semibold">{{ $price }}</span><a href="#chas" class="text-sm font-semibold text-[#0e8f8f]">Запази час <x-icon name="arrow-right" class="text-xs" /></a></p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- First visit --}}
<section class="bg-[#0b3b44] px-4 py-20 text-white">
    <div class="mx-auto max-w-7xl">
        <h2 class="max-w-2xl text-4xl font-extrabold tracking-tight md:text-5xl">Как минава първото посещение</h2>
        <ol class="dr mt-12 grid gap-8 md:grid-cols-4">
            @foreach([
                ['Разговор', 'Разказвате какво ви притеснява. Не бързаме и не съдим.'],
                ['Преглед и снимка', 'Панорамна снимка на място и преглед с камера, за да видите и вие.'],
                ['План с цени', 'Получавате писмен план с етапите и точната цена за всеки.'],
                ['Лечение', 'Започваме, когато вие решите. Можете да платите на части.'],
            ] as $i => [$title, $text])
                <li class="border-t border-white/25 pt-6">
                    <span class="text-5xl font-extrabold text-[#5fd0c8]">0{{ $i + 1 }}</span>
                    <h3 class="mt-4 text-xl font-semibold">{{ $title }}</h3>
                    <p class="mt-2 font-light leading-relaxed text-[#bcd6da]">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Team --}}
<section id="ekip" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <p class="text-sm font-semibold text-[#0e8f8f]">Лекарите</p>
        <h2 class="mt-3 text-4xl font-extrabold tracking-tight md:text-5xl">Знаете при кого отивате</h2>
        <div class="dr mt-12 grid gap-6 md:grid-cols-3">
            @foreach($team as $i => [$name, $role, $years, $color])
                <article class="dc-lift overflow-hidden rounded-3xl bg-[#f3f9fa]">
                    <x-demo-photo :slug="$demo['slug']" name="team-{{ $i + 1 }}" alt="{{ $name }}" class="relative aspect-[4/3] w-full" style="background: linear-gradient(160deg, {{ $color }}33, {{ $color }}aa)">
                        @if($i === 0)
<svg viewBox="0 0 200 150" class="absolute inset-0 h-full w-full" fill="none">
  <defs><linearGradient id="dp-coat-0" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#e3eef0"/></linearGradient></defs>
  <rect x="12" y="14" width="46" height="58" rx="4" fill="#fff" opacity=".3"/><rect x="18" y="20" width="34" height="46" rx="2" fill="#fff" opacity=".3"/><path d="M35 20v46M18 43h34" stroke="#fff" stroke-width="2" opacity=".5"/>
  <circle cx="100" cy="64" r="58" fill="#fff" opacity=".2"/>
  <circle cx="100" cy="27" r="11" fill="#5a3a2a"/>
  <path d="M30 150c0-30 20-46 50-50l20 12 20-12c30 4 50 20 50 50Z" fill="url(#dp-coat-0)"/>
  <path d="M82 100l18 30 18-30 6 4-24 46h-0l-24-46Z" fill="#0e8f8f"/><path d="M76 98l16 40-24 12-18-34c0-8 12-14 26-18ZM124 98l16 40 24 12 18-34c0-8-12-14-26-18Z" fill="#fff" stroke="#c9dde0"/>
  <path d="M76 104c-10 12-6 28 8 30" stroke="#4a5a63" stroke-width="2.5" stroke-linecap="round"/><circle cx="85" cy="134" r="4.5" fill="#8fa3ab" stroke="#4a5a63" stroke-width="1.5"/>
  <path d="M90 88h20v14c-3 6-17 6-20 0Z" fill="#e8b796"/>
  <ellipse cx="100" cy="62" rx="22" ry="26" fill="#f1c7a5"/><ellipse cx="78" cy="64" rx="3" ry="5" fill="#e8b796"/><ellipse cx="122" cy="64" rx="3" ry="5" fill="#e8b796"/>
  <path d="M77 62c-2-22 10-34 25-34 14 0 24 10 21 34-6-12-10-18-14-22-8 6-20 10-32 22Z" fill="#5a3a2a"/>
  <ellipse cx="91" cy="62" rx="2.2" ry="2.6" fill="#2a2a2a"/><ellipse cx="109" cy="62" rx="2.2" ry="2.6" fill="#2a2a2a"/><path d="M86 56q5-3 9 0M105 56q5-3 9 0" stroke="#5a3a2a" stroke-width="1.6" stroke-linecap="round"/>
  <path d="M92 73q8 7 16 0" stroke="#b4584d" stroke-width="2.2" stroke-linecap="round"/><circle cx="84" cy="70" r="4" fill="#f09a8a" opacity=".35"/><circle cx="116" cy="70" r="4" fill="#f09a8a" opacity=".35"/>
</svg>
@elseif($i === 1)
<svg viewBox="0 0 200 150" class="absolute inset-0 h-full w-full" fill="none">
  <defs><linearGradient id="dp-coat-1" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#e3eef0"/></linearGradient></defs>
  <rect x="140" y="16" width="44" height="56" rx="4" fill="#fff" opacity=".3"/><path d="M148 30h28M148 40h28M148 50h18" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".5"/>
  <circle cx="100" cy="64" r="58" fill="#fff" opacity=".2"/>
  <path d="M26 150c0-30 22-46 54-50l20 12 20-12c32 4 54 20 54 50Z" fill="url(#dp-coat-1)"/>
  <path d="M82 100l18 28 18-28 6 4-24 46-24-46Z" fill="#2f6fb0"/><path d="M76 98l16 40-24 12-22-34c0-8 14-14 30-18ZM124 98l16 40 24 12 22-34c0-8-14-14-30-18Z" fill="#fff" stroke="#c9dde0"/>
  <rect x="128" y="124" width="16" height="4" rx="2" fill="#2f6fb0" opacity=".6"/>
  <path d="M89 86h22v16c-4 6-18 6-22 0Z" fill="#c8946b"/>
  <ellipse cx="100" cy="61" rx="23" ry="27" fill="#d9a67c"/><ellipse cx="77" cy="63" rx="3" ry="5" fill="#c8946b"/><ellipse cx="123" cy="63" rx="3" ry="5" fill="#c8946b"/>
  <path d="M77 60c-3-24 9-34 24-34 16 0 26 10 22 34-2-8-4-12-8-14-12 4-26 2-38 14Z" fill="#2b2420"/>
  <path d="M78 66c2 20 12 30 22 30s20-10 22-30c-4 10-10 12-22 12S82 76 78 66Z" fill="#2b2420" opacity=".4"/>
  <ellipse cx="91" cy="62" rx="2.2" ry="2.6" fill="#1b1b1b"/><ellipse cx="109" cy="62" rx="2.2" ry="2.6" fill="#1b1b1b"/>
  <g stroke="#2b3a46" stroke-width="2"><rect x="82" y="55" width="17" height="14" rx="5"/><rect x="101" y="55" width="17" height="14" rx="5"/><path d="M99 61h2M82 60l-6-2M118 60l6-2"/></g>
  <path d="M93 76q7 5 14 0" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/>
</svg>
@else
<svg viewBox="0 0 200 150" class="absolute inset-0 h-full w-full" fill="none">
  <defs><linearGradient id="dp-coat-2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#e3eef0"/></linearGradient></defs>
  <circle cx="40" cy="38" r="22" fill="#fff" opacity=".28"/><circle cx="160" cy="48" r="14" fill="#fff" opacity=".28"/><path d="M150 100l4-9 4 9 9 4-9 4-4 9-4-9-9-4Z" fill="#fff" opacity=".5"/>
  <circle cx="100" cy="64" r="58" fill="#fff" opacity=".2"/>
  <path d="M70 52c-8 22-8 48 6 58h48c14-10 14-36 6-58-6-22-54-22-60 0Z" fill="#b9562f"/>
  <path d="M32 150c0-30 20-46 48-50l20 12 20-12c28 4 48 20 48 50Z" fill="url(#dp-coat-2)"/>
  <path d="M82 100l18 26 18-26 6 4-24 46-24-46Z" fill="#e08a5b"/><path d="M76 98l16 40-24 12-16-34c0-8 12-14 24-18ZM124 98l16 40 24 12 16-34c0-8-12-14-24-18Z" fill="#fff" stroke="#c9dde0"/>
  <circle cx="64" cy="124" r="7" fill="#0e8f8f"/><path d="M61 124h6M64 121v6" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
  <path d="M91 88h18v14c-3 5-15 5-18 0Z" fill="#eebf9f"/>
  <ellipse cx="100" cy="62" rx="21" ry="25" fill="#f6d3b8"/>
  <path d="M77 62c-4-24 8-36 24-36 15 0 26 12 22 36-3-10-7-16-12-20-10 8-22 12-34 20Z" fill="#b9562f"/><path d="M80 44c10-10 30-10 40 0" stroke="#d27449" stroke-width="2" stroke-linecap="round" opacity=".7"/>
  <ellipse cx="92" cy="63" rx="2.2" ry="2.8" fill="#2a2a2a"/><ellipse cx="108" cy="63" rx="2.2" ry="2.8" fill="#2a2a2a"/><path d="M87 58q5-3 9 0M104 58q5-3 9 0" stroke="#8a3f1f" stroke-width="1.5" stroke-linecap="round"/>
  <path d="M91 74q9 8 18 0" stroke="#c0574d" stroke-width="2.4" stroke-linecap="round"/><circle cx="85" cy="71" r="4" fill="#f09a8a" opacity=".4"/><circle cx="115" cy="71" r="4" fill="#f09a8a" opacity=".4"/>
</svg>
@endif
                    </x-demo-photo>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold">{{ $name }}</h3>
                        <p class="text-[#48636b]">{{ $role }}</p>
                        <p class="mt-3 inline-block rounded-full bg-white px-3 py-1 text-sm font-semibold text-[#0e8f8f] ring-1 ring-[#dbe9eb]">{{ $years }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Technology and care --}}
<section class="px-4 pb-4 pt-2">
    <div class="dr mx-auto grid max-w-7xl gap-px overflow-hidden rounded-3xl bg-[#dbe9eb] ring-1 ring-[#dbe9eb] sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['3D скенер', 'Планираме импланти до десета от милиметъра.', 'cogs'],
            ['Стерилност клас B', 'Всеки инструмент е в запечатан плик, който отваряте пред вас.', 'shield-halved'],
            ['Упойка без убождане', 'Първо гел, после игла. Повечето пациенти не усещат нищо.', 'heart-pulse'],
            ['Гаранция в договора', 'До пет години, записана на хартия още преди лечението.', 'check-circle'],
        ] as [$t, $d, $ic])
            <div class="group flex gap-4 bg-white p-6 transition hover:bg-[#f3f9fa]">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#e6f6f5] text-[#0e8f8f] transition group-hover:bg-[#0e8f8f] group-hover:text-white"><x-icon :name="$ic" /></span>
                <div><h3 class="font-semibold">{{ $t }}</h3><p class="mt-1 text-sm font-light leading-relaxed text-[#48636b]">{{ $d }}</p></div>
            </div>
        @endforeach
    </div>
</section>

{{-- Prices and payments --}}
<section id="ceni" class="scroll-mt-16 bg-[#f3f9fa] px-4 py-20">
    <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <p class="text-sm font-semibold text-[#0e8f8f]">Цени</p>
            <h2 class="mt-3 text-4xl font-extrabold tracking-tight md:text-5xl">Знаете цената предварително</h2>
            <dl class="dr mt-8 divide-y divide-[#dbe9eb] rounded-3xl bg-white px-6 ring-1 ring-[#dbe9eb]">
                @foreach($prices as [$name, $price])
                    <div class="flex items-center justify-between gap-4 py-4"><dt>{{ $name }}</dt><dd class="shrink-0 font-semibold">{{ $price }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="dr rounded-3xl bg-[#0e8f8f] p-8 text-white lg:col-span-2" data-installment>
            <h3 class="text-2xl font-semibold">Плащане на вноски без лихва</h3>
            <p class="mt-2 font-light text-[#d6f0ee]">Вижте колко излиза на месец.</p>
            <label class="mt-8 flex items-baseline justify-between text-sm"><span>Сума на лечението</span><span class="text-2xl font-extrabold"><span data-sum>1 200</span> €</span></label>
            <input type="range" min="300" max="6000" step="100" value="1200" data-range class="mt-3 w-full accent-white" aria-label="Сума на лечението">
            <div data-pick="months" class="mt-6 grid grid-cols-4 gap-2">
                @foreach([3, 6, 12, 24] as $m)
                    <button type="button" data-value="{{ $m }}" aria-pressed="{{ $m === 12 ? 'true' : 'false' }}" class="rounded-xl border border-white/40 py-2 text-sm font-semibold aria-pressed:bg-white aria-pressed:text-[#0b6f6f]">{{ $m }} мес.</button>
                @endforeach
            </div>
            <p class="mt-8 border-t border-white/30 pt-6 text-sm">Месечна вноска</p>
            <p class="text-5xl font-extrabold"><span data-monthly>100</span> €</p>
            <p class="mt-4 text-xs font-light text-[#d6f0ee]">Без лихва и без такса за разглеждане. Одобрение на място за 15 минути.</p>
        </div>
    </div>
</section>

{{-- Reviews and questions --}}
<section id="vaprosi" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight md:text-4xl">Какво казват пациентите</h2>
            <div class="dr mt-8 space-y-5">
                @foreach([
                    ['Не бях ходила на зъболекар от осем години от страх. Д-р Маринова ми обясни всичко на снимката и не усетих нищо.', 'Десислава Т.'],
                    ['Синът ми вече сам пита кога ще ходим пак. Получихме план с цени и нямаше нито една изненада.', 'Николай В.'],
                ] as [$quote, $who])
                    <figure class="dc-lift rounded-3xl border border-[#dbe9eb] p-7">
                        <p class="text-[#f2a93b]" aria-label="5 от 5 звезди">★★★★★</p>
                        <blockquote class="mt-3 text-lg font-light leading-relaxed">„{{ $quote }}“</blockquote>
                        <figcaption class="mt-4 text-sm font-semibold">{{ $who }} <span class="font-light text-[#4f6a72]">· отзив в Google</span></figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight md:text-4xl">Често задавани въпроси</h2>
            <div class="dr mt-8 divide-y divide-[#dbe9eb] border-y border-[#dbe9eb]">
                @foreach([
                    ['Боли ли поставянето на имплант?', 'Не. Работим с местна упойка и повечето пациенти казват, че е по-леко от вадене на зъб. След това даваме точни указания и телефон за връзка.'],
                    ['Работите ли със Здравната каса?', 'Да. Покриваме дейностите по НЗОК за възрастни и деца. На първия преглед казваме какво се поема и какво се доплаща.'],
                    ['Колко често трябва да идвам на преглед?', 'На шест месеца. Напомняме ви със съобщение, за да не мислите за това.'],
                    ['Какво да направя при силна болка извън работно време?', 'Обадете се на 056 000 000. В почивните дни имаме дежурен лекар от 9 до 14 часа.'],
                ] as [$q, $a])
                    <details class="group py-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold">{{ $q }}<span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#e6f6f5] text-xs text-[#0e8f8f] transition group-open:rotate-180"><x-icon name="chevron-down" /></span></summary>
                        <p class="mt-3 font-light leading-relaxed text-[#48636b]">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</section>

<footer class="bg-[#0b3b44] px-4 py-12 text-white">
    <div class="mx-auto grid max-w-7xl gap-8 md:grid-cols-3">
        <div><p class="text-xl font-semibold">Бяла усмивка</p><p class="mt-3 font-light text-[#bcd6da]">ул. „Александровска“ 58, Бургас<br>Вход откъм вътрешния двор, етаж 2</p></div>
        <div class="font-light text-[#bcd6da]"><p class="font-semibold text-white">Работно време</p><p class="mt-3">Понеделник до петък, 8:30 до 19:30<br>Събота, 9:00 до 14:00</p></div>
        <div class="flex flex-col items-start gap-3"><a href="tel:+35956000000" data-demo-action class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 font-semibold text-[#0b3b44]"><x-icon name="phone" /> 056 000 000</a><a href="#chas" class="font-semibold underline underline-offset-4">Запази час онлайн</a></div>
    </div>
</footer>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var btn = document.querySelector('[data-menu-button]'), menu = document.getElementById('dc-menu');
        function setMenu(open) { menu.hidden = ! open; btn.setAttribute('aria-expanded', open ? 'true' : 'false'); btn.querySelector('[data-menu-lines]').setAttribute('d', open ? 'M6 6l12 12M18 6L6 18' : 'M4 7h16M4 12h16M4 17h16'); }
        btn.addEventListener('click', function () { setMenu(menu.hidden); });
        menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
    })();
    (function () {
        var box = document.querySelector('[data-installment]'), range = box.querySelector('[data-range]'), months = 12;
        function draw() {
            box.querySelector('[data-sum]').textContent = Number(range.value).toLocaleString('bg-BG').replace(/ /g, ' ');
            box.querySelector('[data-monthly]').textContent = Math.round(range.value / months);
        }
        range.addEventListener('input', draw);
        box.querySelector('[data-pick]').addEventListener('pick', function (e) { months = Number(e.detail); draw(); });
    })();
</script>
@endpush
