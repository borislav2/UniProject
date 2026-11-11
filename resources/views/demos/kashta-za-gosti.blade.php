@extends('demos._layout')
@section('body_class', 'bg-[#f4eee3] text-[#1d2b24]')

@php
    $rooms = [
        ['Двойна стая с изглед към реката', '2 гости · 22 кв. м · тераса', 68, ['Закуска', 'Камина', 'Изглед'], '#7c9a7e'],
        ['Студио с кухненски бокс', '3 гости · 34 кв. м · кухня', 89, ['Закуска', 'Кухня', 'Тераса'], '#b5683c'],
        ['Семеен апартамент', '4 гости · 52 кв. м · две спални', 124, ['Закуска', 'Две спални', 'Вана'], '#4d6b8a'],
    ];
    $in = now()->addDays(9)->format('Y-m-d');
    $out = now()->addDays(11)->format('Y-m-d');
@endphp

@section('content')
<header class="absolute inset-x-0 z-50 text-white">
    <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4">
        <a href="#" class="demo-serif relative z-10 text-2xl">Бяла река <span class="block whitespace-nowrap text-[10px] font-sans font-semibold uppercase tracking-[0.2em] sm:tracking-[0.3em] text-[#e6d3b3]">Къща за гости · Родопи</span></a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#stai" class="bz-link">Стаи</a><a href="#kashtata" class="bz-link">Къщата</a><a href="#den" class="bz-link">Един ден</a><a href="#naokolo" class="bz-link">Наоколо</a><a href="#kontakt" class="bz-link">Как да стигнете</a>
        </div>
        <div class="flex items-center gap-2">
            <a href="#rezervaciya" class="rounded-full bg-white px-5 py-2 text-sm font-bold text-[#1f3a2e] transition hover:bg-[#e6d3b3]">Резервирай</a>
            <details data-menu class="md:hidden">
                <summary aria-label="Меню" class="relative z-10 flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full border border-white/50 bg-[#14261d]/70 text-white backdrop-blur [&::-webkit-details-marker]:hidden">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path class="bz-b1" d="M4 7h16"/><path class="bz-b2" d="M4 12h16"/><path class="bz-b3" d="M4 17h16"/></svg>
                </summary>
                <div class="absolute inset-x-0 top-0 -z-0 bg-[#14261d] px-4 pb-6 pt-20 shadow-2xl">
                    <div class="flex flex-col divide-y divide-white/10 text-lg">
                        @foreach([['#stai', 'Стаи'], ['#kashtata', 'Къщата'], ['#den', 'Един ден при нас'], ['#naokolo', 'Наоколо'], ['#kontakt', 'Как да стигнете']] as [$href, $label])
                            <a href="{{ $href }}" class="demo-serif py-3.5 text-white">{{ $label }}</a>
                        @endforeach
                    </div>
                    <a href="#rezervaciya" class="mt-4 block rounded-full bg-[#e6d3b3] py-3.5 text-center font-bold text-[#1f3a2e]">Резервирай</a>
                </div>
            </details>
        </div>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden bg-[#1f3a2e] text-white">
    <x-demo-photo :slug="$demo['slug']" name="hero" alt="Къщата сред Родопите" class="absolute inset-0 h-full w-full">
        <svg viewBox="0 0 1440 760" preserveAspectRatio="xMidYMid slice" class="h-full w-full" fill="none">
            <defs>
                <linearGradient id="kz-sky" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#3b4f6b"/><stop offset=".3" stop-color="#a5707a"/><stop offset=".6" stop-color="#f0a56f"/><stop offset="1" stop-color="#f9d49a"/></linearGradient>
                <radialGradient id="kz-sun" cx=".5" cy=".5" r=".5"><stop stop-color="#fff2c8"/><stop offset=".25" stop-color="#fbd38e" stop-opacity=".85"/><stop offset="1" stop-color="#f4a672" stop-opacity="0"/></radialGradient>
                <linearGradient id="kz-mist" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#f8d9b0" stop-opacity="0"/><stop offset=".6" stop-color="#f8d9b0" stop-opacity=".55"/><stop offset="1" stop-color="#f8d9b0" stop-opacity="0"/></linearGradient>
                <linearGradient id="kz-r1" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#b57a7e"/><stop offset="1" stop-color="#c58f80"/></linearGradient>
                <linearGradient id="kz-r2" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#8a6f7a"/><stop offset="1" stop-color="#a88378"/></linearGradient>
                <linearGradient id="kz-r3" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#5f6f6e"/><stop offset="1" stop-color="#7d8479"/></linearGradient>
                <linearGradient id="kz-r4" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#3b5a4e"/><stop offset="1" stop-color="#53705f"/></linearGradient>
                <linearGradient id="kz-meadow" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#33503f"/><stop offset="1" stop-color="#1c3226"/></linearGradient>
                <linearGradient id="kz-river" x1="0" y1="0" x2="1" y2="0"><stop stop-color="#e9a77a"/><stop offset=".5" stop-color="#fbe0b0"/><stop offset="1" stop-color="#d98f6c"/></linearGradient>
                <radialGradient id="kz-win" cx=".5" cy=".5" r=".5"><stop stop-color="#ffd978"/><stop offset="1" stop-color="#ffb347" stop-opacity="0"/></radialGradient>
                <linearGradient id="kz-smoke" x1="0" y1="1" x2="0" y2="0"><stop stop-color="#f4e6d2" stop-opacity=".6"/><stop offset="1" stop-color="#f4e6d2" stop-opacity="0"/></linearGradient>
            </defs>
            <rect width="1440" height="760" fill="url(#kz-sky)"/>
            <g fill="#fff" opacity=".7"><circle cx="120" cy="70" r="1.5"/><circle cx="340" cy="40" r="1.2"/><circle cx="560" cy="95" r="1.4"/><circle cx="760" cy="30" r="1.2"/><circle cx="1240" cy="60" r="1.5"/><circle cx="1380" cy="130" r="1.2"/></g>
            
            <circle class="kz-sun" cx="1010" cy="330" r="260" fill="url(#kz-sun)"/><circle cx="1010" cy="330" r="62" fill="#fff0c4"/>
            <path d="M0 400 140 350l130 40 150-70 160 60 130-80 170 80 140-60 150 70 130-50 130 60v280H0Z" fill="url(#kz-r1)"/>
            <rect y="380" width="1440" height="70" fill="url(#kz-mist)"/>
            <path d="M0 470 110 410l120 50 140-80 150 70 120-50 160 70 130-40 150 60 140-60 120 50 100-30v250H0Z" fill="url(#kz-r2)"/>
            <rect y="450" width="1440" height="80" fill="url(#kz-mist)"/>
            <path d="M0 540 150 470l140 60 130-70 170 80 130-40 190 70 120-60 150 60 130-40 130 40v220H0Z" fill="url(#kz-r3)"/>
            <rect y="520" width="1440" height="90" fill="url(#kz-mist)"/>
            <path d="M0 610C180 540 330 600 520 570s330-70 520-20 280 30 400-10v220H0Z" fill="url(#kz-r4)"/>
            <rect y="585" width="1440" height="70" fill="url(#kz-mist)" opacity=".8"/>
            <path d="M0 650c200-40 420 10 640-12s520-40 800 20v110H0Z" fill="url(#kz-meadow)"/>
            <path d="M470 760c60-40 180-52 260-80s60-44-40-58c-90-12-90-22 20-34 90-10 170 0 230-20" stroke="url(#kz-river)" stroke-width="26" stroke-linecap="round" opacity=".95"/>
            <path d="M520 740c70-30 170-44 240-66M820 600c60-6 120 2 160-6" stroke="#fff" stroke-opacity=".55" stroke-width="3" stroke-linecap="round"/>
            <g transform="translate(985 470)">
                <ellipse cx="85" cy="152" rx="130" ry="14" fill="#14261d" opacity=".45"/>
                <path d="M-6 66 84 2l90 64Z" fill="#6b3d28"/><path d="M-6 66 84 2l90 64-10 0L84 12 4 66Z" fill="#9a5836"/>
                <rect x="6" y="64" width="156" height="90" fill="#cdbfa6"/><rect x="6" y="110" width="156" height="44" fill="#6b4a33"/>
                <path d="M6 128h156M6 140h156" stroke="#4a3022" stroke-width="1.5"/>
                <path d="M6 64h40v46H6ZM74 64h44v46H74Z" fill="#b9a98c" opacity=".6"/>
                <rect x="124" y="-14" width="18" height="38" fill="#6f6a60"/><rect x="121" y="-18" width="24" height="7" fill="#4a463f"/>
                <g><circle cx="26" cy="86" r="22" fill="url(#kz-win)"/><circle cx="96" cy="86" r="22" fill="url(#kz-win)"/><circle cx="140" cy="130" r="18" fill="url(#kz-win)"/>
                <rect x="16" y="76" width="22" height="22" fill="#ffd36e"/><rect x="86" y="76" width="22" height="22" fill="#ffd36e"/><rect x="130" y="120" width="20" height="20" fill="#ffc25a"/>
                <path d="M27 76v22M16 87h22M97 76v22M86 87h22" stroke="#6b4a33" stroke-width="2"/></g>
                <rect x="50" y="112" width="26" height="42" rx="3" fill="#3c2a1e"/>
                <path class="kz-smoke" d="M133-20c-14-22 18-34 4-58s20-36 8-62c26 20-6 40 10 62s-6 36-4 58Z" fill="url(#kz-smoke)"/>
            </g>
            <g fill="#12241b">@foreach([[40, 770, 1.15], [130, 790, .9], [230, 760, .75], [330, 740, .6], [1250, 750, .7], [1330, 790, 1.2], [1410, 770, .95], [1180, 735, .55]] as [$x, $y, $k])<g transform="translate({{ $x }} {{ $y }}) scale({{ $k }})"><path d="M-46-40 0-120 46-40Z"/><path d="M-38-95 0-170 38-95Z"/><path d="M-28-150 0-215 28-150Z"/><rect x="-5" y="-40" width="10" height="40"/></g>@endforeach</g>
            <g fill="#f4d9b0" opacity=".5">@foreach([[560, 700], [900, 660], [300, 690], [1100, 640]] as [$x, $y])<circle class="kz-fly" cx="{{ $x }}" cy="{{ $y }}" r="2.2"/>@endforeach</g>
        </svg>
    </x-demo-photo>
    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(18,32,26,.55)_0%,rgba(18,32,26,.15)_45%,rgba(18,32,26,.8)_100%)]"></div>
    <div class="relative mx-auto max-w-7xl px-4 pb-44 pt-40 md:pb-52 md:pt-48">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#e6d3b3]">с. Широка лъка · 1 060 м надморска височина</p>
        <h1 class="demo-serif mt-5 max-w-3xl text-5xl leading-[1.03] sm:text-6xl lg:text-7xl">Тишина, камина и <em>река под прозореца</em></h1>
        <p class="mt-6 max-w-xl text-lg leading-relaxed text-[#e8e0d0]">Възрожденска къща с девет стаи, домашна закуска от продукти от селото и гореща каца под звездите.</p>
        <p class="mt-8 inline-flex flex-wrap items-center gap-x-3 gap-y-1 rounded-full bg-white/15 px-5 py-2 text-sm font-semibold backdrop-blur"><span class="rounded-md bg-[#1f3a2e] px-2 py-0.5">9,6</span> Изключително · 412 отзива на гости</p>
    </div>
