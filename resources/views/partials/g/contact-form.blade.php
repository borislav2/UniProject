{{--
    THE contact form of design G (third round, 16.11.2026: one form everywhere, on the home page, /kontakti and /paketi). It posts to the contact route with a hidden form=g.
    Two steps: 1. the basic data, 2. a package or separate directions plus an optional budget; the booking stays in a collapsed block.
    Preselected by ?paket=N (a package, the index in plans.g_items) and ?napravleniya=google,seo (directions); #service-<key> ticks a direction too.
--}}
@php
    $gPlans = \App\Support\GPlans::cards(app(\App\Support\Content::class)->withDefaults('plans'));
    $directions = (array) site('contact_directions_g');
    $budgets = (array) site('contact_budgets_g');
    $fromUrlPlans = request()->filled('paket') && collect($gPlans)->contains(fn ($card) => (string) $card['index'] === (string) request('paket')) ? [(string) request('paket')] : [];
    $fromUrlDirections = array_values(array_intersect(array_keys($directions), array_filter(explode(',', (string) request('napravleniya')))));
    $oldPlans = array_map('strval', (array) old('g_plans', $fromUrlPlans));
    $oldDirections = (array) old('g_directions', $fromUrlDirections);
    $oldBudget = old('budget', array_key_first($budgets));
    $bookingOpen = filled(old('consultation')) || $errors->has('consultation');
    $firstPhone = g_phones()[0] ?? null;
