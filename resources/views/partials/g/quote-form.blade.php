{{-- „Изпратете запитване“ (the quote form) in design G (same fields and route as partials/quote-form). $plans, $selectedPlan. --}}
@php
    $chosen = old('plan', $selectedPlan ?? 0);
    $mainFields = [
        ['name', __('Име и фамилия'), 'text', 120, 'name', true],
        ['business', __('Име на бизнеса'), 'text', 150, 'organization', true],
        ['email', __('Имейл'), 'email', 255, 'email', false],
        ['phone', __('Телефон'), 'tel', 25, 'tel', false],
    ];
    $extraFields = [
        ['position', __('Длъжност'), 'text', 120, 'organization-title', __('напр. собственик, управител')],
        ['sector', __('С какво се занимавате'), 'text', 120, 'off', __('напр. автосервиз, ресторант, строителство')],
        ['city', __('Град'), 'text', 80, 'address-level2', ''],
        ['site_url', __('Сегашен сайт'), 'text', 255, 'url', __('адрес на сайта, ако имате')],
    ];
    $openExtra = collect(['position', 'sector', 'city', 'site_url', 'goal', 'timeline'])->contains(fn ($n) => filled(old($n)) || $errors->has($n));
@endphp
<div id="oferta" class="g-qf g-fbox">
    @if(session('quote_sent'))
        <div class="g-thanks" role="status">
            <p class="g-mono">{{ __('Изпратено') }}</p>
            <h2 class="g-h">{{ __('Получихме запитването ви.') }}</h2>
            <p>{{ __('Ще ви изпратим оферта до един работен ден на имейла или телефона, които оставихте.') }}</p>
            <a class="g-btn g-ghost" href="{{ lroute('home') }}">{{ __('Към началото') }}</a>
        </div>
    @else
        <h2 class="g-h g-fh">{{ __('Изпратете запитване') }}</h2>
        <p class="g-fintro">{{ __('Оставете име, бизнес и имейл или телефон. Ще ви изпратим оферта за избрания пакет.') }}</p>
        <form method="POST" action="{{ lroute('quote.submit') }}" class="g-f">
            @csrf
            <div class="g-trap" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

            <div class="g-fld">
                <label for="quote-plan">{{ __('Пакет') }}</label>
                <select id="quote-plan" name="plan" class="g-in">
                    @php($quotePlans = \App\Support\GPlans::cards($plans) ?: collect($plans['items'] ?? [])->map(fn ($plan, $i) => ['index' => $i, 'name' => $plan['name']])->all())
                    @foreach($quotePlans as $plan)
                        <option value="{{ $plan['index'] }}" @selected((string) $chosen === (string) $plan['index'])>{{ $plan['name'] }}</option>
                    @endforeach
                </select>
                @error('plan')<p class="g-err">{{ $message }}</p>@enderror
            </div>

            <div class="g-fgrid">
                @foreach($mainFields as [$name, $text, $type, $max, $autocomplete, $required])
                    <div class="g-fld">
                        <label for="quote-{{ $name }}">{{ $text }}@if($required) <b aria-hidden="true">*</b>@endif</label>
                        <input class="g-in" type="{{ $type }}" id="quote-{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" maxlength="{{ $max }}" autocomplete="{{ $autocomplete }}" @if($required) required @endif>
                        @error($name)<p class="g-err">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
            <p class="g-hint">{{ __('Достатъчен е имейл или телефон.') }}</p>

            <details class="g-more-fields" @if($openExtra) open @endif>
                <summary><span>{{ __('По желание, за по-точна оферта') }}</span><i aria-hidden="true"></i></summary>
                <div class="g-fgrid">
                    @foreach($extraFields as [$name, $text, $type, $max, $autocomplete, $placeholder])
                        <div class="g-fld">
                            <label for="quote-{{ $name }}">{{ $text }}</label>
                            <input class="g-in" type="{{ $type }}" id="quote-{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" maxlength="{{ $max }}" autocomplete="{{ $autocomplete }}" @if($placeholder) placeholder="{{ $placeholder }}" @endif>
                            @error($name)<p class="g-err">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    @foreach([['goal', __('Каква е целта?'), site('contact_goals')], ['timeline', __('Кога искате да започнете?'), site('contact_timelines')]] as [$name, $text, $options])
                        <div class="g-fld">
                            <label for="quote-{{ $name }}">{{ $text }}</label>
                            <select id="quote-{{ $name }}" name="{{ $name }}" class="g-in">
                                <option value="">{{ __('Изберете') }}</option>
                                @foreach($options as $key => $optionLabel)
                                    <option value="{{ $key }}" @selected(old($name) === $key)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                            @error($name)<p class="g-err">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </details>

            <div class="g-fld">
                <label class="g-chk">
                    <input type="checkbox" name="consent" value="1" required {{ old('consent') ? 'checked' : '' }}>
                    <span>{!! __('Запознат/а съм с :link', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank">' . e(__('политиката за поверителност')) . '</a>']) !!}</span>
                </label>
                @error('consent')<p class="g-err">{{ $message }}</p>@enderror
            </div>

            @include('partials.turnstile')
            <p class="g-promise">{{ __('Без ангажимент. Отговор до 1 работен ден.') }}</p>
            <button type="submit" class="g-btn g-big">{{ __('Изпратете запитването') }}<i></i></button>
        </form>
    @endif
</div>
