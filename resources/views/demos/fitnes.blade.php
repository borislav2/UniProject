@extends('demos._layout')
@section('body_class', 'bg-[#0b0c0e] text-white')

@php
    $days = ['pon' => 'Пон', 'vto' => 'Вто', 'sry' => 'Сря', 'chet' => 'Чет', 'pet' => 'Пет', 'sab' => 'Съб'];
    $classes = [
        ['07:00', 'Сутрешен кросфит', 'Калоян', 60, 3, 'pon sry pet', 'Сила'],
        ['08:30', 'Пилатес на реформер', 'Ирина', 50, 1, 'pon vto chet sab', 'Баланс'],
        ['10:00', 'Йога за гръб', 'Ирина', 60, 6, 'vto chet sab', 'Баланс'],
        ['12:15', 'Обедна кондиция, 40 минути', 'Мартин', 40, 8, 'pon vto sry chet pet', 'Кардио'],
        ['17:30', 'Силова тренировка с щанга', 'Калоян', 60, 0, 'pon sry pet', 'Сила'],
        ['18:30', 'Бокс за начинаещи', 'Мартин', 60, 4, 'vto chet', 'Кардио'],
        ['19:30', 'Функционална тренировка', 'Весела', 55, 2, 'pon vto sry chet pet', 'Сила'],
        ['20:30', 'Стречинг и възстановяване', 'Весела', 45, 9, 'pon sry sab', 'Баланс'],
    ];
    $plans = [
        ['Старт', 'Два пъти седмично', 39, ['8 групови тренировки на месец', 'Свободен достъп до залата до 16 часа', 'Шкафче и душове'], false],
        ['Без лимит', 'Колкото искате', 59, ['Всички групови тренировки', 'Залата по всяко време', 'Измерване на състава на тялото всеки месец', 'Един гост на месец'], true],
        ['С треньор', 'Личен план', 149, ['Всичко от „Без лимит“', '8 персонални тренировки на месец', 'Хранителен режим', 'Връзка с треньора в чат'], false],
    ];
    $coaches = [['Калоян Димов', 'Сила и кросфит', '#c6ff3d'], ['Ирина Пенева', 'Пилатес и йога', '#ff6b4a'], ['Мартин Илиев', 'Бокс и кондиция', '#4ad1ff'], ['Весела Райкова', 'Функционални тренировки', '#b48cff']];
@endphp

