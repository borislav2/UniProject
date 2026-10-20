@php
    // Слайдове 1600×900. Същото съдържание като на сайта (site()), затова промените от админа влизат и тук.
    // PDF: node tools/presentation/render.mjs (вижте файла). Страницата е noindex.
    $title = e($hero['title']);
    foreach (array_filter((array) ($hero['accent'] ?? [])) as $word) {
        $title = preg_replace('/(?<![\p{L}])(' . preg_quote(e($word), '/') . ')(?![\p{L}])/u', '<span class="text-accent">$1</span>', $title, 1);
    }
    $engineNodes = array_values(array_map(function ($card) {
        preg_match('/^(.+?)\s*\((.+)\)\s*$/u', $card['label'], $m);
        return ['icon' => $card['icon'], 'name' => $m[1] ?? $card['label'], 'tag' => $m[2] ?? null];
    }, $engineCards));
    // Домейнът от имейла (creatiumlab.com), за да е верен и когато PDF-ът се прави от локален сървър.
    $site = \Illuminate\Support\Str::after($contact['email'], '@');
    $slides = 10;
    $page = 1;
    $n = function () use (&$page, $slides) { return str_pad((string) ++$page, 2, '0', STR_PAD_LEFT) . ' / ' . str_pad((string) $slides, 2, '0', STR_PAD_LEFT); };
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Creatium Lab: презентация') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: 1600px 900px; margin: 0; }
        html, body { margin: 0; background: #0b1422; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .slide { position: relative; width: 1600px; height: 900px; overflow: hidden; break-after: page; page-break-after: always; margin: 0 auto 24px; }
        .slide:last-child { break-after: auto; page-break-after: auto; }
        @media print { html, body { background: #fff; } .slide { margin: 0; } }
        /* Gradient text and blur turn into boxes and megabytes in PDF: plain colors here. */
        .text-accent { background: none; color: #8fb5e0; }
        .numeral { background: none !important; color: #2d63a3; }
        @media screen and (max-width: 1640px) { body { zoom: calc(100vw / 1640px); } }
        .slide-pad { padding: 96px 112px; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; }
        .slide-foot { position: absolute; left: 112px; right: 112px; bottom: 44px; display: flex; justify-content: space-between; font-size: 15px; font-weight: 600; letter-spacing: 0.06em; }
    </style>
</head>
<body class="font-sans antialiased">

{{-- 1. Cover --}}
<section class="slide surface-night text-white on-dark">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_70%_30%,black_20%,transparent_70%)]"></div>
    <div class="relative slide-pad justify-between">
        <img src="{{ asset('images/logo-white.webp') }}" alt="Creatium Lab" class="h-12 w-auto self-start">
        <div>
            <p class="kicker text-base">{{ $trust['eyebrow'] }}</p>
            <h1 class="display mt-8 text-[6.5rem] leading-[1.02] max-w-[72rem]">{!! $title !!}</h1>
            <p class="mt-8 text-3xl font-bold text-brand-200 max-w-[60rem]">{{ $hero['highlight'] }}</p>
        </div>
        <div class="flex gap-16 text-xl">
            <span class="font-bold">{{ $site }}</span>
            <span class="text-gray-300">{{ $contact['email'] }}</span>
            <span class="text-gray-300">{{ $contact['phones'][0] ?? '' }}</span>
        </div>
    </div>
</section>

{{-- 2. Problem --}}
<section class="slide bg-white">
    <div class="slide-pad">
        <p class="kicker kicker-light text-base">{{ __('Проблемът') }}</p>
        <h2 class="display mt-6 text-6xl text-brand-950 max-w-[70rem]">{{ $problem['title'] }}</h2>
        <div class="mt-auto grid grid-cols-3 gap-8">
            @foreach($problem['scenarios'] as $scenario)
                <div class="rounded-3xl p-10 {{ $loop->last ? 'bg-brand-950 text-white' : 'border border-gray-200 bg-gray-50' }}">
                    <span class="text-sm font-bold uppercase tracking-[0.2em] {{ $loop->last ? 'text-emerald-300' : 'text-red-700' }}">{{ $scenario['label'] }}</span>
                    <h3 class="mt-5 text-3xl font-extrabold tracking-tight leading-tight {{ $loop->last ? '' : 'text-brand-950' }}">{{ $scenario['title'] }}</h3>
                    <p class="mt-5 text-lg leading-relaxed {{ $loop->last ? 'text-gray-300' : 'text-gray-600' }}">{{ $scenario['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="slide-foot text-gray-400"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 3. The engine --}}
<section class="slide surface-night text-white on-dark">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]"></div>
    <div class="relative slide-pad">
        <p class="kicker text-base">{{ $trust['engine_eyebrow'] }}</p>
        <h2 class="display mt-6 text-6xl max-w-[60rem]">{{ $engine['title'] }}</h2>
        <div class="relative mt-auto mb-10 grid grid-cols-2 gap-x-[26rem] gap-y-20">
            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none"><g stroke="rgb(143 181 224 / 0.5)" stroke-width="2" stroke-dasharray="6 8" fill="none" vector-effect="non-scaling-stroke"><path d="M50 50 L22 22" vector-effect="non-scaling-stroke"/><path d="M50 50 L78 22" vector-effect="non-scaling-stroke"/><path d="M50 50 L22 78" vector-effect="non-scaling-stroke"/><path d="M50 50 L78 78" vector-effect="non-scaling-stroke"/></g></svg>
            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-10 w-60 h-60 rounded-full bg-brand-gradient ring-1 ring-white/25 shadow-[0_0_120px_-10px_rgb(59_119_189/0.9)] flex flex-col items-center justify-center text-center">
                <x-icon name="cogs" class="text-4xl text-brand-100" />
                <span class="mt-3 px-6 text-2xl font-extrabold leading-tight">{{ $trust['engine_center'] }}</span>
            </div>
            @foreach($engineNodes as $node)
                <div class="relative edge glass rounded-3xl p-8 flex items-center gap-6">
                    <span class="w-16 h-16 shrink-0 rounded-2xl bg-brand-500/25 flex items-center justify-center"><x-icon :name="$node['icon']" class="text-2xl text-brand-200" /></span>
                    <span>
                        <span class="block text-sm font-bold tracking-[0.2em] text-brand-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="block text-3xl font-extrabold">{{ $node['name'] }}</span>
                        @if($node['tag'])<span class="block text-lg text-gray-400">{{ $node['tag'] }}</span>@endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="slide-foot text-gray-500"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 4. What is included --}}
<section class="slide bg-brand-50">
    <div class="slide-pad">
        <p class="kicker kicker-light text-base">{{ __('Какво включва') }}</p>
        <div class="mt-10 grid grid-cols-2 gap-10 flex-1">
            @foreach(['tech', 'growth'] as $group)
                <div class="rounded-3xl bg-white p-10 border border-gray-200">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-600">{{ $engine[$group . '_tag'] }}</p>
                    <h3 class="mt-2 text-4xl font-extrabold tracking-tight text-brand-950">{{ $engine[$group . '_title'] }}</h3>
                    <ul class="mt-8 space-y-5">
                        @foreach($engine[$group . '_items'] as $item)
                            <li class="flex gap-4">
                                <span class="mt-1.5 w-6 h-6 shrink-0 rounded-full bg-brand-50 flex items-center justify-center"><x-icon name="check" class="text-xs text-brand-600" /></span>
                                <span><span class="block text-xl font-bold text-brand-950">{{ $item['title'] }}</span><span class="block text-base text-gray-600 leading-snug">{{ $item['text'] }}</span></span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
    <div class="slide-foot text-gray-400"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 5. Who we work with --}}
<section class="slide bg-white">
    <div class="slide-pad">
        <div class="grid grid-cols-12 gap-16 flex-1">
            <div class="col-span-5">
                <p class="kicker kicker-light text-base">{{ $trust['fit_eyebrow'] }}</p>
                <h2 class="display mt-6 text-6xl text-brand-950">{{ $trust['fit_title'] }}</h2>
                <p class="mt-8 text-2xl text-gray-600 leading-relaxed">{{ $trust['fit_text'] }}</p>
                @if(! empty($trust['not_items']))
                    <div class="mt-10 rounded-3xl border border-gray-200 bg-gray-50 p-8">
                        <p class="text-xl font-extrabold text-brand-950">{{ $trust['not_title'] }}:</p>
                        <ul class="mt-4 space-y-3 text-lg text-gray-600">
                            @foreach($trust['not_items'] as $not)<li class="flex gap-3"><span class="text-red-600">–</span> {{ $not }}</li>@endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <ol class="col-span-7 self-center divide-y divide-gray-200 border-y border-gray-200">
                @foreach($trust['fit_items'] as $item)
                    <li class="flex gap-8 py-8">
                        <span class="numeral !text-6xl">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span><span class="block text-3xl font-extrabold tracking-tight text-brand-950">{{ $item['title'] }}</span><span class="mt-2 block text-xl text-gray-600 leading-snug">{{ $item['text'] }}</span></span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
    <div class="slide-foot text-gray-400"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 6. Process --}}
