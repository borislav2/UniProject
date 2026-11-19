{{-- AI agents service, visuals of the brief. $slot = hero (a chatbot against an agent with a chain of actions), v2 (the map of departments). $v = g_vis[$slot]. --}}
@switch($slot)
    @case('hero')
        <figure class="g-bv g-bv-agent">
            <figcaption class="g-sr">{{ $v['label'] }}</figcaption>
            <div class="g-agent">
                @foreach(['bot' => $v['bot'], 'agent' => $v['agent']] as $side => $col)
                    <section class="g-agent-col is-{{ $side }}" aria-label="{{ $col['name'] }}">
                        <p class="g-mono g-agent-name">{{ $col['name'] }}</p>
                        <ol>
                            @foreach((array) $col['steps'] as $step)<li>{{ $step }}</li>@endforeach
                        </ol>
                    </section>
                @endforeach
            </div>
        </figure>
        @break
    @case('v2')
        @include('partials.g.svc.hub', ['v' => $v])
        @break
@endswitch
