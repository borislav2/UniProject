{{-- Head of an inner page in design G: an optional small label, a light Geologica headline, one lead paragraph and optionally more paragraphs. $eyebrow, $heading, $sub, $more (list of paragraphs). --}}
<section class="g-ph">
    <div class="g-wrap">
        @if(filled($eyebrow ?? null))<p class="g-mono g-eyebrow">{{ $eyebrow }}</p>@endif
        <h1 class="g-h">{{ $heading }}</h1>
        @if(filled($sub ?? null))<p class="g-lead">{{ $sub }}</p>@endif
        @if(! empty($more))<div class="g-ph-more">@foreach($more as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div>@endif
        @isset($actions)<div class="g-cta">{!! $actions !!}</div>@endisset
    </div>
</section>
