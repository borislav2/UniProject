<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Админ') · Creatium Lab</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    {{-- Админът може да се добави към началния екран на телефона като приложение (отваря „Днес“). --}}
    <link rel="manifest" href="{{ asset('admin.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0C0E12">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Creatium">
    @vite('resources/css/admin.css')
    @stack('head')
</head>
@php
    // Менюто идва от профила на човека (App\Support\Admin\Profiles); броячите се смятат веднъж на заявка (Badges).
    $me = auth()->user();
    $profileKey = $me->profile();
    $canUsers = $me->isAdmin();
    $menu = \App\Support\Admin\Profiles::menu($profileKey, $canUsers);
    $others = \App\Support\Admin\Profiles::others($profileKey, $canUsers);
    $isActive = fn (array $item) => request()->routeIs(...$item['match']);
    $current = null;
    $currentGroup = null;
    foreach ([$menu, $others] as $part) {
        foreach ($part as $group => $items) {
            foreach ($items as $item) {
                if (! $current && $isActive($item)) { $current = $item; $currentGroup = $group; }
            }
        }
    }
    $moreOpen = $current && collect($others)->flatten(1)->contains(fn ($i) => $i['route'] === $current['route']);
    $title = trim($__env->yieldContent('title', 'Админ'));
    $bottom = \App\Support\Admin\Profiles::phoneNav($profileKey);
    $quick = \App\Support\Admin\Profiles::quickActions($profileKey);
    $initial = mb_strtoupper(mb_substr($me->name, 0, 1));
