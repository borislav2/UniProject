{{-- Shared frame for the industry sites on the Projects page: not indexed, a thin neutral bar on top, forms send nothing. --}}
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $demo['name'] }} | {{ $demo['industry'] }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/css/demos.css'])
    @stack('head')
</head>
<body class="antialiased @yield('body_class', 'bg-white text-gray-900')">
    <div class="bg-[#0f1a2b] text-white text-xs">
        <div class="max-w-7xl mx-auto px-4 py-1.5 flex items-center justify-between gap-3">
            <p class="text-gray-300">Изработка <strong class="font-semibold text-white">Creatium Lab</strong></p>
            <span class="flex shrink-0 items-center gap-4">
                @if(! empty($demo['problems']))<a href="#za-bransha" class="hidden sm:inline font-semibold underline underline-offset-2 hover:text-[#9cc0ff]">Какво решава сайтът</a>@endif
                <a href="{{ route('portfolio') }}" class="font-semibold underline underline-offset-2 hover:text-[#9cc0ff]">Към проектите</a>
            </span>
        </div>
    </div>

    @yield('content')

    @includeWhen(! empty($demo['problems']), \App\Support\Design::is('g') ? 'partials.demo-case-g' : 'partials.demo-case')

    <div class="bg-[#0f1a2b] text-white font-sans">
        <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
            <p class="text-gray-300">Сайт, изработен от <strong class="text-white">Creatium Lab</strong>.</p>
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 font-semibold text-[#0f1a2b] hover:bg-[#e6eefc]">Искате такъв сайт? Пишете ни <x-icon name="arrow-right" class="text-xs" /></a>
        </div>
    </div>

    <div id="demo-toast" role="status" aria-live="polite" class="fixed bottom-5 left-1/2 z-[70] hidden w-[calc(100%-2rem)] max-w-md -translate-x-1/2 rounded-xl bg-[#0f1a2b] px-5 py-3 text-center text-sm text-white shadow-2xl font-sans">
        Благодарим. Формите и бутоните за връзка на тази страница не изпращат данни.
    </div>
    <script>
        (function () {
            var toast = document.getElementById('demo-toast');
            window.demoToast = function () {
                toast.classList.remove('hidden');
                clearTimeout(window.__demoToast);
                window.__demoToast = setTimeout(function () { toast.classList.add('hidden'); }, 3500);
            };
            document.querySelectorAll('form[data-demo-form], [data-demo-action]').forEach(function (el) {
                el.addEventListener(el.tagName === 'FORM' ? 'submit' : 'click', function (e) { e.preventDefault(); window.demoToast(); });
            });

            // Tabs and filters: buttons [data-show="key"] inside [data-group] show the items whose [data-key] has that key ("all" shows everything).
            document.querySelectorAll('[data-group]').forEach(function (group) {
                var buttons = group.querySelectorAll('[data-show]'), items = group.querySelectorAll('[data-key]');
                function set(key) {
                    buttons.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.show === key ? 'true' : 'false'); });
                    items.forEach(function (i) { i.hidden = key !== 'all' && i.dataset.key.split(' ').indexOf(key) < 0; });
                }
                buttons.forEach(function (b) { b.addEventListener('click', function () { set(b.dataset.show); }); });
                var first = group.querySelector('[data-show][aria-pressed="true"]');
                if (first) set(first.dataset.show);
            });

            // One choice out of several: buttons [data-value] inside [data-pick="name"]; the text goes to [data-pick-out="name"].
            document.querySelectorAll('[data-pick]').forEach(function (group) {
                group.addEventListener('click', function (e) {
                    var button = e.target.closest('button[data-value]');
                    if (! button || button.disabled) return;
                    group.querySelectorAll('button[data-value]').forEach(function (b) { b.setAttribute('aria-pressed', b === button ? 'true' : 'false'); });
                    document.querySelectorAll('[data-pick-out="' + group.dataset.pick + '"]').forEach(function (out) { out.textContent = button.dataset.value; });
                    group.dispatchEvent(new CustomEvent('pick', { detail: button.dataset.value, bubbles: true }));
                });
            });

            // Sections fade in once while scrolling; without motion or without the observer they are simply visible.
            var items = document.querySelectorAll('.dr');
            if ('IntersectionObserver' in window && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('dr-on');
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) { if (entry.isIntersecting) { entry.target.classList.add('in'); io.unobserve(entry.target); } });
                }, { rootMargin: '0px 0px -8% 0px' });
                items.forEach(function (item) { io.observe(item); });
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
