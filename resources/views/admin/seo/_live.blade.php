{{-- Живи броячи на знаци (data-counter, data-limit, data-min) и преглед като в Google (data-snippet). Включва се веднъж на страница. --}}
@once
@push('scripts')
<script>
(function () {
    // Ширини на Arial в хилядни от em, същата таблица като в Snippet::charWidth (за кирилицата също).
    var W = {!! json_encode(\App\Support\Seo\Snippet::widthTable(), JSON_UNESCAPED_UNICODE) !!};
    function cw(c, font) { return (W[c] !== undefined ? W[c] : W._avg) * (font || 20) / 1000; }
    function px(t, font) { var w = 0; Array.from(t).forEach(function (c) { w += cw(c, font); }); return w; }
    function cutTitle(t) {
        t = t.trim();
        var chars = Array.from(t), w = 0, out = '', total = 0;
        chars.forEach(function (c) { total += cw(c); });
        if (total <= {{ \App\Support\Seo\Snippet::TITLE_PX }}) return t;
        for (var i = 0; i < chars.length; i++) { w += cw(chars[i]); if (w > {{ \App\Support\Seo\Snippet::TITLE_PX - \App\Support\Seo\Snippet::charWidth('…') }}) break; out += chars[i]; }
        return out.replace(/\s+$/, '') + '…';
    }
    function cutDesc(t) {
        t = t.trim(); var a = Array.from(t);
        if (a.length <= {{ \App\Support\Seo\Snippet::DESC_MAX }} && px(t, 14) <= {{ \App\Support\Seo\Snippet::DESC_PX }}) return t;
        var w = 0, out = '';
        for (var i = 0; i < a.length && i < {{ \App\Support\Seo\Snippet::DESC_MAX - 1 }}; i++) { w += cw(a[i], 14); if (w > {{ \App\Support\Seo\Snippet::DESC_PX - \App\Support\Seo\Snippet::charWidth('…', 14) }}) break; out += a[i]; }
        return out.replace(/\s+$/, '') + '…';
    }

    document.querySelectorAll('[data-counter]').forEach(function (field) {
        var out = document.getElementById(field.dataset.counter);
        var limit = parseInt(field.dataset.limit, 10), min = parseInt(field.dataset.min || '0', 10);
        var update = function () {
            var own = field.value.trim(), fallback = '';
            // Празно поле = ползва се текстът по подразбиране от placeholder-а („Празно = …“): броим него, не нула.
            if (!own && field.placeholder.indexOf('Празно = ') === 0) fallback = field.placeholder.slice(9).trim();
            var text = own || fallback, n = Array.from(text).length;
            // Заглавие (граница 60 знака) се мери на 20px до 561 px, описание (155 знака) на 14px до 985 px.
            var isTitle = limit <= {{ \App\Support\Seo\Snippet::TITLE_MAX }};
            var wide = px(text, isTitle ? 20 : 14) > (isTitle ? {{ \App\Support\Seo\Snippet::TITLE_PX }} : {{ \App\Support\Seo\Snippet::DESC_PX }});
            var tooLong = n > limit || wide;
            out.textContent = n + ' / ' + limit + ' · ' + Math.round(px(text, isTitle ? 20 : 14)) + ' px' + (!own && fallback ? ' по подразбиране' : '');
            if (!own && fallback) { out.className = 'sx-len ' + (tooLong ? 'bad' : n < min ? 'warn' : 'ok'); return; }
            out.className = 'sx-len ' + (n === 0 ? 'missing' : tooLong ? 'bad' : n < min ? 'warn' : 'ok');
        };
        field.addEventListener('input', update);
        update();
    });

    document.querySelectorAll('[data-snippet]').forEach(function (box) {
        var title = document.querySelector(box.dataset.titleInput), desc = document.querySelector(box.dataset.descInput);
        var t = box.querySelector('[data-t]'), d = box.querySelector('[data-d]');
        var update = function () {
            var tv = (title && title.value.trim()) || box.dataset.fallbackTitle || '';
            var dv = (desc && desc.value.trim()) || box.dataset.fallbackDesc || '';
            t.textContent = tv ? cutTitle(tv) : 'Без заглавие';
            d.textContent = dv ? cutDesc(dv) : 'Без описание: Google ще вземе откъс от страницата.';
            t.classList.toggle('empty', !tv); d.classList.toggle('empty', !dv);
        };
        [title, desc].forEach(function (el) { if (el) el.addEventListener('input', update); });
        update();
    });
})();
</script>
@endpush
@endonce
