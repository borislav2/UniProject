@extends('layouts.public')

@section('title', 'Проекти')
@section('meta_description', 'Избрани проекти на Creatium Lab: уебсайтове, онлайн магазини и маркетинг кампании за български бизнеси.')

@section('content')
@include('partials/page-header', ['heading' => 'Проекти', 'sub' => 'Избрани работи за български бизнеси.'])

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($projects->isEmpty())
            <div class="max-w-xl mx-auto text-center py-8">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-brand-50 flex items-center justify-center">
                    <i class="fas fa-rocket text-brand-600 text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold mb-3">Първите проекти идват скоро</h2>
                <p class="text-gray-600 mb-6">Работим по първите си клиентски проекти и ще ги покажем тук. Искате ли вашият да е сред тях?</p>
                <a href="{{ route('contact') }}" class="inline-block bg-brand-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-700 transition-colors">Свържете се с нас</a>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($projects as $project)
                    <article class="border border-gray-200 rounded-2xl p-7 card-hover bg-white reveal">
                        <span class="text-xs font-semibold uppercase tracking-wider text-brand-600">{{ $project->category->name }}</span>
                        <h2 class="text-xl font-bold text-brand-950 mt-2 mb-2">{{ $project->name }}</h2>
                        <p class="text-gray-600 mb-4">{{ $project->description }}</p>
                        @if($project->technologies->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($project->technologies as $technology)
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">{{ $technology->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

@if($projects->isNotEmpty())
    @include('partials/cta')
@endif
@endsection
