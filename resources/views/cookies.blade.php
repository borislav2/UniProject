@extends('layouts.public')

@section('title', 'Политика за бисквитките')
@section('meta_description', 'Какви бисквитки използва сайтът на Creatium Lab, за какво служат и как да управлявате съгласието си.')

@section('content')
@php($tracking = (bool) config('creatium.gtm_id'))
@php($cookies = config('creatium.cookies'))
@include('partials/page-header', ['heading' => 'Политика за бисквитките'])

<section class="py-12 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 text-gray-700 leading-relaxed">
        <p>Тази политика обяснява какви бисквитки използва сайтът creatiumlab.com, за какво служат и как може да промените избора си. Как обработваме личните ви данни като цяло е описано в <a href="{{ route('privacy') }}" class="text-brand-600 underline">Политиката за поверителност</a>.</p>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">1. Какво са бисквитките</h2>
            <p>Бисквитките са малки текстови файлове, които сайтът записва в браузъра ви. Някои са нужни, за да работи сайтът. Други помагат да разберем как се използва сайтът или колко ефективни са рекламите ни. Подобни данни може да се пазят и в локалното хранилище на браузъра (localStorage), и тази политика се отнася и за тях.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">2. Какви бисквитки използваме</h2>

            <h3 class="font-semibold text-gray-900 mt-4 mb-2">Строго необходими</h3>
            <p>Без тях сайтът не може да работи правилно, затова не изискват съгласие (чл. 4а, ал. 3 от Закона за електронните съобщения).</p>
            <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-900">
                        <tr><th class="px-4 py-2.5 font-semibold">Име</th><th class="px-4 py-2.5 font-semibold">За какво е</th><th class="px-4 py-2.5 font-semibold">Срок</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr><td class="px-4 py-2.5 font-mono text-xs">{{ config('session.cookie') }}</td><td class="px-4 py-2.5">Сесия: помни, че формата е изпратена, и показва грешки в нея</td><td class="px-4 py-2.5 whitespace-nowrap">{{ config('session.lifetime') }} мин.</td></tr>
                        <tr><td class="px-4 py-2.5 font-mono text-xs">XSRF-TOKEN</td><td class="px-4 py-2.5">Защита на формите от злоупотреби</td><td class="px-4 py-2.5 whitespace-nowrap">{{ config('session.lifetime') }} мин.</td></tr>
                        @if($tracking)
                            <tr><td class="px-4 py-2.5 font-mono text-xs">creatium-consent</td><td class="px-4 py-2.5">Помни избора ви в банера за бисквитки (localStorage)</td><td class="px-4 py-2.5 whitespace-nowrap">1 година</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($tracking)
                <p class="mt-6">Аналитичните и маркетинговите бисквитки се зареждат през <strong>Google Tag Manager</strong> (Google Ireland Ltd.). Самият Tag Manager не поставя бисквитки. Докато не дадете съгласие, инструментите по-долу не записват бисквитки (използваме Google Consent Mode v2).</p>
            @endif

            @foreach(['analytics' => 'Аналитични', 'marketing' => 'Маркетингови'] as $category => $label)
                <h3 class="font-semibold text-gray-900 mt-6 mb-2">{{ $label }}</h3>
                @if($tracking && count($cookies[$category]))
                    <p>Зареждат се само ако ги разрешите в банера.</p>
                    <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-900">
                        <tr><th class="px-4 py-2.5 font-semibold">Име</th><th class="px-4 py-2.5 font-semibold">Доставчик и цел</th><th class="px-4 py-2.5 font-semibold">Срок</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                            @foreach($cookies[$category] as $cookie)
                                <tr><td class="px-4 py-2.5 font-mono text-xs whitespace-nowrap">{{ $cookie['name'] }}</td><td class="px-4 py-2.5"><strong class="font-semibold">{{ $cookie['provider'] }}</strong>: {{ $cookie['purpose'] }}</td><td class="px-4 py-2.5 whitespace-nowrap">{{ $cookie['duration'] }}</td></tr>
                            @endforeach
                    </tbody>
                </table>
            </div>
                @else
                    <p>В момента не използваме {{ mb_strtolower($label) }} бисквитки. Ако започнем, ще ги заредим само след ваше съгласие и ще ги опишем тук.</p>
                @endif
            @endforeach
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">3. Как да промените избора си</h2>
            @if($tracking)
                <p>Може да промените или оттеглите съгласието си по всяко време от <a href="#" data-cookie-settings class="text-brand-700 underline">Настройки за бисквитки</a> (линкът е и най-долу на всяка страница). Изборът ви важи 12 месеца, след което ще ви попитаме отново.</p>
            @endif
            <p class="mt-3">Може и да изтриете или блокирате бисквитките от настройките на браузъра си. Ако блокирате строго необходимите, контактната форма може да не работи.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">4. Въпроси</h2>
            <p>Пишете ни на <a href="mailto:{{ config('creatium.contact.email') }}" class="text-brand-600 underline">{{ config('creatium.contact.email') }}</a>.</p>
        </div>

        <p class="text-sm text-gray-500">Последна промяна: 02.10.2026</p>
    </div>
</section>
@endsection
