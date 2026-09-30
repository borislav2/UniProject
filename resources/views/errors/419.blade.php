@extends('layouts.public')

@section('title', 'Сесията е изтекла')

@section('content')
<section class="py-24 text-center">
    <div class="max-w-xl mx-auto px-4">
        <h1 class="text-2xl font-bold mb-3">Сесията е изтекла</h1>
        <p class="text-gray-600 mb-8">Формата е стояла отворена твърде дълго. Върнете се назад, презаредете страницата и опитайте отново.</p>
        <a href="{{ url()->previous() }}" class="inline-block bg-brand-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-700">Назад</a>
    </div>
</section>
@endsection
