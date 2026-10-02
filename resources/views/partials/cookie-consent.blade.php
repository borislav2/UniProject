{{-- Shown only when META_PIXEL_ID is set. The pixel loads only after the visitor clicks "Приемам". --}}
<div id="cookie-banner" class="hidden fixed inset-x-0 bottom-0 z-[60] p-3 sm:p-4" role="dialog" aria-live="polite" aria-label="Бисквитки">
    <div class="max-w-3xl mx-auto rounded-2xl bg-brand-950 text-white shadow-2xl ring-1 ring-white/10 p-5 md:flex md:items-center md:gap-6">
        <p class="text-sm text-gray-200 flex-1 leading-relaxed">
            Използваме бисквитки на Meta, за да измерваме ефекта от рекламите си. Зареждат се само с ваше съгласие и можете да го оттеглите по всяко време.
            <a href="{{ route('cookies') }}" class="underline text-white">Научете повече</a>
        </p>
        <div class="mt-4 md:mt-0 flex gap-2 shrink-0">
            <button type="button" data-consent="denied" class="flex-1 md:flex-none px-4 py-2.5 rounded-xl text-sm font-semibold bg-white/10 hover:bg-white/20 transition-colors">Отказвам</button>
            <button type="button" data-consent="granted" class="flex-1 md:flex-none px-4 py-2.5 rounded-xl text-sm font-semibold bg-white text-brand-950 hover:bg-brand-50 transition-colors">Приемам</button>
        </div>
    </div>
</div>
<script>
(function () {
    var KEY = 'creatium-cookie-consent';
    var pixelId = @json(config('creatium.meta_pixel_id'));
    var leadCreated = @json((bool) session('lead_created'));
    var banner = document.getElementById('cookie-banner');

    function read() { try { return localStorage.getItem(KEY); } catch (e) { return null; } }
    function write(v) { try { localStorage.setItem(KEY, v); } catch (e) {} }

    function clearMetaCookies() {
        var host = location.hostname.replace(/^www\./, '');
        ['_fbp', '_fbc'].forEach(function (name) {
            [host, '.' + host, location.hostname].forEach(function (domain) {
                document.cookie = name + '=; Max-Age=0; path=/; domain=' + domain;
            });
            document.cookie = name + '=; Max-Age=0; path=/';
        });
    }

    function loadPixel() {
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', pixelId);
        fbq('track', 'PageView');
        if (leadCreated) { fbq('track', 'Lead'); }
    }

    var state = read();
    if (state === 'granted') { loadPixel(); }
    else if (state !== 'denied') { banner.classList.remove('hidden'); }

    banner.addEventListener('click', function (e) {
        var choice = e.target.getAttribute && e.target.getAttribute('data-consent');
        if (!choice) { return; }
        var previous = read();
        write(choice);
        banner.classList.add('hidden');
        if (choice === 'granted') { loadPixel(); }
        else if (previous === 'granted') { clearMetaCookies(); location.reload(); }
    });

    document.querySelectorAll('[data-cookie-settings]').forEach(function (el) {
        el.addEventListener('click', function (e) { e.preventDefault(); banner.classList.remove('hidden'); });
    });
})();
</script>
