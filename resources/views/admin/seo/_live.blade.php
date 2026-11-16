{{-- Живи броячи на знаци (data-counter, data-limit, data-min) и преглед като в Google (data-snippet). Включва се веднъж на страница. --}}
@once
@push('scripts')
<script>
(function () {
    var narrow = "il.,;:'|!ij()[]", wide = 'mwMWЖШЩЮФЪЫЉ@%';
    function cw(c) {
        if (narrow.indexOf(c) > -1) return 5.5;
        if (c === ' ') return 5.6;
        if (wide.indexOf(c) > -1) return 17;
        if (c.toUpperCase() === c && c.toLowerCase() !== c) return 13.6;
        return 10.6;
    }
    function cutTitle(t) {
        t = t.trim();
        var chars = Array.from(t), w = 0, out = '', total = 0;
        chars.forEach(function (c) { total += cw(c); });
        if (total <= {{ \App\Support\Seo\Snippet::TITLE_PX }}) return t;
        for (var i = 0; i < chars.length; i++) { w += cw(chars[i]); if (w > {{ \App\Support\Seo\Snippet::TITLE_PX - 12 }}) break; out += chars[i]; }
        return out.replace(/\s+$/, '') + '…';
    }
    function cutDesc(t) { t = t.trim(); var a = Array.from(t); return a.length <= {{ \App\Support\Seo\Snippet::DESC_MAX }} ? t : a.slice(0, {{ \App\Support\Seo\Snippet::DESC_MAX - 1 }}).join('').replace(/\s+$/, '') + '…'; }

    document.querySelectorAll('[data-counter]').forEach(function (field) {
        var out = document.getElementById(field.dataset.counter);
        var limit = parseInt(field.dataset.limit, 10), min = parseInt(field.dataset.min || '0', 10);
        var update = function () {
            var own = field.value.trim(), fallback = '';
            // Празно поле = ползва се текстът по подразбиране от placeholder-а („Празно = …“): броим него, не нула.
            if (!own && field.placeholder.indexOf('Празно = ') === 0) fallback = field.placeholder.slice(9).trim();
            var n = Array.from(own || fallback).length;
            out.textContent = n + ' / ' + limit + (!own && fallback ? ' по подразбиране' : '');
            if (!own && fallback) { out.className = 'sx-len ' + (n > limit ? 'bad' : n < min ? 'warn' : 'ok'); return; }
            out.className = 'sx-len ' + (n === 0 ? 'missing' : n > limit ? 'bad' : n < min ? 'warn' : 'ok');
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
