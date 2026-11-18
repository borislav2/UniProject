{{--
    The short form on the right of the website service page (brief of the owners, 18.11.2026). It posts to the contact route with form=g (the same validation, honeypot, consent,
    Turnstile and lead as the main G form) and a hidden direction; the optional short description is written into the lead description. $page = the service page.
--}}
@php
    $f = (array) ($page['g_form'] ?? []);
    $direction = $page['g_direction'] ?? 'website';
@endphp
<div id="zapitvane" class="g-fbox g-svf">
    @if(session('success'))
        <div class="g-thanks" role="status" tabindex="-1" data-svf-thanks>
            <p class="g-mono">{{ __('Изпратено') }}</p>
            <h2 class="g-h">{{ __('Благодарим!') }}</h2>
            <p>{{ session('success') }}</p>
        </div>
        <script>document.addEventListener('DOMContentLoaded', function () { var t = document.querySelector('[data-svf-thanks]'); if (t) { t.scrollIntoView({ block: 'center' }); t.focus({ preventScroll: true }); } });</script>
    @else
        <form action="{{ lroute('contact.submit') }}" method="POST" novalidate class="g-f" data-svc-form>
            @csrf
            <input type="hidden" name="form" value="g">
            <input type="hidden" name="g_directions[]" value="{{ $direction }}">
            <div class="g-trap" aria-hidden="true">
                <label for="svf-website">{{ __('Не попълвайте това поле') }}</label>
                <input type="text" id="svf-website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="g-fld">
                <label for="svf-name">{{ $f['name'] ?? __('Име и фамилия') }} <b aria-hidden="true">*</b></label>
                <input class="g-in" type="text" id="svf-name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name"@error('name') aria-invalid="true" aria-describedby="svf-err-name"@enderror>
                @error('name')<p class="g-err" id="svf-err-name">{{ $message }}</p>@enderror
            </div>
            <div class="g-fld">
                <label for="svf-email">{{ $f['email'] ?? __('Имейл') }} <b aria-hidden="true">*</b></label>
                <input class="g-in" type="email" id="svf-email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"@error('email') aria-invalid="true" aria-describedby="svf-err-email"@enderror>
                @error('email')<p class="g-err" id="svf-err-email">{{ $message }}</p>@enderror
            </div>
            <div class="g-svf-row">
                <div class="g-fld">
                    <label for="svf-phone">{{ $f['phone'] ?? __('Телефон') }}</label>
                    <input class="g-in" type="tel" id="svf-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel"@error('phone') aria-invalid="true" aria-describedby="svf-err-phone"@enderror>
                    @error('phone')<p class="g-err" id="svf-err-phone">{{ $message }}</p>@enderror
                </div>
                <div class="g-fld">
                    <label for="svf-site">{{ $f['site'] ?? __('Име на бизнеса или уебсайт') }}</label>
                    <input class="g-in" type="text" id="svf-site" name="business_site" value="{{ old('business_site') }}" maxlength="255" autocomplete="url"@error('business_site') aria-invalid="true" aria-describedby="svf-err-site"@enderror>
                    @error('business_site')<p class="g-err" id="svf-err-site">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="g-fld">
                <label for="svf-message">{{ $f['message'] ?? '' }}</label>
                <textarea class="g-in" id="svf-message" name="message" rows="3" maxlength="2000"@error('message') aria-invalid="true" aria-describedby="svf-err-message"@enderror>{{ old('message') }}</textarea>
                @error('message')<p class="g-err" id="svf-err-message">{{ $message }}</p>@enderror
            </div>
            <div class="g-fld">
                <label class="g-chk">
                    <input type="checkbox" name="consent" value="1" required {{ old('consent') ? 'checked' : '' }}@error('consent') aria-invalid="true" aria-describedby="svf-err-consent"@enderror>
                    <span>{!! __('Запознат/а съм с :link.', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank">' . e(__('Политиката за поверителност')) . '</a>']) !!}</span>
                </label>
                @error('consent')<p class="g-err" id="svf-err-consent">{{ $message }}</p>@enderror
            </div>
            @include('partials.turnstile')
            <button type="submit" class="g-btn g-big">{{ $page['g_button'] }}<i></i></button>
        </form>
        @include('partials.g.form-feedback', ['box' => '#zapitvane'])
    @endif
</div>
