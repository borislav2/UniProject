@extends('demos._layout')
@section('body_class', 'bg-[#f5f0e8] text-[#1c2730]')

@php
    $materials = [['Керемиди', 62, 'Керамични или бетонови, живот над 50 години'], ['Метални керемиди', 38, 'Леки, бърз монтаж, гаранция 30 години'], ['Битумни керемиди', 34, 'За сложни форми и малък наклон'], ['Хидроизолация на плосък покрив', 29, 'Два слоя мембрана с посипка']];
    $projects = [
        ['Къща в с. Богомилово', 'Нов покрив с керамични керемиди, 186 кв. м', '9 работни дни', '#c8552d'],
        ['Блок в кв. Железник', 'Хидроизолация на плосък покрив, 420 кв. м', '6 работни дни', '#41586a'],
        ['Склад в индустриалната зона', 'Термопанели и улуци, 1 150 кв. м', '14 работни дни', '#7a8a5a'],
    ];
@endphp

@section('content')
<div class="demo-geo">
<header class="sticky top-0 z-50 bg-[#1c2730] text-white">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="flex items-center gap-2 text-xl font-extrabold"><svg viewBox="0 0 40 28" class="h-7 w-10" fill="none"><path d="M2 26 20 4l18 22" stroke="#e8703f" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/><path d="M11 26 20 15l9 11" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>Здрав покрив</a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#kalkulator" class="hover:text-[#e8703f]">Калкулатор</a><a href="#uslugi" class="hover:text-[#e8703f]">Услуги</a><a href="#obekti" class="hover:text-[#e8703f]">Обекти</a><a href="#garanciya" class="hover:text-[#e8703f]">Гаранция</a>
        </div>
        <a href="#ogled" class="rounded-md bg-[#e8703f] px-5 py-2 text-sm font-extrabold hover:bg-[#f08556]">Безплатен оглед</a>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden bg-[#1c2730] text-white">
    <svg viewBox="0 0 1440 520" preserveAspectRatio="xMidYMax slice" class="absolute inset-0 h-full w-full" fill="none" aria-hidden="true">
        <path d="M620 520 1040 150l500 370Z" fill="#26343f"/><path d="M760 520 1040 270l330 250Z" fill="#c8552d"/>
        <g stroke="#1c2730" stroke-opacity=".35" stroke-width="3"><path d="M820 470h470M870 420h370M925 370h260M980 320h150"/></g>
        <path d="M1100 250v-90h60v140" fill="#26343f"/><circle cx="1260" cy="110" r="46" fill="#f2c14e" opacity=".9"/>
    </svg>
    <div class="relative mx-auto max-w-7xl px-4 py-16 lg:py-24">
        <div class="max-w-2xl">
            <p class="inline-flex items-center gap-2 rounded-md bg-white/10 px-3 py-1.5 text-sm font-semibold"><span class="text-[#f2c14e]">★ 4,9</span> от 214 отзива · Стара Загора и областта</p>
            <h1 class="mt-6 text-5xl font-extrabold leading-[1.03] tracking-tight sm:text-6xl lg:text-7xl">Покрив, за който <span class="text-[#e8703f]">не мислите</span> следващите 30 години</h1>
            <p class="mt-6 max-w-xl text-lg font-light leading-relaxed text-[#c9d3da]">Нови покриви, ремонт на течове и хидроизолация. Собствени бригади, договор с крайна цена и писмена гаранция до 15 години за труда.</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="#ogled" class="rounded-md bg-[#e8703f] px-8 py-4 font-extrabold hover:bg-[#f08556]">Заявете безплатен оглед</a>
                <a href="#kalkulator" class="rounded-md border border-white/30 px-8 py-4 font-semibold hover:bg-white/10">Сметнете цената</a>
            </div>
        </div>
        <dl class="mt-14 grid max-w-3xl grid-cols-2 gap-6 border-t border-white/15 pt-8 sm:grid-cols-4">
            @foreach([['1 380', 'покрива от 2006'], ['15 години', 'гаранция за труда'], ['48 часа', 'до оглед на място'], ['0 €', 'аванс преди договор']] as [$n, $t])
                <div><dd class="text-3xl font-extrabold">{{ $n }}</dd><dt class="mt-1 text-sm font-light text-[#9fb0bc]">{{ $t }}</dt></div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Urgent strip --}}
<div class="bg-[#e8703f] px-4 py-4 text-white">
    <p class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-4 gap-y-1 text-center font-semibold"><x-icon name="triangle-exclamation" /> Тече в момента? Аварийна бригада идва до 3 часа, и в почивните дни. <a href="tel:+35942000000" data-demo-action class="underline underline-offset-4">042 000 000</a></p>
