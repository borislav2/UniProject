@extends('admin.layout')

@section('title', 'Възможност')

@section('content')
@php
    $tone = $o->score >= 75 ? 'high' : ($o->score >= 60 ? 'mid' : '');
    $open = $o->isOpen();
    $proc = $o->raw['procedure'] ?? null;
@endphp

<p class="mb-3 text-sm"><a href="{{ route('admin.opportunities.index') }}" class="text-gray-500 hover:underline">← Всички възможности</a></p>

<div class="adm-page-head" style="align-items: flex-start">
    <div class="flex min-w-0 items-start gap-4">
        <div class="op-score {{ $tone }}" style="width: 64px; flex: none"><b>{{ $o->score }}</b><small>оценка</small></div>
        <div class="min-w-0">
            <h2 style="font-size: 22px">{{ $o->title }}</h2>
            <p class="mt-2 flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $o->statusClass() }}">{{ $o->statusLabel() }}</span>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $o->kindLabel() }}</span>
                <span class="text-sm text-gray-500">{{ $o->countryName() }} · {{ $o->sourceLabel() }}</span>
            </p>
        </div>
    </div>
    @if($o->url)
        <a href="{{ $o->url }}" target="_blank" rel="noopener noreferrer" class="adm-btn ghost sm">{{ $o->kind === 'tender' ? 'Официалната обява' : 'Отвори адреса' }} ↗</a>
    @endif
</div>

<p class="op-line mb-4" style="font-size: 15px">{{ $o->summaryLine() }}</p>