<section class="slide surface-night text-white on-dark">
    <div class="relative slide-pad">
        <p class="kicker text-base">{{ __('Работен процес') }}</p>
        <h2 class="display mt-6 text-6xl max-w-[66rem]">{{ $process['title'] }}</h2>
        <div class="mt-auto mb-6 grid grid-cols-3 gap-6">
            @foreach($process['steps'] as $step)
                <div class="relative edge glass rounded-3xl p-8">
                    <div class="flex items-center gap-5"><span class="text-5xl font-extrabold tracking-tight text-brand-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span class="h-px flex-1 bg-white/15"></span></div>
                    <h3 class="mt-5 text-2xl font-extrabold">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-base text-gray-400 leading-snug">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="slide-foot text-gray-500"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 7. Packages --}}
<section class="slide bg-brand-50">
    <div class="slide-pad">
        <p class="kicker kicker-light text-base">{{ __('Пакети') }}</p>
        <h2 class="display mt-6 text-6xl text-brand-950">{{ __('Три пакета с ясни цени.') }}</h2>
        <div class="mt-auto grid grid-cols-3 gap-8">
            @foreach($plans['items'] as $plan)
                <div class="rounded-3xl p-10 flex flex-col {{ ! empty($plan['highlighted']) ? 'bg-brand-950 text-white shadow-2xl' : 'bg-white border border-gray-200' }}">
                    @if(filled($plan['badge'] ?? null))<span class="self-start rounded-full px-4 py-1.5 text-sm font-bold {{ ! empty($plan['highlighted']) ? 'bg-white text-brand-950' : 'bg-brand-50 text-brand-700' }}">{{ $plan['badge'] }}</span>@endif
                    <h3 class="mt-5 text-3xl font-extrabold tracking-tight leading-tight {{ empty($plan['highlighted']) ? 'text-brand-950' : '' }}">{{ $plan['name'] }}</h3>
                    <p class="mt-4 text-lg leading-snug {{ empty($plan['highlighted']) ? 'text-gray-600' : 'text-gray-300' }}">{{ $plan['audience'] }}</p>
                    <ul class="mt-auto pt-8 space-y-3">
                        @foreach((array) $plan['prices'] as $price)
                            <li class="text-xl font-bold {{ $loop->first ? 'text-2xl' : '' }}">{{ $price }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
    <div class="slide-foot text-gray-400"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 8. Industries (demos) --}}
