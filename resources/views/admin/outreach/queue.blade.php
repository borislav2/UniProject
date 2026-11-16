@extends('admin.layout')

@section('title', 'За одобрение')

@section('content')
@include('admin.outreach._tabs')
@if(session('send_error'))
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('send_error') }}</div>
@endif
@unless($configured)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="alert">
        <p class="font-semibold">Пощата за първи писма не е настроена, затова „Одобри и изпрати“ е спряно.</p>
        <p class="mt-1">На сървъра пуснете <code class="rounded bg-yellow-100 px-1">bash deploy/configure-outreach-mail.sh</code>.</p>
    </div>
@endunless

@php
    $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500';
    $userName = auth()->user()->name;
@endphp

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-gray-900">{{ $total }} {{ $total === 1 ? 'бизнес чака' : 'бизнеса чакат' }} одобрение</h2>
        <p class="text-sm text-gray-600">Подредени по оценка. Нищо не се праща, докато не натиснете „Одобри и изпрати“. Днес може да се изпратят още <strong>{{ $remaining }}</strong> от {{ config('creatium.outreach.daily_limit') }}.</p>
    </div>
    <form method="GET" action="{{ route('admin.outreach.queue') }}" class="inline-flex items-center gap-2 text-sm">
        <label for="queue-country" class="font-semibold text-gray-700">Държава</label>
        <select id="queue-country" name="country" class="rounded border border-gray-300 px-2 py-1.5" onchange="this.form.submit()">
            <option value="">Всички ({{ $total }})</option>
            @foreach(\App\Support\Outreach\Markets::codes() as $code)<option value="{{ $code }}" @selected($country === $code)>{{ \App\Support\Outreach\Markets::label($code) }} ({{ $byCountry[$code] ?? 0 }})</option>@endforeach
        </select>
    </form>
</div>

@if($prospects->isEmpty())
    <div class="rounded-lg bg-white p-8 text-center shadow">
        <p class="text-lg font-semibold text-gray-900">Няма чернови за одобрение.</p>
        <p class="mt-2 text-sm text-gray-600">Пуснете <a href="{{ route('admin.outreach.search.index') }}" class="text-blue-600 hover:underline">търсене</a> или включете <a href="{{ route('admin.outreach.autopilot') }}" class="text-blue-600 hover:underline">автопилота</a>: той намира бизнеси, оценява ги и подготвя черновите тук.</p>
    </div>
@else
<form id="bulk-approve" action="{{ route('admin.outreach.queue.approve-selected') }}" method="POST" class="mb-4 flex flex-wrap items-center gap-3 rounded-lg bg-white p-3 text-sm shadow" onsubmit="return confirm('Да изпратим ли писмата до избраните бизнеси? Всяко е със запазената му чернова.')" data-limit="{{ $remaining }}">
    @csrf
    <button @disabled(! $configured || $remaining < 1) class="inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 font-semibold text-white hover:bg-brand-800 disabled:opacity-50"><x-icon name="paper-plane" /> Одобри избраните</button>
    <span class="text-gray-600"><span data-picked>0</span> избрани, най-много {{ $remaining }} днес. Изпраща се запазената чернова, затова първо натиснете „Запази черновата“, ако сте я променили.</span>
    @error('ids')<span class="text-red-600">{{ $message }}</span>@enderror
</form>