</section>

{{-- Booking bar --}}
<section id="rezervaciya" class="relative z-10 -mt-28 scroll-mt-24 px-4">
    <form data-demo-form data-stay class="mx-auto max-w-6xl rounded-3xl bg-white p-5 shadow-2xl ring-1 ring-black/5 md:p-7">
        <div class="grid gap-4 md:grid-cols-12 md:items-end">
            <label class="md:col-span-3"><span class="text-[11px] font-bold uppercase tracking-widest text-[#8a6a4a]">Настаняване</span><input type="date" data-in value="{{ $in }}" class="mt-1.5 w-full rounded-xl border border-[#ded3c0] px-4 py-3 font-semibold focus:border-[#1f3a2e] focus:ring-[#1f3a2e]"></label>
            <label class="md:col-span-3"><span class="text-[11px] font-bold uppercase tracking-widest text-[#8a6a4a]">Напускане</span><input type="date" data-out value="{{ $out }}" class="mt-1.5 w-full rounded-xl border border-[#ded3c0] px-4 py-3 font-semibold focus:border-[#1f3a2e] focus:ring-[#1f3a2e]"></label>
            <label class="md:col-span-2"><span class="text-[11px] font-bold uppercase tracking-widest text-[#8a6a4a]">Гости</span><select data-guests class="mt-1.5 w-full rounded-xl border border-[#ded3c0] px-4 py-3 font-semibold focus:border-[#1f3a2e] focus:ring-[#1f3a2e]"><option value="2">2 възрастни</option><option value="3">3 гости</option><option value="4">4 гости</option></select></label>
            <div class="md:col-span-2"><p class="text-[11px] font-bold uppercase tracking-widest text-[#8a6a4a]">Общо за <span data-nights>2 нощувки</span></p><p class="demo-serif mt-1 text-4xl"><span data-total>136</span> €</p></div>
            <button class="rounded-xl bg-[#1f3a2e] py-3.5 font-bold text-white hover:bg-[#2c5241] md:col-span-2">Резервирай</button>
        </div>
        <p class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm text-[#5c6b62]">
            <span data-room-name class="font-semibold text-[#1d2b24]">Двойна стая с изглед към реката</span>
            <span class="flex items-center gap-2"><x-icon name="check" class="text-xs text-[#3f8a5c]" /> С 10% по-евтино, отколкото в сайтовете за резервации</span>
            <span class="flex items-center gap-2"><x-icon name="check" class="text-xs text-[#3f8a5c]" /> Безплатна отмяна до 7 дни преди</span>
        </p>
    </form>
</section>

{{-- Rooms --}}
<section id="stai" class="dr scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-xs font-bold uppercase tracking-[0.3em] text-[#b5683c]">Стаи</p><h2 class="demo-serif mt-3 text-4xl md:text-5xl">Изберете своята стая</h2></div>
            <p class="max-w-sm text-[#5c6b62]">Цените са за нощувка със закуска. Изберете стая и общата сума горе се смята сама.</p>
        </div>
        <div data-pick="room" class="dr mt-12 grid gap-6 lg:grid-cols-3">
            @foreach($rooms as $i => [$name, $meta, $price, $tags, $color])
                <button type="button" data-value="{{ $name }}" data-price="{{ $price }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="bz-lift group flex flex-col overflow-hidden rounded-3xl bg-white text-left ring-1 ring-[#e2d8c6] transition hover:-translate-y-1 hover:shadow-xl aria-pressed:ring-2 aria-pressed:ring-[#1f3a2e]">
                    <x-demo-photo :slug="$demo['slug']" name="room-{{ $i + 1 }}" alt="{{ $name }}" class="relative block aspect-[4/3] w-full" style="background: linear-gradient(170deg, {{ $color }}55, {{ $color }})">
                        <svg viewBox="0 0 400 300" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none">
                            <defs><linearGradient id="rw{{ $i }}" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#f1c58f"/><stop offset="1" stop-color="#9fb7a0"/></linearGradient><linearGradient id="rf{{ $i }}" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#a47a56"/><stop offset="1" stop-color="#7a5539"/></linearGradient></defs>
                            <rect width="400" height="300" fill="#e9dcc3"/><rect y="236" width="400" height="64" fill="url(#rf{{ $i }})"/><path d="M0 236h400" stroke="#5e4029" stroke-width="3"/>
                            @if($i === 0)
                                <rect x="214" y="30" width="150" height="130" rx="6" fill="#5d4330"/><rect x="222" y="38" width="134" height="114" fill="url(#rw{{ $i }})"/>
                                <path d="M222 120 270 92l36 24 50-30v66H222Z" fill="#6c8a73"/><path d="M222 140c40-14 80 6 134-6v18H222Z" fill="#8fc0cf"/><path d="M240 146h60M310 142h40" stroke="#fff" stroke-opacity=".7" stroke-width="2"/><path d="M289 38v114M222 95h134" stroke="#5d4330" stroke-width="5"/>
                                <path d="M200 30c20 40 6 90 14 130M376 30c-14 40 0 90-12 130" stroke="#b5683c" stroke-width="16" opacity=".85"/>
                                <rect x="30" y="150" width="150" height="86" rx="8" fill="#6b4a33"/><rect x="24" y="116" width="162" height="50" rx="10" fill="#8a5a3a"/><rect x="34" y="150" width="142" height="62" rx="10" fill="#f8f3ea"/><rect x="44" y="132" width="52" height="30" rx="12" fill="#fff"/><rect x="104" y="132" width="52" height="30" rx="12" fill="#fff"/><path d="M34 188h142v24c0 4-4 6-8 6H42c-4 0-8-2-8-6Z" fill="#b5683c"/>
                                <rect x="196" y="204" width="26" height="32" rx="3" fill="#6b4a33"/><circle cx="209" cy="194" r="10" fill="#ffd36e"/><circle cx="209" cy="194" r="22" fill="#ffd36e" opacity=".25"/>
                            @elseif($i === 1)
                                <rect x="24" y="50" width="120" height="110" rx="6" fill="#5d4330"/><rect x="32" y="58" width="104" height="94" fill="url(#rw{{ $i }})"/><path d="M84 58v94M32 105h104" stroke="#5d4330" stroke-width="4"/>
                                <rect x="176" y="40" width="200" height="14" rx="3" fill="#6b4a33"/><rect x="176" y="64" width="200" height="8" fill="#f4eee3"/><rect x="190" y="74" width="172" height="162" rx="4" fill="#f4eee3" stroke="#d9ccb2" stroke-width="2"/>
                                <rect x="190" y="74" width="172" height="50" fill="#fbf7ef"/><rect x="200" y="86" width="52" height="38" rx="4" fill="#b5683c"/><rect x="262" y="94" width="40" height="30" rx="4" fill="#cfa77f"/>
                                <circle cx="330" cy="104" r="14" fill="#1d2b24"/><circle cx="330" cy="104" r="8" fill="#3b4a42"/><path d="M190 166h172M190 200h172" stroke="#d9ccb2" stroke-width="2"/><rect x="262" y="140" width="26" height="12" rx="3" fill="#8a5a3a"/><path d="M265 60c-6-8 6-10 0-18" stroke="#b5683c" stroke-width="2"/>
                                <rect x="40" y="170" width="116" height="66" rx="8" fill="#4d6b8a"/><rect x="34" y="150" width="128" height="36" rx="12" fill="#6785a4"/><rect x="48" y="160" width="40" height="22" rx="8" fill="#e9dcc3"/><rect x="96" y="160" width="40" height="22" rx="8" fill="#b5683c"/>
                                <rect x="294" y="214" width="42" height="22" rx="4" fill="#8a5a3a"/><circle cx="315" cy="196" r="12" fill="#3f8a5c"/><path d="M315 208v8" stroke="#2c5a3d" stroke-width="3"/>
                            @else
                                <rect x="150" y="26" width="100" height="84" rx="5" fill="#5d4330"/><rect x="157" y="33" width="86" height="70" fill="url(#rw{{ $i }})"/><path d="M200 33v70M157 68h86" stroke="#5d4330" stroke-width="4"/><path d="M157 90 190 70l24 14 29-16v35H157Z" fill="#587a62"/>
                                <rect x="14" y="120" width="170" height="116" rx="8" fill="#4d6b8a"/><rect x="14" y="98" width="170" height="40" rx="10" fill="#3f5a75"/><rect x="26" y="120" width="68" height="28" rx="10" fill="#f8f3ea"/><rect x="104" y="120" width="68" height="28" rx="10" fill="#f8f3ea"/><rect x="14" y="158" width="170" height="20" fill="#6b8fb0" opacity=".6"/>
                                <rect x="212" y="150" width="174" height="86" rx="8" fill="#6b4a33"/><rect x="206" y="128" width="186" height="38" rx="10" fill="#8a5a3a"/><rect x="218" y="150" width="70" height="26" rx="10" fill="#fff"/><rect x="304" y="150" width="70" height="26" rx="10" fill="#fff"/><path d="M206 192h186v16H206Z" fill="#b5683c"/>
                                <rect x="196" y="190" width="6" height="46" fill="#5d4330"/><circle cx="199" cy="182" r="12" fill="#ffd36e"/><circle cx="199" cy="182" r="26" fill="#ffd36e" opacity=".22"/><ellipse cx="200" cy="266" rx="90" ry="12" fill="#b5683c" opacity=".55"/>
                            @endif
                        </svg>
                    </x-demo-photo>
                    <span class="flex flex-1 flex-col p-6">
                        <span class="demo-serif text-2xl leading-snug">{{ $name }}</span>
                        <span class="mt-1 text-sm text-[#5c6b62]">{{ $meta }}</span>
                        <span class="mt-4 flex flex-wrap gap-2">@foreach($tags as $tag)<span class="rounded-full bg-[#f4eee3] px-3 py-1 text-xs font-semibold">{{ $tag }}</span>@endforeach</span>
                        <span class="mt-6 flex items-end justify-between border-t border-[#ece3d3] pt-4"><span><span class="demo-serif text-3xl">{{ $price }} €</span><span class="text-sm text-[#5c6b62]"> на нощувка</span></span><span class="rounded-full border border-[#1f3a2e] px-4 py-1.5 text-sm font-bold group-aria-pressed:bg-[#1f3a2e] group-aria-pressed:text-white"><span class="group-aria-pressed:hidden">Избери</span><span class="hidden group-aria-pressed:inline">Избрана</span></span></span>
                    </span>
                </button>
            @endforeach
        </div>
    </div>
</section>

{{-- The house --}}
<section id="kashtata" class="dr scroll-mt-16 bg-[#1f3a2e] px-4 py-20 text-white">
    <div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#e6d3b3]">Къщата</p>
            <h2 class="demo-serif mt-3 text-4xl leading-tight md:text-5xl">Всичко, за да не ви се тръгва</h2>
            <p class="mt-5 max-w-lg leading-relaxed text-[#cfd9d2]">Къщата е на 180 години и я възстановихме с камък, дърво и глина. Стаите са топли през зимата и прохладни през лятото, а сутрин ухае на мекици.</p>
            <ul class="mt-10 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                @foreach([['Домашна закуска', 'Мекици, сирене, мед и сладко от селото'], ['Гореща каца на открито', 'Работи целогодишно, до 6 души'], ['Механа с камина', 'Вечеря по заявка, родопска кухня'], ['Безплатен паркинг', 'Шест места в двора'], ['Бърз интернет', 'Във всички стаи и в двора'], ['Приемаме домашни любимци', 'Без доплащане']] as [$title, $text])
                    <li class="flex gap-3"><span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#e6d3b3] text-xs text-[#1f3a2e]"><x-icon name="check" /></span><span><strong class="block">{{ $title }}</strong><span class="text-sm text-[#cfd9d2]">{{ $text }}</span></span></li>
                @endforeach
            </ul>
        </div>
        <div class="grid grid-cols-2 gap-4">
            @foreach([['house-1', 'Гореща каца под звездите', '#27475a', 'aspect-[3/4]'], ['house-2', 'Закуска на двора', '#b5683c', 'aspect-[3/4] mt-10']] as [$file, $alt, $color, $shape])
                <x-demo-photo :slug="$demo['slug']" :name="$file" :alt="$alt" class="relative w-full overflow-hidden rounded-3xl {{ $shape }}" style="background: linear-gradient(170deg, {{ $color }}, #14261d)">
                    <svg viewBox="0 0 150 200" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none">
                        <defs><radialGradient id="kh{{ $loop->index }}" cx=".5" cy=".5" r=".5"><stop stop-color="#ffd98a" stop-opacity=".9"/><stop offset="1" stop-color="#ffd98a" stop-opacity="0"/></radialGradient><linearGradient id="kw{{ $loop->index }}" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#8fd0e6"/><stop offset="1" stop-color="#3f87a8"/></linearGradient></defs>
                        @if($loop->first)
                            <g fill="#fff">@foreach([[20, 30, 1.6], [60, 18, 1.2], [110, 40, 1.8], [130, 80, 1.2], [40, 70, 1.3], [90, 62, 1.5], [12, 100, 1], [140, 20, 1.2]] as [$x, $y, $r])<circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $r }}"/>@endforeach</g>
                            <circle cx="112" cy="36" r="13" fill="#fbe9b8"/><circle cx="117" cy="33" r="11" fill="#27475a"/>
                            <path d="M0 150 30 108l30 28 30-34 60 42v56H0Z" fill="#0f2330" opacity=".7"/>
                            <path d="M-4 200V150c30-10 50-8 70 0s60 6 88-4v54Z" fill="#14261d"/>
                            <path d="M8 138 20 96l10 42ZM126 140l12-52 12 52Z" fill="#0c1a14"/>
                            <ellipse cx="75" cy="146" rx="56" ry="15" fill="#5a3a26"/><ellipse cx="75" cy="142" rx="52" ry="12" fill="url(#kw0)"/><ellipse cx="75" cy="142" rx="52" ry="12" stroke="#c28a5a" stroke-width="3"/><ellipse cx="62" cy="141" rx="22" ry="4" fill="#fff" opacity=".25"/>
                            <path d="M27 148v22c0 10 96 10 96 0v-22" fill="#8a5a3a"/><path d="M40 156v20M55 160v20M70 162v20M85 162v20M100 160v20M112 156v20" stroke="#5e3b24" stroke-width="2"/>
                            <path d="M55 126c-7-9 7-12 0-22m20 22c-7-9 7-12 0-22m20 22c-7-9 7-12 0-22" stroke="#fff" stroke-opacity=".55" stroke-width="2.5" stroke-linecap="round"/>
                            <circle cx="22" cy="160" r="12" fill="url(#kh0)"/><rect x="19" y="158" width="6" height="12" rx="2" fill="#ffd36e"/>
                            <rect x="124" y="164" width="14" height="10" rx="2" fill="#f4eee3"/><path d="M127 164c0-6 8-6 8 0" stroke="#f4eee3" stroke-width="2"/>
                        @else
                            <rect width="150" height="200" fill="#d9894f" opacity=".35"/><circle cx="118" cy="38" r="30" fill="#ffe2a0" opacity=".7"/>
                            <path d="M0 78h150" stroke="#6b3d28" stroke-width="3"/><path d="M-4 78 16 40l20 38M30 78 52 34l22 44" fill="#7a4a30"/>
                            <path d="M0 200V150c40-10 90-10 150 0v50Z" fill="#5f8a54"/><path d="M0 200V170c50-8 100-8 150 0v30Z" fill="#4a7345"/>
                            <path d="M30 128 20 190M120 128l10 62" stroke="#6b4a33" stroke-width="5" stroke-linecap="round"/><rect x="14" y="124" width="122" height="10" rx="3" fill="#8a5a3a"/>
                            <path d="M22 124h106l-8-14H30Z" fill="#fff" opacity=".9"/><path d="M30 124h14l-4-14h-6ZM62 124h14l-2-14h-10ZM94 124h14l-4-14h-6Z" fill="#c9503a"/>
                            <ellipse cx="75" cy="122" rx="46" ry="9" fill="#f8f3ea"/><circle cx="50" cy="117" r="9" fill="#e9b949"/><circle cx="50" cy="117" r="5" fill="#f6d36e"/><circle cx="82" cy="115" r="8" fill="#fff" stroke="#b5683c" stroke-width="2"/><path d="M98 118c6-8 14-8 18 0z" fill="#8a4a2a"/>
                            <rect x="58" y="96" width="14" height="20" rx="3" fill="#f4eee3"/><path d="M60 100h10" stroke="#b5683c" stroke-width="2"/><path d="M65 94c-5-7 5-9 0-16" stroke="#fff" stroke-opacity=".7" stroke-width="2" stroke-linecap="round"/>
                            <rect x="104" y="94" width="18" height="22" rx="3" fill="#f4eee3"/><path d="M104 102h18" stroke="#6b4a33" stroke-width="2"/>
                        @endif
                    </svg>
                    <span class="absolute bottom-3 left-3 rounded-full bg-black/45 px-3 py-1 text-xs font-semibold">{{ $alt }}</span>
                </x-demo-photo>
            @endforeach
        </div>
    </div>
