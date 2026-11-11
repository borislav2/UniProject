@extends('demos._layout')
@section('body_class', 'bg-[#f6efe3] text-[#2a1c14]')

@push('head')
<style>
    .rnav { position: relative; }
    .rnav::after { content: ''; position: absolute; left: 0; right: 0; bottom: -6px; height: 1px; background: #c9a15b; transform: scaleX(0); transform-origin: left; transition: transform .3s ease; }
    .rnav:hover::after, .rnav:focus-visible::after { transform: scaleX(1); }
    .rcard { transition: transform .3s ease, box-shadow .3s ease; }
    .rcard:hover { transform: translateY(-4px); box-shadow: 0 18px 40px -18px rgba(42, 28, 20, .45); }
    .rdish svg { transition: transform .6s ease; }
    .rdish:hover svg { transform: scale(1.06); }
    [data-show] { transition: background-color .25s ease, border-color .25s ease, color .25s ease; }
    @media (prefers-reduced-motion: no-preference) {
        @keyframes rfloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
        @keyframes rflame { 0%, 100% { transform: scale(1, 1); } 35% { transform: scale(.92, 1.08); } 70% { transform: scale(1.05, .95); } }
        @keyframes rfade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @keyframes rsteam { 0% { opacity: 0; transform: translateY(6px); } 40% { opacity: .55; } 100% { opacity: 0; transform: translateY(-14px); } }
        .rf-float { animation: rfloat 6s ease-in-out infinite; }
        .rf-flame { transform-box: fill-box; transform-origin: 50% 100%; animation: rflame 1.8s ease-in-out infinite; }
        .rf-steam { animation: rsteam 4s ease-in-out infinite; }
        .rf-steam.b { animation-delay: 1.3s; }
        [data-key]:not([hidden]) { animation: rfade .45s ease both; }
    }
</style>
@endpush

@php
    $menu = [
        'predyastiya' => ['Предястия', [
            ['Печен пипер с бяло сирене', 'Чушки на жар, отлежало овче сирене, чесново олио', '7,90', 'В'],
            ['Телешки език в масло', 'С гъби манатарки и пресен магданоз', '11,50', ''],
            ['Родопска плата', 'Суджук, пастърма, катък и туршия от нашето мазе', '14,90', ''],
            ['Салата с розови домати', 'Домати от Куртово Конаре, лук, босилек и сирене', '8,40', 'В'],
        ]],
        'osnovni' => ['Основни', [
            ['Агнешко на пещ', 'Осем часа на бавен огън, с дроб сарма и млади картофи', '24,90', 'Препоръка на готвача'],
            ['Патешко магре с вишни', 'Сос от мавруд, пюре от целина', '22,50', ''],
            ['Пъстърва на плоча', 'От Девин, с масло, лимон и бадеми', '17,80', ''],
            ['Гювеч с манатарки', 'Сезонни зеленчуци и яйце от свободни кокошки', '13,90', 'В'],
        ]],
        'skara' => ['На жар', [
            ['Телешки стек рибай', '300 г, зрял 28 дни, с масло от печен чесън', '34,00', ''],
            ['Свински ребра', 'Мариновани в мед и чубрица, 12 часа на ниска температура', '18,90', ''],
            ['Кебапчета по наша рецепта', 'Три броя, с лютеница и картофи на фурна', '11,90', ''],
        ]],
        'deserti' => ['Десерти', [
            ['Биволско кисело мляко с мед', 'С орехи и сладко от смокини', '6,50', 'В'],
            ['Печена тиква с крем', 'Канела, орехова хрупка и ванилов сладолед', '7,20', 'В'],
            ['Шоколадов фондан', 'С малини и солен карамел', '8,90', ''],
        ]],
        'vina' => ['Вина', [
            ['Мавруд резерва', 'Собствено лозе край Брестовица, чаша или бутилка', '6,50 / 32', ''],
            ['Рубин', 'Тракийска низина, плътно и меко', '5,80 / 28', ''],
            ['Мискет', 'Свежо бяло, с аромат на цвят', '5,20 / 25', ''],
        ]],
    ];
    $times = ['18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30'];
    $busy = ['19:30', '20:00'];
@endphp

@section('content')
<div class="demo-serif">
<header class="sticky top-0 z-50 border-b border-white/10 bg-[#17100c]/95 text-[#f6efe3] backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="text-2xl font-semibold tracking-wide">Лозата<span class="text-[#c9a15b]">.</span></a>
        <div class="hidden items-center gap-9 font-sans text-[13px] font-semibold uppercase tracking-[0.18em] md:flex">
            <a href="#menu" class="rnav hover:text-[#c9a15b]">Меню</a><a href="#istoriya" class="rnav hover:text-[#c9a15b]">Историята</a><a href="#sabitiya" class="rnav hover:text-[#c9a15b]">Събития</a><a href="#kontakt" class="rnav hover:text-[#c9a15b]">Контакт</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="#rezervaciya" class="rounded-full bg-[#c9a15b] px-5 py-2 font-sans text-sm font-bold text-[#17100c] transition hover:bg-[#dcb875]">Запази маса</a>
            <button type="button" data-menu-btn aria-expanded="false" aria-controls="mnav" aria-label="Меню" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 md:hidden">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </nav>
    <div id="mnav" hidden class="border-t border-white/10 bg-[#17100c] px-4 pb-6 pt-3 font-sans md:hidden">
        <div class="flex flex-col text-lg font-semibold">
            @foreach([['#menu', 'Меню'], ['#istoriya', 'Историята'], ['#sabitiya', 'Събития'], ['#kontakt', 'Контакт']] as [$href, $label])
                <a href="{{ $href }}" class="border-b border-white/10 py-3.5 hover:text-[#c9a15b]">{{ $label }}</a>
            @endforeach
        </div>
        <a href="#rezervaciya" class="mt-5 block rounded-full bg-[#c9a15b] py-3 text-center font-bold text-[#17100c]">Запази маса</a>
    </div>
</header>

{{-- Hero --}}
<section class="demo-grain relative overflow-hidden bg-[#17100c] text-[#f6efe3]">
    <div class="absolute inset-0 bg-[radial-gradient(70%_60%_at_78%_30%,#5b1a24_0%,transparent_60%),radial-gradient(50%_50%_at_10%_90%,#3a2a14_0%,transparent_70%)]"></div>
    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 pb-28 pt-14 md:grid-cols-12 md:pt-20">
        <div class="md:col-span-7">
            <p class="font-sans text-xs font-bold uppercase tracking-[0.3em] text-[#c9a15b]">Пловдив · Старият град · от 1998</p>
            <h1 class="mt-6 text-5xl font-medium leading-[1.02] sm:text-6xl lg:text-7xl">Тракийска кухня,<br><em class="text-[#c9a15b]">готвена на жив огън</em></h1>
            <p class="mt-7 max-w-lg font-sans text-lg leading-relaxed text-[#d8cbb8]">Агнешко от Родопите, зеленчуци от градините край Марица и вино от нашето лозе. Менюто се сменя със сезона.</p>
            <div class="mt-9 flex flex-wrap items-center gap-4 font-sans">
                <a href="#rezervaciya" class="rounded-full bg-[#c9a15b] px-8 py-3.5 font-bold text-[#17100c] hover:bg-[#dcb875]">Запази маса</a>
                <a href="#menu" class="rounded-full border border-white/30 px-8 py-3.5 font-semibold hover:bg-white/10">Виж менюто</a>
            </div>
            <dl class="mt-12 grid max-w-lg grid-cols-3 gap-6 border-t border-white/15 pt-6 font-sans">
                <div><dt class="text-xs uppercase tracking-widest text-[#a8977f]">Оценка</dt><dd class="mt-1 text-2xl font-bold">4,9 <span class="text-sm font-medium text-[#a8977f]">от 5</span></dd></div>
                <div><dt class="text-xs uppercase tracking-widest text-[#a8977f]">Отзиви</dt><dd class="mt-1 text-2xl font-bold">1 240</dd></div>
                <div><dt class="text-xs uppercase tracking-widest text-[#a8977f]">Днес</dt><dd class="mt-1 text-2xl font-bold">12 до 23</dd></div>
            </dl>
        </div>
        <div class="relative md:col-span-5">
            <x-demo-photo :slug="$demo['slug']" name="hero" alt="Маса в ресторант Лозата" class="relative mx-auto aspect-[4/5] w-full max-w-sm overflow-hidden rounded-t-full border border-[#c9a15b]/40 bg-[linear-gradient(170deg,#6b1f2b_0%,#3a1218_55%,#1d0d0c_100%)]">
                <svg viewBox="0 0 320 400" class="absolute inset-0 h-full w-full" fill="none">
                    <defs>
                        <radialGradient id="rh-glow" cx="50%" cy="38%" r="60%"><stop stop-color="#f0a850" stop-opacity=".6"/><stop offset="1" stop-color="#f0a850" stop-opacity="0"/></radialGradient>
                        <linearGradient id="rh-cloth" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#efe4d0"/><stop offset="1" stop-color="#c2ad8b"/></linearGradient>
                        <radialGradient id="rh-plate" cx="40%" cy="30%" r="80%"><stop stop-color="#fffaf0"/><stop offset="1" stop-color="#dccdb5"/></radialGradient>
                        <linearGradient id="rh-wine" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#b0334a"/><stop offset="1" stop-color="#4a0f1a"/></linearGradient>
                        <linearGradient id="rh-gold" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#ecd29a"/><stop offset="1" stop-color="#a77c36"/></linearGradient>
                        <linearGradient id="rh-meat" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#c47a45"/><stop offset="1" stop-color="#7a3f20"/></linearGradient>
                        <radialGradient id="rh-fl" cx="50%" cy="70%" r="60%"><stop stop-color="#fff6cf"/><stop offset="1" stop-color="#f08a2a"/></radialGradient>
                        <filter id="rh-blur"><feGaussianBlur stdDeviation="6"/></filter>
                    </defs>
                    <rect width="320" height="400" fill="url(#rh-glow)"/>
                    <path d="M40 240V130a120 120 0 0 1 240 0v110" stroke="#c9a15b" stroke-opacity=".35"/><path d="M62 240V138a98 98 0 0 1 196 0v102" stroke="#c9a15b" stroke-opacity=".2"/>
                    <path d="M160 0v44" stroke="#c9a15b" stroke-opacity=".7"/><path d="M138 70c2-18 10-26 22-26s20 8 22 26Z" fill="url(#rh-gold)"/><path d="M146 72h28l24 70h-76Z" fill="#f0a850" opacity=".08"/><ellipse cx="160" cy="72" rx="22" ry="3" fill="#fff3c4"/>
                    <rect x="64" y="176" width="192" height="4" fill="#c9a15b" opacity=".55"/>
                    @foreach([78, 104, 130, 190, 216, 242] as $i => $x)
                        <g><rect x="{{ $x - 8 }}" y="{{ 138 - ($i % 2) * 6 }}" width="16" height="{{ 38 + ($i % 2) * 6 }}" rx="3" fill="#2b0d12"/><rect x="{{ $x - 3 }}" y="{{ 118 - ($i % 2) * 6 }}" width="6" height="24" fill="#2b0d12"/><rect x="{{ $x - 5 }}" y="{{ 150 - ($i % 2) * 6 }}" width="10" height="14" fill="#f6efe3" opacity=".75"/><path d="M{{ $x - 5 }} {{ 144 - ($i % 2) * 6 }}v26" stroke="#fff" stroke-opacity=".18" stroke-width="2"/></g>
                    @endforeach
                    <g transform="translate(0 -44)"><rect y="232" width="320" height="230" fill="url(#rh-cloth)"/><path d="M0 232h320" stroke="#fff" stroke-opacity=".6"/><path d="M0 380c80-8 160-8 320 0" stroke="#8a7658" stroke-opacity=".3"/>
                    <ellipse cx="164" cy="314" rx="112" ry="36" fill="#2a1c14" opacity=".3" filter="url(#rh-blur)"/>
                    <ellipse cx="160" cy="302" rx="110" ry="40" fill="url(#rh-plate)"/><ellipse cx="160" cy="302" rx="84" ry="28" fill="#fbf5e8" stroke="#c9a15b" stroke-width="1.5"/>
                    <path d="M112 306c14-16 44-20 80-12 18 4 22 14 4 20-30 10-66 8-84-8Z" fill="#5a1520" opacity=".9"/>
                    <path d="M126 298c10-10 38-14 58-8 6 6 0 14-12 18-22 4-40 0-46-10Z" fill="url(#rh-meat)"/><path d="M134 296c14-6 32-6 44-2" stroke="#e2a06a" stroke-width="3" stroke-linecap="round"/>
                    <path d="M122 300 108 286m12 14-14-2m16 8-12 8" stroke="#f6efe3" stroke-width="3.5" stroke-linecap="round"/>
                    <ellipse cx="204" cy="304" rx="12" ry="8" fill="#e0b25a"/><ellipse cx="200" cy="301" rx="6" ry="3" fill="#f6d88c"/><ellipse cx="218" cy="308" rx="9" ry="6" fill="#d4a24a"/>
                    <circle cx="176" cy="288" r="5" fill="#7a1a2b"/><circle cx="188" cy="292" r="4" fill="#7a1a2b"/><circle cx="175" cy="287" r="1.4" fill="#fff" opacity=".6"/>
                    <path d="M150 284c8-8 16-12 28-12m-26 10 6-8m6 10 8-10" stroke="#5d7a3a" stroke-width="2.5" stroke-linecap="round"/><g fill="#7fa35a"><circle cx="158" cy="310" r="2"/><circle cx="166" cy="314" r="2"/><circle cx="148" cy="314" r="1.6"/></g>
                    <path d="M50 262h36c0 28-8 42-18 42s-18-14-18-42Z" transform="translate(-18 0)" fill="#fff" fill-opacity=".14" stroke="#fff" stroke-opacity=".55"/><path d="M34 276h32c-1 18-7 28-16 28s-15-10-16-28Z" fill="url(#rh-wine)"/><path d="M50 304v36" stroke="#fff" stroke-opacity=".6" stroke-width="2"/><ellipse cx="50" cy="342" rx="18" ry="4" fill="#fff" fill-opacity=".5"/><path d="M38 268c0 10 2 20 5 28" stroke="#fff" stroke-opacity=".5" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="268" cy="262" r="30" fill="#f0a850" opacity=".35" filter="url(#rh-blur)"/><rect x="261" y="276" width="14" height="56" rx="2" fill="#f3e6c8"/><rect x="255" y="330" width="26" height="6" rx="3" fill="url(#rh-gold)"/><path d="M268 276v-6" stroke="#2a1c14" stroke-width="1.5"/>
                    <path class="rf-flame" d="M268 270c-7-8-5-14 0-22 5 8 7 14 0 22Z" fill="url(#rh-fl)"/>
                    <path d="M96 370h128" stroke="url(#rh-gold)" stroke-width="3" stroke-linecap="round"/><path d="M110 380h100" stroke="url(#rh-gold)" stroke-width="3" stroke-linecap="round"/>
                    <path class="rf-steam" d="M150 270c-5-6 5-8 0-14" stroke="#fff" stroke-opacity=".5" stroke-width="2" stroke-linecap="round"/><path class="rf-steam b" d="M166 270c-5-6 5-8 0-14" stroke="#fff" stroke-opacity=".5" stroke-width="2" stroke-linecap="round"/></g>
                </svg>
            </x-demo-photo>
            <div class="rf-float absolute -bottom-6 left-0 max-w-[15rem] rounded-2xl bg-[#f6efe3] p-4 font-sans text-[#2a1c14] shadow-2xl sm:-left-6">
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#8c2332]">Тази седмица</p>
                <p class="mt-1 font-semibold leading-snug">Дегустационно меню от пет ястия с вино към всяко</p>
                <p class="mt-1 text-sm text-[#6a5646]">58 € на човек</p>
            </div>
        </div>
    </div>

    {{-- Quick booking bar --}}
    <div id="rezervaciya" class="relative mx-auto -mb-16 max-w-6xl scroll-mt-24 px-4 font-sans">
        <form data-demo-form class="rounded-3xl bg-[#f6efe3] p-5 text-[#2a1c14] shadow-2xl ring-1 ring-black/5 md:p-7">
            <div class="grid gap-5 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-3">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-[#8c2332]">Ден</label>
                    <div data-pick="day" class="mt-2 grid grid-cols-4 gap-1.5">
                        @foreach(['Днес', 'Утре', 'Пет', 'Съб'] as $i => $day)
                            <button type="button" data-value="{{ $day }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-lg border border-[#dccfbb] py-2 text-sm font-semibold aria-pressed:border-[#8c2332] aria-pressed:bg-[#8c2332] aria-pressed:text-white">{{ $day }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="lg:col-span-5">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-[#8c2332]">Час <span class="font-medium normal-case tracking-normal text-[#6f5c49]">(зачеркнатите са заети)</span></label>
                    <div data-pick="time" class="mt-2 grid grid-cols-4 gap-1.5 sm:grid-cols-8 lg:grid-cols-4 xl:grid-cols-8">
                        @foreach($times as $time)
                            <button type="button" data-value="{{ $time }}" @disabled(in_array($time, $busy)) aria-pressed="{{ $time === '19:00' ? 'true' : 'false' }}" class="rounded-lg border border-[#dccfbb] py-2 text-sm font-semibold tabular-nums disabled:text-[#b9aa95] disabled:line-through aria-pressed:border-[#8c2332] aria-pressed:bg-[#8c2332] aria-pressed:text-white">{{ $time }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-[#8c2332]">Гости</label>
                    <div class="mt-2 flex items-center justify-between rounded-lg border border-[#dccfbb] px-2 py-1">
                        <button type="button" data-step="-1" aria-label="По-малко гости" class="h-8 w-8 rounded-md text-lg font-bold hover:bg-[#eadfcd]">−</button>
                        <span data-guests class="font-bold tabular-nums">2</span>
                        <button type="button" data-step="1" aria-label="Повече гости" class="h-8 w-8 rounded-md text-lg font-bold hover:bg-[#eadfcd]">+</button>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <button class="w-full rounded-lg bg-[#17100c] py-3 font-bold text-[#f6efe3] hover:bg-[#8c2332]">Запази</button>
                </div>
            </div>
            <p class="mt-4 text-sm text-[#6a5646]">Маса за <strong data-guests-text>2 гости</strong>, <strong data-pick-out="day">Днес</strong> в <strong data-pick-out="time">19:00</strong>. Потвърждаваме с обаждане до 15 минути. За компании над 10 души звъннете на 032 000 000.</p>
        </form>
    </div>
</section>

{{-- Story --}}
<section id="istoriya" class="scroll-mt-20 px-4 pb-20 pt-36">
    <div class="dr mx-auto grid max-w-6xl items-center gap-14 md:grid-cols-2">
        <div class="grid grid-cols-2 gap-4">
            <x-demo-photo :slug="$demo['slug']" name="story-1" alt="Пещта на дърва" class="aspect-[3/4] w-full rounded-t-full bg-[linear-gradient(160deg,#c9a15b,#8a5a2b)]"><svg viewBox="0 0 120 160" class="h-full w-full" fill="none">
                <defs>
                    <linearGradient id="ro-wall" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#dcb67e"/><stop offset="1" stop-color="#8a5a2b"/></linearGradient>
                    <radialGradient id="ro-glow" cx="50%" cy="72%" r="55%"><stop stop-color="#ffb347" stop-opacity=".75"/><stop offset="1" stop-color="#ffb347" stop-opacity="0"/></radialGradient>
                    <radialGradient id="ro-fire" cx="50%" cy="85%" r="75%"><stop stop-color="#ffe3a0"/><stop offset=".45" stop-color="#e2622c"/><stop offset="1" stop-color="#6b160f"/></radialGradient>
                </defs>
                <rect width="120" height="160" fill="url(#ro-wall)"/>
                <path d="M0 22h120M0 42h120M0 62h120M0 82h120M0 102h120M0 122h120M20 0v22m40 0V0m40 22V0M0 42h0m30 0V22m40 20V22M10 62V42m40 20V42m40 20V42M30 82V62m40 20V62M20 102V82m40 20V82m40 20V82" stroke="#6b4423" stroke-opacity=".3"/>
                <rect width="120" height="160" fill="url(#ro-glow)"/>
                <path d="M16 150V94a44 44 0 0 1 88 0v56Z" fill="#4a3220"/><path d="M16 150V94a44 44 0 0 1 88 0v56Z" stroke="#2a1b10" stroke-width="3"/>
                <path d="M30 150V96a30 30 0 0 1 60 0v54Z" fill="#17100c"/><path d="M34 150V98a26 26 0 0 1 52 0v52Z" fill="url(#ro-fire)"/>
                <path d="M60 148c-18-8-12-26-2-38 2 10 6 12 8 6 10 10 14 26-6 32Z" fill="#f08a2a"/><path d="M60 148c-9-5-6-14 0-22 6 8 9 16 0 22Z" fill="#ffd36b"/><path d="M46 148c-8-4-4-12 0-18 4 6 6 12 0 18Z" fill="#e2622c"/>
                <rect x="36" y="140" width="48" height="8" rx="4" fill="#2a1b10"/><ellipse cx="38" cy="144" rx="3" ry="4" fill="#5a3a22"/><ellipse cx="82" cy="144" rx="3" ry="4" fill="#5a3a22"/>
                <g fill="#ffd36b"><circle cx="40" cy="84" r="1.2"/><circle cx="80" cy="76" r="1"/><circle cx="52" cy="64" r="1.4"/></g>
                <path d="M110 150 96 52" stroke="#6b4423" stroke-width="3.5" stroke-linecap="round"/><ellipse cx="94" cy="46" rx="9" ry="10" fill="#8a5a2b" transform="rotate(-8 94 46)"/>
                <path d="M8 36h22v14c0 6-22 6-22 0Z" fill="#b8693a"/><path d="M8 36h22" stroke="#f0b97a" stroke-width="2"/>
                <rect y="150" width="120" height="10" fill="#2a1b10"/>
            </svg></x-demo-photo>
            <x-demo-photo :slug="$demo['slug']" name="story-2" alt="Лозето край Брестовица" class="mt-10 aspect-[3/4] w-full rounded-t-full bg-[linear-gradient(160deg,#5d7a3a,#2c3d1c)]"><svg viewBox="0 0 120 160" class="h-full w-full" fill="none">
                <defs>
                    <linearGradient id="rv-sky" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#7a3a4a"/><stop offset=".5" stop-color="#e08a5a"/><stop offset="1" stop-color="#f6c98a"/></linearGradient>
                    <linearGradient id="rv-hill" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#5d7a3a"/><stop offset="1" stop-color="#2c3d1c"/></linearGradient>
                </defs>
                <rect width="120" height="160" fill="url(#rv-sky)"/><circle cx="84" cy="62" r="15" fill="#fff3c4" opacity=".9"/><circle cx="84" cy="62" r="24" fill="#fff3c4" opacity=".18"/>
                <path d="M0 88c20-14 36-10 52-4s40 4 68-8v40H0Z" fill="#5a3a52" opacity=".7"/><path d="M0 98c24-10 44-8 64-2s38 2 56-4v30H0Z" fill="#3f5a3a"/>
                <path d="M0 160V104h120v56Z" fill="url(#rv-hill)"/>
                @foreach([-40, -8, 20, 44, 60, 76, 100, 128, 160] as $x)
                    <path d="M60 104 {{ $x }} 160" stroke="#16210f" stroke-width="7" stroke-opacity=".55" stroke-linecap="round"/><path d="M60 104 {{ $x }} 160" stroke="#8fb05a" stroke-width="2.5" stroke-opacity=".7" stroke-dasharray="3 5" stroke-linecap="round"/>
                @endforeach
                <path d="M60 104 20 160M60 104 100 160" stroke="#c9a15b" stroke-opacity=".25"/>
                <g transform="translate(70 112)"><path d="M0 -8c8-6 18-4 20 2-8 6-16 6-20-2Z" fill="#7fa35a"/><path d="M0 -8c-8-8-18-6-20 0 8 6 16 6 20 0Z" fill="#5d7a3a"/><path d="M0 -8v-10" stroke="#f6efe3" stroke-width="1.5"/>
                    <g fill="#6b1f2b">@foreach([[0, 2], [-7, 4], [7, 4], [-3, 12], [4, 12], [0, 22], [-9, 14], [9, 14]] as [$cx, $cy])<circle cx="{{ $cx }}" cy="{{ $cy }}" r="6"/>@endforeach</g>
                    <g fill="#fff" opacity=".35">@foreach([[-2, 0], [-9, 2], [5, 2], [-5, 10]] as [$cx, $cy])<circle cx="{{ $cx }}" cy="{{ $cy }}" r="1.6"/>@endforeach</g></g>
            </svg></x-demo-photo>
        </div>
        <div>
            <p class="font-sans text-xs font-bold uppercase tracking-[0.3em] text-[#8c2332]">Историята</p>
            <h2 class="mt-4 text-4xl font-medium leading-tight md:text-5xl">Една къща, една пещ и три поколения готвачи</h2>
            <p class="mt-6 font-sans leading-relaxed text-[#5a4636]">Започнахме в двора на възрожденска къща с шест маси и пещ, която дядо Атанас иззида сам. Днес готвим в същата пещ. Месото идва от две ферми в Родопите, които познаваме по име, а хлябът се меси всяка сутрин.</p>
            <ul class="mt-8 grid gap-4 font-sans sm:grid-cols-3">
                @foreach([['28', 'години на същото място'], ['11', 'местни ферми и градини'], ['140', 'етикета в избата']] as [$n, $t])
                    <li class="border-t border-[#d9c9b1] pt-4"><span class="demo-serif block text-4xl text-[#8c2332]">{{ $n }}</span><span class="mt-1 block text-sm text-[#6a5646]">{{ $t }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- Menu --}}
<section id="menu" class="scroll-mt-16 bg-[#17100c] px-4 py-20 text-[#f6efe3]">
    <div class="dr mx-auto max-w-5xl" data-group>
        <div class="text-center">
            <p class="font-sans text-xs font-bold uppercase tracking-[0.3em] text-[#c9a15b]">Есенно меню</p>
            <h2 class="mt-4 text-4xl font-medium md:text-5xl">Какво готвим сега</h2>
        </div>
        <div class="demo-scroll mt-10 flex gap-2 font-sans md:justify-center">
            @foreach($menu as $key => [$label])
                <button type="button" data-show="{{ $key }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="shrink-0 rounded-full border border-white/20 px-5 py-2 text-sm font-semibold hover:border-[#c9a15b] aria-pressed:border-[#c9a15b] aria-pressed:bg-[#c9a15b] aria-pressed:text-[#17100c]">{{ $label }}</button>
            @endforeach
        </div>
        @foreach($menu as $key => [$label, $dishes])
            <ul data-key="{{ $key }}" @if(! $loop->first) hidden @endif class="mt-10 grid gap-x-14 gap-y-7 md:grid-cols-2">
                @foreach($dishes as [$name, $text, $price, $note])
                    <li>
                        <div class="flex items-baseline gap-3">
                            <h3 class="text-xl">{{ $name }}</h3>
                            @if($note === 'В')<span class="rounded-full border border-[#7fa35a] px-2 font-sans text-[10px] font-bold uppercase tracking-wider text-[#a5c97f]" title="Вегетарианско">Вег</span>@endif
                            <span class="mb-1 flex-1 border-b border-dotted border-white/25"></span>
                            <span class="font-sans font-bold tabular-nums text-[#c9a15b]">{{ $price }} €</span>
                        </div>
                        <p class="mt-1 font-sans text-sm text-[#b9aa95]">{{ $text }}</p>
                        @if($note !== '' && $note !== 'В')<p class="mt-1.5 font-sans text-[11px] font-bold uppercase tracking-widest text-[#c9a15b]">{{ $note }}</p>@endif
                    </li>
                @endforeach
            </ul>
        @endforeach
        <p class="mt-12 text-center font-sans text-sm text-[#a8977f]">Имате алергия или специален режим? Кажете на сервитьора. За всяко ястие имаме списък с алергените.</p>
    </div>
</section>


{{-- Dishes strip --}}
<section class="px-4 py-20">
    <div class="mx-auto max-w-6xl">
        <div class="dr flex flex-wrap items-end justify-between gap-4">
            <div><p class="font-sans text-xs font-bold uppercase tracking-[0.3em] text-[#8c2332]">От кухнята</p><h2 class="mt-4 text-4xl font-medium md:text-5xl">Четири ястия, които гостите снимат</h2></div>
            <p class="max-w-xs font-sans text-[#5a4636]">Всяко се сервира на ръчно правена керамика от Троян.</p>
        </div>
        <div class="dr mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach([['Агнешко на пещ', '#3a1218', '#6b1f2b'], ['Пъстърва на плоча', '#1d2b24', '#3d5a48'], ['Кисело мляко с мед', '#3a2a14', '#8a5a2b'], ['Родопска плата', '#2a1c14', '#5a3a22']] as $i => [$title, $c1, $c2])
                <figure class="rdish">
                    <x-demo-photo :slug="$demo['slug']" name="dish-{{ $i + 1 }}" alt="{{ $title }}" class="aspect-square w-full overflow-hidden rounded-3xl" style="background: radial-gradient(circle at 50% 40%, {{ $c2 }}, {{ $c1 }})">
                        <svg viewBox="0 0 200 200" class="h-full w-full" fill="none">
                            <ellipse cx="104" cy="108" rx="78" ry="76" fill="#000" opacity=".3"/><circle cx="100" cy="100" r="78" fill="#f6efe3"/><circle cx="100" cy="100" r="62" fill="#fbf5e8" stroke="#c9a15b" stroke-width="1.5"/><path d="M40 70a70 70 0 0 1 40-30" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".8"/>
                            @if($i === 0)
                                <path d="M62 110c16-26 62-30 80-6-8 24-60 36-80 6Z" fill="#5a1520"/><path d="M70 104c10-18 44-20 56-4-6 14-42 22-56 4Z" fill="#b86a3a"/><path d="M78 100c10-8 28-10 40-4" stroke="#e2a06a" stroke-width="3" stroke-linecap="round"/><path d="M70 106 58 96m14 14-14 2" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
                                <ellipse cx="138" cy="128" rx="13" ry="9" fill="#e0b25a"/><ellipse cx="152" cy="116" rx="10" ry="7" fill="#d4a24a"/><circle cx="108" cy="130" r="5" fill="#7a1a2b"/><path d="M92 82c12-8 26-10 40-6m-30 6 6-8" stroke="#5d7a3a" stroke-width="3" stroke-linecap="round"/>
                            @elseif($i === 1)
                                <path d="M52 104c18-22 56-24 84-4l22-12v34l-22-12c-28 22-66 20-84-4Z" fill="#9fb4a8"/><path d="M60 104c18-14 50-16 72-2-20 12-54 14-72 2Z" fill="#c9d6cc"/><circle cx="68" cy="102" r="2.6" fill="#17100c"/><path d="M84 92c10-4 24-4 34 2M84 112c10 4 24 4 34-2" stroke="#6f8a7a" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="134" cy="134" r="14" fill="#f2cf4a"/><circle cx="134" cy="134" r="10" fill="#f8e58a"/><path d="M134 124v20M124 134h20" stroke="#f2cf4a" stroke-width="1.5"/><g fill="#d9b27a"><ellipse cx="86" cy="130" rx="6" ry="3"/><ellipse cx="100" cy="136" rx="6" ry="3"/><ellipse cx="74" cy="124" rx="5" ry="2.6"/></g><g fill="#7fa35a"><circle cx="110" cy="76" r="3"/><circle cx="120" cy="80" r="2.4"/></g>
                            @elseif($i === 2)
                                <circle cx="100" cy="100" r="44" fill="#fff"/><circle cx="100" cy="100" r="44" stroke="#e6dac7" stroke-width="3"/><path d="M64 96c14 12 28-6 40 6s24-4 34 4" stroke="#e2a73c" stroke-width="5" stroke-linecap="round"/><path d="M104 108c6 10-4 14 0 24" stroke="#e2a73c" stroke-width="4" stroke-linecap="round"/>
                                <path d="M84 84c6-8 18-6 18 2-6 6-14 8-18-2ZM112 80c8-4 16 2 12 10-8 0-14-4-12-10Z" fill="#8a5a2b"/><circle cx="118" cy="116" r="9" fill="#6b1f2b"/><path d="M110 116h16" stroke="#e8a0a8" stroke-width="2"/><circle cx="86" cy="118" r="8" fill="#7a2a3a"/><path d="M80 118h12" stroke="#e8a0a8" stroke-width="2"/>
                            @else
                                <rect x="36" y="56" width="128" height="88" rx="14" fill="#8a5a2b"/><rect x="40" y="60" width="120" height="80" rx="11" fill="#a8743c"/>
                                <path d="M50 70h40l-6 24H50Z" fill="#f2dca0"/><circle cx="62" cy="80" r="2" fill="#d4b060"/><circle cx="74" cy="86" r="2" fill="#d4b060"/>
                                <circle cx="112" cy="80" r="14" fill="#9a2a3a"/><circle cx="112" cy="80" r="10" fill="#c24a5a"/><circle cx="136" cy="92" r="12" fill="#9a2a3a"/><circle cx="136" cy="92" r="8" fill="#c24a5a"/>
                                <g fill="#5a1f6b">@foreach([[60, 114], [72, 108], [66, 124], [80, 120], [90, 112]] as [$cx, $cy])<circle cx="{{ $cx }}" cy="{{ $cy }}" r="7"/>@endforeach</g><path d="M106 110h46l-6 22h-40Z" fill="#e8d4a8"/><path d="M112 118h30" stroke="#c9a15b" stroke-width="2"/>
                            @endif
                        </svg>
                    </x-demo-photo>
                    <figcaption class="mt-3 font-sans text-sm font-semibold text-[#2a1c14]">{{ $title }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Events --}}
<section id="sabitiya" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-6xl">
        <div class="dr max-w-2xl">
            <p class="font-sans text-xs font-bold uppercase tracking-[0.3em] text-[#8c2332]">Повече от вечеря</p>
            <h2 class="mt-4 text-4xl font-medium md:text-5xl">Вечери, за които се говори</h2>
        </div>
        <div class="dr mt-12 grid gap-6 font-sans md:grid-cols-3">
            @foreach([
                ['Четвъртък', 'Вечер на мавруда', 'Пет вина от пет изби, с ястие към всяко. Води нашият сомелиер.', '45 € на човек'],
                ['Всяка събота', 'Жива музика в двора', 'Китара и цигулка от 20 часа. Масите в двора се запазват първи.', 'Без куверт'],
                ['По заявка', 'Частна зала до 24 души', 'Рождени дни, фирмени вечери и годежи с отделно меню.', 'Меню от 38 € на човек'],
            ] as [$when, $title, $text, $price])
                <article class="rcard flex flex-col rounded-3xl border border-[#dccfbb] bg-white/60 p-7">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-[#8c2332]">{{ $when }}</p>
                    <h3 class="demo-serif mt-3 text-2xl">{{ $title }}</h3>
                    <p class="mt-3 flex-1 leading-relaxed text-[#5a4636]">{{ $text }}</p>
                    <p class="mt-5 border-t border-[#e6dac7] pt-4 font-bold">{{ $price }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Reviews --}}
<section class="bg-[#8c2332] px-4 py-20 text-[#f6efe3]">
    <div class="dr mx-auto max-w-6xl">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <h2 class="text-4xl font-medium md:text-5xl">Гостите за нас</h2>
            <p class="font-sans text-sm text-[#f0d9c4]"><span class="text-2xl font-bold text-white">4,9</span> от 5 в Google, от 1 240 отзива</p>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach([
                ['Най-доброто агнешко, което съм ял в Пловдив. Запазих маса от телефона за една минута и ни се обадиха веднага.', 'Георги П.'],
                ['Дворът вечер е приказка. Менюто е кратко, всичко е сезонно и сервитьорите знаят откъде идва всеки продукт.', 'Мария Д.'],
                ['Празнувахме годишнина в частната зала. Помогнаха ни с менюто и виното, не се наложи да мислим за нищо.', 'Ивелина С.'],
            ] as [$quote, $who])
                <figure class="rcard rounded-3xl bg-white/10 p-7 ring-1 ring-white/15">
                    <p class="font-sans text-[#c9a15b]" aria-label="5 от 5 звезди">★★★★★</p>
                    <blockquote class="mt-4 text-xl leading-snug">„{{ $quote }}“</blockquote>
                    <figcaption class="mt-5 font-sans text-sm font-semibold text-[#f0d9c4]">{{ $who }}</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="scroll-mt-16 px-4 py-20">
    <div class="dr mx-auto grid max-w-6xl gap-10 font-sans md:grid-cols-2">
        <div>
            <h2 class="demo-serif text-4xl font-medium md:text-5xl">Заповядайте</h2>
            <p class="mt-5 text-lg text-[#5a4636]">ул. „Съборна“ 14, Старият град, Пловдив</p>
            <p class="mt-1 text-[#5a4636]">Паркинг на 200 метра, при Античния театър.</p>
            <dl class="mt-8 divide-y divide-[#e0d3bf] border-y border-[#e0d3bf]">
                @foreach([['Понеделник', 'Почивен ден'], ['Вторник до четвъртък', '12:00 до 23:00'], ['Петък и събота', '12:00 до 24:00'], ['Неделя', '12:00 до 22:00']] as [$day, $hours])
                    <div class="flex justify-between py-3"><dt>{{ $day }}</dt><dd class="font-bold tabular-nums">{{ $hours }}</dd></div>
                @endforeach
            </dl>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="tel:+35932000000" data-demo-action class="inline-flex items-center gap-2 rounded-full bg-[#17100c] px-6 py-3 font-bold text-[#f6efe3] hover:bg-[#8c2332]"><x-icon name="phone" /> 032 000 000</a>
                <a href="#" data-demo-action class="inline-flex items-center gap-2 rounded-full border border-[#17100c] px-6 py-3 font-semibold hover:bg-[#17100c] hover:text-[#f6efe3]"><x-icon name="location-dot" /> Упътване</a>
            </div>
        </div>
        <x-demo-photo :slug="$demo['slug']" name="map" alt="Карта на Стария град с ресторанта" class="relative min-h-[22rem] overflow-hidden rounded-3xl bg-[#e9dfcd]">
            <svg viewBox="0 0 400 320" class="absolute inset-0 h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none">
                <g stroke="#fff" stroke-width="14" stroke-linecap="round"><path d="M-10 90 150 120 260 60 420 80"/><path d="M150 120 180 340"/><path d="M260 60 300 200 420 230"/><path d="M-10 230 180 250 300 200"/></g>
                <path d="M30 20h70v50H30zM300 250h80v60h-80z" fill="#cfdcc0"/><path d="M190 150h70v50h-70z" fill="#dccfbb"/>
                <circle cx="225" cy="150" r="26" fill="#8c2332" opacity=".18"/><path d="M225 118c-13 0-22 10-22 22 0 16 22 34 22 34s22-18 22-34c0-12-9-22-22-22Z" fill="#8c2332"/><circle cx="225" cy="140" r="8" fill="#f6efe3"/>
            </svg>
            <p class="absolute bottom-4 left-4 rounded-xl bg-white px-4 py-2 text-sm font-semibold shadow">5 минути пеша от Главната</p>
        </x-demo-photo>
    </div>
</section>

<footer class="bg-[#17100c] px-4 py-10 text-center font-sans text-sm text-[#a8977f]">
    <p class="demo-serif text-2xl text-[#f6efe3]">Лозата<span class="text-[#c9a15b]">.</span></p>
    <p class="mt-3">ул. „Съборна“ 14, Пловдив · 032 000 000 · masa@lozata.example</p>
</footer>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var mb = document.querySelector('[data-menu-btn]'), mn = document.getElementById('mnav');
        function closeMenu() { mn.hidden = true; mb.setAttribute('aria-expanded', 'false'); }
        mb.addEventListener('click', function () { var open = mn.hidden; mn.hidden = ! open; mb.setAttribute('aria-expanded', open ? 'true' : 'false'); });
        mn.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeMenu); });
    })();
    (function () {
        var count = 2, out = document.querySelector('[data-guests]'), text = document.querySelector('[data-guests-text]');
        document.querySelectorAll('[data-step]').forEach(function (button) {
            button.addEventListener('click', function () {
                count = Math.min(10, Math.max(1, count + Number(button.dataset.step)));
                out.textContent = count;
                text.textContent = count === 1 ? '1 гост' : count + ' гости';
            });
        });
    })();
</script>
@endpush
