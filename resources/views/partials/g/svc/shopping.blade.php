{{-- Google Shopping service, visuals of the brief. $slot = hero (a product card with callouts), v2 (the feed before and after). $v = g_vis[$slot]. --}}
@switch($slot)
    @case('hero')
        @php($callouts = array_values((array) $v['callouts']))
        <figure class="g-bv g-bv-shop">
            <figcaption class="g-sr">{{ $v['label'] }}</figcaption>
            <div class="g-shop-search" aria-hidden="true"><i></i><span>{{ $v['query'] }}</span></div>
            <div class="g-shop">
                <div class="g-shop-cell photo" aria-hidden="true">
                    <svg viewBox="0 0 160 90" focusable="false"><rect class="p-bg" width="160" height="90"/><ellipse class="p-sh" cx="80" cy="74" rx="52" ry="6"/><path class="p-boot" d="M44 70V40L62 36L74 44L96 40L112 52C122 54 128 60 128 70Z"/><path class="p-sole" d="M44 70H128"/></svg>
                </div>
                <span class="g-shop-lead" aria-hidden="true"></span><p class="g-shop-note">{{ $callouts[0] ?? '' }}</p>
                <div class="g-shop-cell price"><p class="g-shop-title">{{ $v['title'] }}</p><p class="g-shop-price">{{ $v['price'] }}</p></div>
                <span class="g-shop-lead" aria-hidden="true"></span><p class="g-shop-note">{{ $callouts[1] ?? '' }}</p>
                <div class="g-shop-cell rate" aria-hidden="true"><span class="g-stars is-hi"><i></i><i></i><i></i><i></i><i></i></span></div>
                <span class="g-shop-lead" aria-hidden="true"></span><p class="g-shop-note">{{ $callouts[2] ?? '' }}</p>
                <div class="g-shop-cell ship"><span>{{ $v['shipping'] }}</span></div>
                <span class="g-shop-lead" aria-hidden="true"></span><p class="g-shop-note">{{ $callouts[3] ?? '' }}</p>
            </div>
        </figure>
        @break
    @case('v2')
        @include('partials.g.svc.cmp', ['v' => $v])
        @break
@endswitch
