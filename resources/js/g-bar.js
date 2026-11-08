// Design G: the phone bar (call + quote) hides while the contact form or the quote form is on screen, and the chat button follows it down.
const bar = document.querySelector('[data-g-bar]');
const targets = document.querySelectorAll('#contact-form, #oferta');

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
