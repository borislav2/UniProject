{{--
    Плъзгачът „колко съдържание ви трябва“ и превключвателят Месечно / Годишно над пакетите (`plans.pricing`).
    Това е GET форма: без JavaScript бутоните и „Покажи цените“ пращат ?obem=<стъпка>&plashtane=..., а с JS цените се сменят на място.
    Всички надписи са готови от App\Support\PlanPricing; скриптът (partials/plan-pricing-script) само ги сменя.
    $plans, $pricing (PlanPricing::config), $pricingStep, $pricingBilling.
--}}
@php
    $steps = $pricing['steps'];
    $count = count($steps);
    $share = fn ($i) => $count > 1 ? round($i / ($count - 1), 4) : 0;
    $unit = $pricing['unit'];
    $savePercent = \App\Support\PlanPricing::savingPercent($pricing);
    $recPlan = \App\Support\PlanPricing::recommended($pricing, $pricingStep, count($plans['items'] ?? []));
    $btnBase = 'relative inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition-colors text-brand-950 hover:text-brand-700 dark:text-gray-200 dark:hover:text-white aria-checked:bg-brand-950 aria-checked:text-white aria-checked:hover:text-white dark:aria-checked:bg-white dark:aria-checked:text-brand-950 dark:aria-checked:hover:text-brand-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500';
@endphp
<form method="GET" action="{{ lroute('packages') }}#izbor" id="izbor" data-plan-pricing data-billing="{{ $pricingBilling }}" class="scroll-mt-28 mb-12 md:mb-16 rounded-[2rem] border border-gray-200 bg-brand-50/60 p-6 md:p-10 dark:border-white/10 dark:bg-white/[0.04]">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-2xl">
            <h2 class="text-2xl md:text-3xl font-extrabold leading-tight tracking-tight text-brand-950 text-balance dark:text-white">{{ $pricing['title'] }}</h2>
            @if(filled($pricing['hint'] ?? null))
                <p class="mt-3 leading-relaxed text-gray-600 dark:text-gray-300">{{ $pricing['hint'] }}</p>
            @endif
        </div>

        <div class="shrink-0">
            <div role="radiogroup" aria-label="{{ __('Начин на плащане') }}" class="inline-flex rounded-2xl bg-white p-1 ring-1 ring-gray-200 dark:bg-white/[0.06] dark:ring-white/15" data-pp-billing-group>
                <button type="submit" name="plashtane" value="{{ \App\Support\PlanPricing::billingParam('monthly') }}" role="radio" aria-checked="{{ $pricingBilling === 'monthly' ? 'true' : 'false' }}" tabindex="{{ $pricingBilling === 'monthly' ? '0' : '-1' }}" data-pp-billing="monthly" class="{{ $btnBase }}">{{ __('Месечно') }}</button>
                <button type="submit" name="plashtane" value="{{ \App\Support\PlanPricing::billingParam('yearly') }}" role="radio" aria-checked="{{ $pricingBilling === 'yearly' ? 'true' : 'false' }}" tabindex="{{ $pricingBilling === 'yearly' ? '0' : '-1' }}" data-pp-billing="yearly" class="{{ $btnBase }}">
                    {{ __('Годишно') }}
                    @if($savePercent > 0)<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[0.7rem] font-extrabold leading-none text-emerald-800 dark:bg-emerald-400/20 dark:text-emerald-200">&minus;{{ $savePercent }}%</span>@endif
                </button>
            </div>
            @if(filled($pricing['yearly_label'] ?? null))
                <p class="mt-2 text-sm font-semibold text-brand-700 dark:text-brand-300">{{ $pricing['yearly_label'] }}</p>
            @endif
        </div>
    </div>

    {{-- Голямата стойност на избраната стъпка. --}}
    <div class="mt-8 flex flex-wrap items-end gap-x-4 gap-y-1" aria-hidden="true">
        <span data-pp-value class="text-6xl md:text-7xl font-extrabold leading-none tracking-tight text-gradient tabular-nums">{{ $steps[$pricingStep]['value'] }}</span>
        <span class="pb-1 text-lg md:text-xl font-semibold text-brand-950 dark:text-white">{{ $unit }}</span>
    </div>

    <div class="mt-5">
        <label for="plan-range" class="sr-only">{{ $pricing['title'] }}</label>
        <input id="plan-range" name="obem" type="range" min="0" max="{{ $count - 1 }}" step="1" value="{{ $pricingStep }}" class="plan-range" style="--p: {{ $share($pricingStep) }}" aria-valuetext="{{ \App\Support\PlanPricing::volumeLabel($pricing, $pricingStep) }}" data-pp-range>
        <div class="relative h-9" aria-hidden="true">
            @foreach($steps as $i => $step)
                <button type="button" tabindex="-1" data-pp-tick="{{ $i }}" @if($i === $pricingStep) aria-current="true" @endif class="absolute top-0 -translate-x-1/2 px-1.5 py-1.5 text-sm font-bold tabular-nums text-gray-500 hover:text-brand-700 aria-[current=true]:text-brand-700 dark:text-gray-400 dark:hover:text-white dark:aria-[current=true]:text-white" style="left: calc({{ $share($i) }} * (100% - 2rem) + 1rem)">{{ $step['value'] }}</button>
            @endforeach
        </div>
    </div>

    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ __('Препоръчваме') }}:
            <a href="#paket-{{ $recPlan }}" data-pp-rec-link class="font-bold text-brand-700 underline decoration-brand-300 underline-offset-4 hover:text-brand-950 dark:text-brand-300 dark:hover:text-white">{{ ($plans['items'][$recPlan]['name'] ?? '') }}</a>
        </p>
        <noscript><button type="submit" class="rounded-xl bg-brand-950 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-brand-950">{{ __('Покажи цените') }}</button></noscript>
    </div>

    @if(filled($pricing['note'] ?? null))
        <p class="mt-4 border-t border-gray-200 pt-4 text-sm leading-relaxed text-gray-600 dark:border-white/10 dark:text-gray-300">{{ $pricing['note'] }}</p>
    @endif
</form>

@php
    $payload = \App\Support\PlanPricing::payload($pricing, $plans);
    $payload['labels'] = ['summary' => __('Избрано от плъзгача'), 'monthly' => __('месечно плащане'), 'yearly' => __('годишно плащане')];
    $payload['names'] = array_map(fn ($plan) => (string) ($plan['name'] ?? ''), array_values($plans['items'] ?? []));
@endphp
<script type="application/json" id="plan-pricing-data">{!! json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
