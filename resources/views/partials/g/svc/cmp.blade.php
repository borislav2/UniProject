{{--
    Before and after (Google profile, product feed, automation). $v = ['label', 'before' => card, 'after' => card], a card = tag, name, big?, sub?, quote?, fig? (stars-low, stars-high, chaos, flow), items[], result?.
    Real text; the small figures are decorative.
--}}
@php
    $cards = ['before' => $v['before'], 'after' => $v['after']];
@endphp
<figure class="g-bv g-bv-cmp">
    <figcaption class="g-bv-cap g-mono">{{ $v['label'] }}</figcaption>
    <div class="g-cmp">
        @foreach($cards as $side => $card)
            <section class="g-cmp-card is-{{ $side }}" aria-label="{{ $card['tag'] }}">
                <p class="g-mono g-cmp-tag">{{ $card['tag'] }}</p>
                <h3 class="g-cmp-name">{{ $card['name'] }}</h3>
                @if(filled($card['quote'] ?? null))<p class="g-cmp-quote">{{ $card['quote'] }}</p>@endif
                @if(filled($card['big'] ?? null))
                    <p class="g-cmp-big"><b>{{ $card['big'] }}</b> <span>{{ $card['sub'] ?? '' }}</span></p>
                @endif
                @switch($card['fig'] ?? '')
                    @case('stars-low')
                        <p class="g-stars" aria-hidden="true"><i></i><i></i><i></i><i></i><i class="is-off"></i></p>
                        @break
                    @case('stars-high')
                        <p class="g-stars is-hi" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></p>
                        @break
                    @case('chaos')
                        <svg class="g-cmp-fig" viewBox="0 0 220 64" aria-hidden="true" focusable="false"><path class="f-l" d="M30 14L110 50M30 50L110 14M110 14L190 50M110 50L190 14M30 14L190 14M30 50L190 50M30 14L30 50M190 14L190 50"/><rect class="f-n" x="20" y="6" width="20" height="16"/><rect class="f-n" x="20" y="42" width="20" height="16"/><rect class="f-n" x="180" y="6" width="20" height="16"/><rect class="f-n" x="180" y="42" width="20" height="16"/></svg>
                        @break
                    @case('flow')
                        <svg class="g-cmp-fig is-flow" viewBox="0 0 220 64" aria-hidden="true" focusable="false"><path class="f-l f-a" d="M44 32H70M100 32H126M156 32H182"/><rect class="f-n f-k" x="20" y="22" width="24" height="20"/><rect class="f-n f-k" x="76" y="22" width="24" height="20"/><rect class="f-n f-k" x="132" y="22" width="24" height="20"/><rect class="f-n f-k" x="188" y="22" width="24" height="20"/></svg>
                        @break
                @endswitch
                @if(! empty($card['items']))
                    <ul class="g-cmp-list">
                        @foreach($card['items'] as $item)<li>{{ $item }}</li>@endforeach
                    </ul>
                @endif
                @if(filled($card['result'] ?? null))<p class="g-cmp-result">{{ $card['result'] }}</p>@endif
            </section>
        @endforeach
    </div>
</figure>
