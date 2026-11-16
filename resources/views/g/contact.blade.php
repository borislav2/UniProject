@extends('layouts.public')

{{-- /kontakti in design G: the one contact form (third round, 16.11.2026) with the phones (call, Viber and WhatsApp) beside it. --}}
@section('title', __('Контакти и час за разговор'))
@section('meta_description', __('Обадете се, пишете или запазете час за разговор. Кажете ни какъв е бизнесът ви и ще ви предложим какво да направим за повече клиенти.'))
@section('g_lean', 'inner')

@section('content')
@include('partials.g.contact-section', ['anchor' => 'kontakt', 'asH1' => true, 'full' => true])
@endsection
