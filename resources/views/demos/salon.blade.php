@extends('demos._layout')
@section('body_class', 'bg-[#faf5f1] text-[#3a2440]')

@php
    $services = [
        'kosa' => ['Коса', [
            ['Дамско подстригване и сешоар', '60 мин', '32'], ['Боядисване с амонячна или безамонячна боя', '120 мин', '58'],
            ['Балеаж', '180 мин', '110'], ['Кератинова терапия', '150 мин', '95'], ['Официална прическа', '75 мин', '45'],
        ]],
        'nokti' => ['Нокти', [
            ['Маникюр с гел лак', '75 мин', '28'], ['Изграждане с гел', '120 мин', '45'], ['Педикюр с гел лак', '80 мин', '36'], ['Сваляне и грижа за нокътя', '30 мин', '10'],
        ]],
        'lice' => ['Лице', [
            ['Дълбоко почистване', '70 мин', '40'], ['Хидратираща терапия с хиалурон', '60 мин', '52'], ['Оформяне и ламиниране на вежди', '45 мин', '26'], ['Миглопластика', '120 мин', '48'],
        ]],
        'tyalo' => ['Тяло', [
            ['Класически масаж на цяло тяло', '60 мин', '42'], ['Антицелулитен масаж', '45 мин', '35'], ['Кола маска на цели крака', '40 мин', '24'],
        ]],
    ];
    $popular = [['Подстригване и сешоар', 32, 60], ['Маникюр с гел лак', 28, 75], ['Балеаж', 110, 180], ['Почистване на лице', 40, 70], ['Масаж', 42, 60]];
    $team = [['Виктория', 'Стилист и колорист', '#b79ad1'], ['Десислава', 'Маникюр и педикюр', '#d9a5a0'], ['Йоана', 'Козметик', '#c8b08a'], ['Ралица', 'Масажист', '#9fb7a5']];
@endphp

