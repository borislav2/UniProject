@extends('admin.layout')

@section('title', 'Потенциални клиенти')

@section('content')
@include('admin.outreach._tabs')
@if(session('send_error'))
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('send_error') }}</div>
@endif
@unless($configured)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="alert">
        <p class="font-semibold">Пощата за първи писма не е настроена, затова изпращането е спряно.</p>
        <p class="mt-1">Писмата до нови бизнеси се пращат от отделен домейн (напр. info@creativemoa.site), никога от creatiumlab.com. На сървъра пуснете <code class="rounded bg-yellow-100 px-1">bash deploy/configure-outreach-mail.sh</code>.</p>
    </div>
@endunless

<div class="grid gap-6 lg:grid-cols-3 mb-8">
    <div class="lg:col-span-2 rounded-lg bg-white p-5 shadow">
        <h3 class="font-bold text-gray-900">Внасяне от файл</h3>
        <p class="mt-1 text-sm text-gray-600">По желание: CSV със списък (например от <code class="rounded bg-gray-100 px-1">harvey export</code>). Новите бизнеси идват от „Търсене“.</p>
        <form action="{{ route('admin.outreach.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-center gap-3">
            @csrf
            <input type="file" name="file" accept=".csv,text/csv" required class="text-sm">
            <button class="inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800"><x-icon name="plus" /> Внеси</button>
        </form>
        @error('file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @if($serverCsv)
            <form action="{{ route('admin.outreach.import-server') }}" method="POST" class="mt-4 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-4">
                @csrf
                <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50"><x-icon name="rotate-left" /> Внеси от сървъра</button>
                <span class="text-sm text-gray-600">Harvey на сървъра изнесе списък на {{ $serverCsv->timezone('Europe/Sofia')->format('d.m.Y H:i') }}.</span>
            </form>
        @endif
    </div>
    <div class="rounded-lg bg-white p-5 shadow text-sm text-gray-700 space-y-1">
        <h3 class="font-bold text-gray-900">Правила</h3>
        <p>Днес може да се изпратят още <strong>{{ $remaining }}</strong> от {{ config('creatium.outreach.daily_limit') }} писма.</p>
        <p>Пишем само до адреси, които бизнесът сам е публикувал или които са потвърдени, по едно писмо на бизнес.</p>
        <p>Всяко писмо е с проверката на техния сайт и има линк за отписване. Преди „Изпрати на избраните“ отворете няколко сайта и вижте дали находките са верни.</p>
        @if($configured)<p>Изпраща се от <strong>{{ config('creatium.outreach.from_address') }}</strong>.</p>@endif
    </div>
</div>

<nav class="mb-4 flex flex-wrap gap-2 text-sm">
    <a href="{{ route('admin.outreach.index') }}" class="rounded-full px-3 py-1 {{ $status || $clicked ? 'bg-white text-gray-700 shadow' : 'bg-brand-950 text-white' }}">Всички ({{ $counts->sum() }})</a>
    @foreach(\App\Models\OutreachProspect::STATUSES as $key => $label)
        <a href="{{ route('admin.outreach.index', ['status' => $key]) }}" class="rounded-full px-3 py-1 {{ $status === $key ? 'bg-brand-950 text-white' : 'bg-white text-gray-700 shadow' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
        @if($key === 'sent')
            <a href="{{ route('admin.outreach.index', ['clicked' => 1]) }}" class="rounded-full px-3 py-1 {{ $clicked ? 'bg-brand-950 text-white' : 'bg-white text-gray-700 shadow' }}" title="Кликнали по линк в писмото: обадете им се първо">Кликнали ({{ $clickedCount }})</a>
        @endif
    @endforeach
</nav>

@if($unchecked > 0)
    <div class="mb-3 flex flex-wrap items-center gap-3 rounded-lg border border-blue-200 bg-white p-3 text-sm shadow" data-check-work="{{ route('admin.outreach.check') }}">
        <button type="button" data-check-start class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 font-semibold text-gray-800 hover:bg-gray-50 disabled:opacity-50"><x-icon name="rotate-left" /> Провери сайта и имейла на непроверените ({{ $unchecked }})</button>
        <span class="text-gray-600" data-check-progress>Имейлът става „публикуван от бизнеса“ само ако е на домейна на сайта им (или gmail/abv) и наистина е на сайта. Останалите остават „непроверен“ с причина. Оставете страницата отворена.</span>
    </div>
@endif

<form id="bulk-send" action="{{ route('admin.outreach.send-selected') }}" method="POST" class="mb-3 flex flex-wrap items-center gap-3 rounded-lg bg-white p-3 text-sm shadow" onsubmit="return confirm('Да изпратим ли писмата до избраните бизнеси? Всяко е с проверката на техния сайт.')">
    @csrf
    <button @disabled(! $configured || $remaining < 1) class="inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 font-semibold text-white hover:bg-brand-800 disabled:opacity-50"><x-icon name="paper-plane" /> Изпрати на избраните</button>
    <span class="text-gray-600">Готовото писмо от проверката на сайта им. Днес остават {{ $remaining }}. Отворете „Напиши“, ако искате да го промените първо.</span>
    @error('ids')<span class="text-red-600">{{ $message }}</span>@enderror
</form>

<div class="overflow-x-auto rounded-lg bg-white shadow">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
            <tr>
                <th class="px-4 py-3"><input type="checkbox" aria-label="Избери всички" onclick="document.querySelectorAll('[data-bulk]').forEach(c => c.checked = this.checked)"></th>
                <th class="px-4 py-3 font-semibold">Бизнес</th>
                <th class="px-4 py-3 font-semibold">Имейл</th>
                <th class="px-4 py-3 font-semibold">Какво забелязахме на сайта</th>
                <th class="px-4 py-3 font-semibold">Оценка</th>
                <th class="px-4 py-3 font-semibold">Статус</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($prospects as $prospect)
                <tr class="align-top">
                    <td class="px-4 py-3">@if($prospect->canBeEmailed())<input type="checkbox" name="ids[]" value="{{ $prospect->id }}" form="bulk-send" data-bulk aria-label="Избери {{ $prospect->displayName() }}">@endif</td>
                    <td class="px-4 py-3">
                        <span class="font-semibold text-gray-900">{{ $prospect->displayName() }}</span>
                        <span class="block text-xs text-gray-500">{{ collect([\App\Models\OutreachCall::INDUSTRIES[mb_strtolower((string) $prospect->industry)] ?? $prospect->industry, $prospect->city])->filter()->join(' · ') }}</span>
                        @if($prospect->website)<a href="{{ $prospect->website }}" target="_blank" rel="noopener" class="block text-xs text-blue-600 hover:underline">{{ parse_url($prospect->website, PHP_URL_HOST) ?: $prospect->website }}</a>@endif
                    </td>
                    <td class="px-4 py-3">{{ $prospect->email }}<span class="block text-xs {{ in_array($prospect->email_status, \App\Models\OutreachProspect::SENDABLE_EMAIL_STATUSES, true) ? 'text-green-700' : 'text-red-600' }}">{{ \App\Models\OutreachProspect::EMAIL_STATUSES[$prospect->email_status] ?? ($prospect->email_status ?: 'непроверен') }}</span>@if($prospect->check_note)<span class="block text-xs text-gray-500">{{ $prospect->check_note }}</span>@endif</td>
                    <td class="px-4 py-3 max-w-md text-gray-600">
                        @if($prospect->findings)
                            <ul class="space-y-0.5">@foreach($prospect->findings as $finding)<li>– {{ $finding['title'] }}</li>@endforeach</ul>
                        @elseif($prospect->site_checked_at && $prospect->findings === [])
                            Сайтът е в добро състояние по 14-те показателя.
                        @else
                            {{ \Illuminate\Support\Str::limit($prospect->personalization, 160) }}
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $prospect->score }}</td>
                    <td class="px-4 py-3">{{ \App\Models\OutreachProspect::STATUSES[$prospect->status] ?? $prospect->status }}@if($prospect->sent_at)<span class="block text-xs text-gray-500">{{ $prospect->sent_at->format('d.m.Y') }}</span>@endif
                        @if($prospect->clicked_at)<span class="mt-1 block rounded bg-green-50 px-1.5 py-0.5 text-xs font-semibold text-green-800" title="Първи клик: {{ $prospect->clicked_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}">Кликна: {{ \App\Models\OutreachProspect::CLICK_LINKS[$prospect->last_click_link] ?? $prospect->last_click_link }} · {{ $prospect->last_clicked_at->timezone('Europe/Sofia')->format('d.m H:i') }}@if($prospect->click_count > 1) ({{ $prospect->click_count }}×)@endif</span>@endif</td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        @if($prospect->canBeEmailed())
                            <a href="{{ route('admin.outreach.compose', $prospect) }}" class="inline-flex items-center gap-1 rounded-lg bg-brand-950 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-800"><x-icon name="paper-plane" /> Напиши</a>
                        @endif
                        <form action="{{ route('admin.outreach.update', $prospect) }}" method="POST" class="mt-1 inline-flex gap-1">
                            @csrf @method('PATCH')
                            <select name="status" class="rounded border border-gray-300 px-1 py-1 text-xs" onchange="this.form.submit()" aria-label="Смени статуса">
                                <option value="" selected disabled>Статус…</option>
                                <option value="replied">Отговори</option>
                                <option value="skipped">Пропусни</option>
                                <option value="unsubscribed">Не пиши повече</option>
                                <option value="new">Върни като нов</option>
                            </select>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Още няма бизнеси с имейл. Пуснете търсене в „Търсене“.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $prospects->links() }}</div>

@if($unchecked > 0)
<script>
(function () {
    var box = document.querySelector('[data-check-work]');
    if (!box) { return; }
    var button = box.querySelector('[data-check-start]');
    var out = box.querySelector('[data-check-progress]');
    var token = document.querySelector('input[name=_token]').value;
    var total = 0;
    function step(failures) {
        fetch(box.dataset.checkWork, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (p) {
                total += p.checked;
                out.textContent = 'Проверени ' + total + ' · остават ' + p.remaining + (p.busy ? ' · друга проверка върви, изчакваме…' : '');
                if (p.remaining > 0) { setTimeout(function () { step(0); }, p.busy ? 5000 : 0); return; }
                out.textContent = 'Готово: проверени ' + total + '. Презареждаме…';
                location.reload();
            })
            .catch(function () {
                out.textContent += ' · връзката прекъсна, опитваме пак…';
                if (failures < 5) { setTimeout(function () { step(failures + 1); }, 5000); }
            });
    }
    button.addEventListener('click', function () {
        button.disabled = true;
        out.textContent = 'Проверяваме…';
        step(0);
    });
})();
</script>
@endif
@endsection
