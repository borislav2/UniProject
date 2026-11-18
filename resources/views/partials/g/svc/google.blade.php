{{-- Google profile service, visuals of the brief. $slot = hero (the local pack of Google Maps on a phone), v2 (before and after). $v = g_vis[$slot]. --}}
@switch($slot)
    @case('hero')
        <figure class="g-bv g-bv-map">
            <figcaption class="g-sr">{{ $v['label'] }}</figcaption>
            <div class="g-map" aria-hidden="true">
                <span class="pin p1"></span><span class="pin p2"></span><span class="pin p3"></span>
            </div>
            <ol class="g-pack">
                <li class="is-top">
                    <span class="g-rank g-mono">{{ $v['rank'] }}</span>
                    <div class="g-pack-main">
                        <b>{{ $v['name'] }}</b>
                        <p class="g-pack-rate"><strong>{{ $v['rating'] }}</strong> <span class="g-stars is-hi" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span> <span class="g-sr">{{ $v['stars'] }}</span></p>
                        <p class="g-pack-open"><i aria-hidden="true"></i>{{ $v['open'] }}</p>
                        <p class="g-pack-photos"><span class="g-thumbs" aria-hidden="true"><i></i><i></i><i></i></span>{{ $v['photos'] }}</p>
                        <div class="g-pack-btns"><span>{{ $v['call'] }}</span><span>{{ $v['route'] }}</span></div>
                    </div>
                </li>
                <li class="is-ghost" aria-hidden="true"><span class="g-rank g-mono">2</span><div class="g-pack-main"><span class="g-ghost-l w1"></span><span class="g-ghost-l w2"></span></div></li>
                <li class="is-ghost" aria-hidden="true"><span class="g-rank g-mono">3</span><div class="g-pack-main"><span class="g-ghost-l w1"></span><span class="g-ghost-l w2"></span></div></li>
            </ol>
        </figure>
        @break
    @case('v2')
        @include('partials.g.svc.cmp', ['v' => $v])
        @break
@endswitch
