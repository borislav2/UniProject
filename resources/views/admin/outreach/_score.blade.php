{{-- Оценка 0 до 100 като малък чип; отваря се за причините (LeadScore). $prospect, по желание $inline = reasons в потока. --}}
@php
    $score = (int) $prospect->score;
    $tone = $score >= 70 ? 'bg-green-100 text-green-800' : ($score >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
    $reasons = $prospect->score_reasons ?: [];
    $tip = $reasons ? collect($reasons)->map(fn ($r) => $r['label'] . ' (' . ($r['points'] > 0 ? '+' : '') . $r['points'] . ')')->join("\n") : 'Оценка 0 до 100. Още няма подробности.';
@endphp
<details class="group relative inline-block align-top text-left" data-score>
    <summary class="inline-flex cursor-pointer list-none items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $tone }}" title="{{ $tip }}" aria-label="Оценка {{ $score }} от 100, отворете за причините">{{ $score }}<span class="font-normal opacity-60">/100</span></summary>
    @if($reasons)
        <ul class="{{ ! empty($inline) ? 'mt-2' : 'absolute left-0 z-20 mt-1 w-72 shadow-lg' }} space-y-0.5 rounded-md border border-gray-200 bg-white p-2 text-xs text-gray-700">
            @foreach($reasons as $reason)
                <li class="flex justify-between gap-3"><span>{{ $reason['label'] }}</span><b class="{{ $reason['points'] < 0 ? 'text-red-700' : 'text-gray-900' }} whitespace-nowrap">{{ $reason['points'] > 0 ? '+' : '' }}{{ $reason['points'] }}</b></li>
            @endforeach
        </ul>
    @else
        <p class="mt-1 w-56 rounded-md border border-gray-200 bg-white p-2 text-xs text-gray-600">Още няма подробности. Те се записват при следващата проверка на бизнеса.</p>
    @endif
</details>
