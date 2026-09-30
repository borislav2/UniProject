@extends('layouts.public')

@section('title', 'Услуги: уебсайтове, маркетинг и SEO')
@section('meta_description', 'Изработка на уебсайтове и онлайн магазини, маркетинг във Facebook, Instagram и Google и локално SEO за български бизнеси.')

@section('content')
@include('partials/page-header', ['heading' => 'Услуги', 'sub' => 'Всичко, от което бизнесът ви се нуждае, за да бъде намерен и избран онлайн.'])

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
        @foreach($services as $service)
            <article id="{{ \Illuminate\Support\Str::slug($service['title'], '-', 'en') ?: 'usluga-'.$loop->iteration }}" class="grid md:grid-cols-3 gap-8 scroll-mt-24">
                <div>
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas {{ $service['icon'] }} text-blue-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $service['title'] }}</h2>
                </div>
                <div class="md:col-span-2">
                    <p class="text-gray-600 mb-6">{{ $service['details'] }}</p>
                    <h3 class="font-semibold text-gray-900 mb-3">Какво включва</h3>
                    <ul class="space-y-2">
                        @foreach($service['includes'] as $item)
                            <li class="flex items-start gap-2 text-gray-700"><i class="fas fa-check text-blue-600 text-xs mt-1.5"></i>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-8">Често задавани въпроси</h2>
        <div class="space-y-3">
            @foreach($faq as $item)
                <details class="bg-white rounded-lg border p-4 group">
                    <summary class="font-semibold cursor-pointer">{{ $item['q'] }}</summary>
                    <p class="text-gray-600 mt-3">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

@include('partials/cta')
@endsection
