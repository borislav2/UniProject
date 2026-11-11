@extends('demos._layout')
@section('body_class', 'bg-[#0e1012] text-white pb-16 md:pb-0')

@php
    $jobs = [
        ['Компютърна диагностика', 25, '30 мин'], ['Смяна на масло и филтри', 49, '45 мин'], ['Предни накладки', 59, '1 час'],
        ['Предни дискове и накладки', 129, '1 ч 30 мин'], ['Зареждане на климатик', 45, '40 мин'], ['Ангренажен комплект', 240, '4 часа'],
        ['Съединител', 290, '5 часа'], ['Геометрия на ходовата част', 35, '40 мин'],
    ];
    $services = [
        ['Диагностика', 'Четем грешките с оригинален софтуер и казваме какво е спешно и какво може да почака.', 'gauge-high'],
        ['Двигател и ангренаж', 'Ремонт и обслужване на бензинови, дизелови и хибридни двигатели.', 'cogs'],
        ['Спирачки и ходова', 'Накладки, дискове, амортисьори, носачи и геометрия на 3D стенд.', 'shield-halved'],
        ['Климатици', 'Профилактика, зареждане, търсене на теч и дезинфекция.', 'sun'],
        ['Електрика', 'Стартери, алтернатори, акумулатори, сензори и окабеляване.', 'chart-line'],
        ['Преглед преди покупка', 'Проверка на 120 точки, с доклад и снимки, преди да платите колата.', 'clipboard-list'],
    ];
@endphp

