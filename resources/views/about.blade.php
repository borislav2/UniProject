@extends('layouts.public')

@section('title', 'За нас')
@section('meta_description', 'Creatium Lab сме двама: единият прави сайтовете, другият се грижи хората да ги намират в Google. Работим с малки фирми в България.')

@section('content')
@include('partials/page-header', ['heading' => 'За нас', 'sub' => 'Двама души, които правят сайтове за малки фирми.'])

<section class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 text-lg text-gray-700">
        <p>Ние сме двама. Единият прави сайтовете, другият се занимава с маркетинг и SEO. Затова мислим за това как ще ви намират в Google още докато правим сайта, а не след като е готов.</p>
        <p>Работим директно с вас. Ако имате въпрос, пишете или звъннете и ще говорите с човека, който прави сайта ви.</p>
        <p>Creatium Lab е нов и сега правим първите си проекти. Това значи, че всеки клиент получава цялото ни внимание.</p>
    </div>
</section>

<section class="py-16 md:py-20 bg-brand-50/60">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-10">Екипът</h2>
        <div class="grid md:grid-cols-2 gap-8">
            @foreach($team as $member)
                <div class="bg-white rounded-2xl border border-gray-200 p-7 card-hover reveal">
                    @if(!empty($member['name']))
                        <h3 class="text-xl font-bold">{{ $member['name'] }}</h3>
                        <p class="text-brand-600 font-semibold mb-3">{{ $member['role'] }}</p>
                    @else
                        <h3 class="text-xl font-bold text-brand-600 mb-3">{{ $member['role'] }}</h3>
                    @endif
                    <p class="text-gray-600">{{ $member['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-10">Какво можете да очаквате</h2>
        <div class="grid sm:grid-cols-3 gap-8 text-center">
            <div><i class="fas fa-reply text-brand-600 text-2xl mb-3"></i><h3 class="font-semibold mb-1">Отговаряме бързо</h3><p class="text-sm text-gray-600">До един работен ден, по телефона или по имейл.</p></div>
            <div><i class="fas fa-tag text-brand-600 text-2xl mb-3"></i><h3 class="font-semibold mb-1">Цената е ясна предварително</h3><p class="text-sm text-gray-600">Знаете колко ще струва, преди да започнем работа.</p></div>
            <div><i class="fas fa-screwdriver-wrench text-brand-600 text-2xl mb-3"></i><h3 class="font-semibold mb-1">Оставаме и след това</h3><p class="text-sm text-gray-600">Ако трябва да смените нещо или нещо спре да работи, пишете ни.</p></div>
        </div>
    </div>
</section>

@include('partials/cta')
@endsection
