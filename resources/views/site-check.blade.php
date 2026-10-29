@extends('layouts.public')

@section('title', __('Безплатна проверка на сайт: SEO, скорост и Google'))
@section('meta_description', __('Въведете адреса на сайта си и получете безплатен отчет на имейла: HTTPS, скорост на сървъра, заглавия за Google, телефон, sitemap и още.'))

@php
    $checks = [
        ['lock', __('Защитена връзка'), __('Работи ли HTTPS и води ли адресът без „s“ към него.')],
        ['gauge-high', __('Скорост на сървъра'), __('Колко бързо започва да се зарежда страницата.')],
        ['heading', __('Заглавие и описание за Google'), __('Има ли ги и с подходяща дължина ли са.')],
        ['display', __('Телефон'), __('Настроен ли е сайтът за телефон и набира ли се номерът с едно докосване.')],
        ['eye', __('Видим ли е за Google'), __('Дали сайтът не е скрит по погрешка и има ли карта на сайта.')],
        ['robot', __('Данни за Google и AI'), __('Описан ли е бизнесът така, че машините да го разберат, и не спира ли сайтът ChatGPT и другите AI асистенти.')],
        ['link', __('Споделяне на линк'), __('Какво се показва, когато някой изпрати линка ви.')],
        ['image', __('Снимки'), __('Имат ли описания, които Google разбира.')],
    ];
@endphp

@section('content')
@include('partials/page-header', ['eyebrow' => __('Безплатно'), 'heading' => __('Проверка на сайт'), 'sub' => __('Въведете адреса на сайта и имейл. До няколко минути ще получите отчет какво работи и какво да оправите.')])

<section class="py-16 md:py-24 bg-brand-50/60 dark:bg-ink-900">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 items-start">
        <div class="lg:col-span-6">
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('Какво проверяваме') }}</h2>
            <p class="mt-3 text-gray-600 leading-relaxed dark:text-gray-300">{{ __('Автоматично, на началната страница. Отчетът е на разбираем език, с обяснение какво значи всеки резултат.') }}</p>
            <ul class="mt-8 grid sm:grid-cols-2 gap-x-6 gap-y-6">
                @foreach($checks as [$icon, $title, $text])
                    <li class="flex items-start gap-3">
                        <span class="w-10 h-10 shrink-0 rounded-xl bg-white ring-1 ring-brand-100 shadow-sm inline-flex items-center justify-center dark:bg-white/5 dark:ring-white/10"><x-icon name="{{ $icon }}" class="text-sm text-brand-600 dark:text-brand-300" /></span>
                        <span><span class="block font-semibold text-brand-950 dark:text-white">{{ $title }}</span><span class="block mt-0.5 text-sm text-gray-600 leading-relaxed dark:text-gray-300">{{ $text }}</span></span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="lg:col-span-6 reveal">
            <div id="site-check-form" class="bg-white rounded-2xl p-6 md:p-8 shadow-2xl shadow-brand-950/10 ring-1 ring-gray-100 text-gray-900 dark:bg-ink-950 dark:text-gray-100 dark:ring-white/10 dark:shadow-black/40">
                @if(session('site_check_sent'))
                    <div class="text-center py-6" role="status">
                        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center dark:bg-green-500/15">
                            <x-icon name="paper-plane" class="text-green-600 text-xl dark:text-green-400" />
                        </div>
                        <h2 class="text-xl font-bold mb-2">{{ __('Проверката започна') }}</h2>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Ще изпратим отчета на :email до няколко минути. Ако не го виждате, проверете папка „Спам“ или „Промоции“.', ['email' => session('site_check_sent')]) }}</p>
                        <a href="{{ lroute('site-check') }}" class="mt-6 inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">{{ __('Проверете друг сайт') }} <x-icon name="arrow-right" class="text-xs" /></a>
                    </div>
                @else
                    <h2 class="text-xl font-bold text-brand-950 dark:text-white">{{ __('Получете отчета на имейла си') }}</h2>
                    <form action="{{ lroute('site-check.submit') }}" method="POST" class="mt-6 space-y-4" novalidate>
                        @csrf
                        <div class="absolute -left-[9999px]" aria-hidden="true">
                            <label for="website">{{ __('Не попълвайте това поле') }}</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div>
                            <label for="domain" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Адрес на сайта') }}</label>
                            <input type="text" id="domain" name="domain" value="{{ old('domain') }}" required inputmode="url" autocomplete="url" autocapitalize="off" spellcheck="false" placeholder="{{ __('vashiyatsait.bg') }}"
                                   class="w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30 placeholder:text-gray-500 dark:placeholder:text-gray-400">
                            @error('domain')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Имейл за отчета') }}</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                   class="w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30">
                            @error('email')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input type="checkbox" name="consent" value="1" class="mt-1 accent-brand-600" {{ old('consent') ? 'checked' : '' }}>
                                <span>{!! __('Съгласен/на съм да използвате имейла ми, за да ми изпратите отчета и да се свържете с мен за него. Прочетох :link.', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank" class="text-brand-700 underline dark:text-brand-300">' . e(__('Политиката за поверителност')) . '</a>']) !!}</span>
                            </label>
                            @error('consent')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn-shine w-full inline-flex items-center justify-center gap-2 bg-brand-950 text-white py-3.5 px-6 rounded-xl font-semibold hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                            {{ __('Проверете сайта') }} <x-icon name="magnifying-glass" class="text-sm" />
                        </button>
                        <p class="text-xs text-gray-500 text-center dark:text-gray-400">{{ __('Безплатно и без ангажимент. Не изпращаме реклами.') }}</p>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="py-16 md:py-24 bg-white dark:bg-ink-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 dark:text-white">{{ __('След отчета') }}</h2>
        <div class="mt-8 grid md:grid-cols-3 gap-6">
            <div class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.03]">
                <x-icon name="envelope-open-text" class="text-brand-600 dark:text-brand-300" />
                <h3 class="mt-4 font-bold text-brand-950 dark:text-white">{{ __('Оправяте сами') }}</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed dark:text-gray-300">{{ __('Всеки резултат е обяснен. Много от нещата се оправят за минути от човека, който поддържа сайта.') }}</p>
            </div>
            <div class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: 100ms">
                <x-icon name="clipboard-list" class="text-brand-600 dark:text-brand-300" />
                <h3 class="mt-4 font-bold text-brand-950 dark:text-white">{{ __('Подробен одит') }}</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed dark:text-gray-300">{{ __('Преглеждаме целия сайт ръчно и ви даваме план какво да оправите първо. Цената е според големината на сайта.') }}</p>
            </div>
            <div class="reveal card-hover rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.03]" style="--reveal-delay: 200ms">
                <x-icon name="screwdriver-wrench" class="text-brand-600 dark:text-brand-300" />
                <h3 class="mt-4 font-bold text-brand-950 dark:text-white">{{ __('Оправяме ние') }}</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed dark:text-gray-300">{{ __('Ако не искате да се занимавате, кажете ни. Ще ви кажем цената, преди да започнем.') }}</p>
            </div>
        </div>
        <div class="mt-10">
            <a href="{{ lroute('contact') }}" class="inline-flex items-center gap-2 font-semibold text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-white">{{ __('Свържете се с нас') }} <x-icon name="arrow-right" class="text-xs" /></a>
        </div>
    </div>
</section>
@endsection
