{{--
    Формата за запитване на 4 стъпки: цел → бизнес → кога → контакт.
    Без JavaScript всички стъпки се виждат една под друга и формата работи; скриптът ги показва по една,
    с проверка на задължителните полета преди „Напред“. Връзка към #konsultatsiya отваря стъпката с календара.
--}}
@php
    $topics = site('contact_topics');
    $selectedService = old('service', in_array(request('service'), array_keys($topics), true) ? request('service') : null);
    $goals = site('contact_goals') ?? [];
    $timelines = site('contact_timelines') ?? [];
    $oldGoals = (array) old('goals', []);
    $steps = [__('Цел'), __('Бизнес'), __('Кога'), __('Контакт')];
    $field = 'w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30 placeholder:text-gray-500 dark:placeholder:text-gray-400';
    $choice = 'block cursor-pointer rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-brand-400 peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:text-brand-950 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-100 dark:border-white/15 dark:text-gray-200 dark:peer-checked:border-brand-300 dark:peer-checked:bg-white/10 dark:peer-checked:text-white';
    $stepWithError = collect([['service', 'goals'], ['business', 'site_url', 'message'], ['timeline', 'consultation'], ['name', 'phone', 'email', 'consent']])
        ->search(fn ($names) => collect($names)->contains(fn ($n) => $errors->has($n) || $errors->has("$n.*")));
