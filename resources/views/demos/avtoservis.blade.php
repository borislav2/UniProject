@extends('demos._layout')
@section('body_class', 'bg-[#0e1012] text-white pb-16 md:pb-0')

@php
    $jobs = [
        ['Компютърна диагностика', 25, '30 мин'], ['Смяна на масло и филтри', 49, '45 мин'], ['Предни накладки', 59, '1 час'],
        ['Предни дискове и накладки', 129, '1 ч 30 мин'], ['Зареждане на климатик', 45, '40 мин'], ['Ангренажен комплект', 240, '4 часа'],
        ['Съединител', 290, '5 часа'], ['Геометрия на ходовата част', 35, '40 мин'],
    ];
    $services = [
        ['Диагностика', 'Четем грешките с оригинален софтуер и казваме какво е спешно и какво може да почака.', 'gauge-high'],
        ['Двигател и ангренаж', 'Ремонт и обслужване на бензинови, дизелови и хибридни двигатели.', 'cogs'],
        ['Спирачки и ходова', 'Накладки, дискове, амортисьори, носачи и геометрия на 3D стенд.', 'shield-halved'],
        ['Климатици', 'Профилактика, зареждане, търсене на теч и дезинфекция.', 'sun'],
        ['Електрика', 'Стартери, алтернатори, акумулатори, сензори и окабеляване.', 'chart-line'],
        ['Преглед преди покупка', 'Проверка на 120 точки, с доклад и снимки, преди да платите колата.', 'clipboard-list'],
    ];
@endphp

