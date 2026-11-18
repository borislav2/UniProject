{{-- Website service, visuals of the brief. $slot = hero (phone and desktop with floating badges), v2 (speed bars), v3 (timeline from the process days). $v = g_vis[$slot], $page = the service page. --}}
@switch($slot)
    @case('hero')
        <figure class="g-bv g-bv-web">
            <figcaption class="g-sr">{{ $v['label'] }}</figcaption>
            @include('partials.g.service-visual', ['key' => 'website'])
            <ul class="g-bv-chips">
                @foreach((array) $v['badges'] as $badge)<li class="c{{ $loop->iteration }}">{{ $badge }}</li>@endforeach
            </ul>
        </figure>
        @break
    @case('v2')
        <figure class="g-bv g-bv-bars">
            <figcaption class="g-bv-cap g-mono">{{ $v['label'] }}</figcaption>
            @foreach((array) $v['bars'] as $bar)
                @php(preg_match('/^([\d.,]+%?)\s*(.*)$/u', $bar['result'], $res))
                <div class="g-bar-row{{ ! empty($bar['good']) ? ' is-good' : '' }}">
                    <div class="g-bar-top"><b>{{ $bar['name'] }}</b><span class="g-mono">{{ $bar['time'] }}</span></div>
                    <div class="g-bar-track" aria-hidden="true"><span style="width: {{ (int) $bar['share'] }}%"></span></div>
                    <p class="g-bar-res">@if($res)<strong>{{ $res[1] }}</strong> {{ $res[2] }}@else{{ $bar['result'] }}@endif</p>
                </div>
            @endforeach
        </figure>
        @break
    @case('v3')
        @include('partials.g.svc.timeline', ['label' => $v['label'], 'items' => collect($page['g_process'])->map(fn ($step) => ['day' => $step['days'] ?? '', 'text' => $step['short'] ?? $step['title']])->all()])
        @break
@endswitch
