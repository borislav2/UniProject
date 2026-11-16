{{-- Head of an inner page in design G: a light Geologica headline, one lead paragraph, an optional small note under it and optionally more paragraphs. No label above the headline. $note, $heading, $sub, $more (list of paragraphs), $visualKey (a service_pages key: its illustration beside the text). --}}
<section class="g-ph{{ ! empty($visualKey) ? ' g-ph-vis-on' : '' }}">
    <div class="g-wrap">
        @if(! empty($visualKey))<div class="g-ph-text">@endif
        <h1 class="g-h">{{ $heading }}</h1>
        @if(filled($sub ?? null))<p class="g-lead">{{ $sub }}</p>@endif
        @if(filled($note ?? null))<p class="g-mono g-mute g-ph-note">{{ $note }}</p>@endif
        @if(! empty($more))<div class="g-ph-more">@foreach($more as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div>@endif
        @isset($actions)<div class="g-cta">{!! $actions !!}</div>@endisset
        @if(! empty($visualKey))</div><div class="g-ph-vis">@include('partials.g.service-visual', ['key' => $visualKey])</div>@endif
    </div>
</section>