@section('content')
<div class="demo-geo">
<header class="sticky top-0 z-50 border-b border-white/10 bg-[#0e1012]/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <a href="#" class="flex items-center gap-2 text-xl font-extrabold uppercase tracking-tight"><span class="bg-[#ffd400] px-2 py-0.5 text-black">Мотор</span>Про</a>
        <div class="hidden items-center gap-8 text-sm font-semibold uppercase tracking-wider text-gray-300 md:flex">
            <a href="#ceni" class="hover:text-[#ffd400]">Цени</a><a href="#uslugi" class="hover:text-[#ffd400]">Услуги</a><a href="#kak" class="hover:text-[#ffd400]">Как работим</a><a href="#kontakt" class="hover:text-[#ffd400]">Контакт</a>
        </div>
        <a href="tel:+35982000000" data-demo-action class="inline-flex items-center gap-2 bg-[#ffd400] px-4 py-2 text-sm font-extrabold text-black hover:bg-white"><x-icon name="phone" /> 082 000 000</a>
    </nav>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-[repeating-linear-gradient(135deg,transparent_0_38px,rgba(255,255,255,.025)_38px_40px)]"></div>
    <div class="absolute -right-24 top-0 h-full w-1/2 -skew-x-12 bg-[#ffd400]/[0.06]"></div>
    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-7">
            <p class="inline-flex items-center gap-2 border border-[#ffd400]/50 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-[#ffd400]"><span class="h-2 w-2 animate-pulse rounded-full bg-[#3ddc84]"></span> Отворено сега · свободен подемник днес в 14:30</p>
            <h1 class="mt-6 text-5xl font-extrabold uppercase leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl">Казваме цената,<br><span class="text-[#ffd400]">преди да пипнем</span><br>колата</h1>
            <p class="mt-6 max-w-xl text-lg font-light leading-relaxed text-gray-300">Сервиз за всички марки в Русе. Диагностика за 30 минути, писмена оферта и 12 месеца гаранция за труда и частите.</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="#ceni" class="bg-[#ffd400] px-8 py-4 font-extrabold uppercase tracking-wide text-black hover:bg-white">Изчисли цена</a>
                <a href="#chas" class="border-2 border-white/30 px-8 py-4 font-extrabold uppercase tracking-wide hover:border-[#ffd400] hover:text-[#ffd400]">Запази час</a>
            </div>
            <dl class="mt-12 grid max-w-xl grid-cols-3 gap-px bg-white/10">
                @foreach([['17', 'години в бранша'], ['4,8', 'от 5 в Google'], ['12 мес.', 'гаранция']] as [$n, $t])
                    <div class="bg-[#0e1012] py-4 pr-4"><dd class="text-3xl font-extrabold text-[#ffd400]">{{ $n }}</dd><dt class="mt-1 text-sm font-light text-gray-400">{{ $t }}</dt></div>
                @endforeach
            </dl>
        </div>
        <div class="lg:col-span-5">
            <x-demo-photo :slug="$demo['slug']" name="hero" alt="Автомобил на подемник в сервиза" class="relative aspect-[5/4] w-full overflow-hidden border border-white/10 bg-[linear-gradient(160deg,#1c2024,#0e1012)]">
                <svg viewBox="0 0 400 320" class="absolute inset-0 h-full w-full" fill="none">
                    <path d="M0 262h400" stroke="#ffd400" stroke-width="4"/><path d="M0 276h400" stroke="#fff" stroke-opacity=".12" stroke-width="12" stroke-dasharray="24 16"/>
                    <path d="M70 196c6-30 26-58 56-64l28-40c8-10 18-14 30-14h74c14 0 26 6 34 18l24 38c26 4 46 18 46 42v18c0 8-6 14-14 14H84c-8 0-14-6-14-14Z" fill="#ffd400"/>
                    <path d="M168 94h36v38h-60l14-28c2-6 6-10 10-10Zm50 0h38c8 0 14 4 18 10l16 28h-72Z" fill="#0e1012"/>
                    <g fill="#0e1012" stroke="#fff" stroke-width="6"><circle cx="134" cy="210" r="30"/><circle cx="298" cy="210" r="30"/></g><circle cx="134" cy="210" r="9" fill="#fff"/><circle cx="298" cy="210" r="9" fill="#fff"/>
                    <path d="M40 60v186M360 60v186" stroke="#fff" stroke-opacity=".3" stroke-width="10"/><path d="M40 246h320" stroke="#fff" stroke-opacity=".3" stroke-width="6"/>
                </svg>
                <p class="absolute left-4 top-4 bg-black/70 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-[#ffd400]">Подемник 2 · диагностика</p>
            </x-demo-photo>
            <div class="mt-3 grid grid-cols-3 gap-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-300">
                @foreach(['Оригинални части', 'Кола под наем', 'Плащане с карта'] as $perk)<p class="border border-white/10 px-2 py-3">{{ $perk }}</p>@endforeach
            </div>
        </div>
    </div>
</section>

{{-- Brands --}}
<div class="border-y border-white/10 bg-[#15181b]">
    <div class="demo-scroll mx-auto flex max-w-7xl items-center gap-10 px-4 py-5 text-sm font-extrabold uppercase tracking-[0.2em] text-gray-500">
        <span class="shrink-0 text-[#ffd400]">Работим с</span>
        @foreach(['VW', 'Audi', 'Škoda', 'BMW', 'Mercedes', 'Toyota', 'Renault', 'Dacia', 'Ford', 'Opel', 'Peugeot', 'Kia'] as $brand)<span class="shrink-0">{{ $brand }}</span>@endforeach
    </div>
</div>

