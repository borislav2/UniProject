// Design G, every page: the phone bar (call + quote) hides while the contact form or the quote form is on screen, and the chat button follows it down.
const bar = document.querySelector('[data-g-bar]');
const targets = document.querySelectorAll('#contact-form, #g-contact-form, #oferta');

if (bar && targets.length && 'IntersectionObserver' in window) {
    const inView = new Set();
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => (entry.isIntersecting ? inView.add(entry.target) : inView.delete(entry.target)));
        const off = inView.size > 0;
        bar.classList.toggle('is-off', off);
        document.documentElement.classList.toggle('g-bar-off', off);
    });
    targets.forEach((target) => observer.observe(target));
}

// Billing switch: both prices are in the page, the attribute picks one (without JavaScript the monthly one shows).
document.querySelectorAll('[data-g-billing-root]').forEach((root) => {
    const buttons = root.querySelectorAll('.g-tog button');
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            root.setAttribute('data-g-billing', button.dataset.b);
            buttons.forEach((other) => other.setAttribute('aria-pressed', other === button ? 'true' : 'false'));
        });
    });
});

// Package cards on a phone (below 760px they are a swipeable row): start with the recommended card in the middle so both neighbours peek.
document.querySelectorAll('.g-plans').forEach((row) => {
    const rec = row.querySelector('.g-rec');
    if (rec && row.scrollWidth > row.clientWidth + 1) row.scrollLeft = Math.max(0, rec.offsetLeft - (row.clientWidth - rec.offsetWidth) / 2);
});

// Header dropdowns ("Услуги", "За нас"): hover and focus open them in CSS; the small button toggles (Enter and Space), Enter on a link follows it,
// Esc closes and returns focus to the button, the arrow keys move between the button and the items. A closed state (data-closed) is dropped
// when focus or the pointer comes back from outside, so a keyboard user can always reach the items again.
document.querySelectorAll('[data-g-dd]').forEach((dd) => {
    const button = dd.querySelector('.g-dd-btn');
    const items = () => Array.from(dd.querySelectorAll('.g-dd-panel a'));
    const isOpen = () => dd.matches(':hover, :focus-within, .is-open') && !dd.hasAttribute('data-closed');
    const sync = () => button.setAttribute('aria-expanded', isOpen() ? 'true' : 'false');
    const open = () => { dd.removeAttribute('data-closed'); dd.classList.add('is-open'); sync(); };
    const close = () => { dd.classList.remove('is-open'); dd.setAttribute('data-closed', ''); sync(); };
    // On a touch screen the emulated hover opens the panel just before the click, so the state before the tap is remembered.
    let before = null;
    button.addEventListener('pointerdown', (event) => { before = event.pointerType === 'touch' ? isOpen() : null; });
    button.addEventListener('click', () => {
        const wasOpen = before === null ? isOpen() : before;
        before = null;
        if (wasOpen) close(); else open();
    });
    dd.addEventListener('mouseenter', () => { dd.removeAttribute('data-closed'); sync(); });
    dd.addEventListener('mouseleave', () => { dd.classList.remove('is-open'); dd.removeAttribute('data-closed'); sync(); });
    dd.addEventListener('focusin', (event) => {
        if (!event.relatedTarget || !dd.contains(event.relatedTarget)) dd.removeAttribute('data-closed');
        sync();
    });
    dd.addEventListener('focusout', () => setTimeout(() => { if (!dd.contains(document.activeElement)) { dd.classList.remove('is-open'); dd.removeAttribute('data-closed'); } sync(); }, 0));
    dd.addEventListener('keydown', (event) => {
        const list = items();
        const index = list.indexOf(document.activeElement);
        if (event.key === 'ArrowDown' && (document.activeElement === button || index > -1)) {
            event.preventDefault();
            open();
            (list[index + 1] || list[0]).focus();
        } else if (event.key === 'ArrowUp' && index > -1) {
            event.preventDefault();
            (index === 0 ? button : list[index - 1]).focus();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || button.getAttribute('aria-expanded') !== 'true') return;
        const inside = dd.contains(document.activeElement);
        close();
        if (inside) button.focus();
    });
    document.addEventListener('click', (event) => {
        if (!dd.contains(event.target)) { dd.classList.remove('is-open'); sync(); }
    });
});