</section>

{{-- A day here --}}
<section id="den" class="dr scroll-mt-16 bg-[#e8dfce] px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-[#b5683c]">Един ден при нас</p>
        <h2 class="demo-serif mt-3 max-w-2xl text-4xl md:text-5xl">От мекиците до звездите</h2>
        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['08:30', 'Закуска на двора', 'Топли мекици, сирене, мед и билков чай.', '#f0b97a', '#d98a6a'],
                ['11:00', 'Пътека през гората', 'Домакините начертават маршрут по вкуса ви.', '#9fb7a0', '#4d6b5a'],
                ['16:00', 'Чай до реката', 'Одеяла, термос и тишина на брега.', '#a9c4cf', '#5f8a9a'],
                ['21:00', 'Каца под звездите', 'Гореща вода и небе без градски светлини.', '#4a6a86', '#1b2f40'],
            ] as $k => [$time, $title, $text, $c1, $c2])
                <article class="bz-lift group overflow-hidden rounded-3xl bg-[#f4eee3] ring-1 ring-[#d3c7b0]">
                    <div class="relative aspect-[5/4]" style="background: linear-gradient(170deg, {{ $c1 }}, {{ $c2 }})">
                        <svg viewBox="0 0 200 160" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none" aria-hidden="true">
                            @if($k === 0)<circle cx="150" cy="40" r="22" fill="#fff2c8"/><path d="M0 110h200v50H0Z" fill="#8a5a3a"/><ellipse cx="100" cy="104" rx="56" ry="12" fill="#f8f3ea"/><circle cx="80" cy="98" r="10" fill="#e9b949"/><circle cx="116" cy="97" r="9" fill="#fff" stroke="#b5683c" stroke-width="2"/><path d="M60 60c-6-8 6-10 0-18" stroke="#fff" stroke-opacity=".7" stroke-width="2.5" stroke-linecap="round"/>
                            @elseif($k === 1)<path d="M0 100 50 60l40 30 50-44 60 50v64H0Z" fill="#3b5a4e"/>@foreach([30, 80, 130, 175] as $x)<path d="M{{ $x }} 150l-18 0 18-52 18 52Z M{{ $x }} 120l-14 0 14-40 14 40Z" fill="#17301f"/>@endforeach<path d="M92 160c10-20 30-26 40-44" stroke="#f4eee3" stroke-width="5" stroke-linecap="round" stroke-dasharray="2 9"/>
                            @elseif($k === 2)<path d="M0 90h200v70H0Z" fill="#5f8a9a"/><path d="M0 100c40-8 90 8 200-4v10c-110 12-160-4-200 4Z" fill="#fff" opacity=".35"/><rect x="60" y="100" width="70" height="8" rx="3" fill="#b5683c"/><rect x="82" y="68" width="14" height="24" rx="3" fill="#f4eee3"/><path d="M89 62c-5-6 5-8 0-14" stroke="#fff" stroke-opacity=".7" stroke-width="2" stroke-linecap="round"/><path d="M0 90c30-24 60-6 90-16s70 10 110-6v22Z" fill="#3b5a4e"/>
                            @else<g fill="#fff">@foreach([[20, 24], [60, 14], [110, 30], [160, 18], [180, 56], [40, 56]] as [$x, $y])<circle cx="{{ $x }}" cy="{{ $y }}" r="1.6"/>@endforeach</g><path d="M0 110h200v50H0Z" fill="#0f2330"/><ellipse cx="100" cy="116" rx="60" ry="14" fill="#6fa8c7"/><ellipse cx="100" cy="116" rx="60" ry="14" stroke="#c28a5a" stroke-width="3"/><path d="M80 98c-6-8 6-10 0-18m22 18c-6-8 6-10 0-18m22 18c-6-8 6-10 0-18" stroke="#fff" stroke-opacity=".55" stroke-width="2.5" stroke-linecap="round"/>@endif
                        </svg>
                        <span class="absolute left-3 top-3 rounded-full bg-[#14261d]/80 px-3 py-1 text-xs font-bold text-white">{{ $time }}</span>
                    </div>
                    <div class="p-5"><h3 class="demo-serif text-xl">{{ $title }}</h3><p class="mt-1.5 text-sm leading-relaxed text-[#4a5a51]">{{ $text }}</p></div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Around --}}
