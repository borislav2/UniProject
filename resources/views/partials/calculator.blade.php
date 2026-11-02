{{--
    Калкулатор на подобрението (началната страница, #kalkulator; бутонът в менюто и в hero-то водят тук).
    Собствениците, ноември 2026: не пари, а колко можем да подобрим бизнеса откъм видимост, запитвания и конверсия.
    Посетителят въвежда посещенията и запитванията си и отмята какво вече има; всяко липсващо нещо добавя умерен процент
    към посещенията, към конверсията (запитвания от посещения) или към запитванията. Без JavaScript се вижда сметката
    за примерните стойности; скриптът я смята наново при всяка промяна. $number: номерът на секцията („03 /“).
--}}
@php
    // [ключ, текст, къде действа (visits|conv|leads), процент, обяснение, имат ли го по подразбиране]
    $calcItems = [
        ['profile', __('Попълнен Google профил със снимки, отзиви и работно време'), 'visits', 20, __('повече хора ви намират в Google Maps'), false],
        ['seo', __('SEO и видимост в AI асистентите (ChatGPT, Gemini)'), 'visits', 25, __('повече посещения от търсения'), false],
        ['ads', __('Реклама в Google и Meta, насочена към вашия град'), 'visits', 30, __('повече посещения от хора, които търсят сега'), false],
        ['brand', __('Единен бранд: лого, цветове и тон навсякъде'), 'conv', 5, __('повече доверие, повече хора ви пишат'), true],
        ['speed', __('Сайтът се отваря бързо на телефон'), 'conv', 8, __('повече запитвания от същите посещения'), false],
        ['cta', __('Бутон за обаждане и кратка форма на всяка страница'), 'conv', 20, __('по-лесно е да ви пишат'), true],
        ['email', __('Автоматични имейли след запитване и покупка'), 'leads', 10, __('повече хора се връщат и пишат отново'), false],
    ];
    $calcVisits = 600;
    $calcLeads = 6;
    $bg = app()->getLocale() === 'bg';
    $num = fn ($n, $d = 0) => number_format($n, $d, $bg ? ',' : '.', $bg ? ' ' : ',');
    $factors = ['visits' => 1, 'conv' => 1, 'leads' => 1];
    foreach ($calcItems as [$key, $label, $area, $pct, $why, $has]) {
        if (! $has) {
            $factors[$area] *= 1 + $pct / 100;
        }
    }
    $afterVisits = $calcVisits * $factors['visits'];
    $nowConv = $calcLeads / $calcVisits * 100;
    $afterConv = $nowConv * $factors['conv'];
    $afterLeads = $afterVisits * $afterConv / 100 * $factors['leads'];
    $rows = [
        ['visits', __('Видимост: посещения на месец'), $calcVisits, $afterVisits, 0, ''],
        ['leads', __('Запитвания на месец'), $calcLeads, $afterLeads, 0, ''],
        ['conv', __('Конверсия: от посетителите ви пишат'), $nowConv, $afterConv, 1, '%'],
    ];
@endphp
<section id="kalkulator" class="relative py-20 md:py-28 bg-white scroll-mt-24 dark:bg-ink-950" data-calc data-locale="{{ $bg ? 'bg-BG' : 'en-GB' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-3xl">
            <p class="kicker kicker-light">{{ $number }} {{ __('Калкулатор') }}</p>
            <h2 class="display mt-5 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ __('Колко можем да подобрим бизнеса ви?') }}</h2>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed dark:text-gray-300">{{ __('Въведете колко посещения и запитвания имате сега и отметнете какво вече работи при вас. Калкулаторът показва с колко ще се подобрят видимостта, запитванията и конверсията, когато добавим липсващото.') }}</p>
        </div>

        <div class="mt-12 grid lg:grid-cols-12 gap-6">
            <form class="reveal lg:col-span-5 rounded-[2rem] border border-gray-200 bg-white p-6 md:p-8 dark:border-white/10 dark:bg-white/[0.03]" onsubmit="return false">
                <h3 class="text-sm font-bold uppercase tracking-wider text-brand-600 dark:text-brand-300">{{ __('Сега при вас') }}</h3>
                <div class="mt-5 grid grid-cols-2 gap-4">
                    @foreach([['visits', __('Посещения на месец'), $calcVisits, 10, 1000000, 10], ['leads', __('Запитвания на месец'), $calcLeads, 0, 100000, 1]] as [$key, $label, $default, $min, $max, $step])
                        <label class="block">
                            <span class="block text-sm font-semibold text-brand-950 dark:text-white">{{ $label }}</span>
                            <input type="number" inputmode="numeric" name="{{ $key }}" value="{{ $default }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" data-calc-input class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 text-lg font-semibold text-brand-950 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-white/15 dark:bg-white/5 dark:text-white">
                        </label>
                    @endforeach
                </div>

                <h3 class="mt-8 text-sm font-bold uppercase tracking-wider text-brand-600 dark:text-brand-300">{{ __('Какво вече имате?') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Отметнете каквото работи. Неотметнатото е това, което ще добавим.') }}</p>
                <div class="mt-4 space-y-2">
                    @foreach($calcItems as [$key, $label, $area, $pct, $why, $has])
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-gray-200 px-4 py-3 transition-colors has-[:checked]:border-emerald-300 has-[:checked]:bg-emerald-50/60 dark:border-white/10 dark:has-[:checked]:border-emerald-400/40 dark:has-[:checked]:bg-emerald-400/10">
                            <input type="checkbox" name="{{ $key }}" value="1" data-calc-item data-area="{{ $area }}" data-pct="{{ $pct }}" @checked($has) class="mt-1 h-4 w-4 accent-emerald-600">
                            <span class="text-sm font-medium leading-snug text-brand-950 dark:text-gray-100">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </form>

            <div class="reveal lg:col-span-7 flex flex-col gap-6" style="--reveal-delay: 100ms" aria-live="polite">
                <div class="relative overflow-hidden rounded-[2rem] bg-brand-950 p-7 md:p-9 text-white dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30">
                    <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
                    <div class="relative">
                        <p class="text-sm font-bold uppercase tracking-wider text-brand-300">{{ __('Подобрението за вашия бизнес') }}</p>
                        <p class="mt-3 display text-5xl md:text-6xl"><span data-calc-out="leadsTimes">×{{ $num($afterLeads / max(0.0001, $calcLeads), 1) }}</span> <span class="text-2xl md:text-3xl text-brand-200">{{ __('запитвания') }}</span></p>
                        <div class="mt-7 space-y-5">
                            @foreach($rows as [$key, $label, $now, $after, $decimals, $unit])
                                <div>
                                    <div class="flex flex-wrap items-baseline justify-between gap-2 text-sm">
                                        <span class="font-semibold text-gray-200">{{ $label }}</span>
                                        <span class="tabular-nums"><span class="text-gray-400" data-calc-out="{{ $key }}Now">{{ $num($now, $decimals) }}{{ $unit }}</span> → <strong class="text-lg text-white" data-calc-out="{{ $key }}After">{{ $num($after, $decimals) }}{{ $unit }}</strong> <span class="ml-1 rounded-full bg-emerald-400/15 px-2 py-0.5 text-xs font-bold text-emerald-300" data-calc-out="{{ $key }}Up">+{{ $num(($after / max(0.0001, $now) - 1) * 100) }}%</span></span>
                                    </div>
                                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-emerald-400/70" title="{{ __('Зелено: с подобренията') }}">
                                        <div class="h-full rounded-full bg-brand-400 transition-[width] duration-500" style="width: {{ min(100, $now / max(0.0001, $after) * 100) }}%" data-calc-bar="{{ $key }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-7 flex flex-col sm:flex-row gap-3">
                            <a href="{{ lroute('packages') }}#oferta" class="btn-shine btn-glow inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 font-semibold text-brand-950 hover:bg-brand-50">{{ __('Получете оферта') }} <x-icon name="arrow-right" class="text-sm" /></a>
                            <a href="{{ lroute('packages') }}" class="inline-flex items-center justify-center rounded-xl border border-white/25 px-6 py-3.5 font-semibold text-white hover:bg-white/10">{{ __('Вижте пакетите') }}</a>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-gray-200 p-6 dark:border-white/10">
                    <h3 class="font-bold text-brand-950 dark:text-white">{{ __('Какво ще подобрим') }}</h3>
                    <ul class="mt-3 space-y-2" data-calc-list>
                        @foreach($calcItems as [$key, $label, $area, $pct, $why, $has])
                            <li class="flex items-start gap-3 text-sm {{ $has ? 'hidden' : '' }}" data-calc-improve="{{ $key }}">
                                <span class="mt-0.5 shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700 dark:bg-white/10 dark:text-brand-300">+{{ $pct }}%</span>
                                <span class="text-gray-700 dark:text-gray-300"><strong class="text-brand-950 dark:text-white">{{ $label }}</strong>: {{ $why }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-3 hidden text-sm text-gray-600 dark:text-gray-300" data-calc-all>{{ __('Имате основите. Ще ги поддържаме и ще търсим къде още да растете: на консултация ще видим вашите числа.') }}</p>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Синьо: сега. Зелено: с подобренията.') }} {{ __('Сметката е ориентировъчна. Процентите са умерени оценки по данни на Google и Deloitte и от нашата работа; реалният резултат зависи от бранша, града и конкуренцията. Не обещаваме конкретен резултат.') }}</p>
            </div>
        </div>
    </div>
</section>
<script>
(function () {
    var root = document.querySelector('[data-calc]');
    if (!root) return;
    var loc = root.dataset.locale;
    function fmt(n, d) { return new Intl.NumberFormat(loc, { maximumFractionDigits: d, minimumFractionDigits: d }).format(n); }
    function update() {
        var visits = Math.max(1, parseFloat(root.querySelector('[name=visits]').value) || 0);
        var leads = Math.max(0, parseFloat(root.querySelector('[name=leads]').value) || 0);
        var f = { visits: 1, conv: 1, leads: 1 }, missing = 0;
        root.querySelectorAll('[data-calc-item]').forEach(function (box) {
            var li = root.querySelector('[data-calc-improve="' + box.name + '"]');
            if (!box.checked) { f[box.dataset.area] *= 1 + Number(box.dataset.pct) / 100; missing++; }
            li.classList.toggle('hidden', box.checked);
        });
        root.querySelector('[data-calc-all]').classList.toggle('hidden', missing > 0);
        var convNow = leads / visits * 100, visitsAfter = visits * f.visits, convAfter = convNow * f.conv;
        var leadsAfter = visitsAfter * convAfter / 100 * f.leads;
        var rows = { visits: [visits, visitsAfter, 0, ''], leads: [leads, leadsAfter, 0, ''], conv: [convNow, convAfter, 1, '%'] };
        Object.keys(rows).forEach(function (k) {
            var r = rows[k];
            root.querySelector('[data-calc-out="' + k + 'Now"]').textContent = fmt(r[0], r[2]) + r[3];
            root.querySelector('[data-calc-out="' + k + 'After"]').textContent = fmt(r[1], r[2]) + r[3];
            root.querySelector('[data-calc-out="' + k + 'Up"]').textContent = '+' + fmt(r[0] ? (r[1] / r[0] - 1) * 100 : 0, 0) + '%';
            root.querySelector('[data-calc-bar="' + k + '"]').style.width = (r[1] ? Math.min(100, r[0] / r[1] * 100) : 0) + '%';
        });
        root.querySelector('[data-calc-out="leadsTimes"]').textContent = '×' + fmt(leads ? leadsAfter / leads : f.visits * f.conv * f.leads, 1);
    }
    root.addEventListener('input', update);
    root.addEventListener('change', update);
    update();
})();
</script>
