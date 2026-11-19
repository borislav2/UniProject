// Design G home, 3D touches: laptops in #proekti that follow the scroll and the mouse, and the globe in #tsel (packages are CSS only).
// Own entry, loaded as a module (deferred) with the home page; it never touches the hero.
const mq = (q) => window.matchMedia(q);
const reduce = mq('(prefers-reduced-motion: reduce)');
const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
const listen = (m, fn) => (m.addEventListener ? m.addEventListener('change', fn) : m.addListener && m.addListener(fn));

// Laptops: the static pose is CSS (--rx, --ry). Here, on a wide screen with a mouse, the yaw eases toward a smaller angle as a card
// reaches the middle of the screen, and the card leans a little after the pointer. Only custom properties of the frame change.
function laptops() {
    const grid = document.querySelector('[data-g-laptops]');
    if (!grid) return;
    const wide = mq('(min-width: 1100px) and (hover: hover) and (pointer: fine)');
    const yaws = [13, 0, -13];
    const cards = [...grid.querySelectorAll('.g-card')].map((el, i) => ({ el, f: el.querySelector('.g-frame'), yaw: yaws[i % 3], seen: false, busy: false, rx: 8, ry: yaws[i % 3], hx: 0, hy: 0 }));
    let on = false, io = null, raf = 0, hover = null, px = 0, py = 0;

    const run = () => { if (!raf && on) raf = requestAnimationFrame(tick); };
    function tick() {
        raf = 0;
        const live = cards.filter((c) => c.seen || c.busy);
        if (!live.length) return; // nothing near the screen: no layout read
        const vh = window.innerHeight, rects = [];
        for (const c of live) rects.push(c.el.getBoundingClientRect());
        let busy = false;
        live.forEach((c, i) => {
            const r = rects[i];
            const t = clamp(Math.abs(r.top + r.height / 2 - vh / 2) / (vh / 2), 0, 1);
            const k = 0.35 + 0.65 * t;
            let hx = 0, hy = 0;
            if (c === hover) {
                hy = clamp((px - r.left - r.width / 2) / (r.width / 2), -1, 1) * 6;
                hx = clamp(-(py - r.top - r.height / 2) / (r.height / 2), -1, 1) * 4;
            }
            c.hx += (hx - c.hx) * 0.15;
            c.hy += (hy - c.hy) * 0.15;
            const ty = c.yaw * k + c.hy, tx = 8 * (0.6 + 0.4 * t) + c.hx;
            const dy = ty - c.ry, dx = tx - c.rx;
            c.busy = Math.abs(dy) > 0.02 || Math.abs(dx) > 0.02;
            if (c.busy) {
                c.ry += dy * 0.14;
                c.rx += dx * 0.14;
                c.f.style.setProperty('--ry', c.ry.toFixed(2) + 'deg');
                c.f.style.setProperty('--rx', c.rx.toFixed(2) + 'deg');
                busy = true;
            }
        });
        if (busy) run();
    }
    const move = (e) => { px = e.clientX; py = e.clientY; hover = cards.find((c) => c.el.contains(e.target)) || null; run(); };
    const leave = () => { hover = null; run(); };
    const enable = () => {
        if (on) return;
        on = true;
        window.addEventListener('scroll', run, { passive: true });
        window.addEventListener('resize', run, { passive: true });
        grid.addEventListener('pointermove', move, { passive: true });
        grid.addEventListener('pointerleave', leave, { passive: true });
        if ('IntersectionObserver' in window) {
            io = new IntersectionObserver((es) => { es.forEach((e) => { cards.find((c) => c.el === e.target).seen = e.isIntersecting; }); run(); }, { rootMargin: '120px 0px' });
            cards.forEach((c) => io.observe(c.el));
        } else cards.forEach((c) => (c.seen = true));
        run();
    };
    const disable = () => {
        if (!on) return;
        on = false;
        window.removeEventListener('scroll', run);
        window.removeEventListener('resize', run);
        grid.removeEventListener('pointermove', move);
        grid.removeEventListener('pointerleave', leave);
        if (io) io.disconnect();
        cards.forEach((c) => { c.f.style.removeProperty('--rx'); c.f.style.removeProperty('--ry'); c.rx = 8; c.ry = c.yaw; });
    };
    const sync = () => (wide.matches && !reduce.matches ? enable() : disable());
    sync();
    listen(wide, sync);
    listen(reduce, sync);
}