<section id="naokolo" class="dr scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="demo-serif max-w-2xl text-4xl md:text-5xl">Какво да правите наоколо</h2>
        <div class="dr mt-12 grid gap-px overflow-hidden rounded-3xl bg-[#e2d8c6] sm:grid-cols-2 lg:grid-cols-4">
            @foreach([['12 мин', 'Пампорово', 'Ски писти през зимата, лифт и пътеки през лятото.'], ['25 мин', 'Ягодинска пещера', 'Една от най-дългите пещери в България.'], ['От прага', 'Еко пътека „Гела“', 'Три часа пеша през борова гора до връх Турлата.'], ['40 мин', 'Дяволското гърло', 'Пещера с водопад и ждрелото на Триград.']] as [$time, $name, $text])
                <article class="bg-[#f4eee3] p-7"><p class="text-sm font-bold text-[#b5683c]">{{ $time }}</p><h3 class="demo-serif mt-2 text-2xl">{{ $name }}</h3><p class="mt-2 text-[#5c6b62]">{{ $text }}</p></article>
            @endforeach
        </div>
        <div class="mt-16 grid gap-6 md:grid-cols-3">
            @foreach([
                ['Събудихме се от реката и миризмата на мекици. Домакините ни начертаха маршрут и ни дадоха термос с чай за пътя.', 'Силвия и Борис', 'София'],
                ['Резервирах от сайта за две минути и излезе по-евтино, отколкото другаде. Кацата през януари е преживяване.', 'Александър Т.', 'Пловдив'],
                ['Бяхме с две деца и куче. Никой не ни направи забележка, напротив, децата хранеха козите на съседите.', 'Милена В.', 'Варна'],
            ] as [$quote, $who, $city])
                <figure class="bz-lift rounded-3xl bg-white p-7 ring-1 ring-[#e2d8c6]">
                    <p class="inline-block rounded-md bg-[#1f3a2e] px-2 py-0.5 text-sm font-bold text-white">10</p>
                    <blockquote class="demo-serif mt-4 text-xl leading-snug">„{{ $quote }}“</blockquote>
                    <figcaption class="mt-5 text-sm font-semibold">{{ $who }} <span class="font-normal text-[#5c6b62]">· {{ $city }}</span></figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="scroll-mt-16 bg-[#e8dfce] px-4 py-16">
    <div class="mx-auto grid max-w-7xl items-center gap-10 md:grid-cols-2">
        <div>
            <h2 class="demo-serif text-4xl md:text-5xl">Как да стигнете</h2>
            <p class="mt-5 max-w-md leading-relaxed text-[#4a5a51]">На 230 км от София и на 90 км от Пловдив. Пътят е асфалтиран до двора и се чисти през зимата. Посрещаме ви от 14 часа.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="tel:+359301000000" data-demo-action class="inline-flex items-center gap-2 rounded-full bg-[#1f3a2e] px-6 py-3 font-bold text-white"><x-icon name="phone" /> 0301 00 000</a>
                <a href="#" data-demo-action class="inline-flex items-center gap-2 rounded-full border border-[#1f3a2e] px-6 py-3 font-semibold hover:bg-[#1f3a2e] hover:text-white"><x-icon name="map-location-dot" /> Отвори в навигацията</a>
            </div>
        </div>
        <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-3xl bg-[#d3c7b0]">
            @foreach([['Настаняване', 'от 14:00'], ['Напускане', 'до 11:00'], ['От София', '3 ч 20 мин'], ['От Пловдив', '1 ч 40 мин']] as [$k, $v])
                <div class="bg-[#f4eee3] p-6"><dt class="text-xs font-bold uppercase tracking-widest text-[#8a6a4a]">{{ $k }}</dt><dd class="demo-serif mt-1 text-2xl">{{ $v }}</dd></div>
            @endforeach
        </dl>
    </div>
</section>
<footer class="bg-[#1f3a2e] px-4 py-8 text-center text-sm text-[#cfd9d2]">Къща за гости „Бяла река“ · с. Широка лъка, община Смолян · Категория три звезди</footer>
@endsection

@push('head')
<style>
    .bz-link { position: relative; padding: .25rem 0; }
    .bz-link::after { content: ""; position: absolute; left: 0; right: 0; bottom: -2px; height: 2px; background: #e6d3b3; transform: scaleX(0); transform-origin: left; }
    details[data-menu][open] .bz-b1 { transform: translateY(5px) rotate(45deg); transform-origin: center; transform-box: fill-box; }
    details[data-menu][open] .bz-b2 { opacity: 0; }
    details[data-menu][open] .bz-b3 { transform: translateY(-5px) rotate(-45deg); transform-origin: center; transform-box: fill-box; }
    @media (prefers-reduced-motion: no-preference) {
        .bz-link::after { transition: transform .3s ease; }
        .bz-link:hover::after { transform: scaleX(1); }
        .bz-lift { transition: transform .35s ease, box-shadow .35s ease; }
        .bz-lift:hover { transform: translateY(-6px); box-shadow: 0 20px 40px -18px rgba(31,58,46,.45); }
        details[data-menu] svg path { transition: transform .25s ease, opacity .2s ease; }
        details[data-menu][open] > div { animation: bz-in .25s ease; }
        .kz-sun { transform-origin: center; transform-box: fill-box; animation: bz-pulse 6s ease-in-out infinite; }
        .kz-smoke { animation: bz-smoke 7s ease-in-out infinite; }
        .kz-fly { animation: bz-fly 4s ease-in-out infinite; }
        @keyframes bz-pulse { 50% { opacity: .75; transform: scale(1.06); } }
        @keyframes bz-smoke { 50% { transform: translate(6px, -8px); opacity: .6; } }
        @keyframes bz-fly { 50% { transform: translateY(-10px); opacity: .2; } }
        @keyframes bz-in { from { opacity: 0; transform: translateY(-6px); } }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var form = document.querySelector('[data-stay]'), price = 68;
        function draw() {
            var nights = Math.round((new Date(form.querySelector('[data-out]').value) - new Date(form.querySelector('[data-in]').value)) / 86400000);
            if (! (nights > 0)) nights = 1;
            var extra = Math.max(0, Number(form.querySelector('[data-guests]').value) - 2) * 15;
            form.querySelector('[data-nights]').textContent = nights === 1 ? '1 нощувка' : nights + ' нощувки';
            form.querySelector('[data-total]').textContent = nights * (price + extra);
        }
        form.querySelectorAll('input, select').forEach(function (el) { el.addEventListener('change', draw); });
        document.querySelector('[data-pick="room"]').addEventListener('pick', function (e) {
            price = Number(e.target.querySelector('[aria-pressed="true"]').dataset.price);
            form.querySelector('[data-room-name]').textContent = e.detail;
            draw();
        });
        draw();
        document.querySelectorAll('details[data-menu] a').forEach(function (a) { a.addEventListener('click', function () { a.closest('details').removeAttribute('open'); }); });
    })();
</script>
@endpush
