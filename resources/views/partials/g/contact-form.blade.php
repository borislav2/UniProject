{{--
    The contact form in design G (same fields and route as partials/contact-form): visible labels with hints, interests as square chips,
    the booking in a collapsed block, the promise right above a big button and a clear thank-you state after sending.
--}}
@php
    $interests = site('contact_interests') ?? [];
    $oldInterests = (array) old('interests', in_array(request('interest'), array_keys($interests), true) ? [request('interest')] : []);
    $bookingOpen = filled(old('consultation')) || $errors->has('consultation');
    $firstPhone = g_phones()[0] ?? null;
    $budgets = site('contact_budgets') ?? [];
    $oldBudget = old('budget');
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
            <div class="g-trap" aria-hidden="true">
                <label for="website">{{ __('Не попълвайте това поле') }}</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <p class="g-hint g-req">{{ __('Полетата със звезда са задължителни.') }}</p>

            <div class="g-fgrid">
                <div class="g-fld">
                    <label for="name">{{ __('Име и фамилия') }} <b aria-hidden="true">*</b></label>
                    <input class="g-in" type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name">
                    <p class="g-hint">{{ __('Как да се обръщаме към вас.') }}</p>
                    @error('name')<p class="g-err">{{ $message }}</p>@enderror
                </div>
                <div class="g-fld">
                    <label for="site_url">{{ __('Уебсайт') }}</label>
                    <input class="g-in" type="text" id="site_url" name="site_url" value="{{ old('site_url') }}" maxlength="255" inputmode="url" autocomplete="url" placeholder="{{ __('вашсайт.bg') }}">
                    <p class="g-hint">{{ __('Ако имате сайт, ще го прегледаме преди разговора.') }}</p>
                    @error('site_url')<p class="g-err">{{ $message }}</p>@enderror
                </div>
                <div class="g-fld">
                    <label for="email">{{ __('Имейл') }} <b aria-hidden="true">*</b></label>
                    <input class="g-in" type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="{{ __('ime@firma.bg') }}">
                    <p class="g-hint">{{ __('Тук ще получите отговора.') }}</p>
                    @error('email')<p class="g-err">{{ $message }}</p>@enderror
                </div>
                <div class="g-fld">
                    <label for="phone">{{ __('Телефон') }} <b aria-hidden="true">*</b></label>
                    <input class="g-in" type="tel" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="{{ __('08X XXX XXXX') }}">
                    <p class="g-hint">{{ __('За да можем да ви се обадим.') }}</p>
                    @error('phone')<p class="g-err">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($interests)
                <fieldset class="g-fld g-interests">
                    <legend>{{ __('От какво се интересувате') }}</legend>
                    <p class="g-hint">{{ __('Отметнете каквото искате, може няколко.') }}</p>
                    <div class="g-chips">
                        @foreach($interests as $key => $interest)
                            <label class="g-chip-opt"><input type="checkbox" name="interests[]" value="{{ $key }}" @checked(in_array($key, $oldInterests, true))><span>{{ $interest }}</span></label>
                        @endforeach
                    </div>
                    @error('interests.*')<p class="g-err">{{ $message }}</p>@enderror
                </fieldset>
            @endif

            @if($budgets)
                <div class="g-fld">
                    <label for="budget">{{ __('Месечен маркетингов бюджет') }}</label>
                    <select class="g-in" id="budget" name="budget">
                        <option value="">{{ __('Не съм сигурен') }}</option>
                        @foreach($budgets as $key => $label)
                            <option value="{{ $key }}"{{ $oldBudget === (string) $key ? ' selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="g-hint">{{ __('По желание. Помага ни да предложим подходящ план.') }}</p>
                    @error('budget')<p class="g-err">{{ $message }}</p>@enderror
                </div>
            @endif

            <div class="g-fld">
                <label for="message">{{ __('Разкажете ни накратко за бизнеса си и какво търсите') }}</label>
                <textarea class="g-in" id="message" name="message" rows="4" maxlength="5000">{{ old('message') }}</textarea>
                @error('message')<p class="g-err">{{ $message }}</p>@enderror
            </div>

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
            <button type="submit" class="g-btn g-big">{{ __('Изпратете') }}<i></i></button>
        </form>
    @endif
</div>
<script>
    (function () {
        var form = document.querySelector('#g-contact-form [data-contact-form]');
        var box = document.getElementById('g-contact-form');
        if (!form) return;
        var details = form.querySelector('[data-booking-details]');

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
    <script>document.getElementById('g-contact-form').scrollIntoView({ block: 'center' });</script>
@endif
