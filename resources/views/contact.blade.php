@extends('layouts.public')

@section('title', 'Контакти')
@section('meta_description', 'Свържете се с Creatium Lab за безплатна консултация за уебсайт и маркетинг за вашия бизнес.')

@section('content')
@include('partials/page-header', ['heading' => 'Контакти', 'sub' => 'Разкажете ни за бизнеса си. Първият разговор е безплатен и без ангажимент.'])

<section class="py-16 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-start">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center shrink-0"><i class="fas fa-envelope text-blue-600"></i></div>
                <div><h2 class="font-semibold">Имейл</h2><a href="mailto:{{ $contact['email'] }}" class="text-gray-600 hover:text-blue-600">{{ $contact['email'] }}</a></div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center shrink-0"><i class="fas fa-phone text-blue-600"></i></div>
                <div><h2 class="font-semibold">Телефон</h2><p class="text-gray-600">{{ $contact['phone'] }}</p></div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center shrink-0"><i class="fas fa-location-dot text-blue-600"></i></div>
                <div><h2 class="font-semibold">Град</h2><p class="text-gray-600">{{ $contact['city'] }}, България</p></div>
            </div>
            <p class="text-gray-600">Отговаряме на всяко запитване, обикновено до един работен ден.</p>
        </div>

        <div class="shadow-lg rounded-xl">
            @include('partials/contact-form')
        </div>
    </div>
</section>
@endsection
