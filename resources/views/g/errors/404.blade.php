@extends('layouts.public')

@section('title', __('Страницата не е намерена'))
@section('g_lean', 'inner')

@section('content')
<section class="g-ph g-404">
    <div class="g-wrap">
        <p class="g-mono g-eyebrow">404</p>
        <h1 class="g-h">{{ __('Страницата не е намерена') }}</h1>
        <p class="g-lead">{{ __('Възможно е адресът да е променен или страницата да не съществува.') }}</p>
        <div class="g-cta">
            <a class="g-btn" href="{{ lroute('home') }}">{{ __('Към началото') }}<i></i></a>
            <a class="g-btn g-ghost" href="{{ lroute('contact') }}">{{ __('Контакти') }}</a>
        </div>
    </div>
</section>
@endsection
