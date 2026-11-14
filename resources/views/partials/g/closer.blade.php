{{-- The one quiet closing action of an inner page in design G: a line of text, a button and an optional plain link. $text, $label, $href, $kicker, $link (['label', 'href']). --}}
<section class="g-closer">
    <div class="g-wrap g-closer-in">
        <div>
            @if(filled($kicker ?? null))<p class="g-closer-k">{{ $kicker }}</p>@endif
            <p class="g-closer-t g-h">{{ $text }}</p>
        </div>
        <div class="g-closer-a">
            <a class="g-btn" href="{{ $href }}">{{ $label }}<i></i></a>
            @if(! empty($link))<a class="g-link" href="{{ $link['href'] }}">{{ $link['label'] }}</a>@endif
        </div>
    </div>
</section>