{{-- Estimator --}}
<section id="ceni" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <p class="text-sm font-semibold uppercase tracking-widest text-[#ffd400]">Калкулатор</p>
        <h2 class="mt-3 max-w-3xl text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Вижте колко ще струва, още сега</h2>
        <div class="mt-10 grid gap-6 lg:grid-cols-12" data-estimator>
            <div class="bg-[#15181b] p-6 md:p-8 lg:col-span-8">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">1. Каква е колата</p>
                <div data-pick="car" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach([['Малка', '1'], ['Средна', '1.15'], ['Джип', '1.3'], ['Бус', '1.45']] as $i => [$car, $factor])
                        <button type="button" data-value="{{ $car }}" data-factor="{{ $factor }}" aria-pressed="{{ $i === 1 ? 'true' : 'false' }}" class="border border-white/15 py-3 text-sm font-semibold uppercase tracking-wide hover:border-[#ffd400] aria-pressed:border-[#ffd400] aria-pressed:bg-[#ffd400] aria-pressed:text-black">{{ $car }}</button>
                    @endforeach
                </div>
                <p class="mt-8 text-xs font-semibold uppercase tracking-widest text-gray-400">2. Какво трябва да се направи</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach($jobs as $i => [$job, $price, $time])
                        <label class="flex cursor-pointer items-center gap-3 border border-white/15 p-4 hover:border-[#ffd400] has-[:checked]:border-[#ffd400] has-[:checked]:bg-[#ffd400]/10">
                            <input type="checkbox" data-job="{{ $price }}" @checked($i < 2) class="h-5 w-5 rounded-none border-white/40 bg-transparent text-[#ffd400] focus:ring-[#ffd400]">
                            <span class="flex-1"><span class="block font-semibold">{{ $job }}</span><span class="text-xs font-light text-gray-400">{{ $time }}</span></span>
                            <span class="text-sm font-semibold text-[#ffd400]">от {{ $price }} €</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <aside id="chas" class="scroll-mt-24 flex flex-col bg-[#ffd400] p-6 text-black md:p-8 lg:col-span-4">
                <p class="text-xs font-extrabold uppercase tracking-widest">Ориентировъчна цена с труда</p>
                <p class="mt-3 text-6xl font-extrabold tracking-tight"><span data-total>85</span> €</p>
                <p class="mt-2 text-sm"><span data-count>2</span> услуги за кола <span data-pick-out="car" class="lowercase">средна</span>. Частите са по избор, оригинални или алтернативни.</p>
                <form data-demo-form class="mt-auto space-y-3 pt-8">
                    <input type="text" required placeholder="Марка, модел и година" class="w-full border-2 border-black bg-transparent px-4 py-3 placeholder-black/60 focus:border-black focus:ring-0">
                    <input type="tel" required placeholder="Телефон" class="w-full border-2 border-black bg-transparent px-4 py-3 placeholder-black/60 focus:border-black focus:ring-0">
                    <button class="w-full bg-black py-4 font-extrabold uppercase tracking-wide text-[#ffd400] hover:bg-[#23272b]">Запази час с тази цена</button>
                    <p class="text-xs">Връщаме обаждане до 20 минути и потвърждаваме точната сума.</p>
                </form>
            </aside>
        </div>
    </div>
</section>

{{-- Services --}}
<section id="uslugi" class="scroll-mt-16 bg-[#15181b] px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Какво ремонтираме</h2>
        <div class="dr mt-12 grid gap-px bg-white/10 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($services as $i => [$name, $text, $icon])
                <article class="group bg-[#15181b] p-8 transition hover:bg-[#ffd400] hover:text-black">
                    <p class="flex items-center justify-between text-3xl text-[#ffd400] group-hover:text-black"><x-icon :name="$icon" /><span class="text-sm font-extrabold text-white/25 group-hover:text-black/40">0{{ $i + 1 }}</span></p>
                    <h3 class="mt-6 text-xl font-extrabold uppercase">{{ $name }}</h3>
                    <p class="mt-2 font-light leading-relaxed text-gray-400 group-hover:text-black/80">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Process --}}
