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
