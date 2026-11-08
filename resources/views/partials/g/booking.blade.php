{{-- Day and time for a call in design G (same fields as partials/booking: consultation radios, #konsultatsiya). Without JavaScript every day is listed. --}}
@php
    $bookingDays = \App\Support\Booking::days();
    $selectedSlot = old('consultation');
    $selectedDay = $selectedSlot ? substr($selectedSlot, 0, 10) : $bookingDays[0]['date']->format('Y-m-d');
@endphp
<fieldset id="konsultatsiya" class="g-book" data-booking>
    <legend class="g-mono">{{ __('Ден и час за разговор') }} <span>{{ __('(по избор)') }}</span></legend>
    <p class="g-hint">{{ __('Изберете удобен час и ще ви се обадим тогава. Разговорът е около :minutes минути.', ['minutes' => config('creatium.booking.minutes')]) }}</p>

    <div class="g-days" data-booking-days role="tablist" aria-label="{{ __('Ден') }}" hidden>
        @foreach($bookingDays as $day)
            @php($key = $day['date']->format('Y-m-d'))
            <button type="button" data-day="{{ $key }}" role="tab" aria-selected="{{ $key === $selectedDay ? 'true' : 'false' }}">
                <span class="g-mono">{{ $day['date']->locale(app()->getLocale())->isoFormat('dd') }}</span>
                <b>{{ $day['date']->format('j') }}</b>
                <span class="g-mono">{{ $day['date']->locale(app()->getLocale())->isoFormat('MMM') }}</span>
            </button>
        @endforeach
    </div>

    @foreach($bookingDays as $day)
        @php($key = $day['date']->format('Y-m-d'))
        <div class="g-slots-wrap" data-day-panel="{{ $key }}">
            <p class="g-slot-title" data-day-title>{{ ucfirst($day['date']->locale(app()->getLocale())->isoFormat('dddd, D MMMM')) }}</p>
            <div class="g-slots">
                @foreach($day['slots'] as $slot)
                    <label>
                        <input type="radio" name="consultation" value="{{ $slot['value'] }}" @checked($selectedSlot === $slot['value']) @disabled($slot['taken'])>
                        <span>{{ $slot['time'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="g-book-foot">
        <p data-booking-summary aria-live="polite"></p>
        <button type="button" class="g-textbtn" data-booking-clear hidden>{{ __('Без час') }}</button>
    </div>
    @error('consultation')<p class="g-err">{{ $message }}</p>@enderror
</fieldset>
<script>
    (function () {
        var box = document.currentScript.previousElementSibling;
        var tabs = box.querySelector('[data-booking-days]'), panels = box.querySelectorAll('[data-day-panel]');
        var summary = box.querySelector('[data-booking-summary]'), clear = box.querySelector('[data-booking-clear]');
        tabs.hidden = false;
        box.querySelectorAll('[data-day-title]').forEach(function (t) { t.classList.add('g-sr'); });
        function show(day) {
            tabs.querySelectorAll('[data-day]').forEach(function (b) { b.setAttribute('aria-selected', b.dataset.day === day ? 'true' : 'false'); });
            panels.forEach(function (p) { p.hidden = p.dataset.dayPanel !== day; });
        }
        function update() {
            var checked = box.querySelector('input[name="consultation"]:checked');
            clear.hidden = !checked;
            summary.textContent = checked ? @json(__('Избрахте')) + ' ' + checked.closest('[data-day-panel]').querySelector('[data-day-title]').textContent + ', ' + checked.value.slice(11) : '';
        }
        tabs.addEventListener('click', function (e) { var b = e.target.closest('[data-day]'); if (b) show(b.dataset.day); });
        box.addEventListener('change', update);
        clear.addEventListener('click', function () { var c = box.querySelector('input[name="consultation"]:checked'); if (c) c.checked = false; update(); });
        show(@json($selectedDay));
        update();
    })();
</script>
