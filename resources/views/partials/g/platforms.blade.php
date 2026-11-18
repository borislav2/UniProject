{{--
    „Работим с водещите платформи“ (18.11.2026): an endless strip of platform names (creatium.platforms, plain wordmarks, no logos) on the G service pages and /uslugi.
    The list is written twice for the loop, the second copy is aria-hidden. It pauses on hover, on focus and with the button; under reduced motion it does not move and the list wraps.
--}}
@php($platforms = array_values(array_filter((array) config('creatium.platforms'))))
@if($platforms)
<section class="g-plat" aria-labelledby="g-plat-h" data-g-plat>
    <div class="g-wrap g-plat-top">
        <h2 class="g-mono g-mute" id="g-plat-h">{{ __('Работим с водещите платформи') }}</h2>
        <button type="button" class="g-plat-btn" aria-pressed="false" data-g-plat-toggle><span data-off>{{ __('Пауза') }}</span><span data-on>{{ __('Пусни') }}</span></button>
    </div>
    <div class="g-plat-view">
        <ul class="g-plat-track">
            @foreach($platforms as $platform)<li>{{ $platform }}</li>@endforeach
            @foreach($platforms as $platform)<li aria-hidden="true">{{ $platform }}</li>@endforeach
        </ul>
    </div>
</section>
<script>
    (function () {
        var box = document.querySelector('[data-g-plat]');
        var btn = box && box.querySelector('[data-g-plat-toggle]');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var paused = box.hasAttribute('data-paused');
            if (paused) box.removeAttribute('data-paused'); else box.setAttribute('data-paused', '');
            btn.setAttribute('aria-pressed', paused ? 'false' : 'true');
        });
    })();
</script>
@endif
