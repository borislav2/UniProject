{{-- Броячът за деня в „Без сайт (за обаждане)“ и „Следващо обаждане“ (OutreachCallController::todayStats). --}}
@php($percent = min(100, (int) round($today['total'] / $today['goal'] * 100)))
<div class="mb-6 rounded-lg bg-white p-4 shadow">
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <p class="text-sm text-gray-700"><strong class="text-lg text-gray-900">Днес: {{ $today['total'] }}</strong> от {{ $today['goal'] }} обаждания</p>
        <p class="text-xs text-gray-600">
            @foreach(['interested' => 'Интерес', 'no_answer' => 'Не вдигна', 'not_interested' => 'Без интерес', 'client' => 'Клиент'] as $key => $label)
                {{ $label }}: <strong>{{ $today['by_status'][$key] ?? 0 }}</strong>@unless($loop->last) · @endunless
            @endforeach
        </p>
    </div>
    <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-valuenow="{{ $today['total'] }}" aria-valuemin="0" aria-valuemax="{{ $today['goal'] }}" aria-label="Обаждания днес">
        <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-green-600' : 'bg-brand-600' }}" style="width: {{ $percent }}%"></div>
    </div>
</div>
