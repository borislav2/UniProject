@extends('layouts.public')

@section('title', __('Услуги: сайтове, SEO и имейл кампании'))
@section('meta_description', __('Изработка и поддръжка на сайтове, мониторинг, локално SEO и видимост в AI търсачки, имейл кампании. За малки и средни бизнеси в България.'))

@section('content')
@include('partials/page-header', ['heading' => __('Услуги'), 'sub' => __('Сайт, който работи, клиенти, които ви намират, и грижа и след старта. Всичко с ясна оферта, преди да започнем.')])

<section class="py-16 bg-white dark:bg-ink-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 divide-y divide-gray-100 dark:divide-white/10 [&>*]:py-12 [&>*:first-child]:pt-0">
        @foreach($services as $service)
            <article id="{{ $service['slug'] }}" class="grid md:grid-cols-3 gap-8 scroll-mt-28 reveal">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-brand-gradient flex items-center justify-center mb-4 shadow-lg shadow-brand-900/20">
                        <x-icon :name="$service['icon']" class="text-white" />
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ $service['title'] }}</h2>
                    <p class="mt-1 text-sm font-semibold text-brand-600 dark:text-brand-300">{{ $service['tag'] }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-lg text-gray-900 mb-3 dark:text-white">{{ $service['description'] }}</p>
                    <p class="text-gray-600 mb-6 dark:text-gray-300">{{ $service['details'] }}</p>
                    <h3 class="font-semibold text-gray-900 mb-3 dark:text-white">{{ __('Какво включва') }}</h3>
                    <ul class="space-y-2">
                        @foreach($service['includes'] as $item)
                            <li class="flex items-start gap-2 text-gray-700 dark:text-gray-200"><x-icon name="check" class="text-brand-600 text-xs mt-1.5 dark:text-brand-400" />{{ $item }}</li>
                        @endforeach
                    </ul>
                    @php($topic = ['websites' => 'website', 'monitoring' => 'monitoring', 'geo-seo' => 'marketing', 'email' => 'marketing'][$service['slug']] ?? null)
                    <a href="{{ lroute('contact') }}{{ $topic ? '?service=' . $topic : '' }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-brand-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                        {{ __('Запитване за тази услуга') }} <x-icon name="arrow-right" class="text-xs" />
                    </a>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
