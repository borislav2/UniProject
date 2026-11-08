{{-- Head of an inner page in design G: a mono kicker, a light Geologica headline and one lead paragraph. $eyebrow, $heading, $sub (all optional but the heading). --}}
<section class="g-ph">
    <div class="g-wrap">
        @if(filled($eyebrow ?? null))<p class="g-mono g-eyebrow">{{ $eyebrow }}</p>@endif
        <h1 class="g-h">{{ $heading }}</h1>
        @if(filled($sub ?? null))<p class="g-lead">{{ $sub }}</p>@endif
        @isset($actions)<div class="g-cta">{!! $actions !!}</div>@endisset
    </div>
</section>
