{{-- „Какво е това?“: обяснение на екрана с прост български (App\Support\Admin\Help). Отваря се първия път, затворено остава затворено (по човек и раздел). --}}
@php $help = \App\Support\Admin\Help::forRoute(request()->route()?->getName()); @endphp
@if($help)
    <details class="adm-help" data-adm-help="adm-help:{{ auth()->id() }}:{{ $help['key'] }}" open>
        <summary>
            <span class="adm-help-mark" aria-hidden="true">?</span>
            <span class="adm-help-line"><span class="adm-help-q">Какво е това?</span> <span class="adm-help-what">{{ $help['what'] }}</span></span>
            <span class="adm-help-toggle"><span class="adm-help-open">Повече</span><span class="adm-help-close">Скрий</span><x-icon name="chevron-down" /></span>
        </summary>
        <div class="adm-help-body">
            <p><b>Защо е важно</b> {{ $help['why'] }}</p>
            <p class="adm-help-sub">Какво да направите тук</p>
            <ol>
                @foreach($help['steps'] as $step)<li>{{ $step }}</li>@endforeach
            </ol>
            <p class="adm-help-more"><a href="{{ route('admin.help') }}">Как работи админът, всички раздели обяснени</a></p>
        </div>
    </details>
    <script>
        (function () {
            var el = document.currentScript.previousElementSibling, key = el.getAttribute('data-adm-help');
            try { if (localStorage.getItem(key) === 'closed') el.removeAttribute('open'); } catch (e) {}
            el.addEventListener('toggle', function () {
                try { if (el.open) localStorage.removeItem(key); else localStorage.setItem(key, 'closed'); } catch (e) {}
            });
        })();
    </script>
@endif
