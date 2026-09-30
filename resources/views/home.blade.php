@extends('layouts.public')

@section('title', 'Уебсайтове и маркетинг за бизнеса в България')
@section('meta_description', 'Правим сайтове за ресторанти, салони, кабинети, сервизи и малки магазини и се грижим хората да ги намират в Google. Първата консултация е безплатна.')

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white">
    <div class="absolute inset-0 bg-grid [mask-image:radial-gradient(ellipse_at_top,black_30%,transparent_75%)]" aria-hidden="true"></div>
    <div class="absolute -top-40 -right-32 w-[34rem] h-[34rem] rounded-full bg-brand-300/30 blur-3xl" aria-hidden="true"></div>
    <div class="absolute top-40 -left-40 w-[26rem] h-[26rem] rounded-full bg-brand-100/60 blur-3xl" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-20 md:pt-24 md:pb-28 grid lg:grid-cols-2 gap-14 items-center">
        <div class="reveal">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-4 py-1.5 text-xs font-semibold text-brand-700 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Сайтове и SEO за малки фирми
            </span>
            <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-brand-950 leading-[1.05]">
                Уебсайт, който ви носи <span class="text-gradient">клиенти.</span>
            </h1>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-xl">
                Правим сайтове за малки фирми и се грижим хората да ги намират в Google.
                Ние сме двама и с вас говори точно човекът, който върши работата.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="#kontakt" class="inline-flex items-center justify-center gap-2 bg-brand-950 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-brand-800 transition-colors shadow-lg shadow-brand-950/20">
                    Поискайте оферта <x-icon name="arrow-right" class="text-sm" />
                </a>
                <a href="{{ route('services') }}" class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 text-brand-950 px-7 py-3.5 rounded-xl font-semibold hover:border-brand-300 hover:text-brand-700 transition-colors">
                    Вижте услугите
                </a>
            </div>
            <ul class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm text-gray-600">
                <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-brand-500" />Първата консултация е безплатна</li>
                <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-brand-500" />Отговаряме до един работен ден</li>
                <li class="flex items-center gap-2"><x-icon name="circle-check" class="text-brand-500" />Без посредници</li>
            </ul>
        </div>

        {{-- Illustration: a website with an incoming inquiry --}}
        <div class="relative reveal" style="--reveal-delay: 150ms" aria-hidden="true">
            <div class="relative rounded-2xl bg-white shadow-2xl shadow-brand-950/15 ring-1 ring-gray-200/70 overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 bg-gray-50/80">
                    <div class="flex gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-300"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-300"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-300"></span>
                    </div>
                    <div class="flex-1 flex items-center gap-2 rounded-md bg-white border border-gray-200 px-3 py-1 text-xs text-gray-500">
                        <x-icon name="lock" class="text-[10px] text-emerald-500" /> vashiat-biznes.bg
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="h-3 w-24 rounded bg-brand-950/80"></div>
                        <div class="flex gap-3">
                            <div class="h-2 w-10 rounded bg-gray-200"></div>
                            <div class="h-2 w-10 rounded bg-gray-200"></div>
                            <div class="h-2 w-10 rounded bg-gray-200"></div>
                        </div>
                    </div>
                    <div class="rounded-xl bg-brand-gradient p-6">
                        <div class="h-3.5 w-3/4 rounded bg-white/85 mb-3"></div>
                        <div class="h-3.5 w-1/2 rounded bg-white/60 mb-5"></div>
                        <div class="h-8 w-32 rounded-lg bg-white"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['fa-display', 'fa-magnifying-glass', 'fa-location-dot'] as $icon)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                                <div class="w-7 h-7 rounded-lg bg-brand-100 flex items-center justify-center mb-2"><x-icon :name="$icon" class="text-xs text-brand-600" /></div>
                                <div class="h-2 w-full rounded bg-gray-200 mb-1.5"></div>
                                <div class="h-2 w-2/3 rounded bg-gray-200"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="absolute -top-6 right-2 sm:-right-6 animate-float rounded-xl bg-white shadow-xl ring-1 ring-gray-200/70 px-4 py-3 flex items-center gap-3">
                <span class="relative flex w-9 h-9 rounded-full bg-emerald-50 items-center justify-center">
                    <x-icon name="bell" class="text-emerald-600 text-sm" />
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </span>
                <span class="text-sm leading-tight"><strong class="block text-brand-950">Ново запитване</strong><span class="text-gray-500 text-xs">от контактната форма</span></span>
            </div>

            <div class="absolute -bottom-6 left-2 sm:-left-8 rounded-xl bg-brand-950 text-white shadow-xl px-5 py-3.5 flex items-center gap-3">
                <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center"><x-icon name="chart-line" class="text-brand-300" /></span>
                <span class="text-sm font-semibold leading-tight">Дизайн, код<br>и SEO</span>
            </div>
        </div>
    </div>
