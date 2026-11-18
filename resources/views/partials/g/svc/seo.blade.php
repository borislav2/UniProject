{{-- SEO service, visuals of the brief. $slot = hero (an AI answer that cites the brand), v2 (four pillars), v3 (paid ads against SEO and GEO over 12 months). $v = g_vis[$slot]. --}}
@switch($slot)
    @case('hero')
        <figure class="g-bv g-bv-ai">
            <figcaption class="g-sr">{{ $v['label'] }}</figcaption>
            <div class="g-ai-top"><i aria-hidden="true"></i><span class="g-mono">{{ $v['assistant'] }}</span></div>
            <p class="g-ai-q">{{ $v['question'] }}</p>
            <div class="g-ai-a">
                <p>{{ $v['answer'] }}</p>
                <div class="g-ai-cite">
                    <b>{{ $v['brand'] }}</b>
                    <span class="g-ai-link">{{ $v['link'] }}</span>
                    <em class="g-mono">{{ $v['cited'] }}</em>
                </div>
                <span class="g-ai-line" aria-hidden="true"></span><span class="g-ai-line s" aria-hidden="true"></span>
            </div>
        </figure>
        @break
    @case('v2')
        <figure class="g-bv g-bv-pillars">
            <figcaption class="g-bv-cap g-mono">{{ $v['label'] }}</figcaption>
            <ul class="g-pillars">
                @foreach((array) $v['pillars'] as $pillar)
                    <li>
                        <span class="g-pillar-ico" aria-hidden="true"><x-icon :name="$pillar['icon']" /></span>
                        <b>{{ $pillar['title'] }}</b>
                        <span>{{ $pillar['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        </figure>
        @break
    @case('v3')
        <figure class="g-bv g-bv-curve">
            <figcaption class="g-bv-cap g-mono">{{ $v['label'] }}</figcaption>
            <svg class="g-curve" viewBox="0 0 520 240" aria-hidden="true" focusable="false">
                <path class="c-g" d="M0 60H520M0 120H520M0 180H520"/>
                <path class="c-ax" d="M1 0V239H520"/>
                <path class="c-ads" d="M1 150H500V239"/>
                <path class="c-fill" d="M1 232C90 230 150 224 210 196S320 96 400 62S470 30 518 16V239H1Z"/>
                <path class="c-seo" d="M1 232C90 230 150 224 210 196S320 96 400 62S470 30 518 16"/>
                <circle class="c-dot" cx="518" cy="16" r="5"/>
            </svg>
            <div class="g-curve-x g-mono" aria-hidden="true"><span>{{ $v['from'] }}</span><span>{{ $v['to'] }}</span></div>
            <ul class="g-curve-key">
                <li class="k-ads"><i aria-hidden="true"></i><div><b>{{ $v['ads'] }}</b><span>{{ $v['ads_note'] }}</span></div></li>
                <li class="k-seo"><i aria-hidden="true"></i><div><b>{{ $v['seo'] }}</b><span>{{ $v['seo_note'] }}</span></div></li>
            </ul>
        </figure>
        @break
@endswitch
