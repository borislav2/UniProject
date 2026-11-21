// Design G home, the scroll opening: the hero gives way to four steps that play beside one stage (search, three results, a phone, the
// inquiry), then a compact closing releases the page. Markup and static fallback: resources/views/g/home.blade.php, styles: g-scroll.css.
// Plain JS. The headline is server-rendered; this file only enhances. No pinning with prefers-reduced-motion (the CSS shows a static layout).
// Per frame: one getBoundingClientRect (read first), then transforms and opacity only. No work while the section is off screen.
const mq = (q) => window.matchMedia(q);
const clamp = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
const lerp = (a, b, t) => a + (b - a) * t;
const lin = (t) => t;
const eio = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
const eo = (t) => 1 - Math.pow(1 - t, 3);
const seg = (p, a, b, f = eio) => f(clamp((p - a) / (b - a)));
const reduce = mq('(prefers-reduced-motion: reduce)');

// Style writes only when a value changed.
function put(el, prop, v) {
    const c = el._c || (el._c = {});
    if (c[prop] === v) return;
    c[prop] = v;
    el.style[prop] = v;
}
const T = (el, x, y, s, o) => {
    put(el, 'transform', `translate(${x.toFixed(2)}px,${y.toFixed(2)}px) scale(${s.toFixed(3)})`);
    put(el, 'opacity', o.toFixed(3));
    put(el, 'visibility', o <= 0.001 ? 'hidden' : 'visible');
};

