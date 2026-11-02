{{--
    „Дигиталният двигател“ като фуния (собствениците, ноември 2026): топчетата са клиенти, които падат отгоре
    и минават през четирите нива (home_trust.funnel: намират ви → влизат в сайта → пишат и купуват → стигат до вас).
    Всяко ниво е компонент от services_page.cards (име, икона, линк към /uslugi). Изключите ли ниво, то се затваря:
    клиентите изпадат там и долу не стига никой. До фунията всяко ниво казва какво прави, какво става без него и
    от кое ниво получава хора и на кое ги подава. Без JavaScript се вижда списъкът. Слага се върху тъмна секция (on-dark).
    $cards: services_page.cards; $center: не се ползва вече, остава за съвместимост.
--}}
@php
    $funnelTrust = app(\App\Support\Content::class)->withDefaults('home_trust');
    $cardsBySlug = collect($cards)->keyBy('slug');
    $funnelSteps = collect($funnelTrust['funnel'] ?? [])->map(function ($step) use ($cardsBySlug) {
        $card = $cardsBySlug[$step['card'] ?? ''] ?? null;
        if (! $card) {
            return null;
        }
        preg_match('/^(.+?)\s*\((.+)\)\s*$/u', $card['label'], $m);

        return $step + ['name' => $m[1] ?? $card['label'], 'tag' => $m[2] ?? null, 'icon' => $card['icon'], 'slug' => $card['slug']];
    })->filter()->values();
