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
            <a href="#imoti" class="hover:text-[#ff6b4a]">Имоти</a><a href="#kredit" class="hover:text-[#ff6b4a]">Кредитен калкулатор</a><a href="#ocenka" class="hover:text-[#ff6b4a]">Продавам имот</a><a href="#ekip" class="hover:text-[#ff6b4a]">Брокери</a>
        </div>
        <a href="tel:+35932000000" data-demo-action class="inline-flex items-center gap-2 rounded-full bg-[#ff6b4a] px-5 py-2 text-sm font-semibold text-white hover:bg-[#e85a3a]"><x-icon name="phone" /> 032 000 000</a>
    </nav>
</header>

{{-- Hero with search --}}
<section class="relative overflow-hidden bg-[#0f3d3e] text-white">
    <svg viewBox="0 0 1440 360" preserveAspectRatio="xMidYMax slice" class="absolute inset-x-0 bottom-0 h-3/5 w-full opacity-30" fill="#1c5a5b" aria-hidden="true">
        <path d="M0 360V230h70v-60h90v60h50V120h110v110h60v-70h80v70h70V60h130v170h50v-90h100v90h80V150h90v80h60V90h120v140h70v-50h80v50h130v130Z"/>
        <g fill="#0f3d3e">@foreach(range(0, 26) as $i)<rect x="{{ 90 + $i * 48 }}" y="{{ 250 + ($i % 4) * 14 }}" width="14" height="18"/>@endforeach</g>
    </svg>
    <div class="relative mx-auto max-w-7xl px-4 pb-32 pt-16 lg:pt-24">
        <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-semibold"><span class="text-[#ffd27a]">★ 4,9</span> от 380 отзива · Пловдив и областта</p>
        <h1 class="mt-6 max-w-4xl text-5xl font-extrabold leading-[1.03] tracking-tight sm:text-6xl lg:text-7xl">Намерете дома, в който <span class="text-[#ff8a6e]">ще останете</span></h1>
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
<section id="imoti" class="scroll-mt-16 px-4 py-20">
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
                                @if(str_contains($keys, 'kashta'))<path d="M90 170 200 90l110 80Z" fill="#0f2b2c"/><rect x="110" y="170" width="180" height="110" fill="#fff"/><rect x="180" y="210" width="40" height="70" fill="{{ $color }}"/><rect x="130" y="196" width="34" height="34" fill="{{ $color }}" opacity=".6"/><rect x="236" y="196" width="34" height="34" fill="{{ $color }}" opacity=".6"/><path d="M0 280h400v20H0Z" fill="#5a7f4a"/>
                                @else<rect x="110" y="50" width="180" height="250" fill="#fff"/>@foreach(range(0, 4) as $row)@foreach(range(0, 2) as $col)<rect x="{{ 130 + $col * 52 }}" y="{{ 72 + $row * 44 }}" width="36" height="26" fill="{{ $color }}" opacity="{{ ($row + $col) % 3 ? '.55' : '.95' }}"/>@endforeach<path d="M124 {{ 104 + $row * 44 }}h152" stroke="#0f2b2c" stroke-width="3" opacity=".5"/>@endforeach<rect x="50" y="150" width="60" height="150" fill="#fff" opacity=".6"/><rect x="290" y="110" width="70" height="190" fill="#fff" opacity=".6"/>@endif
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
<section class="bg-[#eef3f1] px-4 py-20">
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
<section id="kredit" class="scroll-mt-16 px-4 py-20">
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
<section id="ocenka" class="scroll-mt-16 px-4 pb-20">
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

{{-- Agents --}}
<section id="ekip" class="scroll-mt-16 bg-[#eef3f1] px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="text-4xl font-extrabold tracking-tight md:text-5xl">Брокерите</h2>
        <div class="dr mt-12 grid gap-6 md:grid-cols-3">
            @foreach($agents as $i => [$name, $role, $deals, $color])
                <article class="flex items-center gap-5 rounded-3xl bg-white p-6">
                    <x-demo-photo :slug="$demo['slug']" name="agent-{{ $i + 1 }}" alt="{{ $name }}" class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl" style="background: {{ $color }}33">
                        <svg viewBox="0 0 100 100" class="h-full w-full" fill="none"><circle cx="50" cy="40" r="17" fill="{{ $color }}"/><path d="M14 100c0-24 15-40 36-40s36 16 36 40Z" fill="{{ $color }}"/></svg>
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