// ---- contour lines, drawn once (and again on a width change or a theme switch) ----
function field(x, y) {
    const H = [[0.2, 0.3, 0.9, 0.3, 0.22], [0.75, 0.25, 0.7, 0.2, 0.3], [0.55, 0.75, 1, 0.28, 0.2], [0.1, 0.9, 0.6, 0.2, 0.25], [0.92, 0.8, 0.8, 0.22, 0.18]];
    let v = 0.1 * Math.sin(3.1 * x + 1.7 * Math.sin(2.2 * y)) + 0.08 * Math.sin(5.3 * y + 2.1 * x);
    for (const [a, b, A, sx, sy] of H) v += A * Math.exp(-(((x - a) / sx) ** 2 + ((y - b) / sy) ** 2));
    return v;
}
function drawTopo(cv) {
    const W = cv.clientWidth, Hh = cv.clientHeight;
    if (!W || !Hh) return;
    const dpr = Math.min(window.devicePixelRatio || 1, W < 768 ? 1.5 : 2);
    cv.width = Math.round(W * dpr);
    cv.height = Math.round(Hh * dpr);
    const c = cv.getContext('2d');
    if (!c) return;
    c.scale(dpr, dpr);
    const dark = document.documentElement.classList.contains('dark');
    const ink = dark ? '236,238,240' : '12,14,18';
    const cell = W < 768 ? 18 : 14, nx = Math.ceil(W / cell) + 1, ny = Math.ceil(Hh / cell) + 1, g = [];
    for (let j = 0; j < ny; j++) {
        g[j] = [];
        for (let i = 0; i < nx; i++) g[j][i] = field((i * cell) / W, (j * cell) / Hh);
    }
    for (let li = 0; li < 18; li++) {
        const L = -0.05 + li * 0.075;
        c.beginPath();
        for (let j = 0; j < ny - 1; j++) {
            for (let i = 0; i < nx - 1; i++) {
                const a = g[j][i], b = g[j][i + 1], d = g[j + 1][i], e = g[j + 1][i + 1], P = [], x = i * cell, y = j * cell;
                const t = (u, v) => (L - u) / (v - u);
                if ((a < L) !== (b < L)) P.push([x + t(a, b) * cell, y]);
                if ((b < L) !== (e < L)) P.push([x + cell, y + t(b, e) * cell]);
                if ((d < L) !== (e < L)) P.push([x + t(d, e) * cell, y + cell]);
                if ((a < L) !== (d < L)) P.push([x, y + t(a, d) * cell]);
                for (let k = 0; k + 1 < P.length; k += 2) {
                    c.moveTo(P[k][0], P[k][1]);
                    c.lineTo(P[k + 1][0], P[k + 1][1]);
                }
            }
        }
        const major = li % 4 === 0;
        c.strokeStyle = `rgba(${ink},${major ? (dark ? 0.14 : 0.09) : (dark ? 0.08 : 0.05)})`;
        c.lineWidth = major ? 1 : 0.8;
        c.stroke();
    }
}
function topo(root) {
    const cv = root.querySelector('.g-sc-topo');
    if (!cv || !cv.getContext) return;
    let w = 0, h = 0, timer = 0;
    const run = () => {
        w = cv.clientWidth;
        h = cv.clientHeight;
        drawTopo(cv);
    };
    const later = () => (window.requestIdleCallback ? window.requestIdleCallback(run, { timeout: 1500 }) : setTimeout(run, 200));
    if (document.readyState === 'complete') later();
    else window.addEventListener('load', later, { once: true });
    window.addEventListener('resize', () => {
        clearTimeout(timer);
        timer = setTimeout(() => { if (cv.clientWidth !== w || Math.abs(cv.clientHeight - h) > 120) run(); }, 200);
    });
    // The theme switch toggles .dark on <html>: draw the lines in the other colour.
    new MutationObserver(() => { clearTimeout(timer); timer = setTimeout(run, 50); }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}

// ---- the pinned story ----
function story(root) {
    const $ = (s) => root.querySelector(s);
    const stage = $('.g-sc-stage'), hero = $('.g-sc-hero'), end = $('.g-sc-end'), hint = $('.g-sc-hint'), txt = $('.g-sc-txt'), prog = $('.g-sc-prog');
    const viz = $('.g-sc-viz'), cam = $('.g-sc-cam'), topoEl = $('.g-sc-topo');
    if (!stage || !hero || !txt || !viz || !cam) return;
    const stp = [...root.querySelectorAll('.g-sc-stp')], bars = prog ? [...prog.children] : [], sts = [...root.querySelectorAll('.g-sc-ph .st')];
    const el = { q: $('.g-sc-q'), cg: $('.g-sc-cg'), cm: $('.g-sc-cm'), cc: $('.g-sc-cc'), ph: $('.g-sc-ph'), pc: $('.g-sc-ph .pc'), iq: $('.g-sc-iq'), ring: $('.g-sc-iq .ring'), tk: $('.g-sc-iq .tk'), tkp: $('.g-sc-iq .tk path'), ln: $('.g-sc-ln'), d1: $('.g-sc-dot'), d2: $('.g-sc-dot2'), typed: $('.g-sc-typed') };
    if (stp.length !== 4 || Object.values(el).some((v) => !v)) return; // the design is made for four steps; anything else stays the static list
    const WORD = el.typed.textContent;
    const B = [0, 0.22, 0.44, 0.64], END = 0.82;
    let on = false, raf = 0, cur = 0, target = 0, range = 1, VW = 0, VH = 0, ws = 1, lastP = -1, narrow = false;

    function measure() { // reads only
        range = Math.max(1, root.offsetHeight - stage.offsetHeight);
        VW = viz.clientWidth;
        VH = viz.clientHeight;
        ws = Math.min(VW / 610, VH / 640, 1.35);
        narrow = VW < 500; // phone: the three result cards settle earlier and sit further apart, so nothing overlaps while step 02 is read
    }

    function textRender(q, P) {
        stp.forEach((e, i) => {
            const inn = i === 0 ? seg(P, 0.065, 0.1, lin) : seg(q, B[i] - 0.004, B[i] + 0.02, lin);
            const out = i === 3 ? seg(q, END, END + 0.04, lin) : seg(q, B[i + 1] - 0.02, B[i + 1] + 0.004, lin);
            put(e, 'opacity', (inn * (1 - out)).toFixed(3));
            put(e, 'transform', `translateY(${((1 - inn) * 16 - out * 16).toFixed(2)}px)`);
        });
        bars.forEach((e, i) => {
            const f = clamp((q - B[i]) / ((i === 3 ? END : B[i + 1]) - B[i])).toFixed(3);
            if (e._f !== f) { e._f = f; e.style.setProperty('--f', f); }
        });
        const fi = seg(P, 0.065, 0.1, lin), tOut = 1 - seg(q, END, END + 0.04, lin);
        put(txt, 'opacity', (fi * tOut).toFixed(3));
        put(txt, 'visibility', fi * tOut <= 0.001 ? 'hidden' : 'visible');
        if (hint) put(hint, 'opacity', (1 - seg(P, 0, 0.035, lin)).toFixed(3));
        const vo = fi * (1 - seg(q, END, END + 0.05, lin));
        put(viz, 'opacity', vo.toFixed(3));
        put(viz, 'visibility', vo <= 0 ? 'hidden' : 'visible');
        const n = Math.round(seg(q, 0.02, 0.14, lin) * WORD.length);
        if (el.typed._n !== n) { el.typed._n = n; el.typed.textContent = WORD.slice(0, n); }
        put(topoEl, 'transform', `translateY(${(-P * 50).toFixed(1)}px) scale(1.12)`);
    }

    function heroEnd(P) {
        const t = seg(P, 0, 0.075, eio);
        put(hero, 'opacity', (1 - t).toFixed(3));
        put(hero, 'transform', `translate(${(-t * 64).toFixed(1)}px,${(-t * 8).toFixed(1)}px)`);
        put(hero, 'visibility', t >= 1 ? 'hidden' : 'visible');
        const u = seg(P, 0.885, 0.935, eo);
        put(end, 'opacity', u.toFixed(3));
        put(end, 'transform', `translateY(${((1 - u) * 16).toFixed(1)}px)`);
        put(end, 'visibility', u > 0 ? 'visible' : 'hidden');
    }

    function inquiry(p, x, y, land) {
        const a = seg(p, land - 0.01, land + 0.03, eo), pulse = seg(p, land + 0.03, land + 0.09, lin), tk = seg(p, land + 0.08, land + 0.12, eo);
        T(el.iq, x, y + (1 - a) * 6, lerp(1.05, 1, a), a);
        put(el.ring, 'opacity', (pulse > 0 && pulse < 1 ? (1 - pulse) * 0.55 : 0).toFixed(3));
        put(el.ring, 'transform', `scale(${(1 + pulse * 0.16).toFixed(3)})`);
        put(el.tk, 'opacity', tk.toFixed(3));
        put(el.tk, 'transform', `translateY(${((1 - tk) * 8).toFixed(2)}px) scale(${lerp(0.9, 1, tk).toFixed(3)})`);
        put(el.tkp, 'strokeDashoffset', (16 * (1 - seg(p, land + 0.1, land + 0.135, lin))).toFixed(2));
    }

    function render(P) {
        const p = ((P - 0.1) / 0.76) * 0.82;
        const z = ws * lerp(1.1, 1, seg(p, 0, 0.1, eo));
        put(cam, 'transform', `translate(${VW / 2}px,${VH / 2}px) scale(${z.toFixed(4)})`);
        const t2 = seg(p, 0.18, 0.26), t4 = seg(p, 0.6, 0.68);
        const qy = lerp(-40, -228, t2), qs = lerp(1, 0.72, t2);
        T(el.q, 0, qy, qs, lerp(1, 0.5, t2) * (1 - seg(p, 0.4, 0.46, lin)));
        const top = qy + 32 * qs, tip = p < 0.2 ? lerp(top + 2, 110, seg(p, 0.08, 0.2)) : lerp(110, 250, seg(p, 0.2, 0.34));
        const sp = (p > 0.08 ? 1 : 0) * (1 - seg(p, 0.4, 0.45, lin));
        put(el.ln, 'top', top.toFixed(1) + 'px');
        put(el.ln, 'height', Math.max(0, tip - top).toFixed(1) + 'px');
        put(el.ln, 'opacity', sp.toFixed(3));
        put(el.d1, 'transform', `translate(0px,${tip.toFixed(1)}px)`);
        put(el.d1, 'opacity', sp.toFixed(3));
        const card = (e, a, b, fx, fy) => {
            const k = seg(p, a, b, eo), c = seg(p, 0.4, 0.47, eio);
            T(e, lerp(lerp(0, fx, k), 0, c), lerp(lerp(qy, fy, k), 0, c), lerp(lerp(0.3, 1, k), 0.4, c), seg(p, a, a + 0.035, lin) * (1 - seg(p, 0.4, 0.44, lin)));
        };
        if (narrow) {
            card(el.cg, 0.19, 0.24, 0, -98);
            card(el.cm, 0.2, 0.25, -101, 100);
            card(el.cc, 0.21, 0.26, 101, 100);
        } else {
            card(el.cg, 0.2, 0.27, 0, -85);
            card(el.cm, 0.22, 0.29, -103, 88);
            card(el.cc, 0.24, 0.31, 103, 88);
        }
        const pa = seg(p, 0.42, 0.5, eo);
        T(el.ph, lerp(0, -128, t4), 0, lerp(0.7, 1, pa) * lerp(1, 0.86, t4), seg(p, 0.425, 0.455, lin) * lerp(1, 0.92, t4));
        sts.forEach((e, i) => {
            const u = seg(p, 0.455 + i * 0.011, 0.5 + i * 0.011, lin);
            put(e, 'opacity', u.toFixed(3));
            put(e, 'transform', `translateY(${((1 - u) * 8).toFixed(2)}px)`);
        });
        put(el.pc, 'transform', `translateY(${(-lerp(0, 16, seg(p, 0.5, 0.62))).toFixed(2)}px)`);
        const f = seg(p, 0.62, 0.68, eio), P0 = [-128, -30], C0 = [0, -150], P2 = [132, -40];
        const bz = (i) => (1 - f) * (1 - f) * P0[i] + 2 * (1 - f) * f * C0[i] + f * f * P2[i];
        put(el.d2, 'transform', `translate(${bz(0).toFixed(1)}px,${bz(1).toFixed(1)}px)`);
        put(el.d2, 'opacity', (seg(p, 0.615, 0.625, lin) * (1 - seg(p, 0.675, 0.69, lin))).toFixed(3));
        inquiry(p, 132, -40, 0.665);
        textRender(p, P);
        heroEnd(P);
    }

    function tick() {
        raf = 0;
        if (!on) return;
        const top = root.getBoundingClientRect().top; // the only layout read of the frame
        target = clamp(-top / range);
        cur += (target - cur) * 0.14;
        if (Math.abs(target - cur) < 0.0004) cur = target;
        if (cur !== lastP) { lastP = cur; render(cur); }
        if (cur !== target) raf = requestAnimationFrame(tick);
    }
    const schedule = () => { if (on && !raf) raf = requestAnimationFrame(tick); };

    function enable() {
        if (on) return;
        on = true;
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', onResize);
        root.classList.add('is-on');
        raf = requestAnimationFrame(() => { // after the class took effect: measure, then draw the current frame
            raf = 0;
            measure();
            const top = root.getBoundingClientRect().top;
            cur = target = clamp(-top / range);
            lastP = -1;
            render(cur);
            lastP = cur;
        });
    }
    function disable() {
        if (!on) return;
        on = false;
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', onResize);
        root.classList.remove('is-on');
        if (raf) cancelAnimationFrame(raf);
        raf = 0;
    }
    let rs = 0;
    function onResize() {
        if (rs) return;
        rs = requestAnimationFrame(() => { rs = 0; measure(); lastP = -1; schedule(); });
    }

    if (!reduce.matches) enable();
    const change = () => (reduce.matches ? disable() : enable());
    reduce.addEventListener ? reduce.addEventListener('change', change) : reduce.addListener && reduce.addListener(change);
    // Fonts change the text size inside the stage: measure again once they are ready.
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => { if (on) onResize(); });
}

const root = document.querySelector('[data-g-scene]');
if (root) {
    topo(root);
    story(root);
}