<section id="kak" class="scroll-mt-16 px-4 py-20">
    <div class="mx-auto max-w-7xl">
        <h2 class="max-w-2xl text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Без изненади от началото до ключа</h2>
        <ol class="dr mt-12 grid gap-6 md:grid-cols-4">
            @foreach([
                ['Запазвате час', 'По телефона или от сайта. Казваме точен ден и час за приемане.'],
                ['Диагностика', 'За 30 минути знаем какво е. Пращаме ви снимки на проблема.'],
                ['Писмена оферта', 'Получавате цена за труда и частите. Започваме след вашето „да“.'],
                ['Взимате колата', 'С протокол, старите части и 12 месеца гаранция.'],
            ] as $i => [$title, $text])
                <li class="relative border-l-4 border-[#ffd400] bg-[#15181b] p-6">
                    <span class="text-sm font-extrabold uppercase tracking-widest text-[#ffd400]">Стъпка {{ $i + 1 }}</span>
                    <h3 class="mt-3 text-xl font-extrabold uppercase">{{ $title }}</h3>
                    <p class="mt-2 font-light leading-relaxed text-gray-400">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach([
                ['Обадих се в 9, в 11 колата беше на подемника, в 12 имах оферта със снимки. Платих точно колкото пишеше.', 'Красимир Й.', 'Škoda Octavia'],
                ['Друг сервиз ми искаше смяна на турбото. Тук намериха спукан маркуч за 30 евро.', 'Теодора М.', 'VW Golf'],
                ['Дадоха ми кола под наем за двата дни ремонт. Така се работи.', 'Пламен Г.', 'Ford Transit'],
            ] as [$quote, $who, $car])
                <figure class="border border-white/10 p-6">
                    <p class="text-[#ffd400]" aria-label="5 от 5 звезди">★★★★★</p>
                    <blockquote class="mt-3 font-light leading-relaxed text-gray-200">„{{ $quote }}“</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold">{{ $who }} <span class="font-light text-gray-500">· {{ $car }}</span></figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="scroll-mt-16 bg-[#ffd400] px-4 py-16 text-black">
    <div class="mx-auto grid max-w-7xl items-center gap-10 md:grid-cols-2">
        <div>
            <h2 class="text-4xl font-extrabold uppercase tracking-tight md:text-5xl">Колата не пали? Звъннете.</h2>
            <p class="mt-4 text-lg">Пътна помощ в Русе и областта до 40 минути.</p>
            <a href="tel:+35982000000" data-demo-action class="mt-6 inline-flex items-center gap-3 bg-black px-8 py-4 text-2xl font-extrabold text-[#ffd400]"><x-icon name="phone-volume" /> 082 000 000</a>
        </div>
        <dl class="grid gap-px bg-black/20 sm:grid-cols-2">
            @foreach([['Адрес', 'бул. „Липник“ 123, Русе'], ['Понеделник до петък', '8:00 до 18:30'], ['Събота', '9:00 до 14:00'], ['Пътна помощ', 'Всеки ден до 22:00']] as [$k, $v])
                <div class="bg-[#ffd400] p-5"><dt class="text-xs font-extrabold uppercase tracking-widest">{{ $k }}</dt><dd class="mt-1 text-lg font-semibold">{{ $v }}</dd></div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Phone bar --}}
<div class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-2 md:hidden">
    <a href="tel:+35982000000" data-demo-action class="flex items-center justify-center gap-2 bg-[#ffd400] py-4 font-extrabold uppercase text-black"><x-icon name="phone" /> Обади се</a>
    <a href="#ceni" class="flex items-center justify-center gap-2 bg-white py-4 font-extrabold uppercase text-black"><x-icon name="calculator" /> Цена</a>
</div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var box = document.querySelector('[data-estimator]'), factor = 1.15;
        function draw() {
            var sum = 0, count = 0;
            box.querySelectorAll('[data-job]').forEach(function (job) { if (job.checked) { sum += Number(job.dataset.job); count++; } });
            box.querySelector('[data-total]').textContent = Math.round(sum * factor / 5) * 5;
            box.querySelector('[data-count]').textContent = count;
        }
        box.querySelectorAll('[data-job]').forEach(function (job) { job.addEventListener('change', draw); });
        box.querySelector('[data-pick="car"]').addEventListener('pick', function (e) { factor = Number(e.target.querySelector('[aria-pressed="true"]').dataset.factor); draw(); });
        draw();
    })();
</script>
@endpush
