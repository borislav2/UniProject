{{-- Three measurable facts about a site (demos[].g_facts: value + label), a small three column row on the project cards. $facts: list of {value, label}. --}}
@php($facts = collect((array) ($facts ?? []))->filter(fn ($f) => filled($f['value'] ?? null) && filled($f['label'] ?? null))->take(3)->values())
@if($facts->isNotEmpty())
    <dl class="g-facts">
        @foreach($facts as $fact)
            <div><dd>{{ $fact['value'] }}</dd><dt>{{ $fact['label'] }}</dt></div>
        @endforeach
    </dl>
@endif
