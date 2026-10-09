@extends('demos._layout')
@section('body_class', 'bg-[#fbf7f0] text-[#2b1d14]')

@section('content')
<header class="sticky top-0 z-50 bg-[#fbf7f0]/90 backdrop-blur border-b border-[#e8dccb]">
    <nav class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="#" class="demo-serif text-2xl font-bold text-[#7a1f2b]">Лозата</a>
        <div class="hidden md:flex items-center gap-8 text-sm font-semibold">
            <a href="#menu" class="hover:text-[#7a1f2b]">Меню</a><a href="#za-nas" class="hover:text-[#7a1f2b]">За нас</a><a href="#kontakt" class="hover:text-[#7a1f2b]">Контакт</a>
        </div>
        <a href="#rezervaciya" class="rounded-full bg-[#7a1f2b] px-5 py-2 text-sm font-semibold text-white hover:bg-[#5e1520]">Резервирай маса</a>
    </nav>
</header>

<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,#f1d9b8_0%,transparent_55%)]"></div>
    <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <p class="uppercase tracking-[0.25em] text-xs font-bold text-[#b0763a]">Пловдив · Стария град</p>
            <h1 class="demo-serif mt-4 text-5xl md:text-6xl font-bold leading-[1.05]">Българска кухня с вкус на дом</h1>
            <p class="mt-6 text-lg text-[#5a4636] max-w-md">Пресни продукти от местни ферми, домашен хляб от фурна на дърва и вино от нашите лозя.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#rezervaciya" class="rounded-full bg-[#7a1f2b] px-7 py-3 font-semibold text-white hover:bg-[#5e1520]">Резервирай маса</a>
                <a href="#menu" class="rounded-full border-2 border-[#7a1f2b] px-7 py-3 font-semibold text-[#7a1f2b] hover:bg-[#7a1f2b] hover:text-white">Виж менюто</a>
            </div>
            <p class="mt-6 text-sm text-[#5a4636]">Всеки ден 12:00 – 23:00 · ★★★★★ 4,8 от 320 отзива</p>
        </div>
        <div class="relative">
            <div class="aspect-square max-w-md mx-auto rounded-full bg-gradient-to-br from-[#7a1f2b] to-[#3d0f16] flex items-center justify-center shadow-2xl">
                <div class="w-3/4 aspect-square rounded-full border-4 border-dashed border-[#f1d9b8]/50 flex flex-col items-center justify-center text-center text-[#f1d9b8]">
                    <x-icon name="utensils" class="text-6xl" />
                    <p class="demo-serif mt-4 text-2xl font-bold">Ястие на деня</p>
                    <p class="mt-1 text-sm">Агнешко с пресни картофи</p>
                    <p class="demo-serif mt-3 text-3xl font-bold text-white">18,90 €</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="menu" class="bg-white py-16 md:py-20">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="demo-serif text-center text-4xl font-bold">Нашето меню</h2>
        <p class="mt-3 text-center text-[#5a4636]">Част от любимите ни ястия. Цялото меню сменяме всеки сезон.</p>
        @php($menu = ['Предястия' => [['Шопска салата', 'домати, краставици, печени чушки, сирене', '7,50'], ['Сач с гъби', 'гъби, лук, масло и чубрица', '9,90']], 'Основни' => [['Чомлек', 'телешко, арпаджик, червено вино', '16,90'], ['Пъстърва на скара', 'с лимон и зеленчуци', '15,50']], 'Десерти' => [['Баклава', 'домашна, с орехи и мед', '5,90'], ['Тиквеник', 'с канела и пудра захар', '4,90']]])
        <div class="mt-12 grid md:grid-cols-3 gap-10">
            @foreach($menu as $group => $items)
                <div>
                    <h3 class="demo-serif text-xl font-bold text-[#7a1f2b] border-b-2 border-[#f1d9b8] pb-2">{{ $group }}</h3>
                    <ul class="mt-4 space-y-5">
                        @foreach($items as [$dish, $desc, $price])
                            <li><div class="flex justify-between gap-3 font-semibold"><span>{{ $dish }}</span><span class="text-[#7a1f2b]">{{ $price }} €</span></div><p class="text-sm text-[#5a4636]">{{ $desc }}</p></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="za-nas" class="py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-6 text-center">
        @foreach([['Семейна рецепта', 'Готвим по рецепти, предавани три поколения.'], ['Местни продукти', 'Зеленчуци и месо от ферми около Пловдив.'], ['Градина за 60 души', 'Лятна градина под лозите, идеална за празници.']] as [$t, $d])
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-[#e8dccb]"><h3 class="demo-serif text-xl font-bold">{{ $t }}</h3><p class="mt-2 text-[#5a4636]">{{ $d }}</p></div>
        @endforeach
    </div>
</section>

<section id="rezervaciya" class="bg-[#7a1f2b] text-white py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center" id="kontakt">
        <div>
            <h2 class="demo-serif text-4xl font-bold">Запазете маса</h2>
            <p class="mt-3 text-[#f1d9b8]">Обаждаме се да потвърдим до 30 минути.</p>
            <ul class="mt-8 space-y-3">
                <li class="flex items-center gap-3"><x-icon name="location-dot" class="text-[#f1d9b8]" /> ул. „Съборна“ 12, Пловдив</li>
                <li class="flex items-center gap-3"><x-icon name="phone" class="text-[#f1d9b8]" /> +359 32 000 000</li>
                <li class="flex items-center gap-3"><x-icon name="envelope" class="text-[#f1d9b8]" /> office@example.com</li>
            </ul>
        </div>
        <form data-demo-form class="rounded-2xl bg-white p-6 text-[#2b1d14] shadow-2xl grid sm:grid-cols-2 gap-4">
            <input class="sm:col-span-2 rounded-lg border border-[#e8dccb] px-4 py-3" placeholder="Име">
            <input class="rounded-lg border border-[#e8dccb] px-4 py-3" placeholder="Телефон">
            <input class="rounded-lg border border-[#e8dccb] px-4 py-3" placeholder="Брой гости">
            <input class="sm:col-span-2 rounded-lg border border-[#e8dccb] px-4 py-3" placeholder="Дата и час">
            <button class="sm:col-span-2 rounded-full bg-[#7a1f2b] py-3 font-semibold text-white hover:bg-[#5e1520]">Изпрати резервация</button>
        </form>
    </div>
</section>
@endsection
