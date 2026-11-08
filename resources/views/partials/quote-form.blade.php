{{--
    „Получете оферта“ на /paketi (#oferta), QuoteController::store. Задължителни са само име, бизнес и имейл или телефон;
    останалото е в „По желание“. След изпращане същото място показва благодарността (session quote_sent).
    $plans: site('plans'); $selectedPlan: индекс на пакета от ?paket= или null.
--}}
@php
    $field = 'w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30 placeholder:text-gray-500 dark:placeholder:text-gray-400';
    $label = 'block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200';
    $chosen = old('plan', $selectedPlan ?? 0);
    $mainFields = [
        ['name', __('Име и фамилия'), 'text', 120, 'name', true, ''],
        ['business', __('Име на бизнеса'), 'text', 150, 'organization', true, ''],
        ['email', __('Имейл'), 'email', 255, 'email', false, ''],
        ['phone', __('Телефон'), 'tel', 25, 'tel', false, ''],
    ];
    $extraFields = [
        ['position', __('Длъжност'), 'text', 120, 'organization-title', __('напр. собственик, управител')],
        ['sector', __('С какво се занимавате'), 'text', 120, 'off', __('напр. автосервиз, ресторант, строителство')],
        ['city', __('Град'), 'text', 80, 'address-level2', ''],
        ['site_url', __('Сегашен сайт'), 'text', 255, 'url', __('адрес на сайта, ако имате')],
    ];
    $extraNames = ['position', 'sector', 'city', 'site_url', 'goal', 'timeline'];
    $openExtra = collect($extraNames)->contains(fn ($n) => filled(old($n)) || $errors->has($n));
@endphp
<div id="oferta" class="reveal scroll-mt-28 md:scroll-mt-32 mx-auto max-w-3xl rounded-3xl border border-gray-200 bg-white p-6 md:p-10 shadow-xl shadow-brand-900/5 dark:border-white/10 dark:bg-ink-900 dark:shadow-none">
    @if(session('quote_sent'))
        <div class="text-center py-6" role="status">
            <span class="mx-auto w-14 h-14 rounded-full bg-brand-50 text-brand-700 inline-flex items-center justify-center dark:bg-white/10 dark:text-brand-300"><x-icon name="check" class="text-xl" /></span>
            <h2 class="mt-5 text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Получихме запитването ви.') }}</h2>
            <p class="mt-4 text-lg leading-relaxed text-gray-600 dark:text-gray-300">{{ __('Ще ви изпратим оферта до един работен ден на имейла или телефона, които оставихте.') }}</p>
        </div>
    @else
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Получете оферта') }}</h2>
        <p class="mt-3 leading-relaxed text-gray-600 dark:text-gray-300">{{ __('Оставете име, бизнес и имейл или телефон. Ще ви изпратим оферта за избрания пакет.') }}</p>

        <form method="POST" action="{{ lroute('quote.submit') }}" class="mt-8 space-y-5">
            @csrf
            <div class="hidden" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

            @if(! empty($pricing))
                <input type="hidden" name="volume" value="{{ old('volume', $pricing['steps'][$pricingStep]['value']) }}" data-quote-volume>
                <input type="hidden" name="billing" value="{{ old('billing', $pricingBilling) }}" data-quote-billing>
                <p class="rounded-xl bg-brand-50/70 px-4 py-3 text-sm font-semibold text-brand-950 dark:bg-white/[0.06] dark:text-white" data-quote-summary>{{ __('Избрано от плъзгача') }}: {{ \App\Support\PlanPricing::volumeLabel($pricing, $pricingStep) }}, {{ $pricingBilling === 'yearly' ? __('годишно плащане') : __('месечно плащане') }}</p>
            @endif

            <div>
                <label for="quote-plan" class="{{ $label }}">{{ __('Пакет') }}</label>
                <select id="quote-plan" name="plan" class="{{ $field }}">
                    @foreach($plans['items'] ?? [] as $i => $plan)
                        <option value="{{ $i }}" @selected((string) $chosen === (string) $i)>{{ $plan['name'] }}</option>
                    @endforeach
                </select>
                @error('plan')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                @foreach($mainFields as [$name, $text, $type, $max, $autocomplete, $required, $placeholder])
                    <div>
                        <label for="quote-{{ $name }}" class="{{ $label }}">{{ $text }}</label>
                        <input type="{{ $type }}" id="quote-{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" maxlength="{{ $max }}" autocomplete="{{ $autocomplete }}" @if($required) required @endif class="{{ $field }}">
                        @error($name)<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
            <p class="-mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Достатъчен е имейл или телефон.') }}</p>

            <details class="group rounded-2xl border border-gray-200 dark:border-white/10" @if($openExtra) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4 font-semibold text-brand-950 [&::-webkit-details-marker]:hidden dark:text-white">
                    {{ __('По желание: за по-точна оферта') }} <x-icon name="chevron-down" class="text-xs text-gray-500 transition-transform group-open:rotate-180" />
                </summary>
                <div class="space-y-5 p-4 pt-0">
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach($extraFields as [$name, $text, $type, $max, $autocomplete, $placeholder])
                            <div>
                                <label for="quote-{{ $name }}" class="{{ $label }}">{{ $text }}</label>
                                <input type="{{ $type }}" id="quote-{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" maxlength="{{ $max }}" autocomplete="{{ $autocomplete }}" @if($placeholder) placeholder="{{ $placeholder }}" @endif class="{{ $field }}">
                                @error($name)<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach([['goal', __('Каква е целта?'), site('contact_goals')], ['timeline', __('Кога искате да започнете?'), site('contact_timelines')]] as [$name, $text, $options])
                            <div>
                                <label for="quote-{{ $name }}" class="{{ $label }}">{{ $text }}</label>
                                <select id="quote-{{ $name }}" name="{{ $name }}" class="{{ $field }}">
                                    <option value="">{{ __('Изберете') }}</option>
                                    @foreach($options as $key => $optionLabel)
                                        <option value="{{ $key }}" @selected(old($name) === $key)>{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                                @error($name)<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </details>

            <div>
                <label class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="consent" value="1" required class="mt-1 accent-brand-600" {{ old('consent') ? 'checked' : '' }}>
                    <span>{!! __('Запознат/а съм с :link', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank" class="text-brand-700 underline dark:text-brand-300">' . e(__('политиката за поверителност')) . '</a>']) !!}</span>
                </label>
                @error('consent')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            @include('partials.turnstile')
            <button type="submit" class="btn-shine w-full inline-flex items-center justify-center gap-2 bg-brand-950 text-white py-3.5 px-6 rounded-xl font-semibold uppercase tracking-wide hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                {{ __('Изпратете запитването') }} <x-icon name="arrow-right" class="text-sm" />
            </button>
            <p class="text-center text-sm text-gray-600 dark:text-gray-300">{{ __('Без ангажимент. Отговор до 1 работен ден.') }}</p>
        </form>
    @endif
</div>
