@extends('layouts.public')

@section('title', 'Политика за бисквитките')
@section('meta_description', 'Какви бисквитки използва сайтът на Creatium Lab, за какво служат и как да управлявате съгласието си.')

@section('content')
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
                        @if(config('creatium.meta_pixel_id'))
                            <tr><td class="px-4 py-2.5 font-mono text-xs">creatium-cookie-consent</td><td class="px-4 py-2.5">Помни избора ви в банера за бисквитки (localStorage)</td><td class="px-4 py-2.5 whitespace-nowrap">до изтриване</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <h3 class="font-semibold text-gray-900 mt-6 mb-2">Аналитични</h3>
            <p>В момента не използваме аналитични бисквитки. Ако започнем, ще ги заредим само след ваше съгласие и ще ги опишем тук.</p>

            <h3 class="font-semibold text-gray-900 mt-6 mb-2">Маркетингови</h3>
            @if(config('creatium.meta_pixel_id'))
                <p>Зареждат се само ако ги приемете в банера.</p>
                <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-900">
                            <tr><th class="px-4 py-2.5 font-semibold">Име</th><th class="px-4 py-2.5 font-semibold">Доставчик и цел</th><th class="px-4 py-2.5 font-semibold">Срок</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr><td class="px-4 py-2.5 font-mono text-xs">_fbp, _fbc</td><td class="px-4 py-2.5">Meta Pixel (Meta Platforms Ireland Ltd.): измерване на посещенията и изпратените запитвания от реклами</td><td class="px-4 py-2.5 whitespace-nowrap">90 дни</td></tr>
                        </tbody>
                    </table>
                </div>
            @else
                <p>В момента не използваме маркетингови бисквитки. Ако започнем, ще ги заредим само след ваше съгласие и ще ги опишем тук.</p>
            @endif
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">3. Как да промените избора си</h2>
            @if(config('creatium.meta_pixel_id'))
                <p>Може да промените или оттеглите съгласието си по всяко време от <a href="#" data-cookie-settings class="text-brand-700 underline">Настройки за бисквитки</a> (линкът е и най-долу на всяка страница).</p>
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
