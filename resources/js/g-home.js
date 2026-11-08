// Home page of design G: the topographic hero map (the monthly / yearly switch is in g-bar.js, shared with /paketi).

// Topographic contours: a noise field drifts, bends around the cursor, and its iso-lines are drawn with marching squares.
// One line is in the signal colour; it is clipped away from the headline, the text and the buttons.
function initTopo() {
    const canvas = document.getElementById('g-topo');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const root = document.documentElement;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const hero = canvas.parentNode;
    const LEVELS = [];
    for (let i = -8; i <= 8; i++) LEVELS.push(i * 0.17);
    const ACCENT = 5;
    // Marching squares: for each case, the pairs of cell edges (0 top, 1 right, 2 bottom, 3 left) a segment joins.
    const SEG = { 1: [[3, 2]], 2: [[2, 1]], 3: [[3, 1]], 4: [[0, 1]], 5: [[3, 0], [1, 2]], 6: [[0, 2]], 7: [[3, 0]], 8: [[3, 0]], 9: [[0, 2]], 10: [[0, 1], [2, 3]], 11: [[0, 1]], 12: [[3, 1]], 13: [[2, 1]], 14: [[3, 2]] };

    let W = 0, H = 0, dpr = 1, CELL = 14, nx = 0, ny = 0, field = null;
    let colors = null, clear = [];
    let visible = true, t = 3.2, px = -1e4, py = -1e4, tx = -1e4, ty = -1e4, last = 0, frame = 0;

    function readColors() {
        const s = getComputedStyle(root);
        colors = { ink: s.getPropertyValue('--g-canvas-ink').trim() || '12,14,18', accent: s.getPropertyValue('--g-canvas-accent').trim() || '232,67,27' };
    }

    // The boxes the signal line must not cross: the kicker, headline, lead and buttons, with some air around.
    function readClear() {
        const box = canvas.getBoundingClientRect();
        clear = [...hero.querySelectorAll('[data-g-clear]')].map((el) => {
            const r = el.getBoundingClientRect();
            const pad = 22;
            // data-g-clear="full" (the row of six parts) keeps the signal line out of the whole band, edge to edge.
            const full = el.dataset.gClear === 'full';
            return [full ? 0 : r.left - box.left - pad, r.top - box.top - pad, full ? W : r.right - box.left + pad, r.bottom - box.top + pad];
        });
    }

    function resize() {
        const r = canvas.getBoundingClientRect();
        W = r.width; H = r.height;
        dpr = Math.min(window.devicePixelRatio || 1, 1.5);
        canvas.width = Math.round(W * dpr);
        canvas.height = Math.round(H * dpr);
        CELL = W < 600 ? 12 : 14;
        nx = Math.ceil(W / CELL) + 1;
        ny = Math.ceil(H / CELL) + 1;
        field = new Float32Array(nx * ny);
        readClear();
        draw();
    }

    function fill() {
        const k = W < 600 ? 0.075 : 0.05;
        const R2 = 2 * Math.pow(W < 600 ? 90 : 150, 2);
        for (let j = 0; j < ny; j++) {
            for (let i = 0; i < nx; i++) {
                const u = i * k, v = j * k;
                let f = 0.55 * Math.sin(u * 1.3 + t * 0.35 + 1.7 * Math.sin(v * 0.7 - t * 0.2))
                    + 0.45 * Math.cos(v * 1.1 - t * 0.28 + 0.8 * Math.sin(u * 0.9 + t * 0.15))
                    + 0.5 * Math.sin((u + v) * 0.6 + t * 0.1);
                const dx = i * CELL - px, dy = j * CELL - py;
                f += 1.25 * Math.exp(-(dx * dx + dy * dy) / R2);
                field[j * nx + i] = f;
            }
        }
    }

    function draw() {
        if (!field) return;
        if (!colors) readColors();
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.clearRect(0, 0, W, H);
        ctx.lineJoin = 'round';
        fill();

        for (let li = 0; li < LEVELS.length; li++) {
            const L = LEVELS[li];
            const path = new Path2D();
            for (let j = 0; j < ny - 1; j++) {
                for (let i = 0; i < nx - 1; i++) {
                    const a = field[j * nx + i], b = field[j * nx + i + 1], c = field[(j + 1) * nx + i + 1], d = field[(j + 1) * nx + i];
                    const m = (a > L ? 8 : 0) | (b > L ? 4 : 0) | (c > L ? 2 : 0) | (d > L ? 1 : 0);
                    if (m === 0 || m === 15) continue;
                    const x0 = i * CELL, y0 = j * CELL;
                    for (const [e0, e1] of SEG[m]) {
                        const p = [];
                        for (const e of [e0, e1]) {
                            let x, y, s;
                            if (e === 0) { s = (L - a) / (b - a); x = x0 + s * CELL; y = y0; }
                            else if (e === 1) { s = (L - b) / (c - b); x = x0 + CELL; y = y0 + s * CELL; }
                            else if (e === 2) { s = (L - d) / (c - d); x = x0 + s * CELL; y = y0 + CELL; }
                            else { s = (L - a) / (d - a); x = x0; y = y0 + s * CELL; }
                            p.push(x, y);
                        }
                        path.moveTo(p[0], p[1]);
                        path.lineTo(p[2], p[3]);
                    }
                }
            }

            if (li === ACCENT) {
                // Clip out the text boxes (even-odd) so the signal line passes below and beside them, never through them.
                ctx.save();
                const keep = new Path2D();
                keep.rect(0, 0, W, H);
                clear.forEach((r) => keep.rect(r[0], r[1], r[2] - r[0], r[3] - r[1]));
                ctx.clip(keep, 'evenodd');
                ctx.strokeStyle = `rgba(${colors.accent},.95)`;
                ctx.lineWidth = 2;
                ctx.stroke(path);
                ctx.restore();
            } else {
                ctx.strokeStyle = `rgba(${colors.ink},${li % 4 === 0 ? 0.62 : 0.32})`;
                ctx.lineWidth = li % 4 === 0 ? 1.2 : 0.9;
                ctx.stroke(path);
            }
        }
    }

    function loop(ts) {
        if (visible && ts - last > 33) {
            last = ts;
            t += 0.012;
            px += (tx - px) * 0.12;
            py += (ty - py) * 0.12;
            if (++frame % 40 === 0) { readColors(); readClear(); }
            draw();
        }
        requestAnimationFrame(loop);
    }

    resize();
    let timer;
    window.addEventListener('resize', () => { clearTimeout(timer); timer = setTimeout(resize, 120); });
    // Web fonts change the size of the headline: measure the text boxes again once they are in.
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => { readClear(); draw(); });
    // The theme switch changes the colours (one still frame under reduced motion has no loop to pick it up).
    new MutationObserver(() => { readColors(); draw(); }).observe(root, { attributes: true, attributeFilter: ['class'] });

    if (reduce) return;

    hero.addEventListener('pointermove', (e) => {
        const r = canvas.getBoundingClientRect();
        tx = e.clientX - r.left;
        ty = e.clientY - r.top;
        if (px < -1e3) { px = tx; py = ty; }
    });
    hero.addEventListener('pointerleave', () => { tx = ty = px = py = -1e4; });
    // Pause off-screen and in hidden tabs.
    if ('IntersectionObserver' in window) {
        let onScreen = true;
        new IntersectionObserver((entries) => { onScreen = entries[0].isIntersecting; visible = onScreen && !document.hidden; }).observe(canvas);
        document.addEventListener('visibilitychange', () => { visible = onScreen && !document.hidden; });
    } else {
        document.addEventListener('visibilitychange', () => { visible = !document.hidden; });
    }
    requestAnimationFrame(loop);
}

// Start after the page has loaded so the map never competes with the first paint.
if (document.readyState === 'complete') initTopo();
else window.addEventListener('load', initTopo);