// The five steps (/paketi): the pointer, focus or a tap lights one step, the line fills up to it and the others dim. Without JavaScript all five stay as they are.
document.querySelectorAll('.g-steps').forEach((list) => {
    const items = Array.from(list.children);
    const light = (index) => {
        list.setAttribute('data-active', String(index + 1));
        items.forEach((item, i) => {
            item.classList.toggle('is-reached', i <= index);
            item.classList.toggle('is-active', i === index);
        });
    };
    const clear = () => {
        list.removeAttribute('data-active');
        items.forEach((item) => item.classList.remove('is-reached', 'is-active'));
    };
    items.forEach((item, index) => {
        item.addEventListener('mouseenter', () => light(index));
        item.addEventListener('focus', () => light(index));
        item.addEventListener('click', () => light(index));
        item.addEventListener('blur', () => { if (!list.matches(':hover')) clear(); });
    });
    list.addEventListener('mouseleave', () => { if (!list.contains(document.activeElement)) clear(); });
    document.addEventListener('click', (event) => { if (!list.contains(event.target)) clear(); });
});

// The interactive panel (/uslugi): the same rules as App\Support\GPanel. Switches 1 to 7: 1 Google profile, 2 SEO, 3 ads, 4 brand, 5 fast site, 6 path to contact, 7 emails.
document.querySelectorAll('[data-g-panel]').forEach((panel) => {
    const total = Number(panel.dataset.total) || 7;
    const switches = Array.from(panel.querySelectorAll('[role="switch"]'));
    const presets = Array.from(panel.querySelectorAll('[data-preset]'));
    const order = (panel.dataset.order || '').split(',');
    const count = panel.querySelector('[data-count]');
    const bar = panel.querySelector('[data-bar]');
    const msg = panel.querySelector('[data-msg]');
    const cta = panel.querySelector('[data-cta]');
    // Rule order, first match wins (same as GPanel::message): full (all 7), traffic (ads or SEO while the fast site 5 is off), base (5 and 6 without 1, 2, 3), low (0 to 2 without 2 or 3), mixed.
    const rule = (on) => {
        const has = (n) => on.includes(n);
        if (on.length >= total) return 'full';
        if ((has(2) || has(3)) && !has(5)) return 'traffic';
        if (has(5) && has(6) && !has(1) && !has(2) && !has(3)) return 'base';
        if (on.length <= 2 && !has(2) && !has(3)) return 'low';
        return 'mixed';
    };
    const render = () => {
        const on = switches.filter((s) => s.getAttribute('aria-checked') === 'true').map((s) => Number(s.dataset.n));
        const share = Math.floor((on.length * 100) / total);
        const key = rule(on);
        count.textContent = String(on.length);
        bar.style.width = `${share}%`;
        msg.textContent = panel.dataset[`msg${key.charAt(0).toUpperCase()}${key.slice(1)}`] || '';
        // The button leads to the contact form with the directions of the missing switches ticked.
        const missing = new Set(switches.filter((s) => s.getAttribute('aria-checked') !== 'true').map((s) => s.dataset.dir).filter(Boolean));
        const directions = order.filter((direction) => missing.has(direction));
        cta.href = panel.dataset.contact + (directions.length ? `?napravleniya=${directions.join(',')}` : '') + '#kontakt';
        // 6 or 7 of 7: nothing left to complete, so the button invites a talk (same rule as GPanel::state nearly_full).
        const label = cta.querySelector('[data-cta-label]');
        if (label) label.textContent = (on.length >= total - 1 ? cta.dataset.labelFull : cta.dataset.label) || cta.dataset.label;
    };
    switches.forEach((button) => button.addEventListener('click', () => {
        button.setAttribute('aria-checked', button.getAttribute('aria-checked') === 'true' ? 'false' : 'true');
        // A manual change means it is their own business now, so the quick button turns neutral.
        presets.forEach((p) => p.setAttribute('aria-pressed', 'false'));
        render();
    }));
    presets.forEach((button) => button.addEventListener('click', () => {
        const numbers = button.dataset.preset.split(',').map(Number);
        switches.forEach((s) => s.setAttribute('aria-checked', String(numbers.includes(Number(s.dataset.n)))));
        presets.forEach((p) => p.setAttribute('aria-pressed', String(p === button)));
        render();
    }));
});
