{{--
    Превключвателят Месечно / Годишно над пакетите (`plans.billing`): при годишно плащане 2 месеца от абонамента са безплатни.
    Това е GET форма: без JavaScript бутоните пращат ?plashtane=godishno и страницата се зарежда с другия вариант; с JS двата варианта
    на всяка карта (data-billing-view) се показват и скриват на място. Низовете са готови от App\Support\PlanBilling.
    $plans, $billing (PlanBilling::config), $billingMode.
--}}
@php
    $savePercent = \App\Support\PlanBilling::savingPercent($billing);
    $btnBase = 'relative inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition-colors text-brand-950 hover:text-brand-700 dark:text-gray-200 dark:hover:text-white aria-checked:bg-brand-950 aria-checked:text-white aria-checked:hover:text-white dark:aria-checked:bg-white dark:aria-checked:text-brand-950 dark:aria-checked:hover:text-brand-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500';
@endphp
<form method="GET" action="{{ lroute('packages') }}#paketi" id="paketi" data-plan-billing data-billing="{{ $billingMode }}" class="scroll-mt-28 mb-10 md:mb-14 flex flex-col items-center gap-3 text-center">
    <div role="radiogroup" aria-label="{{ __('Начин на плащане') }}" class="inline-flex rounded-2xl bg-brand-50/70 p-1 ring-1 ring-gray-200 dark:bg-white/[0.06] dark:ring-white/15">
        <button type="submit" name="plashtane" value="{{ \App\Support\PlanBilling::billingParam('monthly') }}" role="radio" aria-checked="{{ $billingMode === 'monthly' ? 'true' : 'false' }}" tabindex="{{ $billingMode === 'monthly' ? '0' : '-1' }}" data-pb-mode="monthly" class="{{ $btnBase }}">{{ __('Месечно') }}</button>
        <button type="submit" name="plashtane" value="{{ \App\Support\PlanBilling::billingParam('yearly') }}" role="radio" aria-checked="{{ $billingMode === 'yearly' ? 'true' : 'false' }}" tabindex="{{ $billingMode === 'yearly' ? '0' : '-1' }}" data-pb-mode="yearly" class="{{ $btnBase }}">
            {{ __('Годишно') }}
            @if($savePercent > 0)<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[0.7rem] font-extrabold leading-none text-emerald-800 dark:bg-emerald-400/20 dark:text-emerald-200">&minus;{{ $savePercent }}%</span>@endif
        </button>
    </div>
    @if(filled($billing['yearly_label'] ?? null))
        <p class="text-sm font-semibold text-brand-700 dark:text-brand-300">{{ $billing['yearly_label'] }}</p>
    @endif
    @if(filled($billing['note'] ?? null))
        <p class="max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $billing['note'] }}</p>
    @endif
</form>

{{-- Превключвателят: показва единия от двата варианта на цените, подрежда адреса и скритото поле на формата за оферта. --}}
<script>
    (function () {
        var form = document.querySelector('[data-plan-billing]');
        if (!form) { return; }
        var buttons = form.querySelectorAll('[data-pb-mode]');
        var params = { monthly: 'mesechno', yearly: 'godishno' };
        var mode = form.getAttribute('data-billing') === 'yearly' ? 'yearly' : 'monthly';

        function render(updateUrl) {
            document.querySelectorAll('[data-billing-view]').forEach(function (el) {
                el.hidden = el.getAttribute('data-billing-view') !== mode;
            });
            buttons.forEach(function (btn) {
                var on = btn.getAttribute('data-pb-mode') === mode;
                btn.setAttribute('aria-checked', on ? 'true' : 'false');
                btn.tabIndex = on ? 0 : -1;
            });
            var field = document.querySelector('[data-quote-billing]');
            if (field) { field.value = mode; }
            var tail = mode === 'yearly' ? '&plashtane=' + params.yearly : '';
            document.querySelectorAll('[data-p-link]').forEach(function (link) {
                link.href = link.href.split('#')[0].replace(/[?&]plashtane=[^&#]*/g, '') + tail + '#oferta';
            });
            if (updateUrl) {
                try {
                    var url = new URL(window.location.href);
                    url.searchParams.set('plashtane', params[mode]);
                    window.history.replaceState(null, '', url.toString());
                } catch (e) { /* the page works without the address update */ }
            }
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                mode = btn.getAttribute('data-pb-mode') === 'yearly' ? 'yearly' : 'monthly';
                render(true);
            });
            // Radio group: the arrow keys move between the two options.
            btn.addEventListener('keydown', function (event) {
                if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].indexOf(event.key) === -1) { return; }
                event.preventDefault();
                mode = mode === 'yearly' ? 'monthly' : 'yearly';
                render(true);
                var next = form.querySelector('[data-pb-mode="' + mode + '"]');
                if (next) { next.focus(); }
            });
        });
        form.addEventListener('submit', function (event) { event.preventDefault(); });
        render(false);
    })();
</script>
