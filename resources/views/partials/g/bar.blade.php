{{-- Phone bar of the redesign (concept G): call and quote, below md only; hidden while a form is in view (resources/js/g-bar.js). --}}
@php($gPhone = g_phones())
<div class="g-bar" data-g-bar>
    @if($gPhone)
        <a class="g-bar-call" href="tel:{{ preg_replace('/[^0-9+]/', '', $gPhone[0]) }}">{{ __('Обадете се') }}</a>
    @endif
    <a class="g-bar-offer" href="{{ lroute('packages') }}#oferta">{{ __('Изпратете запитване') }}</a>
</div>
