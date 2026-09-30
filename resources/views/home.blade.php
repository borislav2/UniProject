@extends('layouts.public')

@section('title', 'Уебсайтове и маркетинг за бизнеса в България')
@section('meta_description', 'Creatium Lab изгражда бързи и красиви сайтове за ресторанти, салони, кабинети, фирми и онлайн магазини и ги подкрепя с маркетинг и SEO. Безплатна консултация.')

@section('content')

<!-- Hero Section -->
<section class="bg-gray-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-6">
                Уебсайт, който ви носи <span class="text-blue-600">клиенти.</span>
            </h1>
            <p class="text-lg text-gray-600 mb-8">
                Creatium Lab изгражда бързи и красиви сайтове за български бизнеси и ги подкрепя с маркетинг стратегия.
                Един екип от идеята до първите запитвания.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="#kontakt" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold text-center hover:bg-blue-700 transition-colors">
                    Поискайте оферта
                </a>
                <a href="{{ route('services') }}" class="border-2 border-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold text-center hover:border-blue-600 hover:text-blue-600 transition-colors">
                    Вижте услугите
                </a>
            </div>
        </div>

        <div class="relative">
            <div class="bg-white rounded-xl shadow-xl p-4">
                <div class="flex gap-1.5 mb-4">
                    <span class="w-3 h-3 rounded-full bg-gray-200"></span>
                    <span class="w-3 h-3 rounded-full bg-gray-200"></span>
                    <span class="w-3 h-3 rounded-full bg-gray-200"></span>
                </div>
                <div class="bg-blue-600 rounded-lg p-6 mb-4">
                    <div class="h-3 w-2/3 bg-white/70 rounded mb-3"></div>
                    <div class="h-3 w-1/2 bg-white/50 rounded mb-3"></div>
                    <div class="h-8 w-1/3 bg-white rounded mt-4"></div>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="h-14 bg-gray-100 rounded-lg"></div>
                    <div class="h-14 bg-gray-100 rounded-lg"></div>
                    <div class="h-14 bg-gray-100 rounded-lg"></div>
                </div>
                <div class="h-3 w-full bg-gray-100 rounded"></div>
            </div>
            <div class="absolute -bottom-6 -left-6 bg-gray-900 text-white rounded-xl shadow-lg px-5 py-3 flex items-center gap-3">
                <i class="fas fa-chart-line text-blue-400"></i>
                <span class="text-sm font-medium">Дизайн, код<br>и маркетинг</span>
            </div>
        </div>
    </div>
</section>

<!-- Industries bar -->
<section class="bg-gray-900 text-white py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-center gap-4">
        <span class="text-xs uppercase tracking-wider text-gray-400 whitespace-nowrap">Работим с бизнеси като:</span>
        <div class="flex flex-wrap gap-x-8 gap-y-2 text-gray-200 text-sm">
            @foreach($industries as $industry)
                <span>{{ $industry }}</span>
            @endforeach
        </div>
    </div>
</section>

<!-- Услуги -->
<section id="uslugi" class="py-16 md:py-20 bg-white scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-blue-600 text-sm font-semibold uppercase tracking-wider">Услуги</span>
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2 mb-12">
            Всичко, от което бизнесът ви се нуждае онлайн
        </h2>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach($services as $service)
                <div class="border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas {{ $service['icon'] }} text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $service['title'] }}</h3>
                    <p class="text-gray-600 mb-4">{{ $service['description'] }}</p>
                    <ul class="space-y-1">
                        @foreach($service['points'] as $point)
                            <li class="text-sm text-gray-600 flex items-center gap-2">
                                <i class="fas fa-check text-blue-600 text-xs"></i>{{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Как работим -->
<section id="proces" class="py-16 md:py-20 bg-blue-50 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-blue-600 text-sm font-semibold uppercase tracking-wider">Как работим</span>
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2 mb-12">
            Ясен процес, без изненади
        </h2>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($process as $step)
                <div>
                    <div class="text-3xl font-extrabold text-blue-600 mb-3">{{ $step['step'] }}</div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ $step['title'] }}</h3>
                    <p class="text-sm text-gray-600">{{ $step['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Пакети -->
<section id="paketi" class="py-16 md:py-20 bg-white scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-blue-600 text-sm font-semibold uppercase tracking-wider">Пакети</span>
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2 mb-12">
            Изберете началото си
        </h2>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach($packages as $package)
                <div class="rounded-xl p-8 flex flex-col {{ $package['highlighted'] ? 'bg-gray-900 text-white shadow-xl' : 'border border-gray-200 text-gray-900' }}">
                    <h3 class="text-xl font-bold mb-2">{{ $package['name'] }}</h3>
                    <p class="{{ $package['highlighted'] ? 'text-gray-300' : 'text-gray-600' }} mb-6">{{ $package['description'] }}</p>
                    <p class="text-2xl font-extrabold mb-6">{{ $package['price_note'] }}</p>
                    <ul class="space-y-2 mb-8 flex-1">
                        @foreach($package['features'] as $feature)
                            <li class="text-sm flex items-center gap-2 {{ $package['highlighted'] ? 'text-gray-200' : 'text-gray-600' }}">
                                <i class="fas fa-check {{ $package['highlighted'] ? 'text-blue-400' : 'text-blue-600' }} text-xs"></i>{{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="#kontakt" class="text-center px-6 py-3 rounded-lg font-semibold transition-colors {{ $package['highlighted'] ? 'bg-white text-gray-900 hover:bg-gray-100' : 'border-2 border-gray-300 hover:border-blue-600 hover:text-blue-600' }}">
                        Поискайте оферта
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Контакт -->
<section id="kontakt" class="py-16 md:py-20 bg-gray-900 text-white scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <span class="text-blue-400 text-sm font-semibold uppercase tracking-wider">Контакт</span>
            <h2 class="text-3xl md:text-4xl font-bold mt-2 mb-4">Разкажете ни за вашия бизнес.</h2>
            <p class="text-gray-300 mb-8">
                Първият разговор е безплатен и без ангажимент. Ще се свържем с вас възможно най-скоро.
            </p>
            <div class="space-y-2 text-gray-300 text-sm">
                <p><i class="fas fa-envelope mr-2 text-blue-400"></i>{{ $contact['email'] }}</p>
                <p><i class="fas fa-phone mr-2 text-blue-400"></i>{{ $contact['phone'] }}</p>
                <p><i class="fas fa-location-dot mr-2 text-blue-400"></i>{{ $contact['city'] }}, България</p>
            </div>
        </div>

        @include('partials/contact-form')
    </div>
</section>
@endsection
