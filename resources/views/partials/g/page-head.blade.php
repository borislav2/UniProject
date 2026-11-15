{{-- Head of an inner page in design G: a light Geologica headline, one lead paragraph, an optional small note under it and optionally more paragraphs. No label above the headline. $note, $heading, $sub, $more (list of paragraphs). --}}
<section class="g-ph">
    <div class="g-wrap">
        <h1 class="g-h">{{ $heading }}</h1>
        @if(filled($sub ?? null))<p class="g-lead">{{ $sub }}</p>@endif
        @if(filled($note ?? null))<p class="g-mono g-mute g-ph-note">{{ $note }}</p>@endif
        @if(! empty($more))<div class="g-ph-more">@foreach($more as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div>@endif
        @isset($actions)<div class="g-cta">{!! $actions !!}</div>@endisset
    </div>
</section>