</section>

{{-- Industries --}}
<section class="border-y border-gray-100 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col lg:flex-row lg:items-center gap-5">
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Подходящо за</span>
        <div class="flex flex-wrap gap-2.5">
            @foreach($industries as $industry)
                <span class="inline-flex items-center gap-2 rounded-full bg-gray-50 border border-gray-100 px-3.5 py-1.5 text-sm text-gray-700">
                    <x-icon :name="$industry['icon']" class="text-brand-500 text-xs" />{{ $industry['name'] }}
                </span>
            @endforeach
        </div>
    </div>
</section>

{{-- Services --}}
<section id="uslugi" class="py-20 md:py-28 bg-white scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider">Услуги</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950">С какво можем да помогнем</h2>
        </div>

        <div class="mt-14 grid md:grid-cols-2 gap-6">
            @foreach($services as $service)
                <div class="reveal card-hover group rounded-2xl border border-gray-200 bg-white p-7 flex flex-col" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <div class="w-12 h-12 rounded-xl bg-brand-gradient flex items-center justify-center shadow-lg shadow-brand-900/20">
                        <x-icon :name="$service['icon']" class="text-white" />
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-brand-950">{{ $service['title'] }}</h3>
                    <p class="mt-3 text-gray-600 leading-relaxed">{{ $service['description'] }}</p>
                    <ul class="mt-5 space-y-2 flex-1">
                        @foreach($service['points'] as $point)
                            <li class="text-sm text-gray-700 flex items-center gap-2.5">
                                <x-icon name="check" class="text-brand-500 text-xs" />{{ $point }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('services') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-brand-700 group-hover:gap-3 transition-all">
                        Вижте повече <x-icon name="arrow-right" class="text-xs" />
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Process --}}
<section id="proces" class="relative py-20 md:py-28 bg-brand-50/60 scroll-mt-20 overflow-hidden">
    <div class="absolute inset-0 bg-grid opacity-60 [mask-image:linear-gradient(to_bottom,transparent,black_30%,black_70%,transparent)]" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider">Как работим</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950">Как протича работата</h2>
        </div>

        <ol class="mt-14 relative grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="hidden lg:block absolute top-6 left-12 right-12 h-px bg-gradient-to-r from-brand-200 via-brand-400 to-brand-200" aria-hidden="true"></div>
            @foreach($process as $step)
                <li class="relative reveal" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <div class="relative w-12 h-12 rounded-full bg-white ring-4 ring-brand-50 border border-brand-200 flex items-center justify-center text-brand-700 font-extrabold shadow-sm">
                        {{ $step['step'] }}
                    </div>
                    <h3 class="mt-5 text-lg font-bold text-brand-950">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-gray-600 leading-relaxed">{{ $step['description'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Packages --}}
