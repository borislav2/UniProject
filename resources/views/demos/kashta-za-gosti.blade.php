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
<header class="absolute inset-x-0 z-40 text-white">
    <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4">
        <a href="#" class="demo-serif text-2xl">Бяла река <span class="block text-[10px] font-sans font-semibold uppercase tracking-[0.3em] text-[#e6d3b3]">Къща за гости · Родопи</span></a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#stai" class="hover:text-[#e6d3b3]">Стаи</a><a href="#kashtata" class="hover:text-[#e6d3b3]">Къщата</a><a href="#naokolo" class="hover:text-[#e6d3b3]">Наоколо</a><a href="#kontakt" class="hover:text-[#e6d3b3]">Как да стигнете</a>
        </div>
        <a href="#rezervaciya" class="rounded-full bg-white px-5 py-2 text-sm font-bold text-[#1f3a2e] hover:bg-[#e6d3b3]">Резервирай</a>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden bg-[#1f3a2e] text-white">
    <x-demo-photo :slug="$demo['slug']" name="hero" alt="Къщата сред Родопите" class="absolute inset-0 h-full w-full">
        <svg viewBox="0 0 1440 760" preserveAspectRatio="xMidYMid slice" class="h-full w-full" fill="none">
            <defs><linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#f0b97a"/><stop offset=".55" stop-color="#d98a6a"/><stop offset="1" stop-color="#45645a"/></linearGradient></defs>
            <rect width="1440" height="760" fill="url(#sky)"/><circle cx="1010" cy="250" r="70" fill="#fbe3b0"/>
            <path d="M0 420 220 250l180 130 200-190 240 220 190-140 410 190v300H0Z" fill="#5f7d70"/>
            <path d="M0 520 260 380l230 130 260-170 280 190 410-150v380H0Z" fill="#345246"/>
            <path d="M0 640c240-70 420-20 640-50s520-60 800 20v150H0Z" fill="#1f3a2e"/>
            <g transform="translate(930 470)"><path d="M-14 62 80 0l94 62Z" fill="#8a4a2a"/><rect x="6" y="62" width="148" height="86" fill="#f4eee3"/><rect x="6" y="104" width="148" height="44" fill="#6b4a33"/><g fill="#f6c453"><rect x="26" y="74" width="24" height="22"/><rect x="68" y="74" width="24" height="22"/><rect x="110" y="74" width="24" height="22"/></g><rect x="118" y="-6" width="16" height="34" fill="#6b4a33"/></g>
            <g fill="#16291f">@foreach([120, 210, 330, 520, 700, 1180, 1290, 1380] as $i => $x)<path d="M{{ $x }} {{ 700 - ($i % 3) * 24 }}l-34 0 34-{{ 110 + ($i % 2) * 40 }} 34 {{ 110 + ($i % 2) * 40 }}Z"/>@endforeach</g>
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
<section id="stai" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="text-xs font-bold uppercase tracking-[0.3em] text-[#b5683c]">Стаи</p><h2 class="demo-serif mt-3 text-4xl md:text-5xl">Изберете своята стая</h2></div>
            <p class="max-w-sm text-[#5c6b62]">Цените са за нощувка със закуска. Изберете стая и общата сума горе се смята сама.</p>
        </div>
        <div data-pick="room" class="dr mt-12 grid gap-6 lg:grid-cols-3">
            @foreach($rooms as $i => [$name, $meta, $price, $tags, $color])
                <button type="button" data-value="{{ $name }}" data-price="{{ $price }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="group flex flex-col overflow-hidden rounded-3xl bg-white text-left ring-1 ring-[#e2d8c6] transition hover:-translate-y-1 hover:shadow-xl aria-pressed:ring-2 aria-pressed:ring-[#1f3a2e]">
                    <x-demo-photo :slug="$demo['slug']" name="room-{{ $i + 1 }}" alt="{{ $name }}" class="relative block aspect-[4/3] w-full" style="background: linear-gradient(170deg, {{ $color }}55, {{ $color }})">
                        <svg viewBox="0 0 400 300" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none"><rect x="230" y="40" width="130" height="110" fill="#f4eee3" opacity=".9"/><path d="M230 95h130M295 40v110" stroke="{{ $color }}" stroke-width="5"/><path d="M40 300v-90c0-10 8-18 18-18h220c10 0 18 8 18 18v90Z" fill="#f4eee3"/><rect x="40" y="150" width="256" height="56" rx="12" fill="#1d2b24" opacity=".75"/><rect x="62" y="176" width="92" height="36" rx="10" fill="#fff"/><rect x="176" y="176" width="92" height="36" rx="10" fill="#fff"/><path d="M40 244h256" stroke="{{ $color }}" stroke-width="10"/><path d="M330 300v-70m0 0c-14 0-20-12-20-24h40c0 12-6 24-20 24Z" stroke="#1d2b24" stroke-width="4"/></svg>
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
<section id="kashtata" class="scroll-mt-16 bg-[#1f3a2e] px-4 py-20 text-white">
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
                    <svg viewBox="0 0 150 200" class="h-full w-full" fill="none">@if($loop->first)<g fill="#fff">@foreach([[20, 30], [60, 18], [110, 40], [130, 80], [40, 70], [90, 62]] as [$x, $y])<circle cx="{{ $x }}" cy="{{ $y }}" r="1.6"/>@endforeach</g><ellipse cx="75" cy="140" rx="48" ry="12" fill="#6fa8c7"/><path d="M27 140v30c0 8 96 8 96 0v-30" fill="#8a5a3a"/><path d="M60 118c-6-8 6-10 0-18m18 18c-6-8 6-10 0-18m18 18c-6-8 6-10 0-18" stroke="#fff" stroke-opacity=".6" stroke-width="2" stroke-linecap="round"/>@else<ellipse cx="75" cy="120" rx="52" ry="34" fill="#f4eee3"/><circle cx="58" cy="114" r="12" fill="#e9b949"/><circle cx="92" cy="110" r="10" fill="#fff" stroke="#b5683c" stroke-width="2"/><path d="M70 136c8 4 18 4 26-2" stroke="#8a4a2a" stroke-width="4" stroke-linecap="round"/><path d="M118 70c-6-8 6-10 0-18" stroke="#fff" stroke-opacity=".6" stroke-width="2" stroke-linecap="round"/><rect x="108" y="76" width="22" height="20" rx="4" fill="#f4eee3"/>@endif</svg>
                    <span class="absolute bottom-3 left-3 rounded-full bg-black/45 px-3 py-1 text-xs font-semibold">{{ $alt }}</span>
                </x-demo-photo>
            @endforeach
        </div>
    </div>
</section>

{{-- Around --}}
<section id="naokolo" class="scroll-mt-16 px-4 py-20">
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
                <figure class="rounded-3xl bg-white p-7 ring-1 ring-[#e2d8c6]">
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
    })();
</script>
@endpush