@endphp
@if($funnelSteps->count() >= 2)
    <div class="reveal mt-12 grid gap-8 lg:grid-cols-12 lg:items-start" style="--reveal-delay: 80ms" data-funnel
         data-stages='@json($funnelSteps->pluck('stage'))'>
        <div class="lg:col-span-5 lg:sticky lg:top-28">
            <div class="rounded-3xl bg-white/[0.03] p-4 ring-1 ring-white/10 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-300" data-funnel-status role="status">
                        <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span><span data-funnel-status-text>{{ __('Двигателят работи') }}</span>
                    </span>
                    <span class="text-right">
                        <span class="block text-2xl font-extrabold leading-none tabular-nums" data-funnel-count>…</span>
                        <span class="text-xs text-gray-400">{{ __('стигат до вас като клиенти') }}</span>
                    </span>
                </div>
                <canvas class="mt-3 block h-[380px] w-full sm:h-[460px]" data-funnel-canvas role="img"
                        aria-label="{{ __('Фуния: клиентите падат отгоре и минават през всяко ниво, докато стигнат до вас.') }}"></canvas>
                {{-- На телефон превключвателите до текстовете са далеч под фунията: тук са същите, наблизо. --}}
                <div class="mt-3 grid grid-cols-2 gap-2 lg:hidden">
                    @foreach($funnelSteps as $step)
                        <button type="button" role="switch" aria-checked="true" data-funnel-toggle="{{ $loop->index }}"
                                class="rounded-xl px-3 py-2 text-left text-xs font-bold ring-1 ring-emerald-400/40 bg-emerald-400/10 text-emerald-200 aria-[checked=false]:bg-red-500/15 aria-[checked=false]:text-red-200 aria-[checked=false]:ring-red-400/50">
                            {{ $loop->iteration }} · {{ $step['stage'] }}
                        </button>
                    @endforeach
                </div>
            </div>
            <p class="mt-4 min-h-[4.5rem] leading-relaxed text-gray-300" data-funnel-message aria-live="polite">{{ __('Всички нива работят: хората ви намират, влизат, пишат ви и запитването стига до вас. Изключете едно ниво и вижте какво става.') }}</p>
        </div>

        <ol class="space-y-3 lg:col-span-7">
            @foreach($funnelSteps as $step)
                <li class="edge glass rounded-2xl p-5 transition-[box-shadow,opacity] duration-300" data-funnel-step="{{ $loop->index }}">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-500/20"><x-icon :name="$step['icon']" class="text-brand-200" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-300">{{ $loop->iteration }} · {{ $step['stage'] }}</p>
                            <a href="{{ lroute('services') }}#{{ $step['slug'] }}" class="mt-0.5 block text-lg font-extrabold leading-snug hover:text-brand-100">{{ $step['name'] }}</a>
                        </div>
                        <button type="button" role="switch" aria-checked="true" aria-label="{{ $step['name'] }}" data-funnel-toggle="{{ $loop->index }}"
                                class="relative h-7 w-12 shrink-0 rounded-full bg-emerald-500/80 ring-1 ring-white/20 transition-colors aria-[checked=false]:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300">
                            <span class="absolute left-1 top-1 h-5 w-5 translate-x-5 rounded-full bg-white shadow transition-transform" data-funnel-knob></span>
                        </button>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-gray-300">{{ $step['does'] }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400" data-funnel-without><span class="font-semibold text-red-300">{{ __('Ако спре:') }}</span> {{ $step['without'] }}</p>
                    <p class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                        <span>{{ __('Получава хора от:') }} <strong class="text-gray-200">{{ $loop->first ? __('търсенията в Google и AI') : $funnelSteps[$loop->index - 1]['stage'] }}</strong></span>
                        <span>{{ __('Подава ги на:') }} <strong class="text-gray-200">{{ $loop->last ? __('вас, като нов клиент') : $funnelSteps[$loop->index + 1]['stage'] }}</strong></span>
                    </p>
                </li>
            @endforeach
        </ol>
    </div>
    <script>
    (function () {
        var root = document.querySelector('[data-funnel]');
        if (!root) return;
        var canvas = root.querySelector('[data-funnel-canvas]'), ctx = canvas.getContext('2d');
        var stages = JSON.parse(root.dataset.stages), n = stages.length, on = stages.map(function () { return true; });
        var steps = root.querySelectorAll('[data-funnel-step]');
        var status = root.querySelector('[data-funnel-status]'), statusText = root.querySelector('[data-funnel-status-text]');
        var countEl = root.querySelector('[data-funnel-count]'), message = root.querySelector('[data-funnel-message]');
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var withoutTexts = Array.prototype.map.call(steps, function (li) { return li.querySelector('[data-funnel-without]').lastChild.textContent.trim(); });
        var T = {
            running: @json(__('Двигателят работи')), stopped: @json(__('Двигателят спря')),
            ok: @json(__('Всички нива работят: хората ви намират, влизат, пишат ви и запитването стига до вас. Изключете едно ниво и вижте какво става.')),
            stoppedAt: @json(__('Спря „:stage“.')), after: @json(__('Затова до вас не стига никой, а нивата над него работят напразно.')),
            perMonth: @json(__('на месец')), closed: @json(__('затворено')), clients: @json(__('Клиенти')), search: @json(__('Търсят в Google и AI'))
        };
        var W = 0, H = 0, balls = [], arrived = [], last = 0, spawnAt = 0;

        function size() {
            var dpr = Math.min(2, window.devicePixelRatio || 1);
            W = canvas.clientWidth; H = canvas.clientHeight;
            canvas.width = W * dpr; canvas.height = H * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        // Геометрия: фунията заема горните ~80%, отдолу гърлото и „Клиенти“.
        function geo() {
            var top = 34, bottom = H - 78, lh = (bottom - top) / n, cx = W / 2;
            return { top: top, bottom: bottom, lh: lh, cx: cx, wTop: W * 0.46, wBottom: Math.max(22, W * 0.07) };
        }
        function half(g, y) { var t = Math.max(0, Math.min(1, (y - g.top) / (g.bottom - g.top))); return g.wTop + (g.wBottom - g.wTop) * t; }
        function firstOff() { return on.indexOf(false); }

        function render() {
            root.querySelectorAll('[data-funnel-toggle]').forEach(function (b) { b.setAttribute('aria-checked', on[+b.dataset.funnelToggle] ? 'true' : 'false'); });
            steps.forEach(function (li, i) {
                li.querySelector('[data-funnel-knob]').style.transform = on[i] ? 'translateX(1.25rem)' : 'translateX(0)';
                li.style.boxShadow = on[i] ? '' : 'inset 0 0 0 1px rgb(248 113 113 / 0.65)';
                li.querySelector('[data-funnel-without]').classList.toggle('text-red-200', !on[i]);
            });
            var off = firstOff(), running = off === -1;
            status.classList.toggle('bg-emerald-400/15', running); status.classList.toggle('text-emerald-300', running);
            status.classList.toggle('bg-red-500/20', !running); status.classList.toggle('text-red-300', !running);
            statusText.textContent = running ? T.running : T.stopped;
            message.textContent = running ? T.ok : T.stoppedAt.replace(':stage', stages[off]) + ' ' + withoutTexts[off] + ' ' + T.after;
            message.classList.toggle('text-red-200', !running);
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
                ctx.fillStyle = ok ? 'rgba(59,119,189,' + (0.16 + i * 0.06) + ')' : 'rgba(248,113,113,0.10)';
                ctx.fill();
                ctx.lineWidth = 1.5; ctx.strokeStyle = ok ? 'rgba(143,181,224,0.55)' : 'rgba(248,113,113,0.8)'; ctx.stroke();
                ctx.textAlign = 'center'; ctx.font = '800 12px Manrope Variable, Manrope, system-ui, sans-serif';
                ctx.fillStyle = ok ? 'rgba(232,238,248,0.92)' : 'rgba(252,165,165,1)';
                ctx.fillText((i + 1) + ' · ' + stages[i], g.cx, y0 + g.lh / 2 + 4);
                if (!ok) {
                    // Затворен капак най-горе на нивото.
                    ctx.lineWidth = 4; ctx.strokeStyle = 'rgba(248,113,113,0.95)';
                    ctx.beginPath(); ctx.moveTo(g.cx - half(g, y0) + 4, y0); ctx.lineTo(g.cx + half(g, y0) - 4, y0); ctx.stroke();
                    ctx.font = '700 10px Manrope Variable, Manrope, system-ui, sans-serif'; ctx.fillText('✕ ' + T.closed, g.cx, y0 + g.lh / 2 + 18);
                }
            }
            // Гърлото и „Клиенти“.
            ctx.fillStyle = 'rgba(52,211,153,0.12)'; ctx.strokeStyle = firstOff() === -1 ? 'rgba(52,211,153,0.6)' : 'rgba(248,113,113,0.5)'; ctx.lineWidth = 1.5;
            ctx.beginPath(); ctx.roundRect ? ctx.roundRect(g.cx - 70, H - 40, 140, 30, 12) : ctx.rect(g.cx - 70, H - 40, 140, 30); ctx.fill(); ctx.stroke();
            ctx.fillStyle = 'rgba(167,243,208,1)'; ctx.font = '800 12px Manrope Variable, Manrope, system-ui, sans-serif'; ctx.textAlign = 'center';
            ctx.fillText(T.clients, g.cx, H - 21);
            ctx.fillStyle = 'rgba(143,161,191,1)'; ctx.font = '700 10px Manrope Variable, Manrope, system-ui, sans-serif';
            ctx.fillText(T.search, g.cx, 14);

            // Топчетата.
            var grav = 0.0011 * dt, k = dt / 16;
            balls.forEach(function (b) {
                b.vy += grav * 16; b.x += b.vx * k; b.y += b.vy * k;
                if (!b.out && b.y > g.top && b.y < g.bottom) {
                    var lvl = Math.floor((b.y - g.top) / g.lh), prevY = b.y - b.vy * k, boundary = g.top + lvl * g.lh;
                    if (!on[lvl] && prevY < boundary + 2) {
                        // Удря затворения капак и изпада от фунията.
                        b.out = true; b.y = boundary - 1; b.vy = -1.2 - Math.random(); b.vx = (b.x < g.cx ? -1 : 1) * (1.5 + Math.random() * 2);
                    } else {
                        var h = half(g, b.y) - b.r - 2;
                        if (Math.abs(b.x - g.cx) > h) { b.x = g.cx + Math.sign(b.x - g.cx) * h; b.vx *= -0.35; }
                        b.vx += (g.cx - b.x) * 0.0006 * k; b.vy = Math.min(b.vy, 2.6);
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
        requestAnimationFrame(frame);
    })();
    </script>
@endif
