{{--
    „Дигиталният двигател“ като фуния (собствениците, ноември 2026): топчетата са клиенти, които падат отгоре
    и минават през седемте нива от home_trust.funnel_levels (Откриване → Ангажиране → Обмисляне → Продажба →
    Въвеждане и поддръжка → Задържане и развитие → Лоялност и препоръки).
    Всяко ниво има: stage + tag (английската дума), works_title / works_text (какво правим), lesson („Урок за фунията“),
    off_title / off_text (какво става, ако спре) и икона.
    Изключите ли ниво, то се затваря, клиентите изпадат, а ЦЯЛАТА секция става червена като известие за проблем,
    с off_title / off_text на първото спряно ниво; включите ли го пак, всичко е нормално. Без JavaScript се вижда списъкът.
    $cards и $center: не се ползват вече, остават за съвместимост. Слага се върху тъмна секция (on-dark).
--}}
@php
    $funnelTrust = app(\App\Support\Content::class)->withDefaults('home_trust');
    $funnelSteps = collect($funnelTrust['funnel_levels'] ?? [])->filter(fn ($s) => filled($s['stage'] ?? null))->values();
    $funnelOkTitle = $funnelTrust['funnel_ok_title'] ?? __('Всичко работи');
    $funnelOkText = $funnelTrust['funnel_ok_text'] ?? '';
    // Данните за скрипта: какво реално се случва, когато спре всяко ниво.
    $funnelData = [
        'stages' => $funnelSteps->pluck('stage')->all(),
        'ok' => ['title' => $funnelOkTitle, 'text' => $funnelOkText],
        'alerts' => $funnelSteps->map(fn ($s) => [
            'title' => $s['off_title'] ?? $s['stage'],
            'text' => $s['off_text'] ?? '',
            'points' => [],
        ])->all(),
    ];
