@extends('demos._layout')
@section('body_class', 'bg-[#f7f4ee] text-[#131c2b]')

@php
    $areas = [
        'grazhdani' => ['За граждани', [
            ['Имоти и вещно право', 'Покупка и продажба, проверка на тежести, делби и спорове за собственост.'],
            ['Семейно право', 'Развод, издръжка, родителски права и брачни договори.'],
            ['Наследство', 'Завещания, откази, делба на наследство и запазена част.'],
            ['Трудови спорове', 'Незаконно уволнение, неизплатени заплати и обезщетения.'],
            ['Обезщетения', 'Пътни произшествия, трудови злополуки и застрахователни спорове.'],
            ['Защита по наказателни дела', 'От първия разпит до последната инстанция.'],
        ]],
        'biznes' => ['За бизнеса', [
            ['Търговски договори', 'Изготвяне и преглед на договори, общи условия и споразумения.'],
            ['Регистрация и промени', 'Учредяване на дружества, прехвърляне на дялове и вписвания.'],
            ['Събиране на вземания', 'Заповедни производства, обезпечения и изпълнителни дела.'],
            ['Трудово право за работодатели', 'Договори, вътрешни правила и прекратяване без риск.'],
            ['Защита на лични данни', 'Документи и процедури по регламента за личните данни.'],
            ['Абонаментно обслужване', 'Адвокат на разположение срещу фиксирана месечна сума.'],
        ]],
    ];
    $cases = [
        ['Купувам или продавам имот', 'Проверяваме собствеността и тежестите, подготвяме предварителния договор и сме с вас при нотариуса.', 'от 350 €', '5 до 10 дни'],
        ['Развеждам се', 'Обясняваме двата пътя, по взаимно съгласие и по исков ред, и какво следва за децата и имуществото.', 'от 450 €', '2 до 6 месеца'],
        ['Уволниха ме', 'Преглеждаме заповедта още на първата среща. Срокът за иск е два месеца, затова не чакайте.', 'от 300 €', '4 до 8 месеца'],
        ['Дължат ми пари', 'Избираме най-бързия път. При ясни документи заповед за изпълнение излиза за седмици.', 'от 200 €', '1 до 3 месеца'],
        ['Наследих имот', 'Изясняваме кой какъв дял има и как се стига до делба, доброволна или съдебна.', 'от 300 €', '1 до 12 месеца'],
    ];
    $team = [['адв. Радослав Велчев', 'Управляващ съдружник', 'Имоти и търговско право · 21 години стаж'], ['адв. Цветелина Барова', 'Съдружник', 'Семейно и наследствено право · 16 години стаж'], ['адв. Димитър Сираков', 'Адвокат', 'Трудово право и вземания · 9 години стаж']];
@endphp