@endphp
<body>
    <div class="adm-shell">
        <aside class="adm-side" id="adm-side" aria-label="Меню">
            <a href="{{ route('admin.today') }}" class="adm-brand"><img src="{{ asset('images/logo-g-dark-224.webp') }}" srcset="{{ asset('images/logo-g-dark-224.webp') }} 1x, {{ asset('images/logo-g-dark-352.webp') }} 2x" alt="Creatium Lab"></a>
            <a href="{{ route('admin.profile.edit') }}" class="adm-who" title="Моят профил">
                <span class="adm-avatar">{{ $initial }}</span>
                <span class="min-w-0"><b class="truncate">{{ $me->name }}</b><span>{{ \App\Support\Admin\Profiles::label($profileKey) }}</span></span>
            </a>

            <nav class="adm-nav">
                @foreach($menu as $group => $items)
                    @if($group !== '')<p class="adm-group">{{ $group }}</p>@endif
                    @foreach($items as $item)
                        @php $count = \App\Support\Admin\Badges::get($item['badge']); @endphp
                        <a href="{{ route($item['route']) }}" class="adm-link" @if($isActive($item)) aria-current="page" @endif>
                            <x-icon name="{{ $item['icon'] }}" />
                            <span class="adm-label">{{ $item['label'] }}</span>
                            @if($count > 0)<span class="adm-badge">{{ $count }}</span>@endif
                        </a>
                    @endforeach
                @endforeach

                @if($others)
                    {{-- Разделите извън профила не са заключени: стоят тук и излизат в търсенето (Ctrl+K). --}}
                    <details class="adm-more" @if($moreOpen) open @endif>
                        <summary>Всички раздели <x-icon name="chevron-down" /></summary>
                        @foreach($others as $group => $items)
                            @if($group !== '')<p class="adm-group">{{ $group }}</p>@endif
                            @foreach($items as $item)
                                @php $count = \App\Support\Admin\Badges::get($item['badge']); @endphp
                                <a href="{{ route($item['route']) }}" class="adm-link" @if($isActive($item)) aria-current="page" @endif>
                                    <x-icon name="{{ $item['icon'] }}" />
                                    <span class="adm-label">{{ $item['label'] }}</span>
                                    @if($count > 0)<span class="adm-badge">{{ $count }}</span>@endif
                                </a>
                            @endforeach
                        @endforeach
                    </details>
                @endif
            </nav>

            <div class="adm-side-foot">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="adm-link"><x-icon name="home" /><span class="adm-label">Към сайта</span><x-icon name="arrow-up-right-from-square" /></a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="adm-link w-full text-left"><x-icon name="sign-out-alt" /><span class="adm-label">Изход</span></button>
                </form>
            </div>
        </aside>
        <div class="adm-scrim" data-adm-close></div>

        <div class="adm-main">
            <header class="adm-top">
                <button type="button" class="adm-burger" data-adm-toggle aria-label="Менюто" aria-controls="adm-side"><x-icon name="bars" class="text-xl" /></button>
                <p class="adm-crumb">@if($currentGroup && $currentGroup !== '')<span>{{ $currentGroup }}</span> / @endif<b>{{ $title }}</b></p>

                <form action="{{ route('admin.search') }}" method="GET" role="search" class="adm-search">
                    <x-icon name="search" />
                    <input type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" placeholder="Търсене във всичко" aria-label="Търсене" data-global-search>
                    <kbd>Ctrl K</kbd>
                </form>
                <a href="{{ route('admin.search') }}" class="adm-search-icon" aria-label="Търсене"><x-icon name="search" class="text-lg" /></a>

                <details class="adm-menu adm-hide-sm" data-adm-menu>
                    <summary class="adm-btn sm"><x-icon name="plus" /> Ново</summary>
                    <div class="adm-pop">
                        @foreach($quick as $action)<a href="{{ route($action[0]) }}">{{ $action[1] }}</a>@endforeach
                    </div>
                </details>

                @if(\App\Support\Design::configured() !== 'g')
                    {{-- Новият дизайн (концепция Г): прегледът за влезлите и линк за 30 дни за хора без профил в админа. --}}
                    @php $designLink = \Illuminate\Support\Facades\URL::temporarySignedRoute('design.link', now()->addDays(30)); @endphp
                    <details class="adm-menu adm-hide-sm" data-adm-menu>
                        <summary class="adm-btn ghost sm">Нов дизайн <x-icon name="chevron-down" class="text-[10px]" /></summary>
                        <div class="adm-pop">
                            <a href="{{ route('home') }}?dizain=g" target="_blank" rel="noopener" data-new-design>Отвори прегледа</a>
                            <button type="button" data-copy-design-link="{{ $designLink }}" title="Линк, с който всеки вижда новия дизайн 30 дни, без вход">Копирай линк за преглед</button>
                        </div>
                    </details>
                    <script>
                        document.querySelectorAll('[data-copy-design-link]').forEach(function (b) {
                            b.addEventListener('click', function () {
                                var link = b.getAttribute('data-copy-design-link');
                                var done = function () { var t = b.textContent; b.textContent = 'Копирано'; setTimeout(function () { b.textContent = t; }, 1800); };
                                if (navigator.clipboard) { navigator.clipboard.writeText(link).then(done, function () { window.prompt('Копирайте линка', link); }); } else { window.prompt('Копирайте линка', link); }
                            });
                        });
                    </script>
                @endif

                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="adm-btn ghost sm adm-hide-sm"><x-icon name="home" /> Към сайта</a>

                <details class="adm-menu" data-adm-menu>
                    <summary class="adm-me" aria-label="Профил"><span class="adm-avatar">{{ $initial }}</span></summary>
                    <div class="adm-pop">
                        <small>{{ $me->name }} · {{ \App\Support\Admin\Profiles::label($profileKey) }}</small>
                        <a href="{{ route('admin.profile.edit') }}"><x-icon name="user-gear" /> Моят профил</a>
                        <hr>
                        <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit"><x-icon name="sign-out-alt" /> Изход</button></form>
                    </div>
                </details>
            </header>

            <main class="adm-content">
                @if(session('success'))
                    <div class="adm-flash ok" role="status">{{ session('success') }}</div>
                @endif

                @if($errors->any() && request()->routeIs('admin.posts.*', 'admin.post-categories.*', 'admin.content.*', 'admin.seo.*', 'admin.profile.*', 'admin.client-emails.*'))
                    <div class="adm-flash err" role="alert">
                        <div>
                            <p class="font-semibold">Има грешки във формата:</p>
                            <ul class="list-disc pl-5 mt-1 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="adm-flash err" role="alert">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Долно меню на телефона: най-честите неща с палеца, според профила. --}}
    <nav class="adm-bottom" aria-label="Бързо меню">
        @foreach($bottom as $item)
            @php $count = \App\Support\Admin\Badges::get($item['badge']); @endphp
            <a href="{{ route($item['route']) }}" @if($isActive($item)) aria-current="page" @endif>
                <x-icon name="{{ $item['icon'] }}" />{{ $item['label'] }}
                @if($count > 0)<span class="adm-badge">{{ $count }}</span>@endif
            </a>
        @endforeach
        <button type="button" data-bottom-menu data-adm-toggle><x-icon name="bars" />Меню</button>
    </nav>

    <script>
        (function () {
            var body = document.body;
            document.querySelectorAll('[data-adm-toggle]').forEach(function (b) { b.addEventListener('click', function () { body.classList.toggle('adm-open'); }); });
            document.querySelectorAll('[data-adm-close]').forEach(function (b) { b.addEventListener('click', function () { body.classList.remove('adm-open'); }); });
            document.querySelectorAll('.adm-side a').forEach(function (a) { a.addEventListener('click', function () { body.classList.remove('adm-open'); }); });

            // Само едно падащо меню е отворено; затваря се с клик отвън и с Esc.
            var menus = document.querySelectorAll('[data-adm-menu]');
            menus.forEach(function (m) { m.addEventListener('toggle', function () { if (m.open) menus.forEach(function (o) { if (o !== m) o.open = false; }); }); });
            document.addEventListener('click', function (e) { menus.forEach(function (m) { if (m.open && !m.contains(e.target)) m.open = false; }); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { menus.forEach(function (m) { m.open = false; }); body.classList.remove('adm-open'); }
                // Ctrl+K (или Cmd+K) отваря търсенето отвсякъде.
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    var box = document.querySelector('[data-global-search]');
                    if (box && box.offsetParent !== null) { box.focus(); box.select(); } else { location.href = @json(route('admin.search')); }
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