<div class="space-y-4" data-queue>
    @foreach($prospects as $prospect)
        @php
            $emailable = $prospect->canBeEmailed();
            $reason = \App\Support\Outreach\Markets::emailReason($prospect);
            $english = $prospect->mailLanguage() === 'en';
            $industry = \App\Models\OutreachCall::INDUSTRIES[mb_strtolower((string) $prospect->industry)] ?? $prospect->industry;
            $host = $prospect->website ? preg_replace('/^www\./', '', (string) parse_url($prospect->website, PHP_URL_HOST)) : null;
        @endphp
        <article id="p{{ $prospect->id }}" tabindex="0" data-card class="scroll-mt-24 rounded-lg bg-white p-5 shadow outline-none focus:ring-2 focus:ring-brand-500">
            <form action="{{ $emailable ? route('admin.outreach.queue.approve', $prospect) : route('admin.outreach.queue.route', $prospect) }}" method="POST" data-card-form data-name="{{ $prospect->displayName() }}">
                @csrf
                <input type="hidden" name="first_name" value="{{ $prospect->first_name }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        @if($emailable)<input type="checkbox" name="ids[]" value="{{ $prospect->id }}" form="bulk-approve" data-pick class="mt-1.5" aria-label="Избери {{ $prospect->displayName() }}">@endif
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-gray-900">{{ $prospect->displayName() }}</h3>
                            <p class="text-sm text-gray-600">{{ collect([$industry, $prospect->city, $prospect->marketLabel()])->filter()->join(' · ') }}</p>
                            <p class="mt-0.5 text-xs">
                                @if($host)<a href="{{ $prospect->website }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $host }}</a>@endif
                                @if($prospect->phone)<x-phone :number="$prospect->phone" :market="$prospect->country" class="ml-2 text-xs" />@endif
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @include('admin.outreach._score', ['prospect' => $prospect])
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 font-semibold text-gray-700">Писмо на {{ $english ? 'английски' : 'български' }}</span>
                        @if($emailable)
                            <span class="rounded-full bg-green-100 px-2 py-0.5 font-semibold text-green-800" title="{{ $prospect->email }}">Имейл разрешен</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-800">Без имейл</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 grid gap-4 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="adm-eyebrow">Какво забелязахме на сайта</p>
                            @if($prospect->findings)
                                <ul class="mt-1 space-y-0.5 text-gray-700">@foreach($prospect->findings as $finding)<li>– {{ $finding['title'] }}</li>@endforeach</ul>
                            @else
                                <p class="mt-1 text-gray-600">Сайтът е в добро състояние по 14-те показателя.</p>
                            @endif
                        </div>
                        <div class="rounded-lg {{ $emailable ? 'bg-green-50 text-green-900' : 'bg-amber-50 text-amber-900' }} p-3 text-xs">
                            <p class="font-semibold">{{ $emailable ? 'Правило за канала' : 'Правило за канала: без имейл' }}</p>
                            @if($emailable)
                                <p class="mt-0.5">{{ \App\Support\Outreach\Markets::rule($prospect->country) }}@if($prospect->company_evidence) На сайта пише „{{ $prospect->company_evidence }}“.@endif Адрес {{ $prospect->email }}.</p>
                            @else
                                <p class="mt-0.5">{{ $reason ?? $prospect->emailBlockReason() }}</p>
                            @endif
                        </div>
                        @if($summaries[$prospect->id] ?? null)
                            <p class="rounded-lg bg-blue-50 p-3 text-xs text-blue-900"><span class="font-semibold">На български.</span> {{ $summaries[$prospect->id] }}</p>
                        @endif
                    </div>

                    <div>
                        @if($emailable)
                            <p class="adm-eyebrow">Чернова</p>
                            <label class="mt-1 block text-xs font-semibold text-gray-600" for="subject-{{ $prospect->id }}">Тема</label>
                            <input id="subject-{{ $prospect->id }}" name="subject" value="{{ old('subject', $prospect->draft_subject ?? $prospect->defaultSubject()) }}" required maxlength="150" class="{{ $input }}" data-edit>
                            <label class="mt-2 block text-xs font-semibold text-gray-600" for="obs-{{ $prospect->id }}">Писмо (след представянето ни, преди линковете и бутона за разговор)</label>
                            <textarea id="obs-{{ $prospect->id }}" name="observation" rows="{{ $english ? 7 : 6 }}" required minlength="20" maxlength="900" class="{{ $input }}" data-edit lang="{{ $english ? 'en' : 'bg' }}">{{ old('observation', $prospect->draft_observation ?? $prospect->suggestedObservation()) }}</textarea>
                            <details class="mt-2 text-xs text-gray-600">
                                <summary class="cursor-pointer font-semibold">Подпис и как изглежда писмото</summary>
                                <label class="mt-2 block font-semibold" for="sender-{{ $prospect->id }}">Подпис</label>
                                <input id="sender-{{ $prospect->id }}" name="sender" value="{{ $userName }}" required maxlength="100" class="{{ $input }} max-w-xs">
                                <p class="mt-1">Представянето, линковете към сайта и безплатната проверка, бутонът за разговор и редът за отписване се добавят автоматично.</p>
                            </details>
                        @else
                            <p class="adm-eyebrow">Следваща стъпка</p>
                            <p class="mt-1 text-sm text-gray-700">Писмо не се подготвя. Обадете се или потърсете човека в LinkedIn.@if($english) Скриптът за разговор на английски се отваря в „Следващо обаждане“.@endif</p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3">
                    @if($emailable)
                        <button type="submit" data-approve title="Писмото тръгва веднага към този бизнес" @disabled(! $configured || $remaining < 1) class="adm-btn"><x-icon name="paper-plane" /> Одобри и изпрати <kbd class="ml-1 hidden rounded border border-white/40 px-1 text-[10px] md:inline">A</kbd></button>
                        <button type="submit" formaction="{{ route('admin.outreach.queue.draft', $prospect) }}" class="adm-btn ghost sm" title="Запазва темата и писмото, без да ги праща">Запази черновата</button>
                        <button type="submit" formaction="{{ route('admin.outreach.preview', $prospect) }}" formtarget="_blank" formnovalidate class="adm-btn ghost sm"><x-icon name="eye" /> Преглед</button>
                        <button type="button" class="adm-btn ghost sm" data-edit-key>Редактирай <kbd class="ml-1 hidden rounded border border-gray-300 px-1 text-[10px] md:inline">E</kbd></button>
                    @else
                        <button type="submit" formaction="{{ route('admin.outreach.queue.route', $prospect) }}" name="to" value="call" formnovalidate class="adm-btn" title="Без писмо. Бизнесът отива в списъка за обаждане."><x-icon name="phone" /> → Обаждане</button>
                        <button type="submit" formaction="{{ route('admin.outreach.queue.route', $prospect) }}" name="to" value="linkedin" formnovalidate class="adm-btn ghost" title="Без писмо. Бизнесът отива в списъка за LinkedIn."><x-icon name="linkedin" /> → LinkedIn</button>
                    @endif
                    <button type="submit" formaction="{{ route('admin.outreach.queue.skip', $prospect) }}" formnovalidate data-skip class="adm-btn ghost sm">Пропусни <kbd class="ml-1 hidden rounded border border-gray-300 px-1 text-[10px] md:inline">S</kbd></button>
                    @if($prospect->project_id)<a href="{{ route('admin.projects.show', $prospect->project_id) }}" class="text-xs text-blue-600 hover:underline">Запитване →</a>@endif
                </div>
            </form>
        </article>
    @endforeach
