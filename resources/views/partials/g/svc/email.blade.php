{{-- Email service, visuals of the brief. $slot = hero (the letter through the verification shield into the Primary folder), v2 (the automated sequence). $v = g_vis[$slot]. --}}
@switch($slot)
    @case('hero')
        <figure class="g-bv g-bv-mail">
            <figcaption class="g-sr">{{ $v['label'] }}</figcaption>
            <div class="g-mail">
                <div class="g-mail-env">
                    <svg viewBox="0 0 64 48" aria-hidden="true" focusable="false"><rect class="e-b" x="1" y="1" width="62" height="46"/><path class="e-l" d="M1 1L32 28L63 1M1 47L24 24M63 47L40 24"/></svg>
                    <b>{{ $v['letter'] }}</b>
                </div>
                <span class="g-mail-arrow" aria-hidden="true"></span>
                <div class="g-mail-shield">
                    <svg viewBox="0 0 48 56" aria-hidden="true" focusable="false"><path class="s-b" d="M24 2L45 10V28C45 41 36 50 24 54C12 50 3 41 3 28V10Z"/><path class="s-c" d="M14 28L21 35L34 20"/></svg>
                    <b>{{ $v['verify'] }}</b>
                    <ul>@foreach((array) $v['checks'] as $check)<li>{{ $check }}</li>@endforeach</ul>
                </div>
                <span class="g-mail-arrow" aria-hidden="true"></span>
                <div class="g-mail-box">
                    <b class="g-mono">{{ $v['inbox'] }}</b>
                    @foreach((array) $v['folders'] as $folder)
                        <div class="g-mail-folder{{ ! empty($folder['on']) ? ' is-on' : '' }}">
                            <span>{{ $folder['name'] }}</span>
                            @if(! empty($folder['on']))<i aria-hidden="true"></i>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        </figure>
        @break
    @case('v2')
        @include('partials.g.svc.timeline', ['label' => $v['label'], 'items' => $v['steps']])
        @break
@endswitch