@endphp
@if($funnelSteps->count() >= 2)
    <div class="reveal mt-12 grid gap-8 lg:grid-cols-12" style="--reveal-delay: 80ms" data-funnel data-funnel-data='@json($funnelData)'>
        {{-- Фунията се разтяга на височината на списъка вдясно, за да няма празно място под нея. --}}
        <div class="flex flex-col lg:col-span-5">
            <div class="flex flex-1 flex-col rounded-3xl bg-white/[0.03] p-4 ring-1 ring-white/10 transition-colors duration-500 sm:p-5" data-funnel-box>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-300" data-funnel-status role="status">
                        <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span><span data-funnel-status-text>{{ __('Двигателят работи') }}</span>
                    </span>
                    <span class="text-right">
                        <span class="block text-2xl font-extrabold leading-none tabular-nums" data-funnel-count>…</span>
                        <span class="text-xs text-gray-400">{{ __('стигат до вас като клиенти') }}</span>
                    </span>
                </div>
                <div class="relative mt-3 min-h-[440px] flex-1 sm:min-h-[500px] lg:min-h-[600px]">
                    <canvas class="absolute inset-0 block h-full w-full" data-funnel-canvas role="img"
                            aria-label="{{ __('Фуния, в която клиентите падат отгоре и минават през всяко ниво, докато стигнат до вас.') }}"></canvas>
                </div>
                {{-- На телефон превключвателите до текстовете са далеч под фунията: тук са същите, наблизо. --}}
                <div class="mt-3 grid grid-cols-1 gap-2 min-[420px]:grid-cols-2 lg:hidden">
                    @foreach($funnelSteps as $step)
                        <button type="button" role="switch" aria-checked="true" data-funnel-toggle="{{ $loop->index }}"
                                class="rounded-xl px-3 py-2 text-left text-xs font-bold ring-1 ring-emerald-400/40 bg-emerald-400/10 text-emerald-200 aria-[checked=false]:bg-red-500/15 aria-[checked=false]:text-red-200 aria-[checked=false]:ring-red-400/50">
                            {{ $loop->iteration }} · {{ $step['stage'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Известието: нормално „Всичко работи“, а при спряно ниво червено съобщение какво реално се случва. --}}
            <div class="mt-4 rounded-2xl bg-white/[0.04] p-5 ring-1 ring-white/10 transition-colors duration-500" data-funnel-panel role="status" aria-live="polite">
                <p class="flex items-center gap-2.5 text-lg font-extrabold leading-snug">
                    <span data-funnel-icon-ok><x-icon name="circle-check" class="text-emerald-300" /></span>
                    <span class="hidden" data-funnel-icon-alert><x-icon name="triangle-exclamation" class="text-red-300" /></span>
                    <span data-funnel-panel-title>{{ $funnelOkTitle }}</span>
                </p>
                <p class="mt-2 leading-relaxed text-gray-300" data-funnel-panel-text>{{ $funnelOkText }}</p>
                <ul class="mt-3 hidden space-y-2 text-sm" data-funnel-panel-points></ul>
            </div>
        </div>

        <ol class="space-y-3 lg:col-span-7">
            @foreach($funnelSteps as $step)
                <li class="edge glass rounded-2xl p-5 transition-[box-shadow,background-color] duration-500" data-funnel-step="{{ $loop->index }}">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-500/20"><x-icon :name="$step['icon'] ?? 'check'" class="text-brand-200" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-bold uppercase tracking-[0.16em] text-brand-300">
                                <span>{{ $loop->iteration }} · {{ $step['stage'] }}</span>
                                @if(filled($step['tag'] ?? null))<span class="text-[11px] font-semibold normal-case tracking-normal text-gray-400">{{ $step['tag'] }}</span>@endif
                                <span class="rounded-full bg-emerald-400/15 px-2 py-0.5 text-[10px] tracking-wider text-emerald-300" data-funnel-badge>{{ __('Работи') }}</span>
                            </p>
                            <h3 class="mt-1 text-lg font-extrabold leading-snug">{{ $step['works_title'] ?? '' }}</h3>
                        </div>
                        <button type="button" role="switch" aria-checked="true" aria-label="{{ $step['stage'] }}" data-funnel-toggle="{{ $loop->index }}"
                                class="relative h-7 w-12 shrink-0 rounded-full bg-emerald-500/80 ring-1 ring-white/20 transition-colors aria-[checked=false]:bg-red-500/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300">
                            <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition-transform" style="transform: translateX(1.25rem)" data-funnel-knob></span>
                        </button>
                    </div>
                    @if(filled($step['works_text'] ?? null))<p class="mt-3 leading-relaxed text-gray-200">{{ $step['works_text'] }}</p>@endif
                    @if(filled($step['lesson'] ?? null))
                        <p class="mt-3 rounded-xl bg-white/[0.05] px-3 py-2 text-sm leading-relaxed text-gray-300"><span class="block font-semibold text-brand-200">{{ __('Урок за фунията') }}</span>{{ $step['lesson'] }}</p>
                    @endif
                    <p class="mt-3 rounded-xl bg-red-500/[0.07] px-3 py-2 text-sm leading-relaxed text-gray-300" data-funnel-without><span class="block font-semibold text-red-300">{{ __('Ако спре') }}</span><strong class="font-bold text-gray-100">{{ $step['off_title'] ?? '' }}</strong>@if(filled($step['off_text'] ?? null))<span class="mt-0.5 block" data-funnel-without-text>{{ $step['off_text'] }}</span>@endif</p>
                    <p class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                        <span>{{ __('Получава хората от') }} <strong class="text-gray-200">{{ $loop->first ? __('търсенията в Google и AI') : $funnelSteps[$loop->index - 1]['stage'] }}</strong></span>
                        <span>{{ __('Подава ги на') }} <strong class="text-gray-200">{{ $loop->last ? __('вас, като нов клиент') : $funnelSteps[$loop->index + 1]['stage'] }}</strong></span>
                    </p>
                </li>
            @endforeach
        </ol>
    </div>
    <script>
    (function () {
        var root = document.querySelector('[data-funnel]');
        if (!root) return;
        var data = JSON.parse(root.dataset.funnelData), stages = data.stages, n = stages.length, on = stages.map(function () { return true; });
        var canvas = root.querySelector('[data-funnel-canvas]'), ctx = canvas.getContext('2d');
        var steps = root.querySelectorAll('[data-funnel-step]');
        var status = root.querySelector('[data-funnel-status]'), statusText = root.querySelector('[data-funnel-status-text]');
        var countEl = root.querySelector('[data-funnel-count]'), box = root.querySelector('[data-funnel-box]');
        var panel = root.querySelector('[data-funnel-panel]'), panelTitle = panel.querySelector('[data-funnel-panel-title]'), panelText = panel.querySelector('[data-funnel-panel-text]'), panelPoints = panel.querySelector('[data-funnel-panel-points]');
        var iconOk = panel.querySelector('[data-funnel-icon-ok]'), iconAlert = panel.querySelector('[data-funnel-icon-alert]');
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var T = {
            running: @json(__('Двигателят работи')), stopped: @json(__('Двигателят спря')), problem: @json(__('Проблем')),
            badgeOk: @json(__('Работи')), badgeOff: @json(__('Спряно')), badgeVain: @json(__('Работи напразно')), badgeNone: @json(__('Не получава клиенти')),
            perMonth: @json(__('на месец')), closed: @json(__('затворено')), clients: @json(__('Клиенти')), search: @json(__('Търсят в Google и AI'))
        };
        var W = 0, H = 0, balls = [], arrived = [], last = 0, spawnAt = 0;

        // Цялата секция става червена при проблем: прозрачен слой зад съдържанието.
        var section = root.closest('section'), overlay = null;
        if (section) {
            if (getComputedStyle(section).position === 'static') section.style.position = 'relative';
            var child = root; while (child.parentElement && child.parentElement !== section) child = child.parentElement;
            overlay = document.createElement('div');
            overlay.setAttribute('aria-hidden', 'true'); overlay.setAttribute('data-funnel-overlay', '');
            overlay.className = 'pointer-events-none absolute inset-0';
            overlay.style.cssText = 'opacity:0;transition:opacity .7s ease;background:radial-gradient(120% 90% at 50% 0%,rgba(220,38,38,.55),rgba(127,29,29,.5) 55%,rgba(69,10,10,.65));box-shadow:inset 0 0 0 3px rgba(248,113,113,.55),inset 0 0 160px rgba(239,68,68,.35)';
            section.insertBefore(overlay, child);
        }

        function size() {
            var dpr = Math.min(2, window.devicePixelRatio || 1);
            W = canvas.clientWidth; H = canvas.clientHeight;
            canvas.width = W * dpr; canvas.height = H * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        // Геометрия: фунията заема горните ~80%, отдолу гърлото и „Клиенти“.
        function geo() {
            var top = 30, bottom = H - 62, lh = (bottom - top) / n, cx = W / 2;
            return { top: top, bottom: bottom, lh: lh, cx: cx, wTop: W * 0.46, wBottom: Math.max(40, W * 0.2) };
        }
        function half(g, y) { var t = Math.max(0, Math.min(1, (y - g.top) / (g.bottom - g.top))); return g.wTop + (g.wBottom - g.wTop) * t; }
        function firstOff() { return on.indexOf(false); }
        function setClasses(el, add, remove) { remove.forEach(function (c) { el.classList.remove(c); }); add.forEach(function (c) { el.classList.add(c); }); }

        function render() {
            var off = firstOff(), running = off === -1;
            root.querySelectorAll('[data-funnel-toggle]').forEach(function (b) { b.setAttribute('aria-checked', on[+b.dataset.funnelToggle] ? 'true' : 'false'); });
            steps.forEach(function (li, i) {
                li.querySelector('[data-funnel-knob]').style.transform = on[i] ? 'translateX(1.25rem)' : 'translateX(0)';
                li.style.boxShadow = on[i] ? '' : 'inset 0 0 0 1px rgb(248 113 113 / 0.75)';
                li.style.backgroundColor = on[i] ? '' : 'rgb(239 68 68 / 0.10)';
                var badge = li.querySelector('[data-funnel-badge]'), label, cls;
                if (!on[i]) { label = T.badgeOff; cls = ['bg-red-500/25', 'text-red-200']; }
                else if (running) { label = T.badgeOk; cls = ['bg-emerald-400/15', 'text-emerald-300']; }
                else if (i < off) { label = T.badgeVain; cls = ['bg-amber-400/20', 'text-amber-200']; }
                else { label = T.badgeNone; cls = ['bg-red-500/25', 'text-red-200']; }
                badge.textContent = label;
                setClasses(badge, cls, ['bg-emerald-400/15', 'text-emerald-300', 'bg-amber-400/20', 'text-amber-200', 'bg-red-500/25', 'text-red-200']);
                li.querySelector('[data-funnel-without]').classList.toggle('ring-1', !on[i]);
                li.querySelector('[data-funnel-without]').classList.toggle('ring-red-400/40', !on[i]);
            });
            setClasses(status, running ? ['bg-emerald-400/15', 'text-emerald-300'] : ['bg-red-500/20', 'text-red-300'], ['bg-emerald-400/15', 'text-emerald-300', 'bg-red-500/20', 'text-red-300']);
            statusText.textContent = running ? T.running : T.stopped;
            if (overlay) overlay.style.opacity = running ? '0' : '1';
            box.classList.toggle('ring-red-400/50', !running);
            // Известието под фунията.
            iconOk.classList.toggle('hidden', !running); iconAlert.classList.toggle('hidden', running);
            panelPoints.innerHTML = '';
            if (running) {
                panelTitle.textContent = data.ok.title; panelText.textContent = data.ok.text;
                panelPoints.classList.add('hidden');
                setClasses(panel, ['bg-white/[0.04]', 'ring-white/10'], ['bg-red-500/20', 'ring-red-400/60']);
            } else {
                var a = data.alerts[off];
                panelTitle.textContent = T.problem + '. ' + a.title; panelText.textContent = a.text;
                (a.points || []).forEach(function (p) {
                    var li = document.createElement('li'); li.className = 'flex items-start gap-2 text-red-100';
                    var x = document.createElement('span'); x.textContent = '✕'; x.className = 'mt-px text-xs font-extrabold text-red-300';
                    var t = document.createElement('span'); t.textContent = p;
                    li.appendChild(x); li.appendChild(t); panelPoints.appendChild(li);
                });
                panelPoints.classList.toggle('hidden', !(a.points && a.points.length));
                setClasses(panel, ['bg-red-500/20', 'ring-red-400/60'], ['bg-white/[0.04]', 'ring-white/10']);
            }
        }
        root.addEventListener('click', function (e) {
            var b = e.target.closest('[data-funnel-toggle]');
            if (!b) return;
            var i = +b.dataset.funnelToggle; on[i] = !on[i]; render();
        });

        function frame(t) {
            var dt = Math.min(40, t - (last || t)); last = t;
            var g = geo();
            if (t > spawnAt && balls.length < 200) {
                spawnAt = t + (reduce ? 400 : 70);
                balls.push({ x: g.cx + (Math.random() - 0.5) * g.wTop * 1.6, y: 4, vx: (Math.random() - 0.5) * 0.3, vy: 0, out: false, a: 1, r: 3.2 + Math.random() * 1.6 });
            }
            ctx.clearRect(0, 0, W, H);

            // Нивата на фунията.
            for (var i = 0; i < n; i++) {
                var y0 = g.top + i * g.lh, y1 = y0 + g.lh - 4, ok = on[i];
                ctx.beginPath();
                ctx.moveTo(g.cx - half(g, y0), y0); ctx.lineTo(g.cx + half(g, y0), y0);
                ctx.lineTo(g.cx + half(g, y1), y1); ctx.lineTo(g.cx - half(g, y1), y1); ctx.closePath();
                ctx.fillStyle = ok ? 'rgba(59,119,189,' + (0.16 + i * 0.06) + ')' : 'rgba(248,113,113,0.18)';
                ctx.fill();
                ctx.lineWidth = 1.5; ctx.strokeStyle = ok ? 'rgba(143,181,224,0.55)' : 'rgba(248,113,113,0.9)'; ctx.stroke();
                ctx.textAlign = 'center'; ctx.font = '800 11px Manrope Variable, Manrope, system-ui, sans-serif';
                ctx.fillStyle = ok ? 'rgba(232,238,248,0.92)' : 'rgba(254,202,202,1)';
                // Дългите имена се свиват до ширината на нивото (поне 90px), за да се четат и в тясната част.
                ctx.fillText((i + 1) + ' · ' + stages[i], g.cx, y0 + g.lh / 2 + (ok ? 4 : -1), Math.max(90, 2 * half(g, y0 + g.lh / 2) - 14));
                if (!ok) {
                    // Затворен капак най-горе на нивото.
                    ctx.lineWidth = 4; ctx.strokeStyle = 'rgba(248,113,113,0.95)';
                    ctx.beginPath(); ctx.moveTo(g.cx - half(g, y0) + 4, y0); ctx.lineTo(g.cx + half(g, y0) - 4, y0); ctx.stroke();
                    ctx.font = '700 10px Manrope Variable, Manrope, system-ui, sans-serif'; ctx.fillText('✕ ' + T.closed, g.cx, y0 + g.lh / 2 + 12);
                }
            }
            // Гърлото и „Клиенти“.
            ctx.fillStyle = firstOff() === -1 ? 'rgba(52,211,153,0.12)' : 'rgba(248,113,113,0.12)'; ctx.strokeStyle = firstOff() === -1 ? 'rgba(52,211,153,0.6)' : 'rgba(248,113,113,0.6)'; ctx.lineWidth = 1.5;
            ctx.beginPath(); ctx.roundRect ? ctx.roundRect(g.cx - 70, H - 40, 140, 30, 12) : ctx.rect(g.cx - 70, H - 40, 140, 30); ctx.fill(); ctx.stroke();
            ctx.fillStyle = firstOff() === -1 ? 'rgba(167,243,208,1)' : 'rgba(254,202,202,1)'; ctx.font = '800 12px Manrope Variable, Manrope, system-ui, sans-serif'; ctx.textAlign = 'center';
            ctx.fillText(T.clients, g.cx, H - 21);
            ctx.fillStyle = 'rgba(143,161,191,1)'; ctx.font = '700 10px Manrope Variable, Manrope, system-ui, sans-serif';
            ctx.fillText(T.search, g.cx, 14);

            // Топчетата.
            var grav = 0.0011 * dt, k = dt / 16;
            balls.forEach(function (b) {
                b.vy += grav * 16; b.x += b.vx * k; b.y += b.vy * k;
                if (!b.out && b.y > g.top && b.y < g.bottom) {
                    // b.lvl = последното ниво, в което топчето е влязло: проверката не зависи от скоростта (бързо топче не прескача капака).
                    var lvl = Math.floor((b.y - g.top) / g.lh), boundary = g.top + lvl * g.lh;
                    if (!on[lvl] && (b.lvl === undefined || b.lvl < lvl)) {
                        // Удря затворения капак и изпада от фунията.
                        b.out = true; b.y = boundary - 1; b.vy = -1.2 - Math.random(); b.vx = (b.x < g.cx ? -1 : 1) * (1.5 + Math.random() * 2);
                    } else {
                        var h = half(g, b.y) - b.r - 2;
                        if (Math.abs(b.x - g.cx) > h) { b.x = g.cx + Math.sign(b.x - g.cx) * h; b.vx *= -0.35; }
                        b.vx += (g.cx - b.x) * 0.0006 * k; b.vy = Math.min(b.vy, 2.6);
                        b.lvl = lvl;
                    }
                }
                if (!b.out && b.y >= g.bottom) { b.x += (g.cx - b.x) * 0.2; }
                if (b.out) b.a -= 0.012 * k;
                ctx.globalAlpha = Math.max(0, b.a);
                ctx.fillStyle = b.out ? '#f87171' : (b.y >= g.bottom ? '#34d399' : '#bcd3ef');
                ctx.beginPath(); ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2); ctx.fill();
            });
            ctx.globalAlpha = 1;
            balls = balls.filter(function (b) {
                if (!b.out && b.y > H - 30) { arrived.push(t); return false; }
                return b.a > 0 && b.y < H + 20 && b.x > -20 && b.x < W + 20;
            });
            arrived = arrived.filter(function (a) { return t - a < 2500; });
            var perMonth = arrived.length * 2;
            countEl.textContent = perMonth + ' ' + T.perMonth;
            countEl.classList.toggle('text-red-300', perMonth === 0);
            requestAnimationFrame(frame);
        }
        size(); render();
        window.addEventListener('resize', size);
        if (window.ResizeObserver) new ResizeObserver(size).observe(canvas);
        requestAnimationFrame(frame);
    })();
    </script>
@endif
