@extends('layouts.public')

@section('title', 'Пакети и цени')
@section('meta_description', 'Пакети за изработка на сайт и SEO за малки фирми: Старт, Бизнес и Растеж. Разберете какво включва всеки и поискайте точна оферта.')

@section('content')
@include('partials/page-header', ['eyebrow' => 'Пакети', 'heading' => 'Откъде да започнете', 'sub' => 'Цената зависи от това колко страници и какво съдържание ви трябва. Кажете ни и ще ви дадем точна оферта.'])

<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-6 items-stretch">
            @foreach($packages as $package)
                @if($package['highlighted'])
                    <div class="reveal relative rounded-2xl bg-brand-950 text-white p-8 flex flex-col shadow-2xl shadow-brand-950/30 md:-my-4 overflow-hidden" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                        <div class="absolute inset-0 bg-grid-light" aria-hidden="true"></div>
                        <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
                        <div class="relative flex flex-col flex-1">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold">{{ $package['name'] }}</h2>
                                <span class="rounded-full bg-brand-500/20 text-brand-200 text-xs font-semibold px-3 py-1 ring-1 ring-brand-400/30">Препоръчан</span>
                            </div>
                            <p class="mt-2 text-gray-300">{{ $package['description'] }}</p>
                            <p class="mt-6 text-2xl font-extrabold">{{ $package['price_note'] }}</p>
                            <ul class="mt-6 space-y-3 flex-1">
                                @foreach($package['features'] as $feature)
                                    <li class="text-sm flex items-center gap-3 text-gray-200">
                                        <span class="w-5 h-5 shrink-0 rounded-full bg-brand-500/25 flex items-center justify-center"><x-icon name="check" class="text-brand-200 text-[10px]" /></span>{{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('contact') }}" class="mt-8 text-center px-6 py-3.5 rounded-xl font-semibold bg-white text-brand-950 hover:bg-brand-50 transition-colors">Свържете се с нас</a>
                        </div>
                    </div>
                @else
                    <div class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-8 flex flex-col" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                        <h2 class="text-xl font-bold text-brand-950">{{ $package['name'] }}</h2>
                        <p class="mt-2 text-gray-600">{{ $package['description'] }}</p>
                        <p class="mt-6 text-2xl font-extrabold text-brand-950">{{ $package['price_note'] }}</p>
                        <ul class="mt-6 space-y-3 flex-1">
                            @foreach($package['features'] as $feature)
                                <li class="text-sm flex items-center gap-3 text-gray-700">
                                    <span class="w-5 h-5 shrink-0 rounded-full bg-brand-50 flex items-center justify-center"><x-icon name="check" class="text-brand-600 text-[10px]" /></span>{{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('contact') }}" class="mt-8 text-center px-6 py-3.5 rounded-xl font-semibold border border-gray-200 text-brand-950 hover:border-brand-400 hover:text-brand-700 transition-colors">Свържете се с нас</a>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 md:py-20 bg-brand-50/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 reveal">Как се договаряме</h2>
        <div class="mt-10 grid md:grid-cols-3 gap-6">
            @foreach($notes as $note)
                <div class="reveal rounded-2xl border border-gray-200 bg-white p-7" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <span class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center"><x-icon :name="$note['icon']" class="text-brand-600" /></span>
                    <h3 class="mt-5 text-lg font-bold text-brand-950">{{ $note['title'] }}</h3>
                    <p class="mt-2 text-gray-600 leading-relaxed">{{ $note['text'] }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-8 text-gray-600 reveal">Пакетите са за сайт и SEO. За <a href="{{ route('services') }}#monitoring" class="text-brand-700 underline">мониторинг на сайта</a> и <a href="{{ route('services') }}#email" class="text-brand-700 underline">имейл кампании</a> правим отделна оферта според нуждите ви.</p>
    </div>
</section>

@include('partials/cta')
@endsection
