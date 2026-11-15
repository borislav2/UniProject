{{-- Един ред от списъка „Възможности“ ($o). Нищо тук не подава оферта: връзката към обявата отваря официалния сайт. --}}
@php
    $days = $o->daysLeft();
    $tone = $o->score >= 75 ? 'high' : ($o->score >= 60 ? 'mid' : '');
    $open = $o->isOpen();
@endphp
<li class="op-row {{ $o->isUrgent() ? 'urgent' : '' }}" id="o{{ $o->id }}">
    <div class="op-score {{ $tone }}" title="Оценка от 0 до 100"><b>{{ $o->score }}</b><small>оценка</small></div>

    <div class="min-w-0">
        <a href="{{ route('admin.opportunities.show', $o) }}" class="op-title">{{ $o->title }}</a>
        <span class="op-tags">
            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $o->statusClass() }}">{{ $o->statusLabel() }}</span>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $o->kindLabel() }}</span>
        </span>
        <p class="op-line">{{ $o->summaryLine() }}</p>
        <div class="op-meta">
            <span>{{ $o->countryName() }}</span>
            <span>{{ $o->sourceLabel() }}</span>
            @if($o->deadline_at)
                <span class="{{ $o->isUrgent() ? 'late' : '' }}">Срок <b class="{{ $o->isUrgent() ? 'late' : '' }}">{{ $o->deadlineText() }}</b> ({{ $o->daysLeftText() }})</span>
            @endif
            @if($o->valueText())<span>Стойност <b>{{ $o->valueText() }}</b></span>@endif
            @if($o->assignee)<span>Води: <b>{{ $o->assignee->name }}</b></span>@endif
            @if($o->url)<a href="{{ $o->url }}" target="_blank" rel="noopener noreferrer" class="adm-link-out text-brand-600 hover:underline">{{ $o->kind === 'tender' ? 'Към обявата' : 'Отвори' }} ↗</a>@endif
        </div>
        @if($o->note)<p class="op-line" style="color: var(--mute)">Бележка: {{ \Illuminate\Support\Str::limit($o->note, 140) }}</p>@endif
        @if($o->skip_reason && $o->status === 'skipped')<p class="op-line" style="color: var(--mute)">Пропусната: {{ $o->skip_reason }}</p>@endif
        <details class="op-reasons">
            <summary>Защо {{ $o->score }} точки</summary>
            <ul>
                @foreach($o->score_reasons ?? [] as $reason)
                    <li><b class="{{ $reason['points'] < 0 ? 'minus' : '' }}">{{ $reason['points'] > 0 ? '+' : '' }}{{ $reason['points'] }}</b><span>{{ $reason['label'] }}</span></li>
                @endforeach
            </ul>
        </details>
    </div>

    <div class="op-side">
        <div class="op-actions">
            @if($o->status === 'new')
                <form action="{{ route('admin.opportunities.interesting', $o) }}" method="POST">@csrf<button class="td-act primary">Интересна</button></form>
            @endif
            @if($open && $o->assigned_to !== auth()->id())
                <form action="{{ route('admin.opportunities.take', $o) }}" method="POST">@csrf<button class="td-act">Поемам я</button></form>
            @endif
            @if($o->project_id)
                <a href="{{ route('admin.projects.show', $o->project_id) }}" class="td-act">Проект →</a>
            @elseif($open)
                <form action="{{ route('admin.opportunities.project', $o) }}" method="POST">@csrf<button class="td-act" title="Създава проект в „Проекти“. Нищо не се подава никъде.">→ Проект</button></form>
            @endif
        </div>

        <details class="text-sm">
            <summary class="text-gray-500">Статус, бележка, следваща стъпка</summary>
            <form action="{{ route('admin.opportunities.update', $o) }}" method="POST" class="op-mini">
                @csrf @method('PATCH')
                <select name="status" aria-label="Статус">
                    @foreach(\App\Models\Opportunity::STATUSES as $key => [$label])<option value="{{ $key }}" @selected($o->status === $key)>{{ $label }}</option>@endforeach
                </select>
                <input type="date" name="next_action_at" value="{{ $o->next_action_at?->timezone('Europe/Sofia')->toDateString() }}" aria-label="Следваща стъпка" title="Следваща стъпка (дата)">
                <input type="text" name="note" value="{{ $o->note }}" maxlength="2000" placeholder="Бележка" aria-label="Бележка">
                <button class="td-act">Запази</button>
            </form>

        </details>
        @if($open)
            <details class="text-sm">
                <summary class="text-gray-500">Пропусни</summary>
                <form action="{{ route('admin.opportunities.skip', $o) }}" method="POST" class="op-mini mt-2">
                    @csrf
                    <input type="text" name="skip_reason" maxlength="200" placeholder="Причина (по желание)" aria-label="Причина за пропускане">
                    <button class="td-act">Пропусни</button>
                </form>
            </details>
        @endif
    </div>
</li>
