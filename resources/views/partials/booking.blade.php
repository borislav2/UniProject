{{-- Ден и час за консултация (по избор). Без JavaScript се виждат всички дни един под друг; със скрипта се показва само избраният. --}}
@php
    $bookingDays = \App\Support\Booking::days();
    $selectedSlot = old('consultation');
    $selectedDay = $selectedSlot ? substr($selectedSlot, 0, 10) : $bookingDays[0]['date']->format('Y-m-d');
@endphp
<fieldset id="konsultatsiya" class="min-w-0 scroll-mt-28 rounded-2xl border border-gray-200 p-4 dark:border-white/15" data-booking>
    <legend class="px-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Ден и час за консултация') }} <span class="text-gray-500 font-normal dark:text-gray-400">{{ __('(по избор)') }}</span></legend>
    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Изберете удобен час и ще ви се обадим тогава. Разговорът е около :minutes минути.', ['minutes' => config('creatium.booking.minutes')]) }}</p>

    <div class="mt-3 -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:thin] hidden" data-booking-days role="tablist" aria-label="{{ __('Ден') }}">
        @foreach($bookingDays as $day)
            @php($key = $day['date']->format('Y-m-d'))
            <button type="button" data-day="{{ $key }}" role="tab" aria-selected="{{ $key === $selectedDay ? 'true' : 'false' }}"
                    class="shrink-0 w-16 rounded-xl border px-2 py-2 text-center transition-colors border-gray-200 text-gray-700 hover:border-brand-400 aria-selected:border-brand-950 aria-selected:bg-brand-950 aria-selected:text-white dark:border-white/15 dark:text-gray-200 dark:aria-selected:border-white dark:aria-selected:bg-white dark:aria-selected:text-brand-950">
                <span class="block text-[11px] font-semibold uppercase">{{ $day['date']->locale(app()->getLocale())->isoFormat('dd') }}</span>
                <span class="block text-lg font-extrabold leading-tight">{{ $day['date']->format('j') }}</span>
                <span class="block text-[11px]">{{ $day['date']->locale(app()->getLocale())->isoFormat('MMM') }}</span>
            </button>
        @endforeach
    </div>

    @foreach($bookingDays as $day)
        @php($key = $day['date']->format('Y-m-d'))
        <div class="mt-3" data-day-panel="{{ $key }}">
            <p class="mb-2 text-xs font-semibold text-gray-600 dark:text-gray-300" data-day-title>{{ ucfirst($day['date']->locale(app()->getLocale())->isoFormat('dddd, D MMMM')) }}</p>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                @foreach($day['slots'] as $slot)
                    <label class="relative">
                        <input type="radio" name="consultation" value="{{ $slot['value'] }}" class="peer sr-only" @checked($selectedSlot === $slot['value']) @disabled($slot['taken'])>
                        <span class="block cursor-pointer rounded-lg border py-2 text-center text-sm font-semibold transition-colors border-gray-200 text-gray-700 hover:border-brand-400 peer-checked:border-brand-600 peer-checked:bg-brand-600 peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-brand-100 peer-disabled:cursor-not-allowed peer-disabled:border-dashed peer-disabled:text-gray-400 peer-disabled:line-through peer-disabled:hover:border-gray-200 dark:border-white/15 dark:text-gray-200 dark:peer-checked:border-brand-400 dark:peer-checked:bg-brand-500 dark:peer-disabled:text-gray-500">{{ $slot['time'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="mt-3 flex items-center justify-between gap-3 text-xs">
        <p class="text-gray-600 dark:text-gray-300" data-booking-summary aria-live="polite"></p>
        <button type="button" class="hidden font-semibold text-brand-700 underline dark:text-brand-300" data-booking-clear>{{ __('Без час') }}</button>
    </div>
    @error('consultation')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
</fieldset>
<script>
    (function () {
        var box = document.currentScript.previousElementSibling;
        var tabs = box.querySelector('[data-booking-days]'), panels = box.querySelectorAll('[data-day-panel]');
        var summary = box.querySelector('[data-booking-summary]'), clear = box.querySelector('[data-booking-clear]');
        tabs.classList.remove('hidden');
        box.querySelectorAll('[data-day-title]').forEach(function (t) { t.classList.add('sr-only'); });
        function show(day) {
            tabs.querySelectorAll('[data-day]').forEach(function (b) { b.setAttribute('aria-selected', b.dataset.day === day ? 'true' : 'false'); });
            panels.forEach(function (p) { p.hidden = p.dataset.dayPanel !== day; });
        }
        function update() {
            var checked = box.querySelector('input[name="consultation"]:checked');
            clear.classList.toggle('hidden', !checked);
            summary.textContent = checked ? @json(__('Избрахте:')) + ' ' + checked.closest('[data-day-panel]').querySelector('[data-day-title]').textContent + ', ' + checked.value.slice(11) : '';
        }
        tabs.addEventListener('click', function (e) { var b = e.target.closest('[data-day]'); if (b) show(b.dataset.day); });
        box.addEventListener('change', update);
        clear.addEventListener('click', function () { var c = box.querySelector('input[name="consultation"]:checked'); if (c) c.checked = false; update(); });
        show(@json($selectedDay));
        update();
    })();
</script>