@push('head')
<style>
    .av-link { position: relative; }
    .av-link::after { content: ""; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px; background: #ffd400; transform: scaleX(0); transform-origin: left; }
    .av-link:hover::after { transform: scaleX(1); }
    details[data-menu] > summary::-webkit-details-marker { display: none; }
    details[data-menu][open] .av-bars { display: none; }
    details[data-menu]:not([open]) .av-close { display: none; }
    @media (prefers-reduced-motion: no-preference) {
        .av-link::after { transition: transform .25s ease; }
        .av-lift { transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease; }
        .av-lift:hover { transform: translateY(-5px); border-color: #ffd400; box-shadow: 0 14px 30px -12px rgba(255, 212, 0, .35); }
        [data-show], [data-value] { transition: background-color .2s ease, color .2s ease, border-color .2s ease; }
        @keyframes av-glow { 0%, 100% { opacity: .55; } 50% { opacity: 1; } }
        @keyframes av-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        @keyframes av-sway { 0%, 100% { transform: rotate(-1.2deg); } 50% { transform: rotate(1.2deg); } }
        .av-glow { animation: av-glow 3.6s ease-in-out infinite; }
        .av-float { animation: av-float 5s ease-in-out infinite; }
        .av-sway { transform-origin: 200px 44px; animation: av-sway 6s ease-in-out infinite; }
    }
</style>
@endpush

@section('content')
<div class="demo-geo">
<header class="sticky top-0 z-50 border-b border-white/10 bg-[#0e1012]/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="flex items-center gap-2 text-xl font-extrabold uppercase tracking-tight"><span class="bg-[#ffd400] px-2 py-0.5 text-black">Мотор</span>Про</a>
        <div class="hidden items-center gap-8 text-sm font-semibold uppercase tracking-wider text-gray-300 md:flex">
            <a href="#ceni" class="av-link hover:text-[#ffd400]">Цени</a><a href="#uslugi" class="av-link hover:text-[#ffd400]">Услуги</a><a href="#kak" class="av-link hover:text-[#ffd400]">Как работим</a><a href="#kontakt" class="av-link hover:text-[#ffd400]">Контакт</a>
        </div>
        <div class="flex items-center gap-2">
            <a href="tel:+35982000000" data-demo-action class="inline-flex items-center gap-2 bg-[#ffd400] px-4 py-2 text-sm font-extrabold text-black hover:bg-white"><x-icon name="phone" /> 082 000 000</a>
            <details data-menu class="md:hidden">
                <summary aria-label="Меню" class="flex h-10 w-10 cursor-pointer list-none items-center justify-center border border-white/25 text-lg text-white hover:border-[#ffd400] hover:text-[#ffd400]"><span class="av-bars"><x-icon name="bars" /></span><span class="av-close text-2xl leading-none">&times;</span></summary>
                <div class="absolute inset-x-0 top-full border-b-4 border-[#ffd400] bg-[#0e1012] px-4 pb-5 pt-2 shadow-2xl">
                    <div class="grid text-lg font-extrabold uppercase tracking-wide">
                        @foreach([['#ceni', 'Цени'], ['#uslugi', 'Услуги'], ['#kak', 'Как работим'], ['#kontakt', 'Контакт']] as [$href, $label])
                            <a href="{{ $href }}" class="border-b border-white/10 py-4 hover:text-[#ffd400]">{{ $label }}</a>
                        @endforeach
                    </div>
                    <a href="#chas" class="mt-4 block bg-[#ffd400] py-4 text-center font-extrabold uppercase tracking-wide text-black">Запази час</a>
                </div>
            </details>
        </div>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-[repeating-linear-gradient(135deg,transparent_0_38px,rgba(255,255,255,.025)_38px_40px)]"></div>
    <div class="absolute -right-24 top-0 h-full w-1/2 -skew-x-12 bg-[#ffd400]/[0.06]"></div>
    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-7">
            <p class="inline-flex items-center gap-2 border border-[#ffd400]/50 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-[#ffd400]"><span class="h-2 w-2 animate-pulse rounded-full bg-[#3ddc84]"></span> Отворено сега · свободен подемник днес в 14:30</p>
            <h1 class="mt-6 text-5xl font-extrabold uppercase leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl">Казваме цената,<br><span class="text-[#ffd400]">преди да пипнем</span><br>колата</h1>
            <p class="mt-6 max-w-xl text-lg font-light leading-relaxed text-gray-300">Сервиз за всички марки в Русе. Диагностика за 30 минути, писмена оферта и 12 месеца гаранция за труда и частите.</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="#ceni" class="bg-[#ffd400] px-8 py-4 font-extrabold uppercase tracking-wide text-black hover:bg-white">Изчисли цена</a>
                <a href="#chas" class="border-2 border-white/30 px-8 py-4 font-extrabold uppercase tracking-wide hover:border-[#ffd400] hover:text-[#ffd400]">Запази час</a>
            </div>
            <dl class="mt-12 grid max-w-xl grid-cols-3 gap-px bg-white/10">
                @foreach([['17', 'години в бранша'], ['4,8', 'от 5 в Google'], ['12 мес.', 'гаранция']] as [$n, $t])
                    <div class="bg-[#0e1012] py-4 pr-4"><dd class="text-3xl font-extrabold text-[#ffd400]">{{ $n }}</dd><dt class="mt-1 text-sm font-light text-gray-400">{{ $t }}</dt></div>
                @endforeach
            </dl>
        </div>
        <div class="lg:col-span-5">
            <x-demo-photo :slug="$demo['slug']" name="hero" alt="Автомобил на подемник в сервиза" class="relative aspect-[5/4] w-full overflow-hidden border border-white/10 bg-[linear-gradient(160deg,#1c2024,#0e1012)]">
                <svg viewBox="0 0 400 320" class="absolute inset-0 h-full w-full" fill="none">
                    <defs>
                        <linearGradient id="av-body" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffe45c"/><stop offset=".55" stop-color="#ffd400"/><stop offset="1" stop-color="#c79f00"/></linearGradient>
                        <linearGradient id="av-glass" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#3a4752"/><stop offset="1" stop-color="#10151a"/></linearGradient>
                        <linearGradient id="av-post" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#2a2f34"/><stop offset=".5" stop-color="#5b646c"/><stop offset="1" stop-color="#252a2e"/></linearGradient>
                        <linearGradient id="av-floor" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#23272b"/><stop offset="1" stop-color="#0e1012"/></linearGradient>
                        <radialGradient id="av-rim" cx=".4" cy=".35" r=".8"><stop offset="0" stop-color="#e8ecef"/><stop offset="1" stop-color="#6a737b"/></radialGradient>
                        <radialGradient id="av-cone" cx=".5" cy="0" r="1"><stop offset="0" stop-color="#ffd400" stop-opacity=".45"/><stop offset="1" stop-color="#ffd400" stop-opacity="0"/></radialGradient>
                    </defs>
                    <rect x="0" y="0" width="400" height="262" fill="#14181b"/>
                    <g stroke="#fff" stroke-opacity=".05"><path d="M0 100h400M0 160h400M0 220h400"/></g>
                    <rect x="46" y="62" width="130" height="64" rx="2" fill="#1d2226" stroke="#fff" stroke-opacity=".12"/>
                    <g fill="#ffd400" fill-opacity=".85"><path d="M60 76h46v5H60Zm0 12h30v5H60Zm0 12h40v5H60Z"/><circle cx="124" cy="78" r="8"/><circle cx="124" cy="78" r="3.5" fill="#1d2226"/><path d="M142 70v40" stroke="#ffd400" stroke-width="5" stroke-linecap="round"/><circle cx="142" cy="112" r="6"/><path d="M154 72l10 36" stroke="#fff" stroke-opacity=".6" stroke-width="5" stroke-linecap="round"/></g>
                    <g fill="#8a939a"><circle cx="108" cy="104" r="4"/><circle cx="92" cy="110" r="4"/><circle cx="76" cy="104" r="4"/></g>
                    <rect x="290" y="70" width="64" height="44" fill="#0e1012" stroke="#ffd400" stroke-opacity=".5"/>
                    <path d="M298 100l10-12 8 8 10-18 8 12 12-14" stroke="#3ddc84" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <g class="av-sway"><path d="M200 40v26" stroke="#5b646c" stroke-width="3"/><path d="M178 80l10-18h24l10 18Z" fill="#2a2f34"/><path d="M200 80 130 262h140Z" fill="url(#av-cone)" class="av-glow"/><ellipse cx="200" cy="81" rx="16" ry="3" fill="#ffd400" class="av-glow"/></g>
                    <rect x="0" y="262" width="400" height="58" fill="url(#av-floor)"/>
                    <path d="M0 262h400" stroke="#ffd400" stroke-width="4"/>
                    <path d="M0 284h400" stroke="#fff" stroke-opacity=".14" stroke-width="3" stroke-dasharray="26 16"/>
                    <path d="M20 270 380 270M8 306 392 306" stroke="#ffd400" stroke-opacity=".35" stroke-width="2"/>
                    <path d="M14 262h16l-10 8Zm40 0h16l-10 8Z" fill="#ffd400" opacity=".7"/>
                    <ellipse cx="206" cy="268" rx="170" ry="9" fill="#000" opacity=".45"/>
                    <rect x="22" y="42" width="20" height="220" fill="url(#av-post)"/><rect x="358" y="42" width="20" height="220" fill="url(#av-post)"/>
                    <rect x="22" y="42" width="356" height="10" fill="#3a4147"/><path d="M22 247h20v15H22Zm336 0h20v15h-20Z" fill="#ffd400"/><path d="M26 262l8-15m4 15 8-15M362 262l8-15m4 15 8-15" stroke="#0e1012" stroke-width="4"/>
                    <path d="M42 224h110M358 224h-100" stroke="#6a737b" stroke-width="9" stroke-linecap="round"/><path d="M42 220h110M358 220h-100" stroke="#2a2f34" stroke-width="3"/><rect x="144" y="214" width="16" height="6" fill="#ffd400"/><rect x="250" y="214" width="16" height="6" fill="#ffd400"/>
                    <path d="M54 206V196c0-8 6-12 16-14l62-9c14-14 34-24 62-26l60-1c22 2 36 12 50 26l30 6c12 3 20 10 20 20v8Z" fill="url(#av-body)" transform="translate(-4 4) scale(1)"/>
                    <path d="M138 173c14-12 30-20 56-22h38v22Z" fill="url(#av-glass)" transform="translate(-4 4)"/><path d="M240 151h20c18 2 30 12 44 22h-64Z" fill="url(#av-glass)" transform="translate(-4 4)"/>
                    <path d="M152 164c10-8 22-12 36-13" stroke="#fff" stroke-opacity=".35" stroke-width="3" stroke-linecap="round"/>
                    <path d="M236 151v44" stroke="#9a7b00" stroke-width="2.5"/><rect x="245" y="180" width="16" height="4" rx="2" fill="#9a7b00"/>
                    <path d="M66 188c40-8 150-18 280-8" stroke="#fff" stroke-opacity=".55" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M150 204h112v10H150Z" fill="#14181b"/>
                    <path d="M338 182h22c4 0 6 3 6 6v5h-28Z" fill="#fff" transform="translate(-4 4)"/><path d="M352 186h12" stroke="#ffd400" stroke-width="3"/>
                    <rect x="50" y="188" width="9" height="14" rx="2" fill="#ff3b30"/>
                    <circle cx="116" cy="212" r="34" fill="#0e1012"/><circle cx="292" cy="212" r="34" fill="#0e1012"/>
                    <g><circle cx="116" cy="212" r="29" fill="#0a0b0c"/><circle cx="116" cy="212" r="19" fill="url(#av-rim)"/><circle cx="116" cy="212" r="5" fill="#ffd400"/><path d="M116 194v36M98 212h36M103 199l26 26M129 199l-26 26" stroke="#0a0b0c" stroke-opacity=".45" stroke-width="3"/></g>
                    <g><circle cx="292" cy="212" r="29" fill="#0a0b0c"/><circle cx="292" cy="212" r="19" fill="url(#av-rim)"/><circle cx="292" cy="212" r="5" fill="#ffd400"/><path d="M292 194v36M274 212h36M279 199l26 26M305 199l-26 26" stroke="#0a0b0c" stroke-opacity=".45" stroke-width="3"/></g>
                </svg>
                <p class="absolute left-4 top-4 bg-black/70 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-[#ffd400]">Подемник 2 · диагностика</p>
            </x-demo-photo>
            <div class="av-float mt-3 grid grid-cols-3 gap-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-300">
                @foreach(['Оригинални части', 'Кола под наем', 'Плащане с карта'] as $perk)<p class="border border-white/10 px-2 py-3">{{ $perk }}</p>@endforeach
            </div>
        </div>
    </div>
</section>

{{-- Brands --}}
<div class="border-y border-white/10 bg-[#15181b]">
    <div class="demo-scroll mx-auto flex max-w-7xl items-center gap-10 px-4 py-5 text-sm font-extrabold uppercase tracking-[0.2em] text-gray-400">
        <span class="shrink-0 text-[#ffd400]">Работим с</span>
        @foreach(['VW', 'Audi', 'Škoda', 'BMW', 'Mercedes', 'Toyota', 'Renault', 'Dacia', 'Ford', 'Opel', 'Peugeot', 'Kia'] as $brand)<span class="shrink-0">{{ $brand }}</span>@endforeach
    </div>
</div>

{{-- Estimator --}}
<section id="ceni" class="dr scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <p class="text-sm font-semibold uppercase tracking-widest text-[#ffd400]">Калкулатор</p>
        <h2 class="mt-3 max-w-3xl text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Вижте колко ще струва, още сега</h2>
        <div class="mt-10 grid gap-6 lg:grid-cols-12" data-estimator>
            <div class="bg-[#15181b] p-6 md:p-8 lg:col-span-8">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">1. Каква е колата</p>
                <div data-pick="car" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach([['Малка', '1'], ['Средна', '1.15'], ['Джип', '1.3'], ['Бус', '1.45']] as $i => [$car, $factor])
                        <button type="button" data-value="{{ $car }}" data-factor="{{ $factor }}" aria-pressed="{{ $i === 1 ? 'true' : 'false' }}" class="border border-white/15 py-3 text-sm font-semibold uppercase tracking-wide hover:border-[#ffd400] aria-pressed:border-[#ffd400] aria-pressed:bg-[#ffd400] aria-pressed:text-black">{{ $car }}</button>
                    @endforeach
                </div>
                <p class="mt-8 text-xs font-semibold uppercase tracking-widest text-gray-400">2. Какво трябва да се направи</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach($jobs as $i => [$job, $price, $time])
                        <label class="flex cursor-pointer items-center gap-3 border border-white/15 p-4 hover:border-[#ffd400] has-[:checked]:border-[#ffd400] has-[:checked]:bg-[#ffd400]/10">
                            <input type="checkbox" data-job="{{ $price }}" @checked($i < 2) class="h-5 w-5 rounded-none border-white/40 bg-transparent text-[#ffd400] focus:ring-[#ffd400]">
                            <span class="flex-1"><span class="block font-semibold">{{ $job }}</span><span class="text-xs font-light text-gray-400">{{ $time }}</span></span>
                            <span class="text-sm font-semibold text-[#ffd400]">от {{ $price }} €</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <aside id="chas" class="scroll-mt-24 flex flex-col bg-[#ffd400] p-6 text-black md:p-8 lg:col-span-4">
                <p class="text-xs font-extrabold uppercase tracking-widest">Ориентировъчна цена с труда</p>
                <p class="mt-3 text-6xl font-extrabold tracking-tight"><span data-total>85</span> €</p>
                <p class="mt-2 text-sm"><span data-count>2</span> услуги за кола <span data-pick-out="car" class="lowercase">средна</span>. Частите са по избор, оригинални или алтернативни.</p>
                <form data-demo-form class="mt-auto space-y-3 pt-8">
                    <input type="text" required placeholder="Марка, модел и година" class="w-full border-2 border-black bg-transparent px-4 py-3 placeholder-black/60 focus:border-black focus:ring-0">
                    <input type="tel" required placeholder="Телефон" class="w-full border-2 border-black bg-transparent px-4 py-3 placeholder-black/60 focus:border-black focus:ring-0">
                    <button class="w-full bg-black py-4 font-extrabold uppercase tracking-wide text-[#ffd400] hover:bg-[#23272b]">Запази час с тази цена</button>
                    <p class="text-xs">Връщаме обаждане до 20 минути и потвърждаваме точната сума.</p>
                </form>
            </aside>
        </div>
    </div>
</section>

{{-- Services --}}
<section id="uslugi" class="scroll-mt-16 bg-[#15181b] px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Какво ремонтираме</h2>
        <div class="dr mt-12 grid gap-px bg-white/10 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($services as $i => [$name, $text, $icon])
                <article class="group bg-[#15181b] p-8 transition hover:bg-[#ffd400] hover:text-black">
                    <p class="flex items-center justify-between text-3xl text-[#ffd400] group-hover:text-black"><x-icon :name="$icon" /><span class="text-sm font-extrabold text-white/25 group-hover:text-black/40">0{{ $i + 1 }}</span></p>
                    <h3 class="mt-6 text-xl font-extrabold uppercase">{{ $name }}</h3>
                    <p class="mt-2 font-light leading-relaxed text-gray-400 group-hover:text-black/80">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Process --}}
<section id="kak" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="max-w-2xl text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Без изненади от началото до ключа</h2>
        <ol class="dr mt-12 grid gap-6 md:grid-cols-4">
            @foreach([
                ['Запазвате час', 'По телефона или от сайта. Казваме точен ден и час за приемане.'],
                ['Диагностика', 'За 30 минути знаем какво е. Пращаме ви снимки на проблема.'],
                ['Писмена оферта', 'Получавате цена за труда и частите. Започваме след вашето „да“.'],
                ['Взимате колата', 'С протокол, старите части и 12 месеца гаранция.'],
            ] as $i => [$title, $text])
                <li class="av-lift relative border-l-4 border-[#ffd400] border-y border-r border-y-transparent border-r-transparent bg-[#15181b] p-6">
                    <span class="text-sm font-extrabold uppercase tracking-widest text-[#ffd400]">Стъпка {{ $i + 1 }}</span>
                    <h3 class="mt-3 text-xl font-extrabold uppercase">{{ $title }}</h3>
                    <p class="mt-2 font-light leading-relaxed text-gray-400">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach([
                ['Обадих се в 9, в 11 колата беше на подемника, в 12 имах оферта със снимки. Платих точно колкото пишеше.', 'Красимир Й.', 'Škoda Octavia'],
                ['Друг сервиз ми искаше смяна на турбото. Тук намериха спукан маркуч за 30 евро.', 'Теодора М.', 'VW Golf'],
                ['Дадоха ми кола под наем за двата дни ремонт. Така се работи.', 'Пламен Г.', 'Ford Transit'],
            ] as [$quote, $who, $car])
                <figure class="av-lift border border-white/10 p-6">
                    <p class="text-[#ffd400]" aria-label="5 от 5 звезди">★★★★★</p>
                    <blockquote class="mt-3 font-light leading-relaxed text-gray-200">„{{ $quote }}“</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold">{{ $who }} <span class="font-light text-gray-400">· {{ $car }}</span></figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Equipment and standards --}}
<section class="dr px-4 pb-20">
    <div class="mx-auto max-w-7xl">
        <p class="text-sm font-semibold uppercase tracking-widest text-[#ffd400]">Защо ни се доверяват</p>
        <div class="mt-6 grid gap-px bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['3D стенд за геометрия', 'Точност до стотна от градуса, с разпечатка преди и след.', 'gauge-high'],
                ['Оригинален софтуер', 'Диагностика с производителски програми за всички основни марки.', 'cogs'],
                ['Обучени техници', 'Годишно обучение за хибридни и електрически системи.', 'helmet-safety'],
                ['Писмена гаранция', '12 месеца за труда и частите, с протокол при приемане.', 'shield-halved'],
            ] as [$t, $d, $ic])
                <div class="av-lift flex gap-4 bg-[#15181b] p-6">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center bg-[#ffd400] text-xl text-black"><x-icon :name="$ic" /></span>
                    <div><h3 class="font-extrabold uppercase">{{ $t }}</h3><p class="mt-1 text-sm font-light leading-relaxed text-gray-400">{{ $d }}</p></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="dr scroll-mt-16 bg-[#ffd400] px-4 py-16 text-black">
    <div class="mx-auto grid max-w-7xl items-center gap-10 md:grid-cols-2">
        <div>
            <h2 class="text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Колата не пали? Звъннете.</h2>
            <p class="mt-4 text-lg">Пътна помощ в Русе и областта до 40 минути.</p>
            <a href="tel:+35982000000" data-demo-action class="mt-6 inline-flex items-center gap-3 bg-black px-8 py-4 text-2xl font-extrabold text-[#ffd400]"><x-icon name="phone-volume" /> 082 000 000</a>
        </div>
        <dl class="grid gap-px bg-black/20 sm:grid-cols-2">
            @foreach([['Адрес', 'бул. „Липник“ 123, Русе'], ['Понеделник до петък', '8:00 до 18:30'], ['Събота', '9:00 до 14:00'], ['Пътна помощ', 'Всеки ден до 22:00']] as [$k, $v])
                <div class="bg-[#ffd400] p-5"><dt class="text-xs font-extrabold uppercase tracking-widest">{{ $k }}</dt><dd class="mt-1 text-lg font-semibold">{{ $v }}</dd></div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Phone bar --}}
<div class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-2 md:hidden">
    <a href="tel:+35982000000" data-demo-action class="flex items-center justify-center gap-2 bg-[#ffd400] py-4 font-extrabold uppercase text-black"><x-icon name="phone" /> Обади се</a>
    <a href="#ceni" class="flex items-center justify-center gap-2 bg-white py-4 font-extrabold uppercase text-black"><x-icon name="calculator" /> Цена</a>
</div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var menu = document.querySelector('details[data-menu]');
        if (menu) menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { menu.open = false; }); });
        var box = document.querySelector('[data-estimator]'), factor = 1.15;
        function draw() {
            var sum = 0, count = 0;
            box.querySelectorAll('[data-job]').forEach(function (job) { if (job.checked) { sum += Number(job.dataset.job); count++; } });
            box.querySelector('[data-total]').textContent = Math.round(sum * factor / 5) * 5;
            box.querySelector('[data-count]').textContent = count;
        }
        box.querySelectorAll('[data-job]').forEach(function (job) { job.addEventListener('change', draw); });
        box.querySelector('[data-pick="car"]').addEventListener('pick', function (e) { factor = Number(e.target.querySelector('[aria-pressed="true"]').dataset.factor); draw(); });
        draw();
    })();
</script>
@endpush
