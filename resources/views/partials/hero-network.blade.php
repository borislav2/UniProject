{{--
    Мрежата на първия екран: шест светещи възела (home_trust.network_nodes: Бранд, Сайт, Google, Реклами, Имейли, Грижа),
    свързани в една система около лаптопа, с бавно пулсиране и светлинки по линиите. Само украса (aria-hidden).
    Лек canvas: започва след зареждането на страницата, спира извън екрана и в скрит таб,
    а при prefers-reduced-motion рисува една неподвижна картина.
--}}
@php($nodes = array_values(array_filter((array) ($nodes ?? []))))
@if(count($nodes) >= 3)
<canvas data-hero-network class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true"
        data-nodes="{{ json_encode(array_slice($nodes, 0, 6), JSON_UNESCAPED_UNICODE) }}"></canvas>
<script>
(function () {
    var canvas = document.currentScript.previousElementSibling;
    var section = canvas.parentElement;
    var labels = JSON.parse(canvas.getAttribute('data-nodes'));
    var ctx = canvas.getContext('2d');
    if (!ctx) return;
    var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // Позиции спрямо кутията на лаптопа (x, y, надпис отляво „l“ или отдясно „r“): по кръга горе-ляво → горе → горе-дясно
    // → долу-дясно → долу → долу-ляво, около устройствата, за да не пречат на заглавието и екраните.
    var wide = [[0.08, -0.04, 'r'], [0.55, -0.1, 'r'], [1.0, 0.15, 'l'], [1.0, 1.0, 'l'], [0.6, 1.06, 'r'], [0.12, 1.03, 'r']];
    var narrow = [[0.08, -0.05, 'r'], [0.45, -0.08, 'r'], [0.94, -0.12, 'l'], [0.94, 1.04, 'l'], [0.5, 1.07, 'r'], [0.08, 1.04, 'r']];
    var w = 0, h = 0, dpr = 1, hubs = [], dust = [], running = false, visible = true, raf = 0, t0 = performance.now();

    function layout() {
        var r = section.getBoundingClientRect();
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        w = r.width; h = r.height;
        canvas.width = Math.round(w * dpr); canvas.height = Math.round(h * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        var box = section.querySelector('[data-hero-visual]');
        var b = box ? box.getBoundingClientRect() : { left: r.left + w * 0.55, top: r.top + h * 0.15, width: w * 0.4, height: h * 0.5 };
        var small = w < 640;
        var spots = w < 1024 ? narrow : wide;
        hubs = labels.map(function (label, i) {
            var s = spots[i % spots.length];
            var x = b.left - r.left + b.width * s[0], y = b.top - r.top + b.height * s[1];
            return { x: Math.max(18, Math.min(w - 18, x)), y: Math.max(18, Math.min(h - 18, y)), label: label, side: s[2], phase: i * 1.05, small: small };
        });
        dust = [];
        for (var i = 0; i < (small ? 10 : 22); i++) {
            dust.push({ x: Math.random() * w, y: Math.random() * h, vx: (Math.random() - 0.5) * 0.12, vy: (Math.random() - 0.5) * 0.12 });
        }
    }

    function draw(now) {
        var t = (now - t0) / 1000;
        ctx.clearRect(0, 0, w, h);
        var n = hubs.length, i, j, a, b2;
        // Линиите: кръгът на системата и по-бледи връзки през средата.
        ctx.lineWidth = 1;
        for (i = 0; i < n; i++) {
            for (j = i + 1; j < n; j++) {
                a = hubs[i]; b2 = hubs[j];
                var ring = j === i + 1 || (i === 0 && j === n - 1);
                ctx.strokeStyle = ring ? 'rgba(143,181,224,0.28)' : 'rgba(143,181,224,0.07)';
                ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b2.x, b2.y); ctx.stroke();
            }
        }
        // Светлинки, които обикалят по кръга: данните минават от едно звено в следващото.
        if (!still) {
            for (i = 0; i < n; i++) {
                a = hubs[i]; b2 = hubs[(i + 1) % n];
                var p = ((t * 0.12) + i / n) % 1;
                var px = a.x + (b2.x - a.x) * p, py = a.y + (b2.y - a.y) * p;
                var g = ctx.createRadialGradient(px, py, 0, px, py, 6);
                g.addColorStop(0, 'rgba(219,234,254,0.9)'); g.addColorStop(1, 'rgba(219,234,254,0)');
                ctx.fillStyle = g; ctx.beginPath(); ctx.arc(px, py, 6, 0, Math.PI * 2); ctx.fill();
            }
        }
        // Прашинките: бледи точки, свързани с най-близкия възел.
        for (i = 0; i < dust.length; i++) {
            var d = dust[i];
            if (!still) {
                d.x += d.vx; d.y += d.vy;
                if (d.x < 0 || d.x > w) d.vx *= -1;
                if (d.y < 0 || d.y > h) d.vy *= -1;
            }
            var best = null, bd = 170;
            for (j = 0; j < n; j++) {
                var dist = Math.hypot(hubs[j].x - d.x, hubs[j].y - d.y);
                if (dist < bd) { bd = dist; best = hubs[j]; }
            }
            if (best) {
                ctx.strokeStyle = 'rgba(143,181,224,' + (0.12 * (1 - bd / 170)).toFixed(3) + ')';
                ctx.beginPath(); ctx.moveTo(d.x, d.y); ctx.lineTo(best.x, best.y); ctx.stroke();
            }
            ctx.fillStyle = 'rgba(188,211,238,0.35)';
            ctx.beginPath(); ctx.arc(d.x, d.y, 1.3, 0, Math.PI * 2); ctx.fill();
        }
        // Възлите: ореол, който бавно пулсира, ядро и надпис.
        ctx.textBaseline = 'middle';
        for (i = 0; i < n; i++) {
            a = hubs[i];
            var pulse = still ? 0.5 : (Math.sin(t * 1.4 + a.phase) + 1) / 2;
            var halo = (a.small ? 14 : 22) + pulse * (a.small ? 6 : 10);
            var hg = ctx.createRadialGradient(a.x, a.y, 0, a.x, a.y, halo);
            hg.addColorStop(0, 'rgba(93,145,205,' + (0.45 + pulse * 0.25).toFixed(3) + ')');
            hg.addColorStop(1, 'rgba(93,145,205,0)');
            ctx.fillStyle = hg; ctx.beginPath(); ctx.arc(a.x, a.y, halo, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#dbeafe'; ctx.beginPath(); ctx.arc(a.x, a.y, a.small ? 3 : 4, 0, Math.PI * 2); ctx.fill();
            ctx.font = '700 ' + (a.small ? 11 : 13) + 'px system-ui, -apple-system, "Segoe UI", sans-serif';
            var left = a.side === 'l';
            ctx.textAlign = left ? 'right' : 'left';
            ctx.shadowColor = 'rgba(7,13,24,0.9)'; ctx.shadowBlur = 6;
            ctx.fillStyle = 'rgba(232,241,253,' + (0.8 + pulse * 0.2).toFixed(3) + ')';
            ctx.fillText(a.label, a.x + (left ? -12 : 12), a.y);
            ctx.shadowBlur = 0;
        }
    }

    function loop(now) {
        if (!running) return;
        draw(now);
        raf = requestAnimationFrame(loop);
    }
    function update() {
        var go = !still && visible && !document.hidden;
        if (go && !running) { running = true; raf = requestAnimationFrame(loop); }
        if (!go && running) { running = false; cancelAnimationFrame(raf); }
    }

    function start() {
        layout(); draw(performance.now());
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (e) { visible = e[0].isIntersecting; update(); }).observe(section);
        }
        document.addEventListener('visibilitychange', update);
        var timer;
        window.addEventListener('resize', function () { clearTimeout(timer); timer = setTimeout(function () { layout(); draw(performance.now()); }, 150); });
        update();
    }
    // След зареждането: първото рисуване не забавя LCP.
    if (document.readyState === 'complete') { setTimeout(start, 300); } else { window.addEventListener('load', function () { setTimeout(start, 300); }); }
})();
</script>
@endif
