{{-- Three measurable facts about a site (demos[].g_facts: value + label) as one quiet line under the project text. $facts: list of {value, label}. --}}
@php($facts = collect((array) ($facts ?? []))->filter(fn ($f) => filled($f['value'] ?? null) && filled($f['label'] ?? null))->take(3)->values())
@if($facts->isNotEmpty())
    <ul class="g-facts">
        @foreach($facts as $fact)
            <li><b>{{ $fact['value'] }}</b> {{ $fact['label'] }}</li>
        @endforeach
    </ul>
@endif
