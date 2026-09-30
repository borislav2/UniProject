@extends('layouts.public')

@section('title', 'Страницата не е намерена')

@section('content')
<section class="py-24 text-center">
    <div class="max-w-xl mx-auto px-4">
        <p class="text-6xl font-extrabold text-blue-600 mb-4">404</p>
        <h1 class="text-2xl font-bold mb-3">Страницата не е намерена</h1>
        <p class="text-gray-600 mb-8">Възможно е адресът да е променен или страницата да не съществува.</p>
        <a href="{{ route('home') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700">Към началото</a>
    </div>
</section>
@endsection
