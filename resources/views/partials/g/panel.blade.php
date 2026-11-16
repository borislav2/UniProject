{{--
    The interactive panel of the services page in design G (third round, 16.11.2026): seven switches on the left, the share of active ones with exactly one message on the right,
    three quick buttons for a typical business and a button to the contact form with the missing directions ticked. The first screen is rendered here (switches 1 and 5 on),
    resources/js/g-bar.js only reacts to clicks (the same rules as App\Support\GPanel). Texts: services_page.g_panel.
--}}
@php
    $texts = \App\Support\GPanel::texts();
    $state = \App\Support\GPanel::state(\App\Support\GPanel::DEFAULT);
    $presetLabels = ['start' => $texts['preset_start'] ?? '', 'ads' => $texts['preset_ads'] ?? '', 'full' => $texts['preset_full'] ?? ''];
    $messages = collect(\App\Support\GPanel::RULE_ORDER)->mapWithKeys(fn ($rule) => [$rule => (string) ($texts['msg_' . $rule] ?? '')]);
    $directionOf = \App\Support\GPanel::DIRECTIONS;
@endphp
<section id="sistema" class="g-s g-panel" data-g-panel data-total="{{ \App\Support\GPanel::COUNT }}" data-contact="{{ lroute('contact') }}" data-order="{{ implode(',', array_keys((array) config('creatium.contact_directions_g'))) }}" @foreach($messages as $rule => $message) data-msg-{{ $rule }}="{{ $message }}" @endforeach>
    <div class="g-wrap">
        <div class="g-panel-box">
            <div class="g-panel-head">
                <div class="g-panel-intro">
                    <h2 class="g-h">{{ $texts['title'] }}</h2>
                    <p class="g-sh-text">{{ $texts['text'] }}</p>
                </div>
                <div class="g-presets" role="group" aria-label="{{ $texts['presets_label'] }}" data-g-presets>
                    <span class="g-mono g-mute">{{ $texts['presets_label'] }}</span>
                    <div>
                        @foreach(\App\Support\GPanel::PRESETS as $key => $numbers)
                            <button type="button" data-preset="{{ implode(',', $numbers) }}" aria-pressed="{{ $state['preset'] === $key ? 'true' : 'false' }}">{{ $presetLabels[$key] }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="g-panel-grid">
                <ul class="g-switches">
                    @foreach($texts['switches'] as $i => $label)
                        @php($n = $i + 1)
                        <li>
                            <button type="button" role="switch" aria-checked="{{ in_array($n, $state['on'], true) ? 'true' : 'false' }}" data-n="{{ $n }}" data-dir="{{ $directionOf[$n] ?? '' }}">
                                <span class="g-sw-n g-mono">{{ str_pad((string) $n, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="g-sw-label">{{ $label }}</span>
                                <span class="g-sw-state g-mono" aria-hidden="true"><span data-on>{{ __('Вкл.') }}</span><span data-off>{{ __('Изкл.') }}</span></span>
                                <span class="g-sw-track" aria-hidden="true"><i></i></span>
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div class="g-panel-out">
                    <p class="g-mono g-mute">{{ $texts['active_label'] }}</p>
                    <p class="g-panel-count"><b data-count>{{ $state['count'] }}</b> <span>{{ $texts['of'] }} {{ \App\Support\GPanel::COUNT }}</span></p>
                    <div class="g-meter" role="presentation"><div data-bar style="width: {{ $state['percent'] }}%"></div></div>
                    <p class="g-panel-msg" data-msg aria-live="polite">{{ $messages[$state['message']] }}</p>
                    <a class="g-btn" data-cta href="{{ \App\Support\GPanel::contactUrl($state['directions']) }}">{{ $texts['button'] }}<i></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
