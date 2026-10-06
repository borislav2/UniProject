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

<section class="py-16 md:py-20 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-10 dark:text-white">{{ __('Как започваме') }}</h2>
        <ol class="grid md:grid-cols-3 gap-6">
            @foreach($steps as $step)
                <li class="reveal rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: {{ $loop->index * 100 }}ms">
                    <div class="flex items-center justify-between">
                        <span class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center dark:bg-white/10"><x-icon :name="$step['icon']" class="text-brand-600 dark:text-brand-300" /></span>
                        <span class="text-3xl font-extrabold text-brand-400" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-bold text-brand-950 dark:text-white"><span class="sr-only">{{ __('Стъпка :n', ['n' => $loop->iteration]) }}: </span>{{ $step['title'] }}</h3>
                    <p class="mt-2 text-gray-600 leading-relaxed dark:text-gray-300">{{ $step['description'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section class="py-16 md:py-20 bg-white dark:bg-ink-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-8 dark:text-white">{{ __('Често задавани въпроси') }}</h2>
        <div class="space-y-3">
            @foreach($faq as $item)
                <details class="group bg-brand-50/60 rounded-xl border border-gray-200 p-5 open:shadow-sm reveal dark:bg-white/[0.03] dark:border-white/10">
                    <summary class="font-semibold text-brand-950 cursor-pointer list-none flex items-center justify-between gap-4 [&::-webkit-details-marker]:hidden dark:text-white">{{ $item['q'] }}<x-icon name="chevron-down" class="text-xs text-gray-400 transition-transform group-open:rotate-180" /></summary>
                    <p class="text-gray-600 mt-3 dark:text-gray-300">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

@endsection
