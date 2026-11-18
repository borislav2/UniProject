{{-- Support and hosting service, visuals of the brief. $slot = hero (status dashboard), v2 (the circle of daily care). $v = g_vis[$slot]. --}}
@switch($slot)
    @case('hero')
        <figure class="g-bv g-bv-dash">
            <figcaption class="g-bv-cap g-mono"><i class="g-live" aria-hidden="true"></i>{{ $v['label'] }}</figcaption>
            <dl class="g-dash">
                @foreach((array) $v['tiles'] as $tile)
                    <div class="g-dash-tile">
                        <dt class="g-mono">{{ $tile['name'] }}</dt>
                        <dd><b>{{ $tile['value'] }}</b>@if(filled($tile['note'] ?? null))<span>{{ $tile['note'] }}</span>@endif</dd>
                    </div>
                @endforeach
            </dl>
        </figure>
        @break
    @case('v2')
        <figure class="g-bv g-bv-cycle">
            <figcaption class="g-bv-cap g-mono">{{ $v['label'] }}</figcaption>
            <div class="g-cycle">
                <svg viewBox="0 0 240 240" aria-hidden="true" focusable="false">
                    <circle class="y-r" cx="120" cy="120" r="82"/>
                    <circle class="y-f" cx="120" cy="120" r="82"/>
                    <g class="y-n"><rect x="104" y="22" width="32" height="32"/><rect x="186" y="104" width="32" height="32"/><rect x="104" y="186" width="32" height="32"/><rect x="22" y="104" width="32" height="32"/></g>
                    <g class="y-t"><text x="120" y="43" text-anchor="middle">1</text><text x="202" y="125" text-anchor="middle">2</text><text x="120" y="207" text-anchor="middle">3</text><text x="38" y="125" text-anchor="middle">4</text></g>
                    <path class="y-a" d="M170 62L184 54L180 70M178 178L186 192L170 190M70 178L56 186L60 170M62 70L54 56L70 60"/>
                </svg>
                <ol>
                    @foreach((array) $v['steps'] as $step)
                        <li><b>{{ $step['title'] }}</b>@if(filled($step['text'] ?? null))<span>{{ $step['text'] }}</span>@endif</li>
                    @endforeach
                </ol>
            </div>
        </figure>
        @break
@endswitch
