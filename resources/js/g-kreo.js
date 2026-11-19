// Design G home, „Как работим“: the scroll story with Kreo. This file is tiny and the only part that is loaded with the page. The 3D scene
// (three.js, ./kreo/stage.js) is imported dynamically only when the section is within about one screen of the viewport, on a wide screen,
// without reduced motion and with WebGL2. Everywhere else the four steps stay a plain list with a still image each (see the markup).
const section = document.querySelector('[data-g-kreo]');

if (section) {
    const story = section.querySelector('[data-g-kreo-story]');
    const stick = section.querySelector('[data-g-kreo-stick]');
    const stageEl = section.querySelector('[data-g-kreo-stage]');
    const steps = [...section.querySelectorAll('[data-g-step]')];
    const wide = window.matchMedia('(min-width: 1024px)');
    const calm = window.matchMedia('(prefers-reduced-motion: reduce)');

    // The WebGL probe creates a context, so it runs only when the scene could actually start (wide screen, motion allowed), never on a phone.
    let glChecked = null;
    const hasGl = () => {
        if (glChecked !== null) return glChecked;
        try {
            const probe = document.createElement('canvas');
            const gl = probe.getContext('webgl2');
            if (gl) gl.getExtension('WEBGL_lose_context')?.loseContext();
            glChecked = !!gl;
        } catch (e) {
            glChecked = false;
        }

        return glChecked;
    };

    let near = false;
    let active = false;
    let stage = null;
    let loading = false;
    let broken = false;
    let ticking = false;
    let current = -1;

    const clamp = (v) => Math.min(1, Math.max(0, v));
    const smooth = (k) => { const c = clamp(k); return c * c * (3 - 2 * c); };

    // Scroll position inside the story (0 to 1) and the scene value (0 to 3) with a rest at every scene.
    function progress() {
        const rect = story.getBoundingClientRect();
        const span = Math.max(1, rect.height - stick.getBoundingClientRect().height);

        return clamp(-rect.top / span);
    }

    function sceneValue() {
        const raw = progress() * 3;
        const i = Math.min(2, Math.floor(raw));

        return raw >= 3 ? 3 : i + smooth((raw - i - 0.2) / 0.6);
    }

    function sync() {
        ticking = false;
        if (!active) return;
        const p = progress();
        section.style.setProperty('--kreo-p', p.toFixed(4));
        const index = Math.min(3, Math.round(sceneValue()));
        if (index !== current) {
            current = index;
            steps.forEach((step, n) => {
                step.classList.toggle('is-active', n === index);
                step.classList.toggle('is-done', n < index);
            });
        }
    }

    const onScroll = () => { if (!ticking) { ticking = true; requestAnimationFrame(sync); } };

    function stop() {
        if (stage) { stage.dispose(); stage = null; }
    }

    async function load() {
        if (loading || stage || broken) return;
        loading = true;
        try {
            const { mount } = await import('./kreo/stage.js');
            if (!active) { loading = false; return; }
            stage = await mount({ section, stageEl, getT: sceneValue, onLost: fallBack });
        } catch (e) {
            fallBack();
        }
        loading = false;
    }

    // Anything goes wrong with WebGL: back to the list with the stills, for good.
    function fallBack() {
        broken = true;
        stop();
        evaluate();
    }

    function evaluate() {
        const on = wide.matches && !calm.matches && !broken && hasGl();
        section.classList.toggle('g-kreo-3d', on);
        if (on !== active) {
            active = on;
            current = -1;
            window[on ? 'addEventListener' : 'removeEventListener']('scroll', onScroll, { passive: true });
            window[on ? 'addEventListener' : 'removeEventListener']('resize', onScroll, { passive: true });
            if (on) sync(); else stop();
        }
        if (on && near) load();
    }

    wide.addEventListener('change', evaluate);
    calm.addEventListener('change', evaluate);
    window.addEventListener('pagehide', stop);

    if ('IntersectionObserver' in window) {
        new IntersectionObserver((entries) => {
            near = entries.some((entry) => entry.isIntersecting);
            if (near) evaluate();
        }, { rootMargin: '100% 0px' }).observe(section);
    }

    evaluate();
}