<div class="op-detail">
    <div class="grid gap-4">
        <section class="adm-card op-panel">
            <h3>Данни</h3>
            <dl class="op-dl mt-3">
                <dt>Вид</dt><dd>{{ $o->kindLabel() }}</dd>
                <dt>Източник</dt><dd>{{ $o->sourceLabel() }}@if($o->source !== 'manual' && $o->source !== 'search') · № {{ $o->raw['publication_number'] ?? $o->raw['notice'] ?? $o->external_id }}@endif</dd>
                @if($o->buyer)<dt>{{ $o->kind === 'tender' ? 'Възложител' : 'Кой' }}</dt><dd>{{ $o->buyer }}</dd>@endif
                <dt>Държава</dt><dd>{{ $o->countryName() }}</dd>
                @if($o->language)<dt>Език</dt><dd>{{ \App\Support\Opportunities\Countries::languageName($o->language) }}</dd>@endif
                @if($o->topicLabel())<dt>За какво</dt><dd>{{ $o->topicLabel() }}</dd>@endif
                <dt>Публикувана</dt><dd>{{ $o->published_at?->timezone('Europe/Sofia')->format('d.m.Y') ?? 'не е известно' }}</dd>
                <dt>Срок</dt>
                <dd>@if($o->deadline_at){{ $o->deadline_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }} <span class="{{ $o->isUrgent() ? 'font-semibold text-red-700' : 'text-gray-500' }}">({{ $o->daysLeftText() }})</span>@else не е обявен@endif</dd>
                <dt>Стойност</dt><dd>{{ $o->valueText() ?? 'не е обявена' }}</dd>
                @if($proc)<dt>Процедура</dt><dd>{{ $proc }}</dd>@endif
                @if($o->cpv)
                    <dt>CPV</dt>
                    <dd>
                        @foreach($o->cpv as $code)
                            <span class="block"><span class="font-mono text-xs">{{ $code }}</span> {{ $cpvNames[$code] ?? '' }}</span>
                        @endforeach
                    </dd>
                @endif
                @if($o->prospect)<dt>В потенциални клиенти</dt><dd><a href="{{ route('admin.outreach.compose', $o->prospect) }}" class="text-brand-600 hover:underline">{{ $o->prospect->displayName() }}</a>@if($o->prospect->email) · {{ $o->prospect->email }}@endif</dd>@endif
                <dt>Първо видяна</dt><dd>{{ $o->created_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</dd>
            </dl>
        </section>

        @if($o->summary)
            <section class="adm-card op-panel">
                <h3>Описание от източника</h3>
                <p class="hint">На езика на обявата, без имейли и телефони. Не е превод.</p>
                <p class="text-sm text-gray-800" style="white-space: pre-line">{{ $o->summary }}</p>
            </section>
        @endif

        <section class="adm-card op-panel">
            <h3>Защо {{ $o->score }} точки</h3>
            <div class="op-reasons" style="margin: 8px 0 0">
                <ul>
                    @foreach($o->score_reasons ?? [] as $reason)
                        <li><b class="{{ $reason['points'] < 0 ? 'minus' : '' }}">{{ $reason['points'] > 0 ? '+' : '' }}{{ $reason['points'] }}</b><span>{{ $reason['label'] }}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>

        @if($pitch)
            <section class="adm-card op-panel">
                <h3>Партньорско писмо (чернова на английски)</h3>
                <p class="hint">Нищо не се праща само. Правилата за канала важат както за всяко първо писмо: ако за този бизнес не може имейл, обадете се или пишете в LinkedIn. Пращането е от „За одобрение“.</p>
                <p class="text-sm"><strong>Тема:</strong> {{ $pitch['subject'] }}</p>
                <p class="mt-2 text-sm text-gray-800" style="white-space: pre-line">{{ $pitch['body'] }}</p>
                @if($o->prospect)
                    <p class="mt-3 text-sm">
                        @if($o->prospect->draft_at && $o->prospect->status === 'new')<a href="{{ route('admin.outreach.queue') }}#p{{ $o->prospect->id }}" class="text-brand-600 hover:underline">Към „За одобрение“ →</a>@else<a href="{{ route('admin.outreach.compose', $o->prospect) }}" class="text-brand-600 hover:underline">Към бизнеса в „Потенциални клиенти“ →</a>@endif
                    </p>
                @endif
            </section>
        @endif

        @if($o->kind === 'tender')
            <section class="adm-card op-panel">
                <h3>Какво трябва за участие</h3>
                <div class="op-warn mt-3"><strong>Първо проверете условията.</strong> Обществените поръчки често изискват оборот, референции и опит, които малка агенция може да не покрива. Ако не ги покривате, по-добре пропуснете и не губете дни за оферта.</div>
                <ol class="op-check">
                    <li><strong>Условията на възложителя.</strong> Прочетете документацията на официалната обява: изисквания за оборот, брой сходни проекти, срок на изпълнение, критерии за оценка.</li>
                    <li><strong>Регистрация на портала на възложителя.</strong> Оферти се подават само през неговия портал (за поръчки от ЕС обикновено е национална платформа, за Великобритания Find a Tender или порталът на купувача). Понякога е нужен електронен подпис.</li>
                    <li><strong>Документи на фирмата.</strong> Удостоверения за актуално състояние и за липса на задължения, декларация за годност (ESPD или ЕЕДОП), застраховка за професионална отговорност, когато се иска.</li>
                    <li><strong>Референции и предишна работа.</strong> Реални проекти и клиенти с контакт за препоръка. Нищо измислено.</li>
                    <li><strong>Финансови прагове.</strong> Минимален годишен оборот и финансови отчети за последните години.</li>
                    <li><strong>Език на офертата.</strong> На какъв език се подава и дали са нужни заверени преводи{{ $o->language && ! in_array($o->language, config('creatium.opportunities.languages'), true) ? ' (обявата е на ' . \App\Support\Opportunities\Countries::languageName($o->language) . ')' : '' }}.</li>
                    <li><strong>Цена и екип.</strong> Фиксирана цена, кой ще работи, времева зона, поддръжка след предаването.</li>
                    <li><strong>Подаване.</strong> Само от човек от екипа, преди срока{{ $o->deadline_at ? ' (' . $o->deadlineText() . ')' : '' }}. Админът не подава нищо.</li>
                </ol>
            </section>
        @endif
    </div>

    <div class="grid gap-4">
        <section class="adm-card op-panel">
            <h3>Работа по нея</h3>
            <form action="{{ route('admin.opportunities.update', $o) }}" method="POST" class="op-form mt-3" style="grid-template-columns: 1fr">
                @csrf @method('PATCH')
                <label>Статус
                    <select name="status">@foreach(\App\Models\Opportunity::STATUSES as $key => [$label])<option value="{{ $key }}" @selected($o->status === $key)>{{ $label }}</option>@endforeach</select>
                </label>
                <label>Причина, ако я пропускате
                    <input type="text" name="skip_reason" value="{{ $o->skip_reason }}" maxlength="200">
                </label>
                <label>Води я
                    <select name="assigned_to"><option value="">Никой</option>@foreach($team as $person)<option value="{{ $person->id }}" @selected($o->assigned_to === $person->id)>{{ $person->name }}</option>@endforeach</select>
                </label>
                <label>Следваща стъпка (дата)
                    <input type="date" name="next_action_at" value="{{ $o->next_action_at?->timezone('Europe/Sofia')->toDateString() }}">
                </label>
                <label>Бележка
                    <textarea name="note" rows="3" maxlength="2000">{{ $o->note }}</textarea>
                </label>
                <div><button class="adm-btn">Запази</button></div>
            </form>
            <div class="mt-4 flex flex-wrap gap-2">
                @if($open && $o->assigned_to !== auth()->id())<form action="{{ route('admin.opportunities.take', $o) }}" method="POST">@csrf<button class="td-act">Поемам я</button></form>@endif
                @if($o->project_id)
                    <a href="{{ route('admin.projects.show', $o->project_id) }}" class="td-act primary">Проектът →</a>
                @else
                    <form action="{{ route('admin.opportunities.project', $o) }}" method="POST">@csrf<button class="td-act primary" title="Създава проект в „Проекти“. Нищо не се подава никъде.">→ Проект</button></form>
                @endif
            </div>
        </section>

        <section class="adm-card op-panel">
            <h3>История</h3>
            <form action="{{ route('admin.opportunities.note', $o) }}" method="POST" class="op-mini mt-3">
                @csrf
                <input type="text" name="body" required maxlength="2000" placeholder="Нова бележка" aria-label="Нова бележка">
                <button class="td-act">Добави</button>
            </form>
            <ul class="td-feed mt-3" style="margin-left: -18px; margin-right: -18px">
                @forelse($o->notes as $entry)
                    <li>
                        <time style="width: 64px">{{ $entry->created_at->timezone('Europe/Sofia')->format('d.m H:i') }}</time>
                        <span class="min-w-0">
                            <b class="font-semibold">{{ $entry->kind === 'status' ? 'Статус' : 'Бележка' }}</b>@if($entry->user)<span class="text-gray-500"> · {{ $entry->user->name }}</span>@endif
                            <span class="block text-gray-700">{{ $entry->body }}</span>
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500">Още няма записи.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection
