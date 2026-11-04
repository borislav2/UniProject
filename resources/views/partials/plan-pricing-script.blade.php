{{-- Плъзгачът на /paketi: сменя готовите низове от PlanPricing::payload() (никакви сметки в браузъра) и пази адреса с history.replaceState. --}}
<script>
    (function () {
        var form = document.querySelector('[data-plan-pricing]');
        var dataEl = document.getElementById('plan-pricing-data');
        if (!form || !dataEl) { return; }
        var data;
        try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }

        var range = form.querySelector('[data-pp-range]');
        var valueEl = form.querySelector('[data-pp-value]');
        var ticks = form.querySelectorAll('[data-pp-tick]');
        var billingBtns = form.querySelectorAll('[data-pp-billing]');
        var recLink = form.querySelector('[data-pp-rec-link]');
        var cards = document.querySelectorAll('[data-plan-card]');
        var count = data.steps.length;
        var state = { step: parseInt(range.value, 10) || 0, billing: form.getAttribute('data-billing') === 'yearly' ? 'yearly' : 'monthly' };

        function text(root, selector, value) {
            var el = root.querySelector(selector);
            if (el) { el.textContent = value; }
            return el;
        }
        function show(el, visible) {
            if (el) { el.hidden = !visible; }
        }

        function render(updateUrl) {
            var step = data.steps[state.step];
            var share = count > 1 ? state.step / (count - 1) : 0;

            range.value = state.step;
            range.style.setProperty('--p', share);
            range.setAttribute('aria-valuetext', step.label);
            if (valueEl) { valueEl.textContent = step.value; }
            ticks.forEach(function (tick) {
                if (parseInt(tick.getAttribute('data-pp-tick'), 10) === state.step) { tick.setAttribute('aria-current', 'true'); }
                else { tick.removeAttribute('aria-current'); }
            });
            billingBtns.forEach(function (btn) {
                var on = btn.getAttribute('data-pp-billing') === state.billing;
                btn.setAttribute('aria-checked', on ? 'true' : 'false');
                btn.tabIndex = on ? 0 : -1;
            });

            var qs = '&obem=' + state.step + '&plashtane=' + data.params[state.billing];
            cards.forEach(function (card) {
                var i = parseInt(card.getAttribute('data-plan-card'), 10);
                var cell = step.plans[i] && step.plans[i][state.billing];
                if (!cell) { return; }
                var rec = i === step.recommended;
                card.setAttribute('data-rec', rec ? 'true' : 'false');
                var badge = card.querySelector('[data-p-badge]');
                if (badge) { badge.setAttribute('aria-hidden', rec ? 'false' : 'true'); }

                text(card, '[data-p-volume]', cell.volume);
                var numbers = card.querySelector('[data-p-numbers]');
                if (numbers) {
                    show(numbers, !cell.custom);
                    show(card.querySelector('[data-p-custom]'), cell.custom);
                    text(card, '[data-p-price]', cell.price);
                    text(card, '[data-p-suffix]', cell.suffix);
                    show(text(card, '[data-p-sub]', cell.sub), cell.sub !== '');
                    show(text(card, '[data-p-year]', cell.year), cell.year !== '');
                }
                show(card.querySelector('[data-p-btn-default]'), !cell.custom);
                show(card.querySelector('[data-p-btn-custom]'), cell.custom);
                var link = card.querySelector('[data-p-link]');
                if (link) { link.href = link.href.split('#')[0].replace(/[?&](obem|plashtane)=[^&#]*/g, '').replace(/\?&/, '?') + qs + '#oferta'; }
            });

            document.querySelectorAll('[data-compare-volume]').forEach(function (cellEl) {
                var i = parseInt(cellEl.getAttribute('data-compare-volume'), 10);
                if (step.plans[i]) { cellEl.textContent = step.plans[i][state.billing].cell; }
            });

            if (recLink) {
                recLink.href = '#paket-' + step.recommended;
                recLink.textContent = data.names[step.recommended] || '';
            }

            var volumeField = document.querySelector('[data-quote-volume]');
            var billingField = document.querySelector('[data-quote-billing]');
            if (volumeField) { volumeField.value = step.value; }
            if (billingField) { billingField.value = state.billing; }
            text(document, '[data-quote-summary]', data.labels.summary + ': ' + step.label + ', ' + data.labels[state.billing]);

            if (updateUrl) {
                try {
                    var url = new URL(window.location.href);
                    url.searchParams.set('obem', state.step);
                    url.searchParams.set('plashtane', data.params[state.billing]);
                    window.history.replaceState(null, '', url.toString());
                } catch (e) { /* the page works without the address update */ }
            }
        }

        range.addEventListener('input', function () {
            state.step = Math.max(0, Math.min(count - 1, parseInt(range.value, 10) || 0));
            render(true);
        });
        ticks.forEach(function (tick) {
            tick.addEventListener('click', function () {
                state.step = parseInt(tick.getAttribute('data-pp-tick'), 10);
                render(true);
            });
        });
        billingBtns.forEach(function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                state.billing = btn.getAttribute('data-pp-billing') === 'yearly' ? 'yearly' : 'monthly';
                render(true);
            });
            // Radio group: the arrow keys move between the two options.
            btn.addEventListener('keydown', function (event) {
                if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].indexOf(event.key) === -1) { return; }
                event.preventDefault();
                state.billing = state.billing === 'yearly' ? 'monthly' : 'yearly';
                render(true);
                var next = form.querySelector('[data-pp-billing="' + state.billing + '"]');
                if (next) { next.focus(); }
            });
        });
        form.addEventListener('submit', function (event) { event.preventDefault(); });
        render(false);
    })();
</script>
