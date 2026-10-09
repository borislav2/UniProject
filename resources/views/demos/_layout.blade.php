{{-- Shared frame for the demo sites on the Projects page: not indexed, marked as a demo, forms send nothing. --}}
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $demo['name'] }} · демо сайт | Creatium Lab</title>
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
            <p><span class="mr-2 rounded bg-white/15 px-1.5 py-0.5 font-bold uppercase tracking-wider text-[10px]">Демо</span>Примерен сайт от Creatium Lab. Бизнесът е измислен.</p>
            <a href="{{ route('portfolio') }}#demo" class="shrink-0 font-semibold underline underline-offset-2 hover:text-[#9cc0ff]">Към проектите</a>
        </div>
    </div>

    @yield('content')

    <div class="bg-[#0f1a2b] text-white">
        <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
            <p class="text-gray-300">Този сайт е демо, изработено от <strong class="text-white">Creatium Lab</strong>.</p>
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 font-semibold text-[#0f1a2b] hover:bg-[#e6eefc]">Искате такъв сайт? Пишете ни <x-icon name="arrow-right" class="text-xs" /></a>
        </div>
    </div>

    <div id="demo-toast" role="status" aria-live="polite" class="fixed bottom-5 left-1/2 z-[70] hidden -translate-x-1/2 rounded-xl bg-[#0f1a2b] px-5 py-3 text-sm text-white shadow-2xl">
        Това е демо: формата не изпраща нищо. Във вашия сайт тук ще пристигат запитванията.
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
