{{--
    The funnel of the services page in design G (same data and behaviour as partials/engine-diagram, drawn with the g palette): customers fall through
    the levels of home_trust.funnel_levels; a level switched off turns the section signal red, its balls spill out and a panel says what really happens.
    Without JavaScript the list is shown. Put it on a dark band (.g-band).
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
    <div class="g-fn" style="margin-top:clamp(40px,6vw,72px)" data-funnel data-funnel-data='@json($funnelData)'>
        <div class="g-fn-left">
            <div class="g-fn-box" data-funnel-box>
                <div class="g-fn-top">
                    <span class="g-fn-status g-mono" data-funnel-status role="status"><i aria-hidden="true"></i><span data-funnel-status-text>{{ __('Двигателят работи') }}</span></span>
                    <span class="g-fn-count"><b data-funnel-count>…</b><small>{{ __('стигат до вас като клиенти') }}</small></span>
                </div>
                <div class="g-fn-canvas">
                    <canvas data-funnel-canvas role="img" aria-label="{{ __('Фуния, в която клиентите падат отгоре и минават през всяко ниво, докато стигнат до вас.') }}"></canvas>
                </div>
                <div class="g-fn-chips">
                    @foreach($funnelSteps as $step)
                        <button type="button" role="switch" aria-checked="true" data-funnel-toggle="{{ $loop->index }}">{{ $loop->iteration }} · {{ $step['stage'] }}</button>
                    @endforeach
                </div>
            </div>
            <div class="g-fn-panel" data-funnel-panel role="status" aria-live="polite">
                <p><span data-funnel-panel-title>{{ $funnelOkTitle }}</span></p>
                <p class="g-fn-panel-text" data-funnel-panel-text>{{ $funnelOkText }}</p>
            </div>
        </div>

        <ol class="g-fn-steps">
            @foreach($funnelSteps as $step)
                <li data-funnel-step="{{ $loop->index }}">
                    <div class="g-fn-head">
                        <span class="g-mono">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <p class="g-mono g-fn-stage">{{ $step['stage'] }}@if(filled($step['tag'] ?? null)) <em>{{ $step['tag'] }}</em>@endif <span class="g-fn-badge" data-funnel-badge>{{ __('Работи') }}</span></p>
                            <h3>{{ $step['works_title'] ?? '' }}</h3>
                        </div>
                        <button type="button" class="g-switch" role="switch" aria-checked="true" aria-label="{{ $step['stage'] }}" data-funnel-toggle="{{ $loop->index }}"><span data-funnel-knob></span></button>
                    </div>
                    @if(filled($step['works_text'] ?? null))<p class="g-fn-text">{{ $step['works_text'] }}</p>@endif
                    @if(filled($step['lesson'] ?? null))<p class="g-fn-note"><b>{{ __('Урок за фунията') }}</b>{{ $step['lesson'] }}</p>@endif
                    <p class="g-fn-note g-fn-without" data-funnel-without><b>{{ __('Ако спре') }}</b><strong>{{ $step['off_title'] ?? '' }}</strong>@if(filled($step['off_text'] ?? null))<span>{{ $step['off_text'] }}</span>@endif</p>
                    <p class="g-fn-flow">
                        <span>{{ __('Получава хората от') }} <b>{{ $loop->first ? __('търсенията в Google и AI') : $funnelSteps[$loop->index - 1]['stage'] }}</b></span>
                        <span>{{ __('Подава ги на') }} <b>{{ $loop->last ? __('вас, като нов клиент') : $funnelSteps[$loop->index + 1]['stage'] }}</b></span>
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
        var countEl = root.querySelector('[data-funnel-count]');
        var panel = root.querySelector('[data-funnel-panel]'), panelTitle = panel.querySelector('[data-funnel-panel-title]'), panelText = panel.querySelector('[data-funnel-panel-text]');
        var section = root.closest('section');
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var T = {
            running: @json(__('Двигателят работи')), stopped: @json(__('Двигателят спря')), problem: @json(__('Проблем')),
            badgeOk: @json(__('Работи')), badgeOff: @json(__('Спряно')), badgeVain: @json(__('Работи напразно')), badgeNone: @json(__('Не получава клиенти')),
            perMonth: @json(__('на месец')), closed: @json(__('затворено')), clients: @json(__('Клиенти')), search: @json(__('Търсят в Google и AI'))
        };
        // g palette (the funnel sits on the dark band in both themes)
        var LIGHT = '236,238,240', SIGNAL = '255,90,51', MUTE = '154,160,169';
        var FONT = '500 11px "JetBrains Mono", ui-monospace, monospace';
        var W = 0, H = 0, balls = [], arrived = [], last = 0, spawnAt = 0;

        function size() {
            var dpr = Math.min(2, window.devicePixelRatio || 1);
            W = canvas.clientWidth; H = canvas.clientHeight;
            canvas.width = W * dpr; canvas.height = H * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        function geo() {
            var top = 30, bottom = H - 62, lh = (bottom - top) / n, cx = W / 2;
            return { top: top, bottom: bottom, lh: lh, cx: cx, wTop: W * 0.46, wBottom: Math.max(40, W * 0.2) };
        }
        function half(g, y) { var t = Math.max(0, Math.min(1, (y - g.top) / (g.bottom - g.top))); return g.wTop + (g.wBottom - g.wTop) * t; }
        function firstOff() { return on.indexOf(false); }

        function render() {
            var off = firstOff(), running = off === -1;
            root.querySelectorAll('[data-funnel-toggle]').forEach(function (b) { b.setAttribute('aria-checked', on[+b.dataset.funnelToggle] ? 'true' : 'false'); });
            steps.forEach(function (li, i) {
                var badge = li.querySelector('[data-funnel-badge]'), label, state;
                if (!on[i]) { label = T.badgeOff; state = 'off'; }
                else if (running) { label = T.badgeOk; state = 'ok'; }
                else if (i < off) { label = T.badgeVain; state = 'vain'; }
                else { label = T.badgeNone; state = 'off'; }
                badge.textContent = label;
                badge.setAttribute('data-state', state);
                li.classList.toggle('is-off', !on[i]);
            });
            root.classList.toggle('is-alert', !running);
            if (section) section.classList.toggle('is-alert', !running);
            statusText.textContent = running ? T.running : T.stopped;
            if (running) { panelTitle.textContent = data.ok.title; panelText.textContent = data.ok.text; }
            else { var a = data.alerts[off]; panelTitle.textContent = T.problem + '. ' + a.title; panelText.textContent = a.text; }
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
                balls.push({ x: g.cx + (Math.random() - 0.5) * g.wTop * 1.6, y: 4, vx: (Math.random() - 0.5) * 0.3, vy: 0, out: false, a: 1, r: 3 + Math.random() * 1.4 });
            }
            ctx.clearRect(0, 0, W, H);

            for (var i = 0; i < n; i++) {
                var y0 = g.top + i * g.lh, y1 = y0 + g.lh - 4, ok = on[i];
                ctx.beginPath();
                ctx.moveTo(g.cx - half(g, y0), y0); ctx.lineTo(g.cx + half(g, y0), y0);
                ctx.lineTo(g.cx + half(g, y1), y1); ctx.lineTo(g.cx - half(g, y1), y1); ctx.closePath();
                ctx.fillStyle = ok ? 'rgba(' + LIGHT + ',' + (0.04 + i * 0.025) + ')' : 'rgba(' + SIGNAL + ',0.16)';
                ctx.fill();
                ctx.lineWidth = 1; ctx.strokeStyle = ok ? 'rgba(' + LIGHT + ',0.4)' : 'rgba(' + SIGNAL + ',0.95)'; ctx.stroke();
                ctx.textAlign = 'center'; ctx.font = FONT;
                ctx.fillStyle = ok ? 'rgba(' + LIGHT + ',0.9)' : 'rgba(' + SIGNAL + ',1)';
                ctx.fillText((i + 1) + ' · ' + stages[i], g.cx, y0 + g.lh / 2 + (ok ? 4 : -1), Math.max(90, 2 * half(g, y0 + g.lh / 2) - 14));
                if (!ok) {
                    ctx.lineWidth = 3; ctx.strokeStyle = 'rgba(' + SIGNAL + ',1)';
                    ctx.beginPath(); ctx.moveTo(g.cx - half(g, y0) + 4, y0); ctx.lineTo(g.cx + half(g, y0) - 4, y0); ctx.stroke();
                    ctx.font = '500 10px "JetBrains Mono", ui-monospace, monospace'; ctx.fillText('✕ ' + T.closed, g.cx, y0 + g.lh / 2 + 12);
                }
            }
            var all = firstOff() === -1;
            ctx.fillStyle = all ? 'rgba(' + LIGHT + ',0.08)' : 'rgba(' + SIGNAL + ',0.14)'; ctx.strokeStyle = all ? 'rgba(' + LIGHT + ',0.6)' : 'rgba(' + SIGNAL + ',0.9)'; ctx.lineWidth = 1;
            ctx.beginPath(); ctx.rect(g.cx - 70, H - 40, 140, 30); ctx.fill(); ctx.stroke();
            ctx.fillStyle = all ? 'rgba(' + LIGHT + ',1)' : 'rgba(' + SIGNAL + ',1)'; ctx.font = '500 12px "JetBrains Mono", ui-monospace, monospace'; ctx.textAlign = 'center';
            ctx.fillText(T.clients.toUpperCase(), g.cx, H - 21);
            ctx.fillStyle = 'rgba(' + MUTE + ',1)'; ctx.font = '500 10px "JetBrains Mono", ui-monospace, monospace';
            ctx.fillText(T.search, g.cx, 14);

            var grav = 0.0011 * dt, k = dt / 16;
            balls.forEach(function (b) {
                b.vy += grav * 16; b.x += b.vx * k; b.y += b.vy * k;
                if (!b.out && b.y > g.top && b.y < g.bottom) {
                    var lvl = Math.floor((b.y - g.top) / g.lh), boundary = g.top + lvl * g.lh;
                    if (!on[lvl] && (b.lvl === undefined || b.lvl < lvl)) {
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
                ctx.fillStyle = b.out ? 'rgb(' + SIGNAL + ')' : (b.y >= g.bottom ? 'rgb(' + SIGNAL + ')' : 'rgb(' + LIGHT + ')');
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
            countEl.classList.toggle('is-zero', perMonth === 0);
            requestAnimationFrame(frame);
        }
        size(); render();
        window.addEventListener('resize', size);
        if (window.ResizeObserver) new ResizeObserver(size).observe(canvas);
        requestAnimationFrame(frame);
    })();
    </script>
@endif
