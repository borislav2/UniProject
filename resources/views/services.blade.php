@extends('layouts.public')

@section('title', 'Услуги: сайтове, SEO и имейл кампании')
@section('meta_description', 'Изработка и поддръжка на сайтове, мониторинг, локално SEO и видимост в AI търсачки, имейл кампании. За малки и средни фирми в България.')

@section('content')
@include('partials/page-header', ['heading' => 'Услуги', 'sub' => 'Правим сайтове, грижим се да работят и да ги намират в Google, и настройваме имейл кампании. Ето какво включва всяко.'])

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 divide-y divide-gray-100 [&>*]:py-12 [&>*:first-child]:pt-0">
        @foreach($services as $service)
            <article id="{{ $service['slug'] }}" class="grid md:grid-cols-3 gap-8 scroll-mt-28 reveal">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-brand-gradient flex items-center justify-center mb-4 shadow-lg shadow-brand-900/20">
                        <x-icon :name="$service['icon']" class="text-white" />
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-brand-950">{{ $service['title'] }}</h2>
                    <p class="mt-1 text-sm font-semibold text-brand-600">{{ $service['tag'] }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-lg text-gray-900 mb-3">{{ $service['description'] }}</p>
                    <p class="text-gray-600 mb-6">{{ $service['details'] }}</p>
                    <h3 class="font-semibold text-gray-900 mb-3">Какво включва</h3>
                    <ul class="space-y-2">
                        @foreach($service['includes'] as $item)
                            <li class="flex items-start gap-2 text-gray-700"><x-icon name="check" class="text-brand-600 text-xs mt-1.5" />{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="py-16 md:py-20 bg-brand-50/60">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-8">Често задавани въпроси</h2>
        <div class="space-y-3">
            @foreach($faq as $item)
                <details class="group bg-white rounded-xl border border-gray-200 p-5 open:shadow-sm reveal">
                    <summary class="font-semibold text-brand-950 cursor-pointer list-none flex items-center justify-between gap-4 [&::-webkit-details-marker]:hidden">{{ $item['q'] }}<x-icon name="chevron-down" class="text-xs text-gray-400 transition-transform group-open:rotate-180" /></summary>
                    <p class="text-gray-600 mt-3">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

@include('partials/cta')
@endsection
