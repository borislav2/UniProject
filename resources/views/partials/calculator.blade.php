{{--
    Калкулатор на ползата (началната страница, #kalkulator; бутонът в менюто и в hero-то водят тук).
    Посетителят въвежда своите числа; показваме клиентите и приходите сега и при повече посещения и запитвания.
    Без JavaScript се виждат изчисленията за примерните стойности; скриптът ги смята наново при всяка промяна.
    $number: номерът на секцията („03 /“).
--}}
@php
    $calcFields = [
        ['visits', __('Посещения на сайта на месец'), 1000, 50, 100000, 50, ''],
        ['leadRate', __('От тях ви пишат или звънят'), 1.5, 0.1, 30, 0.1, '%'],
        ['closeRate', __('От запитванията стават клиенти'), 30, 1, 100, 1, '%'],
        ['value', __('Средна стойност на един клиент'), 150, 5, 100000, 5, '€'],
    ];
    $calcBoosts = [
        ['visitsUp', __('Повече посещения'), 30, __('Google профил, SEO и видимост в AI асистентите')],
        ['leadsUp', __('Повече запитвания от същите посещения'), 30, __('Бутон за обаждане, бързина, ясни цени и кратка форма')],
    ];
    $calc = function (array $v) {
        $now = $v['visits'] * $v['leadRate'] / 100 * $v['closeRate'] / 100;
        $after = $v['visits'] * (1 + $v['visitsUp'] / 100) * $v['leadRate'] * (1 + $v['leadsUp'] / 100) / 100 * $v['closeRate'] / 100;

        return ['nowClients' => $now, 'nowRevenue' => $now * $v['value'], 'afterClients' => $after, 'afterRevenue' => $after * $v['value'],
            'extraClients' => $after - $now, 'extraMonth' => ($after - $now) * $v['value'], 'extraYear' => ($after - $now) * $v['value'] * 12];
    };
    $defaults = collect($calcFields)->mapWithKeys(fn ($f) => [$f[0] => $f[2]])->all() + collect($calcBoosts)->mapWithKeys(fn ($b) => [$b[0] => $b[2]])->all();
    $r = $calc($defaults);
    $bg = app()->getLocale() === 'bg';
    $money = fn ($n) => '€' . number_format(round($n), 0, $bg ? ',' : '.', $bg ? ' ' : ',');
    $count = fn ($n) => number_format($n, $n < 10 ? 1 : 0, $bg ? ',' : '.', $bg ? ' ' : ',');
@endphp
<section id="kalkulator" class="relative py-20 md:py-28 bg-white scroll-mt-24 dark:bg-ink-950" data-calc data-locale="{{ $bg ? 'bg-BG' : 'en-GB' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal max-w-3xl">
            <p class="kicker kicker-light">{{ $number }} {{ __('Калкулатор') }}</p>
            <h2 class="display mt-5 text-4xl md:text-5xl text-brand-950 dark:text-white">{{ __('Колко повече може да ви носи сайтът?') }}</h2>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed dark:text-gray-300">{{ __('Въведете вашите числа. Калкулаторът показва колко клиенти и приходи ви носи сайтът сега и колко, ако повече хора ви намират и повече от тях ви пишат.') }}</p>
        </div>

        <div class="mt-12 grid lg:grid-cols-12 gap-6">
            <form class="reveal lg:col-span-5 rounded-3xl border border-gray-200 bg-white p-6 md:p-8 dark:border-white/10 dark:bg-white/[0.03]" onsubmit="return false">
                <h3 class="text-sm font-bold uppercase tracking-wider text-brand-600 dark:text-brand-300">{{ __('Вашите числа сега') }}</h3>
                <div class="mt-5 space-y-5">
                    @foreach($calcFields as [$key, $label, $default, $min, $max, $step, $unit])
                        <label class="block">
                            <span class="flex items-baseline justify-between gap-3 text-sm font-semibold text-brand-950 dark:text-white">{{ $label }} @if($unit)<span class="font-normal text-gray-500 dark:text-gray-400">{{ $unit }}</span>@endif</span>
                            <input type="number" inputmode="decimal" name="{{ $key }}" value="{{ $default }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" data-calc-input class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 text-lg font-semibold text-brand-950 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-white/15 dark:bg-white/5 dark:text-white">
                        </label>
                    @endforeach
                </div>

                <h3 class="mt-8 text-sm font-bold uppercase tracking-wider text-brand-600 dark:text-brand-300">{{ __('С по-добър сайт и маркетинг') }}</h3>
                <div class="mt-5 space-y-6">
                    @foreach($calcBoosts as [$key, $label, $default, $how])
                        <label class="block">
                            <span class="flex items-baseline justify-between gap-3 text-sm font-semibold text-brand-950 dark:text-white">{{ $label }} <output class="text-brand-700 dark:text-brand-300" data-calc-show="{{ $key }}">+{{ $default }}%</output></span>
                            <input type="range" name="{{ $key }}" value="{{ $default }}" min="0" max="200" step="5" data-calc-input class="mt-3 w-full accent-brand-700 dark:accent-brand-300">
                            <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $how }}</span>
                        </label>
                    @endforeach
                </div>
            </form>

            <div class="reveal lg:col-span-7 flex flex-col gap-6" style="--reveal-delay: 100ms" aria-live="polite">
                <div class="grid sm:grid-cols-2 gap-6">
                    <div class="rounded-3xl border border-gray-200 bg-gray-50/70 p-6 md:p-7 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Сега') }}</p>
                        <p class="mt-3 display text-4xl text-brand-950 dark:text-white" data-calc-out="nowRevenue">{{ $money($r['nowRevenue']) }}</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">{{ __('на месец от') }} <strong data-calc-out="nowClients">{{ $count($r['nowClients']) }}</strong> {{ __('клиента') }}</p>
                    </div>
                    <div class="rounded-3xl border border-brand-200 bg-brand-50/70 p-6 md:p-7 dark:border-brand-400/30 dark:bg-white/[0.05]">
                        <p class="text-sm font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300">{{ __('С подобренията') }}</p>
                        <p class="mt-3 display text-4xl text-brand-950 dark:text-white" data-calc-out="afterRevenue">{{ $money($r['afterRevenue']) }}</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">{{ __('на месец от') }} <strong data-calc-out="afterClients">{{ $count($r['afterClients']) }}</strong> {{ __('клиента') }}</p>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-3xl bg-brand-950 p-7 md:p-9 text-white dark:bg-brand-900/60 dark:ring-1 dark:ring-brand-400/30">
                    <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
                    <div class="relative">
                        <p class="text-sm font-bold uppercase tracking-wider text-brand-300">{{ __('Разликата за вашия бизнес') }}</p>
                        <p class="mt-3 display text-5xl md:text-6xl"><span data-calc-out="extraYear">{{ $money($r['extraYear']) }}</span> <span class="text-2xl md:text-3xl text-brand-200">{{ __('на година') }}</span></p>
                        <p class="mt-3 text-gray-300">{{ __('Това са') }} <strong class="text-white" data-calc-out="extraMonth">{{ $money($r['extraMonth']) }}</strong> {{ __('на месец и') }} <strong class="text-white" data-calc-out="extraClients">{{ $count($r['extraClients']) }}</strong> {{ __('клиента повече всеки месец.') }}</p>
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <a href="{{ lroute('packages') }}#oferta" class="btn-shine btn-glow inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 font-semibold text-brand-950 hover:bg-brand-50">{{ __('Получете оферта') }} <x-icon name="arrow-right" class="text-sm" /></a>
                            <a href="{{ lroute('packages') }}" class="inline-flex items-center justify-center rounded-xl border border-white/25 px-6 py-3.5 font-semibold text-white hover:bg-white/10">{{ __('Вижте пакетите') }}</a>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    @foreach([
                        ['magnifying-glass', __('Повече хора ви намират'), __('Профил в Google Maps, SEO и съдържание, което AI асистентите разбират, водят хора, които вече търсят това, което предлагате.')],
                        ['phone', __('Повече от тях ви пишат'), __('Бутон за обаждане на всеки екран, бързо зареждане, ясни цени и кратка форма. Посетителят не се чуди как да се свърже с вас.')],
                        ['envelope-open-text', __('Клиентите се връщат'), __('Автоматични имейли след запитване или покупка напомнят за вас. Тази полза не е в сметката отгоре, а идва отделно.')],
                    ] as [$icon, $title, $text])
                        <div class="rounded-2xl border border-gray-200 p-5 dark:border-white/10">
                            <x-icon :name="$icon" class="text-brand-600 dark:text-brand-300" />
                            <p class="mt-3 font-bold text-brand-950 dark:text-white">{{ $title }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Сметката е ориентировъчна и е по вашите числа. Не обещаваме конкретен резултат: на разговор ще видим реалните ви данни.') }}</p>
            </div>
        </div>
    </div>
</section>
<script>
(function () {
    var root = document.querySelector('[data-calc]');
    if (!root) return;
    var loc = root.dataset.locale;
    var money = new Intl.NumberFormat(loc, { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });
    function count(n) { return new Intl.NumberFormat(loc, { maximumFractionDigits: n < 10 ? 1 : 0, minimumFractionDigits: n < 10 ? 1 : 0 }).format(n); }
    function update() {
        var v = {};
        root.querySelectorAll('[data-calc-input]').forEach(function (el) { v[el.name] = Math.max(0, parseFloat(String(el.value).replace(',', '.')) || 0); });
        var now = v.visits * v.leadRate / 100 * v.closeRate / 100;
        var after = v.visits * (1 + v.visitsUp / 100) * v.leadRate * (1 + v.leadsUp / 100) / 100 * v.closeRate / 100;
        var out = { nowClients: count(now), nowRevenue: money.format(now * v.value), afterClients: count(after), afterRevenue: money.format(after * v.value),
            extraClients: count(after - now), extraMonth: money.format((after - now) * v.value), extraYear: money.format((after - now) * v.value * 12) };
        root.querySelectorAll('[data-calc-out]').forEach(function (el) { el.textContent = out[el.dataset.calcOut]; });
        root.querySelectorAll('[data-calc-show]').forEach(function (el) { el.textContent = '+' + v[el.dataset.calcShow] + '%'; });
    }
    root.addEventListener('input', update);
    update();
})();
</script>