<section class="slide surface-night text-white on-dark">
    <div class="relative slide-pad">
        <div class="flex items-end justify-between gap-10">
            <div>
                <p class="kicker text-base">{{ $trust['demos_eyebrow'] }}</p>
                <h2 class="display mt-6 text-6xl">{{ $trust['demos_title'] }}</h2>
            </div>
            <p class="max-w-[34rem] text-lg text-gray-400 leading-relaxed">{{ $trust['demos_text'] }}</p>
        </div>
        <div class="mt-auto mb-6 grid grid-cols-3 gap-6">
            @foreach(array_slice($demos, 0, 6) as $demo)
                <div class="relative edge glass rounded-2xl overflow-hidden">
                    <div class="relative h-44 overflow-hidden bg-white/5">
                        @if(is_file(public_path('images/demos/' . $demo['slug'] . '.webp')))
                            <img src="{{ asset('images/demos/' . $demo['slug'] . '.webp') }}" alt="" class="w-full h-full object-cover object-top">
                        @endif
                        <span class="absolute top-3 left-3 rounded-full bg-brand-950/85 px-3 py-1 text-xs font-bold uppercase tracking-wider ring-1 ring-white/20">{{ __('Демо') }}</span>
                    </div>
                    <div class="p-5"><span class="block text-xs font-bold uppercase tracking-[0.2em] text-brand-300">{{ $demo['industry'] }}</span><span class="mt-1 block text-xl font-extrabold">{{ $demo['name'] }}</span></div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="slide-foot text-gray-500"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 9. Principles and team --}}