@push('head')
<style>
    .ak-nav { position: relative; }
    .ak-nav::after { content: ''; position: absolute; left: 0; right: 0; bottom: -6px; height: 1px; background: #b08d57; transform: scaleX(0); transform-origin: left; transition: transform .35s ease; }
    .ak-nav:hover::after { transform: scaleX(1); }
    @media (prefers-reduced-motion: no-preference) {
        @keyframes ak-draw { from { stroke-dashoffset: 1; } to { stroke-dashoffset: 0; } }
        @keyframes ak-glow { 0%, 100% { opacity: .55; } 50% { opacity: 1; } }
        .ak-draw path, .ak-draw rect { stroke-dasharray: 1; stroke-dashoffset: 0; animation: ak-draw 2.4s ease-out both; }
        .ak-glow { animation: ak-glow 6s ease-in-out infinite; }
        .ak-lift { transition: transform .35s ease, box-shadow .35s ease; }
        .ak-lift:hover { transform: translateY(-4px); box-shadow: 0 22px 40px -26px rgba(19, 28, 43, .55); }
        .ak-tab { transition: background-color .2s ease, color .2s ease, border-color .2s ease; }
    }
</style>
@endpush

@section('content')
<header class="sticky top-0 z-50 border-b border-[#e3ddd0] bg-[#f7f4ee]/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="demo-serif text-2xl tracking-wide">Велчев <span class="text-[#b08d57]">&amp;</span> Барова <span class="ml-2 hidden align-middle text-[10px] font-sans font-bold uppercase tracking-[0.25em] text-[#6b7383] sm:inline">Адвокатско дружество</span></a>
        <div class="hidden items-center gap-8 text-sm font-semibold lg:flex">
            <a href="#oblasti" class="ak-nav hover:text-[#8a6a35]">Области</a><a href="#hononari" class="ak-nav hover:text-[#8a6a35]">Хонорари</a><a href="#ekip" class="ak-nav hover:text-[#8a6a35]">Адвокати</a><a href="#vaprosi" class="ak-nav hover:text-[#8a6a35]">Въпроси</a>
        </div>
        <span class="flex items-center gap-3">
            <a href="#konsultaciya" class="hidden bg-[#131c2b] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#b08d57] sm:inline-block">Запазете консултация</a>
            <button type="button" data-menu-button aria-expanded="false" aria-controls="ak-menu" aria-label="Меню" class="flex h-10 w-10 items-center justify-center border border-[#131c2b] text-[#131c2b] lg:hidden"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"><path data-menu-lines d="M4 7h16M4 12h16M4 17h16"/></svg></button>
        </span>
    </nav>
    <div id="ak-menu" hidden class="absolute inset-x-0 top-full border-b border-[#e3ddd0] bg-[#f7f4ee] shadow-xl lg:hidden">
        <div class="mx-auto flex max-w-7xl flex-col px-4 py-3">
            @foreach([['#oblasti', 'Области'], ['#hononari', 'Хонорари'], ['#ekip', 'Адвокати'], ['#vaprosi', 'Въпроси']] as [$href, $label])
                <a href="{{ $href }}" class="demo-serif border-b border-[#e3ddd0] py-3.5 text-xl">{{ $label }}</a>
            @endforeach
            <a href="#konsultaciya" class="my-4 bg-[#131c2b] py-3.5 text-center font-semibold text-white">Запазете консултация</a>
        </div>
    </div>
</header>

{{-- Hero --}}
<section class="demo-grain relative overflow-hidden bg-[#131c2b] text-white">
    <svg class="pointer-events-none absolute inset-y-0 right-0 h-full w-full opacity-[0.32] lg:w-[62%]" viewBox="0 0 600 640" preserveAspectRatio="xMaxYMax slice" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="ak-line" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#e2c691"/><stop offset="1" stop-color="#b08d57" stop-opacity=".25"/></linearGradient>
            <radialGradient id="ak-halo" cx=".5" cy=".42" r=".6"><stop offset="0" stop-color="#b08d57" stop-opacity=".35"/><stop offset="1" stop-color="#b08d57" stop-opacity="0"/></radialGradient>
        </defs>
        <circle class="ak-glow" cx="320" cy="250" r="270" fill="url(#ak-halo)"/>
        <g class="ak-draw" stroke="url(#ak-line)" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round">
            <path pathLength="1" d="M70 232 330 70l260 162Z"/><path pathLength="1" d="M110 222 330 92l220 130"/>
            <circle cx="330" cy="168" r="26"/><circle cx="330" cy="168" r="17" stroke-opacity=".6"/>
            <path pathLength="1" d="M330 150v36M316 160h28M322 186h16" stroke-opacity=".8"/>
            <path pathLength="1" d="M60 232h540v26H60ZM74 258h512v10H74Z"/>
            @foreach(range(0, 5) as $i)
                @php($x = 100 + $i * 90)
                <path pathLength="1" d="M{{ $x }} 268h40M{{ $x - 4 }} 276h48v-8M{{ $x + 6 }} 276v212M{{ $x + 20 }} 276v212M{{ $x + 34 }} 276v212M{{ $x - 4 }} 488h48v12H{{ $x - 4 }}Z"/>
            @endforeach
            <path pathLength="1" d="M70 500h520v14H70ZM50 514h560v14H50ZM30 528h600v14H30Z"/>
            <path pathLength="1" d="M10 560h620" stroke-opacity=".5"/>
        </g>
        <g stroke="#e2c691" stroke-opacity=".5" stroke-width="1"><path d="M330 20v34M313 36h34"/><circle cx="330" cy="36" r="3" fill="#e2c691" class="ak-glow"/></g>
    </svg>
    <div class="relative z-10 mx-auto grid max-w-7xl gap-12 px-4 py-16 lg:grid-cols-12 lg:py-24">
        <div class="lg:col-span-7">
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#c9a977]">Варна · от 2004 година</p>
            <h1 class="demo-serif mt-6 text-5xl leading-[1.06] sm:text-6xl lg:text-7xl">Ясен отговор на <em class="text-[#c9a977]">трудния въпрос</em></h1>
            <p class="mt-7 max-w-xl text-lg leading-relaxed text-[#c5ccd9]">Обясняваме на човешки език какви са шансовете ви, колко ще продължи и колко ще струва. Преди да подпишете каквото и да е.</p>
            <div class="mt-9 flex flex-wrap gap-4">
                <a href="#konsultaciya" class="bg-[#b08d57] px-8 py-4 font-semibold hover:bg-[#c9a977]">Запазете консултация</a>
                <a href="tel:+35952000000" data-demo-action class="inline-flex items-center gap-2 border border-white/30 px-8 py-4 font-semibold hover:bg-white/10"><x-icon name="phone" /> 052 000 000</a>
            </div>
            <dl class="mt-14 grid max-w-xl grid-cols-3 gap-8 border-t border-white/15 pt-8">
                @foreach([['21', 'години практика'], ['2 900', 'приключени дела'], ['24 часа', 'до първи отговор']] as [$n, $t])
                    <div><dd class="demo-serif text-4xl text-[#c9a977]">{{ $n }}</dd><dt class="mt-1 text-sm text-[#9aa3b5]">{{ $t }}</dt></div>
                @endforeach
            </dl>
        </div>

        {{-- Case picker --}}
        <div class="lg:col-span-5" data-group>
            <div class="bg-[#f7f4ee] p-6 text-[#131c2b] shadow-2xl md:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#8a6a35]">С какво можем да помогнем</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($cases as $i => [$case])
                        <button type="button" data-show="case-{{ $i }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="ak-tab border border-[#d6cebd] px-3.5 py-2 text-sm font-semibold hover:border-[#131c2b] aria-pressed:border-[#131c2b] aria-pressed:bg-[#131c2b] aria-pressed:text-white">{{ $case }}</button>
                    @endforeach
                </div>
                @foreach($cases as $i => [$case, $text, $fee, $time])
                    <div data-key="case-{{ $i }}" @if($i > 0) hidden @endif class="mt-6 border-t border-[#e3ddd0] pt-6">
                        <p class="demo-serif text-2xl leading-snug">{{ $case }}</p>
                        <p class="mt-3 leading-relaxed text-[#4a5364]">{{ $text }}</p>
                        <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-white p-4 ring-1 ring-[#e3ddd0]"><dt class="text-[#6b7383]">Хонорар</dt><dd class="demo-serif mt-1 text-2xl">{{ $fee }}</dd></div>
                            <div class="bg-white p-4 ring-1 ring-[#e3ddd0]"><dt class="text-[#6b7383]">Обичаен срок</dt><dd class="demo-serif mt-1 text-2xl">{{ $time }}</dd></div>
                        </dl>
                    </div>
                @endforeach
                <a href="#konsultaciya" class="mt-6 block bg-[#b08d57] py-3.5 text-center font-semibold text-white hover:bg-[#131c2b]">Разкажете ни случая си</a>
            </div>
        </div>
    </div>
</section>

{{-- Standards --}}
<section class="border-b border-[#e3ddd0] bg-white px-4 py-10">
    <ul class="dr mx-auto grid max-w-7xl gap-8 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([['lock', 'Адвокатска тайна', 'Важи от първото обаждане'], ['check-circle', 'Писмен договор', 'Хонорарът е записан предварително'], ['shield-halved', 'Застраховка отговорност', 'Покрива професионалните ни действия'], ['calendar', 'Отговор до 24 часа', 'Всеки работен ден']] as [$ic, $t, $d])
            <li class="flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#b08d57] text-[#8a6a35]"><x-icon :name="$ic" /></span>
                <span><span class="demo-serif block text-lg leading-tight">{{ $t }}</span><span class="text-sm text-[#5b6475]">{{ $d }}</span></span>
            </li>
        @endforeach
    </ul>
</section>

{{-- Practice areas --}}
<section id="oblasti" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl" data-group>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div><p class="text-xs font-bold uppercase tracking-[0.3em] text-[#b08d57]">Области на практика</p><h2 class="demo-serif mt-3 text-4xl md:text-5xl">В какво сме добри</h2></div>
            <div class="inline-flex border border-[#131c2b]">
                @foreach($areas as $key => [$label])
                    <button type="button" data-show="{{ $key }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="px-6 py-2.5 text-sm font-semibold aria-pressed:bg-[#131c2b] aria-pressed:text-white">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        @foreach($areas as $key => [$label, $items])
            <div data-key="{{ $key }}" @if(! $loop->first) hidden @endif class="mt-12 grid gap-px bg-[#e3ddd0] sm:grid-cols-2 lg:grid-cols-3">
                @foreach($items as $i => [$name, $text])
                    <article class="group bg-[#f7f4ee] p-8 transition duration-300 hover:bg-white">
                        <p class="demo-serif text-sm text-[#b08d57]">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</p>
                        <h3 class="demo-serif mt-3 text-2xl">{{ $name }}</h3>
                        <p class="mt-3 leading-relaxed text-[#4a5364]">{{ $text }}</p>
                        <a href="#konsultaciya" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-[#131c2b] opacity-0 transition group-hover:opacity-100">Запазете консултация <x-icon name="arrow-right" class="text-xs" /></a>
                    </article>
                @endforeach
            </div>
        @endforeach
    </div>
</section>

{{-- Fees --}}
<section id="hononari" class="scroll-mt-16 bg-white px-4 py-20">
    <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#b08d57]">Хонорари</p>
            <h2 class="demo-serif mt-3 text-4xl leading-tight md:text-5xl">Знаете сумата, преди да започнем</h2>
            <p class="mt-5 max-w-lg leading-relaxed text-[#4a5364]">Хонорарът се записва в договора за правна помощ и не се променя, освен ако не се промени самата работа. Държавните такси и разноските описваме отделно.</p>
            <ol class="dr mt-10 space-y-6">
                @foreach([['Първа консултация', 'До един час, в кантората или онлайн. Преглеждаме документите и казваме какви са вариантите.'], ['Писмено становище', 'До три работни дни получавате план, срокове и хонорар черно на бяло.'], ['Работа по случая', 'Имате един адвокат за връзка и получавате известие след всяко действие по делото.']] as $i => [$title, $text])
                    <li class="flex gap-5"><span class="demo-serif text-4xl text-[#b08d57]">{{ $i + 1 }}</span><div><h3 class="font-bold">{{ $title }}</h3><p class="mt-1 leading-relaxed text-[#4a5364]">{{ $text }}</p></div></li>
                @endforeach
            </ol>
        </div>
        <div class="dr bg-[#131c2b] p-8 text-white md:p-10">
            <h3 class="demo-serif text-2xl">Ориентировъчни хонорари</h3>
            <dl class="mt-6 divide-y divide-white/15">
                @foreach([['Първа консултация до един час', '60 €'], ['Преглед на договор', 'от 90 €'], ['Сделка с недвижим имот', 'от 350 €'], ['Регистрация на дружество', '220 €'], ['Развод по взаимно съгласие', 'от 450 €'], ['Абонамент за фирми', 'от 250 € на месец']] as [$k, $v])
                    <div class="flex items-baseline justify-between gap-4 py-4"><dt class="text-[#c5ccd9]">{{ $k }}</dt><dd class="demo-serif shrink-0 text-xl text-[#c9a977]">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <p class="mt-6 text-sm text-[#9aa3b5]">Ако ни възложите случая, сумата за първата консултация се приспада от хонорара.</p>
        </div>
    </div>
</section>

{{-- Team --}}
<section id="ekip" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="demo-serif text-4xl md:text-5xl">Адвокатите</h2>
        <div class="dr mt-12 grid gap-8 md:grid-cols-3">
            @foreach($team as $i => [$name, $role, $text])
                <article class="ak-lift">
                    <x-demo-photo :slug="$demo['slug']" name="team-{{ $i + 1 }}" alt="{{ $name }}" class="relative aspect-[4/5] w-full overflow-hidden bg-[linear-gradient(170deg,#d9d2c3,#b9b0a0)]">
                        @if($i === 0)
<svg viewBox="0 0 200 250" class="h-full w-full" fill="none">
  <defs><radialGradient id="pk-0" cx=".5" cy=".4" r=".6"><stop offset="0" stop-color="#f3ede0"/><stop offset="1" stop-color="#f3ede0" stop-opacity="0"/></radialGradient></defs>
  <g opacity=".28" stroke="#fff" stroke-width="2"><path d="M0 40h200M0 84h200"/></g><g fill="#131c2b" opacity=".12"><rect x="10" y="12" width="9" height="28"/><rect x="22" y="18" width="7" height="22"/><rect x="32" y="10" width="10" height="30"/><rect x="160" y="14" width="8" height="26"/><rect x="171" y="8" width="10" height="32"/><rect x="184" y="16" width="7" height="24"/></g><circle cx="100" cy="110" r="90" fill="url(#pk-0)"/>
  <path d="M14 250c0-54 30-92 86-94 56 2 86 40 86 94Z" fill="#131c2b"/><path d="M76 158l24 52 24-52Z" fill="#f7f4ee"/>
  <path d="M76 156 56 206l42 8-10-40ZM124 156l20 50-42 8 10-40Z" fill="#0d1420"/><path d="M95 178h10l5 46-10 10-10-10Z" fill="#b08d57"/><path d="M98 178h4l2 10-4 4-4-4Z" fill="#c9a977"/>
  <path d="M86 132h28v30c-6 8-22 8-28 0Z" fill="#dcae88"/>
  <ellipse cx="68" cy="98" rx="5" ry="9" fill="#dcae88"/><ellipse cx="132" cy="98" rx="5" ry="9" fill="#dcae88"/>
  <path d="M68 92c-6-40 22-54 34-54 22 0 38 18 30 54-2-14-8-24-14-28-18 8-34 10-50 28Z" fill="#7b7f89"/><path d="M70 88c-4-26 10-44 30-48-14 6-24 20-24 44Z" fill="#a8acb6"/>
  <ellipse cx="100" cy="98" rx="32" ry="40" fill="#ecc7a3"/>
  <path d="M68 92c4-18 16-30 32-30 18 0 28 12 32 30-6-10-14-18-32-18S74 82 68 92Z" fill="#7b7f89"/>
  <path d="M80 90q8-5 16 0M104 90q8-5 16 0" stroke="#3b3b40" stroke-width="3" stroke-linecap="round"/><ellipse cx="88" cy="98" rx="3" ry="2.6" fill="#1b1b20"/><ellipse cx="112" cy="98" rx="3" ry="2.6" fill="#1b1b20"/>
  <path d="M100 100v14q-5 3-7 0" stroke="#b78a66" stroke-width="2" stroke-linecap="round"/><path d="M88 124q12 4 24 0" stroke="#8d4f45" stroke-width="2.4" stroke-linecap="round"/>
  <path d="M77 108q2 4 2 8M123 108q-2 4-2 8" stroke="#c99a78" stroke-width="1.6" stroke-linecap="round"/>
</svg>
@elseif($i === 1)
<svg viewBox="0 0 200 250" class="h-full w-full" fill="none">
  <defs><radialGradient id="pk-1" cx=".5" cy=".4" r=".6"><stop offset="0" stop-color="#f3ede0"/><stop offset="1" stop-color="#f3ede0" stop-opacity="0"/></radialGradient></defs>
  <rect x="120" y="18" width="62" height="78" fill="#fff" opacity=".3"/><path d="M130 30h42M130 44h42M130 58h30" stroke="#b08d57" stroke-width="2" opacity=".5"/>
  <circle cx="100" cy="110" r="90" fill="url(#pk-1)"/>
  <path d="M58 90c-12 50-10 92 8 112h68c18-20 20-62 8-112-10-36-74-36-84 0Z" fill="#2a1f1c"/>
  <path d="M16 250c0-50 30-88 84-90 54 2 84 40 84 90Z" fill="#2b3445"/><path d="M78 160l22 46 22-46Z" fill="#f7f4ee"/>
  <path d="M78 158 62 214l38-6-14-46ZM122 158l16 56-38-6 14-46Z" fill="#1f2735"/>
  <circle cx="138" cy="192" r="5" fill="#b08d57"/><circle cx="138" cy="192" r="2" fill="#e2c691"/>
  <path d="M88 132h24v28c-6 8-18 8-24 0Z" fill="#e5b896"/>
  <ellipse cx="70" cy="100" rx="4" ry="8" fill="#e5b896"/><ellipse cx="130" cy="100" rx="4" ry="8" fill="#e5b896"/><circle cx="70" cy="110" r="3" fill="#f7f4ee"/><circle cx="130" cy="110" r="3" fill="#f7f4ee"/>
  <ellipse cx="100" cy="98" rx="30" ry="38" fill="#f1cfb2"/>
  <path d="M68 100c-4-40 20-56 40-54 22 2 28 28 24 54-4-22-12-34-30-36-14 6-28 14-34 36Z" fill="#2a1f1c"/>
  <path d="M82 92q7-4 14 0M104 92q7-4 14 0" stroke="#2a1f1c" stroke-width="2.4" stroke-linecap="round"/><ellipse cx="89" cy="99" rx="3" ry="2.6" fill="#1b1b20"/><ellipse cx="111" cy="99" rx="3" ry="2.6" fill="#1b1b20"/>
  <path d="M100 101v12q-4 3-6 0" stroke="#c99a78" stroke-width="1.8" stroke-linecap="round"/><path d="M89 123q11 4 22 0" stroke="#a5524f" stroke-width="3" stroke-linecap="round"/>
</svg>
@else
<svg viewBox="0 0 200 250" class="h-full w-full" fill="none">
  <defs><radialGradient id="pk-2" cx=".5" cy=".4" r=".6"><stop offset="0" stop-color="#f3ede0"/><stop offset="1" stop-color="#f3ede0" stop-opacity="0"/></radialGradient></defs>
  <rect x="18" y="20" width="56" height="70" fill="#fff" opacity=".3"/><path d="M46 20v70M18 55h56" stroke="#fff" stroke-width="2" opacity=".6"/><circle cx="162" cy="50" r="22" fill="#fff" opacity=".25"/>
  <circle cx="100" cy="110" r="90" fill="url(#pk-2)"/>
  <path d="M18 250c0-52 30-90 82-92 52 2 82 40 82 92Z" fill="#3b4a63"/><path d="M80 158l20 34 20-34 8 6-28 52-28-52Z" fill="#cfdbe9"/>
  <path d="M80 156 56 204l36 10-8-42ZM120 156l24 48-36 10 8-42Z" fill="#2c3a52"/>
  <path d="M88 132h24v28c-6 6-18 6-24 0Z" fill="#c68f69"/>
  <ellipse cx="70" cy="100" rx="4" ry="8" fill="#c68f69"/><ellipse cx="130" cy="100" rx="4" ry="8" fill="#c68f69"/>
  <ellipse cx="100" cy="98" rx="30" ry="37" fill="#d4a07a"/>
  <path d="M70 98c-2-34 12-48 32-48 20 0 30 14 28 48-2-14-8-22-16-26-14 4-30 4-44 26Z" fill="#3a2a22"/>
  <path d="M72 106c2 24 12 40 28 40s26-16 28-40c-4 10-10 14-28 14S76 116 72 106Z" fill="#3a2a22"/><path d="M90 126q10 5 20 0" stroke="#e8c9ae" stroke-width="2.6" stroke-linecap="round"/>
  <path d="M80 91q7-3 14 0M106 91q7-3 14 0" stroke="#3a2a22" stroke-width="2.6" stroke-linecap="round"/><ellipse cx="88" cy="99" rx="3" ry="2.6" fill="#1b1b20"/><ellipse cx="112" cy="99" rx="3" ry="2.6" fill="#1b1b20"/>
  <g stroke="#b08d57" stroke-width="1.8"><rect x="77" y="91" width="22" height="16" rx="6"/><rect x="101" y="91" width="22" height="16" rx="6"/><path d="M99 98h2M77 96l-7-2M123 96l7-2"/></g>
  <path d="M100 101v12q-4 3-6 0" stroke="#a97650" stroke-width="1.8" stroke-linecap="round"/>
</svg>
@endif
                    </x-demo-photo>
                    <h3 class="demo-serif mt-5 text-2xl">{{ $name }}</h3>
                    <p class="text-sm font-bold uppercase tracking-widest text-[#b08d57]">{{ $role }}</p>
                    <p class="mt-2 text-[#4a5364]">{{ $text }}</p>
                </article>
            @endforeach
        </div>
        <div class="dr mt-16 grid gap-6 md:grid-cols-2">
            @foreach([
                ['Купувах първото си жилище и се страхувах от измама. Адвокат Велчев откри възбрана, за която брокерът не знаеше. Спести ми 80 хиляди евро.', 'Клиент по сделка с имот, 2026'],
                ['Преди първата среща знаех колко ще струва всичко. След всяко заседание получавах имейл какво се е случило и какво следва.', 'Клиент по трудов спор, 2025'],
            ] as [$quote, $who])
                <figure class="ak-lift border-l-2 border-[#b08d57] bg-white p-8"><blockquote class="demo-serif text-xl leading-relaxed">„{{ $quote }}“</blockquote><figcaption class="mt-4 text-sm text-[#6b7383]">{{ $who }}</figcaption></figure>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="vaprosi" class="scroll-mt-16 bg-white px-4 py-20">
    <div class="mx-auto max-w-3xl">
        <h2 class="demo-serif text-center text-4xl md:text-5xl">Преди да се обадите</h2>
        <div class="dr mt-10 divide-y divide-[#e3ddd0] border-y border-[#e3ddd0]">
            @foreach([
                ['Какво да нося на първата консултация?', 'Всички документи, свързани със случая, дори да ви се струват маловажни. Договори, писма, съобщения и нотариални актове. Ако нямате нищо, пак елате.'],
                ['Може ли консултацията да е онлайн?', 'Да. Провеждаме срещи с видео връзка и приемаме документи по имейл. Договорът се подписва и с електронен подпис.'],
                ['Остава ли разговорът между нас?', 'Да. Адвокатската тайна е задължение по закон и важи от първото обаждане, дори да не ни възложите случая.'],
                ['Какви са шансовете ми?', 'Казваме го честно на първата среща, след като видим документите. Ако случаят не си струва разходите, ще го чуете от нас.'],
            ] as [$q, $a])
                <details class="group py-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg font-semibold">{{ $q }}<span class="text-[#b08d57] transition group-open:rotate-45"><x-icon name="plus" /></span></summary><p class="mt-3 leading-relaxed text-[#4a5364]">{{ $a }}</p></details>
            @endforeach
        </div>
    </div>
</section>

{{-- Consultation --}}
<section id="konsultaciya" class="scroll-mt-16 bg-[#131c2b] px-4 py-20 text-white">
    <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-2">
        <div>
            <h2 class="demo-serif text-4xl leading-tight md:text-5xl">Разкажете ни накратко. Отговаряме до 24 часа.</h2>
            <p class="mt-5 text-[#c5ccd9]">Не е нужно да знаете как се казва проблемът ви на правен език. Напишете го, както бихте го разказали на приятел.</p>
            <dl class="mt-10 space-y-5 text-[#c5ccd9]">
                <div class="flex gap-4"><dt class="text-[#c9a977]"><x-icon name="location-dot" /></dt><dd>ул. „Шипка“ 10, етаж 3, Варна<br><span class="text-sm text-[#9aa3b5]">Срещу Съдебната палата</span></dd></div>
                <div class="flex gap-4"><dt class="text-[#c9a977]"><x-icon name="calendar" /></dt><dd>Понеделник до петък, 9:00 до 18:00<br><span class="text-sm text-[#9aa3b5]">Срещи след 18 часа по уговорка</span></dd></div>
                <div class="flex gap-4"><dt class="text-[#c9a977]"><x-icon name="lock" /></dt><dd>Всичко, което споделите, е защитено от адвокатска тайна.</dd></div>
            </dl>
        </div>
        <form data-demo-form class="grid gap-4 bg-[#f7f4ee] p-8 text-[#131c2b] md:p-10">
            <div data-pick="how" class="grid grid-cols-3 gap-2">
                @foreach(['В кантората', 'Онлайн', 'По телефона'] as $i => $how)
                    <button type="button" data-value="{{ $how }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="border border-[#d6cebd] py-3 text-sm font-semibold hover:border-[#131c2b] aria-pressed:border-[#131c2b] aria-pressed:bg-[#131c2b] aria-pressed:text-white">{{ $how }}</button>
                @endforeach
            </div>
            <input type="text" required placeholder="Име и фамилия" class="border border-[#d6cebd] bg-white px-4 py-3.5 focus:border-[#131c2b] focus:ring-[#131c2b]">
            <input type="tel" required placeholder="Телефон" class="border border-[#d6cebd] bg-white px-4 py-3.5 focus:border-[#131c2b] focus:ring-[#131c2b]">
            <textarea rows="4" placeholder="Какво се случи, с няколко изречения" class="border border-[#d6cebd] bg-white px-4 py-3.5 focus:border-[#131c2b] focus:ring-[#131c2b]"></textarea>
            <button class="bg-[#b08d57] py-4 font-semibold text-white hover:bg-[#131c2b]">Изпратете запитване</button>
            <p class="text-xs text-[#6b7383]">Консултацията <span data-pick-out="how" class="lowercase">в кантората</span> е 60 € и се приспада от хонорара.</p>
        </form>
    </div>
</section>
<footer class="bg-[#0d1420] px-4 py-8 text-center text-sm text-[#9aa3b5]">Адвокатско дружество „Велчев и Барова“ · Вписано във Варненската адвокатска колегия</footer>
@endsection

@push('scripts')
<script>
    (function () {
        var btn = document.querySelector('[data-menu-button]'), menu = document.getElementById('ak-menu');
        function setMenu(open) { menu.hidden = ! open; btn.setAttribute('aria-expanded', open ? 'true' : 'false'); btn.querySelector('[data-menu-lines]').setAttribute('d', open ? 'M6 6l12 12M18 6L6 18' : 'M4 7h16M4 12h16M4 17h16'); }
        btn.addEventListener('click', function () { setMenu(menu.hidden); });
        menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
    })();
</script>
@endpush
