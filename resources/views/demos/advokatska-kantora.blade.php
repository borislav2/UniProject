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

@section('content')
<header class="sticky top-0 z-50 border-b border-[#e3ddd0] bg-[#f7f4ee]/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="demo-serif text-2xl tracking-wide">Велчев <span class="text-[#b08d57]">&amp;</span> Барова <span class="ml-2 hidden align-middle text-[10px] font-sans font-bold uppercase tracking-[0.25em] text-[#6b7383] sm:inline">Адвокатско дружество</span></a>
        <div class="hidden items-center gap-8 text-sm font-semibold lg:flex">
            <a href="#oblasti" class="hover:text-[#b08d57]">Области</a><a href="#hononari" class="hover:text-[#b08d57]">Хонорари</a><a href="#ekip" class="hover:text-[#b08d57]">Адвокати</a><a href="#vaprosi" class="hover:text-[#b08d57]">Въпроси</a>
        </div>
        <a href="#konsultaciya" class="bg-[#131c2b] px-5 py-2 text-sm font-semibold text-white hover:bg-[#b08d57]">Запазете консултация</a>
    </nav>
</header>

{{-- Hero --}}
<section class="demo-grain relative overflow-hidden bg-[#131c2b] text-white">
    <svg class="absolute right-0 top-0 h-full w-1/2 opacity-[0.07]" viewBox="0 0 400 500" preserveAspectRatio="xMaxYMid slice" fill="none" aria-hidden="true"><g stroke="#fff" stroke-width="2">@foreach(range(0, 6) as $i)<path d="M{{ 60 + $i * 48 }} 120v300"/>@endforeach<path d="M30 120h340L200 30ZM30 420h340M20 450h360"/></g></svg>
    <div class="relative mx-auto grid max-w-7xl gap-12 px-4 py-16 lg:grid-cols-12 lg:py-24">
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
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#b08d57]">С какво можем да помогнем</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($cases as $i => [$case])
                        <button type="button" data-show="case-{{ $i }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="border border-[#d6cebd] px-3.5 py-2 text-sm font-semibold hover:border-[#131c2b] aria-pressed:border-[#131c2b] aria-pressed:bg-[#131c2b] aria-pressed:text-white">{{ $case }}</button>
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
                    <article class="group bg-[#f7f4ee] p-8 transition hover:bg-white">
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
            <ol class="mt-10 space-y-6">
                @foreach([['Първа консултация', 'До един час, в кантората или онлайн. Преглеждаме документите и казваме какви са вариантите.'], ['Писмено становище', 'До три работни дни получавате план, срокове и хонорар черно на бяло.'], ['Работа по случая', 'Имате един адвокат за връзка и получавате известие след всяко действие по делото.']] as $i => [$title, $text])
                    <li class="flex gap-5"><span class="demo-serif text-4xl text-[#b08d57]">{{ $i + 1 }}</span><div><h3 class="font-bold">{{ $title }}</h3><p class="mt-1 leading-relaxed text-[#4a5364]">{{ $text }}</p></div></li>
                @endforeach
            </ol>
        </div>
        <div class="bg-[#131c2b] p-8 text-white md:p-10">
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
                <article>
                    <x-demo-photo :slug="$demo['slug']" name="team-{{ $i + 1 }}" alt="{{ $name }}" class="relative aspect-[4/5] w-full overflow-hidden bg-[linear-gradient(170deg,#d9d2c3,#b9b0a0)]">
                        <svg viewBox="0 0 200 250" class="h-full w-full" fill="none"><circle cx="100" cy="96" r="34" fill="#f7f4ee"/><path d="M26 250c0-56 30-92 74-92s74 36 74 92Z" fill="#131c2b"/><path d="M84 160 100 196 116 160Z" fill="#f7f4ee"/><path d="M97 172h6l4 44-7 10-7-10Z" fill="#b08d57"/></svg>
                    </x-demo-photo>
                    <h3 class="demo-serif mt-5 text-2xl">{{ $name }}</h3>
                    <p class="text-sm font-bold uppercase tracking-widest text-[#b08d57]">{{ $role }}</p>
                    <p class="mt-2 text-[#4a5364]">{{ $text }}</p>
                </article>
            @endforeach
        </div>
        <div class="mt-16 grid gap-6 md:grid-cols-2">
            @foreach([
                ['Купувах първото си жилище и се страхувах от измама. Адвокат Велчев откри възбрана, за която брокерът не знаеше. Спести ми 80 хиляди евро.', 'Клиент по сделка с имот, 2026'],
                ['Преди първата среща знаех колко ще струва всичко. След всяко заседание получавах имейл какво се е случило и какво следва.', 'Клиент по трудов спор, 2025'],
            ] as [$quote, $who])
                <figure class="border-l-2 border-[#b08d57] bg-white p-8"><blockquote class="demo-serif text-xl leading-relaxed">„{{ $quote }}“</blockquote><figcaption class="mt-4 text-sm text-[#6b7383]">{{ $who }}</figcaption></figure>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="vaprosi" class="scroll-mt-16 bg-white px-4 py-20">
    <div class="mx-auto max-w-3xl">
        <h2 class="demo-serif text-center text-4xl md:text-5xl">Преди да се обадите</h2>
        <div class="mt-10 divide-y divide-[#e3ddd0] border-y border-[#e3ddd0]">
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