</div>

{{-- Calculator --}}
<section id="kalkulator" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-12" data-roof>
        <div class="lg:col-span-5">
            <p class="text-sm font-extrabold uppercase tracking-widest text-[#c8552d]">Калкулатор</p>
            <h2 class="mt-3 text-4xl font-extrabold tracking-tight md:text-5xl">Колко струва вашият покрив</h2>
            <p class="mt-5 font-light leading-relaxed text-[#4c5b66]">Ориентировъчна цена с материалите, труда, скелето и извозването на стария покрив. Точната оферта получавате след оглед, който е безплатен.</p>
            <ul class="mt-8 space-y-3 text-sm">
                @foreach(['Цената в договора не се променя', 'Плащане на три етапа, без аванс преди договор', 'Материали с фабрична гаранция'] as $point)
                    <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#c8552d] text-xs text-white"><x-icon name="check" /></span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-black/5 md:p-9 lg:col-span-7">
            <p class="text-xs font-extrabold uppercase tracking-widest text-[#6b7a85]">Покритие</p>
            <div data-pick="material" class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach($materials as $i => [$name, $price, $note])
                    <button type="button" data-value="{{ $name }}" data-price="{{ $price }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-xl border-2 border-[#e6ded2] p-4 text-left hover:border-[#c8552d] aria-pressed:border-[#c8552d] aria-pressed:bg-[#fbeee8]">
                        <span class="block font-extrabold">{{ $name }}</span><span class="mt-1 block text-sm font-light text-[#4c5b66]">{{ $note }}</span>
                    </button>
                @endforeach
            </div>
            <label class="mt-8 flex items-baseline justify-between"><span class="text-xs font-extrabold uppercase tracking-widest text-[#6b7a85]">Площ на покрива</span><span class="text-2xl font-extrabold"><span data-area>140</span> кв. м</span></label>
            <input type="range" min="40" max="400" step="5" value="140" data-range class="mt-3 w-full accent-[#c8552d]" aria-label="Площ на покрива">
            <label class="mt-6 flex cursor-pointer items-center gap-3 text-sm"><input type="checkbox" data-old checked class="h-5 w-5 rounded border-[#cfc5b6] text-[#c8552d] focus:ring-[#c8552d]"> Сваляне и извозване на стария покрив</label>
            <div class="mt-8 flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-[#1c2730] p-6 text-white">
                <div><p class="text-sm font-light text-[#9fb0bc]">Ориентировъчна цена</p><p class="text-4xl font-extrabold sm:text-5xl"><span data-from>9 100</span> до <span data-to>10 900</span> €</p></div>
                <a href="#ogled" class="rounded-md bg-[#e8703f] px-6 py-3 font-extrabold hover:bg-[#f08556]">Искам точна оферта</a>
            </div>
        </div>
    </div>
</section>

{{-- Services --}}
<section id="uslugi" class="scroll-mt-16 bg-white px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="max-w-2xl text-4xl font-extrabold tracking-tight md:text-5xl">От един счупен улук до цял нов покрив</h2>
        <div class="dr mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['Нов покрив', 'Конструкция, изолация, покритие и обшивки, до ключ.', 'home'],
                ['Ремонт на течове', 'Намираме причината с термокамера и поправяме само нужното.', 'screwdriver-wrench'],
                ['Хидроизолация', 'Плоски покриви, тераси и гаражи с мембрана на два слоя.', 'shield-halved'],
                ['Улуци и обшивки', 'Олуци, водосточни тръби, комини и капандури.', 'hammer'],
            ] as [$name, $text, $icon])
                <article class="rounded-2xl border border-[#e6ded2] p-7 transition hover:-translate-y-1 hover:border-[#c8552d] hover:shadow-lg">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#1c2730] text-xl text-[#e8703f]"><x-icon :name="$icon" /></span>
                    <h3 class="mt-5 text-xl font-extrabold">{{ $name }}</h3>
                    <p class="mt-2 font-light leading-relaxed text-[#4c5b66]">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Projects with before / after --}}
