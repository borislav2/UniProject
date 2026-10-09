@extends('demos._layout')
@section('body_class', 'bg-[#111315] text-white')

@section('content')
<header class="sticky top-0 z-50 bg-[#111315]/95 backdrop-blur border-b border-white/10">
    <nav class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="#" class="text-xl font-black uppercase tracking-tight">Мотор<span class="text-[#ff7a1a]">Про</span></a>
        <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-300"><a href="#uslugi" class="hover:text-white">Услуги</a><a href="#ceni" class="hover:text-white">Цени</a><a href="#kontakt" class="hover:text-white">Контакт</a></div>
        <a href="tel:+35982000000" data-demo-action class="inline-flex items-center gap-2 rounded-md bg-[#ff7a1a] px-4 py-2 text-sm font-bold text-black hover:bg-[#ff9447]"><x-icon name="phone" /> Обади се</a>
    </nav>
</header>

<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-[repeating-linear-gradient(135deg,rgba(255,255,255,0.03)_0,rgba(255,255,255,0.03)_2px,transparent_2px,transparent_24px)]"></div>
    <div class="absolute -right-24 top-10 w-[28rem] h-[28rem] rounded-full bg-[#ff7a1a]/20 blur-3xl"></div>
    <div class="relative max-w-6xl mx-auto px-4 py-20 md:py-28">
        <p class="text-sm font-bold uppercase tracking-[0.3em] text-[#ff7a1a]">Автосервиз · Русе</p>
        <h1 class="mt-4 text-5xl md:text-7xl font-black uppercase leading-[0.95] max-w-3xl">Колата ви е<br>в сигурни ръце</h1>
        <p class="mt-6 text-lg text-gray-300 max-w-xl">Компютърна диагностика, ремонт на двигател, ходова част и климатици. Казваме цената преди да започнем.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#kontakt" class="rounded-md bg-[#ff7a1a] px-7 py-3.5 font-bold text-black hover:bg-[#ff9447]">Запази час за сервиз</a>
            <a href="#ceni" class="rounded-md border border-white/30 px-7 py-3.5 font-bold hover:border-white">Цени</a>
        </div>
        <div class="mt-12 flex flex-wrap gap-x-10 gap-y-3 text-sm text-gray-300">
            <span class="flex items-center gap-2"><x-icon name="check" class="text-[#ff7a1a]" /> Гаранция 12 месеца</span>
            <span class="flex items-center gap-2"><x-icon name="check" class="text-[#ff7a1a]" /> Оригинални части</span>
            <span class="flex items-center gap-2"><x-icon name="check" class="text-[#ff7a1a]" /> Ремонт до 24 часа</span>
        </div>
    </div>
</section>

<section id="uslugi" class="bg-[#17191c] py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-black uppercase">Услуги</h2>
        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-white/10 rounded-lg overflow-hidden">
            @foreach([['gauge-high', 'Диагностика', 'Компютърна диагностика на всички марки.'], ['screwdriver-wrench', 'Двигател', 'Ремонт, ангренажни комплекти, масла.'], ['car', 'Ходова част', 'Окачване, спирачки, геометрия.'], ['cogs', 'Климатици', 'Зареждане, дезинфекция, ремонт.']] as [$icon, $t, $d])
                <div class="bg-[#17191c] p-7 hover:bg-[#1e2125] transition-colors"><x-icon :name="$icon" class="text-3xl text-[#ff7a1a]" /><h3 class="mt-5 text-lg font-bold">{{ $t }}</h3><p class="mt-2 text-sm text-gray-400">{{ $d }}</p></div>
            @endforeach
        </div>
    </div>
</section>

<section id="ceni" class="py-16 md:py-20">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-black uppercase">Ориентировъчни цени</h2>
        <div class="mt-8 divide-y divide-white/10 border-y border-white/10">
            @foreach([['Компютърна диагностика', '25 €'], ['Смяна на масло и филтри', 'от 30 € труд'], ['Смяна на накладки (ос)', 'от 35 € труд'], ['Зареждане на климатик', '45 €']] as [$s, $p])
                <div class="flex items-center justify-between py-4"><span class="font-semibold">{{ $s }}</span><span class="font-black text-[#ff7a1a]">{{ $p }}</span></div>
            @endforeach
        </div>
        <p class="mt-4 text-sm text-gray-400">Точната цена казваме след оглед, преди да започнем ремонта.</p>
    </div>
</section>

<section id="kontakt" class="bg-[#ff7a1a] text-black py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-4xl md:text-5xl font-black uppercase leading-none">Запази час</h2>
            <p class="mt-4 font-semibold">Пн – Пт 8:00 – 18:00 · Сб 9:00 – 14:00</p>
            <p class="mt-6 flex items-center gap-3 font-semibold"><x-icon name="location-dot" /> бул. „Липник“ 100, Русе</p>
            <p class="mt-2 flex items-center gap-3 font-semibold"><x-icon name="phone" /> +359 82 000 000</p>
        </div>
        <form data-demo-form class="rounded-lg bg-[#111315] p-6 text-white space-y-4">
            <input class="w-full rounded-md border border-white/15 bg-white/5 px-4 py-3" placeholder="Име">
            <input class="w-full rounded-md border border-white/15 bg-white/5 px-4 py-3" placeholder="Телефон">
            <input class="w-full rounded-md border border-white/15 bg-white/5 px-4 py-3" placeholder="Марка и модел на колата">
            <button class="w-full rounded-md bg-[#ff7a1a] py-3.5 font-bold text-black hover:bg-[#ff9447]">Изпрати</button>
        </form>
    </div>
</section>
@endsection
