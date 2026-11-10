{{--
    The improvement calculator of the services page in design G (same maths and data attributes as partials/calculator). $number: optional prefix of the kicker.
--}}
@php
    // [ключ, текст, къде действа (visits|conv|leads), процент, обяснение, имат ли го по подразбиране]
    $calcItems = [
        ['profile', __('Попълнен Google профил със снимки, отзиви и работно време'), 'visits', 20, __('повече хора ви намират в Google Maps'), false],
        ['seo', __('SEO и видимост в AI асистентите (ChatGPT, Gemini)'), 'visits', 25, __('повече посещения от търсения'), false],
        ['ads', __('Реклама в Google и Meta, насочена към вашия град'), 'visits', 30, __('повече посещения от хора, които търсят сега'), false],
        ['brand', __('Единен бранд с лого, цветове и тон навсякъде'), 'conv', 5, __('повече доверие, повече хора ви пишат'), true],
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
        ['visits', __('Видимост в посещения на месец'), $calcVisits, $afterVisits, 0, ''],
        ['leads', __('Запитвания на месец'), $calcLeads, $afterLeads, 0, ''],
        ['conv', __('Конверсия, или колко от посетителите ви пишат'), $nowConv, $afterConv, 1, '%'],
    ];
@endphp
<section id="kalkulator" class="g-s g-calc" data-calc data-locale="{{ $bg ? 'bg-BG' : 'en-GB' }}">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono">@if(filled($number ?? null))<b>{{ $number }}</b>@endif{{ __('Калкулатор') }}</p>
            <h2 class="g-h">{{ __('Колко можем да подобрим бизнеса ви?') }}</h2>
            <p class="g-sh-text">{{ __('Въведете колко посещения и запитвания имате сега и отметнете какво вече работи при вас. Калкулаторът показва с колко ще се подобрят видимостта, запитванията и конверсията, когато добавим липсващото.') }}</p>
        </div>

        <div class="g-calc-grid">
            <form class="g-calc-form" onsubmit="return false">
                <h3 class="g-mono">{{ __('Сега при вас') }}</h3>
                <div class="g-fgrid">
                    @foreach([['visits', __('Посещения на месец'), $calcVisits, 10, 1000000, 10], ['leads', __('Запитвания на месец'), $calcLeads, 0, 100000, 1]] as [$key, $label, $default, $min, $max, $step])
                        <div class="g-fld">
                            <label for="calc-{{ $key }}">{{ $label }}</label>
                            <input class="g-in" id="calc-{{ $key }}" type="number" inputmode="numeric" name="{{ $key }}" value="{{ $default }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" data-calc-input>
                        </div>
                    @endforeach
                </div>
                <h3 class="g-mono">{{ __('Какво вече имате?') }}</h3>
                <p class="g-hint">{{ __('Отметнете каквото работи. Неотметнатото е това, което ще добавим.') }}</p>
                <div class="g-calc-items">
                    @foreach($calcItems as [$key, $label, $area, $pct, $why, $has])
                        <label class="g-chk"><input type="checkbox" name="{{ $key }}" value="1" data-calc-item data-area="{{ $area }}" data-pct="{{ $pct }}" @checked($has)><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </form>

            <div class="g-calc-out" aria-live="polite">
                <div class="g-calc-result">
                    <p class="g-mono">{{ __('Подобрението за вашия бизнес') }}</p>
                    <p class="g-calc-big"><span data-calc-out="leadsTimes">×{{ $num($afterLeads / max(0.0001, $calcLeads), 1) }}</span> <small>{{ __('запитвания') }}</small></p>
                    <div class="g-calc-rows">
                        @foreach($rows as [$key, $label, $now, $after, $decimals, $unit])
                            <div>
                                <div class="g-calc-line">
                                    <span>{{ $label }}</span>
                                    <span><i data-calc-out="{{ $key }}Now">{{ $num($now, $decimals) }}{{ $unit }}</i> → <b data-calc-out="{{ $key }}After">{{ $num($after, $decimals) }}{{ $unit }}</b> <em data-calc-out="{{ $key }}Up">+{{ $num(($after / max(0.0001, $now) - 1) * 100) }}%</em></span>
                                </div>
                                <div class="g-bar2" title="{{ __('Оранжевото означава с подобренията') }}"><div data-calc-bar="{{ $key }}" style="width: {{ min(100, $now / max(0.0001, $after) * 100) }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="g-cta">
                        <a class="g-btn g-onband" href="{{ lroute('packages') }}#oferta">{{ __('Изпратете запитване') }}<i></i></a>
                        <a class="g-btn g-ghost g-onband-ghost" href="{{ lroute('packages') }}">{{ __('Вижте пакетите') }}</a>
                    </div>
                </div>
                <div class="g-calc-improve">
                    <h3 class="g-mono">{{ __('Какво ще подобрим') }}</h3>
                    <ul data-calc-list>
                        @foreach($calcItems as [$key, $label, $area, $pct, $why, $has])
                            <li class="{{ $has ? 'hidden' : '' }}" data-calc-improve="{{ $key }}"><span class="g-mono">+{{ $pct }}%</span><span><b>{{ $label }}</b>{{ \Illuminate\Support\Str::ucfirst($why) }}</span></li>
                        @endforeach
                    </ul>
                    <p class="g-hint hidden" data-calc-all>{{ __('Имате основите. Ще ги поддържаме и ще търсим къде още да растете. На разговор ще видим вашите числа.') }}</p>
                </div>
                <p class="g-hint">{{ __('Оранжевото е с подобренията, сивото е сега.') }} {{ __('Сметката е ориентировъчна. Процентите са умерени оценки по данни на Google и Deloitte и от нашата работа; реалният резултат зависи от бранша, града и конкуренцията. Не обещаваме конкретен резултат.') }}</p>
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
