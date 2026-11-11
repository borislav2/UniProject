@extends('demos._layout')
@section('body_class', 'bg-[#f6efe3] text-[#2a1c14]')

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
            <a href="#menu" class="hover:text-[#c9a15b]">Меню</a><a href="#istoriya" class="hover:text-[#c9a15b]">Историята</a><a href="#sabitiya" class="hover:text-[#c9a15b]">Събития</a><a href="#kontakt" class="hover:text-[#c9a15b]">Контакт</a>
        </div>
        <a href="#rezervaciya" class="rounded-full bg-[#c9a15b] px-5 py-2 font-sans text-sm font-bold text-[#17100c] hover:bg-[#dcb875]">Запази маса</a>
    </nav>
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
                    <circle cx="160" cy="250" r="96" fill="#f6efe3"/><circle cx="160" cy="250" r="72" stroke="#c9a15b" stroke-width="1.5"/>
                    <path d="M118 262c14-34 62-44 86-14-8 30-58 44-86 14Z" fill="#8c2332"/><circle cx="182" cy="238" r="9" fill="#5d7a3a"/><circle cx="142" cy="236" r="6" fill="#c9a15b"/><path d="M150 268c10 6 26 6 38-2" stroke="#f6efe3" stroke-width="3" stroke-linecap="round"/>
                    <path d="M262 150v70m0-70c-12 0-18 16-18 30s8 22 18 22 18-8 18-22-6-30-18-30Zm0 70v60m-16 0h32" stroke="#c9a15b" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M40 180v150M30 180v40c0 10 20 10 20 0v-40" stroke="#d8cbb8" stroke-width="3" stroke-linecap="round"/>
                    <path d="M160 40c-30 18-30 52 0 66 30-14 30-48 0-66Z" stroke="#c9a15b" stroke-width="1.5"/><path d="M160 40v66" stroke="#c9a15b" stroke-width="1.5"/>
                </svg>
            </x-demo-photo>
            <div class="absolute -bottom-6 left-0 max-w-[15rem] rounded-2xl bg-[#f6efe3] p-4 font-sans text-[#2a1c14] shadow-2xl sm:-left-6">
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
                    <label class="text-[11px] font-bold uppercase tracking-widest text-[#8c2332]">Час <span class="font-medium normal-case tracking-normal text-[#8a7663]">(зачеркнатите са заети)</span></label>
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
            <x-demo-photo :slug="$demo['slug']" name="story-1" alt="Пещта на дърва" class="aspect-[3/4] w-full rounded-t-full bg-[linear-gradient(160deg,#c9a15b,#8a5a2b)]"><svg viewBox="0 0 120 160" class="h-full w-full" fill="none"><path d="M30 130V86a30 30 0 0 1 60 0v44Z" fill="#17100c"/><path d="M60 122c-14-10-8-24 0-34 8 10 14 24 0 34Z" fill="#e2622c"/><path d="M60 122c-6-5-4-12 0-17 4 5 6 12 0 17Z" fill="#f6c453"/></svg></x-demo-photo>
            <x-demo-photo :slug="$demo['slug']" name="story-2" alt="Лозето край Брестовица" class="mt-10 aspect-[3/4] w-full rounded-t-full bg-[linear-gradient(160deg,#5d7a3a,#2c3d1c)]"><svg viewBox="0 0 120 160" class="h-full w-full" fill="none"><g fill="#6b1f2b"><circle cx="52" cy="84" r="9"/><circle cx="68" cy="84" r="9"/><circle cx="60" cy="98" r="9"/><circle cx="44" cy="98" r="9"/><circle cx="76" cy="98" r="9"/><circle cx="52" cy="112" r="9"/><circle cx="68" cy="112" r="9"/><circle cx="60" cy="126" r="9"/></g><path d="M60 74V52m0 8c10-10 22-8 26 0-8 8-18 8-26 0Z" stroke="#f6efe3" stroke-width="2.5" stroke-linecap="round"/></svg></x-demo-photo>
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
    <div class="mx-auto max-w-5xl" data-group>
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
                <article class="flex flex-col rounded-3xl border border-[#dccfbb] bg-white/60 p-7">
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
    <div class="mx-auto max-w-6xl">
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
                <figure class="rounded-3xl bg-white/10 p-7 ring-1 ring-white/15">
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
    <div class="mx-auto grid max-w-6xl gap-10 font-sans md:grid-cols-2">
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
