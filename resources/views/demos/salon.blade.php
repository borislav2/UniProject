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
            <a href="#uslugi" class="hover:text-[#8a5fa8]">Услуги и цени</a><a href="#ekip" class="hover:text-[#8a5fa8]">Екип</a><a href="#vaucheri" class="hover:text-[#8a5fa8]">Ваучери</a><a href="#kontakt" class="hover:text-[#8a5fa8]">Контакт</a>
        </div>
        <a href="#chas" class="rounded-full bg-[#4a2b4f] px-5 py-2 text-sm font-semibold text-white hover:bg-[#6a3f70]">Запази час</a>
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
                    <ellipse cx="160" cy="165" rx="70" ry="88" fill="#faf5f1"/><path d="M90 165c0-70 30-110 72-110s70 36 70 96c-26-4-52-22-66-52-12 34-40 58-76 66Z" fill="#4a2b4f"/>
                    <path d="M110 400c0-70 20-112 50-112s50 42 50 112Z" fill="#faf5f1"/><path d="M135 188c8 6 14 6 20 0m30 0c-6 6-12 6-20 0" stroke="#4a2b4f" stroke-width="3" stroke-linecap="round"/><path d="M146 222c8 8 20 8 28 0" stroke="#c9736b" stroke-width="4" stroke-linecap="round"/>
                    <g stroke="#faf5f1" stroke-width="2" stroke-linecap="round" opacity=".8"><path d="M270 330V250m0 20c-10-4-14-12-12-20m12 36c10-4 14-12 12-20m-12 40c-10-4-14-12-12-20"/><path d="M44 110V50m0 16c-8-3-11-9-10-16m10 28c8-3 11-9 10-16"/></g>
                </svg>
            </x-demo-photo>
            <div class="absolute bottom-8 left-0 rounded-2xl bg-white p-4 shadow-xl ring-1 ring-[#eadfd8] sm:-left-4">
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#8a5fa8]">Най-близък свободен час</p>
                <p class="mt-1 font-bold">Днес в 16:30 при Виктория</p>
            </div>
        </div>
    </div>
</section>

{{-- Booking --}}
<section id="chas" class="scroll-mt-20 px-4 pb-20">
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
<section id="uslugi" class="scroll-mt-16 bg-white px-4 py-20">
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
                    <li class="flex items-center justify-between gap-4 py-4">
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
                <article>
                    <x-demo-photo :slug="$demo['slug']" name="team-{{ $i + 1 }}" alt="{{ $name }}, {{ $role }}" class="relative aspect-[3/4] w-full overflow-hidden rounded-[2rem] {{ $i % 2 ? 'rounded-tr-[5rem]' : 'rounded-tl-[5rem]' }}" style="background: linear-gradient(170deg, {{ $color }}, #4a2b4f 140%)">
                        <svg viewBox="0 0 120 160" class="absolute inset-0 h-full w-full" fill="none"><circle cx="60" cy="68" r="24" fill="#faf5f1"/><path d="M22 160c0-34 16-54 38-54s38 20 38 54Z" fill="#faf5f1"/><path d="M36 68c0-18 10-28 24-28s24 10 24 24c-14 0-26-8-30-18-4 12-10 18-18 22Z" fill="#3a2440"/></svg>
                    </x-demo-photo>
                    <h3 class="demo-serif mt-4 text-2xl">{{ $name }}</h3>
                    <p class="text-sm text-[#6b5670]">{{ $role }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Vouchers and reviews --}}
<section id="vaucheri" class="scroll-mt-16 px-4 pb-20">
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
                <figure class="flex flex-col rounded-[2rem] bg-white p-8 ring-1 ring-[#eadfd8]">
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

@push('scripts')
<script>
    document.querySelector('[data-pick="service"]').addEventListener('pick', function (e) {
        var button = e.target.querySelector('[aria-pressed="true"]');
        document.querySelector('[data-price]').textContent = button.dataset.price;
        document.querySelector('[data-minutes]').textContent = button.dataset.minutes;
    });
</script>
@endpush
