@extends('layouts.public')

@section('title', __('Сесията е изтекла'))
@section('g_lean', 'inner')

@section('content')
<section class="g-ph g-404">
    <div class="g-wrap">
        <p class="g-mono g-code">419</p>
        <h1 class="g-h">{{ __('Сесията е изтекла') }}</h1>
        <p class="g-lead">{{ __('Формата е стояла отворена твърде дълго. Върнете се назад, презаредете страницата и опитайте отново.') }}</p>
        <div class="g-cta">
            <a class="g-btn" href="{{ url()->previous() }}">{{ __('Назад') }}<i></i></a>
            <a class="g-btn g-ghost" href="{{ lroute('home') }}">{{ __('Към началото') }}</a>
        </div>
    </div>
</section>
@endsection
