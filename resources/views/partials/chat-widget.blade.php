{{--
    Чатът на сайта: бутон долу вдясно и прозорец. Говори с ChatController (POST /chat, GET /chat/{id}).
    Номерът на разговора се пази в localStorage (creatium-chat), за да продължи на следващата страница.
    Текстът от сървъра се показва като текст (textContent); само адресите стават връзки.
--}}
@php
    $chatSuggestions = [__('Колко струва един сайт?'), __('За колко време е готов?'), __('Какво включва месечната поддръжка?'), __('Искам безплатна консултация')];
@endphp
<div id="chat" class="fixed bottom-4 right-4 z-[55] sm:bottom-5 sm:right-5" data-chat
     data-send="{{ route('chat.send') }}" data-history="{{ url('/chat') }}" data-token="{{ csrf_token() }}" data-locale="{{ app()->getLocale() }}"
     data-error="{{ __('Нещо се обърка. Опитайте пак след малко или ни пишете на :email.', ['email' => config('creatium.contact.email')]) }}"
     data-busy="{{ __('Много съобщения за кратко време. Изчакайте минута и опитайте пак.') }}">
    <div class="hidden fixed inset-0 sm:inset-auto sm:bottom-24 sm:right-5 sm:w-[25rem] sm:h-[36rem] sm:max-h-[calc(100vh-8rem)] flex-col overflow-hidden bg-white shadow-[0_30px_80px_-20px_rgb(15_26_43/0.55)] sm:rounded-3xl sm:ring-1 sm:ring-gray-200 dark:bg-ink-900 dark:sm:ring-white/10"
         role="dialog" aria-modal="false" aria-labelledby="chat-title" data-chat-panel>
        <div class="surface-night on-dark relative flex items-center gap-3 px-5 py-4 text-white">
            <span class="relative w-10 h-10 shrink-0 rounded-2xl bg-brand-gradient ring-1 ring-white/20 flex items-center justify-center"><x-icon name="robot" class="text-brand-100" /><span class="absolute -right-0.5 -bottom-0.5 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-ink-950"></span></span>
            <div class="min-w-0">
                <p id="chat-title" class="font-extrabold leading-tight">{{ __('Крео') }}</p>
                <p class="text-xs text-gray-400">{{ __('Асистентът на Creatium Lab') }}</p>
            </div>
            <button type="button" class="ml-auto w-9 h-9 shrink-0 rounded-xl flex items-center justify-center text-gray-300 hover:bg-white/10 hover:text-white" data-chat-close aria-label="{{ __('Затвори чата') }}"><x-icon name="chevron-down" /></button>
        </div>

        <div class="flex-1 overflow-y-auto overscroll-contain px-4 py-5 space-y-3 bg-gray-50/70 dark:bg-ink-950" data-chat-log aria-live="polite">
            <div class="flex justify-start" data-chat-greeting>
                <p class="max-w-[85%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm leading-relaxed text-gray-800 ring-1 ring-gray-200 dark:bg-white/[0.06] dark:text-gray-100 dark:ring-white/10">{{ __('Здравейте! Аз съм Крео, асистентът на Creatium Lab. Питайте ме за услугите, цените и сроковете, или оставете телефон и ще ви се обадим.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2 pt-1" data-chat-suggestions>
                @foreach($chatSuggestions as $suggestion)
                    <button type="button" class="rounded-full border border-brand-200 bg-white px-3 py-1.5 text-xs font-semibold text-brand-700 hover:border-brand-500 dark:border-white/15 dark:bg-white/5 dark:text-brand-300 dark:hover:border-brand-300">{{ $suggestion }}</button>
                @endforeach
            </div>
        </div>

        <form class="border-t border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-ink-900" data-chat-form>
            <div class="flex items-end gap-2">
                <label for="chat-input" class="sr-only">{{ __('Вашето съобщение') }}</label>
                <textarea id="chat-input" rows="1" maxlength="1000" placeholder="{{ __('Напишете въпрос…') }}" class="max-h-32 min-h-[2.75rem] flex-1 resize-none rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 dark:border-white/15 dark:bg-white/5 dark:focus:bg-ink-900 dark:text-white dark:placeholder:text-gray-400 dark:focus:ring-brand-500/30" data-chat-input></textarea>
                <button type="submit" class="w-11 h-11 shrink-0 rounded-xl bg-brand-950 text-white flex items-center justify-center hover:bg-brand-800 disabled:opacity-50 dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100" aria-label="{{ __('Изпрати') }}" data-chat-send><x-icon name="paper-plane" class="text-sm" /></button>
            </div>
            <p class="mt-2 px-1 text-[11px] leading-snug text-gray-500 dark:text-gray-400">{!! __('AI асистент: може да сгреши. Разговорът се пази :days дни. Ако оставите телефон или имейл, ще се свържем с вас. :link', ['days' => config('creatium.chat.retention_days'), 'link' => '<a href="' . e(lroute('privacy')) . '" class="underline hover:text-brand-700 dark:hover:text-brand-300">' . e(__('Поверителност')) . '</a>']) !!}</p>
        </form>
    </div>

    {{-- Покана: показва се веднъж на 3 дни след няколко секунди, ако посетителят още не е писал (creatium-chat-teaser). --}}
    <div class="chat-teaser hidden absolute bottom-full right-0 mb-3 w-64 rounded-2xl rounded-br-md bg-white p-4 pr-9 text-sm leading-snug text-gray-800 shadow-[0_20px_50px_-15px_rgb(15_26_43/0.45)] ring-1 ring-gray-200 dark:bg-ink-900 dark:text-gray-100 dark:ring-white/10" data-chat-teaser>
        <button type="button" class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="{{ __('Затвори') }}" data-chat-teaser-close><x-icon name="minus" class="text-[10px]" /></button>
        <button type="button" class="text-left" data-chat-teaser-open>
            <strong class="block text-brand-950 dark:text-white">{{ __('Здравейте, аз съм Крео!') }}</strong>
            {{ __('Имате въпрос за цени, срокове или вашия сайт? Питайте ме, отговарям веднага.') }}
        </button>
    </div>

    <button type="button" class="chat-toggle group relative flex items-center gap-3 rounded-full bg-gradient-to-br from-brand-700 to-brand-500 py-2.5 pl-2.5 pr-5 text-white shadow-[0_18px_45px_-10px_rgb(45_99_163/0.8)] ring-1 ring-white/25 transition hover:-translate-y-0.5 hover:shadow-[0_22px_50px_-10px_rgb(45_99_163/0.95)]" aria-expanded="false" aria-controls="chat" data-chat-toggle>
        <span class="chat-pulse pointer-events-none absolute inset-0 rounded-full" aria-hidden="true"></span>
        <span class="relative w-10 h-10 rounded-full bg-white/15 flex items-center justify-center ring-1 ring-white/30">
            <x-icon name="robot" class="text-base text-white" />
            <span class="absolute -right-0.5 -top-0.5 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-brand-700" aria-hidden="true"></span>
        </span>
        <span class="relative flex flex-col items-start leading-tight">
            <span class="text-sm font-extrabold">{{ __('Попитайте Крео') }}</span>
            <span class="text-[11px] font-medium text-white/80">{{ __('отговаря веднага') }}</span>
        </span>
    </button>
</div>
<script>
    (function () {
        var root = document.querySelector('[data-chat]');
        if (!root || !window.fetch) return;
        var panel = root.querySelector('[data-chat-panel]'), toggle = root.querySelector('[data-chat-toggle]');
        var log = root.querySelector('[data-chat-log]'), form = root.querySelector('[data-chat-form]');
        var input = root.querySelector('[data-chat-input]'), sendBtn = root.querySelector('[data-chat-send]');
        var suggestions = root.querySelector('[data-chat-suggestions]');
        var KEY = 'creatium-chat', conversation = null, loaded = false, busy = false;
        try { conversation = localStorage.getItem(KEY); } catch (e) {}

        function push(event, extra) { window.dataLayer = window.dataLayer || []; window.dataLayer.push(Object.assign({ event: event }, extra || {})); }

        // Text from the server goes in as text nodes; only http(s) addresses become links.
        function bubble(role, text) {
            var row = document.createElement('div');
            row.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
            var p = document.createElement('p');
            p.className = role === 'user'
                ? 'max-w-[85%] whitespace-pre-line rounded-2xl rounded-br-md bg-brand-950 px-4 py-3 text-sm leading-relaxed text-white dark:bg-brand-600'
                : 'max-w-[85%] whitespace-pre-line rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm leading-relaxed text-gray-800 ring-1 ring-gray-200 dark:bg-white/[0.06] dark:text-gray-100 dark:ring-white/10';
            String(text).replace(/\*\*/g, '').split(/(https?:\/\/[^\s<>"]+[^\s<>".,;:!?)])/g).forEach(function (part, i) {
                if (i % 2) {
                    var a = document.createElement('a');
                    a.href = part; a.textContent = part.replace(/^https?:\/\/(www\.)?/, '');
                    a.className = 'underline font-semibold break-all ' + (role === 'user' ? '' : 'text-brand-700 dark:text-brand-300');
                    if (part.indexOf(location.origin) !== 0) { a.target = '_blank'; a.rel = 'noopener'; }
                    p.appendChild(a);
                } else if (part) {
                    p.appendChild(document.createTextNode(part));
                }
            });
            row.appendChild(p);
            log.appendChild(row);
            log.scrollTop = log.scrollHeight;
            return row;
        }

        function typing() {
            var row = document.createElement('div');
            row.className = 'flex justify-start';
            row.innerHTML = '<span class="inline-flex gap-1 rounded-2xl rounded-bl-md bg-white px-4 py-3.5 ring-1 ring-gray-200 dark:bg-white/[0.06] dark:ring-white/10"><span class="typing-dot w-1.5 h-1.5 rounded-full bg-gray-400"></span><span class="typing-dot w-1.5 h-1.5 rounded-full bg-gray-400" style="animation-delay:.15s"></span><span class="typing-dot w-1.5 h-1.5 rounded-full bg-gray-400" style="animation-delay:.3s"></span></span>';
            log.appendChild(row);
            log.scrollTop = log.scrollHeight;
            return row;
        }

        function loadHistory() {
            if (loaded || !conversation) return;
            loaded = true;
            fetch(root.dataset.history + '/' + encodeURIComponent(conversation), { headers: { Accept: 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : { messages: [] }; })
                .then(function (data) {
                    if (data.messages && data.messages.length) {
                        suggestions.remove();
                        data.messages.forEach(function (m) { bubble(m.role, m.text); });
                    }
                }).catch(function () {});
        }

        function open(state) {
            panel.classList.toggle('hidden', !state);
            panel.classList.toggle('flex', state);
            toggle.setAttribute('aria-expanded', state ? 'true' : 'false');
            toggle.classList.toggle('max-sm:hidden', state);
            if (state) root.querySelector('[data-chat-teaser]').classList.add('hidden');
            if (state) { loadHistory(); setTimeout(function () { input.focus(); }, 50); push('chat_open'); }
        }

        function send(text) {
            text = text.trim();
            if (!text || busy) return;
            busy = true; sendBtn.disabled = true;
            if (suggestions.isConnected) suggestions.remove();
            bubble('user', text);
            input.value = ''; input.style.height = '';
            var dots = typing();
            fetch(root.dataset.send, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': root.dataset.token },
                body: JSON.stringify({ conversation: conversation, message: text, locale: root.dataset.locale, page: location.pathname })
            }).then(function (r) {
                if (r.status === 429) throw new Error('busy');
                if (!r.ok) throw new Error('error');
                return r.json();
            }).then(function (data) {
                conversation = data.conversation;
                try { localStorage.setItem(KEY, conversation); } catch (e) {}
                dots.remove();
                bubble('assistant', data.reply);
                if (data.lead) push('generate_lead', { lead_service: 'Чат' });
            }).catch(function (e) {
                dots.remove();
                bubble('assistant', e.message === 'busy' ? root.dataset.busy : root.dataset.error);
            }).then(function () { busy = false; sendBtn.disabled = false; input.focus(); });
        }

        // Поканата до бутона: веднъж на 3 дни, след 6 секунди, само ако няма разговор и чатът е затворен.
        var teaser = root.querySelector('[data-chat-teaser]'), TEASER = 'creatium-chat-teaser';
        function hideTeaser(remember) {
            teaser.classList.add('hidden');
            if (remember) { try { localStorage.setItem(TEASER, String(Date.now())); } catch (e) {} }
        }
        var lastTeaser = 0;
        try { lastTeaser = Number(localStorage.getItem(TEASER)) || 0; } catch (e) {}
        if (!conversation && Date.now() - lastTeaser > 3 * 864e5) {
            setTimeout(function () {
                if (panel.classList.contains('hidden') && !toggle.classList.contains('opacity-0')) { teaser.classList.remove('hidden'); push('chat_teaser_shown'); }
            }, 6000);
        }
        root.querySelector('[data-chat-teaser-close]').addEventListener('click', function () { hideTeaser(true); });
        root.querySelector('[data-chat-teaser-open]').addEventListener('click', function () { hideTeaser(true); open(true); });

        toggle.addEventListener('click', function () { hideTeaser(true); open(panel.classList.contains('hidden')); });
        root.querySelector('[data-chat-close]').addEventListener('click', function () { open(false); toggle.focus(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.classList.contains('hidden')) { open(false); toggle.focus(); } });
        suggestions.addEventListener('click', function (e) { var b = e.target.closest('button'); if (b) send(b.textContent); });
        form.addEventListener('submit', function (e) { e.preventDefault(); send(input.value); });
        input.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(input.value); } });
        input.addEventListener('input', function () { input.style.height = ''; input.style.height = Math.min(input.scrollHeight, 128) + 'px'; });
        // On phones the button would cover the contact form's buttons: hide it while the form is on screen.
        var contactForm = document.getElementById('contact-form');
        if (contactForm && window.IntersectionObserver && window.matchMedia) {
            var small = window.matchMedia('(max-width: 639px)');
            new IntersectionObserver(function (entries) {
                var hide = small.matches && entries[0].isIntersecting && panel.classList.contains('hidden');
                toggle.classList.toggle('opacity-0', hide);
                toggle.classList.toggle('pointer-events-none', hide);
                if (hide) root.querySelector('[data-chat-teaser]').classList.add('hidden');
            }).observe(contactForm);
        }
        document.querySelectorAll('[data-open-chat]').forEach(function (el) { el.addEventListener('click', function (e) { e.preventDefault(); open(true); }); });
    })();
</script>