@endphp
<span id="contact-form"></span>
<div id="g-contact-form" class="g-fbox">
    @if(session('success'))
        <div class="g-thanks" role="status" tabindex="-1">
            <p class="g-mono">{{ __('Изпратено') }}</p>
            <h2 class="g-h">{{ __('Благодарим!') }}</h2>
            <p>{{ session('success') }}</p>
            <ol class="g-next">
                <li><b>{{ __('Преглеждаме запитването') }}</b><span>{{ __('Прочитаме какво търсите и подготвяме въпросите си.') }}</span></li>
                <li><b>{{ __('Отговаряме до един работен ден') }}</b><span>{{ __('По имейл или телефон, както сте оставили.') }}</span></li>
                <li><b>{{ __('Уговаряме разговор') }}</b><span>{{ __('Предлагаме следващата стъпка и ясна оферта.') }}</span></li>
            </ol>
            @if($firstPhone)
                <p class="g-hint">{{ __('Бързате ли? Обадете се на') }} <a href="{{ \App\Support\Phone::tel($firstPhone) }}">{{ $firstPhone }}</a>.</p>
            @endif
        </div>
    @else
        <form action="{{ lroute('contact.submit') }}" method="POST" novalidate class="g-f" data-contact-form>
            @csrf
            <input type="hidden" name="form" value="g">
            <div class="g-trap" aria-hidden="true">
                <label for="website">{{ __('Не попълвайте това поле') }}</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <p class="g-hint g-req">{{ __('Полетата със звезда са задължителни.') }}</p>

            <section class="g-fstep" aria-labelledby="gf-step-1">
                <{{ $stepTag ?? 'h3' }} class="g-fstep-h" id="gf-step-1"><span class="g-mono">1.</span> {{ __('Основни данни') }}</{{ $stepTag ?? 'h3' }}>
                <div class="g-fgrid">
                    <div class="g-fld">
                        <label for="name">{{ __('Име и фамилия') }} <b aria-hidden="true">*</b></label>
                        <input class="g-in" type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name">
                        @error('name')<p class="g-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="g-fld">
                        <label for="business_site">{{ __('Име на бизнеса или уебсайт') }}</label>
                        <input class="g-in" type="text" id="business_site" name="business_site" value="{{ old('business_site') }}" maxlength="255" autocomplete="organization">
                        <p class="g-hint">{{ __('Ако имате сайт, ще го прегледаме предварително, за да сме максимално полезни в разговора.') }}</p>
                        @error('business_site')<p class="g-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="g-fld">
                        <label for="email">{{ __('Имейл') }} <b aria-hidden="true">*</b></label>
                        <input class="g-in" type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="{{ __('ime@firma.bg') }}">
                        @error('email')<p class="g-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="g-fld">
                        <label for="phone">{{ __('Телефон') }}</label>
                        <input class="g-in" type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="{{ __('08X XXX XXXX') }}">
                        <p class="g-hint">{{ __('Оставете номер, ако предпочитате да ви се обадим или да се свържем с вас по Viber или WhatsApp.') }}</p>
                        @error('phone')<p class="g-err">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="g-fstep" aria-labelledby="gf-step-2">
                <{{ $stepTag ?? 'h3' }} class="g-fstep-h" id="gf-step-2"><span class="g-mono">2.</span> {{ __('Избор на посока или пакет') }}</{{ $stepTag ?? 'h3' }}>
                <p class="g-fq">{{ __('Кой вариант е най-близо до нуждите ви?') }}</p>
                <p class="g-hint g-fq-hint">{{ __('Можете да изберете пакет или отделни направления.') }}</p>

                @if($gPlans)
                    <fieldset class="g-fld g-opts">
                        <legend>{{ __('Готови пакети') }}</legend>
                        <div class="g-chips">
                            @foreach($gPlans as $card)
                                <label class="g-chip-opt"><input type="checkbox" name="g_plans[]" value="{{ $card['index'] }}" @checked(in_array((string) $card['index'], $oldPlans, true))><span>@if($card['tag'] !== '')<b class="g-ptag g-mono">{{ $card['tag'] }}</b> @endif{{ $card['name'] }}@if(\App\Support\GPlans::priceLine($card) !== '') <small>({{ \App\Support\GPlans::priceLine($card) }})</small>@endif</span></label>
                            @endforeach
                        </div>
                        @error('g_plans.*')<p class="g-err">{{ $message }}</p>@enderror
                    </fieldset>
                @endif

                @if($directions)
                    <fieldset class="g-fld g-opts">
                        <legend>{{ __('Или конкретни направления') }}</legend>
                        <div class="g-chips">
                            @foreach($directions as $key => $direction)
                                <label class="g-chip-opt"><input type="checkbox" name="g_directions[]" value="{{ $key }}" @checked(in_array($key, $oldDirections, true))><span>{{ $direction }}</span></label>
                            @endforeach
                        </div>
                        @error('g_directions.*')<p class="g-err">{{ $message }}</p>@enderror
                    </fieldset>
                @endif

                @if($budgets)
                    <div class="g-fld">
                        <label for="budget">{{ __('Ориентировъчен месечен бюджет') }}</label>
                        <select class="g-in" id="budget" name="budget">
                            @foreach($budgets as $key => $label)
                                <option value="{{ $key }}"{{ (string) $oldBudget === (string) $key ? ' selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('budget')<p class="g-err">{{ $message }}</p>@enderror
                    </div>
                @endif
            </section>

            <details class="g-more-fields" data-booking-details{{ $bookingOpen ? ' open' : '' }}>
                <summary><span>{{ __('Искате ли да запазите час за разговор? (по желание)') }}</span><i aria-hidden="true"></i></summary>
                @include('partials.g.booking')
            </details>

            <div class="g-fld">
                <label class="g-chk">
                    <input type="checkbox" name="consent" value="1" required {{ old('consent') ? 'checked' : '' }}>
                    <span>{!! __('Запознат/а съм с :link.', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank">' . e(__('Политиката за поверителност')) . '</a>']) !!}</span>
                </label>
                @error('consent')<p class="g-err">{{ $message }}</p>@enderror
            </div>
            @include('partials.turnstile')
            <button type="submit" class="g-btn g-big">{{ __('Изпратете') }}</button>
        </form>
    @endif
</div>
<script>
    (function () {
        var form = document.querySelector('#g-contact-form [data-contact-form]');
        var box = document.getElementById('g-contact-form');
        if (!form) return;
        var details = form.querySelector('[data-booking-details]');

        // #konsultatsiya opens the booking; #service-<key> ticks that direction. The form is shown from its top.
        function fromHash() {
            var m = location.hash.match(/^#service-([a-z-]+)$/);
            if (m) {
                var c = form.querySelector('input[name="g_directions[]"][value="' + m[1] + '"]');
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
    <script>document.getElementById('g-contact-form').scrollIntoView({ block: 'center' });</script>
@endif
