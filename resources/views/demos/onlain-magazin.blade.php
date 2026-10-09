@extends('demos._layout')
@section('body_class', 'bg-[#fffaf0] text-[#3b2a12]')

@section('content')
<header class="sticky top-0 z-50 bg-[#fffaf0]/95 backdrop-blur border-b border-[#f0e2c4]">
    <nav class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="#" class="text-xl font-extrabold">Медена <span class="text-[#d4a017]">пита</span></a>
        <div class="hidden md:flex items-center gap-8 text-sm font-semibold"><a href="#produkti" class="hover:text-[#b8860b]">Продукти</a><a href="#dostavka" class="hover:text-[#b8860b]">Доставка</a></div>
        <a href="#" data-demo-action class="relative inline-flex items-center gap-2 rounded-full bg-[#3b2a12] px-4 py-2 text-sm font-semibold text-white"><x-icon name="bag-shopping" /> Количка <span class="rounded-full bg-[#d4a017] px-2 text-xs text-[#3b2a12]">2</span></a>
    </nav>
</header>

<section class="relative overflow-hidden">
    <div class="absolute inset-0 opacity-40 bg-[radial-gradient(circle_at_80%_30%,#f7d774_0,transparent_45%)]"></div>
    <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <span class="rounded-full bg-[#f7e7b4] px-4 py-1.5 text-sm font-semibold text-[#8a6410]">Директно от пчелина в Родопите</span>
            <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-tight">Истински пчелен мед до вратата ви</h1>
            <p class="mt-5 text-lg text-[#6b5434] max-w-md">Липов, акациев и билков мед, прополис и пчелно млечице. Безплатна доставка над 40 €.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#produkti" class="rounded-full bg-[#d4a017] px-7 py-3.5 font-bold text-[#3b2a12] hover:bg-[#e8b923]">Към продуктите</a>
                <a href="#dostavka" class="rounded-full px-7 py-3.5 font-bold ring-2 ring-[#3b2a12]">Доставка и плащане</a>
            </div>
        </div>
        <div class="relative mx-auto">
            <svg viewBox="0 0 220 200" class="w-72 md:w-96 drop-shadow-xl" aria-hidden="true">
                @foreach([[60, 50], [110, 50], [160, 50], [85, 93], [135, 93], [60, 136], [110, 136], [160, 136]] as [$x, $y])
                    <polygon points="{{ $x }},{{ $y - 28 }} {{ $x + 24 }},{{ $y - 14 }} {{ $x + 24 }},{{ $y + 14 }} {{ $x }},{{ $y + 28 }} {{ $x - 24 }},{{ $y + 14 }} {{ $x - 24 }},{{ $y - 14 }}" fill="{{ ($x + $y) % 3 ? '#f2c94c' : '#e0a82e' }}" stroke="#fffaf0" stroke-width="4"/>
                @endforeach
            </svg>
        </div>
    </div>
</section>

<section id="produkti" class="py-16 md:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-end justify-between gap-4">
            <h2 class="text-3xl md:text-4xl font-extrabold">Най-продавани</h2>
            <a href="#" data-demo-action class="text-sm font-semibold text-[#b8860b] hover:underline">Всички продукти</a>
        </div>
        <div class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([['Липов мед', '500 г', '9,90', '#f4d27a'], ['Акациев мед', '500 г', '11,50', '#f8e3a3'], ['Билков мед', '450 г', '10,90', '#d9a441'], ['Прополис', '20 мл', '6,50', '#a8742a']] as [$name, $size, $price, $color])
                <div class="rounded-2xl ring-1 ring-[#f0e2c4] p-4 flex flex-col">
                    <div class="aspect-square rounded-xl flex items-center justify-center" style="background: {{ $color }}33">
                        <div class="w-16 h-24 rounded-b-2xl rounded-t-md" style="background: {{ $color }}"><div class="mx-auto -mt-2 w-12 h-4 rounded bg-[#3b2a12]"></div></div>
                    </div>
                    <h3 class="mt-4 font-bold">{{ $name }}</h3>
                    <p class="text-sm text-[#6b5434]">{{ $size }}</p>
                    <div class="mt-auto pt-4 flex items-center justify-between"><span class="text-lg font-extrabold">{{ $price }} €</span><button data-demo-action class="rounded-full bg-[#3b2a12] w-9 h-9 text-white flex items-center justify-center hover:bg-[#5a4220]" aria-label="Добави в количката"><x-icon name="plus" /></button></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="dostavka" class="py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4 grid sm:grid-cols-3 gap-6">
        @foreach([['rocket', 'Доставка до 2 дни', 'С Еконт или Спиди до адрес или офис.'], ['tag', 'Безплатна над 40 €', 'Под тази сума доставката е 3,90 €.'], ['handshake', 'Плащане при доставка', 'Или с карта онлайн, сигурно.']] as [$icon, $t, $d])
            <div class="rounded-2xl bg-white p-6 ring-1 ring-[#f0e2c4]"><x-icon :name="$icon" class="text-2xl text-[#d4a017]" /><h3 class="mt-4 font-bold">{{ $t }}</h3><p class="mt-1 text-sm text-[#6b5434]">{{ $d }}</p></div>
        @endforeach
    </div>
</section>

<section class="bg-[#3b2a12] text-white py-14">
    <div class="max-w-3xl mx-auto px-4 text-center">
        <h2 class="text-2xl md:text-3xl font-extrabold">10% отстъпка за първата поръчка</h2>
        <p class="mt-2 text-[#e9d8b4]">Абонирайте се и получете код на имейла си.</p>
        <form data-demo-form class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
            <input type="email" class="rounded-full px-5 py-3 text-[#3b2a12] sm:w-80" placeholder="Вашият имейл">
            <button class="rounded-full bg-[#d4a017] px-6 py-3 font-bold text-[#3b2a12] hover:bg-[#e8b923]">Абонирай ме</button>
        </form>
    </div>
</section>
@endsection
