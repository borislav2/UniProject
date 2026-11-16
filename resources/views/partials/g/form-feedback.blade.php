{{--
    Design G forms, after a failed submit (the page reloads with the validation errors):
    - a visually hidden live region with the existing messages, so a screen reader announces them (no new text);
    - the first invalid field (aria-invalid="true", or data-g-invalid for a group) is scrolled below the sticky header and focused.
    Each field points to its own message with aria-describedby="err-<field>" (set in the form views). Include it once, after the form: ['box' => selector of the box around the form].
--}}
@if($errors->any())
    <div class="g-sr" role="alert" data-g-errors>
        <ul>
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    <script>
        (function () {
            var box = document.querySelector(@json($box));
            var first = box && box.querySelector('[aria-invalid="true"], [data-g-invalid]');
            if (!first) return;
            // A closed block (the booking) holding the field is opened first.
            for (var d = first.closest('details'); d; d = d.parentElement && d.parentElement.closest('details')) d.open = true;
            var target = first.closest('.g-fld') || first;
            var moved = false;
            ['wheel', 'touchmove', 'keydown'].forEach(function (name) { window.addEventListener(name, function () { moved = true; }, { passive: true, once: true }); });
            function go() {
                // scroll-margin-top (g.css) keeps the label clear of the sticky header
                target.scrollIntoView({ block: 'start', behavior: 'auto' });
                first.focus({ preventScroll: true });
            }
            go();
            // Fonts and images can still move the layout: place it once more after load, unless the visitor already scrolled.
            window.addEventListener('load', function () { if (!moved) requestAnimationFrame(go); });
        })();
    </script>
@endif
