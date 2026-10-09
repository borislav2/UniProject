@extends('demos._layout')
@section('body_class', 'bg-white text-[#1e293b]')

@section('content')
<header class="sticky top-0 z-50 bg-white border-b border-slate-200">
    <nav class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="#" class="flex items-center gap-2 text-lg font-extrabold"><span class="w-9 h-9 bg-[#d97706] text-white flex items-center justify-center rounded"><x-icon name="home" /></span>Здрав покрив</a>
        <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600"><a href="#uslugi" class="hover:text-[#d97706]">Услуги</a><a href="#kak" class="hover:text-[#d97706]">Как работим</a><a href="#ogled" class="hover:text-[#d97706]">Контакт</a></div>
        <a href="#ogled" class="rounded bg-[#d97706] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#b45309]">Безплатен оглед</a>
    </nav>
</header>

<section class="relative bg-[#1e293b] text-white overflow-hidden">
    <svg class="absolute bottom-0 right-0 w-[60%] opacity-20" viewBox="0 0 600 300" fill="none" aria-hidden="true"><path d="M0 300 L300 40 L600 300" stroke="#f59e0b" stroke-width="18"/><path d="M80 300 L300 110 L520 300" stroke="#f59e0b" stroke-width="10"/></svg>
    <div class="relative max-w-6xl mx-auto px-4 py-20 md:py-28 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <p class="text-sm font-bold uppercase tracking-widest text-[#f59e0b]">Бургас и региона</p>
            <h1 class="mt-4 text-4xl md:text-6xl font-extrabold leading-tight">Нов покрив или ремонт, без течове и изненади</h1>
            <p class="mt-6 text-lg text-slate-300 max-w-lg">Ремонт на покриви, хидроизолация и улуци. Писмена оферта след безплатен оглед и 10 години гаранция.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#ogled" class="rounded bg-[#d97706] px-7 py-3.5 font-bold hover:bg-[#b45309]">Заяви безплатен оглед</a>
                <a href="tel:+35956000000" data-demo-action class="rounded border border-white/30 px-7 py-3.5 font-bold hover:border-white">+359 56 000 000</a>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            @foreach([['10 г.', 'гаранция'], ['350+', 'покрива'], ['48 ч', 'до оглед']] as [$n, $l])
                <div class="rounded-lg bg-white/5 ring-1 ring-white/10 p-5"><p class="text-3xl font-extrabold text-[#f59e0b]">{{ $n }}</p><p class="mt-1 text-sm text-slate-300">{{ $l }}</p></div>
            @endforeach
        </div>
    </div>
</section>

<section id="uslugi" class="py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-extrabold">Какво правим</h2>
        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([['home', 'Нови покриви', 'Дървена конструкция, керемиди, метал или битумни керемиди.'], ['hammer', 'Ремонт на покриви', 'Пренареждане, смяна на керемиди и поправка на течове.'], ['shield-halved', 'Хидроизолация', 'Тераси и плоски покриви с гаранция.'], ['helmet-safety', 'Улуци и водосточни тръби', 'Монтаж и почистване.'], ['screwdriver-wrench', 'Покривни прозорци', 'Монтаж с плътни обшивки.'], ['magnifying-glass', 'Оглед и оферта', 'Безплатно, със снимки и писмена оферта.']] as [$icon, $t, $d])
                <div class="rounded-lg border-l-4 border-[#d97706] bg-slate-50 p-6"><x-icon :name="$icon" class="text-2xl text-[#d97706]" /><h3 class="mt-4 font-bold text-lg">{{ $t }}</h3><p class="mt-2 text-slate-600">{{ $d }}</p></div>
            @endforeach
        </div>
    </div>
</section>

<section id="kak" class="bg-slate-50 py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-extrabold">Как работим</h2>
        <ol class="mt-10 grid md:grid-cols-4 gap-6">
            @foreach(['Обаждате се или пишете', 'Безплатен оглед до 48 часа', 'Писмена оферта с цена и срок', 'Изпълнение и гаранция'] as $i => $step)
                <li class="rounded-lg bg-white p-6 shadow-sm"><span class="text-4xl font-extrabold text-[#d97706]">{{ $i + 1 }}</span><p class="mt-3 font-semibold">{{ $step }}</p></li>
            @endforeach
        </ol>
    </div>
</section>

<section id="ogled" class="py-16 md:py-20">
    <div class="max-w-5xl mx-auto px-4 grid md:grid-cols-2 gap-10">
        <div>
            <h2 class="text-3xl md:text-4xl font-extrabold">Заявете безплатен оглед</h2>
            <p class="mt-3 text-slate-600">Оставете телефон и ще ви се обадим до един работен ден.</p>
            <p class="mt-8 flex items-center gap-3"><x-icon name="location-dot" class="text-[#d97706]" /> ж.к. „Изгрев“, Бургас</p>
            <p class="mt-3 flex items-center gap-3"><x-icon name="phone" class="text-[#d97706]" /> +359 56 000 000</p>
            <p class="mt-3 flex items-center gap-3"><x-icon name="envelope" class="text-[#d97706]" /> office@example.com</p>
        </div>
        <form data-demo-form class="rounded-lg border border-slate-200 p-6 space-y-4 shadow-sm">
            <input class="w-full rounded border border-slate-300 px-4 py-3" placeholder="Име">
            <input class="w-full rounded border border-slate-300 px-4 py-3" placeholder="Телефон">
            <input class="w-full rounded border border-slate-300 px-4 py-3" placeholder="Населено място">
            <textarea rows="3" class="w-full rounded border border-slate-300 px-4 py-3" placeholder="Опишете проблема (по желание)"></textarea>
            <button class="w-full rounded bg-[#d97706] py-3.5 font-bold text-white hover:bg-[#b45309]">Заяви оглед</button>
        </form>
    </div>
</section>
@endsection
