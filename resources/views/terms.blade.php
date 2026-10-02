@extends('layouts.public')

@section('title', 'Общи условия')
@section('meta_description', 'Общи условия за ползване на сайта на Creatium Lab и за запитванията, изпратени през него.')

@section('content')
@include('partials/page-header', ['heading' => 'Общи условия'])

<section class="py-12 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 text-gray-700 leading-relaxed">
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">1. Общи положения</h2>
            <p>С използването на сайта creatiumlab.com приемате тези условия. Информацията в сайта е с обща информативна цел и не представлява обвързваща оферта. Конкретните услуги, цени и срокове се уговарят в отделен договор или потвърдена оферта.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">2. Запитвания и оферти</h2>
            <p>Изпращането на запитване през формата не ви задължава с нищо и не създава договор. След разговора ви изпращаме писмена оферта с обхвата, цената и срока. Работата започва, след като я потвърдите. Пакетите и цените в сайта са ориентировъчни, а валидни са тези в потвърдената оферта.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">3. Интелектуална собственост</h2>
            <p>Текстовете, дизайнът, логото и другото съдържание в сайта принадлежат на Creatium Lab и не могат да се копират или използват без наше писмено съгласие.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">4. Отговорност</h2>
            <p>Стараем се информацията да е точна и актуална, но не гарантираме, че сайтът ще работи без прекъсвания или грешки. Не носим отговорност за съдържанието на външни сайтове, към които има препратки.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">5. Лични данни и бисквитки</h2>
            <p>Как обработваме личните ви данни е описано в <a href="{{ route('privacy') }}" class="text-brand-600 underline">Политиката за поверителност</a>, а какви бисквитки използваме, в <a href="{{ route('cookies') }}" class="text-brand-600 underline">Политиката за бисквитките</a>.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">6. Приложимо право</h2>
            <p>За тези условия се прилага българското право. Въпроси: <a href="mailto:{{ $contact['email'] }}" class="text-brand-600 underline">{{ $contact['email'] }}</a>.</p>
        </div>
        <p class="text-sm text-gray-500">Последна промяна: 02.10.2026</p>
    </div>
</section>
@endsection