@endphp
<div id="contact-form" class="scroll-mt-28 md:scroll-mt-32 bg-white rounded-2xl p-6 md:p-8 text-gray-900 dark:bg-ink-900 dark:text-gray-100 dark:ring-1 dark:ring-white/10">
    @if(session('success'))
        <div class="text-center py-8" role="status">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center dark:bg-green-500/15">
                <x-icon name="check" class="text-green-600 text-xl dark:text-green-400" />
            </div>
            <h3 class="text-xl font-bold mb-2">{{ __('Благодарим!') }}</h3>
            <p class="text-gray-600 dark:text-gray-300">{{ session('success') }}</p>
        </div>
    @else
        <form action="{{ lroute('contact.submit') }}" method="POST" novalidate data-steps data-start="{{ $stepWithError === false ? 0 : $stepWithError }}">
            @csrf
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="website">{{ __('Не попълвайте това поле') }}</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="hidden" data-steps-head>
                <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
                    @foreach($steps as $label)
                        <li class="flex items-center gap-2" data-step-label>
                            @if(! $loop->first)<x-icon name="arrow-right" class="text-[9px] text-gray-300 dark:text-gray-600" />@endif
                            <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ $label }}</span>
                        </li>
                    @endforeach
                </ol>
                <div class="mt-3 h-1 rounded-full bg-gray-100 dark:bg-white/10"><div class="h-full rounded-full bg-brand-600 transition-[width] duration-300 dark:bg-brand-300" style="width: 25%" data-steps-bar></div></div>
                <p class="mt-5 text-[11px] font-bold uppercase tracking-[0.25em] text-gray-500 dark:text-gray-400" data-steps-count aria-live="polite"></p>
            </div>

            {{-- 1. Goal --}}
            <fieldset class="min-w-0 space-y-5" data-step>
                <legend class="text-xl md:text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('С какво да помогнем?') }}</legend>
                <div>
                    <p id="service-label" class="text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">{{ __('Настоящ статус на проекта') }}</p>
                    <div class="grid gap-2" role="radiogroup" aria-labelledby="service-label">
                        @foreach($topics as $key => $topic)
                            <label class="relative">
                                <input type="radio" name="service" value="{{ $key }}" class="peer sr-only" required @checked($selectedService === $key)>
                                <span class="{{ $choice }}">{{ $topic }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('service')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                @if($goals)
                    <div>
                        <p id="goals-label" class="text-sm font-medium text-gray-700 mb-2 dark:text-gray-200">{{ __('Какво искате да подобрите?') }} <span class="text-gray-500 font-normal dark:text-gray-400">{{ __('(изберете едно или няколко)') }}</span></p>
                        <div class="flex flex-wrap gap-2" role="group" aria-labelledby="goals-label">
                            @foreach($goals as $key => $goal)
                                <label class="relative">
                                    <input type="checkbox" name="goals[]" value="{{ $key }}" class="peer sr-only" @checked(in_array($key, $oldGoals, true))>
                                    <span class="{{ $choice }} !rounded-full !py-2">{{ $goal }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('goals.*')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif
            </fieldset>

            {{-- 2. Business --}}
            <fieldset class="min-w-0 mt-8 space-y-4" data-step>
                <legend class="text-xl md:text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Разкажете ни за бизнеса') }}</legend>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="business" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Бизнес и град') }} <span class="text-gray-500 font-normal dark:text-gray-400">{{ __('(по избор)') }}</span></label>
                        <input type="text" id="business" name="business" value="{{ old('business') }}" maxlength="150" autocomplete="organization" placeholder="{{ __('напр. автосервиз, Бургас') }}" class="{{ $field }}">
                        @error('business')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="site_url" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Сегашен сайт') }} <span class="text-gray-500 font-normal dark:text-gray-400">{{ __('(ако имате)') }}</span></label>
                        <input type="text" id="site_url" name="site_url" value="{{ old('site_url') }}" maxlength="255" inputmode="url" autocomplete="url" placeholder="vashiyatsait.bg" class="{{ $field }}">
                        @error('site_url')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Описание на бизнес целите') }} <span class="text-gray-500 font-normal dark:text-gray-400">{{ __('(по избор)') }}</span></label>
                    <textarea id="message" name="message" rows="4" placeholder="{{ __('Какво искате да постигнете? Например повече обаждания, онлайн поръчки или по-малко ръчна работа.') }}" class="{{ $field }}">{{ old('message') }}</textarea>
                    @error('message')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </fieldset>

            {{-- 3. Timing and consultation --}}
            <fieldset class="min-w-0 mt-8 space-y-5" data-step>
                <legend class="text-xl md:text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Кога искате да започнем?') }}</legend>
                @if($timelines)
                    <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="{{ __('Кога искате да започнем?') }}">
                        @foreach($timelines as $key => $timeline)
                            <label class="relative">
                                <input type="radio" name="timeline" value="{{ $key }}" class="peer sr-only" @checked(old('timeline') === $key)>
                                <span class="{{ $choice }} text-center">{{ $timeline }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('timeline')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                @endif
                @include('partials/booking')
            </fieldset>

            {{-- 4. Contact --}}
            <fieldset class="min-w-0 mt-8 space-y-4" data-step>
                <legend class="text-xl md:text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Как да се свържем с вас?') }}</legend>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Име и фамилия') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="{{ $field }}">
                        @error('name')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Телефон за връзка') }}</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="{{ __('08X XXX XXXX') }}" class="{{ $field }}">
                        @error('phone')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Имейл') }}</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $field }}">
                        @error('email')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" name="consent" value="1" required class="mt-1 accent-brand-600" {{ old('consent') ? 'checked' : '' }}>
                        <span>{!! __('Запознат/а съм с :link.', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank" class="text-brand-700 underline dark:text-brand-300">' . e(__('Политиката за поверителност')) . '</a>']) !!}</span>
                    </label>
                    @error('consent')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn-shine w-full inline-flex items-center justify-center gap-2 bg-brand-950 text-white py-3.5 px-6 rounded-xl font-semibold uppercase tracking-wide hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                    {{ __('Стартирайте вашия проект') }} <x-icon name="paper-plane" class="text-sm" />
                </button>
                <p class="text-center text-xs text-gray-500 dark:text-gray-400">{{ __('Ще ви отговорим до един работен ден.') }}</p>
            </fieldset>

            <div class="hidden mt-8 items-center justify-between gap-3" data-steps-nav>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl px-4 py-3 font-semibold text-gray-600 hover:text-brand-950 invisible dark:text-gray-300 dark:hover:text-white" data-steps-back><x-icon name="arrow-left" class="text-xs" /> {{ __('Назад') }}</button>
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-brand-950 px-6 py-3 font-semibold text-white hover:bg-brand-800 dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100" data-steps-next>{{ __('Напред') }} <x-icon name="arrow-right" class="text-xs" /></button>
            </div>
        </form>
    @endif
</div>
<script>
    (function () {
        var form = document.querySelector('#contact-form [data-steps]');
        if (!form) return;
        var steps = form.querySelectorAll('[data-step]'), labels = form.querySelectorAll('[data-step-label]');
        var head = form.querySelector('[data-steps-head]'), nav = form.querySelector('[data-steps-nav]');
        var bar = form.querySelector('[data-steps-bar]'), count = form.querySelector('[data-steps-count]');
        var back = form.querySelector('[data-steps-back]'), next = form.querySelector('[data-steps-next]');
        var box = document.getElementById('contact-form'), total = steps.length, current = 0;
        var countText = @json(__('Въпрос :n от :total'));

        head.classList.remove('hidden');
        nav.classList.replace('hidden', 'flex');
        steps.forEach(function (s) { s.classList.remove('mt-8'); });

        function show(i, focus) {
            current = Math.max(0, Math.min(total - 1, i));
            steps.forEach(function (s, k) { s.hidden = k !== current; });
            labels.forEach(function (l, k) { l.classList.toggle('text-brand-700', k === current); l.classList.toggle('dark:text-brand-300', k === current); });
            bar.style.width = ((current + 1) / total * 100) + '%';
            count.textContent = countText.replace(':n', current + 1).replace(':total', total);
            back.classList.toggle('invisible', current === 0);
            next.style.display = current === total - 1 ? 'none' : '';
            // The step's title and progress must stay visible below the floating header.
            if (focus && box.getBoundingClientRect().top < 0) reveal(true);
        }

        function reveal(smooth) {
            box.scrollIntoView({ block: 'start', behavior: smooth ? 'smooth' : 'auto' });
        }

        // Required fields of a step must be valid before moving on (radio groups count once).
        function valid(i) {
            var fields = steps[i].querySelectorAll('[required]');
            for (var k = 0; k < fields.length; k++) {
                if (!fields[k].checkValidity()) {
                    if (i !== current) { show(i); reveal(false); }
                    fields[k].reportValidity();
                    return false;
                }
            }
            return true;
        }

        next.addEventListener('click', function () { if (valid(current)) show(current + 1, true); });
        // A visitor who jumped straight to the calendar may have skipped step 1: send them back to what is missing.
        form.addEventListener('submit', function (e) {
            for (var i = 0; i < total; i++) {
                if (!valid(i)) { e.preventDefault(); return; }
            }
        });
        back.addEventListener('click', function () { show(current - 1, true); });
        form.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && current < total - 1) { e.preventDefault(); next.click(); }
        });

        // Links to #konsultatsiya (and #service-<key>) open the right step.
        function fromHash() {
            var m = location.hash.match(/^#service-([a-z-]+)$/);
            if (m) {
                var r = form.querySelector('input[name="service"][value="' + m[1] + '"]');
                if (r) { r.checked = true; show(0); reveal(false); }
                return;
            }
            var target = location.hash.length > 1 && document.getElementById(location.hash.slice(1));
            if (target && form.contains(target)) {
                var k = Array.prototype.indexOf.call(steps, target.closest('[data-step]'));
                if (k >= 0) { show(k); reveal(false); }
            }
        }
        show(parseInt(form.dataset.start, 10) || 0);
        fromHash();
        window.addEventListener('hashchange', fromHash);
        document.addEventListener('click', function (e) {
            var a = e.target.closest('a[href$="#konsultatsiya"]');
            if (a && a.pathname === location.pathname && location.hash === '#konsultatsiya') setTimeout(fromHash, 0);
        });
    })();
</script>
@if(session('success') || $errors->any())
    <script>document.getElementById('contact-form').scrollIntoView({ block: 'center' });</script>
@endif
