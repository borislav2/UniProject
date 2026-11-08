@extends('layouts.public')

{{-- /proverka-na-sait in design G: what we check as numbered rows, the form (domain, email, consent) and what happens after the report. --}}
@section('title', __('Безплатна проверка на сайт за SEO, скорост и Google'))
@section('meta_description', __('Въведете адреса на сайта си и получете безплатен отчет на имейла. Обхождаме до :n страници за счупени връзки, заглавия за Google, скорост, HTTPS, sitemap и още.', ['n' => config('creatium.site_check.crawl_limit')]))
@section('g_lean', 'inner')

@php
    $checks = [
        ['lock', __('Защитена връзка'), __('Работи ли HTTPS и води ли адресът без „s“ към него.')],
        ['gauge-high', __('Скорост на сървъра'), __('Колко бързо започва да се зарежда страницата.')],
        ['heading', __('Заглавие и описание за Google'), __('Има ли ги и с подходяща дължина ли са.')],
        ['display', __('Телефон'), __('Настроен ли е сайтът за телефон и набира ли се номерът с едно докосване.')],
        ['eye', __('Видим ли е за Google'), __('Дали сайтът не е скрит по погрешка и има ли карта на сайта.')],
        ['robot', __('Данни за Google и AI'), __('Описан ли е бизнесът така, че машините да го разберат, и не спира ли сайтът ChatGPT и другите AI асистенти.')],
        ['link', __('Споделяне на линк'), __('Какво се показва, когато някой изпрати линка ви.')],
        ['image', __('Снимки'), __('Имат ли описания, които Google разбира, и не са ли твърде тежки.')],
        ['triangle-exclamation', __('Счупени връзки и грешки'), __('Страници, които не се отварят, пренасочвания и връзки към несъществуващи адреси.')],
        ['clipboard-list', __('Всички страници, не само първата'), __('Заглавия и описания без повторения, основни заглавия, достатъчно текст и страници, скрити от Google.')],
    ];
@endphp

@section('content')
@include('partials.g.page-head', ['eyebrow' => __('Безплатно'), 'heading' => __('Проверка на сайт'), 'sub' => __('Въведете адреса на сайта и имейл. Обхождаме до :n страници и до няколко минути получавате отчет на имейла какво работи и какво да оправите.', ['n' => config('creatium.site_check.crawl_limit')])])

<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono"><b>01</b>{{ __('Проверка') }}</p>
            <h2 class="g-h">{{ __('Какво проверяваме') }}</h2>
            <p class="g-sh-text">{{ __('Автоматично обхождаме до :n страници на сайта ви. Отчетът е на разбираем език, с обяснение какво значи всеки резултат и къде е проблемът.', ['n' => config('creatium.site_check.crawl_limit')]) }}</p>
            <div id="site-check-form" class="g-fbox g-sticky-form">
                @if(session('site_check_sent'))
                    <div class="g-thanks" role="status">
                        <p class="g-mono">{{ __('Изпратено') }}</p>
                        <h2 class="g-h">{{ __('Проверката започна') }}</h2>
                        <p>{{ __('Ще изпратим отчета на :email до няколко минути, защото обхождането на целия сайт отнема малко повече време. Ако не го виждате, проверете папка „Спам“ или „Промоции“.', ['email' => session('site_check_sent')]) }}</p>
                        <a class="g-btn g-ghost" href="{{ lroute('site-check') }}">{{ __('Проверете друг сайт') }}</a>
                    </div>
                @else
                    <h2 class="g-h g-fh">{{ __('Получете отчета на имейла си') }}</h2>
                    <form action="{{ lroute('site-check.submit') }}" method="POST" class="g-f" novalidate>
                        @csrf
                        <div class="g-trap" aria-hidden="true">
                            <label for="website">{{ __('Не попълвайте това поле') }}</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="g-fld">
                            <label for="domain">{{ __('Адрес на сайта') }}</label>
                            <input class="g-in" type="text" id="domain" name="domain" value="{{ old('domain') }}" required inputmode="url" autocomplete="url" autocapitalize="off" spellcheck="false" placeholder="{{ __('vashiyatsait.bg') }}">
                            @error('domain')<p class="g-err">{{ $message }}</p>@enderror
                        </div>
                        <div class="g-fld">
                            <label for="email">{{ __('Имейл за отчета') }}</label>
                            <input class="g-in" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')<p class="g-err">{{ $message }}</p>@enderror
                        </div>
                        <div class="g-fld">
                            <label class="g-chk">
                                <input type="checkbox" name="consent" value="1" {{ old('consent') ? 'checked' : '' }}>
                                <span>{!! __('Съгласен/на съм да използвате имейла ми, за да ми изпратите отчета и да се свържете с мен за него. Прочетох :link.', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank">' . e(__('Политиката за поверителност')) . '</a>']) !!}</span>
                            </label>
                            @error('consent')<p class="g-err">{{ $message }}</p>@enderror
                        </div>
                        @include('partials.turnstile')
                        <p class="g-promise">{{ __('Безплатно и без ангажимент. Не изпращаме реклами.') }}</p>
                        <button type="submit" class="g-btn g-big">{{ __('Проверете сайта') }}<i></i></button>
                    </form>
                @endif
            </div>
        </div>
        <ol class="g-rows g-rows-tight">
            @foreach($checks as [$icon, $title, $text])
                <li><span class="g-n g-mono">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p></li>
            @endforeach
        </ol>
    </div>
</section>

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap">
        <div class="g-sh">
            <p class="g-mono"><b>02</b>{{ __('След отчета') }}</p>
        </div>
        <div class="g-cells">
            <div><span class="g-mono g-mute">01</span><h3>{{ __('Оправяте сами') }}</h3><p>{{ __('Всеки резултат е обяснен. Много от нещата се оправят за минути от човека, който поддържа сайта.') }}</p></div>
            <div><span class="g-mono g-mute">02</span><h3>{{ __('Подробен одит') }}</h3><p>{{ __('Преглеждаме целия сайт ръчно и ви даваме план какво да оправите първо. Цената е според големината на сайта.') }}</p></div>
            <div><span class="g-mono g-mute">03</span><h3>{{ __('Оправяме ние') }}</h3><p>{{ __('Ако не искате да се занимавате, кажете ни. Ще ви кажем цената, преди да започнем.') }}</p></div>
        </div>
        <p class="g-after"><a class="g-link" href="{{ lroute('contact') }}">{{ __('Свържете се с нас') }}</a></p>
    </div>
</section>
@endsection
