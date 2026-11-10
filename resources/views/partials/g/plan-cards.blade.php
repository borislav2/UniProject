{{--
    The three packages of design G (fourth round): equal cards, a Месечно / Годишно switch that changes every price, and „Какво включва“ as groups
    that open on demand (<details>). Data: plans.g_items (App\Support\GPlans). Needs $plans and $billing; $cta = where the buttons go.
--}}
@php
    $gCards = \App\Support\GPlans::cards($plans, $billing);
    $gRec = collect((array) ($plans['items'] ?? []))->search(fn ($p) => ! empty($p['highlighted']));
    $gBadge = collect((array) ($plans['items'] ?? []))->pluck('badge')->filter()->first() ?: __('Препоръчваме');
    $gSwitch = ! empty($billing['enabled']) && collect($gCards)->contains(fn ($c) => $c['yearly'] !== null);
    $gPad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
@endphp
@if($gCards)
<div data-g-billing="m" data-g-billing-root class="g-plans-wrap">
    @if($gSwitch)
        <div class="g-tog" role="group" aria-label="{{ __('Плащане') }}"><button type="button" data-b="m" aria-pressed="true">{{ __('Месечно') }}</button><button type="button" data-b="y" aria-pressed="false">{{ __('Годишно') }}</button></div>
        <p class="g-tognote">{{ __('Цените са в евро.') }} @if(filled($plans['g_yearly_note'] ?? null)){{ __('Годишно плащане') }}, {{ \Illuminate\Support\Str::lower($plans['g_yearly_note']) }}.@endif</p>
    @endif
    <div class="g-plans">
        @foreach($gCards as $card)
            @php($isRec = $card['index'] === $gRec)
            <article id="paket-{{ $card['index'] }}" class="g-plan {{ $isRec ? 'g-rec' : '' }}" data-plan="{{ $card['index'] }}">
                <div class="g-top"><span class="g-mono {{ $isRec ? '' : 'g-mute' }}">{{ $gPad($loop->iteration) }}.</span>@if($isRec)<span class="g-mono">{{ $gBadge }}</span>@endif</div>
                <h3>{{ $card['name'] }}</h3>
                <div class="g-pricebox">
                    @if($card['monthly'] !== null)
                        <div class="g-price">
                            <span data-bv="m">@if($card['from'] !== '')<span class="g-from">{{ $card['from'] }}</span> @endif{{ $card['monthly'] }}<span class="g-per">{{ __('/месец') }}</span></span>
                            @if($gSwitch && $card['yearly'] !== null)<span data-bv="y">@if($card['from'] !== '')<span class="g-from">{{ $card['from'] }}</span> @endif{{ $card['yearly'] }}<span class="g-per">{{ __('/година') }}</span></span>@endif
                        </div>
                        @if($gSwitch && $card['yearly'] !== null && filled($plans['g_yearly_note'] ?? null))<p class="g-free" data-bv="y">{{ $plans['g_yearly_note'] }}</p>@endif
                    @endif
                </div>
                <p class="g-aud">{{ $card['audience'] }}</p>
                <a class="g-btn" href="{{ lroute('packages') }}?paket={{ $card['index'] }}#oferta">{{ __('Изпратете запитване') }}<i></i></a>
                <div class="g-incl">
                    <p class="g-mono g-mute">{{ $plans['g_includes_label'] ?? __('Какво включва') }}</p>
                    @foreach($card['groups'] as $group)
                        <details class="g-grp">
                            <summary><span>@if(count($card['groups']) > 1)<b class="g-mono">{{ $loop->iteration }}.</b> @endif{{ $group['title'] }}</span><i aria-hidden="true"></i></summary>
                            <ul>
                                @foreach($group['points'] as $point)
                                    <li><b>{{ $point['title'] }}</b>@if(filled($point['text'] ?? null))<span>{{ $point['text'] }}</span>@endif</li>
                                @endforeach
                            </ul>
                        </details>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
</div>
@endif
