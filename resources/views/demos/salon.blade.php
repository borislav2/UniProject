@extends('demos._layout')
@section('body_class', 'bg-white text-[#2d2438]')

@section('content')
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur">
    <nav class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="#" class="demo-serif text-2xl italic text-[#6b4c9a]">Лавандула</a>
        <div class="hidden md:flex items-center gap-8 text-sm"><a href="#uslugi" class="hover:text-[#6b4c9a]">Услуги</a><a href="#ceni" class="hover:text-[#6b4c9a]">Цени</a><a href="#chas" class="hover:text-[#6b4c9a]">Запиши час</a></div>
        <a href="#chas" class="rounded-full border border-[#6b4c9a] px-5 py-2 text-sm font-semibold text-[#6b4c9a] hover:bg-[#6b4c9a] hover:text-white">Запиши час</a>
    </nav>
</header>

<section class="bg-gradient-to-br from-[#f3eefa] via-white to-[#fbf0f5]">
    <div class="max-w-6xl mx-auto px-4 py-16 md:py-24 grid md:grid-cols-5 gap-12 items-center">
        <div class="md:col-span-3">
            <p class="text-sm font-semibold tracking-widest uppercase text-[#b07aa1]">Студио за красота · София, Лозенец</p>
            <h1 class="demo-serif mt-4 text-5xl md:text-6xl leading-tight">Време само за <em class="text-[#6b4c9a]">вас</em></h1>
            <p class="mt-6 text-lg text-[#6c6378] max-w-lg">Маникюр, грижа за лице и коса в спокойна обстановка. Работим само с професионална козметика.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#chas" class="rounded-full bg-[#6b4c9a] px-7 py-3 font-semibold text-white hover:bg-[#563c7d]">Запиши час онлайн</a>
                <a href="#ceni" class="rounded-full bg-white px-7 py-3 font-semibold text-[#6b4c9a] ring-1 ring-[#d9cdea] hover:ring-[#6b4c9a]">Виж цените</a>
            </div>
        </div>
        <div class="md:col-span-2 grid grid-cols-2 gap-4">
            <div class="aspect-[3/4] rounded-[2rem] bg-gradient-to-b from-[#c9b3e6] to-[#6b4c9a] flex items-end p-5 text-white"><span class="demo-serif text-xl">Лице</span></div>
            <div class="aspect-[3/4] rounded-[2rem] bg-gradient-to-b from-[#f6c9dc] to-[#b07aa1] flex items-end p-5 text-white mt-10"><span class="demo-serif text-xl">Ръце</span></div>
        </div>
    </div>
</section>

<section id="uslugi" class="py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="demo-serif text-center text-4xl">Нашите услуги</h2>
        <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([['spa', 'Грижа за лице', 'Почистване, хидратация и масаж на лицето.'], ['heart-pulse', 'Маникюр и педикюр', 'Класически, гел лак и ноктопластика.'], ['crown', 'Коса', 'Подстригване, боядисване и прически.'], ['users', 'Булчински пакет', 'Грим, прическа и маникюр за специалния ден.']] as [$icon, $t, $d])
                <div class="rounded-3xl bg-[#f7f3fc] p-7 text-center">
                    <span class="mx-auto w-14 h-14 rounded-full bg-white flex items-center justify-center text-[#6b4c9a] shadow-sm"><x-icon :name="$icon" class="text-xl" /></span>
                    <h3 class="mt-5 font-semibold text-lg">{{ $t }}</h3><p class="mt-2 text-sm text-[#6c6378]">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="ceni" class="bg-[#f7f3fc] py-16 md:py-20">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="demo-serif text-center text-4xl">Цени</h2>
        <div class="mt-10 rounded-3xl bg-white p-8 shadow-sm divide-y divide-[#eee6f6]">
            @foreach([['Почистване на лице', '60 мин', '45 €'], ['Маникюр с гел лак', '75 мин', '30 €'], ['Подстригване и сешоар', '45 мин', '25 €'], ['Булчински грим', '90 мин', '80 €']] as [$s, $time, $p])
                <div class="flex items-center justify-between py-4"><div><p class="font-semibold">{{ $s }}</p><p class="text-sm text-[#6c6378]">{{ $time }}</p></div><span class="demo-serif text-xl text-[#6b4c9a]">{{ $p }}</span></div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <p class="demo-serif text-2xl md:text-3xl italic leading-relaxed">„Най-приятното място в София. Излизам отпочинала и красива всеки път.“</p>
        <p class="mt-4 text-sm text-[#6c6378]">★★★★★ · Мария, редовна клиентка</p>
    </div>
</section>

<section id="chas" class="bg-[#2d2438] text-white py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <h2 class="demo-serif text-4xl">Запишете час</h2>
            <p class="mt-3 text-[#cbbfdc]">Понеделник – Събота, 9:00 – 20:00</p>
            <p class="mt-6 flex items-center gap-3"><x-icon name="location-dot" class="text-[#c9b3e6]" /> ул. „Черни връх“ 40, София</p>
            <p class="mt-3 flex items-center gap-3"><x-icon name="phone" class="text-[#c9b3e6]" /> +359 2 000 0000</p>
        </div>
        <form data-demo-form class="rounded-3xl bg-white p-6 text-[#2d2438] space-y-4">
            <input class="w-full rounded-xl border border-[#e3daef] px-4 py-3" placeholder="Име">
            <input class="w-full rounded-xl border border-[#e3daef] px-4 py-3" placeholder="Телефон">
            <select class="w-full rounded-xl border border-[#e3daef] px-4 py-3"><option>Изберете услуга</option><option>Грижа за лице</option><option>Маникюр</option><option>Коса</option></select>
            <button class="w-full rounded-full bg-[#6b4c9a] py-3 font-semibold text-white hover:bg-[#563c7d]">Запиши час</button>
        </form>
    </div>
</section>
@endsection