</div>
<div class="mt-4">{{ $prospects->links() }}</div>
<p class="mt-3 hidden text-center text-xs text-gray-400 md:block">Клавиши: <kbd>J</kbd> и <kbd>K</kbd> следваща и предишна · <kbd>E</kbd> редактиране · <kbd>A</kbd> одобри и изпрати (с потвърждение) · <kbd>S</kbd> пропусни</p>

<script>
(function () {
    var cards = Array.prototype.slice.call(document.querySelectorAll('[data-card]'));
    var current = -1;
    function focusCard(i) {
        if (!cards.length) { return; }
        current = Math.max(0, Math.min(cards.length - 1, i));
        cards[current].focus();
        cards[current].scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
    var hash = location.hash.match(/^#p(\d+)$/);
    if (hash) { var at = cards.findIndex(function (c) { return c.id === 'p' + hash[1]; }); if (at >= 0) { current = at; } }
    cards.forEach(function (card, i) { card.addEventListener('focusin', function () { current = i; }); });

    // Пращането е само с клик или с A и потвърждение: нищо не тръгва от само себе си.
    document.querySelectorAll('[data-card-form]').forEach(function (form) {
        var approve = form.querySelector('[data-approve]');
        if (approve) {
            approve.addEventListener('click', function (e) {
                if (!confirm('Да изпратим ли писмото до ' + form.dataset.name + '?')) { e.preventDefault(); }
            });
        }
        var skip = form.querySelector('[data-skip]');
        if (skip) {
            skip.addEventListener('click', function (e) {
                if (!confirm('Да пропуснем ли ' + form.dataset.name + '?')) { e.preventDefault(); }
            });
        }
        var edit = form.querySelector('[data-edit-key]');
        if (edit) { edit.addEventListener('click', function () { var field = form.querySelector('textarea[data-edit]'); if (field) { field.focus(); } }); }
    });

    document.addEventListener('keydown', function (e) {
        var tag = (e.target.tagName || '').toLowerCase();
        if (e.ctrlKey || e.metaKey || e.altKey || tag === 'textarea' || tag === 'select' || (tag === 'input' && e.target.type !== 'checkbox')) { return; }
        var key = e.key.toLowerCase();
        if (key === 'j') { focusCard(current + 1); e.preventDefault(); return; }
        if (key === 'k') { focusCard(current - 1); e.preventDefault(); return; }
        var card = cards[current];
        if (!card) { return; }
        if (key === 'e') { var field = card.querySelector('textarea[data-edit]'); if (field) { field.focus(); e.preventDefault(); } }
        else if (key === 'a') { var ok = card.querySelector('[data-approve]'); if (ok && !ok.disabled) { ok.click(); e.preventDefault(); } }
        else if (key === 's') { var no = card.querySelector('[data-skip]'); if (no) { no.click(); e.preventDefault(); } }
    });

    // „Одобри избраните“: не повече от оставащия дневен лимит.
    var bulk = document.getElementById('bulk-approve');
    if (bulk) {
        var limit = parseInt(bulk.dataset.limit, 10) || 0;
        var picks = Array.prototype.slice.call(document.querySelectorAll('[data-pick]'));
        var counter = bulk.querySelector('[data-picked]');
        function refresh() {
            var n = picks.filter(function (p) { return p.checked; }).length;
            counter.textContent = n;
            picks.forEach(function (p) { p.disabled = !p.checked && n >= limit; });
        }
        picks.forEach(function (p) { p.addEventListener('change', refresh); });
        refresh();
    }
})();
</script>
@endif
@endsection
