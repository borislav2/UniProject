{{--
    Схемата на „Дигиталния двигател“: център и четирите компонента от services_page.cards, всеки води към картата си на /uslugi.
    Всеки компонент има превключвател: изключите ли един, линията му почервенява и двигателят спира (собствениците, ноември 2026).
    Без JavaScript всичко е включено. $cards: services_page.cards, $center: текстът в центъра. Слага се върху тъмна секция (on-dark).
--}}
@php
    $engineNodes = array_values(array_map(function ($card) {
        preg_match('/^(.+?)\s*\((.+)\)\s*$/u', $card['label'], $m);
        return ['slug' => $card['slug'], 'icon' => $card['icon'], 'name' => $m[1] ?? $card['label'], 'tag' => $m[2] ?? null];
    }, $cards));
@endphp
@if(count($engineNodes) === 4)
    <div class="reveal relative mt-14 grid gap-4 md:grid-cols-2 md:gap-x-48 md:gap-y-16 lg:gap-x-72" style="--reveal-delay: 80ms" data-engine>
        <svg class="pointer-events-none absolute inset-0 hidden md:block w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
            <g class="engine-lines" stroke="rgb(143 181 224 / 0.45)" stroke-width="0.25" fill="none" vector-effect="non-scaling-stroke">
                <path d="M50 50 L22 22" vector-effect="non-scaling-stroke" data-engine-line="0"/><path d="M50 50 L78 22" vector-effect="non-scaling-stroke" data-engine-line="1"/>
                <path d="M50 50 L22 78" vector-effect="non-scaling-stroke" data-engine-line="2"/><path d="M50 50 L78 78" vector-effect="non-scaling-stroke" data-engine-line="3"/>
            </g>
        </svg>
        <div class="order-first md:order-none md:absolute md:left-1/2 md:top-1/2 md:-translate-x-1/2 md:-translate-y-1/2 z-10 flex justify-center">
            <div class="engine-hub relative flex flex-col items-center justify-center text-center w-40 h-40 md:w-48 md:h-48 rounded-full bg-brand-gradient ring-1 ring-white/25 shadow-[0_0_80px_-10px_rgb(59_119_189/0.8)] transition-[filter,box-shadow] duration-500" data-engine-hub>
                <span class="absolute inset-0 rounded-full ring-1 ring-brand-300/40 engine-pulse" aria-hidden="true" data-engine-pulse></span>
                <x-icon name="cogs" class="text-2xl text-brand-100 transition-transform duration-700" data-engine-cogs />
                <span class="mt-2 px-4 text-sm md:text-base font-extrabold leading-tight">{{ $center }}</span>
                <span class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-emerald-300" data-engine-status role="status">
                    <span class="w-1.5 h-1.5 rounded-full bg-current" aria-hidden="true"></span><span data-engine-status-text>{{ __('Работи') }}</span>
                </span>
            </div>
        </div>
        @foreach($engineNodes as $node)
            <div class="relative edge glass group rounded-2xl p-5 md:p-6 flex items-center gap-4 transition-[opacity,box-shadow] duration-300" data-engine-node="{{ $loop->index }}">
                <span class="w-12 h-12 shrink-0 rounded-xl bg-brand-500/20 flex items-center justify-center"><x-icon :name="$node['icon']" class="text-brand-200" /></span>
                <a href="{{ lroute('services') }}#{{ $node['slug'] }}" class="min-w-0 hover:text-brand-100">
                    <span class="block text-xs font-bold tracking-[0.18em] text-brand-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="block font-extrabold text-lg leading-snug">{{ $node['name'] }}</span>
                    @if($node['tag'])<span class="block text-sm text-gray-400">{{ $node['tag'] }}</span>@endif
                </a>
                <button type="button" role="switch" aria-checked="true" aria-label="{{ $node['name'] }}" data-engine-toggle class="ml-auto shrink-0 relative h-7 w-12 rounded-full bg-emerald-500/80 ring-1 ring-white/20 transition-colors aria-[checked=false]:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300">
                    <span class="absolute top-1 left-1 h-5 w-5 rounded-full bg-white shadow transition-transform translate-x-5" data-engine-knob></span>
                </button>
            </div>
        @endforeach
    </div>
    <p class="mt-6 text-sm text-gray-400" data-engine-hint>{{ __('Натиснете превключвател, за да изключите една от частите.') }}</p>
    <script>
    (function () {
        var root = document.querySelector('[data-engine]');
        if (!root) return;
        var hub = root.querySelector('[data-engine-hub]'), status = root.querySelector('[data-engine-status]'), text = root.querySelector('[data-engine-status-text]');
        var cogs = root.querySelector('[data-engine-cogs]'), pulse = root.querySelector('[data-engine-pulse]'), hint = document.querySelector('[data-engine-hint]');
        var names = [], angle = 0, timer = null;
        root.querySelectorAll('[data-engine-node]').forEach(function (node) { names.push(node.querySelector('a .font-extrabold').textContent.trim()); });
        function spin() { angle = (angle + 6) % 360; cogs.style.transform = 'rotate(' + angle + 'deg)'; }
        function render() {
            var off = [];
            root.querySelectorAll('[data-engine-node]').forEach(function (node) {
                var i = +node.dataset.engineNode, on = node.querySelector('[data-engine-toggle]').getAttribute('aria-checked') === 'true';
                node.querySelector('[data-engine-knob]').style.transform = on ? 'translateX(1.25rem)' : 'translateX(0)';
                node.style.opacity = on ? '' : '0.55';
                node.style.boxShadow = on ? '' : 'inset 0 0 0 1px rgb(248 113 113 / 0.6)';
                var line = root.querySelector('[data-engine-line="' + i + '"]');
                if (line) { line.style.stroke = on ? '' : 'rgb(248 113 113 / 0.9)'; line.style.strokeDasharray = on ? '' : '4 4'; }
                if (!on) off.push(names[i]);
            });
            var running = off.length === 0;
            hub.style.filter = running ? '' : 'grayscale(1) brightness(0.7)';
            pulse.style.display = running ? '' : 'none';
            status.classList.toggle('bg-emerald-400/15', running); status.classList.toggle('text-emerald-300', running);
            status.classList.toggle('bg-red-500/20', !running); status.classList.toggle('text-red-300', !running);
            text.textContent = running ? @json(__('Работи')) : @json(__('Спря'));
            if (hint) hint.textContent = running ? @json(__('Натиснете превключвател, за да изключите една от частите.')) : @json(__('Без')) + ' „' + off.join('“, „') + '“ ' + @json(__('двигателят не работи. Всички части трябва да са изправни.'));
            clearInterval(timer); timer = null;
            if (running && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) timer = setInterval(spin, 60);
        }
        root.addEventListener('click', function (e) {
            var t = e.target.closest('[data-engine-toggle]');
            if (!t) return;
            t.setAttribute('aria-checked', t.getAttribute('aria-checked') === 'true' ? 'false' : 'true');
            render();
        });
        render();
    })();
    </script>
@endif
