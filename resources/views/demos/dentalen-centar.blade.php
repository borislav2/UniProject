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

@section('content')
<div class="demo-geo">
<div class="bg-[#0b3b44] px-4 py-2 text-center text-sm text-white">
    <span class="mr-2 inline-flex h-2 w-2 rounded-full bg-[#ff7a59]"></span>Спешна болка? Приемаме в същия ден. <a href="tel:+35956000000" data-demo-action class="font-semibold underline underline-offset-2">Обадете се на 056 000 000</a>
</div>
<header class="sticky top-0 z-50 border-b border-[#e3eef0] bg-white/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="flex items-center gap-2 text-xl font-semibold"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0e8f8f] text-white"><svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M7.5 2C4.6 2 3 4.2 3 7c0 2.300.900 3.600 1.500 5.500.500 1.600.600 4.500 1.200 6.800.300 1.200.900 2.700 2 2.700 1.500 0 1.500-2.300 1.800-3.800.300-1.400.900-2.700 2.500-2.700s2.200 1.300 2.500 2.700c.300 1.500.300 3.800 1.800 3.800 1.100 0 1.700-1.500 2-2.700.600-2.300.700-5.200 1.200-6.800.600-1.900 1.500-3.200 1.500-5.500 0-2.800-1.600-5-4.500-5-1.800 0-2.900 1-4.500 1S9.300 2 7.500 2Z"/></svg></span>Бяла усмивка</a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#uslugi" class="hover:text-[#0e8f8f]">Услуги</a><a href="#ekip" class="hover:text-[#0e8f8f]">Лекари</a><a href="#ceni" class="hover:text-[#0e8f8f]">Цени</a><a href="#vaprosi" class="hover:text-[#0e8f8f]">Въпроси</a>
        </div>
        <a href="#chas" class="rounded-full bg-[#0e8f8f] px-5 py-2 text-sm font-semibold text-white hover:bg-[#0b7676]">Запази час</a>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden bg-[linear-gradient(180deg,#eef8f8_0%,#ffffff_100%)]">
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 lg:grid-cols-12 lg:py-20">
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
                <span class="absolute -top-4 right-6 rounded-full bg-[#ff7a59] px-4 py-1.5 text-xs font-semibold text-white">Свободни часове тази седмица</span>
                <h2 class="text-2xl font-semibold">Запазете час за преглед</h2>
                <p class="mt-1 text-sm text-[#48636b]">Връщаме обаждане до един час в работно време.</p>
                <p class="mt-6 text-xs font-semibold uppercase tracking-wider text-[#0e8f8f]">Какво ви води при нас</p>
                <div data-pick="reason" class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach(['Преглед', 'Болка', 'Почистване', 'Имплант', 'Брекети', 'Дете'] as $i => $reason)
                        <button type="button" data-value="{{ $reason }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-xl border border-[#dbe9eb] py-2.5 text-sm font-semibold hover:border-[#0e8f8f] aria-pressed:border-[#0e8f8f] aria-pressed:bg-[#e6f6f5] aria-pressed:text-[#0b6f6f]">{{ $reason }}</button>
                    @endforeach
                </div>
                <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-[#0e8f8f]">Кога ви е удобно</p>
                <div data-pick="when" class="mt-2 grid grid-cols-3 gap-2">
                    @foreach(['Сутрин', 'Следобед', 'След 17 часа'] as $i => $when)
                        <button type="button" data-value="{{ $when }}" aria-pressed="{{ $i === 1 ? 'true' : 'false' }}" class="rounded-xl border border-[#dbe9eb] py-2.5 text-sm font-semibold hover:border-[#0e8f8f] aria-pressed:border-[#0e8f8f] aria-pressed:bg-[#e6f6f5] aria-pressed:text-[#0b6f6f]">{{ $when }}</button>
                    @endforeach
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <input type="text" required placeholder="Име" class="rounded-xl border border-[#dbe9eb] px-4 py-3 focus:border-[#0e8f8f] focus:ring-[#0e8f8f]">
                    <input type="tel" required placeholder="Телефон" class="rounded-xl border border-[#dbe9eb] px-4 py-3 focus:border-[#0e8f8f] focus:ring-[#0e8f8f]">
                </div>
                <button class="mt-4 w-full rounded-xl bg-[#0e8f8f] py-3.5 font-semibold text-white hover:bg-[#0b7676]">Запази час за <span data-pick-out="reason" class="lowercase">преглед</span></button>
                <p class="mt-3 text-center text-xs text-[#6b858c]">Първичният преглед е 20 € и се приспада от лечението.</p>
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
                <article class="overflow-hidden rounded-3xl bg-[#f3f9fa]">
                    <x-demo-photo :slug="$demo['slug']" name="team-{{ $i + 1 }}" alt="{{ $name }}" class="relative aspect-[4/3] w-full" style="background: linear-gradient(160deg, {{ $color }}33, {{ $color }}aa)">
                        <svg viewBox="0 0 200 150" class="absolute inset-0 h-full w-full" fill="none"><circle cx="100" cy="62" r="26" fill="#fff"/><path d="M48 150c0-34 22-56 52-56s52 22 52 56Z" fill="#fff"/><path d="M90 96 100 112 110 96" stroke="{{ $color }}" stroke-width="4" stroke-linejoin="round"/><path d="M74 60c0-18 12-28 26-28s26 10 26 26c-16 2-30-4-36-14-4 8-8 12-16 16Z" fill="#0f2a33"/></svg>
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

{{-- Prices and payments --}}
<section id="ceni" class="scroll-mt-16 bg-[#f3f9fa] px-4 py-20">
    <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <p class="text-sm font-semibold text-[#0e8f8f]">Цени</p>
            <h2 class="mt-3 text-4xl font-extrabold tracking-tight md:text-5xl">Знаете цената предварително</h2>
            <dl class="mt-8 divide-y divide-[#dbe9eb] rounded-3xl bg-white px-6 ring-1 ring-[#dbe9eb]">
                @foreach($prices as [$name, $price])
                    <div class="flex items-center justify-between gap-4 py-4"><dt>{{ $name }}</dt><dd class="shrink-0 font-semibold">{{ $price }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="rounded-3xl bg-[#0e8f8f] p-8 text-white lg:col-span-2" data-installment>
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
            <div class="mt-8 space-y-5">
                @foreach([
                    ['Не бях ходила на зъболекар от осем години от страх. Д-р Маринова ми обясни всичко на снимката и не усетих нищо.', 'Десислава Т.'],
                    ['Синът ми вече сам пита кога ще ходим пак. Получихме план с цени и нямаше нито една изненада.', 'Николай В.'],
                ] as [$quote, $who])
                    <figure class="rounded-3xl border border-[#dbe9eb] p-7">
                        <p class="text-[#f2a93b]" aria-label="5 от 5 звезди">★★★★★</p>
                        <blockquote class="mt-3 text-lg font-light leading-relaxed">„{{ $quote }}“</blockquote>
                        <figcaption class="mt-4 text-sm font-semibold">{{ $who }} <span class="font-light text-[#6b858c]">· отзив в Google</span></figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight md:text-4xl">Често задавани въпроси</h2>
            <div class="mt-8 divide-y divide-[#dbe9eb] border-y border-[#dbe9eb]">
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