<section id="obekti" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <h2 class="text-4xl font-extrabold tracking-tight md:text-5xl">Завършени обекти</h2>
            <p class="font-light text-[#4c5b66]">Плъзнете, за да видите преди и след.</p>
        </div>
        <div class="dr mt-12 grid gap-6 md:grid-cols-3">
            @foreach($projects as $i => [$name, $text, $days, $color])
                <article class="overflow-hidden rounded-3xl bg-white ring-1 ring-[#e6ded2]">
                    <div class="relative aspect-[4/3] select-none overflow-hidden" data-compare style="--pos: 50%">
                        <x-demo-photo :slug="$demo['slug']" name="after-{{ $i + 1 }}" alt="{{ $name }} след ремонта" class="absolute inset-0 h-full w-full bg-[#cfe3f0]">
                            <svg viewBox="0 0 400 300" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none"><path d="M40 190 200 70l160 120Z" fill="{{ $color }}"/><g stroke="#fff" stroke-opacity=".35" stroke-width="3"><path d="M84 160h232M124 130h152M164 100h72"/></g><path d="M70 190h260v110H70Z" fill="#f5f0e8"/><path d="M110 220h50v50h-50Zm130 0h50v80h-50Z" fill="#1c2730" opacity=".8"/></svg>
                        </x-demo-photo>
                        <div class="absolute inset-0 overflow-hidden" style="width: var(--pos)">
                            <x-demo-photo :slug="$demo['slug']" name="before-{{ $i + 1 }}" alt="{{ $name }} преди ремонта" class="absolute inset-y-0 left-0 h-full bg-[#b9bfc2]" data-compare-before>
                                <svg viewBox="0 0 400 300" class="h-full w-full" preserveAspectRatio="xMidYMid slice" fill="none"><path d="M40 190 200 70l160 120Z" fill="#6f6a63"/><path d="M120 150l30 14 20-30 34 26 22-18" stroke="#3a3632" stroke-width="5"/><circle cx="250" cy="150" r="14" fill="#3a3632"/><path d="M70 190h260v110H70Z" fill="#d8d2c8"/><path d="M110 220h50v50h-50Zm130 0h50v80h-50Z" fill="#1c2730" opacity=".6"/><path d="M300 190v70" stroke="#5b6f55" stroke-width="8" opacity=".6"/></svg>
                            </x-demo-photo>
                        </div>
                        <span class="pointer-events-none absolute inset-y-0 w-1 -translate-x-1/2 bg-white shadow" style="left: var(--pos)"><span class="absolute left-1/2 top-1/2 flex h-9 w-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white text-xs font-extrabold text-[#1c2730] shadow-lg">↔</span></span>
                        <span class="pointer-events-none absolute left-3 top-3 rounded bg-black/60 px-2 py-0.5 text-xs font-semibold text-white">Преди</span><span class="pointer-events-none absolute right-3 top-3 rounded bg-[#e8703f] px-2 py-0.5 text-xs font-semibold text-white">След</span>
                        <input type="range" min="0" max="100" value="50" aria-label="Преди и след, {{ $name }}" class="absolute inset-0 h-full w-full cursor-ew-resize opacity-0">
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-extrabold">{{ $name }}</h3>
                        <p class="mt-1 font-light text-[#4c5b66]">{{ $text }}</p>
                        <p class="mt-3 text-sm font-semibold text-[#c8552d]">Готов за {{ $days }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Guarantee and process --}}
<section id="garanciya" class="scroll-mt-16 bg-[#1c2730] px-4 py-20 text-white">
    <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-2">
        <div>
            <p class="text-sm font-extrabold uppercase tracking-widest text-[#e8703f]">Гаранция</p>
            <h2 class="mt-3 text-4xl font-extrabold tracking-tight md:text-5xl">Написано в договора, не казано на ръка</h2>
            <dl class="mt-10 grid grid-cols-2 gap-px overflow-hidden rounded-2xl bg-white/15">
                @foreach([['15 години', 'за труда при нов покрив'], ['30 години', 'фабрична гаранция за покритието'], ['5 години', 'за ремонт и хидроизолация'], ['Всяка пролет', 'безплатен преглед през първите 3 години']] as [$n, $t])
                    <div class="bg-[#1c2730] p-6"><dd class="text-2xl font-extrabold text-[#e8703f]">{{ $n }}</dd><dt class="mt-1 font-light text-[#c9d3da]">{{ $t }}</dt></div>
                @endforeach
            </dl>
        </div>
        <ol class="space-y-6">
            @foreach([
                ['Оглед и замерване', 'Идваме до 48 часа, качваме се на покрива и снимаме с дрон.'],
                ['Оферта с крайна цена', 'До два работни дни получавате оферта по пера, с материали и срок.'],
                ['Договор и график', 'Подписваме договор. Работим без прекъсване, докато не свършим.'],
                ['Приемане и гаранционна карта', 'Оглеждаме заедно, почистваме двора и получавате гаранцията.'],
            ] as $i => [$title, $text])
                <li class="flex gap-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-[#e8703f] text-lg font-extrabold text-[#e8703f]">{{ $i + 1 }}</span>
                    <div><h3 class="text-xl font-extrabold">{{ $title }}</h3><p class="mt-1 font-light leading-relaxed text-[#c9d3da]">{{ $text }}</p></div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Inspection form --}}