@section('content')
<header class="sticky top-0 z-50 border-b border-[#eadfd8] bg-[#faf5f1]/90 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="demo-serif text-2xl italic text-[#4a2b4f]">Лавандула</a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#uslugi" class="lv-link">Услуги и цени</a><a href="#ekip" class="lv-link">Екип</a><a href="#rezultati" class="lv-link">Резултати</a><a href="#vaucheri" class="lv-link">Ваучери</a><a href="#kontakt" class="lv-link">Контакт</a>
        </div>
        <div class="flex items-center gap-2">
            <a href="#chas" class="rounded-full bg-[#4a2b4f] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#6a3f70]">Запази час</a>
            <details data-menu class="md:hidden">
                <summary aria-label="Меню" class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full border border-[#4a2b4f]/25 bg-white/70 text-[#4a2b4f] [&::-webkit-details-marker]:hidden">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path class="lv-b1" d="M4 7h16"/><path class="lv-b2" d="M4 12h16"/><path class="lv-b3" d="M4 17h16"/></svg>
                </summary>
                <div class="absolute inset-x-0 top-16 border-b border-[#eadfd8] bg-[#faf5f1] px-4 pb-6 pt-2 shadow-xl">
                    <div class="flex flex-col divide-y divide-[#eadfd8] text-lg">
                        @foreach([['#uslugi', 'Услуги и цени'], ['#ekip', 'Екип'], ['#rezultati', 'Резултати'], ['#vaucheri', 'Ваучери'], ['#kontakt', 'Контакт']] as [$href, $label])
                            <a href="{{ $href }}" class="demo-serif py-3.5 italic text-[#4a2b4f]">{{ $label }}</a>
                        @endforeach
                    </div>
                    <a href="#chas" class="mt-4 block rounded-full bg-[#4a2b4f] py-3.5 text-center font-semibold text-white">Запази час онлайн</a>
                </div>
            </details>
        </div>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden">
    <div class="absolute -right-40 -top-40 h-[34rem] w-[34rem] rounded-full bg-[#e9dcf3] blur-3xl"></div>
    <div class="absolute -left-32 bottom-0 h-80 w-80 rounded-full bg-[#f6ddd6] blur-3xl"></div>
    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 md:grid-cols-2 md:py-20">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-[#8a5fa8] ring-1 ring-[#eadfd8]"><span class="h-2 w-2 rounded-full bg-[#5aa772]"></span> Отворено до 20:00 · Варна, Гръцката махала</p>
            <h1 class="demo-serif mt-6 text-5xl leading-[1.05] sm:text-6xl lg:text-7xl">Един час <em class="text-[#8a5fa8]">само за теб</em></h1>
            <p class="mt-6 max-w-md text-lg leading-relaxed text-[#6b5670]">Коса, нокти, лице и тяло на едно място. Виждате цените предварително и си запазвате час за по-малко от минута.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#chas" class="rounded-full bg-[#4a2b4f] px-8 py-3.5 font-semibold text-white hover:bg-[#6a3f70]">Запази час онлайн</a>
                <a href="#uslugi" class="rounded-full border border-[#4a2b4f]/30 bg-white/60 px-8 py-3.5 font-semibold hover:border-[#4a2b4f]">Услуги и цени</a>
            </div>
            <div class="mt-10 flex items-center gap-5">
                <div class="flex -space-x-3">
                    @foreach($team as [$name, $role, $color])
                        <span class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-[#faf5f1] font-bold text-white" style="background: {{ $color }}">{{ mb_substr($name, 0, 1) }}</span>
                    @endforeach
                </div>
                <p class="text-sm text-[#6b5670]"><strong class="text-[#3a2440]">4,9 от 5</strong> от 860 отзива в Google<br>Над 3 200 клиентки ни се доверяват</p>
            </div>
        </div>
        <div class="relative">
            <x-demo-photo :slug="$demo['slug']" name="hero" alt="Интериорът на студио Лавандула" class="relative ml-auto aspect-[4/5] w-full max-w-md overflow-hidden rounded-[2.5rem] rounded-tl-[9rem] bg-[linear-gradient(160deg,#d9c6ea_0%,#b79ad1_45%,#7d5a96_100%)]">
                <svg viewBox="0 0 320 400" class="absolute inset-0 h-full w-full" fill="none">
                    <defs>
                        <linearGradient id="sl-h-hair" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#6a3f70"/><stop offset="1" stop-color="#2e1a34"/></linearGradient>
                        <linearGradient id="sl-h-dress" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#faf5f1"/><stop offset="1" stop-color="#e9dcf3"/></linearGradient>
                        <radialGradient id="sl-h-glow" cx=".5" cy=".4" r=".6"><stop offset="0" stop-color="#fff" stop-opacity=".55"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
                        <linearGradient id="sl-h-skin" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f6dccd"/><stop offset="1" stop-color="#e8b9a4"/></linearGradient>
                    </defs>
                    <circle cx="160" cy="170" r="190" fill="url(#sl-h-glow)"/>
                    <path d="M0 400V300c60-30 120-18 160 10s100 30 160-14v104Z" fill="#7d5a96" opacity=".45"/>
                    <path d="M58 400c2-78 40-112 102-112s100 34 102 112Z" fill="url(#sl-h-dress)"/>
                    <path d="M122 290c14 34 24 48 38 48s24-14 38-48c-18 8-58 8-76 0Z" fill="#c9a0b4" opacity=".35"/>
                    <path d="M142 238h36v54c-10 10-26 10-36 0Z" fill="#e3b29c"/>
                    <path d="M84 190c-10-80 22-138 78-138s90 56 78 150c-4 42 8 78 22 108-30 4-52-6-60-30l-8-60H132l-8 60c-8 24-30 34-60 30 14-30 26-66 20-120Z" fill="url(#sl-h-hair)"/>
                    <ellipse cx="160" cy="168" rx="60" ry="76" fill="url(#sl-h-skin)"/>
                    <path d="M100 160c4-62 34-92 66-90 38 2 62 34 60 88-20-6-44-26-54-54-12 30-36 50-72 56Z" fill="url(#sl-h-hair)"/>
                    <path d="M92 118c10-28 36-44 68-44" stroke="#9a78b4" stroke-width="3" stroke-linecap="round" opacity=".6"/>
                    <path d="M126 166c6 6 14 6 20 0m28 0c6 6 14 6 20 0" stroke="#3a2440" stroke-width="3" stroke-linecap="round"/>
                    <path d="M124 160l-6-4m10 6l-4-6m64 4l6-4m-10 6l4-6" stroke="#3a2440" stroke-width="2" stroke-linecap="round"/>
                    <path d="M126 148c8-5 16-5 22-2m26 0c6-3 14-3 22 2" stroke="#4a2b4f" stroke-width="3" stroke-linecap="round"/>
                    <path d="M160 176c-2 12-4 16 2 18" stroke="#c98f7c" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M144 208c10 10 26 10 36 0c-8 3-28 3-36 0Z" fill="#c9566b"/><ellipse cx="128" cy="192" rx="12" ry="7" fill="#f4a39a" opacity=".5"/><ellipse cx="192" cy="192" rx="12" ry="7" fill="#f4a39a" opacity=".5"/>
                    <circle cx="100" cy="190" r="5" fill="#e8c46a"/><path d="M100 195v16" stroke="#e8c46a" stroke-width="2"/><circle cx="100" cy="214" r="4" fill="#e8c46a"/>
                    <g class="lv-spark" fill="#fff"><path d="M268 96l4 12 12 4-12 4-4 12-4-12-12-4 12-4Z"/><path d="M44 250l3 8 8 3-8 3-3 8-3-8-8-3 8-3Z" opacity=".8"/></g>
                    <g stroke="#faf5f1" stroke-width="2" stroke-linecap="round"><path d="M276 372V292m0 22c-10-4-14-12-12-20m12 36c10-4 14-12 12-20m-12 44c-10-4-14-12-12-20"/><path d="M42 140V76m0 18c-8-3-11-9-10-16m10 30c8-3 11-9 10-16"/></g>
                </svg>
            </x-demo-photo>
            <div class="lv-float absolute bottom-8 left-0 rounded-2xl bg-white p-4 shadow-xl ring-1 ring-[#eadfd8] sm:-left-4">
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#8a5fa8]">Най-близък свободен час</p>
                <p class="mt-1 font-bold">Днес в 16:30 при Виктория</p>
            </div>
        </div>
    </div>
</section>

{{-- Booking --}}
<section id="chas" class="dr scroll-mt-20 px-4 pb-20">
    <form data-demo-form data-booking class="mx-auto grid max-w-6xl overflow-hidden rounded-[2rem] bg-white shadow-xl ring-1 ring-[#eadfd8] lg:grid-cols-12">
        <div class="space-y-8 p-6 md:p-10 lg:col-span-8">
            <div>
                <h2 class="demo-serif text-3xl md:text-4xl">Запази час в три стъпки</h2>
                <p class="mt-2 text-[#6b5670]">Без обаждане и без чакане на отговор в съобщения.</p>
            </div>
            <fieldset>
                <legend class="text-sm font-bold"><span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#4a2b4f] text-xs text-white">1</span>Процедура</legend>
                <div data-pick="service" class="mt-3 flex flex-wrap gap-2">
                    @foreach($popular as $i => [$name, $price, $minutes])
                        <button type="button" data-value="{{ $name }}" data-price="{{ $price }}" data-minutes="{{ $minutes }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-full border border-[#e3d6dd] px-4 py-2 text-sm font-semibold hover:border-[#8a5fa8] aria-pressed:border-[#4a2b4f] aria-pressed:bg-[#4a2b4f] aria-pressed:text-white">{{ $name }}</button>
                    @endforeach
                </div>
            </fieldset>
            <fieldset>
                <legend class="text-sm font-bold"><span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#4a2b4f] text-xs text-white">2</span>Специалист</legend>
                <div data-pick="who" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach(array_merge([['Без значение', 'Първият свободен', '#e3d6dd']], array_slice($team, 0, 3)) as $i => [$name, $role, $color])
                        <button type="button" data-value="{{ $name }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="flex items-center gap-3 rounded-2xl border border-[#e3d6dd] p-3 text-left hover:border-[#8a5fa8] aria-pressed:border-[#4a2b4f] aria-pressed:bg-[#f5eef8]">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white" style="background: {{ $color }}">{{ $i === 0 ? '∗' : mb_substr($name, 0, 1) }}</span>
                            <span class="min-w-0"><span class="block truncate text-sm font-bold">{{ $name }}</span><span class="block truncate text-xs text-[#6b5670]">{{ $role }}</span></span>
                        </button>
                    @endforeach
                </div>
            </fieldset>
            <fieldset>
                <legend class="text-sm font-bold"><span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#4a2b4f] text-xs text-white">3</span>Ден и час</legend>
                <div data-pick="day" class="demo-scroll mt-3 flex gap-2">
                    @foreach([['Днес', '12'], ['Чт', '13'], ['Пт', '14'], ['Сб', '15'], ['Пн', '17'], ['Вт', '18'], ['Ср', '19']] as $i => [$d, $n])
                        <button type="button" data-value="{{ $d }} {{ $n }} ноември" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="w-16 shrink-0 rounded-2xl border border-[#e3d6dd] py-2 text-center hover:border-[#8a5fa8] aria-pressed:border-[#4a2b4f] aria-pressed:bg-[#4a2b4f] aria-pressed:text-white"><span class="block text-xs">{{ $d }}</span><span class="block text-lg font-bold">{{ $n }}</span></button>
                    @endforeach
                </div>
                <div data-pick="time" class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-8">
                    @foreach(['10:00', '11:30', '13:00', '14:15', '16:30', '17:45', '18:30', '19:00'] as $time)
                        <button type="button" data-value="{{ $time }}" @disabled(in_array($time, ['11:30', '14:15', '17:45'])) aria-pressed="{{ $time === '16:30' ? 'true' : 'false' }}" class="rounded-xl border border-[#e3d6dd] py-2 text-sm font-semibold tabular-nums hover:border-[#8a5fa8] disabled:border-dashed disabled:text-[#c5b8c2] disabled:line-through aria-pressed:border-[#4a2b4f] aria-pressed:bg-[#4a2b4f] aria-pressed:text-white">{{ $time }}</button>
                    @endforeach
                </div>
            </fieldset>
        </div>
        <aside class="flex flex-col bg-[#4a2b4f] p-6 text-white md:p-10 lg:col-span-4">
            <p class="text-xs font-bold uppercase tracking-widest text-[#d9c6ea]">Вашият час</p>
            <p class="demo-serif mt-4 text-3xl leading-tight" data-pick-out="service">Подстригване и сешоар</p>
            <dl class="mt-6 space-y-3 border-t border-white/20 pt-6 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-[#d9c6ea]">Специалист</dt><dd class="font-semibold" data-pick-out="who">Без значение</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-[#d9c6ea]">Ден</dt><dd class="font-semibold" data-pick-out="day">Днес 12 ноември</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-[#d9c6ea]">Час</dt><dd class="font-semibold" data-pick-out="time">16:30</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-[#d9c6ea]">Времетраене</dt><dd class="font-semibold"><span data-minutes>60</span> мин</dd></div>
            </dl>
            <p class="mt-6 flex items-baseline justify-between border-t border-white/20 pt-6"><span class="text-[#d9c6ea]">Цена</span><span class="demo-serif text-4xl"><span data-price>32</span> €</span></p>
            <input type="tel" placeholder="Телефон за потвърждение" required class="mt-6 w-full rounded-xl border-0 bg-white/10 px-4 py-3 text-white placeholder-[#d9c6ea] ring-1 ring-white/25 focus:ring-2 focus:ring-white">
            <button class="mt-3 w-full rounded-xl bg-white py-3.5 font-bold text-[#4a2b4f] hover:bg-[#f5eef8]">Потвърди часа</button>
            <p class="mt-3 text-xs text-[#d9c6ea]">Плащате в салона. Отказ без такса до 3 часа преди часа.</p>
        </aside>
    </form>
</section>

{{-- Services and prices --}}
<section id="uslugi" class="dr scroll-mt-16 bg-white px-4 py-20">
    <div class="mx-auto max-w-5xl" data-group>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#8a5fa8]">Услуги и цени</p>
                <h2 class="demo-serif mt-3 text-4xl md:text-5xl">Без изненади на касата</h2>
            </div>
            <div class="demo-scroll flex gap-2">
                @foreach($services as $key => [$label])
                    <button type="button" data-show="{{ $key }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="shrink-0 rounded-full border border-[#e3d6dd] px-5 py-2 text-sm font-semibold hover:border-[#8a5fa8] aria-pressed:border-[#4a2b4f] aria-pressed:bg-[#4a2b4f] aria-pressed:text-white">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        @foreach($services as $key => [$label, $rows])
            <ul data-key="{{ $key }}" @if(! $loop->first) hidden @endif class="mt-10 divide-y divide-[#efe5e0] border-y border-[#efe5e0]">
                @foreach($rows as [$name, $time, $price])
                    <li class="lv-row flex items-center justify-between gap-4 px-2 py-4">
                        <div><p class="font-semibold">{{ $name }}</p><p class="text-sm text-[#8a7790]">{{ $time }}</p></div>
                        <div class="flex items-center gap-4"><span class="demo-serif text-2xl">от {{ $price }} €</span><a href="#chas" class="hidden rounded-full border border-[#4a2b4f]/30 px-4 py-1.5 text-sm font-semibold hover:bg-[#4a2b4f] hover:text-white sm:inline-block">Запази</a></div>
                    </li>
                @endforeach
            </ul>
        @endforeach
        <p class="mt-6 text-sm text-[#8a7790]">Цената зависи от дължината и гъстотата на косата. Точната сума казваме преди да започнем.</p>
    </div>
</section>

{{-- Team --}}
<section id="ekip" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-6xl">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#8a5fa8]">Екипът</p>
        <h2 class="demo-serif mt-3 max-w-xl text-4xl md:text-5xl">Четири жени, които обичат работата си</h2>
        <div class="dr mt-12 grid grid-cols-2 gap-5 lg:grid-cols-4">
            @foreach($team as $i => [$name, $role, $color])
                <article class="lv-lift">
                    <x-demo-photo :slug="$demo['slug']" name="team-{{ $i + 1 }}" alt="{{ $name }}, {{ $role }}" class="relative aspect-[3/4] w-full overflow-hidden rounded-[2rem] {{ $i % 2 ? 'rounded-tr-[5rem]' : 'rounded-tl-[5rem]' }}" style="background: linear-gradient(170deg, {{ $color }}, #4a2b4f 140%)">
                        <svg viewBox="0 0 120 160" class="absolute inset-0 h-full w-full" fill="none">
                            @php($skin = ['#f6dccd', '#e8c2aa', '#f3d2bf', '#d9a98d'][$i])
                            @php($hair = ['#3a2440', '#7a4a3a', '#c9a15a', '#1f1a24'][$i])
                            <circle cx="60" cy="150" r="70" fill="#fff" opacity=".14"/>
                            @if($i === 0)<path d="M30 72c0-32 12-50 30-50s30 18 30 50c0 24 6 40 10 58H20c4-18 10-34 10-58Z" fill="{{ $hair }}"/>
                            @elseif($i === 1)<circle cx="60" cy="30" r="13" fill="{{ $hair }}"/>
                            @elseif($i === 2)<path d="M34 64c0-26 10-40 26-40s26 14 26 40v30c0 5-5 7-9 4V68H43v30c-4 3-9 1-9-4Z" fill="{{ $hair }}"/>
                            @else<g fill="{{ $hair }}"><circle cx="36" cy="58" r="12"/><circle cx="48" cy="40" r="13"/><circle cx="68" cy="36" r="14"/><circle cx="84" cy="48" r="13"/><circle cx="90" cy="68" r="11"/><circle cx="32" cy="80" r="10"/><circle cx="90" cy="88" r="9"/></g>@endif
                            <path d="M22 160c0-30 14-50 38-50s38 20 38 50Z" fill="{{ ['#faf5f1', '#f1d6d2', '#efe6d4', '#dfeadf'][$i] }}"/>
                            <path d="M50 108h20v12c-6 6-14 6-20 0Z" fill="{{ $skin }}"/>
                            <ellipse cx="60" cy="70" rx="22" ry="26" fill="{{ $skin }}"/>
                            @if($i === 0)<path d="M38 68c2-20 12-28 22-28s20 8 22 28c-10-4-20-12-22-22-4 10-12 18-22 22Z" fill="{{ $hair }}"/>
                            @elseif($i === 1)<path d="M37 68c0-20 10-30 23-30s23 10 23 30c-8-7-15-15-23-15s-15 8-23 15Z" fill="{{ $hair }}"/>
                            @elseif($i === 2)<path d="M36 66c4-16 14-22 26-22s20 8 22 22c-14 0-24-6-30-14-2 6-8 12-18 14Z" fill="{{ $hair }}"/>
                            @else<path d="M40 64c4-10 12-14 20-14s16 4 20 14c-8-2-14-6-20-10-6 6-12 8-20 10Z" fill="{{ $hair }}"/>@endif
                            <path d="M50 72c3 3 7 3 10 0m4 0c3 3 7 3 10 0" stroke="#3a2440" stroke-width="1.8" stroke-linecap="round"/><path d="M54 84c4 4 12 4 16 0" stroke="#c9566b" stroke-width="2.4" stroke-linecap="round"/>
                            <circle cx="40" cy="80" r="2.6" fill="#e8c46a"/><circle cx="80" cy="80" r="2.6" fill="#e8c46a"/>
                        </svg>
                    </x-demo-photo>
                    <h3 class="demo-serif mt-4 text-2xl">{{ $name }}</h3>
                    <p class="text-sm text-[#6b5670]">{{ $role }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Results --}}
<section id="rezultati" class="dr scroll-mt-16 bg-white px-4 py-20">
    <div class="mx-auto max-w-6xl">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#8a5fa8]">Резултати</p>
        <h2 class="demo-serif mt-3 max-w-xl text-4xl md:text-5xl">Какво носят клиентките ни вкъщи</h2>
        <div class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach([['Балеаж', 'от тъмно към мед'], ['Гел лак', 'четири седмици издръжливост'], ['Лице', 'сияйна и хидратирана кожа'], ['Мигли и вежди', 'оформен и лек поглед']] as $i => [$title, $text])
                <figure class="lv-lift group relative">
                    <x-demo-photo :slug="$demo['slug']" name="gallery-{{ $i + 1 }}" alt="{{ $title }}" class="relative aspect-square w-full overflow-hidden rounded-[1.75rem] ring-1 ring-[#eadfd8]" style="background: {{ ['linear-gradient(160deg,#efe3f6,#d9c6ea)', 'linear-gradient(160deg,#fbeae4,#f1cfc8)', 'linear-gradient(160deg,#fdf1e2,#efd8b8)', 'linear-gradient(160deg,#e9eef0,#cfdad6)'][$i] }}">
                        <svg viewBox="0 0 200 200" class="absolute inset-0 h-full w-full" fill="none">
                            <defs>
                                <linearGradient id="sl-g-a{{ $i }}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3a2440"/><stop offset=".55" stop-color="#8a5fa8"/><stop offset="1" stop-color="#d9a066"/></linearGradient>
                                <radialGradient id="sl-g-b{{ $i }}" cx=".4" cy=".35" r=".7"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#e9b8a6"/></radialGradient>
                            </defs>
                            @if($i === 0)
                                <g stroke="url(#sl-g-a0)" stroke-width="9" stroke-linecap="round"><path d="M50 20c-20 50 20 80 0 160"/><path d="M76 16c-18 50 18 84 0 168"/><path d="M102 14c-14 52 14 88 0 172"/><path d="M128 16c-16 50 16 84 0 168"/><path d="M154 20c-18 50 18 80 0 160"/></g>
                                <g stroke="#fff" stroke-width="2" stroke-linecap="round" opacity=".5"><path d="M62 30c-14 44 14 70 0 130"/><path d="M114 28c-12 46 12 76 0 136"/></g>
                            @elseif($i === 1)
                                <g><rect x="26" y="70" width="26" height="62" rx="13" fill="#c9566b"/><rect x="58" y="52" width="26" height="76" rx="13" fill="#e9a3b4"/><rect x="90" y="46" width="26" height="82" rx="13" fill="#8a5fa8"/><rect x="122" y="56" width="26" height="72" rx="13" fill="#f3d6c9"/><rect x="154" y="74" width="22" height="54" rx="11" fill="#4a2b4f"/></g>
                                <g fill="#fff" opacity=".55"><rect x="32" y="76" width="5" height="26" rx="2.5"/><rect x="64" y="58" width="5" height="30" rx="2.5"/><rect x="96" y="52" width="5" height="34" rx="2.5"/><rect x="128" y="62" width="5" height="28" rx="2.5"/></g>
                            @elseif($i === 2)
                                <circle cx="100" cy="100" r="64" fill="url(#sl-g-b2)"/><circle cx="100" cy="100" r="86" stroke="#fff" stroke-opacity=".7" stroke-width="2"/><circle cx="100" cy="100" r="40" stroke="#c9973f" stroke-opacity=".5" stroke-width="2"/>
                                <g fill="#fff"><path d="M150 50l3 9 9 3-9 3-3 9-3-9-9-3 9-3Z"/><path d="M52 138l2 6 6 2-6 2-2 6-2-6-6-2 6-2Z" opacity=".8"/></g>
                            @else
                                <g stroke="#3a2440" stroke-width="4" stroke-linecap="round"><path d="M40 96c20 20 62 20 90 0"/><path d="M48 104l-6 12m20-4l-4 14m22-2l-1 14m22-4l3 13m18-8l6 11"/></g>
                                <path d="M44 60c20-14 50-16 84-4" stroke="#7a4a3a" stroke-width="9" stroke-linecap="round"/>
                                <path d="M60 150c24 10 52 10 80 0" stroke="#c9566b" stroke-width="6" stroke-linecap="round" opacity=".7"/>
                            @endif
                        </svg>
                    </x-demo-photo>
                    <figcaption class="mt-3"><p class="demo-serif text-xl">{{ $title }}</p><p class="text-sm text-[#6b5670]">{{ $text }}</p></figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Vouchers and reviews --}}
<section id="vaucheri" class="dr scroll-mt-16 px-4 pb-20">
    <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-5">
        <div class="relative overflow-hidden rounded-[2rem] bg-[linear-gradient(135deg,#4a2b4f,#8a5fa8)] p-8 text-white md:p-10 lg:col-span-2">
            <p class="text-xs font-bold uppercase tracking-widest text-[#e9dcf3]">Подаръчен ваучер</p>
            <h2 class="demo-serif mt-4 text-4xl leading-tight">Подарете време за себе си</h2>
            <p class="mt-4 text-[#e9dcf3]">Избирате сума, получавате ваучера по имейл след минута и той важи една година.</p>
            <div data-pick="voucher" class="mt-6 flex flex-wrap gap-2">
                @foreach(['30 €', '50 €', '80 €', '120 €'] as $i => $sum)
                    <button type="button" data-value="{{ $sum }}" aria-pressed="{{ $i === 1 ? 'true' : 'false' }}" class="rounded-full border border-white/40 px-4 py-2 text-sm font-bold aria-pressed:bg-white aria-pressed:text-[#4a2b4f]">{{ $sum }}</button>
                @endforeach
            </div>
            <button type="button" data-demo-action class="mt-6 rounded-full bg-white px-7 py-3 font-bold text-[#4a2b4f] hover:bg-[#f5eef8]">Купи ваучер за <span data-pick-out="voucher">50 €</span></button>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:col-span-3">
            @foreach([
                ['Запазих си час в 23 часа вечерта от телефона. На сутринта имах потвърждение. Балеажът стана точно като на снимката, която показах.', 'Симона К.', 'Балеаж при Виктория'],
                ['Харесва ми, че цените са на сайта и знам колко ще платя. Маникюрът държи четири седмици.', 'Петя Н.', 'Маникюр при Десислава'],
            ] as [$quote, $who, $what])
                <figure class="lv-lift flex flex-col rounded-[2rem] bg-white p-8 ring-1 ring-[#eadfd8]">
                    <p class="text-[#c9973f]" aria-label="5 от 5 звезди">★★★★★</p>
                    <blockquote class="demo-serif mt-4 flex-1 text-xl leading-snug">„{{ $quote }}“</blockquote>
                    <figcaption class="mt-6 text-sm"><strong>{{ $who }}</strong><span class="block text-[#8a7790]">{{ $what }}</span></figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="scroll-mt-16 bg-[#3a2440] px-4 py-16 text-white">
    <div class="mx-auto grid max-w-6xl gap-10 md:grid-cols-3">
        <div>
            <p class="demo-serif text-3xl italic">Лавандула</p>
            <p class="mt-4 text-[#d9c6ea]">ул. „Преслав“ 31, Варна<br>На две минути от Катедралата</p>
        </div>
        <dl class="space-y-2 text-sm">
            <dt class="mb-3 text-xs font-bold uppercase tracking-widest text-[#d9c6ea]">Работно време</dt>
            @foreach([['Понеделник до петък', '9:00 до 20:00'], ['Събота', '9:00 до 18:00'], ['Неделя', 'Почивен ден']] as [$day, $hours])
                <div class="flex justify-between border-b border-white/15 pb-2"><dt>{{ $day }}</dt><dd class="font-semibold">{{ $hours }}</dd></div>
            @endforeach
        </dl>
        <div class="flex flex-col items-start gap-3">
            <a href="tel:+35952000000" data-demo-action class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 font-bold text-[#3a2440]"><x-icon name="phone" /> 052 000 000</a>
            <a href="#chas" class="inline-flex items-center gap-2 rounded-full border border-white/40 px-6 py-3 font-semibold hover:bg-white/10"><x-icon name="calendar" /> Запази час онлайн</a>
        </div>
    </div>
</section>
@endsection

@push('head')
<style>
    .lv-link { position: relative; padding: .25rem 0; }
    .lv-link::after { content: ""; position: absolute; left: 0; right: 0; bottom: -2px; height: 2px; background: #8a5fa8; transform: scaleX(0); transform-origin: left; }
    details[data-menu][open] .lv-b1 { transform: translateY(5px) rotate(45deg); transform-origin: center; transform-box: fill-box; }
    details[data-menu][open] .lv-b2 { opacity: 0; }
    details[data-menu][open] .lv-b3 { transform: translateY(-5px) rotate(-45deg); transform-origin: center; transform-box: fill-box; }
    @media (prefers-reduced-motion: no-preference) {
        .lv-link::after { transition: transform .3s ease; }
        .lv-link:hover::after { transform: scaleX(1); }
        .lv-lift { transition: transform .35s ease, box-shadow .35s ease; }
        .lv-lift:hover { transform: translateY(-6px); }
        .lv-row { transition: background-color .25s ease, padding .25s ease; border-radius: 1rem; }
        .lv-row:hover { background: #faf5f1; }
        [data-key] { animation: lv-in .35s ease; }
        details[data-menu] svg path { transition: transform .25s ease, opacity .2s ease; }
        details[data-menu][open] > div { animation: lv-in .25s ease; }
        .lv-float { animation: lv-float 5s ease-in-out infinite; }
        .lv-spark { transform-origin: center; transform-box: fill-box; animation: lv-twinkle 3.2s ease-in-out infinite; }
        @keyframes lv-float { 50% { transform: translateY(-8px); } }
        @keyframes lv-twinkle { 50% { opacity: .35; } }
        @keyframes lv-in { from { opacity: 0; transform: translateY(6px); } }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('details[data-menu] a').forEach(function (a) { a.addEventListener('click', function () { a.closest('details').open = false; }); });
    document.querySelector('[data-pick="service"]').addEventListener('pick', function (e) {
        var button = e.target.querySelector('[aria-pressed="true"]');
        document.querySelector('[data-price]').textContent = button.dataset.price;
        document.querySelector('[data-minutes]').textContent = button.dataset.minutes;
    });
</script>
@endpush