<section id="paketi" class="py-20 md:py-28 bg-white scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center reveal">
            <span class="text-brand-600 text-sm font-bold uppercase tracking-wider">Пакети</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-brand-950">Откъде да започнете</h2>
            <p class="mt-4 text-gray-600">Цената зависи от това колко страници и какво съдържание ви трябва. Кажете ни и ще ви дадем точна оферта.</p>
        </div>

        <div class="mt-14 grid md:grid-cols-3 gap-6 items-stretch">
            @foreach($packages as $package)
                @if($package['highlighted'])
                    <div class="reveal relative rounded-2xl bg-brand-950 text-white p-8 flex flex-col shadow-2xl shadow-brand-950/30 md:-my-4 overflow-hidden" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                        <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
                        <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
                        <div class="relative flex flex-col flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold">{{ $package['name'] }}</h3>
                                <span class="rounded-full bg-brand-500/20 text-brand-200 text-xs font-semibold px-3 py-1 ring-1 ring-brand-400/30">Препоръчан</span>
                            </div>
                            <p class="mt-2 text-gray-300">{{ $package['description'] }}</p>
                            <p class="mt-6 text-2xl font-extrabold">{{ $package['price_note'] }}</p>
                            <ul class="mt-6 space-y-3 flex-1">
                                @foreach($package['features'] as $feature)
                                    <li class="text-sm flex items-center gap-3 text-gray-200">
                                        <span class="w-5 h-5 rounded-full bg-brand-500/25 flex items-center justify-center"><x-icon name="check" class="text-brand-200 text-[10px]" /></span>{{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                            <a href="#kontakt" class="mt-8 text-center px-6 py-3.5 rounded-xl font-semibold bg-white text-brand-950 hover:bg-brand-50 transition-colors">Поискайте оферта</a>
                        </div>
                    </div>
                @else
                    <div class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-8 flex flex-col" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                        <h3 class="text-xl font-bold text-brand-950">{{ $package['name'] }}</h3>
                        <p class="mt-2 text-gray-600">{{ $package['description'] }}</p>
                        <p class="mt-6 text-2xl font-extrabold text-brand-950">{{ $package['price_note'] }}</p>
                        <ul class="mt-6 space-y-3 flex-1">
                            @foreach($package['features'] as $feature)
                                <li class="text-sm flex items-center gap-3 text-gray-700">
                                    <span class="w-5 h-5 rounded-full bg-brand-50 flex items-center justify-center"><x-icon name="check" class="text-brand-600 text-[10px]" /></span>{{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="#kontakt" class="mt-8 text-center px-6 py-3.5 rounded-xl font-semibold border border-gray-200 text-brand-950 hover:border-brand-400 hover:text-brand-700 transition-colors">Поискайте оферта</a>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- Contact --}}
<section id="kontakt" class="relative py-20 md:py-28 bg-brand-gradient text-white scroll-mt-20 overflow-hidden">
    <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
    <div class="absolute -bottom-40 -left-32 w-[30rem] h-[30rem] rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
        <div class="reveal">
            <span class="text-brand-300 text-sm font-bold uppercase tracking-wider">Контакт</span>
            <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight">Пишете ни.</h2>
            <p class="mt-5 text-lg text-brand-100/90 max-w-md">
                Кажете ни с какво се занимавате и какво търсите. Ще ви отговорим до един работен ден, а първият разговор е безплатен.
            </p>
            <ul class="mt-8 space-y-4">
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="envelope" class="text-brand-200" /></span>
                    <a href="mailto:{{ $contact['email'] }}" class="hover:underline">{{ $contact['email'] }}</a>
                </li>
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="phone" class="text-brand-200" /></span>
                    {{ $contact['phone'] }}
                </li>
                <li class="flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center"><x-icon name="location-dot" class="text-brand-200" /></span>
                    {{ $contact['city'] }}, България
                </li>
            </ul>
        </div>

        <div class="reveal shadow-2xl shadow-black/30 rounded-2xl" style="--reveal-delay: 120ms">
            @include('partials/contact-form')
        </div>
    </div>
</section>
@endsection
