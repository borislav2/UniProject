const revealed = document.querySelectorAll('.reveal');

if ('IntersectionObserver' in window && revealed.length) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    revealed.forEach((el) => observer.observe(el));
} else {
    revealed.forEach((el) => el.classList.add('is-visible'));
}

// Motion that follows the pointer: only for a mouse/trackpad and when the visitor has not asked for less motion.
const motionOk = window.matchMedia('(prefers-reduced-motion: no-preference)').matches;
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

if (motionOk && finePointer) {
    // Cards: light + glowing edge follow the cursor (.card-hover::before/::after read --mx/--my)
    document.addEventListener('pointermove', (event) => {
        const card = event.target.closest?.('.card-hover');
        if (!card) return;
        const box = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${event.clientX - box.left}px`);
        card.style.setProperty('--my', `${event.clientY - box.top}px`);
    }, { passive: true });

    // Hero mock-up tilts slightly towards the pointer
    document.querySelectorAll('[data-tilt]').forEach((el) => {
        const area = el.closest('section') || el;
        let frame = 0;
        area.addEventListener('pointermove', (event) => {
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(() => {
                const box = el.getBoundingClientRect();
                const x = (event.clientX - box.left) / box.width - 0.5;
                const y = (event.clientY - box.top) / box.height - 0.5;
                el.style.setProperty('--ry', `${Math.max(-1, Math.min(1, x)) * 7}deg`);
                el.style.setProperty('--rx', `${Math.max(-1, Math.min(1, y)) * -6}deg`);
            });
        }, { passive: true });
        area.addEventListener('pointerleave', () => {
            el.style.setProperty('--rx', '0deg');
            el.style.setProperty('--ry', '0deg');
        });
    });
}

// Hero laptop: moving the pointer left to right over the screen reveals the marketing behind the site;
// a click or tap switches all the way. Without interaction it shows a short hint once.
document.querySelectorAll('[data-reveal]').forEach((screen) => {
    let pinned = false;
    let touched = false;
    const stage = screen.closest('[data-tilt]');
    const set = (percent, ease = false) => {
        screen.classList.toggle('is-easing', ease);
        screen.classList.toggle('is-revealing', percent > 0 && percent < 100);
        screen.style.setProperty('--reveal', `${percent}%`);
        stage?.classList.toggle('marketing-on', percent > 25);
    };
    const fromPointer = (event) => {
        const box = screen.getBoundingClientRect();
        return Math.max(0, Math.min(100, ((event.clientX - box.left) / box.width) * 100));
    };

    screen.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse' || pinned) return;
        touched = true;
        set(fromPointer(event));
    });
    screen.addEventListener('pointerleave', (event) => {
        if (event.pointerType === 'mouse' && !pinned) set(0, true);
    });
    screen.addEventListener('click', () => {
        touched = true;
        pinned = !pinned;
        set(pinned ? 100 : 0, true);
    });

    if (motionOk) {
        setTimeout(() => {
            if (touched) return;
            set(42, true);
            setTimeout(() => { if (!touched) set(0, true); }, 1400);
        }, 3200);
    }
});

// Header shadow once the page is scrolled
const header = document.querySelector('[data-header]');
if (header) {
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    requestAnimationFrame(onScroll);
    window.addEventListener('scroll', onScroll, { passive: true });
}