<section id="ogled" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto grid max-w-6xl overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-black/5 lg:grid-cols-5">
        <div class="bg-[#c8552d] p-8 text-white md:p-10 lg:col-span-2">
            <h2 class="text-3xl font-extrabold tracking-tight md:text-4xl">Безплатен оглед до 48 часа</h2>
            <p class="mt-4 font-light text-[#fbe3d9]">Без задължение. Ако покривът може да се поправи, без да се сменя, ще ви го кажем.</p>
            <figure class="mt-10 border-t border-white/30 pt-6">
                <blockquote class="text-lg font-light leading-relaxed">„Три фирми ми казаха, че трябва нов покрив. Тук смениха 40 керемиди и обшивката на комина. Две зими няма капка.“</blockquote>
                <figcaption class="mt-3 text-sm font-semibold">Стоян Р., кв. Три чучура</figcaption>
            </figure>
            <a href="tel:+35942000000" data-demo-action class="mt-8 inline-flex items-center gap-2 text-xl font-extrabold"><x-icon name="phone" /> 042 000 000</a>
        </div>
        <form data-demo-form class="grid gap-4 p-8 md:p-10 lg:col-span-3">
            <div data-pick="need" class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach(['Нов покрив', 'Теч', 'Хидроизолация', 'Улуци'] as $i => $need)
                    <button type="button" data-value="{{ $need }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-lg border-2 border-[#e6ded2] py-2.5 text-sm font-semibold hover:border-[#c8552d] aria-pressed:border-[#c8552d] aria-pressed:bg-[#fbeee8]">{{ $need }}</button>
                @endforeach
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <input type="text" required placeholder="Име" class="rounded-lg border border-[#d9cfc0] px-4 py-3 focus:border-[#c8552d] focus:ring-[#c8552d]">
                <input type="tel" required placeholder="Телефон" class="rounded-lg border border-[#d9cfc0] px-4 py-3 focus:border-[#c8552d] focus:ring-[#c8552d]">
            </div>
            <input type="text" placeholder="Населено място и адрес" class="rounded-lg border border-[#d9cfc0] px-4 py-3 focus:border-[#c8552d] focus:ring-[#c8552d]">
            <label class="flex cursor-pointer items-center gap-4 rounded-lg border-2 border-dashed border-[#d9cfc0] p-4 hover:border-[#c8552d]">
                <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#f5f0e8] text-[#c8552d]"><x-icon name="image" /></span>
                <span><span class="block font-semibold">Добавете снимка на покрива</span><span class="text-sm font-light text-[#4c5b66]">По желание. Помага ни да дойдем подготвени.</span></span>
                <input type="file" accept="image/*" class="sr-only">
            </label>
            <button class="rounded-lg bg-[#1c2730] py-4 font-extrabold text-white hover:bg-[#c8552d]">Заявете оглед</button>
        </form>
    </div>
</section>

<footer class="bg-[#141c22] px-4 py-10 text-sm font-light text-[#9fb0bc]">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4">
        <p class="font-extrabold text-white">Здрав покрив</p>
        <p>бул. „Патриарх Евтимий“ 90, Стара Загора · Понеделник до събота, 8:00 до 18:00</p>
    </div>
</footer>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var box = document.querySelector('[data-roof]'), range = box.querySelector('[data-range]'), old = box.querySelector('[data-old]'), price = 62;
        function money(n) { return (Math.round(n / 100) * 100).toLocaleString('bg-BG').replace(/ /g, ' '); }
        function draw() {
            var base = range.value * (price + (old.checked ? 6 : 0));
            box.querySelector('[data-area]').textContent = range.value;
            box.querySelector('[data-from]').textContent = money(base * 0.95);
            box.querySelector('[data-to]').textContent = money(base * 1.15);
        }
        range.addEventListener('input', draw); old.addEventListener('change', draw);
        box.querySelector('[data-pick]').addEventListener('pick', function (e) { price = Number(e.target.querySelector('[aria-pressed="true"]').dataset.price); draw(); });
        draw();

        document.querySelectorAll('[data-compare]').forEach(function (compare) {
            var before = compare.querySelector('[data-compare-before]');
            function size() { before.style.width = compare.offsetWidth + 'px'; }
            size(); window.addEventListener('resize', size);
            compare.querySelector('input').addEventListener('input', function (e) { compare.style.setProperty('--pos', e.target.value + '%'); });
        });
    })();
</script>
@endpush