// Globe: fibonacci points joined to their neighbours, depth shaded, a few signal points; a 2D canvas, about 30 fps, only while visible.
// Nothing is computed or measured while the page starts: the points, the colours and the canvas size are set up in the first observer callback
// (it runs after the browser's own layout) once the band is within a screen of the viewport, so the still frame is ready before it is seen.
function globe() {
    const cv = document.querySelector('[data-g-globe]');
    const ctx = cv && cv.getContext && cv.getContext('2d');
    if (!ctx) return;
    const band = cv.closest('section') || cv.parentNode;
    const small = mq('(max-width: 759px)'); // the same test as a width under 760px, without reading the layout
    const GA = Math.PI * (3 - Math.sqrt(5));
    const pts = [], edges = [];
    let ink = '#ECEEF0', hot = '#FF5A33';
    let S = 0, dpr = 1, yaw = 0.6, mx = 0, my = 0, tx = 0, ty = 0, last = 0, raf = 0, seen = false;
    const build = () => {
        const css = getComputedStyle(band);
        ink = css.getPropertyValue('--g-band-fg').trim() || ink;
        hot = css.getPropertyValue('--g-accent-on-band').trim() || hot;
        const N = small.matches ? 260 : 380;
        for (let i = 0; i < N; i++) {
            const y = 1 - (2 * i) / (N - 1), r = Math.sqrt(1 - y * y);
            pts.push([Math.cos(GA * i) * r, y, Math.sin(GA * i) * r]);
        }
        for (let i = 0; i < N; i++) for (let j = i + 1; j < N; j++) {
            const a = pts[i], b = pts[j];
            if ((a[0] - b[0]) ** 2 + (a[1] - b[1]) ** 2 + (a[2] - b[2]) ** 2 < 21 / N) edges.push([i, j]);
        }
    };
    const size = () => {
        dpr = Math.min(window.devicePixelRatio || 1, 1.5);
        S = cv.clientWidth;
        cv.width = cv.height = Math.round(S * dpr);
        draw();
    };
    function draw() {
        if (!S) return;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.clearRect(0, 0, S, S);
        const ay = yaw + mx * 0.5, ax = -0.35 + my * 0.3, R = S * 0.36, c = S / 2;
        const cy = Math.cos(ay), sy = Math.sin(ay), cx = Math.cos(ax), sx = Math.sin(ax);
        const P = pts.map((p) => {
            const x = p[0] * cy + p[2] * sy, z = -p[0] * sy + p[2] * cy;
            const y = p[1] * cx - z * sx, z2 = p[1] * sx + z * cx, k = 2.6 / (2.6 - z2);
            return [c + x * R * k, c + y * R * k, z2, k];
        });
        ctx.lineWidth = 0.7;
        ctx.strokeStyle = ink;
        for (let b = 0; b < 4; b++) {
            ctx.globalAlpha = 0.05 + 0.065 * b;
            ctx.beginPath();
            for (const [i, j] of edges) {
                const z = (P[i][2] + P[j][2]) / 2;
                if (clamp(Math.floor(((z + 1) / 2) * 4), 0, 3) !== b) continue;
                ctx.moveTo(P[i][0], P[i][1]);
                ctx.lineTo(P[j][0], P[j][1]);
            }
            ctx.stroke();
        }
        for (let b = 0; b < 4; b++) {
            ctx.globalAlpha = 0.18 + 0.2 * b;
            ctx.fillStyle = ink;
            ctx.beginPath();
            P.forEach((p, i) => {
                if (i % 37 === 0 || clamp(Math.floor(((p[2] + 1) / 2) * 4), 0, 3) !== b) return;
                ctx.moveTo(p[0] + 1.3 * p[3], p[1]);
                ctx.arc(p[0], p[1], 1.3 * p[3], 0, 7);
            });
            ctx.fill();
        }
        ctx.fillStyle = hot;
        P.forEach((p, i) => {
            if (i % 37 || p[2] < -0.2) return;
            ctx.globalAlpha = 0.55 + 0.45 * ((p[2] + 1) / 2);
            ctx.beginPath();
            ctx.arc(p[0], p[1], 3.2 * p[3], 0, 7);
            ctx.fill();
        });
        ctx.globalAlpha = 1;
    }
    function frame(now) {
        raf = 0;
        if (!seen || document.hidden) return;
        raf = requestAnimationFrame(frame);
        if (now - last < 33) return;
        const dt = Math.min((now - last) / 1000, 0.1);
        last = now;
        yaw += dt * 0.12;
        mx += (tx - mx) * 0.06;
        my += (ty - my) * 0.06;
        draw();
    }
    const go = () => { if (!raf && seen && !document.hidden && !reduce.matches) { last = performance.now(); raf = requestAnimationFrame(frame); } };
    const start = () => {
        build();
        size();
        window.addEventListener('resize', size, { passive: true });
        if (reduce.matches || !('IntersectionObserver' in window)) return; // one still frame
        new IntersectionObserver((es) => { seen = es[0].isIntersecting; go(); }).observe(band);
        document.addEventListener('visibilitychange', go);
        band.addEventListener('pointermove', (e) => {
            const r = band.getBoundingClientRect();
            tx = ((e.clientX - r.left) / r.width - 0.5) * 2;
            ty = ((e.clientY - r.top) / r.height - 0.5) * 2;
        }, { passive: true });
    };
    if ('IntersectionObserver' in window) {
        const near = new IntersectionObserver((es) => {
            if (!es.some((e) => e.isIntersecting)) return;
            near.disconnect();
            start();
        }, { rootMargin: '300px 0px' });
        near.observe(band);
    } else {
        requestAnimationFrame(() => requestAnimationFrame(start));
    }
}

laptops();
globe();
