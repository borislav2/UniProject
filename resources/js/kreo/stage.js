// The live side of „Как работим“: puts the three.js story on a canvas in the stage and drives it from the scroll position.
// Imported dynamically by g-kreo.js (it pulls in three.js). Renders only while the stage is on screen and the tab is visible.
import { createStory } from './story.js';

export async function mount({ section, stageEl, getT, onLost }) {
    const root = document.documentElement;
    // The board shows the short step names (config short, e.g. „Стратегия“) so they are readable from afar; the full title is the fallback.
    const titles = [...section.querySelectorAll('[data-g-step]')].map((li) => (li.dataset.short || li.querySelector('h3')?.textContent || '').trim());

    // The whiteboard is painted on a canvas, so the fonts of the site must be there first.
    try {
        await Promise.all([
            document.fonts.load('300 46px Geologica', titles.join(' ')),
            document.fonts.load('500 28px "JetBrains Mono"', '0123456789'),
        ]);
    } catch (e) { /* the fallback font is fine */ }

    const canvas = document.createElement('canvas');
    canvas.className = 'g-kreo-canvas';
    stageEl.appendChild(canvas);
    const size = () => { const r = stageEl.getBoundingClientRect(); return [Math.max(1, Math.round(r.width)), Math.max(1, Math.round(r.height))]; };
    const [w, h] = size();

    let story;
    try {
        story = createStory({
            canvas, width: w, height: h, pixelRatio: Math.min(window.devicePixelRatio || 1, 1.5), planTitles: titles,
            siteImage: section.dataset.site || '', dark: root.classList.contains('dark'),
        });
        await story.ready;
    } catch (e) {
        canvas.remove();
        throw e;
    }

    let raf = 0;
    let visible = false;
    let disposed = false;
    let t = getT();
    let last = performance.now();
    const pointer = { x: 0, y: 0 };

    function frame(now) {
        raf = requestAnimationFrame(frame);
        const dt = Math.min(0.1, (now - last) / 1000);
        last = now;
        // The scene follows the scroll with a little lag, so a fast flick still glides.
        t += (getT() - t) * (1 - Math.exp(-dt * 7));
        story.update(t, now / 1000, pointer.x, pointer.y);
        story.render();
    }

    const run = () => { if (!raf && visible && !document.hidden && !disposed) { last = performance.now(); raf = requestAnimationFrame(frame); } };
    const halt = () => { cancelAnimationFrame(raf); raf = 0; };

    const io = new IntersectionObserver((entries) => {
        visible = entries.some((e) => e.isIntersecting);
        if (visible) run(); else halt();
    });
    io.observe(stageEl);

    const onVisibility = () => { if (document.hidden) halt(); else run(); };
    document.addEventListener('visibilitychange', onVisibility);

    const ro = new ResizeObserver(() => { const [nw, nh] = size(); story.resize(nw, nh); if (!raf) { story.update(t, 0, 0, 0, false); story.render(); } });
    ro.observe(stageEl);

    const onPointer = (e) => {
        const r = stageEl.getBoundingClientRect();
        pointer.x = Math.max(-1, Math.min(1, (e.clientX - (r.left + r.width / 2)) / (window.innerWidth / 2)));
        pointer.y = Math.max(-1, Math.min(1, (e.clientY - (r.top + r.height / 2)) / (window.innerHeight / 2)));
    };
    window.addEventListener('pointermove', onPointer, { passive: true });

    const theme = new MutationObserver(() => story.setTheme(root.classList.contains('dark')));
    theme.observe(root, { attributes: true, attributeFilter: ['class'] });

    const onLostContext = (e) => { e.preventDefault(); onLost && onLost(); };
    canvas.addEventListener('webglcontextlost', onLostContext);

    // First frame, then fade the canvas in over the poster.
    story.update(t, 0, 0, 0, false);
    story.render();
    requestAnimationFrame(() => stageEl.classList.add('is-live'));

    function dispose() {
        if (disposed) return;
        disposed = true;
        halt();
        io.disconnect();
        ro.disconnect();
        theme.disconnect();
        document.removeEventListener('visibilitychange', onVisibility);
        window.removeEventListener('pointermove', onPointer);
        canvas.removeEventListener('webglcontextlost', onLostContext);
        stageEl.classList.remove('is-live');
        story.dispose();
        canvas.remove();
    }

    return { dispose };
}
