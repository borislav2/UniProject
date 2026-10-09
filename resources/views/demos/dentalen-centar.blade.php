@extends('demos._layout')
@section('body_class', 'bg-white text-[#12303a]')

@section('content')
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-[#e3f1f1]">
    <nav class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="#" class="flex items-center gap-2 font-extrabold text-lg"><span class="w-9 h-9 rounded-xl bg-[#0f8b8d] text-white flex items-center justify-center"><x-icon name="stethoscope" /></span>Бяла усмивка</a>
        <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-[#3d5a63]"><a href="#uslugi" class="hover:text-[#0f8b8d]">Услуги</a><a href="#ekip" class="hover:text-[#0f8b8d]">Екип</a><a href="#pregled" class="hover:text-[#0f8b8d]">Контакт</a></div>
        <a href="#pregled" class="rounded-xl bg-[#0f8b8d] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0b6e70]">Запиши преглед</a>
    </nav>
</header>

<section class="bg-[#f1f9f9]">
    <div class="max-w-6xl mx-auto px-4 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-semibold text-[#0f8b8d] shadow-sm"><x-icon name="shield-halved" /> Безболезнено лечение</span>
            <h1 class="mt-6 text-4xl md:text-5xl font-extrabold leading-tight">Здрава и красива усмивка за цялото семейство</h1>
            <p class="mt-5 text-lg text-[#3d5a63] max-w-lg">Профилактика, лечение и естетична стоматология във Варна. Без чакане и с ясна цена преди всяка процедура.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#pregled" class="rounded-xl bg-[#0f8b8d] px-6 py-3.5 font-semibold text-white hover:bg-[#0b6e70]">Запиши първичен преглед</a>
                <a href="tel:+35952000000" data-demo-action class="rounded-xl bg-white px-6 py-3.5 font-semibold text-[#0f8b8d] ring-1 ring-[#bfe3e3]">+359 52 000 000</a>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            @foreach([['12+', 'години опит'], ['4 500+', 'доволни пациенти'], ['4,9', 'оценка в Google'], ['0', 'скрити такси']] as [$n, $l])
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#e3f1f1]"><p class="text-3xl font-extrabold text-[#0f8b8d]">{{ $n }}</p><p class="mt-1 text-sm text-[#3d5a63]">{{ $l }}</p></div>
            @endforeach
        </div>
    </div>
</section>

<section id="uslugi" class="py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-extrabold">Услуги</h2>
        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([['Профилактичен преглед', 'Преглед, снимка и план за лечение.', 'от 30 €'], ['Почистване на зъбен камък', 'Ултразвуково почистване и полиране.', 'от 50 €'], ['Пломби', 'Естетични фотополимерни пломби.', 'от 45 €'], ['Избелване', 'Професионално избелване в кабинета.', 'от 180 €'], ['Импланти', 'Консултация и план за имплантиране.', 'по оферта'], ['Детска стоматология', 'Спокойно и игриво за най-малките.', 'от 25 €']] as [$t, $d, $p])
                <div class="rounded-2xl border border-[#e3f1f1] p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between gap-3"><h3 class="font-bold text-lg">{{ $t }}</h3><span class="shrink-0 rounded-full bg-[#f1f9f9] px-3 py-1 text-sm font-semibold text-[#0f8b8d]">{{ $p }}</span></div>
                    <p class="mt-2 text-[#3d5a63]">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="ekip" class="bg-[#f1f9f9] py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-extrabold">Нашият екип</h2>
        <div class="mt-10 grid sm:grid-cols-3 gap-6">
            @foreach([['ДП', 'Д-р Петрова', 'Естетична стоматология'], ['ДИ', 'Д-р Иванов', 'Имплантология'], ['ДГ', 'Д-р Георгиева', 'Детска стоматология']] as [$i, $n, $r])
                <div class="rounded-2xl bg-white p-6 text-center shadow-sm"><span class="mx-auto w-20 h-20 rounded-full bg-gradient-to-br from-[#5cc2c4] to-[#0f8b8d] text-white text-2xl font-bold flex items-center justify-center">{{ $i }}</span><h3 class="mt-4 font-bold">{{ $n }}</h3><p class="text-sm text-[#3d5a63]">{{ $r }}</p></div>
            @endforeach
        </div>
    </div>
</section>

<section id="pregled" class="py-16 md:py-20">
    <div class="max-w-5xl mx-auto px-4 grid md:grid-cols-2 gap-10 items-start">
        <div>
            <h2 class="text-3xl md:text-4xl font-extrabold">Запишете преглед</h2>
            <p class="mt-3 text-[#3d5a63]">Обаждаме се в рамките на работния ден, за да уточним час.</p>
            <dl class="mt-8 space-y-4 text-[#3d5a63]">
                <div class="flex gap-3"><x-icon name="location-dot" class="mt-1 text-[#0f8b8d]" /><dd>бул. „Приморски“ 50, Варна</dd></div>
                <div class="flex gap-3"><x-icon name="phone" class="mt-1 text-[#0f8b8d]" /><dd>+359 52 000 000</dd></div>
                <div class="flex gap-3"><x-icon name="envelope" class="mt-1 text-[#0f8b8d]" /><dd>office@example.com</dd></div>
                <div class="flex gap-3"><x-icon name="check" class="mt-1 text-[#0f8b8d]" /><dd>Пн – Пт 8:30 – 19:00, Сб 9:00 – 14:00</dd></div>
            </dl>
        </div>
        <form data-demo-form class="rounded-2xl bg-[#f1f9f9] p-6 space-y-4">
            <input class="w-full rounded-xl border border-[#cde8e8] bg-white px-4 py-3" placeholder="Име и фамилия">
            <input class="w-full rounded-xl border border-[#cde8e8] bg-white px-4 py-3" placeholder="Телефон">
            <textarea rows="3" class="w-full rounded-xl border border-[#cde8e8] bg-white px-4 py-3" placeholder="Какво ви притеснява? (по желание)"></textarea>
            <button class="w-full rounded-xl bg-[#0f8b8d] py-3.5 font-semibold text-white hover:bg-[#0b6e70]">Изпрати</button>
        </form>
    </div>
</section>
@endsection
