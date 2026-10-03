{{-- Shown only when GTM is on. The choice is stored in localStorage and sent to GTM as a Consent Mode update (see partials/gtm-head). --}}
<div id="cookie-banner" class="hidden fixed inset-x-0 bottom-0 z-[60] p-3 sm:p-4" role="dialog" aria-modal="false" aria-labelledby="cookie-banner-title">
    <div class="max-w-3xl mx-auto rounded-2xl bg-brand-950 text-white shadow-2xl ring-1 ring-white/10 p-5 md:p-6 dark:bg-ink-800 dark:ring-white/15">
        <h2 id="cookie-banner-title" class="font-bold">{{ __('Бисквитки') }}</h2>
        <p class="mt-1 text-sm text-gray-200 leading-relaxed">
            {{ __('Използваме бисквитки, за да виждаме как се ползва сайтът и дали рекламите ни работят. Аналитичните и маркетинговите се зареждат само ако ги разрешите.') }}
            <a href="{{ lroute('cookies') }}" class="underline text-white">{{ __('Политика за бисквитките') }}</a>
        </p>

        <div id="cookie-options" class="hidden mt-4 space-y-2">
            <label class="flex items-start gap-3 rounded-xl bg-white/5 ring-1 ring-white/10 p-3">
                <input type="checkbox" checked disabled class="mt-1 accent-brand-400">
                <span class="text-sm"><strong class="block">{{ __('Необходими') }}</strong><span class="text-gray-300">{{ __('Сесия и защита на формите. Без тях сайтът не работи, затова са винаги включени.') }}</span></span>
            </label>
            <label class="flex items-start gap-3 rounded-xl bg-white/5 ring-1 ring-white/10 p-3 cursor-pointer">
                <input type="checkbox" data-category="analytics" class="mt-1 accent-brand-400">
                <span class="text-sm"><strong class="block">{{ __('Аналитични') }}</strong><span class="text-gray-300">{{ __('Google Analytics и Microsoft Clarity: кои страници се четат и кое е неудобно.') }}</span></span>
            </label>
            <label class="flex items-start gap-3 rounded-xl bg-white/5 ring-1 ring-white/10 p-3 cursor-pointer">
                <input type="checkbox" data-category="marketing" class="mt-1 accent-brand-400">
                <span class="text-sm"><strong class="block">{{ __('Маркетингови') }}</strong><span class="text-gray-300">{{ __('Пиксели на рекламните платформи: колко запитвания идват от рекламите ни.') }}</span></span>
            </label>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row gap-2">
            <button type="button" data-consent="settings" class="sm:mr-auto px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-200 hover:text-white hover:bg-white/10 transition-colors" aria-controls="cookie-options" aria-expanded="false">{{ __('Настройки') }}</button>
            <button type="button" data-consent="save" class="hidden px-4 py-2.5 rounded-xl text-sm font-semibold bg-white/10 hover:bg-white/20 transition-colors">{{ __('Запазване на избора') }}</button>
            <button type="button" data-consent="reject" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white text-brand-950 hover:bg-brand-50 transition-colors">{{ __('Отказвам') }}</button>
            <button type="button" data-consent="accept" class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white text-brand-950 hover:bg-brand-50 transition-colors">{{ __('Приемам всички') }}</button>
        </div>
    </div>
</div>
<script>
(function () {
    var KEY = 'creatium-consent';
    var YEAR = 31536000000;
    var banner = document.getElementById('cookie-banner');
    var options = document.getElementById('cookie-options');
    var boxes = banner.querySelectorAll('[data-category]');
    var saveBtn = banner.querySelector('[data-consent="save"]');
    var settingsBtn = banner.querySelector('[data-consent="settings"]');

    function read() {
        try {
            var c = JSON.parse(localStorage.getItem(KEY));
            return c && Date.now() - c.at < YEAR ? c : null;
        } catch (e) { return null; }
    }

    function clearTrackingCookies() {
        var host = location.hostname.replace(/^www\./, '');
        document.cookie.split(';').forEach(function (part) {
            var name = part.split('=')[0].trim();
            if (!/^(_ga|_gid|_gat|_gcl|_clck|_clsk|_fbp|_fbc)/.test(name)) { return; }
            [host, '.' + host, location.hostname, ''].forEach(function (domain) {
                document.cookie = name + '=; Max-Age=0; path=/' + (domain ? '; domain=' + domain : '');
            });
        });
    }

    function save(analytics, marketing) {
        var previous = read();
        try { localStorage.setItem(KEY, JSON.stringify({analytics: analytics, marketing: marketing, at: Date.now()})); } catch (e) {}
        gtag('consent', 'update', {analytics_storage: analytics ? 'granted' : 'denied', ad_storage: marketing ? 'granted' : 'denied', ad_user_data: marketing ? 'granted' : 'denied', ad_personalization: marketing ? 'granted' : 'denied'});
        dataLayer.push({event: 'consent_update', consent_analytics: analytics, consent_marketing: marketing});
        banner.classList.add('hidden');
        if (previous && ((previous.analytics && !analytics) || (previous.marketing && !marketing))) {
            clearTrackingCookies();
            location.reload();
        }
    }

    function open(withOptions) {
        var c = read();
        boxes.forEach(function (box) { box.checked = !!(c && c[box.getAttribute('data-category')]); });
        options.classList.toggle('hidden', !withOptions);
        saveBtn.classList.toggle('hidden', !withOptions);
        settingsBtn.classList.toggle('hidden', withOptions);
        settingsBtn.setAttribute('aria-expanded', withOptions ? 'true' : 'false');
        banner.classList.remove('hidden');
    }

    try { localStorage.removeItem('creatium-cookie-consent'); } catch (e) {}
    if (!read()) { open(false); }

    banner.addEventListener('click', function (e) {
        var action = e.target.closest && e.target.closest('[data-consent]');
        if (!action) { return; }
        action = action.getAttribute('data-consent');
        if (action === 'settings') { open(true); }
        else if (action === 'accept') { save(true, true); }
        else if (action === 'reject') { save(false, false); }
        else if (action === 'save') {
            save(banner.querySelector('[data-category="analytics"]').checked, banner.querySelector('[data-category="marketing"]').checked);
        }
    });

    document.querySelectorAll('[data-cookie-settings]').forEach(function (el) {
        el.addEventListener('click', function (e) { e.preventDefault(); open(true); });
    });
})();
</script>
