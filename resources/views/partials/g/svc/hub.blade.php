{{-- A core with four modules and two way links (software, AI agents). $v = ['label', 'core', 'modules' => [['name', 'note'?], x4]]. Real text, the links are an SVG behind the boxes. --}}
<figure class="g-bv g-bv-hub">
    <figcaption class="g-sr">{{ $v['label'] }}</figcaption>
    <div class="g-hub">
        <svg class="g-hub-lines" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M50 50L19 19M50 50L81 19M50 50L19 81M50 50L81 81"/></svg>
        @foreach(array_slice((array) $v['modules'], 0, 4) as $i => $module)
            <div class="g-hub-mod m{{ $i + 1 }}">
                <b>{{ $module['name'] }}</b>
                @if(filled($module['note'] ?? null))<span>{{ $module['note'] }}</span>@endif
            </div>
        @endforeach
        <div class="g-hub-core"><b>{{ $v['core'] }}</b></div>
    </div>
</figure>