@section('content')
<div class="demo-geo">
<header class="sticky top-0 z-50 border-b border-white/10 bg-[#0b0c0e]/90 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="text-2xl font-extrabold uppercase tracking-tight">Пулс<span class="text-[#c6ff3d]">/</span></a>
        <div class="hidden items-center gap-8 text-sm font-semibold md:flex">
            <a href="#grafik" class="hover:text-[#c6ff3d]">График</a><a href="#karti" class="hover:text-[#c6ff3d]">Карти</a><a href="#trenyori" class="hover:text-[#c6ff3d]">Треньори</a><a href="#kontakt" class="hover:text-[#c6ff3d]">Контакт</a>
        </div>
        <a href="#probna" class="rounded-full bg-[#c6ff3d] px-5 py-2 text-sm font-extrabold text-black hover:bg-white">Безплатна тренировка</a>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden">
    <div class="absolute left-1/2 top-0 h-[38rem] w-[38rem] -translate-x-1/4 rounded-full bg-[#c6ff3d]/10 blur-3xl"></div>
    <p class="pointer-events-none absolute -bottom-10 left-0 select-none whitespace-nowrap text-[22vw] font-extrabold uppercase leading-none tracking-tighter text-white/[0.035]" aria-hidden="true">Пулс</p>
    <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-7">
            <p class="inline-flex items-center gap-2 rounded-full border border-white/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-gray-300"><span class="h-2 w-2 rounded-full bg-[#c6ff3d]"></span> София, Лозенец · в залата сега тренират 23 души</p>
            <h1 class="mt-6 text-5xl font-extrabold uppercase leading-[0.95] tracking-tight sm:text-7xl lg:text-8xl">По-силен<br>от <span class="text-[#c6ff3d]">вчера</span></h1>
            <p class="mt-6 max-w-lg text-lg font-light leading-relaxed text-gray-300">Малки групи до 12 души, треньори, които знаят името ви, и план, направен за вашето тяло. Първата тренировка е от нас.</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="#probna" class="rounded-full bg-[#c6ff3d] px-8 py-4 font-extrabold text-black hover:bg-white">Запиши безплатна тренировка</a>
                <a href="#grafik" class="rounded-full border border-white/25 px-8 py-4 font-semibold hover:border-[#c6ff3d] hover:text-[#c6ff3d]">Виж графика</a>
            </div>
            <dl class="mt-12 flex flex-wrap gap-x-12 gap-y-4">
                @foreach([['840', 'активни членове'], ['46', 'тренировки седмично'], ['4,9', 'от 5 в Google']] as [$n, $t])
                    <div><dd class="text-4xl font-extrabold">{{ $n }}</dd><dt class="text-sm font-light text-gray-400">{{ $t }}</dt></div>
                @endforeach
            </dl>
        </div>
        <div class="lg:col-span-5">
            <x-demo-photo :slug="$demo['slug']" name="hero" alt="Тренировка в студио Пулс" class="relative aspect-[4/5] w-full overflow-hidden rounded-[2rem] bg-[linear-gradient(160deg,#1b1e22,#0b0c0e)] ring-1 ring-white/10">
                <svg viewBox="0 0 400 500" class="absolute inset-0 h-full w-full" fill="none">
                    <circle cx="200" cy="230" r="150" stroke="#c6ff3d" stroke-opacity=".25" stroke-width="2"/><circle cx="200" cy="230" r="105" stroke="#c6ff3d" stroke-opacity=".4" stroke-width="2"/>
                    <path d="M40 230h90l22-60 36 130 30-96 20 50 24-24h98" stroke="#c6ff3d" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
                    <g fill="#fff"><rect x="70" y="386" width="260" height="14" rx="7"/><rect x="92" y="352" width="26" height="82" rx="8"/><rect x="282" y="352" width="26" height="82" rx="8"/><rect x="56" y="366" width="26" height="54" rx="8" opacity=".6"/><rect x="318" y="366" width="26" height="54" rx="8" opacity=".6"/></g>
                </svg>
                <div class="absolute left-5 top-5 rounded-2xl bg-black/60 px-4 py-3 backdrop-blur"><p class="text-xs font-light text-gray-300">Следваща тренировка</p><p class="font-semibold">Функционална · 19:30</p><p class="text-xs font-semibold text-[#c6ff3d]">Остават 2 места</p></div>
            </x-demo-photo>
        </div>
    </div>
</section>

{{-- Marquee --}}
<div class="overflow-hidden border-y border-white/10 bg-[#c6ff3d] py-3 text-black">
    <p class="flex gap-10 whitespace-nowrap text-sm font-extrabold uppercase tracking-widest">@foreach(range(1, 3) as $r)<span>Кросфит</span><span>/</span><span>Пилатес</span><span>/</span><span>Бокс</span><span>/</span><span>Йога</span><span>/</span><span>Силови</span><span>/</span><span>Персонални</span><span>/</span>@endforeach</p>
</div>

{{-- Schedule --}}
<section id="grafik" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl" data-group>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div><p class="text-sm font-semibold uppercase tracking-widest text-[#c6ff3d]">График</p><h2 class="mt-3 text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Запази място за секунди</h2></div>
            <div class="demo-scroll flex gap-2">
                @foreach($days as $key => $label)
                    <button type="button" data-show="{{ $key }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="h-14 w-14 shrink-0 rounded-2xl border border-white/15 text-sm font-semibold hover:border-[#c6ff3d] aria-pressed:border-[#c6ff3d] aria-pressed:bg-[#c6ff3d] aria-pressed:text-black">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <ul class="mt-10 space-y-3">
            @foreach($classes as [$time, $name, $coach, $minutes, $left, $on, $type])
                <li data-key="{{ $on }}" class="grid grid-cols-[4.5rem_1fr_auto] items-center gap-4 rounded-2xl bg-white/[0.04] p-4 ring-1 ring-white/10 transition hover:bg-white/[0.07] md:grid-cols-[6rem_1fr_10rem_9rem_auto] md:px-6">
                    <p class="text-2xl font-extrabold tabular-nums">{{ $time }}</p>
                    <div><p class="font-semibold">{{ $name }}</p><p class="text-sm font-light text-gray-400">{{ $minutes }} мин · {{ $coach }}</p></div>
                    <p class="hidden md:block"><span class="rounded-full border border-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-gray-300">{{ $type }}</span></p>
                    <p class="hidden text-sm md:block {{ $left === 0 ? 'text-gray-500' : ($left <= 3 ? 'font-semibold text-[#ff6b4a]' : 'text-gray-300') }}">{{ $left === 0 ? 'Няма места' : ($left === 1 ? 'Остава 1 място' : 'Остават ' . $left . ' места') }}</p>
                    @if($left === 0)
                        <button type="button" data-demo-action class="rounded-full border border-white/20 px-5 py-2 text-sm font-semibold text-gray-300 hover:border-white">В списъка с чакащи</button>
                    @else
                        <button type="button" data-demo-action class="rounded-full bg-white px-5 py-2 text-sm font-extrabold text-black hover:bg-[#c6ff3d]">Запази</button>
                    @endif
                </li>
            @endforeach
        </ul>
        <p class="mt-6 text-sm font-light text-gray-400">Отказ без загуба на посещение до 3 часа преди началото.</p>
    </div>
</section>

{{-- Memberships --}}
<section id="karti" class="scroll-mt-16 bg-[#131518] px-4 py-20">
    <div class="mx-auto max-w-7xl" data-plans>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <h2 class="text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Карти без дребен шрифт</h2>
            <div data-pick="billing" class="inline-flex rounded-full border border-white/15 p-1 text-sm font-semibold">
                <button type="button" data-value="month" aria-pressed="true" class="rounded-full px-5 py-2 aria-pressed:bg-white aria-pressed:text-black">Месечно</button>
                <button type="button" data-value="year" aria-pressed="false" class="rounded-full px-5 py-2 aria-pressed:bg-white aria-pressed:text-black">Годишно <span class="text-[#7fae00]">2 месеца подарък</span></button>
            </div>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            @foreach($plans as [$name, $sub, $price, $items, $top])
                <article class="relative flex flex-col rounded-[2rem] p-8 {{ $top ? 'bg-[#c6ff3d] text-black' : 'bg-white/[0.04] ring-1 ring-white/10' }}">
                    @if($top)<span class="absolute right-6 top-6 rounded-full bg-black px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#c6ff3d]">Най-избирана</span>@endif
                    <h3 class="text-2xl font-extrabold uppercase">{{ $name }}</h3>
                    <p class="{{ $top ? 'text-black/70' : 'text-gray-400' }} font-light">{{ $sub }}</p>
                    <p class="mt-6"><span class="text-6xl font-extrabold tracking-tight" data-plan-price="{{ $price }}">{{ $price }}</span><span class="text-xl font-semibold"> €</span> <span class="{{ $top ? 'text-black/70' : 'text-gray-400' }} text-sm" data-plan-period>на месец</span></p>
                    <ul class="mt-6 flex-1 space-y-3 text-sm">
                        @foreach($items as $item)<li class="flex gap-3"><x-icon name="check" class="mt-1 text-xs {{ $top ? '' : 'text-[#c6ff3d]' }}" />{{ $item }}</li>@endforeach
                    </ul>
                    <a href="#probna" class="mt-8 rounded-full py-3.5 text-center font-extrabold {{ $top ? 'bg-black text-white hover:bg-[#23272b]' : 'bg-white text-black hover:bg-[#c6ff3d]' }}">Започни с безплатна тренировка</a>
                </article>
            @endforeach
        </div>
        <p class="mt-6 text-sm font-light text-gray-400">Без встъпителна такса. Картата се замразява до 30 дни в годината. Приемаме и спортните карти от работодателя.</p>
    </div>
</section>

{{-- Coaches --}}
<section id="trenyori" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="max-w-2xl text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Треньори, не надзиратели</h2>
        <div class="dr mt-12 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach($coaches as $i => [$name, $role, $color])
                <article class="group relative overflow-hidden rounded-[1.75rem] bg-[#131518] ring-1 ring-white/10">
                    <x-demo-photo :slug="$demo['slug']" name="coach-{{ $i + 1 }}" alt="{{ $name }}" class="relative block aspect-[3/4] w-full">
                        <svg viewBox="0 0 150 200" class="h-full w-full" fill="none"><circle cx="75" cy="200" r="110" fill="{{ $color }}" opacity=".16"/><circle cx="75" cy="74" r="26" fill="{{ $color }}"/><path d="M18 200c0-44 24-72 57-72s57 28 57 72Z" fill="{{ $color }}"/><path d="M50 150h50" stroke="#0b0c0e" stroke-width="5" stroke-linecap="round"/></svg>
                    </x-demo-photo>
                    <div class="absolute inset-x-0 bottom-0 bg-[linear-gradient(0deg,#0b0c0e_10%,transparent)] p-5 pt-16"><h3 class="font-extrabold md:text-lg">{{ $name }}</h3><p class="text-sm font-light text-gray-300">{{ $role }}</p></div>
                </article>
            @endforeach
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
            @foreach([
                ['За осем месеца свалих 14 килограма и за пръв път не се отказах. Калоян ми пише, ако пропусна две тренировки.', 'Христо А., 41'],
                ['Запазвам си място от телефона в трамвая. Групите са малки и треньорът поправя всяко движение.', 'Биляна С., 29'],
                ['Дойдох заради болки в кръста. След три месеца пилатес при Ирина не помня кога ме е боляло.', 'Росица Л., 52'],
            ] as [$quote, $who])
                <figure class="rounded-[1.75rem] border border-white/10 p-7"><blockquote class="text-lg font-light leading-relaxed">„{{ $quote }}“</blockquote><figcaption class="mt-4 text-sm font-semibold text-[#c6ff3d]">{{ $who }}</figcaption></figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Trial --}}
<section id="probna" class="scroll-mt-16 px-4 pb-20">
    <div class="mx-auto grid max-w-7xl overflow-hidden rounded-[2rem] bg-[#c6ff3d] text-black lg:grid-cols-2">
        <div class="p-8 md:p-12">
            <h2 class="text-4xl font-extrabold uppercase leading-none tracking-tight md:text-6xl">Първата тренировка е безплатна</h2>
            <p class="mt-5 max-w-md text-lg">Идвате, запознавате се с треньор, правим измерване и тренирате. Без карта и без задължение.</p>
            <ol class="mt-8 space-y-3 font-semibold">
                @foreach(['Избирате какво ви е интересно', 'Обаждаме се да уточним ден и час', 'Носите само маратонки и вода'] as $i => $step)<li class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-black text-sm text-[#c6ff3d]">{{ $i + 1 }}</span>{{ $step }}</li>@endforeach
            </ol>
        </div>
        <form data-demo-form class="grid content-center gap-4 bg-black/[0.07] p-8 md:p-12">
            <div data-pick="goal" class="grid grid-cols-2 gap-2">
                @foreach(['Да отслабна', 'Да заякна', 'Без болки в гърба', 'Да се раздвижа'] as $i => $goal)
                    <button type="button" data-value="{{ $goal }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" class="rounded-xl border-2 border-black/20 py-3 text-sm font-semibold hover:border-black aria-pressed:border-black aria-pressed:bg-black aria-pressed:text-[#c6ff3d]">{{ $goal }}</button>
                @endforeach
            </div>
            <input type="text" required placeholder="Име" class="rounded-xl border-2 border-black/20 bg-white px-4 py-3.5 placeholder-black/50 focus:border-black focus:ring-0">
            <input type="tel" required placeholder="Телефон" class="rounded-xl border-2 border-black/20 bg-white px-4 py-3.5 placeholder-black/50 focus:border-black focus:ring-0">
            <button class="rounded-xl bg-black py-4 font-extrabold uppercase tracking-wide text-[#c6ff3d] hover:bg-[#23272b]">Запиши ме</button>
        </form>
    </div>
</section>

<footer id="kontakt" class="scroll-mt-16 border-t border-white/10 px-4 py-12">
    <div class="mx-auto grid max-w-7xl gap-8 text-sm font-light text-gray-400 md:grid-cols-4">
        <p class="text-2xl font-extrabold uppercase text-white">Пулс<span class="text-[#c6ff3d]">/</span></p>
        <p><strong class="block font-semibold text-white">Адрес</strong>бул. „Черни връх“ 47, София<br>На 4 минути от метростанция „Джеймс Баучер“</p>
        <p><strong class="block font-semibold text-white">Работно време</strong>Делници от 6:30 до 22:00<br>Събота и неделя от 8:00 до 20:00</p>
        <p><strong class="block font-semibold text-white">Връзка</strong><a href="tel:+35920000000" data-demo-action class="hover:text-[#c6ff3d]">02 000 0000</a><br>zdravei@puls.example</p>
    </div>
</footer>
</div>
@endsection

@push('scripts')
<script>
    document.querySelector('[data-pick="billing"]').addEventListener('pick', function (e) {
        var year = e.detail === 'year';
        document.querySelectorAll('[data-plan-price]').forEach(function (el) { el.textContent = year ? Math.round(el.dataset.planPrice * 10 / 12) : el.dataset.planPrice; });
        document.querySelectorAll('[data-plan-period]').forEach(function (el) { el.textContent = year ? 'на месец при годишна карта' : 'на месец'; });
    });
</script>
@endpush
