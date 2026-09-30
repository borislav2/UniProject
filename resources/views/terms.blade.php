@extends('layouts.public')

@section('title', 'Условия за ползване')
@section('meta_description', 'Условия за ползване на сайта на Creatium Lab.')

@section('content')
@include('partials/page-header', ['heading' => 'Условия за ползване'])

<section class="py-12 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 text-gray-700 leading-relaxed">
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">1. Общи положения</h2>
            <p>С използването на сайта creatiumlab.com приемате тези условия. Информацията в сайта е с обща информативна цел и не представлява обвързваща оферта. Конкретните услуги, цени и срокове се уговарят в отделен договор или потвърдена оферта.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">2. Интелектуална собственост</h2>
            <p>Текстовете, дизайнът, логото и другото съдържание в сайта принадлежат на Creatium Lab и не могат да се копират или използват без наше писмено съгласие.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">3. Отговорност</h2>
            <p>Стараем се информацията да е точна и актуална, но не гарантираме, че сайтът ще работи без прекъсвания или грешки. Не носим отговорност за съдържанието на външни сайтове, към които има препратки.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">4. Лични данни</h2>
            <p>Как обработваме личните ви данни е описано в <a href="{{ route('privacy') }}" class="text-blue-600 underline">Политиката за поверителност</a>.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">5. Приложимо право</h2>
            <p>За тези условия се прилага българското право. Въпроси: <a href="mailto:{{ $contact['email'] }}" class="text-blue-600 underline">{{ $contact['email'] }}</a>.</p>
        </div>
    </div>
</section>
@endsection