<section class="slide bg-white">
    <div class="slide-pad">
        <div class="grid grid-cols-12 gap-14 flex-1">
            <div class="col-span-7">
                <p class="kicker kicker-light text-base">{{ $trust['principles_eyebrow'] }}</p>
                <h2 class="display mt-6 text-5xl text-brand-950">{{ $trust['principles_title'] }}</h2>
                <div class="mt-10 grid grid-cols-2 gap-x-10 gap-y-8">
                    @foreach($trust['principle_items'] as $item)
                        <div>
                            <span class="numeral !text-5xl">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-brand-950 leading-tight">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-base text-gray-600 leading-snug">{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-span-5 flex flex-col gap-6 justify-center">
                @foreach($team as $member)
                    <div class="flex items-center gap-6 rounded-3xl border border-gray-200 bg-gray-50 p-6">
                        @if(filled($member['image'] ?? null) && is_file(public_path($member['image'])))
                            <img src="{{ asset($member['image']) }}" alt="" class="w-28 h-28 rounded-2xl object-cover">
                        @endif
                        <div>
                            <p class="text-2xl font-extrabold text-brand-950">{{ $member['name'] }}</p>
                            <p class="mt-1 text-lg font-semibold text-brand-600">{{ $member['role'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="slide-foot text-gray-400"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 10. Contact --}}
<section class="slide surface-night text-white on-dark">
    <div class="absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_30%_70%,black_20%,transparent_70%)]"></div>
    <div class="relative slide-pad justify-between">
        <img src="{{ asset('images/logo-white.webp') }}" alt="Creatium Lab" class="h-12 w-auto self-start">
        <div>
            <p class="kicker text-base">{{ $trust['contact_eyebrow'] }}</p>
            <h2 class="display mt-6 text-7xl max-w-[66rem]">{{ __('Безплатна консултация за вашия бизнес.') }}</h2>
            <p class="mt-6 text-2xl text-gray-300 max-w-[56rem]">{{ __(':minutes минути разговор: разглеждаме сайта и присъствието ви в Google и ви казваме какво бихме направили първо.', ['minutes' => config('creatium.booking.minutes')]) }}</p>
        </div>
        <div class="grid grid-cols-3 gap-6">
            <div class="relative edge glass rounded-2xl p-6"><p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-300">{{ __('Сайт') }}</p><p class="mt-2 text-2xl font-bold">{{ $site }}</p></div>
            <div class="relative edge glass rounded-2xl p-6"><p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-300">{{ __('Имейл') }}</p><p class="mt-2 text-2xl font-bold">{{ $contact['email'] }}</p></div>
            <div class="relative edge glass rounded-2xl p-6"><p class="text-sm font-bold uppercase tracking-[0.2em] text-brand-300">{{ __('Телефон') }}</p><p class="mt-2 text-2xl font-bold">{{ implode(' · ', $contact['phones']) }}</p></div>
        </div>
    </div>
</section>

</body>
</html>
