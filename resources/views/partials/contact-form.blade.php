{{--
    Една проста форма (собствениците, ноември 2026): име, сайт, имейл, телефон, от какво се интересувате, кратко съобщение,
    съгласие и бутон. Часът за разговор е в сгънат <details>; връзка към #konsultatsiya го отваря.
    Връзка към #service-<ключ> отмята съответния интерес (ключовете от creatium.contact_interests).
--}}
@php
    $interests = site('contact_interests') ?? [];
    $oldInterests = (array) old('interests', in_array(request('interest'), array_keys($interests), true) ? [request('interest')] : []);
    $bookingOpen = filled(old('consultation')) || $errors->has('consultation');
    $field = 'w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30 placeholder:text-gray-500 dark:placeholder:text-gray-400';
    $label = 'block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200';
    $error = 'text-red-600 text-sm mt-1 dark:text-red-400';
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
        <form action="{{ lroute('contact.submit') }}" method="POST" novalidate class="space-y-5" data-contact-form>
            @csrf
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="website">{{ __('Не попълвайте това поле') }}</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Свържете се с нас') }}</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="{{ $label }}">{{ __('Име и фамилия') }} <span class="text-red-600 dark:text-red-400" aria-hidden="true">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="{{ $field }}">
                    @error('name')<p class="{{ $error }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="site_url" class="{{ $label }}">{{ __('Уебсайт') }}</label>
                    <input type="text" id="site_url" name="site_url" value="{{ old('site_url') }}" maxlength="255" inputmode="url" autocomplete="url" placeholder="{{ __('вашсайт.bg, ако имате') }}" class="{{ $field }}">
                    @error('site_url')<p class="{{ $error }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="{{ $label }}">{{ __('Имейл') }} <span class="text-red-600 dark:text-red-400" aria-hidden="true">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="{{ $field }}">
                    @error('email')<p class="{{ $error }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="{{ $label }}">{{ __('Телефон') }} <span class="text-red-600 dark:text-red-400" aria-hidden="true">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="{{ __('08X XXX XXXX') }}" class="{{ $field }}">
                    @error('phone')<p class="{{ $error }}">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($interests)
                <fieldset class="min-w-0">
                    <legend class="{{ $label }} mb-2">{{ __('От какво се интересувате') }}</legend>
                    <div class="grid sm:grid-cols-2 gap-x-4 gap-y-2">
                        @foreach($interests as $key => $interest)
                            <label class="flex items-start gap-2.5 rounded-xl border border-gray-200 px-3 py-2.5 text-sm text-gray-700 cursor-pointer hover:border-brand-400 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 dark:border-white/15 dark:text-gray-200 dark:has-[:checked]:border-brand-300 dark:has-[:checked]:bg-white/10">
                                <input type="checkbox" name="interests[]" value="{{ $key }}" class="mt-0.5 accent-brand-600 shrink-0" @checked(in_array($key, $oldInterests, true))>
                                <span>{{ $interest }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('interests.*')<p class="{{ $error }}">{{ $message }}</p>@enderror
                </fieldset>
            @endif

            <div>
                <label for="message" class="{{ $label }}">{{ __('Разкажете ни накратко за бизнеса си и какво търсите') }}</label>
                <textarea id="message" name="message" rows="4" maxlength="5000" class="{{ $field }}">{{ old('message') }}</textarea>
                @error('message')<p class="{{ $error }}">{{ $message }}</p>@enderror
            </div>

            <details class="group rounded-2xl border border-gray-200 dark:border-white/15" data-booking-details{{ $bookingOpen ? ' open' : '' }}>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-brand-950 dark:text-white [&::-webkit-details-marker]:hidden">
                    <span>{{ __('Искате ли да запазите час за разговор? (по желание)') }}</span>
                    <x-icon name="chevron-down" class="text-xs text-gray-500 transition-transform group-open:rotate-180 dark:text-gray-400" />
                </summary>
                <div class="px-4 pb-4">
                    @include('partials/booking')
                </div>
            </details>

            <div>
                <label class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="consent" value="1" required class="mt-1 accent-brand-600" {{ old('consent') ? 'checked' : '' }}>
                    <span>{!! __('Запознат/а съм с :link.', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank" class="text-brand-700 underline dark:text-brand-300">' . e(__('Политиката за поверителност')) . '</a>']) !!}</span>
                </label>
                @error('consent')<p class="{{ $error }}">{{ $message }}</p>@enderror
            </div>
            @include('partials.turnstile')
            <button type="submit" class="btn-shine w-full inline-flex items-center justify-center gap-2 bg-brand-950 text-white py-3.5 px-6 rounded-xl font-semibold hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                {{ __('Изпратете') }} <x-icon name="paper-plane" class="text-sm" />
            </button>
            <p class="text-center text-xs text-gray-500 dark:text-gray-400">{{ __('Ще ви отговорим до един работен ден.') }}</p>
        </form>
    @endif
</div>
<script>
    (function () {
        var form = document.querySelector('#contact-form [data-contact-form]');
        if (!form) return;
        var details = form.querySelector('[data-booking-details]');
        var box = document.getElementById('contact-form');

        // #konsultatsiya opens the booking; #service-<key> ticks that interest. The form is shown from its top.
        function fromHash() {
            var m = location.hash.match(/^#service-([a-z-]+)$/);
            if (m) {
                var c = form.querySelector('input[name="interests[]"][value="' + m[1] + '"]');
                if (c) c.checked = true;
                box.scrollIntoView({ block: 'start' });
                return;
            }
            if (location.hash === '#konsultatsiya' && details) {
                details.open = true;
                details.scrollIntoView({ block: 'start' });
            }
        }
        fromHash();
        if (location.hash.length > 1) {
            if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
            window.addEventListener('load', function () { requestAnimationFrame(fromHash); });
        }
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
