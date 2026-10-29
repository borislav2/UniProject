@extends('layouts.public')

@section('title', __('Контакти и безплатна консултация'))
@section('meta_description', __('Обадете се, пишете или запазете час за безплатен разговор. Кажете ни какъв е бизнесът ви и ще ви предложим какво да направим за повече клиенти.'))

@section('content')
@include('partials/page-header', ['heading' => __('Контакти')])

<section class="py-16 md:py-24 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-start">
        <div class="space-y-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-white ring-1 ring-brand-100 shadow-sm rounded-xl flex items-center justify-center shrink-0 dark:bg-white/5 dark:ring-white/10"><x-icon name="envelope" class="text-brand-600 dark:text-brand-300" /></div>
                <div><h2 class="font-semibold dark:text-white">{{ __('Имейл') }}</h2><a href="mailto:{{ $contact['email'] }}" class="text-gray-600 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">{{ $contact['email'] }}</a></div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-white ring-1 ring-brand-100 shadow-sm rounded-xl flex items-center justify-center shrink-0 dark:bg-white/5 dark:ring-white/10"><x-icon name="phone" class="text-brand-600 dark:text-brand-300" /></div>
                <div><h2 class="font-semibold dark:text-white">{{ __('Телефон') }}</h2><p class="flex flex-col text-gray-600 dark:text-gray-300">@foreach($contact['phones'] as $phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:text-brand-600 dark:hover:text-brand-300">{{ $phone }}</a>@endforeach</p></div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-white ring-1 ring-brand-100 shadow-sm rounded-xl flex items-center justify-center shrink-0 dark:bg-white/5 dark:ring-white/10"><x-icon name="location-dot" class="text-brand-600 dark:text-brand-300" /></div>
                <div><h2 class="font-semibold dark:text-white">{{ __('Град') }}</h2><p class="text-gray-600 dark:text-gray-300">{{ __($contact['city']) }}, {{ __('България') }}</p></div>
            </div>
        </div>

        <div class="min-w-0 shadow-2xl shadow-brand-950/10 rounded-2xl ring-1 ring-gray-100 reveal dark:ring-white/10 dark:shadow-black/40">
            @include('partials/contact-form')
        </div>
    </div>
</section>
@endsection
