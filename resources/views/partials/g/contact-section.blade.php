{{--
    The one contact section of design G (home #kontakt, /kontakti, /paketi #oferta): the heading, the short text and the promise beside the form, then the phone and the email.
    $anchor = the id of the section ('kontakt' or 'oferta'; the other one is an empty alias so both links work everywhere), $asH1 = the heading is the page's h1 (/kontakti),
    $full = phones with call, Viber and WhatsApp links plus the city (/kontakti).
--}}
@php
    $anchor = $anchor ?? 'kontakt';
    $alias = $anchor === 'oferta' ? 'kontakt' : 'oferta';
    $headTag = ! empty($asH1) ? 'h1' : 'h2';
    $contactEmail = config('creatium.contact.email');
@endphp
<section class="g-s g-last g-cform {{ ! empty($asH1) ? 'g-ph-form' : '' }}" id="{{ $anchor }}">
    <span id="{{ $alias }}" class="g-alias" aria-hidden="true"></span>
    <div class="g-wrap g-ct">
        <div class="g-ct-info">
            <{{ $headTag }} class="g-h g-ct-h">{{ __('Обсъдете проекта си с нас') }}</{{ $headTag }}>
            <p class="g-ct-text">{{ __('Разкажете ни накратко за бизнеса си. Ще прегледаме текущото ви присъствие и ще ви предложим конкретни стъпки за растеж.') }}</p>
            <p class="g-promise">{{ __('Отговаряме в рамките на един работен ден.') }}</p>
            <div class="g-info">
                @if(! empty($full))
                    <div>
                        <span class="g-lbl">{{ __('Телефон') }}</span>
                        @include('partials.g.phones', ['phones' => g_phones()])
                    </div>
                @else
                    @foreach(g_phones() as $phone)
                        <div><span class="g-lbl">{{ __('Телефон') }}</span><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></div>
                    @endforeach
                @endif
                <div><span class="g-lbl">{{ __('Имейл') }}</span><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></div>
                @if(! empty($full))
                    <div><span class="g-lbl">{{ __('Град') }}</span><p>{{ __(config('creatium.contact.city')) }}, {{ __('България') }}</p></div>
                @endif
            </div>
        </div>
        <div class="g-ct-form">@include('partials.g.contact-form', ['stepTag' => ! empty($asH1) ? 'h2' : 'h3'])</div>
    </div>
</section>
