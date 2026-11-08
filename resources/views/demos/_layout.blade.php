{{-- Shared frame for the concept sites on the Projects page: not indexed, marked „Концепция“, forms send nothing. --}}
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $demo['name'] }} · концепция | Creatium Lab</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css'])
    <style>
        html { scroll-behavior: smooth; }
        .demo-serif { font-family: Georgia, 'Times New Roman', serif; }
    </style>
    @stack('head')
</head>
<body class="antialiased @yield('body_class', 'bg-white text-gray-900')">
    <div class="bg-[#0f1a2b] text-white text-xs sm:text-sm">
        <div class="max-w-6xl mx-auto px-4 py-2 flex items-center justify-between gap-3">
            <p><span class="mr-2 rounded bg-white/15 px-1.5 py-0.5 font-bold uppercase tracking-wider text-[10px]">Концепция</span>Концепция на Creatium Lab за {{ mb_strtolower($demo['industry']) }}</p>
            <span class="flex shrink-0 items-center gap-4">
                @if(! empty($demo['problems']))<a href="#za-bransha" class="hidden sm:inline font-semibold underline underline-offset-2 hover:text-[#9cc0ff]">Какво решава за бранша</a>@endif
                <a href="{{ route('portfolio') }}" class="font-semibold underline underline-offset-2 hover:text-[#9cc0ff]">Към проектите</a>
            </span>
        </div>
    </div>

    @yield('content')

    @includeWhen(! empty($demo['problems']), 'partials.demo-case')

    <div class="bg-[#0f1a2b] text-white">
        <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
            <p class="text-gray-300">Концепция за сайт, изработена от <strong class="text-white">Creatium Lab</strong>.</p>
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 font-semibold text-[#0f1a2b] hover:bg-[#e6eefc]">Искате такъв сайт? Пишете ни <x-icon name="arrow-right" class="text-xs" /></a>
        </div>
    </div>

    <div id="demo-toast" role="status" aria-live="polite" class="fixed bottom-5 left-1/2 z-[70] hidden -translate-x-1/2 rounded-xl bg-[#0f1a2b] px-5 py-3 text-sm text-white shadow-2xl">
        Формата е част от концепцията и не изпраща запитване. Във вашия сайт тук ще пристигат запитванията.
    </div>
    <script>
        document.querySelectorAll('form[data-demo-form], [data-demo-action]').forEach(function (el) {
            el.addEventListener(el.tagName === 'FORM' ? 'submit' : 'click', function (e) {
                e.preventDefault();
                var toast = document.getElementById('demo-toast');
                toast.classList.remove('hidden');
                clearTimeout(window.__demoToast);
                window.__demoToast = setTimeout(function () { toast.classList.add('hidden'); }, 3500);
            });
        });
    </script>
</body>
</html>
