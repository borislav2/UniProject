@extends('layouts.public')

{{-- /kontakti in design G: how to reach us (phones with call, Viber and WhatsApp) and the contact form with labels, hints and a clear thank-you state. --}}
@section('title', __('Контакти и час за разговор'))
@section('meta_description', __('Обадете се, пишете или запазете час за разговор. Кажете ни какъв е бизнесът ви и ще ви предложим какво да направим за повече клиенти.'))
@section('g_lean', 'inner')

@section('content')
@include('partials.g.page-head', ['eyebrow' => __('Контакти'), 'heading' => __('Запазете час за разговор'), 'sub' => __('Кажете ни какъв е бизнесът ви и ще ви предложим какво да направим за повече клиенти.')])

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap g-ct">
        <div class="g-ct-info">
            <div class="g-info">
                <div>
                    <span class="g-mono">{{ __('Телефон') }}</span>
                    @include('partials.g.phones', ['phones' => g_phones()])
                </div>
                <div>
                    <span class="g-mono">{{ __('Имейл') }}</span>
                    <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>
                </div>
                <div>
                    <span class="g-mono">{{ __('Град') }}</span>
                    <p>{{ __($contact['city']) }}, {{ __('България') }}</p>
                </div>
            </div>
            <p class="g-promise-box">{{ __('Отговаряме до един работен ден.') }}</p>
        </div>
        <div class="g-ct-form">@include('partials.g.contact-form')</div>
    </div>
</section>
@endsection
